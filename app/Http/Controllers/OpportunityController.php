<?php

namespace App\Http\Controllers;

use App\Models\InvestmentOpportunity;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(): View
    {
        $opportunities = InvestmentOpportunity::query()
            ->with(['category', 'documents'])
            ->where('availability_status', 'published')
            ->where('legal_review_status', 'approved')
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->whereHas('documents', fn ($query) => $query->where('is_investor_visible', true))
            ->latest('published_at')
            ->paginate(12);

        return view('opportunities.index', compact('opportunities'));
    }

    public function show(string $slug): View
    {
        $opportunity = InvestmentOpportunity::query()
            ->with(['category', 'documents' => fn ($query) => $query->where('is_investor_visible', true)])
            ->where('slug', $slug)
            ->where('availability_status', 'published')
            ->where('legal_review_status', 'approved')
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->whereHas('documents', fn ($query) => $query->where('is_investor_visible', true))
            ->firstOrFail();

        return view('opportunities.show', compact('opportunity'));
    }
}