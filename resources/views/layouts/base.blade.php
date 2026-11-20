<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="@yield('body_class')" @hasSection('brand') style="--brand: @yield('brand')" @endif>
    @auth
        <header class="topbar">
            <a class="logo" href="{{ route('projects.index') }}">★ {{ config('app.name') }}</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="link">Log out</button></form>
        </header>
    @endauth
    <main class="@yield('main_class', 'container')">
        @if (session('status'))<p class="flash" role="status">{{ session('status') }}</p>@endif
        @yield('content')
    </main>
</body>
</html>
