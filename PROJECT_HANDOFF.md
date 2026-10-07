# Etod Graphic — Complete Project Handoff Report

**Document purpose:** This is an English handoff document for another AI agent or developer who will continue the Etod Graphic project.

**Project path:** `C:\xampp\htdocs\Etod-graphic-accio`

**Current HEAD at original report:** `cef3c99 feat: add product catalog and media management`

**Current project state:** Development/demo-ready foundation. The project is not intended for real production use yet. Real mobile authentication, live payment, live SMS, WhatsApp, production admin authentication, and production deployment are intentionally deferred.

---

## 1. Business Context

Etod Graphic is a Persian/RTL online store for custom sublimation printing. Customers should eventually be able to select a product and variant, upload an image, position/resize it inside a product-specific print area, add the customized product to a cart, checkout, pay, receive an order number, and track the order.

The operational user should eventually be able to see new orders, view the customer design/preview, inspect printing specifications, change production status, manage inventory, manage products and images, and use a print queue.

The project owner is not an experienced professional web developer. Code should therefore remain understandable, modular, documented, secure by default, and avoid unnecessary overengineering.

---

## 2. Important Decisions and Explicit Deferrals

The following decisions were made by the owner and must be respected:

### Deferred: mobile authentication and OTP

Do **not** add real mobile registration, OTP delivery, or SMS authentication yet. The current demo uses guest/session-based flows.

Future authentication may use:

- Mobile number registration
- Hashed OTP
- Expiration
- Rate limiting
- Attempt limits
- Resend limits
- Verified mobile number
- Login/logout and profile management

### Deferred: real Zarinpal payment

Do **not** add live Zarinpal credentials or production payment calls yet. The current payment implementation is a Mock Payment flow only.

Future payment work may add:

- Zarinpal sandbox first
- Merchant ID from the owner
- Callback URL
- Server-side verification
- Idempotency
- Duplicate callback protection
- Failed/cancelled/timeout handling
- Production credentials only through `.env` or a secret manager

### Deferred: real SMS and WhatsApp

SMS.ir and Meta WhatsApp Cloud API were selected conceptually, but no live credentials have been supplied. Current code should remain Mock/Log-based.

Never invent:

- API keys
- Merchant IDs
- SMS templates
- WhatsApp access tokens
- Real prices
- Real blank/product dimensions
- Production domain/server settings

---

## 3. Technology Stack

### Backend

- PHP 8.2.12
- Laravel 12.69.2
- Laravel Blade server-rendered views
- MySQL/MariaDB through `pdo_mysql`
- Laravel validation, transactions, services, route model binding, and filesystem abstraction

### Frontend

- Vite 6.x
- Tailwind CSS 4.x
- Vanilla JavaScript for current interactions
- Persian/RTL UI
- Vazirmatn font loaded in CSS

### Testing and quality

- PHPUnit 11
- Laravel Feature tests
- SQLite in-memory test database configured in `phpunit.xml`
- Laravel Pint
- Vite production build

### Local environment used

- Windows + XAMPP
- Project path: `C:\xampp\htdocs\Etod-graphic-accio`
- MySQL/MariaDB on port `3306`
- Development URL: `http://127.0.0.1:8000`

---

## 4. Repository and Git History

The project was rebuilt after the original XAMPP installation/data loss. Git was reinitialized and the current project has these major commits:

```text
7f5c16d feat: rebuild Etod Graphic store foundation
53f658e feat: add guest checkout and mock orders
3268eaa fix: redirect empty checkout to cart
5ad893d feat: add development admin order panel
c5a40cd fix: connect product uploads to guest cart
b92f5d9 feat: add print queue and inventory operations
cef3c99 feat: add product catalog and media management
```

The latest commit is the catalog/media management commit.

Before making large changes:

1. Check `git status`.
2. Read the affected files.
3. Create a commit after a coherent feature is complete.
4. Run tests, Pint, and `npm run build`.

---

## 5. Current Application Features

