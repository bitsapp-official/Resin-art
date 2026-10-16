<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Studio Atelier Metadata
    |--------------------------------------------------------------------------
    |
    | Information displayed on the contact page and correspondence templates.
    | Exactly matching the Maison Résine atelier reference website.
    |
    */

    'studio' => [
        'name' => env('ATELIER_NAME', 'Solitaire Fine Jewels'),
        'subline' => 'Fine Jewellery Atelier',
        'address_line_1' => env('ATELIER_ADDRESS_1', 'Atelier Plaza, Fine Arts Quarter'),
        'address_line_2' => env('ATELIER_ADDRESS_2', 'Mumbai, India'),
        'hours_weekdays' => env('ATELIER_HOURS_WEEKDAYS', 'Monday – Saturday · 11:00 AM – 8:00 PM'),
        'hours_weekend' => env('ATELIER_HOURS_WEEKEND', 'Sunday by appointment'),
        'appointments' => env('ATELIER_APPOINTMENTS', 'Private showroom consultations by appointment'),
        'email' => env('ATELIER_EMAIL', 'concierge@solitairefinejewels.com'),
        'phone' => env('ATELIER_PHONE', '+91 98201 45678'),
        'whatsapp' => env('WHATSAPP_NUMBER', '919820145678'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Inquiry Security & Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limit' => [
        'max_submissions' => (int) env('CONTACT_RATE_LIMIT_SUBMISSIONS', 5),
        'decay_minutes' => (int) env('CONTACT_RATE_LIMIT_MINUTES', 10),
    ],

    'admin_email' => env('ADMIN_EMAIL', 'concierge@solitairefinejewels.com'),

    /*
    |--------------------------------------------------------------------------
    | Order Policy & Cancellation Window (in Hours)
    |--------------------------------------------------------------------------
    */
    'cancellation_hours' => (int) env('ORDER_CANCELLATION_HOURS', 3),
];
