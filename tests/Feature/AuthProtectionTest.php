<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthProtectionTest extends TestCase
{
    public function test_guest_cannot_open_myworks()
    {
        $this->get('/myworks')->assertRedirect(route('guest'));
    }

    public function test_guest_cannot_open_cloud()
    {
        $this->get('/cloud')->assertRedirect(route('guest'));
    }

    public function test_guest_cannot_post_filtergroups()
    {
        $this->from('/filtergroups')
            ->post('/filtergroups', [])
            ->assertRedirect();
    }

    public function test_guest_cannot_post_searchgroups()
    {
        $this->from('/searchgroups')
            ->post('/searchgroups', ['q' => 'test'])
            ->assertRedirect();
    }

    public function test_guest_cannot_post_tool1()
    {
        $this->from('/tool1')
            ->post('/tool1', ['users' => '1'])
            ->assertRedirect();
    }

    public function test_guest_cannot_open_group_viewer()
    {
        $this->get('/group-viewer')->assertRedirect(route('guest'));
    }

    public function test_guest_cannot_open_chains()
    {
        $this->get('/chains')->assertRedirect(route('guest'));
    }
}
