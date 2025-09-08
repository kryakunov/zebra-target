<form action="{{route('posts.store')}}" method="POST">
    @csrf
    <input type="text" name="title" placeholder="title">
    <input type="text" name="content" placeholder="content">
    <input type="submit">

</form>

@forelse($errors->all() as $error)
    {{$error}} <br>
    @empty
    
@endforelse