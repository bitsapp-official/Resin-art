CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "contact_inquiries"(
  "id" integer primary key autoincrement not null,
  "public_reference" varchar not null,
  "name" varchar not null,
  "email" varchar not null,
  "phone" varchar,
  "inquiry_type" varchar not null,
  "subject" varchar not null,
  "message" text not null,
  "status" varchar not null default 'new',
  "priority" varchar not null default 'normal',
  "admin_notes" text,
  "replied_at" datetime,
  "closed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "contact_inquiries_created_at_index" on "contact_inquiries"(
  "created_at"
);
CREATE UNIQUE INDEX "contact_inquiries_public_reference_unique" on "contact_inquiries"(
  "public_reference"
);
CREATE INDEX "contact_inquiries_email_index" on "contact_inquiries"("email");
CREATE INDEX "contact_inquiries_inquiry_type_index" on "contact_inquiries"(
  "inquiry_type"
);
CREATE INDEX "contact_inquiries_status_index" on "contact_inquiries"("status");
CREATE INDEX "contact_inquiries_priority_index" on "contact_inquiries"(
  "priority"
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "is_admin" tinyint(1) not null default '0',
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "phone" varchar
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "site_settings"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "value" text,
  "group" varchar not null default 'general',
  "label" varchar,
  "type" varchar not null default 'text',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "site_settings_key_unique" on "site_settings"("key");
CREATE TABLE IF NOT EXISTS "contact_page_contents"(
  "id" integer primary key autoincrement not null,
  "hero_badge" varchar not null default 'Correspondence',
  "hero_title" varchar not null default 'Write to the atelier.',
  "hero_subtitle" text,
  "studio_address" text,
  "studio_hours" text,
  "studio_email" varchar,
  "studio_phone" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "about_pages"(
  "id" integer primary key autoincrement not null,
  "eyebrow" varchar not null default 'THE HOUSE · EST. 2013',
  "hero_title" varchar not null default 'A quiet atelier.',
  "hero_description" text,
  "hero_image" varchar,
  "hero_image_alt" varchar,
  "founder_quote" text,
  "founder_name" varchar,
  "story_eyebrow" varchar not null default 'OUR STORY',
  "story_title" varchar not null default 'Twelve years, one rhythm.',
  "story_content" text,
  "materials_content" text,
  "visit_cta_text" varchar not null default 'VISIT THE ATELIER',
  "visit_cta_url" varchar not null default '/contact',
  "seo_title" varchar,
  "seo_description" text,
  "is_published" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "about_values"(
  "id" integer primary key autoincrement not null,
  "about_page_id" integer not null,
  "number" varchar not null default '01',
  "title" varchar not null,
  "description" text,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("about_page_id") references "about_pages"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "blog_categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "blog_categories_slug_unique" on "blog_categories"("slug");
CREATE TABLE IF NOT EXISTS "blog_posts"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "title" varchar not null,
  "slug" varchar not null,
  "excerpt" text not null,
  "content" text not null,
  "featured_image" varchar,
  "author_name" varchar not null default 'Atelier Artisan',
  "reading_time" varchar,
  "status" varchar not null default 'DRAFT',
  "published_at" datetime,
  "seo_title" varchar,
  "seo_description" text,
  "og_image" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("category_id") references "blog_categories"("id") on delete cascade
);
CREATE INDEX "blog_posts_status_published_at_index" on "blog_posts"(
  "status",
  "published_at"
);
CREATE UNIQUE INDEX "blog_posts_slug_unique" on "blog_posts"("slug");
CREATE INDEX "blog_posts_published_at_index" on "blog_posts"("published_at");
CREATE TABLE IF NOT EXISTS "process_pages"(
  "id" integer primary key autoincrement not null,
  "eyebrow" varchar not null default 'OUR PROCESS',
  "title" varchar not null default 'Six weeks, one object.',
  "description" text not null default 'From timber selection to the final hand-polish, nothing here is hurried.',
  "cta_title" varchar not null default 'Have a custom piece in mind?',
  "cta_button_text" varchar not null default 'SUBMIT YOUR REQUIREMENTS',
  "cta_url" varchar not null default '/custom',
  "status" varchar not null default 'PUBLISHED',
  "seo_title" varchar,
  "seo_description" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "process_steps"(
  "id" integer primary key autoincrement not null,
  "process_page_id" integer not null,
  "step_number" varchar,
  "title" varchar not null,
  "description" text not null,
  "image_path" varchar,
  "image_alt" varchar,
  "image_caption" varchar,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("process_page_id") references "process_pages"("id") on delete cascade
);
CREATE INDEX "process_steps_process_page_id_sort_order_is_active_index" on "process_steps"(
  "process_page_id",
  "sort_order",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "about_timeline_steps"(
  "id" integer primary key autoincrement not null,
  "about_page_id" integer not null,
  "year" varchar not null,
  "title" varchar not null,
  "description" text not null,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("about_page_id") references "about_pages"("id") on delete cascade
);
CREATE INDEX "about_timeline_steps_about_page_id_sort_order_is_active_index" on "about_timeline_steps"(
  "about_page_id",
  "sort_order",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "about_artisans"(
  "id" integer primary key autoincrement not null,
  "about_page_id" integer not null,
  "name" varchar not null,
  "role" varchar not null,
  "image_path" varchar,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("about_page_id") references "about_pages"("id") on delete cascade
);
CREATE INDEX "about_artisans_about_page_id_sort_order_is_active_index" on "about_artisans"(
  "about_page_id",
  "sort_order",
  "is_active"
);
CREATE TABLE IF NOT EXISTS "gallery_categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "sort_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "gallery_categories_slug_index" on "gallery_categories"("slug");
CREATE INDEX "gallery_categories_is_active_index" on "gallery_categories"(
  "is_active"
);
CREATE INDEX "gallery_categories_sort_order_index" on "gallery_categories"(
  "sort_order"
);
CREATE UNIQUE INDEX "gallery_categories_slug_unique" on "gallery_categories"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "gallery_items"(
  "id" integer primary key autoincrement not null,
  "gallery_category_id" integer,
  "title" varchar not null,
  "slug" varchar,
  "description" text,
  "image_path" varchar not null,
  "image_alt" varchar not null,
  "caption" varchar,
  "location" varchar,
  "sort_order" integer not null default '0',
  "is_featured" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("gallery_category_id") references "gallery_categories"("id") on delete set null
);
CREATE INDEX "gallery_items_gallery_category_id_index" on "gallery_items"(
  "gallery_category_id"
);
CREATE INDEX "gallery_items_slug_index" on "gallery_items"("slug");
CREATE INDEX "gallery_items_is_active_index" on "gallery_items"("is_active");
CREATE INDEX "gallery_items_is_featured_index" on "gallery_items"(
  "is_featured"
);
CREATE INDEX "gallery_items_sort_order_index" on "gallery_items"("sort_order");
CREATE INDEX "gallery_items_created_at_index" on "gallery_items"("created_at");
CREATE TABLE IF NOT EXISTS "custom_requests"(
  "id" integer primary key autoincrement not null,
  "public_reference" varchar not null,
  "user_id" integer,
  "project_type" varchar not null,
  "project_type_other" varchar,
  "width" varchar,
  "height" varchar,
  "depth" varchar,
  "unit" varchar,
  "quantity" integer not null default '1',
  "preferred_style" varchar,
  "preferred_colors" varchar,
  "idea_description" text not null,
  "timeline_type" varchar not null,
  "required_date" date,
  "budget_range" varchar,
  "name" varchar not null,
  "email" varchar not null,
  "phone" varchar,
  "whatsapp" varchar,
  "status" varchar not null default 'submitted',
  "submitted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "custom_requests_public_reference_unique" on "custom_requests"(
  "public_reference"
);
CREATE INDEX "custom_requests_status_index" on "custom_requests"("status");
CREATE TABLE IF NOT EXISTS "custom_request_images"(
  "id" integer primary key autoincrement not null,
  "custom_request_id" integer not null,
  "type" varchar not null,
  "file_path" varchar not null,
  "original_name" varchar,
  "alt_text" varchar,
  "customer_note" text,
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_request_id") references "custom_requests"("id") on delete cascade
);
CREATE INDEX "custom_request_images_type_index" on "custom_request_images"(
  "type"
);
CREATE TABLE IF NOT EXISTS "custom_request_messages"(
  "id" integer primary key autoincrement not null,
  "custom_request_id" integer not null,
  "sender_type" varchar not null,
  "sender_id" integer,
  "message" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_request_id") references "custom_requests"("id") on delete cascade,
  foreign key("sender_id") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "custom_quotes"(
  "id" integer primary key autoincrement not null,
  "custom_request_id" integer not null,
  "quote_reference" varchar not null,
  "subtotal" numeric not null default '0',
  "shipping_amount" numeric not null default '0',
  "tax_amount" numeric not null default '0',
  "discount_amount" numeric not null default '0',
  "total_amount" numeric not null default '0',
  "deposit_type" varchar not null default 'percentage',
  "deposit_amount" numeric not null default '50',
  "valid_until" datetime,
  "estimated_completion" varchar,
  "notes" text,
  "status" varchar not null default 'draft',
  "created_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_request_id") references "custom_requests"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "custom_quotes_quote_reference_unique" on "custom_quotes"(
  "quote_reference"
);
CREATE INDEX "custom_quotes_status_index" on "custom_quotes"("status");
CREATE TABLE IF NOT EXISTS "custom_quote_items"(
  "id" integer primary key autoincrement not null,
  "custom_quote_id" integer not null,
  "description" varchar not null,
  "quantity" integer not null default '1',
  "unit_price" numeric not null default '0',
  "total" numeric not null default '0',
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_quote_id") references "custom_quotes"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "custom_orders"(
  "id" integer primary key autoincrement not null,
  "custom_request_id" integer not null,
  "user_id" integer,
  "custom_quote_id" integer not null,
  "order_reference" varchar not null,
  "payment_reference" varchar,
  "amount_paid" numeric not null default '0',
  "remaining_amount" numeric not null default '0',
  "status" varchar not null default 'confirmed',
  "courier_name" varchar,
  "tracking_number" varchar,
  "tracking_url" varchar,
  "shipping_date" date,
  "delivered_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_request_id") references "custom_requests"("id"),
  foreign key("user_id") references "users"("id"),
  foreign key("custom_quote_id") references "custom_quotes"("id")
);
CREATE UNIQUE INDEX "custom_orders_order_reference_unique" on "custom_orders"(
  "order_reference"
);
CREATE INDEX "custom_orders_payment_reference_index" on "custom_orders"(
  "payment_reference"
);
CREATE INDEX "custom_orders_status_index" on "custom_orders"("status");
CREATE TABLE IF NOT EXISTS "custom_order_designs"(
  "id" integer primary key autoincrement not null,
  "custom_order_id" integer not null,
  "version" integer not null default '1',
  "image_path" varchar not null,
  "description" text,
  "status" varchar not null default 'pending',
  "admin_note" text,
  "customer_note" text,
  "created_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_order_id") references "custom_orders"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE INDEX "custom_order_designs_status_index" on "custom_order_designs"(
  "status"
);
CREATE TABLE IF NOT EXISTS "custom_order_updates"(
  "id" integer primary key autoincrement not null,
  "custom_order_id" integer not null,
  "title" varchar not null,
  "description" text not null,
  "image_path" varchar,
  "status_label" varchar,
  "created_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("custom_order_id") references "custom_orders"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "image" varchar,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "categories_slug_unique" on "categories"("slug");
CREATE TABLE IF NOT EXISTS "collections"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "image" varchar,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "collections_slug_unique" on "collections"("slug");
CREATE TABLE IF NOT EXISTS "products"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "sku" varchar,
  "description" text,
  "short_description" text,
  "price" numeric not null,
  "sale_price" numeric,
  "category_id" integer,
  "collection_id" integer,
  "images" text,
  "inventory_type" varchar not null default 'READY_TO_SHIP',
  "stock" integer not null default '0',
  "low_stock_threshold" integer not null default '2',
  "status" varchar not null default 'published',
  "is_featured" tinyint(1) not null default '0',
  "is_new" tinyint(1) not null default '0',
  "is_bestseller" tinyint(1) not null default '0',
  "care_instructions" text,
  "shipping_info" text,
  "attributes" text,
  "created_at" datetime,
  "updated_at" datetime,
  "materials" text,
  "dimensions" text,
  "faqs" text,
  foreign key("category_id") references "categories"("id") on delete set null,
  foreign key("collection_id") references "collections"("id") on delete set null
);
CREATE INDEX "products_status_is_featured_index" on "products"(
  "status",
  "is_featured"
);
CREATE INDEX "products_inventory_type_stock_index" on "products"(
  "inventory_type",
  "stock"
);
CREATE UNIQUE INDEX "products_slug_unique" on "products"("slug");
CREATE UNIQUE INDEX "products_sku_unique" on "products"("sku");
CREATE TABLE IF NOT EXISTS "carts"(
  "id" integer primary key autoincrement not null,
  "user_id" integer,
  "session_id" varchar,
  "total" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "carts_session_id_index" on "carts"("session_id");
CREATE TABLE IF NOT EXISTS "cart_items"(
  "id" integer primary key autoincrement not null,
  "cart_id" integer not null,
  "product_id" integer not null,
  "quantity" integer not null default '1',
  "price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  "selected_option" varchar,
  foreign key("cart_id") references "carts"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "wishlists"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "product_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE UNIQUE INDEX "wishlists_user_id_product_id_unique" on "wishlists"(
  "user_id",
  "product_id"
);
CREATE TABLE IF NOT EXISTS "addresses"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "type" varchar not null default 'shipping',
  "full_name" varchar not null,
  "phone" varchar not null,
  "address_line_1" varchar not null,
  "address_line_2" varchar,
  "city" varchar not null,
  "state" varchar not null,
  "postal_code" varchar not null,
  "country" varchar not null default 'India',
  "is_default" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "addresses_user_id_type_index" on "addresses"("user_id", "type");
CREATE TABLE IF NOT EXISTS "orders"(
  "id" integer primary key autoincrement not null,
  "order_reference" varchar not null,
  "user_id" integer,
  "email" varchar not null,
  "status" varchar not null default 'PENDING',
  "payment_status" varchar not null default 'unpaid',
  "payment_method" varchar not null default 'card',
  "payment_reference" varchar,
  "subtotal" numeric not null,
  "discount" numeric not null default '0',
  "tax" numeric not null default '0',
  "shipping_fee" numeric not null default '0',
  "grand_total" numeric not null,
  "shipping_address_snapshot" text not null,
  "billing_address_snapshot" text,
  "courier" varchar,
  "tracking_number" varchar,
  "tracking_url" varchar,
  "shipped_at" datetime,
  "notes" text,
  "canceled_at" datetime,
  "cancel_reason" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "orders_order_reference_email_index" on "orders"(
  "order_reference",
  "email"
);
CREATE INDEX "orders_user_id_status_index" on "orders"("user_id", "status");
CREATE UNIQUE INDEX "orders_order_reference_unique" on "orders"(
  "order_reference"
);
CREATE TABLE IF NOT EXISTS "order_items"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "product_id" integer,
  "product_name" varchar not null,
  "sku" varchar,
  "unit_price" numeric not null,
  "quantity" integer not null,
  "subtotal" numeric not null,
  "product_snapshot" text,
  "created_at" datetime,
  "updated_at" datetime,
  "selected_option" varchar,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "payments"(
  "id" integer primary key autoincrement not null,
  "order_id" integer not null,
  "payment_reference" varchar not null,
  "provider" varchar not null default 'stripe',
  "amount" numeric not null,
  "currency" varchar not null default 'USD',
  "status" varchar not null default 'pending',
  "payload" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("order_id") references "orders"("id") on delete cascade
);
CREATE UNIQUE INDEX "payments_payment_reference_unique" on "payments"(
  "payment_reference"
);
CREATE TABLE IF NOT EXISTS "return_requests"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "order_id" integer not null,
  "order_item_id" integer,
  "reason" varchar not null,
  "description" text,
  "images" text,
  "status" varchar not null default 'REQUESTED',
  "admin_notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("order_id") references "orders"("id") on delete cascade,
  foreign key("order_item_id") references "order_items"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "refund_requests"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "order_id" integer not null,
  "amount" numeric not null,
  "reason" varchar not null,
  "status" varchar not null default 'REQUESTED',
  "admin_notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("order_id") references "orders"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "support_tickets"(
  "id" integer primary key autoincrement not null,
  "ticket_number" varchar not null,
  "user_id" integer not null,
  "order_id" integer,
  "subject" varchar not null,
  "category" varchar not null default 'General Inquiry',
  "status" varchar not null default 'OPEN',
  "priority" varchar not null default 'medium',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("order_id") references "orders"("id") on delete set null
);
CREATE UNIQUE INDEX "support_tickets_ticket_number_unique" on "support_tickets"(
  "ticket_number"
);
CREATE TABLE IF NOT EXISTS "support_ticket_messages"(
  "id" integer primary key autoincrement not null,
  "support_ticket_id" integer not null,
  "user_id" integer,
  "is_admin" tinyint(1) not null default '0',
  "message" text not null,
  "attachments" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("support_ticket_id") references "support_tickets"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "customer_notifications"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "title" varchar not null,
  "message" text not null,
  "type" varchar not null default 'info',
  "is_read" tinyint(1) not null default '0',
  "data" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "recently_viewed_products"(
  "id" integer primary key autoincrement not null,
  "user_id" integer,
  "session_id" varchar,
  "product_id" integer not null,
  "viewed_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("product_id") references "products"("id") on delete cascade
);
CREATE INDEX "recently_viewed_products_session_id_index" on "recently_viewed_products"(
  "session_id"
);
CREATE TABLE IF NOT EXISTS "user_settings"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "order_updates_email" tinyint(1) not null default '1',
  "promotional_email" tinyint(1) not null default '0',
  "sms_notifications" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "user_settings_user_id_unique" on "user_settings"(
  "user_id"
);
CREATE TABLE IF NOT EXISTS "product_reviews"(
  "id" integer primary key autoincrement not null,
  "product_id" integer not null,
  "user_id" integer,
  "reviewer_name" varchar not null,
  "reviewer_location" varchar default 'India',
  "rating" integer not null default '5',
  "title" varchar,
  "comment" text not null,
  "is_verified_buyer" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("product_id") references "products"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null
);

INSERT INTO migrations VALUES(1,'2026_08_08_000001_create_contact_inquiries_table',1);
INSERT INTO migrations VALUES(2,'2026_08_08_000002_create_users_table',1);
INSERT INTO migrations VALUES(3,'2026_08_08_163310_create_cache_table',1);
INSERT INTO migrations VALUES(4,'2026_08_08_164337_create_site_settings_table',1);
INSERT INTO migrations VALUES(5,'2026_08_08_165927_create_contact_page_contents_table',1);
INSERT INTO migrations VALUES(6,'2026_08_08_173030_create_about_pages_table',1);
INSERT INTO migrations VALUES(7,'2026_08_08_173031_create_about_values_table',1);
INSERT INTO migrations VALUES(8,'2026_08_08_173032_create_about_media_table',1);
INSERT INTO migrations VALUES(9,'2026_08_09_032835_create_blog_categories_table',1);
INSERT INTO migrations VALUES(10,'2026_08_09_032836_create_blog_posts_table',1);
INSERT INTO migrations VALUES(11,'2026_08_09_040515_create_process_pages_table',1);
INSERT INTO migrations VALUES(12,'2026_08_09_040516_create_process_steps_table',1);
INSERT INTO migrations VALUES(13,'2026_08_09_070246_create_about_timelines_and_artisans_tables',1);
INSERT INTO migrations VALUES(14,'2026_08_10_140000_create_gallery_tables',1);
INSERT INTO migrations VALUES(15,'2026_08_11_100000_create_custom_request_tables',1);
INSERT INTO migrations VALUES(16,'2026_08_11_200001_create_ecommerce_categories_and_collections_tables',1);
INSERT INTO migrations VALUES(17,'2026_08_11_200002_create_products_table',1);
INSERT INTO migrations VALUES(18,'2026_08_11_200003_create_carts_and_cart_items_tables',1);
INSERT INTO migrations VALUES(19,'2026_08_11_200004_create_wishlists_table',1);
INSERT INTO migrations VALUES(20,'2026_08_11_200005_create_addresses_table',1);
INSERT INTO migrations VALUES(21,'2026_08_11_200006_create_orders_and_order_items_tables',1);
INSERT INTO migrations VALUES(22,'2026_08_11_200007_create_payments_table',1);
INSERT INTO migrations VALUES(23,'2026_08_11_200008_create_customer_services_tables',1);
INSERT INTO migrations VALUES(24,'2026_08_11_200009_create_notifications_viewed_settings_tables',1);
INSERT INTO migrations VALUES(25,'2026_08_11_200010_add_phone_to_users_table',1);
INSERT INTO migrations VALUES(26,'2026_08_12_000001_create_product_reviews_table',2);
INSERT INTO migrations VALUES(27,'2026_08_13_070611_add_dynamic_details_to_products_table',3);
INSERT INTO migrations VALUES(28,'2026_08_13_072844_add_materials_and_dimensions_to_products_table',4);
INSERT INTO migrations VALUES(29,'2026_08_16_000001_add_selected_option_to_cart_and_order_items',4);
