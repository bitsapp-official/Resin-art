<?php

namespace App\Models;

use App\Enums\CustomRequestSenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRequestMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'sender_type' => CustomRequestSenderType::class,
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomRequest::class, 'custom_request_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
