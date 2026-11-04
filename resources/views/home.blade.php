@extends('layouts.app')

@section('title', 'Commonwealth | Clear investment information and risk')
@section('description', 'Explore investment structures, supporting documents, key risks, and posted account activity with Commonwealth.')


@section('body-class', 'home-page')

@section('content')
<section class="home-hero" aria-labelledby="home-hero-title">
    <div class="container-xl home-hero-grid">
        <div class="home-hero-copy">
            <p class="eyebrow"><span></span> WELCOME TO COMMONWEALTH</p>
            <h1 id="home-hero-title">Know the structure<br><em>before you invest.</em></h1>
            <p class="home-hero-lede">Explore how savings, pooled funds, and public markets work. Review the details and risks before considering an offer.</p>
            <div class="hero-actions">
                <a class="button button-gold" href="{{ route('opportunities.index') }}">Explore investment categories <span aria-hidden="true">&rarr;</span></a>
                @if (Route::has('register'))<a class="home-text-link" href="{{ route('register') }}">Create an account <span aria-hidden="true">&rarr;</span></a>@endif
                <a class="home-text-link" href="{{ route('how-it-works') }}">How Commonwealth works <span aria-hidden="true">&rarr;</span></a>
            </div>
            <p class="home-hero-note">Commonwealth is not currently offering investments or deposit services.</p>
            <a class="home-scroll-cue" href="#commonwealth-journey"><span>SCROLL TO EXPLORE</span><svg viewBox="0 0 16 28" aria-hidden="true"><path d="M8 1v23m-6-6 6 6 6-6"/></svg></a>
        </div>
        <div class="home-hero-stage">
            <figure class="home-hero-image">
                <img src="https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?auto=format&amp;fit=crop&amp;w=1500&amp;q=85" alt="A notebook and green leaves on a desk" width="1500" height="1100" fetchpriority="high" decoding="async">
            </figure>
            <div class="home-stage-card home-stage-card--terms"><span>START WITH THE TERMS</span><strong>Understand what is being offered.</strong></div>
            <div class="home-stage-card home-stage-card--risk"><span>KEEP RISK IN VIEW</span><strong>Every structure carries different risks.</strong></div>
            <p class="home-stage-caption"><span>COMMONWEALTH</span><span>Clarity before opportunity</span></p>
        </div>
    </div>
    <div class="container-xl hero-proof-row"><span>Transparent records</span><span>Document-led opportunities</span><span>Risk-first information</span></div>
</section>

<section class="home-story-section" id="commonwealth-journey" aria-labelledby="home-story-title">
    <div class="container-xl">
        <header class="home-story-heading">
            <p class="eyebrow home-eyebrow"><span></span> A CLEARER JOURNEY</p>
            <h2 id="home-story-title">Welcome. Begin with context.</h2>
            <p>Move through the questions that matter, one step at a time.</p>
        </header>
        <div class="home-story-grid">
            <div class="home-story-chapters">
                <article class="home-story-chapter" data-story-chapter="landscape">
                    <p class="home-story-step">01 <span>UNDERSTAND THE LANDSCAPE</span></p>
                    <h3>Different structures work in different ways.</h3>
                    <p>Explore savings, pooled funds, and public markets. Learn how each is commonly structured before looking at individual terms.</p>
                    <a class="home-text-link" href="{{ route('opportunities.index') }}">Explore the categories <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="home-story-chapter" data-story-chapter="documents">
                    <p class="home-story-step">02 <span>READ THE DOCUMENTS</span></p>
                    <h3>See the details behind an opportunity.</h3>
                    <p>Legal structure, eligibility, supporting documents, and risk disclosures belong in view before anyone considers an offer.</p>
                    <a class="home-text-link" href="{{ route('risk-disclosure') }}">Review the risk disclosure <span aria-hidden="true">&rarr;</span></a>
                </article>
                <article class="home-story-chapter" data-story-chapter="records">
                    <p class="home-story-step">03 <span>FOLLOW RECORDED ACTIVITY</span></p>
                    <h3>Account activity should be traceable.</h3>
                    <p>Posted activity is recorded in the financial ledger. Asset values and investment performance are not estimated without verified sources.</p>
                    <a class="home-text-link" href="{{ route('how-it-works') }}">See how records work <span aria-hidden="true">&rarr;</span></a>
                </article>
            </div>
            <div class="home-story-stage" role="group" aria-label="Investment information scenes">
                <figure class="home-story-visual is-active" data-story-visual="landscape" aria-hidden="false">
                    <img src="https://images.unsplash.com/photo-1633158829875-e5316a358c6f?auto=format&amp;fit=crop&amp;w=1100&amp;q=82" alt="A small plant beside a jar of coins" width="1100" height="850" loading="lazy" decoding="async">
                    <figcaption><span>01 / SAVINGS STRUCTURES</span><span>Start with the fundamentals</span></figcaption>
                </figure>
                <figure class="home-story-visual" data-story-visual="documents" aria-hidden="true">
                    <img src="https://images.unsplash.com/photo-1758518727707-729d9bef1a16?auto=format&amp;fit=crop&amp;w=1100&amp;q=82" alt="Professionals reviewing a financial report together" width="1100" height="850" loading="lazy" decoding="async">
                    <figcaption><span>02 / DOCUMENTS AND TERMS</span><span>Review what supports an offer</span></figcaption>
                </figure>
                <figure class="home-story-visual" data-story-visual="records" aria-hidden="true">
                    <img src="https://images.unsplash.com/photo-1591696205602-2f950c417cb9?auto=format&amp;fit=crop&amp;w=1100&amp;q=82" alt="A laptop showing a financial market chart" width="1100" height="850" loading="lazy" decoding="async">
                    <figcaption><span>03 / PUBLIC-MARKET INFORMATION</span><span>Keep context beside the numbers</span></figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>

