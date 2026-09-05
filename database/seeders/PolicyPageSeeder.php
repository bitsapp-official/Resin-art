<?php

namespace Database\Seeders;

use App\Models\PolicyPage;
use Illuminate\Database\Seeder;

class PolicyPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug'             => 'shipping',
                'title'            => 'Shipping Policy',
                'hero_badge'       => 'DISPATCH & DELIVERY',
                'hero_label'       => 'How we pack & ship your piece.',
                'meta_title'       => 'Shipping Policy — Maison Résine',
                'meta_description' => 'Learn how Maison Résine ships handcrafted resin art pieces safely across India.',
                'content'          => '<h2>Free Insured Shipping Across India</h2>
<p>Every Maison Résine piece ships free with complimentary insured delivery across India. We partner with premium logistics providers to ensure your piece arrives safely.</p>

<h2>Dispatch Timeline</h2>
<p>Standard pieces ship within <strong>24–48 business hours</strong> of order confirmation. Custom made-to-order pieces ship within <strong>7–21 working days</strong> depending on complexity and curing time.</p>

<h2>Packaging</h2>
<p>All pieces are individually wrapped in archival tissue, nested in custom-cut foam inserts, and dispatched in double-walled corrugated boxes. Large works and river tables ship in bespoke wooden crates.</p>

<h2>Tracking</h2>
<p>Once dispatched, you will receive an email with your AWB tracking number. You can track your shipment directly from your account dashboard under <em>My Orders</em>.</p>

<h2>International Shipping</h2>
<p>We currently ship only within India. For international inquiries, please write to us via our <a href="/contact">contact page</a>.</p>',
            ],
            [
                'slug'             => 'return',
                'title'            => 'Return & Cancellation Policy',
                'hero_badge'       => 'CANCELLATIONS & RETURNS',
                'hero_label'       => 'Important before you order.',
                'meta_title'       => 'Return & Cancellation Policy — Maison Résine',
                'meta_description' => 'Maison Résine cancellation and return policy for handcrafted resin art pieces.',
                'content'          => '<h2>3-Hour Cancellation Window</h2>
<p>Orders may be cancelled within <strong>3 hours of placement</strong>. Once 3 hours have passed, bespoke curing and crafting has already begun and the order cannot be cancelled.</p>
<p>To cancel within the window, visit <em>My Orders</em> in your account and click <strong>"Cancel Order"</strong>. Refunds are processed to your original payment method within 5–7 business days.</p>

<h2>No Returns Policy</h2>
<p>As all Maison Résine pieces are <strong>handcrafted and made-to-order</strong>, we do not accept returns once an order has been dispatched. Each piece is unique and created specifically for your order.</p>
<p>This policy reflects the nature of bespoke artisan work — no two pours are ever identical, and materials are sourced and used for your specific piece.</p>

<h2>Damaged or Defective Pieces</h2>
<p>If your piece arrives damaged due to a logistics failure, please photograph the damage and contact us within <strong>48 hours of delivery</strong>. We will arrange a replacement or refund at no additional cost.</p>

<h2>Custom Orders & Made-to-Order Pieces</h2>
<p>Custom orders are non-refundable and non-cancellable once the design brief has been confirmed and crafting has commenced, as timber slabs and materials are prepared specifically for your piece.</p>',
            ],
            [
                'slug'             => 'privacy',
                'title'            => 'Privacy Policy',
                'hero_badge'       => 'DATA INTEGRITY',
                'hero_label'       => 'How we handle your information.',
                'meta_title'       => 'Privacy Policy — Maison Résine',
                'meta_description' => 'Maison Résine privacy policy — how we collect, use, and protect your personal data.',
                'content'          => '<h2>Information We Collect</h2>
<p>We collect information you provide directly, including your name, email address, shipping address, and payment details when you place an order or create an account.</p>

<h2>How We Use Your Information</h2>
<p>Your information is used to process orders, manage your account, communicate about your purchase, and improve our services. We do not sell your personal data to third parties.</p>

<h2>Payment Security</h2>
<p>All payments are processed through secure, PCI-DSS compliant payment gateways. We do not store your full card details on our servers.</p>

<h2>Cookies</h2>
<p>We use essential cookies to keep your cart session active and optional analytics cookies (which you may decline) to understand how visitors use our site.</p>

<h2>Your Rights</h2>
<p>You may request access to, correction of, or deletion of your personal data at any time by writing to us at our contact email. We will respond within 30 days.</p>

<h2>Data Retention</h2>
<p>We retain your order data for 7 years to comply with Indian tax and accounting regulations. Account data is deleted upon account closure.</p>',
            ],
            [
                'slug'             => 'terms',
                'title'            => 'Terms & Conditions',
                'hero_badge'       => 'ATELIER AGREEMENTS',
                'hero_label'       => 'Terms of engaging with Maison Résine.',
                'meta_title'       => 'Terms & Conditions — Maison Résine',
                'meta_description' => 'Terms and conditions governing purchases and use of Maison Résine website and services.',
                'content'          => '<h2>Acceptance of Terms</h2>
<p>By accessing or placing an order on this website, you agree to be bound by these terms and conditions. If you do not agree, please do not use this site.</p>

<h2>Products & Pricing</h2>
<p>All prices are listed in Indian Rupees (₹) and include applicable taxes. We reserve the right to amend prices at any time. Orders confirmed at a specific price will honour that price.</p>

<h2>Intellectual Property</h2>
<p>All photographs, artwork, design concepts, and written content on this website are the intellectual property of Maison Résine. Reproduction without written permission is prohibited.</p>

<h2>Limitation of Liability</h2>
<p>Maison Résine is not liable for indirect or consequential losses arising from the use of our website or products beyond the original purchase price paid.</p>

<h2>Governing Law</h2>
<p>These terms are governed by the laws of India. Any disputes will be subject to the exclusive jurisdiction of courts in India.</p>

<h2>Changes to Terms</h2>
<p>We reserve the right to update these terms at any time. Material changes will be communicated to registered account holders via email.</p>',
            ],
        ];

        foreach ($pages as $page) {
            PolicyPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
