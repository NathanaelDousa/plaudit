@extends('layouts.base')
@section('title', $project->name)
@section('content')
    <p><a href="{{ route('projects.index') }}">← Projects</a></p>
    <div class="row-between">
        <h1>{{ $project->name }}</h1>
        <a class="btn secondary" href="{{ route('projects.edit', $project) }}">Edit</a>
    </div>

    <div class="card">
        <h2>Collect testimonials</h2>
        <p class="muted">Share this link with your customers. They don’t need an account.</p>
        <div class="copy-row">
            <input id="form-link" readonly value="{{ route('submit', $project) }}" aria-label="Form link">
            <button class="btn secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('form-link').value); this.textContent='Copied'">Copy</button>
            <a class="btn secondary" href="{{ route('submit', $project) }}" target="_blank" rel="noopener">Open</a>
        </div>
        <p class="small muted">Approved testimonials appear on your <a href="{{ route('wall', $project) }}" target="_blank" rel="noopener">wall of love</a>.</p>
    </div>

    <details class="card embed">
        <summary><h2>Show testimonials on your website</h2></summary>
        <p class="muted">Paste this where the testimonials should appear. Only approved testimonials are shown.</p>
        <div class="segmented" role="group" aria-label="Layout">
            <button type="button" class="btn secondary sm" aria-pressed="true" data-layout="grid">Grid</button>
            <button type="button" class="btn secondary sm" aria-pressed="false" data-layout="carousel">Carousel</button>
        </div>
        <textarea id="embed-code" readonly rows="3" aria-label="Embed code"></textarea>
        <p><button class="btn secondary sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('embed-code').value); this.textContent='Copied'">Copy code</button></p>
        <p class="small muted">Options: <code>data-theme="light"</code>, <code>"dark"</code> or <code>"auto"</code>, and <code>data-limit="6"</code>.</p>
        <h3 class="small muted">Preview</h3>
        <div id="embed-preview"></div>
    </details>
    <script>
        (function () {
            var slug = @json($project->slug), src = @json(asset('widget.js'));
            var code = document.getElementById('embed-code'), preview = document.getElementById('embed-preview');
            function show(layout) {
                code.value = '<div data-plaudit="' + slug + '" data-layout="' + layout + '"></div>\n<script src="' + src + '" async><\/script>';
                var box = document.createElement('div');
                box.setAttribute('data-plaudit', slug);
                box.setAttribute('data-layout', layout);
                preview.replaceChildren(box);
                var s = document.createElement('script');
                s.src = src + '?' + Date.now();
                preview.appendChild(s);
                document.querySelectorAll('.segmented button').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.layout === layout); });
            }
            document.querySelectorAll('.segmented button').forEach(function (b) { b.addEventListener('click', function () { show(b.dataset.layout); }); });
            show('grid');
        })();
    </script>

    <div class="toolbar">
        <a class="btn secondary" href="{{ route('projects.testimonials.create', $project) }}">Add manually</a>
        <details class="dropdown">
            <summary class="btn secondary">Import CSV</summary>
            <form method="post" action="{{ route('projects.import', $project) }}" enctype="multipart/form-data" class="card stack">
                @csrf
                <p class="small muted">First row must be column names. Required: <code>name</code>, <code>body</code>. Optional: <code>title</code>, <code>email</code>, <code>rating</code>. Imports go to Pending.</p>
                <input type="file" name="file" accept=".csv,text/csv" required aria-label="CSV file">
                <label class="check"><input type="checkbox" name="consent" value="1" required> These people agreed to have their testimonials shown publicly.</label>
                <button class="btn">Import</button>
            </form>
        </details>
        <span class="muted small">Export:</span>
        <a href="{{ route('projects.export', [$project, 'csv']) }}">CSV</a>
        <a href="{{ route('projects.export', [$project, 'json']) }}">JSON</a>
    </div>
    @include('layouts.errors')

    <nav class="tabs" aria-label="Filter testimonials">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'hidden' => 'Hidden', 'all' => 'All'] as $key => $label)
            <a href="{{ route('projects.show', [$project, 'status' => $key]) }}" @if ($status === $key) aria-current="page" @endif>
                {{ $label }} <span class="count">{{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
            </a>
        @endforeach
    </nav>

    @forelse ($testimonials as $t)
        <article class="card testimonial {{ $t->featured ? 'is-featured' : '' }}">
            <header>
                @if ($t->avatar_path)<img class="avatar" src="{{ asset($t->avatar_path) }}" alt="">@else<span class="avatar initial">{{ mb_substr($t->name, 0, 1) }}</span>@endif
                <div>
                    <strong>{{ $t->name }}</strong>
                    @if ($t->title)<span class="muted">· {{ $t->title }}</span>@endif
                    <div class="muted small">
                        <span class="badge {{ $t->status }}">{{ ucfirst($t->status) }}</span>
                        {{ $t->created_at->diffForHumans() }} · {{ ['form' => 'via form', 'manual' => 'added by you', 'import' => 'imported'][$t->source] ?? $t->source }}@if ($t->email) · {{ $t->email }}@endif
                    </div>
                </div>
                @if ($t->rating)<span class="stars" aria-label="{{ $t->rating }} out of 5 stars">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</span>@endif
            </header>
            <p class="body">{{ $t->body }}</p>
            <footer class="actions">
                @foreach (['approved' => 'Approve', 'hidden' => 'Hide', 'pending' => 'Back to pending'] as $to => $label)
                    @continue($t->status === $to || ($to === 'pending' && $t->status === 'pending'))
                    <form method="post" action="{{ route('projects.testimonials.update', [$project, $t]) }}">
                        @csrf @method('patch')
                        <input type="hidden" name="status" value="{{ $to }}">
                        <button class="btn {{ $to === 'approved' ? '' : 'secondary' }} sm">{{ $label }}</button>
                    </form>
                @endforeach
                <form method="post" action="{{ route('projects.testimonials.update', [$project, $t]) }}">
                    @csrf @method('patch')
                    <input type="hidden" name="featured" value="{{ $t->featured ? 0 : 1 }}">
                    <button class="btn secondary sm" aria-pressed="{{ $t->featured ? 'true' : 'false' }}">{{ $t->featured ? '★ Featured' : '☆ Feature' }}</button>
                </form>
                <form method="post" action="{{ route('projects.testimonials.destroy', [$project, $t]) }}" class="push-right"
                      onsubmit="return confirm('Delete this testimonial for good? This can’t be undone.')">
                    @csrf @method('delete')
                    <button class="btn danger sm">Delete</button>
                </form>
            </footer>
        </article>
    @empty
        <div class="card empty"><p class="muted">
            @if ($status === 'pending') Nothing waiting for review. @elseif ($counts->sum() === 0) Nothing yet. Send the link above to a happy customer. @else Nothing here. @endif
        </p></div>
    @endforelse
@endsection
