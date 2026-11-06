<?php

namespace App\Domain\Investments;

use App\Models\InvestmentAsset;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketDataService
{
    public function quote(InvestmentAsset $asset): ?array
    {
        if (! config('services.market_data.enabled', true)) {
            return null;
        }
        if (! in_array($asset->data_provider, ['coingecko', 'finnhub'], true)) {
            return null;
        }

        return Cache::remember('market-quote:'.$asset->data_provider.':'.$asset->provider_asset_id, now()->addMinute(), function () use ($asset): ?array {
            return $asset->data_provider === 'coingecko'
                ? $this->coinGeckoQuote($asset)
                : $this->finnhubQuote($asset);
        });
    }

    private function coinGeckoQuote(InvestmentAsset $asset): ?array
    {
        $response = $this->request('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => $asset->provider_asset_id,
            'vs_currencies' => 'usd',
            'include_24hr_change' => 'true',
            'include_market_cap' => 'true',
            'include_24hr_vol' => 'true',
            'include_last_updated_at' => 'true',
        ]);

        if (! $response?->successful()) {
            return null;
        }

        $data = $response->json($asset->provider_asset_id);
        if (! is_array($data) || ! isset($data['usd']) || ! is_numeric($data['usd'])) {
            return null;
        }

        $history = Cache::remember('market-history:coingecko:'.$asset->provider_asset_id, now()->addMinutes(30), function () use ($asset): array {
            $historical = $this->request('https://api.coingecko.com/api/v3/coins/'.$asset->provider_asset_id.'/market_chart', [
                'vs_currency' => 'usd',
                'days' => 180,
            ], 8);

            if (! $historical?->successful()) {
                return [];
            }

            return collect($historical->json('prices', []))
                ->filter(fn (array $point, int $index) => $index % 24 === 0)
                ->map(fn (array $point) => ['time' => (int) ($point[0] / 1000), 'value' => (float) $point[1]])
                ->values()
                ->all();
        });

        return [
            'price' => (float) $data['usd'],
            'change_percent' => isset($data['usd_24h_change']) ? (float) $data['usd_24h_change'] : null,
            'market_cap' => isset($data['usd_market_cap']) ? (float) $data['usd_market_cap'] : null,
            'volume' => isset($data['usd_24h_vol']) ? (float) $data['usd_24h_vol'] : null,
            'volume_unit' => 'USD',
            'updated_at' => isset($data['last_updated_at']) ? now()->setTimestamp((int) $data['last_updated_at']) : null,
            'source' => 'CoinGecko',
            'history' => $history,
        ];
    }

    private function finnhubQuote(InvestmentAsset $asset): ?array
    {
        $key = config('services.finnhub.key');
        if (! filled($key)) {
            return null;
        }

        $quoteResponse = $this->request('https://finnhub.io/api/v1/quote', [
            'symbol' => $asset->provider_asset_id,
            'token' => $key,
        ]);
        if (! $quoteResponse?->successful() || ! is_numeric($quoteResponse->json('c')) || (float) $quoteResponse->json('c') <= 0) {
            return null;
        }

        $profileResponse = $this->request('https://finnhub.io/api/v1/stock/profile2', [
            'symbol' => $asset->provider_asset_id,
            'token' => $key,
        ]);
        $profile = $profileResponse?->successful() ? $profileResponse->json() : [];

        $to = now()->timestamp;
        $from = now()->subMonths(6)->timestamp;
        $candleResponse = $this->request('https://finnhub.io/api/v1/stock/candle', [
            'symbol' => $asset->provider_asset_id,
            'resolution' => 'D',
            'from' => $from,
            'to' => $to,
            'token' => $key,
        ], 8);
        $candles = $candleResponse?->successful() && $candleResponse->json('s') === 'ok' ? $candleResponse->json() : [];
        $history = collect($candles['t'] ?? [])->map(fn (int $time, int $index) => [
            'time' => $time,
            'value' => (float) ($candles['c'][$index] ?? 0),
        ])->filter(fn (array $point) => $point['value'] > 0)->values()->all();
        $volumes = $candles['v'] ?? [];
        $latestVolume = count($volumes) ? end($volumes) : null;

        return [
            'price' => (float) $quoteResponse->json('c'),
            'change_percent' => is_numeric($quoteResponse->json('dp')) ? (float) $quoteResponse->json('dp') : null,
            'market_cap' => is_numeric($profile['marketCapitalization'] ?? null) ? (float) $profile['marketCapitalization'] * 1_000_000 : null,
            'volume' => is_numeric($latestVolume) ? (float) $latestVolume : null,
            'volume_unit' => 'shares',
            'updated_at' => is_numeric($quoteResponse->json('t')) ? now()->setTimestamp((int) $quoteResponse->json('t')) : null,
            'source' => 'Finnhub',
            'history' => $history,
        ];
    }

    private function request(string $url, array $parameters, int $timeout = 5): ?Response
    {
        try {
            return Http::acceptJson()->timeout($timeout)->get($url, $parameters);
        } catch (ConnectionException) {
            return null;
        }
    }
}