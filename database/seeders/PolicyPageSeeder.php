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
                'hero_badge'       => 'COMPLIMENTARY WORLDWIDE DELIVERY',
                'hero_label'       => '100% Insured white-glove courier dispatch to patrons worldwide.',
                'meta_title'       => 'Shipping & Delivery Policy — Solitaire Fine Jewels',
                'meta_description' => 'Solitaire Fine Jewels provides 100% free insured express shipping worldwide on all bespoke diamond and gold jewellery orders.',
                'content'          => '<h2>100% Free Worldwide Insured Shipping</h2>
<p>At Solitaire Fine Jewels, we believe the acquisition of exceptional fine jewellery should be effortless and uncompromising from inception to delivery. We proudly provide <strong>100% complimentary, fully insured express shipping on every single order worldwide</strong> — with zero shipping fees, zero handling surcharges, and zero hidden costs, whether delivered within India or internationally to collectors across the United States, United Kingdom, United Arab Emirates, Europe, Singapore, Australia, and beyond.</p>

<h2>Bespoke Made-to-Order Production Timeline</h2>
<p>Every creation from Solitaire Fine Jewels is a bespoke commission, individually cast and handcrafted to order according to your exact ring size, precious metal karat, and gemstone preferences. We do not mass-produce or warehouse pre-assembled inventory.</p>
<ul>
    <li><strong>Artisan Crafting & Setting:</strong> Please allow <strong>7 to 12 business days</strong> for precision 3D CAD modeling, lost-wax gold casting, microscopic diamond hand-setting, BIS hallmark laser engraving, and final optical inspection by our master gemologists.</li>
    <li><strong>Domestic Delivery (India):</strong> Dispatched via express armored courier (Blue Dart Apex / Sequel Logistics) with delivery typically within <strong>2 to 4 business days</strong> post-crafting.</li>
    <li><strong>International Delivery:</strong> Dispatched via high-security international priority couriers (Malca-Amit, Brinks, FedEx Priority International) with transit typically taking <strong>4 to 7 business days</strong> post-crafting.</li>
</ul>

<h2>100% Door-to-Door Transit Insurance</h2>
<p>Your piece is completely protected from the moment it leaves our atelier until it is physically placed into your hands. Every shipment carries comprehensive, 100% door-to-door transit insurance underwritten by leading global fine art and bullion insurers. In the unlikely event of transit loss, theft, or damage prior to handover, our maison assumes full financial liability.</p>

<h2>Discreet Security & Heirloom Presentation Packaging</h2>
<p>To safeguard your investment and preserve the excitement of surprise milestones, all orders arrive in discreet, unbranded exterior security vault boxes bearing no external indication of jewellery, diamonds, or luxury contents.</p>
<p>Inside the exterior security carton, you will discover:</p>
<ul>
    <li>A handcrafted, plush velvet presentation case nestled in our signature luxury gift box.</li>
    <li>The original physical Certificate of Authenticity issued by an independent gemological laboratory (IGI or GIA).</li>
    <li>The official BIS Hallmark guarantee card stating gold purity and HUID laser credentials.</li>
    <li>A complimentary micro-fiber optical cleaning cloth and fine jewellery care guide.</li>
    <li>An individual, tamper-evident security seal uniquely numbered and logged at our dispatch vault.</li>
</ul>

<h2>Mandatory Adult Signature & OTP Verification</h2>
<p>For your security and insurer compliance, all deliveries strictly require an adult recipient signature and government-issued photo ID verification. For domestic Indian deliveries, a secure one-time password (OTP) sent to your registered mobile number is mandatory for courier handover. Couriers are strictly prohibited from leaving shipments unattended, on doorsteps, or with unauthorized building attendants.</p>

<h2>International Customs, Duties & Export Documentation</h2>
<p>For international patrons, all shipments are accompanied by official diamond export declarations, commercial invoices, and gemological certificate duplicates to ensure swift customs clearance. Any local import duties, customs tariffs, or value-added taxes (VAT) levied by the destination country are the responsibility of the recipient, unless otherwise arranged with our private concierge prior to dispatch.</p>

