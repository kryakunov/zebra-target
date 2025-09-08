{{$post->title}} - {{$post->content}}<br>
<a href="{{route('posts.index')}}">Back</a> | <a href="{{route('posts.edit', $post->id)}}">Edit</a>