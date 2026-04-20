@extends('layouts.admin')
@section('title', 'Welcome to Your Dashboard')

@section('breadcrumb')
    <span>Dashboard</span>
@endsection

@push('styles')
<style>
    .welcome-hero {
        background: linear-gradient(135deg, #0f172a 0%, #0e7490 100%);
        border-radius: 16px;
        padding: 3rem 2.5rem;
        color: white;
        margin-bottom: 2rem;
        text-align: center;
    }
    .welcome-hero h1 {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
    }
    .welcome-hero p {
        font-size: 1.125rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    .welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #7dd3fc;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .setup-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .step-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        border: 2px solid #e5e7eb;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .step-card:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 24px rgba(14, 116, 144, 0.12);
        transform: translateY(-2px);
    }
    .step-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary);
        opacity: 0;
        transition: opacity 0.2s;
    }
    .step-card:hover::before {
        opacity: 1;
    }
    
    .step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: 50%;
        font-weight: 800;
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }
    
    .step-card h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e3a5f;
        margin-bottom: 0.75rem;
    }
    .step-card p {
        color: #6b7280;
        font-size: 0.9375rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    .step-card .btn {
        width: 100%;
        justify-content: center;
    }
    
    .help-section {
        background: #f0f9ff;
        border: 2px solid #bae6fd;
        border-radius: 12px;
        padding: 2rem;
        margin-top: 2rem;
        text-align: center;
    }
    .help-section h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0c4a6e;
        margin-bottom: 0.75rem;
    }
    .help-section p {
        color: #0c4a6e;
        margin-bottom: 1.5rem;
    }
    
    .quick-links {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    .quick-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: white;
        border: 2px solid #bae6fd;
        border-radius: 8px;
        color: #0c4a6e;
        font-weight: 600;
        font-size: 0.9375rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .quick-link:hover {
        background: #0e7490;
        border-color: #0e7490;
        color: white;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="welcome-hero">
    <div class="welcome-badge">
        <i class="fas fa-rocket"></i>
        Welcome to Your New Platform
    </div>
    <h1>Let's Build Your Travel Agency! 🌍</h1>
    <p>Your platform is ready to go. Follow these simple steps to set up your agency and start accepting bookings from customers.</p>
</div>

<div class="setup-steps">
    <!-- Step 1: Settings -->
    <div class="step-card">
        <div class="step-number">1</div>
        <h3><i class="fas fa-cog text-primary"></i> Configure Settings</h3>
        <p>Set your agency name, logo, contact details, and branding. This information will appear on your customer-facing website.</p>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i> Go to Settings
        </a>
    </div>

    <!-- Step 2: Categories -->
    <div class="step-card">
        <div class="step-number">2</div>
        <h3><i class="fas fa-folder-open text-primary"></i> Create Categories</h3>
        <p>Organize your tours by creating categories like "Beach Tours", "City Tours", "Adventure Packages", etc.</p>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    <!-- Step 3: Tours -->
    <div class="step-card">
        <div class="step-number">3</div>
        <h3><i class="fas fa-map-marked-alt text-primary"></i> Add Your First Tour</h3>
        <p>Create tour packages with descriptions, pricing, itineraries, and images. Your customers will browse and book these tours.</p>
        <a href="{{ route('admin.tours.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Tour
        </a>
    </div>

    <!-- Step 4: Pages -->
    <div class="step-card">
        <div class="step-number">4</div>
        <h3><i class="fas fa-file-alt text-primary"></i> Customize Pages</h3>
        <p>Edit your homepage, about page, and other content to tell your agency's story and showcase what makes you unique.</p>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Manage Pages
        </a>
    </div>

    <!-- Step 5: Payment -->
    <div class="step-card">
        <div class="step-number">5</div>
        <h3><i class="fas fa-credit-card text-primary"></i> Payment Setup</h3>
        <p>Connect your payment gateway (Xendit, Stripe, etc.) to accept online payments from customers.</p>
        <a href="{{ route('admin.settings.index') }}#payment" class="btn btn-primary">
            <i class="fas fa-wallet"></i> Configure Payments
        </a>
    </div>

    <!-- Step 6: Go Live -->
    <div class="step-card">
        <div class="step-number">6</div>
        <h3><i class="fas fa-rocket text-primary"></i> Launch Your Site</h3>
        <p>Once everything is set up, your customer-facing website will be live and ready to accept bookings!</p>
        <a href="{{ url('/') }}" target="_blank" class="btn btn-success">
            <i class="fas fa-external-link-alt"></i> View Your Site
        </a>
    </div>
</div>

<div class="help-section">
    <h3><i class="fas fa-question-circle"></i> Need Help Getting Started?</h3>
    <p>Explore these resources to learn more about managing your travel agency platform.</p>
    
    <div class="quick-links">
        <a href="{{ route('admin.tours.index') }}" class="quick-link">
            <i class="fas fa-map-marked-alt"></i> All Tours
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="quick-link">
            <i class="fas fa-handshake"></i> Bookings
        </a>
        <a href="{{ route('admin.users.index') }}" class="quick-link">
            <i class="fas fa-users"></i> Customers
        </a>
        <a href="{{ route('admin.staff.index') }}" class="quick-link">
            <i class="fas fa-user-shield"></i> Team Members
        </a>
        <a href="{{ route('admin.reviews.index') }}" class="quick-link">
            <i class="fas fa-star"></i> Reviews
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add animation on load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.step-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
