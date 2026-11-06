<?php

namespace Database\Seeders;

use App\Models\InvestmentAsset;
use Illuminate\Database\Seeder;

class InvestmentAssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['slug' => 'bitcoin', 'name' => 'Bitcoin', 'symbol' => 'BTC', 'category' => 'cryptocurrency', 'asset_type' => 'digital_asset', 'data_provider' => 'coingecko', 'provider_asset_id' => 'bitcoin', 'description' => 'A decentralized digital asset. Market metrics are sourced from CoinGecko when available.', 'details' => ['unit_label' => 'BTC']],
            ['slug' => 'ethereum', 'name' => 'Ethereum', 'symbol' => 'ETH', 'category' => 'cryptocurrency', 'asset_type' => 'digital_asset', 'data_provider' => 'coingecko', 'provider_asset_id' => 'ethereum', 'description' => 'The native digital asset of the Ethereum network. Market metrics are sourced from CoinGecko when available.', 'details' => ['unit_label' => 'ETH']],
            ['slug' => 'tesla', 'name' => 'Tesla, Inc.', 'symbol' => 'TSLA', 'category' => 'public_equity', 'asset_type' => 'public_stock', 'data_provider' => 'finnhub', 'provider_asset_id' => 'TSLA', 'description' => 'Publicly traded common stock of Tesla, Inc.', 'details' => ['unit_label' => 'shares']],
            ['slug' => 'disney', 'name' => 'The Walt Disney Company', 'symbol' => 'DIS', 'category' => 'public_equity', 'asset_type' => 'public_stock', 'data_provider' => 'finnhub', 'provider_asset_id' => 'DIS', 'description' => 'Publicly traded common stock of The Walt Disney Company. Disney owns Marvel Entertainment; this is indirect corporate exposure, not direct ownership of Marvel Studios.', 'details' => ['unit_label' => 'shares']],
            ['slug' => 'scrub-daddy', 'name' => 'Scrub Daddy', 'symbol' => null, 'category' => 'private_company', 'asset_type' => 'private_brand', 'description' => 'Private consumer-products brand. This profile does not imply that shares or an investment offer are available.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Estimated valuation', 'Annual revenue', 'Annual operating expenses', 'Acquisition cost']]],
            ['slug' => 'red-bull', 'name' => 'Red Bull', 'symbol' => null, 'category' => 'private_company', 'asset_type' => 'private_brand', 'description' => 'Privately held beverage and media group. This profile does not imply that shares or an investment offer are available.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Estimated valuation', 'Annual revenue', 'Annual operating expenses', 'Acquisition cost']]],
            ['slug' => 'nfl-commercial-opportunities', 'name' => 'NFL commercial opportunities', 'symbol' => null, 'category' => 'sports_entertainment', 'asset_type' => 'commercial_opportunity', 'description' => 'Educational profile for potential sports-related commercial structures. No league affiliation, rights, or investment offer is represented.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Acquisition cost', 'Annual operating expenses', 'Annual estimated revenue', 'Contract term (months)']]],
            ['slug' => 'sports-businesses', 'name' => 'Sports-related businesses', 'symbol' => null, 'category' => 'sports_entertainment', 'asset_type' => 'operating_business', 'description' => 'General profile for sports-related operating businesses. No specific company or available offer is represented.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Acquisition cost', 'Annual operating expenses', 'Annual estimated revenue', 'Ownership stake']]],
            ['slug' => 'logistics-business', 'name' => 'Logistics business', 'symbol' => null, 'category' => 'business', 'asset_type' => 'operating_business', 'description' => 'Operating-business profile for a logistics company; financial inputs require independent diligence.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Acquisition cost', 'Annual operating expenses', 'Annual estimated revenue', 'Ownership stake']]],
            ['slug' => 'laundromat', 'name' => 'Laundromat', 'symbol' => null, 'category' => 'business', 'asset_type' => 'operating_business', 'description' => 'Operating-business profile for a laundromat; no purchase price, revenue, or return is assumed.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Acquisition cost', 'Annual operating expenses', 'Annual estimated revenue', 'Monthly lease costs']]],
            ['slug' => 'car-wash', 'name' => 'Car wash', 'symbol' => null, 'category' => 'business', 'asset_type' => 'operating_business', 'description' => 'Operating-business profile for a car wash; financial inputs require independent diligence.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Acquisition cost', 'Annual operating expenses', 'Annual estimated revenue', 'Monthly lease costs']]],
            ['slug' => 'vending-machines', 'name' => 'Vending machine business', 'symbol' => null, 'category' => 'business', 'asset_type' => 'operating_business', 'description' => 'Operating-business profile for vending machines; no purchase price, revenue, or return is assumed.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Equipment acquisition cost', 'Annual operating expenses', 'Annual estimated revenue', 'Machine count']]],
            ['slug' => 'rental-property', 'name' => 'Rental property', 'symbol' => null, 'category' => 'real_estate', 'asset_type' => 'property', 'description' => 'Property profile for rental real estate; no valuation, occupancy, or rental income is assumed.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Property valuation', 'Occupancy', 'Monthly rental income', 'Annual operating expenses']]],
            ['slug' => 'farmland', 'name' => 'Farmland', 'symbol' => null, 'category' => 'real_estate', 'asset_type' => 'property', 'description' => 'Property profile for farmland; no valuation, yield, or income is assumed.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Property valuation', 'Acres', 'Annual lease income', 'Annual operating expenses']]],
            ['slug' => 'storage-facility', 'name' => 'Storage facility', 'symbol' => null, 'category' => 'real_estate', 'asset_type' => 'property', 'description' => 'Property and operating-business profile for self-storage; no valuation, occupancy, or income is assumed.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Property valuation', 'Occupancy', 'Monthly rental income', 'Annual operating expenses']]],
            ['slug' => 'commercial-property', 'name' => 'Commercial property', 'symbol' => null, 'category' => 'real_estate', 'asset_type' => 'property', 'description' => 'Property profile for commercial real estate; no valuation, occupancy, or rental income is assumed.', 'details' => ['unit_label' => 'ownership units', 'financial_fields' => ['Property valuation', 'Occupancy', 'Monthly rental income', 'Annual operating expenses']]],
        ];

        foreach ($assets as $asset) {
            InvestmentAsset::query()->updateOrCreate(
                ['slug' => $asset['slug']],
                [...$asset, 'currency' => 'USD', 'is_active' => true],
            );
        }
    }
}