<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{

    public function index()
    {
        $posts = Post::all();

        return view('post.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('post.show', compact('post'));
    }

    public function create()
    {
        return view('post.create');
    }

    public function store()
    {
        $data = request()->validate([
            'title' => 'string',
            'content' => 'string',
        ]);

        Post::create($data);

        return redirect()->route('post.index');
    }


    public function edit(Post $post)
    {
        return view('post.edit', compact('post'));
    }

    public function update(Post $post)
    {
        $data = request()->validate([
            'title' => 'required',
            'content' => 'required'
        ]);
  
        $post->update($data);

        return redirect()->route('post.show', $post->id);
    }


    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('post.index');
    }

}
