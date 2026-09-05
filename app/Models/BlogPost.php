<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\BlogPostStatus;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_name',
        'reading_time',
        'status',
        'published_at',
        'seo_title',
        'seo_description',
        'og_image',
    ];

    protected $casts = [
        'status' => BlogPostStatus::class,
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug) && !empty($post->title)) {
                $post->slug = Str::slug($post->title);
            }

            if (empty($post->reading_time) && !empty($post->content)) {
                $post->reading_time = static::calculateReadingTime($post->content);
            }
        });

        static::saved(function () {
            BlogCategory::clearCache();
        });

        static::deleted(function () {
            BlogCategory::clearCache();
        });
    }

    public static function calculateReadingTime(string $text): string
    {
        $cleanText = strip_tags($text);
        $wordCount = str_word_count($cleanText);
        $minutes = max(1, (int) ceil($wordCount / 200));

        return "{$minutes} MIN";
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', BlogPostStatus::PUBLISHED->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
