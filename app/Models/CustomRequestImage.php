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
}