### 5.1 Persian/RTL storefront UI

The storefront has a light visual style inspired by the owner-provided reference image:

- White header
- Purple/lavender accents
- Persian navigation
- RTL layout
- Responsive cards and sections
- Hero section
- Category cards
- Product cards
- Mobile menu behavior
- Purple CTA buttons
- Vazirmatn font

Main views:

```text
resources/views/storefront/home.blade.php
resources/views/storefront/product.blade.php
resources/views/storefront/cart.blade.php
resources/views/storefront/checkout.blade.php
resources/views/storefront/order.blade.php
```

Frontend files:

```text
resources/css/app.css
resources/js/app.js
```

### 5.2 Catalog

The catalog currently supports:

- Categories
- Products
- Product variants
- Colors
- Sizes/models
- Product status: draft, published, archived
- Base product price
- Printing price
- Product-specific print templates
- Product images
- Variant-specific image relationship field

The storefront hides non-published products.

### 5.3 Demo products and seed data

`database/seeders/EtodDemoSeeder.php` creates demo data.

Current seed concepts include:

- 7 categories
- 8 products
- 8 test colors
- Multiple sizes/models
- Product variants with stock
- Product-specific print templates

Seeded product examples:

- T-shirt
- Hoodie
- Ceramic Mug
- Tumbler
- Phone Case
- Tote Bag
- Cushion Cover
- Puzzle

Seed data is test data. Prices and physical dimensions are not production values.

### 5.4 Product-specific print templates

Each variant can have a `print_templates` record containing:

- Physical width/height
- Print area width/height
- Print area X/Y position
- DPI
- Allowed image formats
- Maximum image size
- Template image path
- Version

The demo seed uses placeholder values. Real blank/product measurements must be entered later by the owner.

### 5.5 Customer image upload and Customizer

The current customer-side Customizer supports:

- Selecting JPG, PNG, or WEBP
- Secure upload endpoint
- MIME and extension validation
- Maximum file size validation
- Image dimension validation in the service
- Server-generated storage path
- Private/local storage
- SHA-256 metadata
- Session-based guest ownership
- User-based ownership if authentication is added later
- Drag and drop positioning
- Scale/resize slider
- Normalized design coordinates
- Rotation field support in customization data
- `upload_id` hidden form input
- `customization_data` hidden form input
- Server-side validation before adding to cart

Important fix already made:

`CartService::add()` previously received `null` customization data. This was fixed at three levels:

- `AddToCartRequest` normalizes missing/invalid JSON to `[]`
- `CartController` ensures the value is an array
- `CartService` safely normalizes nullable input

The fix is in commit:

```text
c5a40cd fix: connect product uploads to guest cart
```

### 5.6 Guest cart

The cart is session-based for guests. It supports:

- Add item
- Update quantity
- Delete item
- Current cart lookup
- Server-side price calculation
- Variant existence and active checks
- Inventory checks
- Row locking during sensitive operations
- Customization data attached to cart items
- Upload relationship attached to cart items
- Product preview in the cart when available

The frontend is never trusted for:

- Price
- Stock
- Payment state
- Permissions

### 5.7 Guest checkout and orders

The current checkout is intentionally a demo/guest checkout.

It collects:

- Customer name
- Customer phone
- Customer address
- Portfolio consent

Order creation:

- Uses a database transaction
- Loads the current guest cart
- Locks relevant variants
- Rechecks available stock
- Increments `reserved_stock`
- Creates an order number
- Stores customer snapshot data
- Stores totals
- Creates order item snapshots
- Copies product/variant/customization information
- Clears the cart

Order number format currently follows a configurable-looking readable format such as:

```text
ORD-2026-000001
```

The current implementation is demo-grade and should later be moved behind a dedicated order number generator/configuration if needed.

### 5.8 Mock Payment

Mock Payment is intentionally available for development only.

The payment entity stores payment information and supports idempotent behavior. A duplicate mock payment should not create duplicate payment effects or duplicate print jobs.

