@extends('layouts.base')
@section('title', 'Log in')
@section('page')
    <main class="auth">
        <div class="sheet">
            <p class="wordmark"><span class="sticker" aria-hidden="true">★</span>{{ config('app.name') }}</p>
            <h1 style="margin-top:1.25rem">Log in</h1>
            <form method="post" action="{{ route('login') }}" class="stack">
                @csrf
                @include('layouts.errors')
                <label>Email <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus></label>
                <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
                <label class="check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
                <button class="btn">Log in</button>
            </form>
        </div>
    </main>
@endsection
