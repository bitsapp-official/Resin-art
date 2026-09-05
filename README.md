# Resin Art — Handcrafted Epoxy & Wood Atelier E-Commerce Platform

<div align="center">

![Resin Art Banner](https://raw.githubusercontent.com/sandip-rathod-2006/resin-art/main/public/images/catalog/resin_wall_clock.png)

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel Framework](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament Admin](https://img.shields.io/badge/Filament-3.x-F59E0B?style=for-the-badge&logo=filament&logoColor=white)](https://filamentphp.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Vite Bundler](https://img.shields.io/badge/Vite-5.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![Stripe Integrated](https://img.shields.io/badge/Stripe-Payment_Gateway-635BFF?style=for-the-badge&logo=stripe&logoColor=white)](https://stripe.com)

**An ultra-premium, direct-to-consumer e-commerce boutique and bespoke commission engine dedicated to handcrafted epoxy resin and reclaimed timber art pieces.**

[Features](#-core-capabilities--architectural-modules) • [Tech Stack](#-technology-stack) • [Architecture](#-directory-architecture) • [Getting Started](#-getting-started--local-development) • [Admin Console](#-administrative-atelier-command-center) • [Security](#-security--validation-standards)

</div>

---

## 🏛️ Executive Summary

**Resin Art** is an enterprise-grade digital atelier designed to present and sell fine handcrafted resin works — including live-edge river tables, celestial wall clocks, oceanic serving coasters, and custom architectural installations. 

Engineered with architectural rigor, the platform blends a high-converting **B2C retail boutique** with a **bespoke commission portal**, backed by a full **Filament v3 administrative command center** for inventory, custom quoting, dynamic invoice generation, and customer relationship management.

---

## ✨ Core Capabilities & Architectural Modules

### 1. 💎 Haute E-Commerce Retail Boutique
* **Editorial Catalog & Filtering:** Fluid browsing across curated collections (Wall Art, River Tables, Ocean Decor, Coasters) with real-time stock availability badges.
* **Artisan Product Detail Experience:** Full-width high-resolution imagery, zoom previews, dimension specifications, craftsmanship stories, and customer review showcases.
* **Slide-Out Cart Drawer:** Real-time state synchronization via Alpine.js, dynamic line-item updates, and Pan-India complimentary shipping calculation.
* **One-Page Frictionless Checkout:** Pre-filled authenticated customer profiles, multi-address book selection, automated delivery estimates, and full Stripe 3D Secure payment integration.

### 2. 🎨 Bespoke Custom Commission Portal (`/custom`)
* **Guided Design Intake:** Clients specify custom dimensions, wood species (Walnut, Teak, Oak), resin tint palettes, and aesthetic goals.
* **Photo Reference Dropzone:** Client-side preview, validation (JPEG, PNG, WebP up to 5MB), and high-visibility delete management.
* **Secure Private Media Storage:** Client reference attachments stored outside public roots, served exclusively through authorized, signed streaming routes.
* **Public Tracking Reference Engine:** Automatically generates immutable, human-friendly references (e.g., `CR-2026-XXXX`) for instant lookup without requiring account login.
* **Omnichannel Communication Touchpoints:** High-contrast direct WhatsApp chat button with official branding and pre-composed commission inquiry templates.

### 3. ⚙️ Administrative Atelier Command Center (Filament v3)
* **Order Lifecycle Orchestration:** Comprehensive tracking from *Pending Payment* to *In Production*, *Shipped*, and *Delivered*.
* **Custom Commission Quoting Suite:**
  * Convert customer requirements into itemized digital quotes.
  * Define production milestones, advance deposit terms, and completion windows.
  * Automated email notifications with secure download links.
* **Dynamic PDF Generation Engine:**
  * Clean, vector-sharp PDF generation for Quotes, Customer Invoices, and Official Certificates of Authenticity using Barryvdh DomPDF.
* **Content & Policy CMS:** Manage hero slides, brand trust badges, artisan profiles, workshop timelines, and legal policies without code deployments.
* **Contact Correspondence Hub:** Review inbound customer letters, internal staff notes, and send direct branded email replies from the dashboard.

---

## 🛠️ Technology Stack

| Domain | Technology | Purpose / Role |
|---|---|---|
| **Backend Core** | PHP 8.2+ / Laravel 11.x | Robust MVC foundations, Eloquent ORM, transactions, service layer |
| **Admin Panel** | Filament v3 / Livewire 3 | Reactive administrative command center, resource tables, and form builders |
| **Frontend Styling** | Tailwind CSS 3.4 | Tailored luxury design tokens, typography, and micro-interactions |
| **Client Interactivity**| Alpine.js 3.x | Lightweight, zero-overhead client reactivity for drawers, modals, and uploaders |
| **Asset Pipeline** | Vite 5.x | Instant HMR development and optimized production asset minification |
| **Database** | SQLite (Dev) / MySQL / PostgreSQL | Relational storage with foreign key constraints, indexes, and full migrations |
| **Payment Gateway** | Stripe SDK (v16.x) | Hosted checkout sessions, 3D Secure compliance, and idempotent webhook listeners |
| **PDF Rendering** | Barryvdh Laravel-DomPDF | High-dpi printable vector invoices, formal quotes, and receipts |
| **Testing Suite** | PHPUnit 11 / Pest Compatible | Automated feature, integration, and security regression test suite |

---

## 🔒 Security & Validation Standards

* **Enterprise Indian Mobile Validation:** Standardized `IndianPhoneNumber` validation rule across all checkout, address, profile, and custom commission touchpoints. Strictly enforces valid 10-digit subscriber patterns (`[6-9]XX...`), rejects dummy numbers (e.g., `12345678`, `9876543210`, repeating digits), and normalizes country code prefixes (`+91`).
* **Anti-Spam Honeypot Traps:** Silent rejection of automated bot submissions on public contact and commission forms.
* **Proactive CSRF Token Lifespan Management:** Automatic background heartbeat refresh on active forms preventing session expiration during lengthy design descriptions.
* **Protected File Access:** Uploaded commission references are quarantined in non-public storage disks and accessible only via authorized middleware.
* **Rate Limiting:** Throttle limits applied to contact submissions, checkout endpoints, and search queries.

---

## 📁 Directory Architecture

```
resin-art/
├── app/
│   ├── Enums/                 # CustomRequestStatus, OrderStatus, InquiryType
│   ├── Filament/              # Filament Resources, Pages, Widgets & Topbar
│   ├── Http/
│   │   ├── Controllers/       # Shop, Checkout, CustomRequest, Contact, Order controllers
│   │   ├── Middleware/        # Authenticated, Verified, and Access control middleware
│   │   └── Requests/          # Form request validations (Contact, Checkout, Custom)
│   ├── Mail/                  # Mailable classes for order and inquiry notifications
│   ├── Models/                # Eloquent models (Product, Order, CustomRequest, SiteSetting, etc.)
│   └── Rules/                 # Custom validation rules (IndianPhoneNumber)
├── config/                    # Atelier settings, services, payment & mail configs
├── database/
│   ├── factories/             # Model factories for realistic test generation
│   ├── migrations/            # Complete schema migrations
│   └── seeders/               # Baseline seed data (Products, Settings, Collections)
├── public/                    # Webroot, compiled Vite assets, icons, and entry point
├── resources/
│   ├── css/                   # Global stylesheet and custom typography rules
│   ├── js/                    # Core Alpine components and app bootstrapping
│   └── views/                 # Blade views organized by domain (Shop, Custom, Account, PDF)
├── routes/
│   ├── web.php                # Public, authenticated customer, and download routes
│   └── console.php            # Scheduled maintenance and artisan routines
└── tests/
    └── Feature/               # Automated feature tests covering orders, customs, and auth
```

---

## 🚀 Getting Started & Local Development

### Prerequisites
* **PHP >= 8.2** with `sqlite3`, `mbstring`, `openssl`, `curl`, `gd` extensions enabled
* **Composer 2.x**
* **Node.js >= 18.x** and **npm**

### Step-by-Step Installation

1. **Clone the repository:**
   ```bash
   git clone git@github.com:bitsapp-official/resin-art.git
   cd resin-art
   ```

2. **Install PHP and Node dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment Variables:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Database Migrations & Seed Baseline Data:**
   ```bash
   php artisan migrate --seed
   ```

5. **Link Public Storage:**
   ```bash
   php artisan storage:link
   ```

6. **Build Frontend Assets:**
   ```bash
   npm run build
   ```

7. **Start the Local Development Server:**
   ```bash
   php artisan serve
   ```
   Visit the storefront at `http://127.0.0.1:8000`.

---

## 🛡️ Quality Assurance & Automated Testing

The platform includes comprehensive PHPUnit tests covering checkout flows, commission submission, phone validation rules, order status state machines, and access control:

```bash
# Run all automated tests
php artisan test

# Run specific feature suites
php artisan test --filter=CustomRequestTest
php artisan test --filter=CheckoutTest
php artisan test --filter=ProfileAddressPhoneTest
```

---

## 👨‍💻 Project Governance

* **Platform:** Resin Art Atelier
* **Lead Engineer:** Sandip Rathod (`sandip.rathod@bitsapp.in`)
* **Organization:** Bitsapp Technologies
* **Status:** Production-Ready & Actively Maintained

---

<div align="center">
  <sub>Crafted with precision for fine art connoisseurs. Resin Art © 2026. All rights reserved.</sub>
</div>