Mock payment can:

- Mark payment as paid
- Mark order as paid
- Release reserved stock according to the current implementation
- Create Print Jobs exactly once per order item

Do not treat this as a real payment integration.

### 5.9 Order status machine

Current status concepts include:

```text
draft
pending_payment
paid
processing
printing
ready
shipped
completed
cancelled
payment_failed
```

`app/Services/OrderStatusService.php` defines allowed transitions and rejects invalid transitions.

Examples:

```text
paid -> processing
processing -> printing
printing -> ready
ready -> shipped
shipped -> completed
```

Invalid examples must remain blocked:

```text
printing -> completed
completed -> processing
```

### 5.10 Development Admin Panel

The admin routes are intentionally registered only in `local` and `testing` environments:

```php
if (app()->environment(['local', 'testing'])) { ... }
```

This is a critical limitation: the current admin panel is not production-safe and has no login.

Current admin pages:

```text
/admin
/admin/orders
/admin/orders/{order}
/admin/print-queue
/admin/inventory
/admin/products
/admin/products/create
/admin/products/{product}/edit
```

Current admin capabilities:

- Dashboard order counts
- Order list with pagination
- Order detail view
- Allowed order status changes
- Print queue view
- Variant inventory view
- Inventory adjustments with reason
- Product list
- Create product
- Edit product
- Product status management
- Product base and printing price fields
- Product image upload
- Product image delete
- Primary image flag

### 5.11 Print Queue

Print jobs are created after a paid order’s mock payment, one per order item.

Print queue fields include concepts such as:

- Order
- Order item
- Product variant
- Status
- Print side
- Preview path
- Started time
- Completed time
- Notes

Admin queue route:

```text
/admin/print-queue
```

Current queue is a simple development operator view. It still needs actions for starting/completing/retrying print jobs.

### 5.12 Inventory

Inventory includes:

- `stock`
- `reserved_stock`
- Inventory movements
- Reason for adjustment
- Transaction and row locking
- Protection against reducing stock below reserved stock
- Overselling protection during cart/order operations

Admin route:

```text
/admin/inventory
```

Inventory adjustment endpoint:

```text
PATCH /admin/inventory/{variant}
```

The current inventory admin is development-only and not protected by admin authentication.

### 5.13 Product media

Product images are separate from customer uploads.

Product image behavior:

- Stored under a product media path
- Private/local disk
- Served through a controlled application route
- Can be marked primary
- Can contain alt text
- Can optionally reference a product variant
- Can be deleted from the development admin

Public/customer image route:

```text
GET /product-images/{image}
```

Admin upload route:

```text
POST /admin/products/{product}/images
```

The storefront product page loads the primary product image if present.

---

## 6. Database Schema Summary

Core migrations:

```text
database/migrations/0001_01_01_000000_create_users_table.php
database/migrations/2026_09_28_143948_create_etod_store_tables.php
database/migrations/2026_09_29_104745_add_session_id_to_orders_table.php
database/migrations/2026_10_01_105009_create_print_jobs_table.php
database/migrations/2026_10_01_105533_create_product_images_table.php
```

Core tables include:

```text
users
categories
colors
sizes
products
product_variants
print_templates
uploads
carts
cart_items
orders
order_items
payments
inventory_movements
print_jobs
product_images
cache
jobs
failed_jobs
```

Important relationships:

```text
categories 1--N products
products 1--N product_variants
colors 1--N product_variants
sizes 1--N product_variants
product_variants 1--1 print_templates
products 1--N product_images
users 1--N carts
carts 1--N cart_items
product_variants 1--N cart_items
users/session 1--N uploads
orders 1--N order_items
orders 1--N payments
orders 1--N print_jobs
product_variants 1--N inventory_movements
```

Order item snapshots are intentionally stored so later product/price changes do not modify previous orders.

---

## 7. Main Routes

### Storefront

