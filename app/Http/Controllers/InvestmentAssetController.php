<?php

namespace App\Http\Controllers;

use App\Domain\Investments\MarketDataService;
use App\Models\InvestmentAsset;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentAssetController extends Controller
{
    private const CATEGORIES = [
        'cryptocurrency', 'public_equity', 'private_company', 'sports_entertainment',
        'business', 'real_estate',
    ];

    public function index(Request $request, MarketDataService $marketData): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'in:'.implode(',', self::CATEGORIES)],
            'sort' => ['nullable', 'string', 'in:name,category'],
        ]);

        $query = InvestmentAsset::query()->where('is_active', true);
        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->where(fn ($assets) => $assets->where('name', 'like', '%'.$term.'%')
                ->orWhere('symbol', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%'));
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('category', $filters['category']);
        }

        $assets = $query->orderBy($filters['sort'] ?? 'name')->paginate(12)->withQueryString();
        $quotes = $assets->getCollection()->mapWithKeys(fn (InvestmentAsset $asset) => [$asset->id => $marketData->quote($asset)]);
        $watchlistIds = $request->user()?->watchlistAssets()->pluck('investment_assets.id')->all() ?? [];

        return view('investments.index', [
            'assets' => $assets,
            'quotes' => $quotes,
            'watchlistIds' => $watchlistIds,
            'categories' => self::CATEGORIES,
            'filters' => $filters,
        ]);
    }

    public function show(InvestmentAsset $asset, Request $request, MarketDataService $marketData): View
    {
        abort_unless($asset->is_active, 404);
        $position = $request->user()?->simulatedPositions()->where('investment_asset_id', $asset->id)->first();
        $quote = $marketData->quote($asset);
        if (! $quote && $position?->manual_price) {
            $quote = [
                'price' => (float) $position->manual_price,
                'change_percent' => null,
                'market_cap' => null,
                'volume' => null,
                'updated_at' => $position->manual_price_updated_at,
                'source' => 'User-entered estimate',
                'history' => [],
            ];
        }

        $isWatchlisted = $request->user()?->watchlistAssets()->where('investment_assets.id', $asset->id)->exists() ?? false;

        return view('investments.show', compact('asset', 'quote', 'position', 'isWatchlisted'));
    }
}