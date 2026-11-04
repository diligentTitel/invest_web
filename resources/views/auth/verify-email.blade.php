@extends('layouts.app')
@section('title', 'Verify your email | Commonwealth')
@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow dark-eyebrow"><span></span> ACCOUNT SECURITY</p><h1>Check your inbox</h1><p class="auth-subtitle">Use the verification link we sent you to finish setting up your account. You need a verified email to access the investor portal.</p>
@if (session('status') === 'verification-link-sent')<div class="alert alert-success">A fresh verification link has been sent.</div>@endif
<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button button-navy w-100" type="submit">Resend verification email <span>→</span></button></form>
<form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf<button class="button button-outline-dark w-100" type="submit">Sign out</button></form></div></section>
@endsection