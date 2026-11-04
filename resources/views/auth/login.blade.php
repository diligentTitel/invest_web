@extends('layouts.app')
@section('title', 'Sign in | Commonwealth')
@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow dark-eyebrow"><span></span> INVESTOR PORTAL</p><h1>Welcome back</h1><p class="auth-subtitle">Sign in to access your verified account records.</p>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('login.store') }}">@csrf
<label for="email">Email address</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
<label for="password">Password</label><input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required>
<div class="form-row"><label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label><a href="{{ route('password.request') }}">Forgot password?</a></div>
<button class="button button-navy w-100" type="submit">Sign in <span>→</span></button></form>
@if (Route::has('register'))<p class="auth-bottom">New here? <a href="{{ route('register') }}">Create an account</a></p>@endif
</div></section>
@endsection