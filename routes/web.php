<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\Auth0Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\XenditController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\DestinationsController;
use App\Http\Controllers\DIYTourController;
use App\Http\Controllers\DIYTourApiController;
use App\Http\Controllers\Admin\DIYTourController as AdminDIYTourController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Auth\AdminAuth0Controller;
use App\Http\Controllers\Admin\Auth\AdminOnboardingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', fn() => view('about'))->name('about');
Route::get('/destinations', [DestinationsController::class, 'index'])->name('destinations.index');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,1');

// Xendit payment callbacks (CSRF-exempt, no auth required for webhook)
Route::post('/xendit/webhook', [XenditController::class, 'webhook'])->name('xendit.webhook');
Route::middleware('auth')->group(function () {
    Route::get('/xendit/success/{booking}', [XenditController::class, 'success'])->name('xendit.success');
    Route::get('/xendit/failure/{booking}', [XenditController::class, 'failure'])->name('xendit.failure');
    Route::get('/xendit/installment-success/{booking}', [XenditController::class, 'installmentSuccess'])->name('xendit.installment.success');
});

// Tours
Route::prefix('tours')->name('tours.')->group(function () {
    Route::get('/', [TourController::class, 'index'])->name('index');
    Route::get('/{slug}', [TourController::class, 'show'])->name('show');
    Route::get('/{slug}/departures.json', [TourController::class, 'liveDepartures'])->name('departures.live');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Auth0 OAuth
    Route::get('/auth/auth0', [Auth0Controller::class, 'redirect'])->name('auth0.redirect');
    Route::get('/auth/auth0/callback', [Auth0Controller::class, 'callback'])->name('auth0.callback');

    // Password reset via OTP
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequest'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify.post');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:password')
        ->name('profile.password');

    // Wishlist
    Route::post('/tours/{tour}/wishlist', [TourController::class, 'wishlistToggle'])->name('tours.wishlist.toggle');
    Route::get('/wishlist', [TourController::class, 'wishlist'])->name('wishlist');

    // Bookings (with sensitive rate limiting)
    Route::prefix('bookings')->name('booking.')->middleware('throttle:sensitive')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/create', [BookingController::class, 'create'])->name('create');
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
    });

    // Checkout (with sensitive rate limiting)
    Route::prefix('checkout')->name('checkout.')->middleware('throttle:sensitive')->group(function () {
        Route::get('/{booking}', [CheckoutController::class, 'show'])->name('show');
        Route::post('/{booking}', [CheckoutController::class, 'process'])->name('process');
        Route::get('/{booking}/confirmation', [CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/{booking}/installment/{term}', [CheckoutController::class, 'payInstallmentTerm'])->name('installment.pay');
    });

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    // DIY Tours — user-facing list, trash, delete, restore
    Route::get('/diy/my-tours', [DIYTourController::class, 'myTours'])->name('diy.my-tours');
    Route::get('/diy/trash', [DIYTourController::class, 'myTrash'])->name('diy.trash');
    Route::delete('/diy/{token}/delete', [DIYTourController::class, 'softDelete'])->name('diy.delete');
    Route::post('/diy/{token}/restore', [DIYTourController::class, 'restoreSession'])->name('diy.restore');
    Route::delete('/diy/{token}/force-delete', [DIYTourController::class, 'forceDeleteSession'])->name('diy.force-delete');
});

