<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'hero_badge',
        'hero_label',
        'content',
        'meta_title',
        'meta_description',
    ];

    /**
     * Find a policy page by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * The 4 canonical policy page definitions used for seeding & navigation.
     */
    public static function canonicalPages(): array
    {
        return [
            'shipping'     => 'Shipping Policy',
            'return'       => 'Return & Cancellation Policy',
            'privacy'      => 'Privacy Policy',
            'terms'        => 'Terms & Conditions',
        ];
    }
}
