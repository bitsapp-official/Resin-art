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
                'title'            => 'Shipping & Delivery Policy',
                'hero_badge'       => 'SECURE INSURED TRANSIT',
                'hero_label'       => 'How we securely package & dispatch your fine jewellery.',
                'meta_title'       => 'Shipping & Delivery Policy — Solitaire Fine Jewels',
                'meta_description' => 'Learn how Solitaire Fine Jewels ships certified diamond and gold jewellery with 100% insured delivery worldwide.',
                'content'          => '<h2>Complimentary Insured Shipping Worldwide</h2>
<p>Every Solitaire Fine Jewels piece is dispatched via tamper-evident, fully insured white-glove courier services. Your shipment is 100% insured against loss or damage until handed over to you directly.</p>

<h2>Dispatch Timeline</h2>
<p>Ready-to-ship catalog pieces dispatch within <strong>24–48 business hours</strong> following laboratory verification. Custom bespoke solitaires and customized ring sizes require <strong>7–14 working days</strong> for master casting and precision micro-setting.</p>

<h2>Vault Packaging & Security</h2>
<p>Each jewel arrives nested in a custom plush velvet presentation case, accompanied by your tamper-sealed physical Certificate of Authenticity (IGI/GIA) and BIS Hallmarking guarantee card, enclosed in a discreet outer security parcel.</p>

<h2>Tracking & Signature on Delivery</h2>
<p>Once dispatched, you will receive real-time SMS and email tracking links. An adult signature and OTP verification are mandatory upon courier handover for your complete peace of mind.</p>

<h2>International Delivery</h2>
<p>We ship securely to collectors across the United States, United Kingdom, UAE, Europe, and Asia. For bespoke international logistics questions, connect with our concierge.</p>',
            ],
            [
                'slug'             => 'return',
                'title'            => 'Return & Exchange Policy',
                'hero_badge'       => 'ASSURANCE & GUARANTEE',
                'hero_label'       => 'Our commitment to diamond integrity.',
                'meta_title'       => 'Return & Exchange Policy — Solitaire Fine Jewels',
                'meta_description' => 'Solitaire Fine Jewels exchange, resizing, and lifetime authenticity guarantee for fine jewellery.',
                'content'          => '<h2>Lifetime Authenticity & Certificate Guarantee</h2>
<p>Every natural diamond and gold jewel crafted by Solitaire Fine Jewels is guaranteed authentic for life. Each piece is accompanied by an independent gemological laboratory certificate (IGI / GIA) and official BIS Hallmark imprint.</p>

<h2>Complimentary Ring Resizing</h2>
<p>We provide one <strong>complimentary ring resizing</strong> within 30 days of receiving your bespoke piece to ensure an absolute, comfortable fit on your finger.</p>

<h2>Cancellation Window</h2>
<p>Orders may be cancelled or modified within <strong>24 hours of placement</strong>. Following this window, precious metal alloy casting and customized stone setting commence.</p>

<h2>15-Day Exchange Guarantee</h2>
<p>Catalog pieces in pristine, unworn condition with original laboratory certification cards and tamper tags intact may be exchanged within 15 days of delivery.</p>

<h2>Damaged or Setting Inquiries</h2>
<p>Every piece undergoes dual-stage microscopic inspection before dispatch. In the rare event of transit damage, contact our concierge within <strong>48 hours of receipt</strong> for immediate insured collection and resolution.</p>',
            ],
            [
                'slug'             => 'privacy',
                'title'            => 'Privacy & Data Protection',
                'hero_badge'       => 'CLIENT CONFIDENTIALITY',
                'hero_label'       => 'How we safeguard your personal & financial privacy.',
                'meta_title'       => 'Privacy Policy — Solitaire Fine Jewels',
                'meta_description' => 'Solitaire Fine Jewels privacy policy — how we collect, use, and protect your personal information with absolute confidentiality.',
                'content'          => '<h2>Strict Confidentiality</h2>
<p>We collect essential information required to fulfill your custom commission, including contact details, delivery addresses, and ring sizing measurements. We never sell or share client information with external third parties.</p>

<h2>Payment Security</h2>
<p>All financial transactions are encrypted through 256-bit bank-grade TLS and processed via PCI-DSS Level 1 compliant gateways. We do not store sensitive payment card details.</p>

<h2>Discreet Communication</h2>
<p>For surprise engagements and private milestones, our concierge offers discreet correspondence options so your celebration remains an intimate surprise.</p>

<h2>Your Rights</h2>
<p>You may request access to, modification of, or deletion of your stored account data at any time by contacting our concierge team.</p>',
            ],
            [
                'slug'             => 'terms',
                'title'            => 'Terms & Conditions',
                'hero_badge'       => 'MAISON AGREEMENTS',
                'hero_label'       => 'Terms governing your engagement with Solitaire Fine Jewels.',
                'meta_title'       => 'Terms & Conditions — Solitaire Fine Jewels',
                'meta_description' => 'Terms and conditions governing fine jewellery purchases, certifications, and commissions with Solitaire Fine Jewels.',
                'content'          => '<h2>Authenticity & Valuation</h2>
<p>All prices are listed in Indian Rupees (₹) inclusive of applicable GST taxes. Diamond carat weights and gold purities are guaranteed as stated on your accompanying certificate.</p>

<h2>Custom Commissions</h2>
<p>Bespoke custom designs approved by the client are crafted specifically to bespoke dimensions and specifications. Deposit terms and delivery milestones are established in your formal quote.</p>

<h2>Intellectual Property</h2>
<p>All jewellery design concepts, 3D CAD renders, editorial photography, and written materials remain the intellectual property of Solitaire Fine Jewels.</p>

<h2>Governing Law</h2>
<p>These terms are governed by the laws of India. Any disputes will be subject to the exclusive jurisdiction of the competent courts in Mumbai, India.</p>',
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
