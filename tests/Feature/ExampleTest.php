<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Review filters and shortlist facilities')
            ->assertSee('Atlas workbench')
            ->assertSee('Open filtered explorer');
    }

    public function test_the_explorer_page_returns_a_successful_response(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/atlas/explore');

        $response
            ->assertOk()
            ->assertSee('Dedicated map explorer')
            ->assertSee('Interactive facility map')
            ->assertSee('Back to Workbench');
    }
}
