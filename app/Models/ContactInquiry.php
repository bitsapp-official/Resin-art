<?php

namespace App\Models;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactInquiryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_inquiries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'public_reference',
        'name',
        'email',
        'phone',
        'inquiry_type',
        'subject',
        'message',
        'status',
        'priority',
        'admin_notes',
        'replied_at',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inquiry_type' => ContactInquiryType::class,
        'status' => ContactInquiryStatus::class,
        'priority' => ContactInquiryPriority::class,
        'replied_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope for new inquiries.
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', ContactInquiryStatus::NEW);
    }

    /**
     * Scope for pending inquiries (new or in progress).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ContactInquiryStatus::NEW,
            ContactInquiryStatus::READ,
            ContactInquiryStatus::IN_PROGRESS,
        ]);
    }
}
