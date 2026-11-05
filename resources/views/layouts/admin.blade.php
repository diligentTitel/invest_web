<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration | Commonwealth')</title>
    <meta name="description" content="@yield('description', 'Commonwealth administrative workspace.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/commonwealth-mark.svg') }}">
    @vite(['resources/css/app.css', 'resources/css/fintechx.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="fintechx-site admin-shell">
<a class="admin-skip-link" href="#admin-main">Skip to main content</a>

<button class="admin-shell__backdrop" type="button" data-admin-backdrop aria-label="Close navigation"></button>

<aside class="admin-sidebar" id="admin-sidebar" aria-label="Administration navigation">
    <a class="admin-sidebar__brand" href="{{ route('admin.dashboard') }}">
        <x-commonwealth-brand />
    </a>
    <p class="admin-sidebar__caption">COMMONWEALTH<br><strong>ADMINISTRATION</strong></p>

    <nav class="admin-nav">
        @if(auth()->user()->hasPermission('admin.dashboard.view'))
            <div class="admin-nav__group">
                <p class="admin-nav__label">Overview</p>
                <a class="admin-nav__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                   href="{{ route('admin.dashboard') }}"
                   @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                   title="Dashboard overview">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    <span>Dashboard overview</span>
                </a>
            </div>
        @endif

        @if(auth()->user()->hasPermission('investors.view'))
            <div class="admin-nav__group">
                <p class="admin-nav__label">User management</p>
                <a class="admin-nav__link {{ request()->routeIs('admin.investors.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.investors.index') }}"
                   @if(request()->routeIs('admin.investors.*')) aria-current="page" @endif
                   title="Manage investors">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 20v-1.5a5.5 5.5 0 0 1 11 0V20z"/><path d="M16 5.5a3 3 0 0 1 0 5.8M17 14a4.5 4.5 0 0 1 3.5 4.4V20"/></svg>
                    <span>Investors</span>
                </a>
            </div>
        @endif

        @if(auth()->user()->hasPermission('ledger.view'))
            <div class="admin-nav__group">
                <p class="admin-nav__label">Financial records</p>
                <a class="admin-nav__link {{ request()->routeIs('admin.transactions.*') ? 'is-active' : '' }}"
                   href="{{ route('admin.transactions.index') }}"
                   @if(request()->routeIs('admin.transactions.*')) aria-current="page" @endif
                   title="Transaction history">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="m3 6 1 1 2-2M3 12l1 1 2-2M3 18l1 1 2-2"/></svg>
                    <span>Transaction history</span>
                </a>
            </div>
        @endif
        @if(auth()->user()->hasPermission('deposits.review') || auth()->user()->hasPermission('withdrawals.review'))
            <div class="admin-nav__group">
                <p class="admin-nav__label">Request review</p>
                @if(auth()->user()->hasPermission('deposits.review'))
                    <a class="admin-nav__link {{ request()->routeIs('admin.deposits.*') ? 'is-active' : '' }}" href="{{ route('admin.deposits.index') }}" @if(request()->routeIs('admin.deposits.*')) aria-current="page" @endif title="Review deposit requests">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M17 8.5c0-1.7-2.2-3-5-3S7 6.8 7 8.5s2.2 3 5 3 5 1.3 5 3-2.2 3.5-5 3.5-5-1.5-5-3.5"/></svg><span>Deposits</span>
                    </a>
                @endif
                @if(auth()->user()->hasPermission('withdrawals.review'))
                    <a class="admin-nav__link {{ request()->routeIs('admin.withdrawals.*') ? 'is-active' : '' }}" href="{{ route('admin.withdrawals.index') }}" @if(request()->routeIs('admin.withdrawals.*')) aria-current="page" @endif title="Review withdrawal requests">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h13v13M20 7 9 18M4 4v8M4 12h8"/></svg><span>Withdrawals</span>
                    </a>
                @endif
            </div>
        @endif
    </nav>

    <div class="admin-sidebar__bottom">
        <span class="admin-sidebar__status"><i aria-hidden="true"></i> Pre-launch environment</span>
        <div class="admin-sidebar__identity">
            <span class="admin-sidebar__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="admin-sidebar__identity-text"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role?->name ?? 'Administrator' }}</small></span>
        </div>
        <a class="admin-sidebar__utility-link" href="{{ route('home') }}"><span aria-hidden="true">&#8599;</span> View public site</a>
    </div>
</aside>

<div class="admin-shell__main">
    <header class="admin-topbar">
        <div class="admin-topbar__left">
            <button class="admin-icon-button admin-menu-toggle" type="button" data-admin-mobile-toggle aria-expanded="false" aria-controls="admin-sidebar" aria-label="Open administration navigation">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button class="admin-icon-button admin-collapse-toggle" type="button" data-admin-collapse-toggle aria-expanded="true" aria-label="Collapse sidebar" title="Collapse sidebar">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="admin-topbar__heading"><span>COMMONWEALTH</span><strong>Administration</strong></div>
        </div>
        <div class="admin-topbar__actions">
            <span class="admin-topbar__user">{{ auth()->user()->name }}</span>
            <a class="admin-topbar__signout" href="{{ route('password.change') }}">Change password</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="admin-topbar__signout" type="submit">Sign out</button></form>
        </div>
    </header>

    <main class="admin-shell__content" id="admin-main" tabindex="-1">
        @if(session('status'))
            <div class="admin-shell__notice" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
