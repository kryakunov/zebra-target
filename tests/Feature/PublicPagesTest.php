<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_home_is_public()
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_free_parser_is_public()
    {
        $this->get('/free-parser')->assertStatus(200);
    }

    public function test_price_is_public()
    {
        $this->get('/price')->assertStatus(200);
    }

    public function test_robots_txt_is_available()
    {
        $this->get('/robots.txt')
            ->assertStatus(200)
            ->assertSee('Sitemap:', false);
    }

    public function test_sitemap_contains_public_pages()
    {
        $this->get('/sitemap.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('filtergroups', false)
            ->assertSee('searchgroups', false)
            ->assertDontSee('myworks', false);
    }

    public function test_tool_landing_is_public_for_guests()
    {
        $response = $this->get('/searchgroups');

        $this->assertFalse(
            $response->isRedirect() && strpos((string) $response->headers->get('Location'), 'guest') !== false,
            'Публичный лендинг инструмента не должен редиректить гостя на /guest'
        );
        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_filtergroups_landing_is_public_for_guests()
    {
        $response = $this->get('/filtergroups');

        $this->assertFalse(
            $response->isRedirect() && strpos((string) $response->headers->get('Location'), 'guest') !== false
        );
        $this->assertContains($response->status(), [200, 404]);
    }
}
