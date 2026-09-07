<?php

namespace App\Models;

use App\Enums\CustomRequestImageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRequestImage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => CustomRequestImageType::class,
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id');
    }

    public function getUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        // 1. Direct match in storage/app/public/
        if (file_exists(storage_path('app/public/' . $this->file_path))) {
            return asset('storage/' . $this->file_path);
        }

        // 2. In custom-requests/ without 'references/'
        $noRef = str_replace('/references/', '/', $this->file_path);
        if (file_exists(storage_path('app/public/' . $noRef))) {
            return asset('storage/' . $noRef);
        }

        // 3. In custom-commissions/
        $commissions = str_replace('custom-requests', 'custom-commissions', $this->file_path);
        if (file_exists(storage_path('app/public/' . $commissions))) {
            return asset('storage/' . $commissions);
        }

        return asset('storage/' . $this->file_path);
    }
}
