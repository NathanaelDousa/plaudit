@extends('layouts.base')
@section('title', 'Thank you')
@section('brand', $project->brand_color)
@section('body_class', 'public')
@section('main_class', 'container narrow')
@section('content')
    <div class="card center">
        <div class="big-star" aria-hidden="true">★</div>
        <h1>Thank you!</h1>
        <p class="muted">Your testimonial was sent to {{ $project->name }}.</p>
    </div>
@endsection
