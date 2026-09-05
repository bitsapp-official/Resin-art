<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_page_id',
        'step_number',
        'title',
        'description',
        'image_path',
        'image_alt',
        'image_caption',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function ($step) {
            ProcessPage::clearCache();
        });

        static::deleted(function ($step) {
            ProcessPage::clearCache();
        });
    }

    public function processPage(): BelongsTo
    {
        return $this->belongsTo(ProcessPage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedStepNumberAttribute(): string
    {
        if (!empty($this->step_number)) {
            $cleaned = preg_replace('/[^0-9]/', '', $this->step_number);
            if (!empty($cleaned)) {
                return sprintf('%02d', (int) $cleaned);
            }
            return strtoupper($this->step_number);
        }

        return sprintf('%02d', max(1, (int) $this->sort_order));
    }
}
