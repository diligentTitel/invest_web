<?php

namespace Tests\Feature\Investor;

use App\Domain\Investments\MarketDataService;
use App\Domain\Investments\SimulatedPortfolioService;
use App\Models\InvestmentAsset;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MultiAssetCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_distinguishes_listed_assets_from_private_businesses_and_property(): void
    {
        $this->seed();

        $this->assertSame('public_stock', InvestmentAsset::query()->where('slug', 'tesla')->value('asset_type'));
        $this->assertSame('public_stock', InvestmentAsset::query()->where('slug', 'disney')->value('asset_type'));
        $this->assertSame('private_brand', InvestmentAsset::query()->where('slug', 'scrub-daddy')->value('asset_type'));
        $this->assertSame('property', InvestmentAsset::query()->where('slug', 'rental-property')->value('asset_type'));
        $this->assertSame('digital_asset', InvestmentAsset::query()->where('slug', 'bitcoin')->value('asset_type'));
        $this->assertNull(InvestmentAsset::query()->where('slug', 'laundromat')->value('symbol'));
        $this->assertSame(16, InvestmentAsset::query()->count());
    }

    public function test_explorer_search_and_laundromat_profile_use_business_fields_without_sample_values(): void
    {
        $this->seed();

        $this->get(route('investments.index', ['q' => 'laundromat']))
            ->assertOk()
            ->assertSee('Laundromat')
            ->assertDontSee('Tesla, Inc.');

        $this->get(route('investments.show', 'laundromat'))
            ->assertOk()
            ->assertSee('Acquisition cost')
            ->assertSee('Operating expenses')
            ->assertSee('Estimated revenue')
            ->assertSee('Not provided')
            ->assertSee('No verified market price');
    }

    public function test_market_quote_is_attributed_to_provider_and_missing_values_stay_missing(): void
    {
        $this->seed();
        config(['services.market_data.enabled' => true]);
        Http::fake([
            'https://api.coingecko.com/api/v3/simple/price*' => Http::response([
                'bitcoin' => [
                    'usd' => 65000.25,
                    'usd_24h_change' => 1.25,
                    'usd_market_cap' => 1270000000000,
                    'usd_24h_vol' => 31000000000,
                    'last_updated_at' => 1790000000,
                ],
            ]),
            'https://api.coingecko.com/api/v3/coins/bitcoin/market_chart*' => Http::response([
                'prices' => [[1790000000000, 64000], [1790000100000, 65000]],
            ]),
        ]);

        $bitcoin = InvestmentAsset::query()->where('slug', 'bitcoin')->firstOrFail();
        $quote = app(MarketDataService::class)->quote($bitcoin);

        $this->assertSame(65000.25, $quote['price']);
        $this->assertSame('CoinGecko', $quote['source']);
        $this->assertSame(1270000000000.0, $quote['market_cap']);
        $this->assertNotEmpty($quote['history']);

        $privateCompany = InvestmentAsset::query()->where('slug', 'red-bull')->firstOrFail();
        $this->assertNull(app(MarketDataService::class)->quote($privateCompany));
    }

    public function test_hypothetical_trades_use_average_cost_and_reject_overselling(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $asset = InvestmentAsset::query()->where('slug', 'tesla')->firstOrFail();
        $portfolio = app(SimulatedPortfolioService::class);

        $portfolio->recordTrade($user, $asset, ['side' => 'buy', 'quantity' => '2', 'unit_price' => '100', 'fees' => '0']);
        $sale = $portfolio->recordTrade($user, $asset, ['side' => 'sell', 'quantity' => '1', 'unit_price' => '125', 'fees' => '5']);

        $this->assertSame('20.000000000000000000', $sale->realized_pnl);
        $this->assertDatabaseHas('simulated_positions', [
            'user_id' => $user->id,
            'investment_asset_id' => $asset->id,
            'quantity' => '1.000000000000000000',
            'cost_basis' => '100.000000000000000000',
            'realized_pnl' => '20.000000000000000000',
        ]);

        try {
            $portfolio->recordTrade($user, $asset, ['side' => 'sell', 'quantity' => '2', 'unit_price' => '120', 'fees' => '0']);
            $this->fail('Overselling should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $this->assertSame(2, $user->simulatedTransactions()->count());
    }

    public function test_simulated_positions_are_scoped_to_the_authenticated_owner(): void
    {
        $this->seed();
        $role = Role::query()->where('slug', 'investor')->firstOrFail();
        $owner = User::factory()->create();
        $owner->forceFill(['role_id' => $role->id])->save();
        $otherUser = User::query()->findOrFail(User::factory()->create()->id);
        $otherUser->forceFill(['role_id' => $role->id])->save();
        $asset = InvestmentAsset::query()->where('slug', 'bitcoin')->firstOrFail();
        app(SimulatedPortfolioService::class)->recordTrade($owner, $asset, [
            'side' => 'buy', 'quantity' => '0.5', 'unit_price' => '50000', 'fees' => '0',
        ]);

        $this->actingAs($otherUser)
            ->put(route('portfolio.valuation.update', $asset->slug), ['manual_price' => '60000'])
            ->assertNotFound();

        $this->assertSame(0, $otherUser->simulatedPositions()->count());
    }
}