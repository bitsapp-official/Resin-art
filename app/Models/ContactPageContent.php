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
            'hero_badge' => 'Jewellery Concierge',
            'hero_title' => 'Connect with our master jewellers.',
            'hero_subtitle' => 'Bespoke solitaires, diamond certifications, custom sizing or showroom appointments. We answer every inquiry promptly.',
            'workshop_label' => 'Atelier Showroom',
            'studio_address' => "Atelier Plaza, Fine Arts Quarter\nMumbai, India",
            'studio_hours' => "Monday – Saturday: 11:00 AM – 8:00 PM\nSunday: By appointment",
            'studio_email' => 'concierge@solitairefinejewels.com',
            'studio_phone' => '+91 98201 45678',
        ]);
    }
}
