<form action="{{route('post.update', $post->id)}}" method="post">
    @csrf
    @method('patch')
    <input type="text" name="title" value="{{old('title', $post->title)}}">
    <input type="text" name="content" value="{{old('content', $post->content)}}">
    <input type="submit">
</form>

@forelse($errors->all() as $error)
    {{$error}} <br>
    @empty
    
@endforelse