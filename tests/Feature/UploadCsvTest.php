<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCsvTest extends TestCase
{
    /**
     * Test the index page returns successfully.
     */
    public function test_upload_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CSV Upload Module');
    }

    /**
     * Test uploading a non-CSV file is blocked by validation.
     */
    public function test_upload_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post('/upload', [
            'csv_file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['csv_file']);
    }

    /**
     * Test uploading a valid CSV file is successful and stored correctly.
     */
    public function test_upload_valid_csv_stores_file_successfully(): void
    {
        Storage::fake('uploads');

        $csvContent = "id,product_name,price\n1,Test Product,9.99";
        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->post('/upload', [
            'csv_file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'original_filename',
                'stored_filename',
                'storage_path',
            ],
        ]);

        $storedName = $response->json('data.stored_filename');
        
        // Assert the file exists on the custom disk
        Storage::disk('uploads')->assertExists($storedName);
    }
}
