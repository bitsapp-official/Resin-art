# Stripe Production Architecture & Integration Manual
**Application:** Maison Résine Atelier (Luxury Resin Art eCommerce)  
**Framework:** Laravel 11.x, PHP 8.2+  
**Payment Gateway:** Stripe Payments / Stripe Checkout (Hosted Mode)  
**Target Markets:** Domestic India (INR) & International Global Collectors  
**Compliance Standard:** Level 1 PCI-DSS SAQ-A Compliant, RBI Foreign Exchange Regulations  

---

## 1. Executive Summary & Production Architecture

Maison Résine Atelier sells bespoke, high-value handcrafted resin artwork ranging from ₹15,000 to ₹1,50,000+. To guarantee high trust, zero cardholder data liability, and seamless multi-currency support, the atelier utilizes **Stripe Checkout (Hosted)** with server-side authoritative state management and dual-channel settlement.

### High-Level Architecture Flowchart

```
Client (Browser)                 Maison Résine Server                  Stripe Platform
      │                                    │                                  │
      ├── 1. POST /checkout/process ──────>│                                  │
      │   (Contact & Address Data)         ├── 2. DB Stock & Price Recalc     │
      │                                    ├── 3. Create PENDING Order        │
      │                                    ├── 4. sessions.create() ─────────>│
      │                                    │<── Returns session.url ──────────┤
      │<── 5. Redirect to Stripe URL ──────┤                                  │
      │                                    │                                  │
      ├── 6. Customer Enters Card & OTP ─────────────────────────────────────>│
      │                                    │                                  │
      │                                    │<── 7. POST /webhook/stripe ──────┤
      │                                    │   (Event: session.completed)     │
      │                                    ├── 8. Verify Raw Signature        │
      │                                    ├── 9. Check Webhook Idempotency   │
      │                                    ├── 10. lockForUpdate Order Row    │
      │                                    ├── 11. Deduct Stock & Confirm     │
      │                                    ├── 12. Send Invoice Mail & PDF    │
      │                                    ├── 13. Empty Patron Cart          │
      │                                    │─── Return 200 OK ───────────────>│
      │                                    │                                  │
      │<── 14. Redirect to confirmation ───┤                                  │
      │   (?session_id=cs_...)             ├── 15. Verify Session (if webhook │
      │                                    │       was delayed/concurrent)   │
      │                                    └── 16. Render Luxury Confirmation │
```

---

## 2. Dual-Channel Settlement & Deduplication

To prevent race conditions, duplicate stock deduction, or missed orders (e.g. customer closes mobile tab right after bank OTP):

1. **Channel A — Direct Redirect Verification (`CheckoutController::confirmation`)**:
   - Customer returns with `?session_id={CHECKOUT_SESSION_ID}`.
   - Server retrieves session via Stripe API.
   - If `payment_status === 'paid'`, delegates to `OrderFulfillmentService::fulfill()`.
2. **Channel B — Asynchronous Webhook (`StripeWebhookController::handle`)**:
   - Stripe sends `checkout.session.completed` event to `POST /webhook/stripe`.
   - Webhook verifies signature using raw `$request->getContent()`.
   - Verifies event ID against durable `stripe_webhook_events` table.
   - Delegates to `OrderFulfillmentService::fulfill()`.
3. **Database Concurrency & Row Locking**:
   - `OrderFulfillmentService::fulfill()` opens an atomic DB transaction with `Order::where('id', $order->id)->lockForUpdate()->first()`.
   - If order is already `paid` and `CONFIRMED`, it safely returns without repeating stock deduction or email dispatch.
   - Single inventory decrement is strictly guaranteed.

---

## 3. Stripe India Rules & Domestic Mandates

### 3.1 Domestic Indian Transactions
* **Currency Requirement**: Under Reserve Bank of India (RBI) regulations, all transactions where both merchant and buyer are in India **MUST** be initiated and settled in **Indian Rupees (`INR`)**.
* **Supported Cards on Stripe India Accounts**:
  - Visa
  - Mastercard
  - American Express
* **Unsupported Payment Methods for Stripe India Domestic**:
  - **RuPay**: Standard Stripe India accounts do not support RuPay domestic cards.
  - **UPI & Netbanking**: Not supported on Stripe India accounts.
  - **Apple Pay & Google Pay**: Not supported for domestic INR payments on Stripe India accounts.
