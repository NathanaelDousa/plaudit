<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/newsreader.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body @hasSection('brand') style="--brand: @yield('brand')" @endif>
    @if (! View::hasSection('public'))
        @auth
            <header class="topbar">
                <a class="wordmark" href="{{ route('projects.index') }}"><span class="sticker" aria-hidden="true">★</span>{{ config('app.name') }}</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="link">Log out</button></form>
            </header>
        @endauth
    @endif
    @yield('page')
</body>
</html>
