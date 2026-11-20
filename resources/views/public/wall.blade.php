@extends('layouts.base')
@section('title', 'What people say about '.$project->name)
@section('brand', $project->brand_color)
@section('public', true)
@section('page')
    <main class="container wide">
        <header class="wall-head">
            <h1>What people say about {{ $project->name }}</h1>
            @if ($testimonials->isNotEmpty())
                @php($rated = $testimonials->whereNotNull('rating'))
                <p>
                    <span>{{ $testimonials->count() }} {{ Str::plural('testimonial', $testimonials->count()) }}</span>
                    @if ($rated->isNotEmpty())<span><span class="sticker" aria-hidden="true">★</span> {{ number_format($rated->avg('rating'), 1) }} out of 5 on average</span>@endif
                </p>
            @endif
        </header>
        @if ($testimonials->isEmpty())
            <div class="empty"><p class="muted">No testimonials published yet.</p></div>
        @else
            <div class="wall">
                @foreach ($testimonials as $t)
                    <figure class="{{ $t->featured ? 'featured' : '' }}">
                        @if ($t->rating)<span class="sticker" role="img" aria-label="{{ $t->rating }} out of 5 stars">{{ str_repeat('★', $t->rating) }}</span>@endif
                        <blockquote class="quote" style="margin:0">{{ $t->body }}</blockquote>
                        <figcaption>
                            @if ($t->avatar_path)<img class="avatar" src="{{ asset($t->avatar_path) }}" alt="" loading="lazy">@else<span class="avatar initial" aria-hidden="true">{{ mb_substr($t->name, 0, 1) }}</span>@endif
                            <span><strong>{{ $t->name }}</strong>@if ($t->title)<small>{{ $t->title }}</small>@endif</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @endif
        <p class="powered">Collected with {{ config('app.name') }}</p>
    </main>
@endsection
