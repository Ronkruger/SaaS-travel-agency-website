# DiscoverGRP — Tour Reservation System

A full-featured ecommerce-style tour reservation platform built with **Laravel 10**, **MySQL**, and vanilla **HTML/CSS/JS**.

---

## Features

- Browse & search tours with filtering (category, country, duration, price range)
- Tour detail pages with gallery, itinerary, reviews, and live booking widget
- Guest counter with real-time price calculation
- Booking flow with payment checkout (demo mode)
- User accounts: registration, login, profile, booking history, wishlist
- Star-rating reviews (admin-approved)
- Admin panel: dashboard, tour CRUD, booking management, user/review management, categories

---

## Requirements

- PHP 8.1+
- Composer
- MySQL 8.0+ (phpMyAdmin compatible)
- Node.js (optional, for asset building)

---

## Setup Instructions

### 1. Clone / Copy the project

```bash
cd /path/to/your/projects
# the project is already at /Users/macbookair/Desktop/discovergrp-new
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_DATABASE=discovergrp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Create the database

In phpMyAdmin (or MySQL CLI), create a database named `discovergrp`:

```sql
CREATE DATABASE discovergrp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Seed demo data

```bash
php artisan db:seed
```

This creates:
- **Admin account**: `admin@discovergrp.com` / `admin123`
- **Demo customer**: `alice@example.com` / `password`
- 8 tour categories, 6 sample tours with schedules

### 7. Create the storage symlink

```bash
php artisan storage:link
```

### 8. Start the development server

```bash
php artisan serve
```

Visit **http://localhost:8000**

Admin panel: **http://localhost:8000/admin**

---

## Project Structure

```
discovergrp-new/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php
│   │   │   ├── Admin/          (DashboardController, TourController, ...)
│   │   │   ├── HomeController.php
│   │   │   ├── TourController.php
│   │   │   ├── BookingController.php
│   │   │   ├── CheckoutController.php
│   │   │   └── ReviewController.php
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php
│   │   └── Kernel.php
│   └── Models/
│       ├── User.php, Tour.php, Category.php
│       ├── Booking.php, Payment.php
│       ├── TourSchedule.php, TourImage.php
│       ├── Review.php, Wishlist.php
├── database/
│   ├── migrations/     (9 migration files)
│   └── seeders/        (Users, Categories, Tours)
├── public/
│   ├── css/styles.css      (frontend styles)
│   ├── css/admin.css       (admin panel styles)
│   ├── js/main.js          (frontend JS)
│   ├── js/admin.js         (admin JS)
│   └── js/admin-tour-form.js
├── resources/views/
│   ├── layouts/app.blade.php, admin.blade.php
│   ├── auth/, home/, tours/, booking/, checkout/
│   └── admin/  (dashboard, tours, bookings, users, reviews, categories)
└── routes/web.php
```

---

## Admin Credentials

| Role  | Email                  | Password  |
|-------|------------------------|-----------|
| Admin | admin@discovergrp.com  | admin123  |
| User  | alice@example.com      | password  |

---

## Payment Processing

The checkout uses **demo mode** — no real payment gateway. It simulates a successful payment and marks the booking as confirmed. To integrate a real gateway (e.g. Stripe), add your keys to `.env` and implement the processor in `CheckoutController::process()`.

---

## License

MIT — free to use and modify.
