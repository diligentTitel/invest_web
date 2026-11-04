<?php

namespace Database\Seeders;

use App\Models\BlockchainNetwork;
use App\Models\PaymentProvider;
use App\Models\SupportedCryptocurrency;
use Illuminate\Database\Seeder;

class PaymentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'BTC', 'name' => 'Bitcoin', 'precision' => 8],
            ['code' => 'ETH', 'name' => 'Ethereum', 'precision' => 18],
            ['code' => 'USDT', 'name' => 'Tether USD', 'precision' => 6],
            ['code' => 'USDC', 'name' => 'USD Coin', 'precision' => 6],
        ] as $asset) {
            SupportedCryptocurrency::query()->updateOrCreate(
                ['code' => $asset['code']],
                [...$asset, 'is_enabled' => false],
            );
        }

        foreach ([
            ['code' => 'bitcoin', 'name' => 'Bitcoin', 'chain_identifier' => 'bitcoin'],
            ['code' => 'ethereum-erc20', 'name' => 'Ethereum ERC20', 'chain_identifier' => 'eip155:1'],
            ['code' => 'tron-trc20', 'name' => 'TRON TRC20', 'chain_identifier' => 'tron:mainnet'],
        ] as $network) {
            BlockchainNetwork::query()->updateOrCreate(
                ['code' => $network['code']],
                [...$network, 'required_confirmations' => 12, 'is_enabled' => false],
            );
        }

        foreach ([
            ['provider_key' => 'nowpayments', 'display_name' => 'NOWPayments'],
            ['provider_key' => 'coinbase-commerce', 'display_name' => 'Coinbase Commerce'],
        ] as $provider) {
            PaymentProvider::query()->updateOrCreate(
                ['provider_key' => $provider['provider_key']],
                [...$provider, 'status' => 'disabled'],
            );
        }
    }
}