/*
|--------------------------------------------------------------------------
| Admin Auth Routes (separate from customer auth)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only: login & register pages
    Route::middleware('guest.admin')->prefix('auth')->name('auth.')->group(function () {
        Route::get('/login',    [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login',   [AdminAuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
        // Registration is Auth0-only; POST is intentionally not exposed

        // Auth0 SSO for admin
        Route::get('/auth0',          [AdminAuth0Controller::class, 'redirect'])->name('auth0.redirect');
        Route::get('/auth0/callback', [AdminAuth0Controller::class, 'callback'])->name('auth0.callback');
    });

    // Authenticated admin routes (auth.admin middleware also handles onboarding gate)
    Route::middleware(['auth.admin', 'throttle:admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Onboarding (excluded from onboard-check inside middleware)
        Route::get('/onboarding',  [AdminOnboardingController::class, 'show'])->name('onboarding');
        Route::post('/onboarding', [AdminOnboardingController::class, 'save'])->name('onboarding.save');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth.admin', 'throttle:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Live polling endpoints (JSON, no page refresh needed)
    Route::get('/live/stats', [DashboardController::class, 'liveStats'])->name('live.stats');
    Route::get('/live/bookings', [DashboardController::class, 'liveBookings'])->name('live.bookings');

    // Tours
    Route::get('/tours', [AdminTourController::class, 'index'])->name('tours.index');
    Route::get('/tours/create', [AdminTourController::class, 'create'])->name('tours.create');
    Route::post('/tours', [AdminTourController::class, 'store'])->name('tours.store');
    Route::get('/tours/{tour}/edit', [AdminTourController::class, 'edit'])->name('tours.edit');
    Route::get('/tours/{tour}', fn(\App\Models\Tour $tour) => redirect()->route('admin.tours.edit', $tour))->name('tours.show');
    Route::put('/tours/{tour}', [AdminTourController::class, 'update'])->name('tours.update');
    Route::delete('/tours/{tour}', [AdminTourController::class, 'destroy'])->name('tours.destroy');
    Route::post('/tours/{id}/restore', [AdminTourController::class, 'restore'])->name('tours.restore');
    Route::delete('/tours/images/{imageId}', [AdminTourController::class, 'deleteImage'])->name('tours.images.destroy');

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    // Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');
    Route::patch('/bookings/{booking}/payment-status', [AdminBookingController::class, 'updatePaymentStatus'])->name('bookings.payment-status');
    Route::patch('/bookings/{booking}/installment/{term}', [AdminBookingController::class, 'updateInstallmentTerm'])->name('bookings.installment-term');
    Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Reviews
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // DIY Tours (admin)  — outer group already has name('admin.') so these become admin.diy.*
    Route::prefix('diy')->group(function () {
        Route::get('/', [AdminDIYTourController::class, 'index'])->name('diy.index');
        Route::get('/{diySession}', [AdminDIYTourController::class, 'show'])->name('diy.show');
        Route::post('/{diySession}/quote', [AdminDIYTourController::class, 'generateQuote'])->name('diy.quote');
        Route::patch('/{diySession}/status', [AdminDIYTourController::class, 'updateStatus'])->name('diy.status');
        Route::post('/{diySession}/approve', [AdminDIYTourController::class, 'approve'])->name('diy.approve');
        Route::post('/{diySession}/reject', [AdminDIYTourController::class, 'reject'])->name('diy.reject');
        Route::delete('/{diySession}', [AdminDIYTourController::class, 'destroy'])->name('diy.destroy');
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    // Branding / Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/logo', [SettingsController::class, 'deleteLogo'])->name('settings.delete-logo');
});

/*
|--------------------------------------------------------------------------
| DIY Tour Builder Routes (public + guest-friendly)
|--------------------------------------------------------------------------
*/
Route::prefix('diy')->name('diy.')->group(function () {
    // Step 1: Preference wizard
    Route::get('/', [DIYTourController::class, 'index'])->name('index');
    // Step 2: Submit preferences → AI generates itinerary
    Route::post('/', [DIYTourController::class, 'store'])->name('store')->middleware('throttle:sensitive');
    // Step 3: Interactive builder
    Route::get('/{token}/builder', [DIYTourController::class, 'builder'])->name('builder');
    // Auto-save draft via AJAX
    Route::post('/{token}/save', [DIYTourController::class, 'saveDraft'])->name('save');
    // Request formal quote
    Route::post('/{token}/quote', [DIYTourController::class, 'requestQuote'])->name('request-quote');
    // Quote review page
    Route::get('/{token}/quote', [DIYTourController::class, 'quote'])->name('quote');
    // Invite collaborator (auth required)
    Route::post('/{token}/invite', [DIYTourController::class, 'invite'])->name('invite')->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| DIY Tour AJAX API (CSRF-protected, rate-limited)
|--------------------------------------------------------------------------
*/
Route::prefix('diy/api')->name('diy.api.')->middleware('throttle:sensitive')->group(function () {
    Route::post('/suggestions', [DIYTourApiController::class, 'suggestions'])->name('suggestions');
    Route::post('/optimize-route', [DIYTourApiController::class, 'optimizeRoute'])->name('optimize-route');
    Route::post('/reachable-cities', [DIYTourApiController::class, 'reachableCities'])->name('reachable-cities');
    Route::post('/calculate-pricing', [DIYTourApiController::class, 'calculatePricing'])->name('calculate-pricing');
    Route::post('/validate', [DIYTourApiController::class, 'validateItinerary'])->name('validate');
});
