

@if (!empty($exceptions))
    @foreach ($exceptions as $exception)
    <div class="alert alert-danger">
        {{ $exception }}
    </div>
    @endforeach
@endif