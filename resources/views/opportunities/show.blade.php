@extends('layouts.app')
@section('title', $opportunity->title.' | Commonwealth')
@section('content')
<section class="page-hero"><div class="container-xl"><p class="eyebrow"><span></span> {{ $opportunity->category->name }}</p><h1>{{ $opportunity->title }}</h1><p>{{ $opportunity->description }}</p></div></section>
<section class="container-xl detail-wrap">
    <div class="detail-main"><p class="eyebrow dark-eyebrow"><span></span> OFFERING OVERVIEW</p><h2>Structure and documentation</h2><p>This opportunity has passed the platform’s initial publication review. Read all legal documents and eligibility terms before taking any action.</p>
        @if ($opportunity->documented_return_terms)<div class="detail-block"><h3>Documented return terms</h3><p>{{ json_encode($opportunity->documented_return_terms, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</p></div>@endif
        <div class="detail-block"><h3>Legal documents</h3><ul>@foreach ($opportunity->documents as $document)<li>{{ $document->document_type }}@if($document->version) · Version {{ $document->version }}@endif</li>@endforeach</ul><p class="field-hint">Documents will be served through authenticated, access-checked routes.</p></div>
    </div>
    <aside class="detail-aside"><p class="eyebrow dark-eyebrow"><span></span> KEY DETAILS</p><dl><dt>Currency</dt><dd>{{ $opportunity->currency }}</dd><dt>Risk classification</dt><dd>{{ $opportunity->risk_classification ?? 'See disclosures' }}</dd><dt>Minimum investment</dt><dd>{{ $opportunity->minimum_investment ?? 'See legal documents' }}</dd><dt>Duration</dt><dd>{{ $opportunity->duration_months ? $opportunity->duration_months.' months' : 'See legal documents' }}</dd></dl><p class="field-hint">Investing involves risk, including loss of capital. Availability and eligibility may vary.</p><button class="button button-navy w-100" type="button" disabled>Investments are not open yet</button></aside>
</section>
@endsection