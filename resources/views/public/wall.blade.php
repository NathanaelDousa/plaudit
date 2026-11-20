@extends('layouts.base')
@section('title', 'What people say about '.$project->name)
@section('brand', $project->brand_color)
@section('body_class', 'public wall-page')
@section('main_class', 'container wide')
@section('content')
    <header class="wall-head">
        <h1>What people say about {{ $project->name }}</h1>
        @if ($testimonials->isNotEmpty())
            @php($rated = $testimonials->whereNotNull('rating'))
            <p class="muted">
                {{ $testimonials->count() }} {{ Str::plural('testimonial', $testimonials->count()) }}@if ($rated->isNotEmpty()) · <span class="stars">★</span> {{ number_format($rated->avg('rating'), 1) }} average @endif
            </p>
        @endif
    </header>
    @if ($testimonials->isEmpty())
        <div class="card empty"><p class="muted">No testimonials yet.</p></div>
    @else
        <div class="wall">
            @foreach ($testimonials as $t)
                <figure class="card testimonial">
                    @if ($t->rating)<div class="stars" aria-label="{{ $t->rating }} out of 5 stars">{{ str_repeat('★', $t->rating) }}</div>@endif
                    <blockquote class="body">{{ $t->body }}</blockquote>
                    <figcaption>
                        @if ($t->avatar_path)<img class="avatar" src="{{ asset($t->avatar_path) }}" alt="" loading="lazy">@else<span class="avatar initial" aria-hidden="true">{{ mb_substr($t->name, 0, 1) }}</span>@endif
                        <span><strong>{{ $t->name }}</strong>@if ($t->title)<br><span class="muted small">{{ $t->title }}</span>@endif</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
    <p class="center small muted powered">Collected with {{ config('app.name') }}</p>
@endsection
