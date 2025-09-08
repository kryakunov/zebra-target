<form action="{{route('posts.update', $post->id)}}" method="POST">
    @csrf
    @method('patch')
    <input type="text" value="{{ $post->title }}" name="title" placeholder="title">
    <input type="text"  value="{{ $post->content }}" name="content" placeholder="content">
    <input type="submit"  value="update">

</form>

@forelse($errors->all() as $error)
    {{$error}} <br>
    @empty
    
@endforelse