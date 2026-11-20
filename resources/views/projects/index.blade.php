@extends('layouts.admin')
@section('title', 'Projects')
@section('content')
    <div class="page-head">
        <div>
            <h1>Projects</h1>
            <p class="muted">Each project is one product or website you collect praise for.</p>
        </div>
        @if ($projects->isNotEmpty())<a class="btn" href="{{ route('projects.create') }}">New project</a>@endif
    </div>
    @if ($projects->isEmpty())
        <div class="empty">
            <h2>Start your first project</h2>
            <p class="muted">You’ll get a link to send customers, and a page that shows what they say.</p>
            <a class="btn" href="{{ route('projects.create') }}">New project</a>
        </div>
    @else
        <ul class="project-list">
            @foreach ($projects as $project)
                <li>
                    <a href="{{ route('projects.show', $project) }}">
                        <span class="swatch" style="background: {{ $project->brand_color }}"></span>
                        <span class="project-name">{{ $project->name }}</span>
                        <span class="project-stats">
                            @if ($project->pending_count)<span class="waiting">{{ $project->pending_count }} to review</span>@endif
                            <span>{{ $project->approved_count }} published</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
