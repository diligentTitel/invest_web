@extends('layouts.app')
@section('title', 'Change password | Commonwealth')
@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow dark-eyebrow"><span></span> ACCOUNT SECURITY</p><h1>Change your password</h1><p class="auth-subtitle">Enter your current password and choose a new password with at least 16 characters.</p>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.change.update') }}">@csrf @method('PUT')
<label for="current_password">Current password</label><input id="current_password" class="form-control" type="password" name="current_password" autocomplete="current-password" required>
<label for="password">New password</label><input id="password" class="form-control" type="password" name="password" autocomplete="new-password" minlength="16" required>
<label for="password_confirmation">Confirm new password</label><input id="password_confirmation" class="form-control" type="password" name="password_confirmation" autocomplete="new-password" minlength="16" required>
<button class="button button-navy w-100" type="submit">Update password <span>→</span></button></form></div></section>
@endsection
