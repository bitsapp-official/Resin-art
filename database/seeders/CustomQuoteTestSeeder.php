<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomRequest;
use App\Models\CustomQuote;
use App\Models\CustomQuoteItem;
use App\Enums\CustomRequestStatus;
use App\Enums\CustomQuoteStatus;
use App\Enums\DepositType;

class CustomQuoteTestSeeder extends Seeder
{
    public function run(): void
    {
        $req = CustomRequest::create([
            'public_reference'  => CustomRequest::generateReference(),
            'name'              => 'Priya Sharma',
            'email'             => 'priya.sharma@gmail.com',
            'phone'             => 'Flat 402, Royale Palms, Bandra West, Mumbai 400050',
            'whatsapp'          => '+91 98765 43210',
            'project_type'      => 'Custom Artwork',
            'quantity'          => 1,
            'timeline_type'     => 'Flexible',
            'idea_description'  => 'Looking for a custom ocean resin wall art piece for our living room wall. Approximately 4x2.5 feet, with deep turquoise fluid resin waves, white seafoam, and subtle gold leaf accents to match our beige interior.',
            'status'            => CustomRequestStatus::QUOTE_PREPARATION,
            'submitted_at'      => now(),
        ]);

        $quote = CustomQuote::create([
            'custom_request_id'    => $req->id,
            'quote_reference'      => CustomQuote::generateReference(),
            'status'               => CustomQuoteStatus::DRAFT,
            'valid_until'          => now()->addDays(7),
            'estimated_completion' => '18-22 working days after design approval',
            'shipping_amount'      => 350,
            'tax_amount'           => 0,
            'discount_amount'      => 0,
            'deposit_type'         => DepositType::PERCENTAGE,
            'deposit_amount'       => 50,
            'subtotal'             => 7500,
            'total_amount'         => 7850,
            'notes'                => "50% advance required to begin production.\nBalance payment before dispatch.\nCustom resin pieces are non-refundable once production begins.",
        ]);

        CustomQuoteItem::create([
            'custom_quote_id' => $quote->id,
            'description'     => 'Ocean Resin Wall Art - 60 x 40 cm (Custom Design)',
            'quantity'        => 1,
            'unit_price'      => 6500,
            'total'           => 6500,
            'sort_order'      => 1,
        ]);

        CustomQuoteItem::create([
            'custom_quote_id' => $quote->id,
            'description'     => 'Premium Birch Backing Board + Hanging Hardware',
            'quantity'        => 1,
            'unit_price'      => 700,
            'total'           => 700,
            'sort_order'      => 2,
        ]);

        CustomQuoteItem::create([
            'custom_quote_id' => $quote->id,
            'description'     => 'Luxury Packaging with Protective Wrapping',
            'quantity'        => 1,
            'unit_price'      => 300,
            'total'           => 300,
            'sort_order'      => 3,
        ]);

        $quote->recalculateTotals();

        $this->command->info("TEST DATA CREATED:");
        $this->command->info("Request: " . $req->public_reference);
        $this->command->info("Quote:   " . $quote->quote_reference);
        $this->command->info("Quote ID: " . $quote->id);
        $this->command->info("Total:   Rs." . $quote->total_amount);
        $this->command->info("PDF URL: http://127.0.0.1:8000/admin/quotes/" . $quote->id . "/pdf");
    }
}
