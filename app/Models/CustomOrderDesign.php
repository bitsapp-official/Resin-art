<?php

namespace App\Models;

use App\Enums\CustomOrderDesignStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrderDesign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => CustomOrderDesignStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
