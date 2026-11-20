@extends('layouts.base')
@section('title', 'Log in')
@section('main_class', 'container narrow')
@section('content')
    <div class="card">
        <h1>Log in</h1>
        @include('layouts.errors')
        <form method="post" action="{{ route('login') }}" class="stack">
            @csrf
            <label>Email <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus></label>
            <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
            <button class="btn">Log in</button>
        </form>
    </div>
@endsection
