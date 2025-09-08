<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @text
     */
    public function post_store()
    {
        $data = [1,2,3];

        $a = $this->get('/');
        $a->assertOk();

        //$this->post('/posts', $data);
    }
}