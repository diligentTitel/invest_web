@extends('layouts.app')
@section('title', 'Choose a new password | Commonwealth')
@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow dark-eyebrow"><span></span> ACCOUNT SECURITY</p><h1>Choose a new password</h1>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}">
<label for="email">Email address</label><input id="email" class="form-control" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
<label for="password">New password</label><input id="password" class="form-control" type="password" name="password" autocomplete="new-password" minlength="12" required>
<label for="password_confirmation">Confirm new password</label><input id="password_confirmation" class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required>
<button class="button button-navy w-100" type="submit">Update password <span>→</span></button></form></div></section>
@endsection