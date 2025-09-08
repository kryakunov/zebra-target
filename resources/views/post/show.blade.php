 {{ $post->id . '. ' . $post->title . ' ' . $post->content }}
<hr>
<a href="{{ route('post.index') }}">Back</a>
<a href="{{ route('post.edit', $post->id) }}">Edit</a>