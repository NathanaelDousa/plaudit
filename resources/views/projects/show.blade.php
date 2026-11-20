@extends('layouts.admin')
@section('title', $project->name)
@section('brand', $project->brand_color)
@section('content')
    <a class="back" href="{{ route('projects.index') }}">← Projects</a>
    <div class="page-head">
        <div>
            <h1>{{ $project->name }}</h1>
            <p class="muted">{{ $counts['approved'] ?? 0 }} published, {{ $counts['pending'] ?? 0 }} waiting for review</p>
        </div>
        <a class="btn quiet sm" href="{{ route('projects.edit', $project) }}">Edit project</a>
    </div>

    <div class="share">
        <section class="sheet">
            <h2>Ask for testimonials</h2>
            <p class="muted small">Send this link to happy customers. They don’t need an account.</p>
            <div class="copy-row">
                <input id="form-link" readonly value="{{ route('submit', $project) }}" aria-label="Form link">
                <button class="btn quiet sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('form-link').value); this.textContent='Copied'">Copy</button>
            </div>
        </section>
        <section class="sheet">
            <h2>Your wall of love</h2>
            <p class="muted small">A public page with every testimonial you publish.</p>
            <div class="copy-row">
                <a class="btn quiet sm" href="{{ route('wall', $project) }}" target="_blank" rel="noopener">Open wall</a>
                <a class="btn quiet sm" href="{{ route('submit', $project) }}" target="_blank" rel="noopener">Preview form</a>
            </div>
        </section>
    </div>

    <details class="sheet embed">
        <summary><h2>Show testimonials on your website</h2></summary>
        <p class="muted">Paste this code where testimonials should appear. Only published testimonials are shown.</p>
        <div class="segmented" role="group" aria-label="Layout" style="margin-top:1rem">
            <button type="button" aria-pressed="true" data-layout="grid">Grid</button>
            <button type="button" aria-pressed="false" data-layout="carousel">Carousel</button>
        </div>
        <textarea id="embed-code" readonly rows="3" aria-label="Embed code"></textarea>
        <div class="copy-row" style="align-items:center">
            <button class="btn quiet sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('embed-code').value); this.textContent='Copied'">Copy code</button>
            <span class="muted small">Also try <code>data-theme="dark"</code> or <code>data-limit="6"</code>.</span>
        </div>
        <div class="preview" id="embed-preview" aria-label="Preview"></div>
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
        <nav class="tabs" aria-label="Filter testimonials">
            @foreach (['pending' => 'To review', 'approved' => 'Published', 'hidden' => 'Hidden', 'all' => 'All'] as $key => $label)
                <a href="{{ route('projects.show', [$project, 'status' => $key]) }}" @if ($status === $key) aria-current="page" @endif>
                    {{ $label }}<span class="count">{{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
                </a>
            @endforeach
        </nav>
        <span class="spacer"></span>
        <a class="btn quiet sm" href="{{ route('projects.testimonials.create', $project) }}">Add manually</a>
        <details class="dropdown">
            <summary class="btn quiet sm">Import CSV</summary>
            <form method="post" action="{{ route('projects.import', $project) }}" enctype="multipart/form-data" class="sheet stack">
                @csrf
                <p class="small muted">The first row must name the columns. <code>name</code> and <code>body</code> are required; <code>title</code>, <code>email</code> and <code>rating</code> are optional. Imported testimonials wait for your review.</p>
                <input type="file" name="file" accept=".csv,text/csv" required aria-label="CSV file">
                <label class="check small"><input type="checkbox" name="consent" value="1" required> These people agreed to have their testimonials shown publicly.</label>
                <div><button class="btn sm">Import</button></div>
            </form>
        </details>
        <span class="small muted">Export <a href="{{ route('projects.export', [$project, 'csv']) }}">CSV</a> or <a href="{{ route('projects.export', [$project, 'json']) }}">JSON</a></span>
    </div>
    @include('layouts.errors')

    @if ($testimonials->isEmpty())
        <div class="empty" style="margin-top:1.5rem">
            @if ($status === 'pending')
                <h2>All caught up</h2>
                <p class="muted">New testimonials will wait here until you publish or hide them.</p>
            @elseif ($counts->sum() === 0)
                <h2>No testimonials yet</h2>
                <p class="muted">Copy your form link above and send it to a customer who likes your work.</p>
            @else
                <p class="muted">Nothing here.</p>
            @endif
        </div>
    @else
        <ul class="inbox">
            @foreach ($testimonials as $t)
                <li class="entry {{ $t->status }}">
                    @if ($t->avatar_path)<img class="avatar" src="{{ asset($t->avatar_path) }}" alt="">@else<span class="avatar initial" aria-hidden="true">{{ mb_substr($t->name, 0, 1) }}</span>@endif
                    @if ($t->rating)
                        <div class="entry-rating" role="img" aria-label="{{ $t->rating }} out of 5 stars"><span class="sticker">{{ str_repeat('★', $t->rating) }}</span><span class="stickers-off">{{ str_repeat('★', 5 - $t->rating) }}</span></div>
                    @endif
                    <p class="quote">{{ $t->body }}</p>
                    <div class="entry-meta">
                        <span><strong>{{ $t->name }}</strong>@if ($t->title), <span class="muted">{{ $t->title }}</span>@endif</span>
                        <span class="muted">{{ ['form' => 'Sent', 'manual' => 'Added', 'import' => 'Imported'][$t->source] ?? 'Added' }} {{ $t->created_at->diffForHumans() }}</span>
                        @if ($t->email)<a class="muted" href="mailto:{{ $t->email }}">{{ $t->email }}</a>@endif
                    </div>
                    <div class="entry-actions">
                        <span class="status {{ $t->status }}">{{ ['pending' => 'Waiting for review', 'approved' => 'Published', 'hidden' => 'Hidden'][$t->status] }}</span>
                        @if ($t->featured)<span class="featured-mark">Featured</span>@endif
                        @foreach (['approved' => 'Publish', 'hidden' => 'Hide', 'pending' => 'Move back to review'] as $to => $label)
                            @continue($t->status === $to)
                            <form method="post" action="{{ route('projects.testimonials.update', [$project, $t]) }}">
                                @csrf @method('patch')
                                <input type="hidden" name="status" value="{{ $to }}">
                                @if ($to === 'approved')<button class="btn brand sm">{{ $label }}</button>@else<button class="link">{{ $label }}</button>@endif
                            </form>
                        @endforeach
                        <form method="post" action="{{ route('projects.testimonials.update', [$project, $t]) }}">
                            @csrf @method('patch')
                            <input type="hidden" name="featured" value="{{ $t->featured ? 0 : 1 }}">
                            <button class="link" aria-pressed="{{ $t->featured ? 'true' : 'false' }}">{{ $t->featured ? 'Unfeature' : 'Feature' }}</button>
                        </form>
                        <form method="post" action="{{ route('projects.testimonials.destroy', [$project, $t]) }}"
                              onsubmit="return confirm('Delete this testimonial for good? This can’t be undone.')">
                            @csrf @method('delete')
                            <button class="link danger push">Delete</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
