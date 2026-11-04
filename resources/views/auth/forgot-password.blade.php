@extends('layouts.app')
@section('title', 'Reset password | Commonwealth')
@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow dark-eyebrow"><span></span> ACCOUNT SECURITY</p><h1>Reset your password</h1><p class="auth-subtitle">We’ll email a password reset link if an account matches the address.</p>
@if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf<label for="email">Email address</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required><button class="button button-navy w-100" type="submit">Send reset link <span>→</span></button></form>
<p class="auth-bottom"><a href="{{ route('login') }}">Return to sign in</a></p></div></section>
@endsection