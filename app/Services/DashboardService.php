<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getStatistics(): array
    {
        return [
            'total_uploads' => Upload::count(),
            'total_products' => Product::count(),
            'successful_imports' => Product::where('status', 'completed')->count(),
            'failed_imports' => Product::where('status', 'failed')->count(),
        ];
    }

    public function getRecentUploads(): Collection
    {
        return Upload::latest()
            ->limit(10)
            ->get();
    }


    public function getRecentProducts(int $perPage = 15): LengthAwarePaginator
    {
        return Product::latest()
            ->paginate($perPage, ['*'], 'products_page');
    }


    public function getRecentLogs(): Collection
    {
        return ImportLog::with(['upload'])
            ->latest()
            ->limit(20)
            ->get();
    }


    public function getUploadDetails(int $uploadId): array
    {
        $upload = Upload::findOrFail($uploadId);

        $products = Product::where('upload_id', $uploadId)
            ->latest()
            ->paginate(15, ['*'], 'products_page');

        $logs = ImportLog::where('upload_id', $uploadId)
            ->latest()
            ->paginate(20, ['*'], 'logs_page');

        return compact('upload', 'products', 'logs');
    }
}
