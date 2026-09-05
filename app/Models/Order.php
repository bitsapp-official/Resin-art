<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    // ─── ORDER STATUS CONSTANTS (STANDARD E-COMMERCE) ────────────────────────
    const STATUS_CONFIRMED      = 'CONFIRMED';      // Order confirmed / Payment received
    const STATUS_PROCESSING     = 'PROCESSING';     // Order is being processed & packed
    const STATUS_SHIPPED        = 'SHIPPED';        // Dispatched with courier
    const STATUS_DELIVERED      = 'DELIVERED';      // Successfully delivered to customer
    const STATUS_CANCELLED      = 'CANCELLED';      // Order cancelled

    // Aliases for backward compatibility with previous artisan statuses
    const STATUS_CRAFTING       = 'PROCESSING';
    const STATUS_QUALITY_CHECK  = 'PROCESSING';
    const STATUS_PACKED         = 'PROCESSING';

    const STATUSES = [
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    // Standard E-Commerce Labels shown across website and admin
    const STATUS_LABELS = [
        self::STATUS_CONFIRMED  => 'Confirmed',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_SHIPPED    => 'Shipped',
        self::STATUS_DELIVERED  => 'Delivered',
        self::STATUS_CANCELLED  => 'Cancelled',
    ];

    protected $fillable = [
        'order_reference',
        'user_id',
        'email',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'subtotal',
        'discount',
        'tax',
        'shipping_fee',
        'grand_total',
        'shipping_address_snapshot',
        'billing_address_snapshot',
        'courier',
        'tracking_number',
        'tracking_url',
        'shipped_at',
        'estimated_delivery_at',
        'notes',
        'canceled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'subtotal'                  => 'decimal:2',
        'discount'                  => 'decimal:2',
        'tax'                       => 'decimal:2',
        'shipping_fee'              => 'decimal:2',
        'grand_total'               => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'billing_address_snapshot'  => 'array',
        'shipped_at'                => 'datetime',
        'estimated_delivery_at'     => 'datetime',
        'canceled_at'               => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    // Helper: customer-facing label for this order's status
    public function getStatusLabelAttribute(): string
    {
        $status = strtoupper((string) $this->status);
        if (in_array($status, ['CRAFTING', 'QUALITY_CHECK', 'PACKED'])) {
            return 'Processing';
        }
        return self::STATUS_LABELS[$status] ?? ucfirst(strtolower($this->status));
    }

    // Helper: is cancellable — Dynamic cancellation window configured in .env (ORDER_CANCELLATION_HOURS)
    // Only allowed while order is CONFIRMED and within configured hours of placement
    public function getIsCancellableAttribute(): bool
    {
        $hours = (int) config('atelier.cancellation_hours', env('ORDER_CANCELLATION_HOURS', 3));
        $inValidStatus = $this->status === self::STATUS_CONFIRMED;
        $withinLimit   = $this->created_at ? $this->created_at->diffInHours(now()) < $hours : false;

        return $inValidStatus && $withinLimit;
    }

    // Helper: total amount accessor for backward compatibility
    public function getTotalAmountAttribute(): float
    {
        return (float) ($this->grand_total ?? $this->subtotal ?? 0);
    }

    // Helper: is returnable — NO RETURNS (Handcrafted resin art & made-to-order)
    public function getIsReturnableAttribute(): bool
    {
        return false;
    }
}
