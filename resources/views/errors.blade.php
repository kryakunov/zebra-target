@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endforeach
@endif

@if (!empty($exceptions))
    @foreach ($exceptions as $exception)
    <div class="alert alert-danger">
        {{ $exception }}
    </div>
    @endforeach
@endif