* **UI Rule**: The checkout UI strictly displays **Visa, Mastercard, and American Express** badges with clean, customer-facing trust text ("Secure Payment", "Your payment is securely processed by our payment provider", "Payments securely processed by Stripe"). Raw encryption technicalities (e.g. "256-bit AES") and unsupported method badges are completely excised.

### 3.2 International Cross-Border Payments
* **Currency**: Charged in INR (or merchant base currency). Foreign cards (US, UK, EU, UAE, Australia) automatically convert INR to the collector's billing currency via card issuer interbank rates.
* **Stripe Adaptive Pricing**: Enabled in Stripe Dashboard (`Settings > Payments > Adaptive Pricing`) to display prices in 135+ local currencies (USD, EUR, GBP) dynamically without modifying codebase.
* **Mandatory Export Purpose Code**: **P0103** (*Export of goods - other than capital goods / gems and jewelry*). Configured in Stripe Dashboard under Export Details.
* **DGFT Import Export Code (IEC)**: Required for commercial physical parcel exports leaving India.
* **Zero-Rated GST / LUT**: Physical artwork exports qualify as zero-rated supply with Letter of Undertaking (LUT).

---

## 4. Authoritative Pricing & Security Hardening

### 4.1 Server-Side Price & Total Calculation
* Client parameters (`amount`, `total`, `currency`) are **never trusted**.
* During `POST /checkout/process`, the server iterates through every cart item, fetches authentic pricing from the database (`Product::effective_price` and variant matrices), recomputes subtotal, calculates GST/tax according to `SiteSetting`, and derives the grand total.
* Cart prices in the database are automatically synchronized to prevent client-side inspection manipulation.

### 4.2 Removal of Simulation Mode
* Previous developer simulation fallback (which marked orders paid when Stripe keys were missing) has been **100% removed**.
* In the absence of valid Stripe API keys, the server logs an error and returns a clean, reassuring customer error message. Unpaid orders remain in `PENDING_PAYMENT` and are never auto-fulfilled.

### 4.3 Cart Retention on Interruption
* If a customer cancels checkout or payment fails, the user's cart is **preserved**.
* Carts are only cleared inside `OrderFulfillmentService` upon verified, confirmed payment.

### 4.4 Insecure Direct Object Reference (IDOR) Protection
* `/checkout/confirmation/{order}` and `/checkout/cancel/{order}` verify ownership:
  - If authenticated: `$order->user_id === Auth::id()`.
  - If guest: verifies `session('checkout_order_ref') === $orderReference`.
  - Unauthorized requests are aborted with HTTP 403 Forbidden.

### 4.5 Rate Limiting & Throttling
* `POST /checkout/process` is throttled to `10` requests per minute per IP via `throttle:10,1` middleware.

---

## 5. Webhook Specifications & Durable Idempotency

### Webhook Endpoint
* **URL**: `https://maisonresine.com/webhook/stripe`
* **Route Name**: `webhook.stripe`
* **CSRF Exemption**: Configured in `routes/web.php` via `->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])`.

### Durable Idempotency Schema
Table: `stripe_webhook_events`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Primary Key |
| `stripe_event_id` | string(255) UNIQUE | Stripe Event ID (`evt_...`) |
| `event_type` | string(100) | Event name (e.g. `checkout.session.completed`) |
| `payload` | json / longtext | Event data object snapshot |
| `processed_at` | timestamp | Timestamp of successful processing |

### Supported Event Types
| Event | Action Taken |
| :--- | :--- |
| `checkout.session.completed` | Resolves order, confirms payment, decrements stock, sends tax invoice PDF, clears cart. |
| `payment_intent.succeeded` | Secondary confirmation fallback, fulfills order if not already paid. |
| `payment_intent.payment_failed` | Updates order `payment_status` to `failed` with bank decline message; retains user bag. |
| `charge.refunded` | Updates payment record to `refunded`, marks Order `status` as `CANCELLED` and `payment_status` as `refunded`. |
| `charge.dispute.created` | Flags order as disputed, logs chargeback warning for admin intervention. |
| Other Events | Logged and acknowledged with HTTP 200 OK. |

