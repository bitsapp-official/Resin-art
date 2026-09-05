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
        'name' => env('ATELIER_NAME', 'Maison Résine'),
        'subline' => 'Bordeaux Atelier',
        'address_line_1' => env('ATELIER_ADDRESS_1', '14 rue des Étoiles'),
        'address_line_2' => env('ATELIER_ADDRESS_2', '33000 Bordeaux, France'),
        'hours_weekdays' => env('ATELIER_HOURS_WEEKDAYS', 'By appointment · Tuesday – Saturday'),
        'hours_weekend' => env('ATELIER_HOURS_WEEKEND', '10h – 18h'),
        'appointments' => env('ATELIER_APPOINTMENTS', 'Atelier visits Thursdays, by appointment'),
        'email' => env('ATELIER_EMAIL', 'atelier@maisonresine.com'),
        'phone' => env('ATELIER_PHONE', '+91 98765 43210'),
        'whatsapp' => env('WHATSAPP_NUMBER', '919876543210'),
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

    'admin_email' => env('ADMIN_EMAIL', 'admin@maisonresine.com'),

    /*
    |--------------------------------------------------------------------------
    | Order Policy & Cancellation Window (in Hours)
    |--------------------------------------------------------------------------
    */
    'cancellation_hours' => (int) env('ORDER_CANCELLATION_HOURS', 3),
];
