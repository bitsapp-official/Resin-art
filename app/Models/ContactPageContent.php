<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPageContent extends Model
{
    protected $fillable = [
        'hero_badge',
        'hero_title',
        'hero_subtitle',
        'workshop_label',
        'studio_address',
        'studio_hours',
        'studio_email',
        'studio_phone',
    ];

    public static function getContent(): self
    {
        return static::firstOrCreate([], [
            'hero_badge' => 'Correspondence',
            'hero_title' => 'Write to the atelier.',
            'hero_subtitle' => 'Custom orders, trade inquiries, press or simply to say hello. We answer every inquiry within 24 hours.',
            'workshop_label' => 'Workshop',
            'studio_address' => "14 rue des Étoiles\n33000 Bordeaux, France",
            'studio_hours' => "By appointment · Tuesday – Saturday\n10h – 18h",
            'studio_email' => 'hello@maisonresine.co',
            'studio_phone' => '+33 5 56 00 00 00',
        ]);
    }
}