---

## 6. Stripe Live Refund Integration

### Service: `App\Services\StripeRefundService`
* **Idempotency Key**: Generated as `refund_req_{id}_{paise}` to eliminate accidental double-refunds over unstable connections.
* **Target Resolution**: Automatically resolves `payment_intent`, `charge`, or parent `checkout_session` to issue the refund through the official Stripe Refunds API.
* **Filament Admin Integration**:
  - Resource: `App\Filament\Resources\RefundRequestResource`.
  - Column: `stripe_refund_id` (searchable, toggleable).
  - Action: **"Process Stripe Refund"** with confirmation modal.
  - Updates `RefundRequest` status to `COMPLETED`, saves `stripe_refund_id`, marks `Payment` as `refunded`, and updates `Order` status to `CANCELLED`.

---

## 7. Testing & Verification Guide

### 7.1 Automated 17-Scenario Test Suite
Execute the dedicated production test suite via PHPUnit:
```bash
php ./vendor/bin/phpunit tests/Feature/StripeProductionIntegrationTest.php
```
All 17 tests verify:
1. `test_01_domestic_india_checkout_enforces_inr_currency`
2. `test_02_international_checkout_uses_inr_or_store_currency`
3. `test_03_client_cannot_tamper_currency`
4. `test_04_server_authoritative_price_recalculation_rejects_client_tampering`
5. `test_05_missing_or_invalid_stripe_keys_gracefully_fails_without_auto_fulfillment`
6. `test_06_cart_retained_when_customer_cancels_stripe_checkout`
7. `test_07_cart_retained_on_payment_intent_failed_webhook`
8. `test_08_valid_webhook_signature_fulfills_order`
9. `test_09_invalid_webhook_signature_returns_400`
10. `test_10_webhook_idempotency_prevents_duplicate_processing`
11. `test_11_row_locking_concurrent_webhook_and_redirect_sync_fulfill_strictly_once`
12. `test_12_single_inventory_decrement_for_ready_to_ship`
13. `test_13_out_of_stock_auto_converts_to_made_to_order`
14. `test_14_stripe_refund_service_executes_refund_and_records_id`
15. `test_15_full_refund_marks_order_refunded_and_cancelled`
16. `test_16_refund_idempotency_prevents_duplicate_api_calls`
17. `test_17_idor_protection_prevents_unauthorized_order_access`

### 7.2 Stripe Official Test Card Reference
| Scenario | Card Number | Expiry | CVC | Expected Result |
| :--- | :--- | :--- | :--- | :--- |
| **Domestic India / Visa Success** | `4000 0000 0000 0042` | Any future date | `123` | Payment succeeds; Order confirmed. |
| **International / Mastercard Success** | `5555 5555 5555 4444` | Any future date | `123` | Payment succeeds; Order confirmed. |
| **American Express Success** | `3782 822463 10005` | Any future date | `1234` | Payment succeeds; Order confirmed. |
| **3D Secure (OTP Challenge)** | `4000 0000 0000 3063` | Any future date | `123` | Triggers 3DS challenge window. |
| **Generic Decline** | `4000 0000 0000 0002` | Any future date | `123` | Immediate card decline (`card_declined`). |
| **Insufficient Funds** | `4000 0000 0000 0999` | Any future date | `123` | Fails with `insufficient_funds`. |

---

## 8. Live Production Deployment Checklist

1. **Environment Configuration (`.env`)**:
   ```env
   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   STRIPE_WEBHOOK_SECRET=your_stripe_webhook_signing_secret
   STRIPE_CURRENCY=inr
   ```
2. **Stripe Dashboard Webhook Registration**:
   - URL: `https://your-domain.com/webhook/stripe`
   - Events:
     - `checkout.session.completed`
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `charge.refunded`
     - `charge.dispute.created`
3. **Database Migrations on VPS / Production**:
   ```bash
   php artisan migrate --force
   ```
4. **Cache Clearing**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. **Rollback Procedure**:
   - If live issues arise, revert `.env` credentials to test mode (`sk_test_...`, `whsec_...`).
   - Webhook events are safely logged in `stripe_webhook_events` table for retroactive reconciliation.
