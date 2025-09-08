<form action="{{route('post.store')}}" method="post">
    @csrf
    <input type="text" name="title" placeholder="title" value="{{old('title')}}">
    <input type="text" name="content" placeholder="content" value="{{old('content')}}">
    <input type="submit" value="Отправить">
</form>

@forelse($errors->all() as $error)
    {{ $error }}<br>
@empty
@endforelse