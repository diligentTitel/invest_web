@extends('layouts.app')
@section('title', 'Explore investments | Commonwealth')
@section('content')
<section class="page-hero"><div class="container-xl"><p class="eyebrow"><span></span> MULTI-ASSET EXPLORER</p><h1>Explore beyond<br><em>one asset class.</em></h1><p>Compare listed markets, private businesses, operating assets, and property profiles in one research catalog.</p></div></section>
<section class="container-xl catalog-wrap asset-explorer">
    <div class="asset-notice"><strong>Research and simulation only</strong><span>Catalog profiles are not investment offers. Hypothetical transactions do not place orders or move money.</span></div>
    <form class="asset-filters" method="GET" action="{{ route('investments.index') }}">
        <label class="asset-search"><span>Search assets</span><input class="form-control" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or symbol" maxlength="80"></label>
        <label><span>Category</span><select class="form-select form-control" name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ str($category)->replace('_', ' ')->title() }}</option>@endforeach</select></label>
        <label><span>Sort by</span><select class="form-select form-control" name="sort"><option value="name" @selected(($filters['sort'] ?? 'name') === 'name')>Name</option><option value="category" @selected(($filters['sort'] ?? '') === 'category')>Category</option></select></label>
        <button class="button button-navy" type="submit">Apply filters</button>
        @if($filters)<a class="asset-reset" href="{{ route('investments.index') }}">Clear</a>@endif
    </form>
    @if($assets->count())
        <div class="asset-grid">
            @foreach($assets as $asset)
                @php($quote = $quotes[$asset->id] ?? null)
                <article class="asset-card">
                    <div class="asset-card-top"><span class="asset-kind">{{ str($asset->category)->replace('_', ' ')->title() }}</span><span class="asset-symbol">{{ $asset->symbol ?? str($asset->asset_type)->replace('_', ' ')->title() }}</span></div>
                    <h2><a href="{{ route('investments.show', $asset->slug) }}">{{ $asset->name }}</a></h2>
                    <p>{{ Illuminate\Support\Str::limit($asset->description, 135) }}</p>
                    <div class="asset-card-quote">
                        @if($quote)
                            <strong>{{ $asset->currency }} {{ number_format($quote['price'], $asset->category === 'cryptocurrency' ? 4 : 2) }}</strong>
                            <span>{{ $quote['source'] }} · latest available</span>
                        @else
                            <strong>Price unavailable</strong>
                            <span>{{ in_array($asset->category, ['public_equity', 'cryptocurrency'], true) ? 'Market data not currently available' : 'No verified market price for this asset type' }}</span>
                        @endif
                    </div>
                    <div class="asset-card-actions"><a class="quiet-link" href="{{ route('investments.show', $asset->slug) }}">View profile <span>↗</span></a>
                        @auth
                            @if(auth()->user()->hasPermission('dashboard.view'))
                                @if(in_array($asset->id, $watchlistIds, true))
                                    <form method="POST" action="{{ route('watchlist.destroy', $asset->slug) }}">@csrf @method('DELETE')<button class="icon-action" type="submit" aria-label="Remove {{ $asset->name }} from watchlist" title="Remove from watchlist">★</button></form>
                                @else
                                    <form method="POST" action="{{ route('watchlist.store', $asset->slug) }}">@csrf<button class="icon-action" type="submit" aria-label="Add {{ $asset->name }} to watchlist" title="Add to watchlist">☆</button></form>
                                @endif
                            @endif
                        @endauth
                    </div>
                </article>
            @endforeach
        </div>
        <div class="catalog-pagination">{{ $assets->links() }}</div>
    @else
        <div class="asset-empty"><p class="eyebrow dark-eyebrow"><span></span> NO MATCHING ASSETS</p><h2>Try a different search or category.</h2><a class="quiet-link" href="{{ route('investments.index') }}">Show all profiles <span>↗</span></a></div>
    @endif
</section>
@endsection