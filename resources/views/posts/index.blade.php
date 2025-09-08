{{dd($posts)}}

$userInfo = collect([$user->name, $user->country, $user->email])->implode(', ');
{{ $userInfo }}


<a href="{{route('posts.create')}}">Add Post</a><hr>
@forelse($posts as $item) 
    <a href="{{route('posts.show', $item->id)}}">{{$item->title}}</a> - {{$item->content}}<form method="post" action="{{route('posts.delete', $item->id)}}">
            @csrf
            @method('delete')
            <input type="submit" value="Delete">
        </form>
    <hr>
@empty
    <b>Пусто!</b>
@endforelse
@php
    $posts = $posts->toArray();
    
@endphp
{{ trim(implode(",", $posts[0]), ',') }}

