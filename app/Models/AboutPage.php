<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class AboutPage extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'maison_resine_about_page_published';

    protected $fillable = [
        'eyebrow',
        'hero_title',
        'hero_description',
        'hero_image',
        'hero_image_alt',
        'founder_quote',
        'founder_name',
        'story_eyebrow',
        'story_title',
        'story_content',
        'materials_content',
        'visit_cta_text',
        'visit_cta_url',
        'seo_title',
        'seo_description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
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

    public static function getPublished(): ?self
    {
        return Cache::remember(self::CACHE_KEY, 86400, function () {
            return self::query()
                ->where('is_published', true)
                ->with(['activeValues', 'activeTimelineSteps', 'activeArtisans'])
                ->first();
        });
    }

    public function values(): HasMany
    {
        return $this->hasMany(AboutValue::class)->orderBy('sort_order', 'asc');
    }

    public function activeValues(): HasMany
    {
        return $this->hasMany(AboutValue::class)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }

    public function timelineSteps(): HasMany
    {
        return $this->hasMany(AboutTimelineStep::class)->orderBy('sort_order', 'asc');
    }

    public function activeTimelineSteps(): HasMany
    {
        return $this->hasMany(AboutTimelineStep::class)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }

    public function artisans(): HasMany
    {
        return $this->hasMany(AboutArtisan::class)->orderBy('sort_order', 'asc');
    }

    public function activeArtisans(): HasMany
    {
        return $this->hasMany(AboutArtisan::class)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }
}
