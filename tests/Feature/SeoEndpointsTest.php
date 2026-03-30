<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private const PUBLIC_URL = 'https://atlas.example.test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_renders_public_seo_meta(): void
    {
        config()->set('civic_atlas.public_url', self::PUBLIC_URL);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('meta property="og:title"', false);
        $response->assertSee('meta name="twitter:card"', false);
        $response->assertSee('link rel="canonical" href="'.self::PUBLIC_URL.'"', false);
    }

    public function test_sitemap_contains_public_routes(): void
    {
        config()->set('civic_atlas.public_url', self::PUBLIC_URL);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(self::PUBLIC_URL.'/', false);
        $response->assertSee(self::PUBLIC_URL.'/atlas/explore', false);
        $response->assertSee('/facilities/lady-reading-hospital', false);
    }

    public function test_robots_disallows_private_routes_and_points_to_sitemap(): void
    {
        config()->set('civic_atlas.public_url', self::PUBLIC_URL);

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Sitemap: '.self::PUBLIC_URL.'/sitemap.xml');
    }
}
