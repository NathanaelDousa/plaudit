@extends('layouts.admin')
@section('title', $project->exists ? 'Edit '.$project->name : 'New project')
@section('width', 'narrow')
@section('content')
    <a class="back" href="{{ $project->exists ? route('projects.show', $project) : route('projects.index') }}">← {{ $project->exists ? $project->name : 'Projects' }}</a>
    <div class="sheet">
        <h1>{{ $project->exists ? 'Edit project' : 'New project' }}</h1>
        <form method="post" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}" class="stack" style="margin-top:1.5rem">
            @csrf
            @if ($project->exists) @method('put') @endif
            @include('layouts.errors')
            <label>Name <span class="hint">Your product, business or website</span>
                <input name="name" value="{{ old('name', $project->name) }}" required maxlength="100"></label>
            <label>Question on the form <span class="hint">Optional. Customers see this as the headline.</span>
                <input name="prompt" value="{{ old('prompt', $project->prompt) }}" maxlength="200" placeholder="What do you think of {{ $project->name ?: 'us' }}?"></label>
            <label>Brand color <span class="hint">Used on your form, wall and widget</span>
                <input type="color" name="brand_color" value="{{ old('brand_color', $project->brand_color ?? '#3346d3') }}"></label>
            <div><button class="btn">{{ $project->exists ? 'Save changes' : 'Create project' }}</button></div>
        </form>
    </div>
    @if ($project->exists)
        <div class="sheet danger-zone">
            <h2>Delete project</h2>
            <p class="muted">Deletes {{ $project->name }} with all its testimonials and photos. Export them first if you want a copy.</p>
            <form method="post" action="{{ route('projects.destroy', $project) }}"
                  onsubmit="return confirm(@js('Delete '.$project->name.' and all its testimonials for good? This can’t be undone.'))">
                @csrf @method('delete')
                <button class="btn quiet sm" style="color:var(--danger)">Delete project</button>
            </form>
        </div>
    @endif
@endsection
