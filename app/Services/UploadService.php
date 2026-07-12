<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class UploadService
{
    public function upload(UploadedFile $file)
    {
        try {
            if (!$file->isValid()) {
                throw new Exception('The uploaded file is not valid.');
            }

            //  Check the file type
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'csv') {
                throw new Exception('Invalid file extension. Only CSV files are allowed.');
            }

            $mimeType = $file->getMimeType();
            $allowedMimes = config('uploads.allowed_mimes', [
                'text/csv',
                'text/plain',
            ]);

            if (!in_array($mimeType, $allowedMimes, true)) {
                Log::warning('Blocked unauthorized CSV upload attempt: Invalid MIME type.', [
                    'mime_type' => $mimeType,
                    'original_filename' => $file->getClientOriginalName(),
                ]);
                throw new Exception('The file type is not allowed.');
            }

            // Verify file size
            $maxSizeKb = config('uploads.max_size', 10240);
            if (($file->getSize() / 1024) > $maxSizeKb) {
                throw new Exception('The file size exceeds the allowed limit.');
            }

            // Generate a unique filename
            $timestamp = date('Ymd_His');
            $randomString = Str::random(6);
            $storedFilename = sprintf('products_%s_%s.csv', $timestamp, $randomString);

            $disk = config('uploads.disk', 'uploads');

            // Store the file
            $path = $file->storeAs('', $storedFilename, $disk);

            if (!$path) {
                throw new Exception('Could not store the file on the filesystem.');
            }

            $storagePath = Storage::disk($disk)->path($storedFilename);

            // Create upload database record
            $upload = \App\Models\Upload::create([
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $storagePath,
                'status' => 'pending',
            ]);

            // Dispatch background queue processing job
            \App\Jobs\ProcessCsvImportJob::dispatch($upload->id);

            Log::info('CSV file uploaded, registered in database, and queued for import.', [
                'upload_id' => $upload->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'storage_path' => $storagePath,
                'size_bytes' => $file->getSize(),
            ]);

            return [
                'id' => $upload->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'storage_path' => $storagePath,
            ];
        } catch (Exception $e) {
            Log::error('CSV Upload Failure: ' . $e->getMessage(), [
                'exception' => $e,
                'original_filename' => $file->getClientOriginalName() ?? 'unknown',
            ]);
            throw $e;
        }
    }
}