```text
GET  /
GET  /products/{product:slug}
GET  /cart
POST /cart/items
PATCH /cart/items/{item}
DELETE /cart/items/{item}
GET  /checkout
POST /checkout
GET  /orders/{order:order_number}
POST /orders/{order}/mock-payment
POST /uploads
GET  /uploads/{upload}
GET  /product-images/{image}
```

### Development admin only

```text
GET    /admin
GET    /admin/orders
GET    /admin/orders/{order}
PATCH  /admin/orders/{order}/status
GET    /admin/print-queue
GET    /admin/inventory
PATCH  /admin/inventory/{variant}
GET    /admin/products
GET    /admin/products/create
POST   /admin/products
GET    /admin/products/{product}/edit
PATCH  /admin/products/{product}
POST   /admin/products/{product}/images
DELETE /admin/product-images/{image}
```

---

## 8. Test Status

Latest verified status:

```text
16 tests passed
48 assertions passed
```

The tests cover:

- Basic application response
- Admin pages in local/testing
- Valid order status transition
- Invalid order status transition
- Cart server-side pricing
- Cart missing customization data regression
- Cart stock limit rejection
- Product creation
- Product image upload
- Product image storefront route
- Inventory adjustment
- Inventory reserved-stock protection
- Print job creation and duplicate protection
- Order snapshot creation
- Stock reservation
- Mock payment idempotency
- Guest private image upload
- Invalid upload rejection

Run tests:

```powershell
php artisan test
```

Run formatting check:

```powershell
vendor\\bin\\pint app database routes tests --test
```

Build frontend:

```powershell
npm run build
```

The latest known successful checks were:

```text
16 tests passed
48 assertions passed
Pint passed on 56 files
Vite production build passed
```

---

## 9. Local Setup and Recovery

### Start XAMPP

1. Open XAMPP Control Panel as Administrator.
2. Start MySQL.
3. Start Apache if serving through Apache.
4. For Laravel’s built-in server, Apache is not required.

### Database

Create the development database if needed:

```sql
CREATE DATABASE etod_graphic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`.env` development database values:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=etod_graphic
DB_USERNAME=root
DB_PASSWORD=
```

### Install and run

