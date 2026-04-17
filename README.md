# TourSaaS — Multi-tenant Travel Agency Platform

A white-label SaaS platform built on Laravel 10, powered by [stancl/tenancy v3](https://tenancyforlaravel.com). Each travel agency that signs up gets its own isolated subdomain, separate database, and full access to the booking platform.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│  Central Domain  (toursaas.com)                             │
│  ─ Landing page, pricing, features                          │
│  ─ Tenant registration & login                              │
│  ─ Billing dashboard (Stripe)                               │
│  ─ Platform admin panel (/platform/*)                       │
└─────────────────────────────────────────────────────────────┘
         │ creates subdomain + isolated DB
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Tenant Subdomains  (agency.toursaas.com)                   │
│  ─ Full tour + booking platform                             │
│  ─ Agency admin dashboard                                   │
│  ─ Customer-facing booking site                             │
│  ─ AI-powered DIY Tour Builder                              │
│  ─ Xendit payment processing                                │
└─────────────────────────────────────────────────────────────┘
```

### Multi-tenancy
- **Package**: stancl/tenancy v3 (subdomain identification)
- **Isolation**: Each tenant gets a **separate MySQL database** (tenant{id})
- **Identification**: HTTP subdomain — {tenant-id}.toursaas.com
- **Central DB**: Stores tenants, domains, platform admins, and plans
- **Tenant DB**: All original agency tables (tours, bookings, users, payments, etc.)

### Auth Guards
| Guard | Model | DB |
|---|---|---|
| web | App\Models\User | Tenant (customers) |
| admin | App\Models\AdminUser | Tenant (agency staff) |
| platform | App\Models\PlatformAdmin | Central (superadmins) |

---

## Subscription Plans

| Plan | Price | Tours | Staff | AI Builder | Custom Domain |
|---|---|---|---|---|---|
| Trial | Free / 14 days | 5 | 1 | No | No |
| Starter | $49/mo | 20 | 3 | No | No |
| Professional | $99/mo | 100 | 10 | Yes | No |
| Enterprise | $249/mo | Unlimited | Unlimited | Yes | Yes |

---

## Local Setup

### Requirements
- PHP 8.1+, MySQL 8.0+, Composer, Node.js 18+

### 1. Install dependencies
```bash
composer install
npm install && npm run build
```

### 2. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Key SaaS variables in .env:
```
APP_URL=http://toursaas.test
APP_DOMAIN=toursaas.test
CENTRAL_DOMAINS=toursaas.test

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

PLATFORM_ADMIN_EMAIL=admin@toursaas.com
PLATFORM_ADMIN_PASSWORD=your_secure_password
```

### 3. Run central migrations and seeders
```bash
php artisan migrate
php artisan db:seed
```

This creates central tables and seeds 4 subscription plans + 1 platform admin.

### 4. Local subdomain routing
Add to /etc/hosts:
```
127.0.0.1   toursaas.test
127.0.0.1   demo.toursaas.test
```

---

## Tenant Lifecycle

1. Agency registers at toursaas.com/register
2. stancl/tenancy auto-creates isolated DB, runs migrations, seeds admin user
3. Owner manages their agency at {slug}.toursaas.com/admin
4. Owner manages billing at toursaas.com/billing

---

## Platform Admin

Access: https://toursaas.com/platform/login

- Dashboard with tenant stats
- Agency management (view, edit, suspend, delete, impersonate)
- Subscription plan management

---

## Stripe Webhook Setup

1. Stripe Dashboard → Webhooks → Add: https://toursaas.com/stripe/webhook
2. Events: checkout.session.completed, customer.subscription.updated, customer.subscription.deleted, invoice.payment_failed
3. Copy signing secret to STRIPE_WEBHOOK_SECRET

---

## Key Directories Added for SaaS

```
app/Http/Controllers/Central/   — 8 central controllers
app/Http/Middleware/             — CheckTenantActive, AuthenticateTenantOwner
app/Models/Tenant.php           — Custom tenant model with subscription logic
app/Models/PlatformAdmin.php
app/Models/Plan.php
database/migrations/            — Central tables (tenants, domains, admins, plans)
database/migrations/tenant/     — All original agency tables (46 migrations)
routes/central.php              — All central-domain routes
resources/views/central/        — SaaS landing, billing, platform admin views
resources/views/errors/         — tenant-inactive, subscription-expired
```

---

## Deployment Notes

- Wildcard SSL certificate (*.toursaas.com) required
- Wildcard DNS: *.toursaas.com → server IP
- Set APP_DEBUG=false, APP_ENV=production
- php artisan config:cache && php artisan route:cache
- Queue worker: php artisan queue:work (for tenant DB creation events)
