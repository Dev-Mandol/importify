<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    protected $fillable = [
        'original_filename',
        'stored_filename',
        'file_path',
        'status',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function importLogs(): HasMany
    {
        return $this->hasMany(ImportLog::class);
    }
}
