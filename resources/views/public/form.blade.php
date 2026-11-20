@extends('layouts.base')
@section('title', $project->name)
@section('brand', $project->brand_color)
@section('body_class', 'public')
@section('main_class', 'container narrow')
@section('content')
    <div class="card">
        <h1>{{ $project->formPrompt() }}</h1>
        <p class="muted">Your words help others decide. It takes about a minute.</p>
        @include('layouts.errors')
        <form method="post" action="{{ route('submit.store', $project) }}" enctype="multipart/form-data" class="stack">
            @csrf
            <fieldset class="rating">
                <legend>Rating <span class="hint">Optional</span></legend>
                @for ($i = 5; $i >= 1; $i--)
                    <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" @checked(old('rating') == $i)>
                    <label for="rating-{{ $i }}" title="{{ $i }} {{ Str::plural('star', $i) }}">★<span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span></label>
                @endfor
            </fieldset>
            <label>Your testimonial
                <textarea name="body" rows="5" required minlength="10" maxlength="2000">{{ old('body') }}</textarea></label>
            <label>Your name <input name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></label>
            <label>Job title and company <span class="hint">Optional, e.g. “Founder, Acme”</span>
                <input name="title" value="{{ old('title') }}" maxlength="120" autocomplete="organization-title"></label>
            <label>Email <span class="hint">Optional, never shown publicly</span>
                <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email"></label>
            <label>Photo <span class="hint">Optional</span>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"></label>
            <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
            <label class="check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
                I agree that {{ $project->name }} may show my testimonial, name and photo publicly.</label>
            <button class="btn">Send testimonial</button>
        </form>
    </div>
@endsection
