<?php

namespace App\Models;

use App\Enums\ProcessPageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProcessPage extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'our_process_published_page';

    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'cta_title',
        'cta_button_text',
        'cta_url',
        'status',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'status' => ProcessPageStatus::class,
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function getPublishedPage(): ?self
    {
        return Cache::remember(self::CACHE_KEY, 86400, function () {
            return self::query()
                ->published()
                ->with(['activeSteps'])
                ->first();
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProcessPageStatus::PUBLISHED->value);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class)->orderBy('sort_order', 'asc');
    }

    public function activeSteps(): HasMany
    {
        return $this->hasMany(ProcessStep::class)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }
}
