<?php

namespace App\Http\Controllers;

use App\Domain\Investments\SimulatedPortfolioService;
use App\Models\InvestmentAsset;
use App\Models\SimulatedPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SimulatedPortfolioController extends Controller
{
    public function trade(Request $request, InvestmentAsset $asset, SimulatedPortfolioService $portfolio): RedirectResponse
    {
        abort_unless($asset->is_active, 404);
        $data = $request->validate([
            'side' => ['required', Rule::in(['buy', 'sell'])],
            'quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,18', 'max:999999999999999999'],
            'unit_price' => ['required', 'numeric', 'gt:0', 'decimal:0,18', 'max:999999999999999999'],
            'fees' => ['nullable', 'numeric', 'gte:0', 'decimal:0,18', 'max:999999999999999999'],
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $portfolio->recordTrade($request->user(), $asset, $data);

        return back()->with('status', 'Hypothetical transaction recorded. It does not create an order or affect your account balance.');
    }

    public function updateValuation(Request $request, InvestmentAsset $asset): RedirectResponse
    {
        abort_unless($asset->is_active, 404);
        $rules = [
            'manual_price' => ['nullable', 'numeric', 'gt:0', 'decimal:0,18', 'max:999999999999999999'],
        ];
        foreach ($asset->details['financial_fields'] ?? [] as $field) {
            $metricKey = str($field)->snake()->toString();
            $rules['metrics.'.$metricKey] = ['nullable', 'numeric', 'gte:0', 'decimal:0,18', 'max:999999999999999999'];
            if (in_array($metricKey, ['occupancy', 'ownership_stake'], true)) {
                $rules['metrics.'.$metricKey][] = 'lte:100';
            }
            if (str_ends_with($metricKey, '_months')) {
                $rules['metrics.'.$metricKey][] = 'integer';
            }
        }
        $data = $request->validate($rules);
        $metrics = collect($asset->details['financial_fields'] ?? [])
            ->mapWithKeys(fn (string $field) => [str($field)->snake()->toString() => $data['metrics'][str($field)->snake()->toString()] ?? null])
            ->filter(fn ($value) => filled($value))
            ->all();
        if (blank($data['manual_price'] ?? null) && $metrics === []) {
            throw ValidationException::withMessages(['manual_price' => 'Enter a valuation or at least one asset-specific estimate.']);
        }

        $position = $request->user()->simulatedPositions()->firstOrNew(['investment_asset_id' => $asset->id]);
        if (! $position->exists) {
            $position->quantity = '0';
            $position->average_cost = '0';
            $position->cost_basis = '0';
            $position->realized_pnl = '0';
        }
        if (filled($data['manual_price'] ?? null)) {
            $position->manual_price = $data['manual_price'];
            $position->manual_price_updated_at = now();
        }
        if ($request->has('metrics')) {
            $position->manual_metrics = $metrics;
        }
        $position->save();

        return back()->with('status', 'Your user-entered valuation and operating estimates were saved.');
    }

    public function removePosition(Request $request, InvestmentAsset $asset): RedirectResponse
    {
        $position = $request->user()->simulatedPositions()->where('investment_asset_id', $asset->id)->firstOrFail();
        if ($position->hasQuantity()) {
            return back()->withErrors(['position' => 'Record a hypothetical sale of the remaining quantity before removing this holding.']);
        }

        $position->delete();

        return back()->with('status', 'Closed position removed. Transaction history is retained.');
    }

    public function addToWatchlist(Request $request, InvestmentAsset $asset): RedirectResponse
    {
        abort_unless($asset->is_active, 404);
        DB::table('investment_watchlist')->updateOrInsert(
            ['user_id' => $request->user()->id, 'investment_asset_id' => $asset->id],
            ['created_at' => now(), 'updated_at' => now()],
        );

        return back()->with('status', 'Added to your watchlist.');
    }

    public function removeFromWatchlist(Request $request, InvestmentAsset $asset): RedirectResponse
    {
        DB::table('investment_watchlist')
            ->where('user_id', $request->user()->id)
            ->where('investment_asset_id', $asset->id)
            ->delete();

        return back()->with('status', 'Removed from your watchlist.');
    }
}