<h2>Order Tracking & Dedicated Concierge Support</h2>
<p>As soon as your creation completes final gemological inspection and enters transit, you will receive real-time tracking credentials via email and WhatsApp. Our dedicated client concierge remains at your disposal around the clock to monitor transit milestones and answer any delivery questions.</p>',
            ],
            [
                'slug'             => 'return',
                'title'            => 'Return, Exchange & Cancellation Policy',
                'hero_badge'       => 'BESPOKE MADE-TO-ORDER POLICY',
                'hero_label'       => 'Handcrafted to individual specifications — Sell-only / Final sale commitment.',
                'meta_title'       => 'Return & Exchange Policy — Solitaire Fine Jewels',
                'meta_description' => 'Solitaire Fine Jewels made-to-order fine jewellery policy: strictly sell-only with no returns or exchanges, backed by lifetime authenticity guarantee.',
                'content'          => '<h2>Bespoke Made-to-Order Standard (Sell-Only / Final Sale)</h2>
<p>Solitaire Fine Jewels operates as an exclusive fine jewellery atelier. Every solitary ring, necklace, bracelet, and earring in our collection is <strong>strictly made to order</strong> — individually cast, handset, and finished specifically for each patron upon confirmation of order.</p>
<p>Because each piece is custom-commissioned to your precise finger size, gold purity (14KT, 18KT, 22KT, or Platinum), metal hue (Yellow Gold, Rose Gold, White Gold), and certified diamond selection, <strong>WE DO NOT ACCEPT RETURNS, EXCHANGES, OR REFUNDS. ALL SALES ARE FINAL.</strong></p>
<p>We operate on a pure sell-only model. Unlike mass-market commercial retailers, we do not stock mass-produced inventory or re-circulate pre-owned creations. When you commission a piece with Solitaire Fine Jewels, you receive a brand-new, untouched masterpiece crafted solely for you.</p>

<h2>Order Cancellation & Modification Window (3 Hours)</h2>
<p>We understand that decisions regarding fine jewellery require utmost consideration. We offer a strict <strong>3-hour window</strong> from the exact moment of order placement during which you may request an order cancellation or modification without penalty.</p>
<ul>
    <li>To cancel or modify an order within 3 hours, immediately contact our concierge at <a href="mailto:concierge@solitairefinejewels.com">concierge@solitairefinejewels.com</a> or via our official WhatsApp concierge channel with your order confirmation number.</li>
    <li><strong>After 3 hours:</strong> Precious metal alloys are allocated to the melting crucible, CAD files are locked for precision lost-wax casting, and gemstones are pulled from the vault. Once crafting commences, orders can neither be cancelled, modified, nor refunded under any circumstances.</li>
</ul>

<h2>Transit Damage & Delivery Discrepancies Protection</h2>
<p>Every completed jewel undergoes rigorous multi-stage inspection under 40x magnification by our senior gemologists before being vacuum-sealed into an individually numbered tamper-evident security vault pouch. In the extremely rare event that your package encounters damage during transit:</p>
<ul>
    <li><strong>Inspect Before Signing:</strong> Examine the exterior carton and tamper-evident seal upon courier handover. If the outer carton is visibly crushed, torn, opened, or resealed with non-original tape, you must <strong>refuse delivery immediately</strong> and instruct the courier to return it to sender.</li>
    <li><strong>Mandatory Unboxing Video Requirement:</strong> If you accept delivery, you must record an <em>unbroken, continuous unboxing video</em> from the moment of breaking the intact outer shipping label down to revealing the velvet box and jewel inside. This unboxing video is required by our transit insurers to process any transit claims.</li>
    <li><strong>24-Hour Notice:</strong> Any transit defect, damage, or package discrepancy must be reported to our concierge within <strong>24 hours of delivery</strong> along with the uncut unboxing video and clear high-resolution photographs.</li>
    <li><strong>Atelier Resolution:</strong> Upon verification by our gemological team, we will arrange complimentary insured return courier pick-up to restore, re-set, or re-craft your bespoke piece to flawless perfection at no additional charge.</li>
</ul>

<h2>Ring Sizing & Fit Advisory</h2>
<p>Because each ring is custom cast to your exact size specification, we strongly advise patrons to verify their exact ring size prior to ordering. You may utilize our comprehensive Ring Sizing Guide or connect with our concierge for guidance. If an adjustment is required post-delivery, we offer professional atelier resizing service for eligible solitaire designs (subject to design tolerances and nominal bench casting fees where applicable).</p>

<h2>Lifetime Authenticity & BIS Hallmarking Guarantee</h2>
<p>While our creations are non-returnable and non-exchangeable, your acquisition is backed by our unconditional <strong>Lifetime Authenticity & Craftsmanship Guarantee</strong>:</p>
<ul>
    <li><strong>100% Certified Diamonds:</strong> Every natural diamond solitaire is accompanied by an independent, internationally recognized laboratory certificate from <strong>IGI</strong> (International Gemological Institute) or <strong>GIA</strong> (Gemological Institute of America) confirming carat weight, color, clarity, and cut precision.</li>
    <li><strong>100% BIS Hallmarked Gold:</strong> Every piece is stamped with the official Bureau of Indian Standards (BIS) Hallmark, including the laser-engraved 6-digit Hallmark Unique Identification (HUID) code certifying gold purity.</li>
    <li><strong>Lifetime Cleaning & Prongs Inspection:</strong> Patrons are welcome to send their Solitaire Fine Jewels creations to our atelier at any time for complimentary professional ultrasonic cleaning, steam sanitization, and microscopic prong integrity inspection.</li>
</ul>',
            ],
            [
                'slug'             => 'privacy',
                'title'            => 'Privacy & Client Confidentiality',
                'hero_badge'       => 'CLIENT CONFIDENTIALITY',
                'hero_label'       => 'Safeguarding your personal and financial information with absolute discretion.',
                'meta_title'       => 'Privacy Policy — Solitaire Fine Jewels',
                'meta_description' => 'Solitaire Fine Jewels client confidentiality, data privacy, and secure encrypted checkout standards.',
                'content'          => '<h2>Our Commitment to Absolute Discretion</h2>
<p>At Solitaire Fine Jewels, we recognize that acquiring bespoke fine jewellery often involves intimate milestones, surprise celebrations, and significant personal investments. We hold client privacy and confidentiality in the highest regard. We never sell, rent, monetize, or publicly disclose our patrons’ personal information under any circumstances.</p>

<h2>Information We Collect</h2>
<p>To fulfill your bespoke commissions and ensure compliant transit, we collect essential information including:</p>
<ul>
    <li>Full legal name, contact telephone number, and email address.</li>
    <li>Delivery address, billing address, and government identification credentials where required by customs or bullion compliance regulations.</li>
    <li>Bespoke sizing specifications, engraving inscriptions, and milestone customization requests.</li>
</ul>

<h2>Discreet Communication & Surprise Protection</h2>
<p>If you are commissioning an engagement ring or milestone anniversary jewel intended as a surprise, our concierge provides discreet correspondence options. You may specify alternate email addresses, request correspondence via WhatsApp only, or designate confidential collection instructions so that your milestone remains a private, magical surprise.</p>

<h2>Bank-Grade Transaction Security</h2>
<p>All online financial transactions are encrypted utilizing 256-bit bank-grade TLS/SSL certificates and processed through PCI-DSS Level 1 compliant financial gateways. Solitaire Fine Jewels never stores, sees, or logs your complete credit card numbers, CVV security codes, or banking PINs.</p>

<h2>Discreet Courier Labeling</h2>
<p>To maintain absolute security in transit and prevent unauthorized attention, external courier shipping labels never display the words "Solitaire", "Diamonds", "Jewellery", or "Gold". Shipments are dispatched under a discreet corporate trade identifier.</p>

<h2>Data Rights & Contact</h2>
<p>You may request an extract, correction, or deletion of your personal account records at any time by contacting our Data Protection Officer at <a href="mailto:privacy@solitairefinejewels.com">privacy@solitairefinejewels.com</a>.</p>',
            ],
            [
                'slug'             => 'terms',
                'title'            => 'Terms & Conditions',
                'hero_badge'       => 'MAISON AGREEMENT',
                'hero_label'       => 'Terms governing bespoke commissions and purchases at Solitaire Fine Jewels.',
                'meta_title'       => 'Terms & Conditions — Solitaire Fine Jewels',
                'meta_description' => 'Terms and conditions governing fine jewellery purchases, certifications, and bespoke commissions with Solitaire Fine Jewels.',
                'content'          => '<h2>1. Acceptance of Terms</h2>
<p>By browsing, accessing, or placing a commission on the Solitaire Fine Jewels website, you agree to be bound by these Terms and Conditions, our Shipping Policy, and our Bespoke Made-to-Order Return Policy.</p>

<h2>2. Bespoke Made-to-Order Nature of Goods</h2>
<p>All jewellery items displayed on this website are bespoke, made-to-order creations. Placing an order constitutes a customized manufacturing contract between the patron and Solitaire Fine Jewels. Because raw precious metals and gemstones are permanently allocated and cast to your unique specifications, <strong>all orders are final sale and non-exchangeable</strong> once the 3-hour modification window has expired.</p>

<h2>3. Diamond & Precious Metal Specifications</h2>
<p>All diamond carat weights, color grades, and clarity ratings are certified according to international gemological standards established by the IGI or GIA. Gold purities are guaranteed in accordance with the Bureau of Indian Standards (BIS) Hallmarking scheme. Minor natural variances in finished metal gram weight (±0.15g) and diamond carat weight (±0.02ct) are standard in the fine jewellery bench casting industry and do not constitute a defect.</p>

<h2>4. Pricing & Taxation</h2>
<p>All prices displayed for domestic Indian delivery are quoted in Indian Rupees (₹) and include applicable Goods and Services Tax (GST). For international destinations, prices are billed in the selected currency, and any applicable local destination duties or import tariffs are the responsibility of the purchaser.</p>

<h2>5. Intellectual Property</h2>
<p>All jewellery designs, 3D CAD renders, proprietary mounting mechanisms, photographic assets, brand typography, and editorial content displayed on this website are the exclusive intellectual property of Solitaire Fine Jewels and are protected under international copyright and trademark laws. Unauthorized replication or commercial reproduction is strictly prohibited.</p>

<h2>6. Governing Law & Dispute Jurisdiction</h2>
<p>These terms and all bespoke commission agreements shall be governed by and construed in accordance with the laws of India. Any dispute, claim, or controversy arising hereunder shall be subject to the exclusive jurisdiction of the competent courts in Mumbai, Maharashtra, India.</p>',
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
