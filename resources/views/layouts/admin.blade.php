@extends('layouts.base')
@section('page')
    <main class="container @yield('width')">
        @if (session('status'))<p class="flash" role="status">{{ session('status') }}</p>@endif
        @yield('content')
    </main>
@endsection
