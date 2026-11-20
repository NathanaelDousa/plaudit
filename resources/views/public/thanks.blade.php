@extends('layouts.base')
@section('title', 'Thank you')
@section('brand', $project->brand_color)
@section('public', true)
@section('page')
    <main class="public-page">
        <div class="sheet thanks">
            <span class="sticker" aria-hidden="true">★</span>
            <h1>Thank you</h1>
            <p class="muted">{{ $project->name }} will read it soon. You can close this page.</p>
        </div>
    </main>
@endsection
