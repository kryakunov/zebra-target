<a href="{{ route('post.create') }}">Add Post</a><br>
@forelse($posts as $post)
   <a href="{{ route('post.show', $post->id) }}"> {{ $post->id . '. ' . $post->title . ' > ' . $post->content }}</a> <form method="post" action="{{ route('post.delete', $post->id)}}">@method('delete')@csrf<input type="submit" value="delete"></form> <br>
@empty
    Пусто!
@endforelse