<?php

namespace Tests\Feature;

use App\Models\Dataset;
use App\Models\Facility;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminImportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_data_manager_can_upload_validate_and_publish_an_import(): void
    {
        $this->seed(DatabaseSeeder::class);

        $manager = User::query()->where('email', 'manager@civicatlas.test')->firstOrFail();
        $dataset = Dataset::query()->firstOrFail();

        $csv = implode("\n", [
            'name,category,facility_type,latitude,longitude,address_line,locality,source_identifier',
            'Demo Family Clinic,Health,Clinic,34.0210,71.5401,Phase 1 Hayatabad,Hayatabad,demo-family-clinic',
        ]);

        $upload = UploadedFile::fake()->createWithContent('facilities.csv', $csv);

        $storeResponse = $this
            ->actingAs($manager)
            ->post(route('admin.imports.store'), [
                'dataset_id' => $dataset->id,
                'source_year' => 2026,
                'import_file' => $upload,
            ]);

        $storeResponse->assertRedirect();

        $importRun = $dataset->versions()->latest('id')->firstOrFail()->importRuns()->latest('id')->firstOrFail();

        $this->actingAs($manager)
            ->post(route('admin.imports.validate', $importRun))
            ->assertRedirect();

        $this->actingAs($manager)
            ->post(route('admin.imports.publish', $importRun))
            ->assertRedirect();

        $this->assertDatabaseHas('facilities', [
            'name' => 'Demo Family Clinic',
            'publication_status' => 'published',
        ]);

        $this->assertSame(
            1,
            Facility::query()->where('name', 'Demo Family Clinic')->count()
        );
    }
}
