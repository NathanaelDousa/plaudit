@extends('layouts.base')
@section('title', $project->exists ? 'Edit project' : 'New project')
@section('main_class', 'container narrow')
@section('content')
    <p><a href="{{ $project->exists ? route('projects.show', $project) : route('projects.index') }}">← Back</a></p>
    <div class="card">
        <h1>{{ $project->exists ? 'Edit project' : 'New project' }}</h1>
        @include('layouts.errors')
        <form method="post" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}" class="stack">
            @csrf
            @if ($project->exists) @method('put') @endif
            <label>Name <span class="hint">Your product, business or website</span>
                <input name="name" value="{{ old('name', $project->name) }}" required maxlength="100"></label>
            <label>Question on the form <span class="hint">Optional</span>
                <input name="prompt" value="{{ old('prompt', $project->prompt) }}" maxlength="200" placeholder="What do you think of {{ $project->name ?: 'us' }}?"></label>
            <label>Brand color
                <input type="color" name="brand_color" value="{{ old('brand_color', $project->brand_color ?? '#4f46e5') }}"></label>
            <button class="btn">{{ $project->exists ? 'Save' : 'Create project' }}</button>
        </form>
    </div>
    @if ($project->exists)
        <div class="card danger-zone">
            <h2>Delete project</h2>
            <p class="muted">Deletes {{ $project->name }} and all its testimonials and photos. Export them first if you want a copy.</p>
            <form method="post" action="{{ route('projects.destroy', $project) }}"
                  onsubmit="return confirm(@js('Delete '.$project->name.' and all its testimonials for good? This can’t be undone.'))">
                @csrf @method('delete')
                <button class="btn danger">Delete project</button>
            </form>
        </div>
    @endif
@endsection
