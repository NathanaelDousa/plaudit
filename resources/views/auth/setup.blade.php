@extends('layouts.base')
@section('title', 'Set up')
@section('page')
    <main class="auth">
        <div class="sheet">
            <p class="wordmark"><span class="sticker" aria-hidden="true">★</span>{{ config('app.name') }}</p>
            <h1 style="margin-top:1.25rem">Create your account</h1>
            <p class="muted">You’ll use it to review testimonials and choose which ones go public.</p>
            <form method="post" action="{{ route('setup.store') }}" class="stack">
                @csrf
                @include('layouts.errors')
                <label>Your name <input name="name" value="{{ old('name') }}" required autocomplete="name"></label>
                <label>Email <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
                <label>Password <span class="hint">At least 10 characters</span>
                    <input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
                <label>Repeat password <input type="password" name="password_confirmation" required autocomplete="new-password"></label>
                <button class="btn">Create account</button>
            </form>
        </div>
    </main>
@endsection
