@extends('layouts.base')
@section('title', $project->name)
@section('brand', $project->brand_color)
@section('public', true)
@section('page')
    <main class="public-page">
        <div class="sheet">
            <p class="from">{{ $project->name }}</p>
            <h1>{{ $project->formPrompt() }}</h1>
            <p class="lede">A few honest sentences help other people decide. It takes about a minute.</p>
            <form method="post" action="{{ route('submit.store', $project) }}" enctype="multipart/form-data" class="stack">
                @csrf
                @include('layouts.errors')
                <fieldset class="rating">
                    <legend>How would you rate it? <span class="hint">Optional</span></legend>
                    <div class="row">
                        @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" @checked(old('rating') == $i)>
                            <label for="rating-{{ $i }}" title="{{ $i }} {{ Str::plural('star', $i) }}">★<span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span></label>
                        @endfor
                    </div>
                </fieldset>
                <label>Your testimonial
                    <textarea name="body" rows="5" required minlength="10" maxlength="2000" placeholder="What did you like? What changed for you?">{{ old('body') }}</textarea></label>
                <label>Your name <input name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></label>
                <label>Job title and company <span class="hint">Optional, for example “Founder at Acme”</span>
                    <input name="title" value="{{ old('title') }}" maxlength="120" autocomplete="organization-title"></label>
                <label>Email <span class="hint">Optional. Only {{ $project->name }} sees it.</span>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email"></label>
                <label>Photo <span class="hint">Optional</span>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"></label>
                <div class="hp" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
                <div class="fine-print stack">
                    <label class="check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
                        {{ $project->name }} may show my testimonial, name and photo publicly.</label>
                    <div><button class="btn brand">Send testimonial</button></div>
                </div>
            </form>
        </div>
    </main>
@endsection
