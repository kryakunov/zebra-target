<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class photo extends Model
{
    protected $guarded = [];
    
    public static function add($fields)
    {
    	$post = new static;
    	$post->fill($fields);
    	$post->save();

    	return $post;
    }



    public function remove()
    {
    	$this->removeImage();
    	$this->delete();
    }

    public function removeImage()
    {
        if($this->image != null)
        {
            Storage::delete('uploads/' . $this->image);
        }
    }

    public function uploadImage($image)
    {
    	if($image == null) { return; }

    	$this->removeImage();
    	$filename = \Illuminate\Support\Str::random(10) . '.' . $image->extension();
    	$image->storeAs('uploads', $filename);
    	$this->image = $filename;
    	$this->save();
    }

    public function getImage()
    {
    	if($this->image == null)
    	{
    		return '/img/no-image.png';
    	}

    	return '/uploads/' . $this->image;

    }
}
