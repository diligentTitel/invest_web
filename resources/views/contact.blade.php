@extends('layouts.app')
@section('title', 'Contact | Commonwealth')
@section('content')
<section class="page-hero"><div class="container-xl"><p class="eyebrow"><span></span> CONTACT</p><h1>Start a conversation.</h1><p>Send a question to Commonwealth. Do not include passwords, payment credentials, or private keys.</p></div></section>
<section class="container-xl info-wrap contact-wrap"><div class="contact-card"><p class="eyebrow dark-eyebrow"><span></span> {{ Route::has('contact.store') ? 'CONTACT FORM' : 'CONTACT' }}</p><h2>How can we help?</h2>
@if (session('status'))<div class="alert alert-success mt-3">{{ session('status') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif
@if (Route::has('contact.store'))<form method="POST" action="{{ route('contact.store') }}">@csrf
<label for="name">Name</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" required maxlength="120">
<label for="email">Email</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="190">
<label for="topic">Topic</label><select class="form-select" id="topic" name="topic" required><option value="">Select a topic</option><option>Account access</option><option>Investment categories</option><option>Business inquiry</option><option>Privacy or security</option><option>Other</option></select>
<label for="message">Message</label><textarea class="form-control" id="message" name="message" rows="6" required minlength="15" maxlength="5000">{{ old('message') }}</textarea>
<button class="button button-navy" type="submit">Send message <span>→</span></button></form>@else<div class="draft-notice"><strong>Contact form unavailable</strong><span>Use the administrator’s Telegram link to reach the project contact.</span></div>@endif<div class="contact-social"><div><strong>Prefer Telegram?</strong><p>Reach the administrator directly with a platform question.</p></div><a href="https://t.me/JaredBirchall673" target="_blank" rel="noopener noreferrer"><svg class="telegram-mark" viewBox="0 0 24 24" focusable="false" aria-hidden="true"><circle cx="12" cy="12" r="12"/><path d="m19.9 5.1-2.8 13.2c-.2.9-.8 1.1-1.5.7l-4.2-3.1-2 1.9c-.2.2-.4.4-.8.4l.3-4.3 7.8-7.1c.4-.3-.1-.5-.5-.2l-9.7 6.1-4.2-1.3c-.9-.3-.9-.9.2-1.3l16.2-6.2c.8-.3 1.3.2 1.2 1.2ZM9.8 14l7.6-5.8c.3-.2.6.1.3.3l-6.3 6.1-.3 2.3L9.8 14Z"/></svg><span>Message on Telegram</span><span class="telegram-external" aria-hidden="true">↗</span></a></div></div></section>
@endsection