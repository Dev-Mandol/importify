<?php

namespace Tests\Feature;

use App\Jobs\ProcessCsvImportJob;
use App\Models\Upload;
use App\Models\Product;
use App\Models\ImportLog;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvImportPipelineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that uploading a CSV file registers an upload in the DB and dispatches the queue job.
     */
    public function test_upload_saves_record_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('uploads');

        $csvContent = "title,handle,vendor,sku,price\nProduct A,prod-a,Vendor A,SKU-A,19.99";
        $file = UploadedFile::fake()->createWithContent('test_products.csv', $csvContent);

        $response = $this->post('/upload', [
            'csv_file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Check if database contains the upload record
        $this->assertDatabaseHas('uploads', [
            'original_filename' => 'test_products.csv',
            'status' => 'pending',
        ]);

        $upload = Upload::first();
        $this->assertNotNull($upload);

        // Assert job was pushed with the upload ID
        Queue::assertPushed(ProcessCsvImportJob::class, function ($job) use ($upload) {
            // Reflect/access protected property
            $ref = new \ReflectionProperty($job, 'uploadId');
            return $ref->getValue($job) === $upload->id;
        });
    }

    /**
     * Test that CsvImportService processes the CSV file, handles rows, calls ShopifyService, and writes logs.
     */
    public function test_import_service_processes_csv_correctly(): void
    {
        Storage::fake('uploads');

        // Create upload record
        $csvContent = "title,handle,vendor,sku,price\nProduct A,prod-a,Vendor A,SKU-A,19.99\nProduct B Fail,prod-b,Vendor B,SKU-B,29.99\n";
        $storedFilename = 'products_test_123.csv';
        Storage::disk('uploads')->put($storedFilename, $csvContent);
        $filePath = Storage::disk('uploads')->path($storedFilename);

        $upload = Upload::create([
            'original_filename' => 'test.csv',
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'status' => 'pending',
        ]);

        // Resolve CsvImportService
        $service = app(CsvImportService::class);
        $service->process($upload->id);

        // Refresh upload record
        $upload->refresh();

        // Verify status and counters
        $this->assertEquals('completed', $upload->status);
        $this->assertEquals(2, $upload->total_records);
        $this->assertEquals(2, $upload->processed_records);
        $this->assertEquals(1, $upload->successful_records);
        $this->assertEquals(1, $upload->failed_records);

        // Verify products created in database
        $this->assertDatabaseHas('products', [
            'upload_id' => $upload->id,
            'title' => 'Product A',
            'sku' => 'SKU-A',
            'price' => 19.99,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('products', [
            'upload_id' => $upload->id,
            'title' => 'Product B Fail',
            'sku' => 'SKU-B',
            'price' => 29.99,
            'status' => 'failed',
        ]);

        // Verify logs created in database
        $this->assertDatabaseHas('import_logs', [
            'upload_id' => $upload->id,
            'event' => 'upload_started',
        ]);

        $this->assertDatabaseHas('import_logs', [
            'upload_id' => $upload->id,
            'event' => 'csv_validated',
        ]);

        $this->assertDatabaseHas('import_logs', [
            'upload_id' => $upload->id,
            'event' => 'product_imported',
        ]);

        $this->assertDatabaseHas('import_logs', [
            'upload_id' => $upload->id,
            'event' => 'product_failed',
        ]);

        $this->assertDatabaseHas('import_logs', [
            'upload_id' => $upload->id,
            'event' => 'upload_completed',
        ]);
    }

    /**
     * Test that validation fails if required headers (title) are missing.
     */
    public function test_import_fails_on_missing_required_headers(): void
    {
        Storage::fake('uploads');

        // Missing "title" header
        $csvContent = "handle,vendor,sku,price\nprod-a,Vendor A,SKU-A,19.99";
        $storedFilename = 'products_invalid_headers.csv';
        Storage::disk('uploads')->put($storedFilename, $csvContent);
        $filePath = Storage::disk('uploads')->path($storedFilename);

        $upload = Upload::create([
            'original_filename' => 'test.csv',
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'status' => 'pending',
        ]);

        $service = app(CsvImportService::class);
        $service->process($upload->id);

        $upload->refresh();

        $this->assertEquals('failed', $upload->status);
        $this->assertDatabaseHas('import_logs', [
            'upload_id' => $upload->id,
            'level' => 'error',
            'event' => 'upload_failed',
            'message' => 'CSV Import process failed: Missing required header: "title".',
        ]);
    }
}
