<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Commonwealth'))</title>
    <meta name="description" content="@yield('description', 'An international investment and asset management platform.')">
    @if (request()->routeIs('terms', 'privacy', 'aml-kyc'))<meta name="robots" content="noindex, nofollow">@endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/commonwealth-mark.svg') }}">
    @vite(['resources/css/app.css', 'resources/css/fintechx.css', 'resources/js/app.js'])
</head>
<body class="fintechx-site @yield('body-class')">
<header class="site-header">
    <nav class="site-nav container-xl" aria-label="Main navigation">
        <a class="site-brand" href="{{ route('home') }}"><x-commonwealth-brand /></a>
        <button class="site-nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav-menu" aria-label="Open navigation"><span></span><span></span><span></span></button>
        <div class="site-nav-menu" id="site-nav-menu">
            <div class="site-nav-links">
                <a href="{{ route('investments.index') }}">Explore assets</a>
                <a href="{{ route('opportunities.index') }}">Reviewed offers</a>
                <a href="{{ route('faq') }}">Resources</a>
                <a href="{{ route('about') }}">Company</a>
            </div>
            <div class="site-nav-actions">
            @auth
                <a class="nav-dashboard" href="{{ auth()->user()->hasPermission('admin.dashboard.view') ? route('admin.dashboard') : route('investor.dashboard') }}">Dashboard</a>
                <a class="nav-signin" href="{{ route('password.change') }}">Change password</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-signin" type="submit">Sign out</button></form>
            @else
                <a class="nav-signin" href="{{ route('login') }}">Sign in</a>
                @if (Route::has('register'))<a class="nav-join" href="{{ route('register') }}">Create account <span>↗</span></a>@endif
            @endauth
            </div>
        </div>
    </nav>
</header>
@if (session('status'))
    <div class="container-xl"><div class="alert alert-success mt-3 mb-0">{{ session('status') }}</div></div>
@endif
<main>@yield('content')</main>
<footer class="site-footer">
    <div class="container-xl footer-layout">
        <div class="footer-brand-block">
            <a class="site-brand footer-brand" href="{{ route('home') }}"><x-commonwealth-brand /></a>
            <p>Investment involves risk, including possible loss of capital. Opportunities appear only after required legal review.</p>
        </div>
        <nav class="footer-column" aria-label="Platform links">
            <h2>Platform</h2>
            <a href="{{ route('investments.index') }}">Explore asset profiles</a>
            <a href="{{ route('opportunities.index') }}">Opportunities</a>
            <a href="{{ route('how-it-works') }}">How it works</a>
            <a href="{{ route('payment-methods') }}">Payment information</a>
        </nav>
        <nav class="footer-column" aria-label="Resources links">
            <h2>Resources</h2>
            <a href="{{ route('faq') }}">FAQs</a>
            <a href="{{ route('risk-disclosure') }}">Risk disclosure</a>
            <a href="{{ route('terms') }}">Terms</a>
            <a href="{{ route('privacy') }}">Privacy</a>
            <a href="{{ route('aml-kyc') }}">AML/KYC</a>
        </nav>
        <nav class="footer-column footer-contact" aria-label="Contact links">
            <h2>Contact</h2>
            <a href="{{ route('contact') }}">Contact form</a>
            <a class="telegram-footer-link" href="https://t.me/JaredBirchall673" target="_blank" rel="noopener noreferrer">
                <svg class="telegram-mark" viewBox="0 0 24 24" focusable="false" aria-hidden="true"><circle cx="12" cy="12" r="12"/><path d="m19.9 5.1-2.8 13.2c-.2.9-.8 1.1-1.5.7l-4.2-3.1-2 1.9c-.2.2-.4.4-.8.4l.3-4.3 7.8-7.1c.4-.3-.1-.5-.5-.2l-9.7 6.1-4.2-1.3c-.9-.3-.9-.9.2-1.3l16.2-6.2c.8-.3 1.3.2 1.2 1.2ZM9.8 14l7.6-5.8c.3-.2.6.1.3.3l-6.3 6.1-.3 2.3L9.8 14Z"/></svg>
                <span>Message admin on Telegram</span>
                <span class="footer-external-mark" aria-hidden="true">↗</span>
            </a>
        </nav>
        <div class="footer-bottom"><span>© {{ now()->year }} Commonwealth</span><span>This platform is not currently accepting live investments.</span></div>
    </div>
</footer>
</body>
</html>
