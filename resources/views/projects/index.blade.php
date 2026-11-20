@extends('layouts.base')
@section('title', 'Projects')
@section('content')
    <div class="row-between">
        <h1>Projects</h1>
        <a class="btn" href="{{ route('projects.create') }}">New project</a>
    </div>
    @forelse ($projects as $project)
        <a class="card project-link" href="{{ route('projects.show', $project) }}">
            <span class="swatch" style="background: {{ $project->brand_color }}"></span>
            <strong>{{ $project->name }}</strong>
            <span class="muted">{{ trans_choice('{0} No testimonials yet|{1} 1 testimonial|[2,*] :count testimonials', $project->testimonials_count) }}</span>
        </a>
    @empty
        <div class="card empty">
            <p><strong>No projects yet.</strong></p>
            <p class="muted">A project is one product or website you collect testimonials for.</p>
            <a class="btn" href="{{ route('projects.create') }}">Create your first project</a>
        </div>
    @endforelse
@endsection
