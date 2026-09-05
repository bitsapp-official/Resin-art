<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;

class BlogCategory extends Model
{
    public const CACHE_KEY = 'blog_categories_active';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public static function getActiveCategories()
    {
        return Cache::remember(self::CACHE_KEY, 86400, function () {
            return static::active()->get();
        });
    }
}