<section class="product-section" id="products">
    <div class="container-xl">
        <div class="home-section-heading">
            <div><p class="eyebrow home-eyebrow"><span></span> EXPLORE THE LANDSCAPE</p><h2>How would you like<br>to explore?</h2></div>
            <p>These are educational categories, not available financial products. Any future offer must have documented legal rights, clear risks, and applicable review.</p>
        </div>
        <div class="product-grid">
            <article class="product-card">
                <figure class="product-card-media"><img src="https://images.unsplash.com/photo-1633158829875-e5316a358c6f?auto=format&amp;fit=crop&amp;w=1000&amp;q=82" alt="A small green plant growing beside a jar of coins" width="1000" height="650" loading="lazy" decoding="async"><figcaption>Photo by Towfiqu barbhuiya · Unsplash</figcaption></figure>
                <div class="product-card-content"><span class="product-kicker">EDUCATIONAL CATEGORY</span><h3>Savings</h3>
                <p>Learn about savings and fixed-term structures. Commonwealth does not currently offer a savings account or interest product.</p>
                <a href="{{ route('how-it-works') }}">Understand the approach <span>→</span></a></div>
            </article>
            <article class="product-card">
                <figure class="product-card-media"><img src="https://images.unsplash.com/photo-1758518727707-729d9bef1a16?auto=format&amp;fit=crop&amp;w=1000&amp;q=82" alt="A small group of professionals reviewing a financial report together" width="1000" height="650" loading="lazy" decoding="async"><figcaption>Photo by Vitaly Gariev · Unsplash</figcaption></figure>
                <div class="product-card-content"><span class="product-kicker">EDUCATIONAL CATEGORY</span><h3>Mutual funds</h3>
                <p>Explore how pooled investment funds may be structured. No fund, manager relationship, or return is represented as available here.</p>
                <a href="{{ route('opportunities.index') }}">Browse reviewed opportunities <span>→</span></a></div>
            </article>
            <article class="product-card">
                <figure class="product-card-media"><img src="https://images.unsplash.com/photo-1591696205602-2f950c417cb9?auto=format&amp;fit=crop&amp;w=1000&amp;q=82" alt="A laptop displaying a stock market chart" width="1000" height="650" loading="lazy" decoding="async"><figcaption>Photo by Markus Winkler · Unsplash</figcaption></figure>
                <div class="product-card-content"><span class="product-kicker">EDUCATIONAL CATEGORY</span><h3>Stocks &amp; public markets</h3>
                <p>Learn about listed securities and market risk. Company or ticker references do not mean shares are offered or ownership is available.</p>
                <a href="{{ route('opportunities.index') }}">Explore the catalog <span>→</span></a></div>
            </article>
        </div>
    </div>
</section>

