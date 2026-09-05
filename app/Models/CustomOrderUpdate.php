<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrderUpdate extends Model
{
    protected $guarded = ['id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomOrder::class, 'custom_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
