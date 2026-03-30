<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAtlasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_facilities_api_returns_seeded_geojson_payload(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->getJson('/api/facilities');

        $response
            ->assertOk()
            ->assertJsonPath('count', 6)
            ->assertJsonStructure([
                'count',
                'geojson' => ['type', 'features'],
                'data' => [['slug', 'name', 'category']],
            ]);
    }
}