<section class="trust-section" id="trust">
    <div class="container-xl trust-grid">
        <div class="trust-emblem" aria-hidden="true"><svg viewBox="0 0 48 48" focusable="false"><path d="M24 4 39 10v11c0 10-6 18-15 23C15 39 9 31 9 21V10l15-6Z"/><path d="m17 23 5 5 10-11"/></svg></div>
        <div class="trust-copy"><p class="eyebrow"><span></span> CLARITY BEFORE CLAIMS</p><h2>Posted records.<br>Clear disclosures.</h2><p>Every displayed balance must come from posted ledger records. Opportunities stay out of the public catalog until required legal review and supporting documents are recorded.</p></div>
        <div class="trust-status"><span class="status-dot"></span><div><strong>Service availability</strong><p>Live investment offers and deposit services are not available. Commonwealth does not claim regulatory approval or securities licensing.</p><a href="{{ route('risk-disclosure') }}">Read the risk disclosure <span>→</span></a></div></div>
    </div>
</section>

<section class="calculator-section" id="calculator">
    <div class="container-xl calculator-grid">
        <div class="calculator-copy"><p class="eyebrow home-eyebrow"><span></span> PLAN WITH A CLEAR VIEW</p><h2>Small steps.<br>Longer horizons.</h2><p>Explore a savings scenario using the contribution amount, time period, and rate assumption you choose. The calculation does not use historical fund or bank performance.</p>
            <div class="calculator-disclaimer"><span>NOTE</span><p>Scenario only. The selected rate is hypothetical, not a forecast or expected return. Actual outcomes can be lower, including a loss. Fees, taxes, inflation, and contribution timing are not included.</p></div>
        </div>
        <div class="calculator-card">
            <div class="calculator-card-head"><div><span>SCENARIO CALCULATOR</span><h3>Choose your inputs</h3></div><span class="example-badge">USER-SELECTED ASSUMPTIONS</span></div>
            <label for="monthly-contribution">Monthly contribution <span>USD</span></label>
            <div class="calculator-input-wrap"><span>$</span><input id="monthly-contribution" type="number" min="25" max="100000" step="25" placeholder="Enter an amount" inputmode="decimal"></div>
            <div class="calculator-controls-row">
                <div><label for="investment-years">Time period</label><div class="select-wrap"><select id="investment-years"><option value="" selected disabled>Select a period</option><option value="3">3 years</option><option value="5">5 years</option><option value="10">10 years</option><option value="15">15 years</option><option value="20">20 years</option><option value="30">30 years</option></select></div></div>
                <div><label for="assumed-rate">Annual rate assumption</label><div class="calculator-input-wrap rate-wrap"><input id="assumed-rate" type="number" min="0" max="20" step="0.25" placeholder="Enter a rate" inputmode="decimal"><span>%</span></div></div>
            </div>
            <div class="calculator-results"><div><span>Total contributions</span><strong id="contribution-total">Not available</strong></div><div><span>Scenario estimate</span><strong id="scenario-total">Not available</strong></div></div>
            <div class="calculator-chart-wrap"><canvas id="growth-chart" aria-label="Scenario chart comparing contributions with a hypothetical user-selected rate" role="img"></canvas></div>
            <p class="calculator-chart-note">Enter your assumptions to view a scenario. Results are not a forecast or an investment offer.</p>
        </div>
    </div>
</section>

<section class="steps-section" id="resources">
    <div class="container-xl">
        <div class="home-section-heading steps-heading"><div><p class="eyebrow home-eyebrow"><span></span> A STRAIGHTFORWARD PROCESS</p><h2>Know what happens<br>at every step.</h2></div><a class="home-text-link" href="{{ route('how-it-works') }}">See how it works <span>→</span></a></div>
        <div class="steps-grid">
            <article><span>01</span><h3>Create your account</h3><p>Register and verify your email. Identity and eligibility checks may apply before any future service.</p></article>
            <article><span>02</span><h3>Review the documents</h3><p>Understand the legal structure, eligibility, risks, and supporting materials before considering an offer.</p></article>
            <article><span>03</span><h3>Follow verified records</h3><p>Any future account activity must reconcile to transaction records and the financial ledger.</p></article>
        </div>
    </div>
</section>

<section class="closing-section">
    <div class="container-xl closing-inner"><div><p class="eyebrow"><span></span> INVEST WITH INFORMATION</p><h2>Start with a clearer view.</h2><p>Explore what the platform is building and the safeguards required before live services can begin.</p></div><a class="button button-gold" href="{{ route('opportunities.index') }}">Explore categories <span>→</span></a></div>
</section>
@endsection