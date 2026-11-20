@if ($errors->any())
    <div class="errors" role="alert">
        @if ($errors->count() === 1)
            <p>{{ $errors->first() }}</p>
        @else
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
    </div>
@endif