```powershell
cd C:\xampp\htdocs\Etod-graphic-accio
composer install
npm install
php artisan migrate:fresh --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Development URL:

```text
http://127.0.0.1:8000/
```

### Test database safety

Tests must use SQLite in-memory as configured in `phpunit.xml`. Do not run destructive `migrate:fresh` against the development MySQL database unless explicitly intended.

### Required PHP extensions

The local PHP setup previously required/enabled:

```text
gd
intl
fileinfo
pdo_mysql
mbstring
zip
bcmath
exif
```

GD and file-related extensions are needed for image validation and Customizer tests.

### Stop the site

The Laravel development server runs on port 8000. To stop it, stop only the PHP processes serving the project or close the terminal that launched `php artisan serve`.

Do not stop MySQL unless intentionally shutting down XAMPP.

---

## 10. Known Limitations and Risks

### 10.1 Admin has no authentication

This is the largest current security limitation.

Admin pages are conditionally registered only for `local` and `testing`, which is acceptable for the current demo requirement but not for real deployment.

Before production:

- Add admin login
- Add roles and permissions
- Add policies/gates
- Add CSRF and rate limiting review
- Add 2FA for Super Admin
- Remove the environment-only shortcut
- Add audit logs

### 10.2 Real payment is not implemented

Mock Payment must never be enabled in production.

### 10.3 Real SMS/WhatsApp is not implemented

SMS and WhatsApp remain Log/Mock only.

### 10.4 Authentication is intentionally deferred

Guest sessions are used. Customer account ownership and order history are not production-ready.

### 10.5 Product prices and dimensions are test data

Do not publish demo prices or assume the seeded print dimensions are accurate.

### 10.6 Print Queue actions are incomplete

The queue can be viewed, but operator actions such as start, complete, retry, download final print file, and production notes need more work.

### 10.7 Product CRUD is basic

Product CRUD currently does not yet provide a complete UI for:

- Variant creation/editing
- Color assignment
- Size/model assignment
- Print Template editing
- Per-variant image assignment UI
- Product deletion/archive workflow with confirmation

### 10.8 Product image route is development-oriented

The current controlled image route is environment-limited and should be redesigned for production cache headers, authorization rules, CDN/storage, and public catalog requirements.

### 10.9 Notifications are not wired end-to-end

Events, queued SMS, WhatsApp, email, in-site notifications, and failed notification logs remain future work.

### 10.10 Review/Portfolio are not implemented as a complete user workflow

Consent fields exist in order concepts, but customer submission, moderation, portfolio publishing, and separate social-media consent need completion.

---

## 11. Remaining Work — Prioritized Roadmap

### Phase A — Finish current development admin

1. Variant CRUD in admin
2. Variant color/size/model assignment
3. Print Template CRUD in admin
4. Per-variant product image assignment
5. Print Queue start/complete/retry actions
6. Print job notes and operator timestamps
7. Downloadable print file handling
8. Product archive confirmation
9. Inventory movement history page
10. Low-stock filtering

### Phase B — Customer experience

1. Add product image gallery instead of only first image
2. Improve mobile Customizer UX
3. Crop support
4. More reliable print-area clamping
5. Rotation UI
6. Better preview rendering
7. Customer order confirmation page improvements
8. Guest order lookup token or future authentication hook

### Phase C — Reviews and Portfolio

1. Review model/UI after completed order
2. Rating validation
3. Review moderation status
4. Admin approve/reject/hide
5. Portfolio item model and admin
6. Portfolio consent audit trail
7. Separate social-media consent

### Phase D — Notifications and queues

1. Domain events such as OrderCreated and OrderReady
2. Queue jobs
3. Notification log table
4. Log driver for development
5. SMS.ir adapter later
6. Meta WhatsApp Cloud adapter later
7. Failed jobs/retry UI
8. Admin reminder for unseen orders

### Phase E — Admin security

1. Admin authentication
2. RBAC roles:
   - Super Admin
   - Admin
   - Support
   - Warehouse
3. Permissions enforced in backend
4. Policies/Gates
5. Login history
6. Audit logs
7. Security events
8. 2FA for Super Admin
9. Confirmation for destructive operations

### Phase F — Real payment and account system, later

Only after the owner supplies credentials and makes the decision:

1. Mobile registration and OTP
2. SMS.ir integration
3. Zarinpal sandbox adapter
4. Server-side callback verification
5. Idempotent payment reconciliation
6. Production payment credentials through secrets
7. Customer accounts and order history

### Phase G — Production deployment

1. Production server selection
2. Domain and DNS
3. HTTPS/SSL
4. Production `.env`
5. Queue worker
6. Scheduler/cron
7. Supervisor or equivalent
8. MySQL backup and restore
9. Monitoring/logging
10. `APP_DEBUG=false`
11. Deployment and rollback procedure
12. Security review
13. Load/concurrency testing

---

## 12. Recommended Next Task for the Next AI

The best immediate next task is:

> Complete Variant CRUD and Print Template CRUD in the development admin, then add operator actions to the Print Queue.

Acceptance criteria:

- Admin can create/edit product variants.
- Admin can select color, size, or model.
- Admin can set per-variant price, printing price, SKU, stock, and active state.
- Admin can create/edit a print template for a variant.
- Admin can set physical dimensions and printable area.
- Admin can view and edit print jobs.
- Admin can move print jobs from queued to printing to completed.
- Invalid print-job transitions are rejected.
- Tests cover authorization boundary, validation, stock, and transitions.
- `php artisan test`, Pint, and `npm run build` all pass.

Do not start OTP or real payment unless the owner explicitly changes the deferral decision.

---

## 13. Suggested Handoff Prompt

The next AI can be given this instruction:

> Read `PROJECT_HANDOFF.md` and inspect the repository at `C:\xampp\htdocs\Etod-graphic-accio`. Continue from Git commit `cef3c99`. Do not implement mobile OTP or live Zarinpal yet; they are explicitly deferred. First complete Variant CRUD, Print Template CRUD, and Print Queue actions in the development-only admin panel. Preserve SQLite test isolation, server-side price/stock validation, private file handling, transactions, row locking, order snapshots, and the existing status machine. Run `php artisan test`, `vendor\\bin\\pint app database routes tests --test`, and `npm run build` before committing changes.

---

## 14. Final Current Snapshot

At the time this report was written:

```text
Laravel: 12.69.2
PHP: 8.2.12
Database: MySQL/MariaDB on localhost:3306
Environment: local
URL: 127.0.0.1:8000
Tests: 16 passed, 48 assertions
Pint: passed
Vite build: passed
Latest commit: cef3c99
```

## 15. Update — 2026-10-07

The Phase A development-admin backlog has been implemented locally:

- Variant create/edit UI with SKU, color, size/model, prices, active state, and initial stock recorded through the inventory service.
- Print template create/edit UI, private template-image handling, printable-area bounds validation, and version increments for production-spec changes.
- Variant-scoped product image assignment and primary-image selection.
- Print-job transitions (queued → printing → completed; printing → failed; failed → queued), timestamped notes, private production-file upload/download, and guarded download of the matching customer source image.
- Product archive confirmation with reason and audit-log entry. This changes status to archived; it does not permanently delete records.
- Inventory movement history and available-stock low-level filtering.
- Timezone is configured as `Asia/Tehran`.

These admin routes remain local/testing-only and have no real authentication. Do not expose them to a network or deploy them. User/customer authentication, live payment, SMS/WhatsApp, notifications workflow, production authentication/deployment, and later customer experience/review phases remain deferred. No schema changes or data migrations were made in this update. Local-only uploads and environment files are excluded from Git.

The last completed feature was Product Catalog and Product Media Management. The next recommended feature is Variant and Print Template administration.

## 16. Update — 2026-10-07 (Phase B started)

Phase B (customer experience) work completed locally:

- Product image gallery: multi-image products render clickable thumbnails that swap the main image with active-state styling.
- Design rotation UI: -180..180 slider writing the already-validated `customization_data.rotation` field.
- Print-area clamping: the whole design box is clamped inside the print-area rect (centered when larger than the area), replacing the loose 0-100 stage clamp.
- Guest order tracking token: `orders.access_token` (unique, 40 chars) generated per order and backfilled by migration. Checkout and mock payment redirect to `/orders/token/{token}`; design previews of an order are served through `/orders/token/{token}/items/{item}/design` (authorized by token). Session-based `orders.show` remains. Mock payment accepts session ownership OR a matching `token` input.
- Order confirmation page rebuilt: success hero, copyable tracking link box, status timeline (Persian labels), payment section, item snapshots with design preview, shipping info, print-receipt button.
- Checkout price integrity: order items now recalculate `unit_price` from the current variant/product prices at checkout instead of trusting the stored cart price.
- Demo seed prices: products now seed with placeholder IRR base/printing prices (clearly test data); the local dev DB was updated to match.
- Design crop support: client-side crop mode in the customizer (drag a rect on the uploaded image, apply/cancel). Cropped output re-uploads through the same secure endpoint. Client-side guard enforces the server minimum of 300x300 px. Upload fetches now send `Accept: application/json` so validation errors render as messages instead of HTML redirects.

Tests: 26 passed, 70 assertions. Pint clean. Vite build clean. The upload-preview 500 regression (BinaryFileResponse type hint) and the customizer null-input JS error were fixed and covered by tests earlier the same day.

Remaining Phase B items: none mandatory; optional polish includes advanced crop handles (resize after drawing) and per-side print previews. Next recommended phase: C (reviews/portfolio) or E (admin authentication) depending on owner priority.
