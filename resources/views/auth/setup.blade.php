@extends('layouts.base')
@section('title', 'Welcome')
@section('main_class', 'container narrow')
@section('content')
    <div class="card">
        <h1>Welcome to {{ config('app.name') }}</h1>
        <p class="muted">Create the owner account. You’ll use it to manage your testimonials.</p>
        @include('layouts.errors')
        <form method="post" action="{{ route('setup.store') }}" class="stack">
            @csrf
            <label>Your name <input name="name" value="{{ old('name') }}" required autocomplete="name"></label>
            <label>Email <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
            <label>Password <span class="hint">At least 10 characters</span>
                <input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
            <label>Repeat password <input type="password" name="password_confirmation" required autocomplete="new-password"></label>
            <button class="btn">Create account</button>
        </form>
    </div>
@endsection
