@extends('layouts.app')
@section('title', 'Investment opportunities | Commonwealth')
@section('content')
<section class="page-hero"><div class="container-xl"><p class="eyebrow"><span></span> THE OPPORTUNITY CATALOG</p><h1>Explore with<br><em>perspective.</em></h1><p>Opportunities appear here only after their legal structure, disclosures, and supporting documents have been reviewed.</p></div></section>
<section class="container-xl catalog-wrap">
    @if ($opportunities->count())
        <div class="catalog-grid">
            @foreach ($opportunities as $opportunity)
                <article class="catalog-card">
                    <div class="catalog-visual"><span>{{ str($opportunity->category->name)->upper() }}</span><i>✦</i></div>
                    <div class="catalog-body"><p class="catalog-category">{{ $opportunity->category->name }}</p><h2>{{ $opportunity->title }}</h2><p>{{ Illuminate\Support\Str::limit($opportunity->description, 150) }}</p><a class="quiet-link" href="{{ route('opportunities.show', $opportunity->slug) }}">Review details <span>↗</span></a></div>
                </article>
            @endforeach
        </div>
        <div class="catalog-pagination">{{ $opportunities->links() }}</div>
    @else
        <div class="empty-catalog"><span class="empty-orbit">✦</span><p class="eyebrow dark-eyebrow"><span></span> NO REVIEWED OFFERS YET</p><h2>We’re building the catalog with care.</h2><p>There are no live investment opportunities available right now. Category names on this site are educational and do not imply that an issuer, property, brand, or asset has offered ownership or securities.</p><p class="interest-note">An opportunity will appear here only after its legal structure, risk disclosures, and supporting documents have been reviewed.</p><a class="button button-navy" href="{{ route('home') }}">Back to overview <span>↗</span></a></div>
    @endif
</section>
@endsection