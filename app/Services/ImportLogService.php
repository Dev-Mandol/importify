<?php

namespace App\Services;

use App\Models\ImportLog;

class ImportLogService
{
    public function log(
        ?int $uploadId,
        ?int $productId,
        string $level,
        string $event,
        string $message,
        array $context = []
    ): ImportLog {
        return ImportLog::create([
            'upload_id' => $uploadId,
            'product_id' => $productId,
            'level' => $level,
            'event' => $event,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function info(?int $uploadId, ?int $productId, string $event, string $message, array $context = []): ImportLog
    {
        return $this->log($uploadId, $productId, 'info', $event, $message, $context);
    }

    public function warning(?int $uploadId, ?int $productId, string $event, string $message, array $context = []): ImportLog
    {
        return $this->log($uploadId, $productId, 'warning', $event, $message, $context);
    }

    public function error(?int $uploadId, ?int $productId, string $event, string $message, array $context = []): ImportLog
    {
        return $this->log($uploadId, $productId, 'error', $event, $message, $context);
    }
}
