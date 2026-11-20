@extends('layouts.base')
@section('title', 'Add testimonial')
@section('main_class', 'container narrow')
@section('content')
    <p><a href="{{ route('projects.show', $project) }}">← {{ $project->name }}</a></p>
    <div class="card">
        <h1>Add a testimonial</h1>
        <p class="muted">For praise you already received by email, chat or social media. Paste the customer’s words as they wrote them.</p>
        @include('layouts.errors')
        <form method="post" action="{{ route('projects.testimonials.store', $project) }}" enctype="multipart/form-data" class="stack">
            @csrf
            <label>Testimonial <textarea name="body" rows="5" required maxlength="2000">{{ old('body') }}</textarea></label>
            <label>Name <input name="name" value="{{ old('name') }}" required maxlength="100"></label>
            <label>Job title and company <span class="hint">Optional</span><input name="title" value="{{ old('title') }}" maxlength="120"></label>
            <label>Rating <span class="hint">Optional, 1–5</span><input type="number" name="rating" min="1" max="5" value="{{ old('rating') }}"></label>
            <label>Photo <span class="hint">Optional</span><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"></label>
            <label class="check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> This person agreed to have their testimonial shown publicly.</label>
            <button class="btn">Add and approve</button>
        </form>
    </div>
@endsection
