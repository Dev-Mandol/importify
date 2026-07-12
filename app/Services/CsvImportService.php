<?php

namespace App\Services;

use App\Models\Upload;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SplFileObject;
use Exception;

class CsvImportService
{
    protected ShopifyService $shopifyService;
    protected ImportLogService $logService;

    public function __construct(ShopifyService $shopifyService, ImportLogService $logService)
    {
        $this->shopifyService = $shopifyService;
        $this->logService = $logService;
    }

    // Import the files
    public function process(int $uploadId)
    {
        $upload = $this->loadUpload($uploadId);

        try {
            $upload->update(['status' => 'processing']);
            $this->logService->info($upload->id, null, 'upload_started', 'CSV processing pipeline started.');

            $filePath = $this->getCsvFilePath($upload);
            $file = $this->openCsv($filePath);

            // Read and normalize headers
            $headersRow = $file->current();
            if (!$headersRow) {
                throw new Exception('The uploaded CSV file is empty or has no header row.');
            }
            $file->next();

            $headers = $this->normalizeHeaders($headersRow);
            $this->validateHeaders($headers);
            $this->logService->info($upload->id, null, 'csv_validated', 'CSV headers validated successfully.', ['headers' => $headers]);

            // Estimate total records
            $totalRecords = $this->countTotalRecords($file);
            $upload->update(['total_records' => $totalRecords]);

            // Process product rows
            $this->processRows($upload, $file, $headers);

            $this->finishImport($upload, 'completed');
        } catch (Exception $e) {
            $this->logService->error($upload->id, null, 'upload_failed', 'CSV Import process failed: ' . $e->getMessage());
            $this->finishImport($upload, 'failed');
        }
    }

    protected function loadUpload(int $uploadId)
    {
        return Upload::findOrFail($uploadId);
    }

    protected function getCsvFilePath(Upload $upload)
    {
        $disk = config('uploads.disk', 'uploads');
        if (!Storage::disk($disk)->exists($upload->stored_filename)) {
            throw new Exception("CSV file not found in storage: {$upload->stored_filename}");
        }
        return Storage::disk($disk)->path($upload->stored_filename);
    }

    protected function openCsv(string $filePath)
    {
        try {
            $file = new SplFileObject($filePath, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
            $this->logService->info(null, null, 'csv_opened', 'CSV file opened successfully.');
            return $file;
        } catch (Exception $e) {
            throw new Exception("Failed to open CSV file: " . $e->getMessage());
        }
    }

    protected function normalizeHeaders(array $headers)
    {
        return array_map(fn($header) => strtolower(trim($header)), $headers);
    }

    protected function validateHeaders(array $headers)
    {
        if (!in_array('title', $headers, true)) {
            throw new Exception('Missing required header: "title".');
        }
    }

    protected function countTotalRecords(SplFileObject $file)
    {
        $count = 0;

        while (!$file->eof()) {
            $row = $file->current();
            $file->next();
            if ($row && !empty(array_filter($row))) {
                $count++;
            }
        }

        $file->rewind();
        $file->next();

        return $count;
    }

    protected function processRows(Upload $upload, SplFileObject $file, array $headers)
    {
        $processed = 0;
        $success = 0;
        $failed = 0;

        while (!$file->eof()) {
            $row = $file->current();
            $file->next();
            if (!$row || empty(array_filter($row))) {
                continue;
            }

            $processed++;
            $mappedData = $this->mapRow($row, $headers);

            Log::info("Mapped Data: ", $mappedData);

            // Save product details to DB
            $product = Product::create([
                'upload_id' => $upload->id,
                'title' => $mappedData['title'],
                'handle' => $mappedData['handle'] ?? null,
                'vendor' => $mappedData['vendor'] ?? null,
                'product_type' => $mappedData['product type'] ?? null,
                'sku' => $mappedData['variant sku'] ?? null,
                'price' => isset($mappedData['variant price']) && is_numeric($mappedData['variant price']) ? (float) $mappedData['variant price'] : null,
                'status' => 'pending',
            ]);

            $this->logService->info($upload->id, $product->id, 'product_processing', "Processing product: {$product->title}");

            try {
                // Call Shopify Service
                $result = $this->shopifyService->importProduct($mappedData);

                if ($result['success']) {
                    $product->update([
                        'shopify_product_id' => $result['shopify_product_id'],
                        'status' => 'completed',
                    ]);
                    $success++;

                    $this->logService->info(
                        $upload->id,
                        $product->id,
                        'product_imported',
                        "Product successfully sent to Shopify. ID: {$result['shopify_product_id']}"
                    );
                } else {
                    $errorMessage = implode(', ', $result['errors']);
                    $product->update([
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                    ]);
                    $failed++;

                    $this->logService->warning(
                        $upload->id,
                        $product->id,
                        'product_failed',
                        "Shopify import failed for product: {$errorMessage}"
                    );
                }
            } catch (Exception $e) {
                $product->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $failed++;

                $this->logService->error(
                    $upload->id,
                    $product->id,
                    'product_failed',
                    "Exception during Shopify import: " . $e->getMessage()
                );
            }

            $this->updateProgress($upload, 'processing', $processed, $success, $failed);
        }
    }

    protected function mapRow(array $row, array $headers)
    {
        $mapped = [];

        foreach ($headers as $index => $header) {

            // Remove UTF-8 BOM and trim spaces
            $header = preg_replace('/^\xEF\xBB\xBF/', '', trim($header));

            if (isset($row[$index])) {
                $mapped[$header] = trim($row[$index]);
            }
        }

        return $mapped;
    }

    protected function updateProgress(Upload $upload, string $status, int $processed, int $success, int $failed)
    {
        $upload->update([
            'status' => $status,
            'processed_records' => $processed,
            'successful_records' => $success,
            'failed_records' => $failed,
        ]);
    }

    protected function finishImport(Upload $upload, string $finalStatus)
    {
        $upload->update(['status' => $finalStatus]);
        $this->logService->info($upload->id, null, 'upload_completed', "CSV import completed with status: {$finalStatus}.");
    }
}
