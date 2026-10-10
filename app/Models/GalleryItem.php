<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class GalleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_category_id',
        'title',
        'slug',
        'description',
        'media_type',
        'video_url',
        'image_path',
        'image_alt',
        'caption',
        'location',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    /**
     * Extract YouTube Video ID from any standard YouTube URL (watch, shorts, embed, youtu.be).
     */
    public function getYoutubeIdAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $pattern = '/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:embed\/|v\/|shorts\/|watch\?(?:.*&)?v=))([\w-]{11})/i';
        if (preg_match($pattern, trim($this->video_url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Return standard YouTube embed URL with optimal parameters.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $ytId = $this->youtube_id;
        if ($ytId) {
            return "https://www.youtube.com/embed/{$ytId}?autoplay=1&rel=0&playsinline=1&enablejsapi=1";
        }
        return null;
    }

    /**
     * Extract Vimeo Video ID if applicable.
     */
    public function getVimeoIdAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        if (preg_match('/(?:vimeo\.com\/(?:video\/)?|player\.vimeo\.com\/video\/)(\d+)/i', trim($this->video_url), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Return Vimeo embed URL if applicable.
     */
    public function getVimeoEmbedUrlAttribute(): ?string
    {
        $vimeoId = $this->vimeo_id;
        if ($vimeoId) {
            return "https://player.vimeo.com/video/{$vimeoId}?autoplay=1&dnt=1&playsinline=1";
        }
        return null;
    }

    /**
     * Return unified video embed URL (YouTube or Vimeo).
     */
    public function getVideoEmbedUrlAttribute(): ?string
    {
        return $this->youtube_embed_url ?? $this->vimeo_embed_url ?? null;
    }

    /**
     * Check whether this gallery item is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return ($this->media_type === 'video' || !empty($this->video_url)) && !empty($this->video_embed_url);
    }

    /**
     * Resolve effective thumbnail URL (uploaded cover or auto YouTube/Vimeo poster).
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->is_video && $this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }

        if (!empty($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        if ($this->vimeo_id) {
            return "https://vumbnail.com/{$this->vimeo_id}.jpg";
        }

        return asset('images/placeholder-art.jpg');
    }

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function ($item) {
            if ($item->media_type === 'image') {
                $item->video_url = null;
            } elseif ($item->media_type === 'video' || !empty($item->video_url)) {
                $item->media_type = 'video';
                $item->image_path = null;
            }
        });

        static::saved(function () {
            Cache::forget('gallery_active_categories');
        });

        static::deleted(function () {
            Cache::forget('gallery_active_categories');
        });
    }

    public function galleryCategory(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}
