<?php $__env->startSection('title', 'Home'); ?>

<?php $__env->startSection('content'); ?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <span class="hero-label">Explore the World</span>
            <h1 class="hero-title">
                <span class="hero-title-block">Discover Your</span>
                <span class="hero-title-script">Journey</span>
            </h1>
            <p class="hero-subtitle">Unlock the magic of the world with our expertly crafted journeys. Immerse yourself in rich cultures and create unforgettable memories.</p>

            <!-- Search Bar -->
            <form action="<?php echo e(route('tours.index')); ?>" method="GET" class="hero-search">
                <div class="search-field">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="search" placeholder="Where do you want to go?"
                        value="<?php echo e(request('search')); ?>">
                </div>
                <div class="search-field">
                    <i class="fas fa-tags"></i>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->slug); ?>"><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Search Tours
                </button>
            </form>

            <!-- Quick Stats -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="stat-num"><?php echo e(number_format($stats['total_tours'])); ?>+</span>
                    <span class="stat-label">Tours</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-num"><?php echo e(number_format($stats['destinations'])); ?>+</span>
                    <span class="stat-label">Destinations</span>
                </div>
                <div class="hero-stat">
                    <span class="stat-num"><?php echo e(number_format($stats['total_reviews'])); ?>+</span>
                    <span class="stat-label">Happy Travelers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="hero-scroll">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- DIY / Package Tour Choice Section -->
<section class="section section-diy-choice">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">How Would You Like to Travel?</span>
            <h2>Choose Your Perfect Adventure</h2>
        </div>
        <div class="diy-choice-grid">
            <div class="diy-choice-card package-card">
                <div class="diy-choice-icon">📦</div>
                <h3>Package Tours</h3>
                <p>Pre-designed, expertly curated itineraries. Everything planned — just show up and enjoy.</p>
                <ul class="diy-choice-features">
                    <li>✓ From ₱150,000 per person</li>
                    <li>✓ Guaranteed departures</li>
                    <li>✓ Group &amp; private options</li>
                </ul>
                <a href="<?php echo e(route('tours.index')); ?>" class="btn btn-outline btn-block">Browse Tours</a>
            </div>
            <div class="diy-choice-card diy-card featured">
                <div class="diy-ai-badge">✨ AI-Powered</div>
                <div class="diy-choice-icon">🗺️</div>
                <h3>Create Your Own Tour</h3>
                <p>Tell our AI your dream trip. It designs a personalised itinerary you can fully customise.</p>
                <ul class="diy-choice-features">
                    <li>✓ 100% customisable</li>
                    <li>✓ AI route optimisation</li>
                    <li>✓ Real-time pricing</li>
                    <li>✓ Interactive map builder</li>
                </ul>
                <a href="<?php echo e(route('diy.index')); ?>" class="btn btn-primary btn-block">
                    Start Building <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="diy-quiz-cta text-center mt-4">
            <span>💡 Not sure which is right for you?</span>
            <a href="<?php echo e(route('diy.index')); ?>" class="diy-quiz-link">Take our 2-minute quiz to find your ideal tour style</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Browse By</span>
            <h2>Tour Categories</h2>
            <p>Find tours that match your travel style</p>
        </div>
        <div class="categories-grid">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('tours.index', ['category' => $cat->slug])); ?>" class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo e($cat->icon ?? 'fas fa-globe'); ?>"></i>
                    </div>
                    <h4><?php echo e($cat->name); ?></h4>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<!-- Featured Tours -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-label">Handpicked</span>
                <h2>Featured Tours</h2>
            </div>
            <a href="<?php echo e(route('tours.index', ['sort' => 'popular'])); ?>" class="btn btn-outline">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="tours-grid">
            <?php $__currentLoopData = $featuredTours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('partials.tour-card', ['tour' => $tour], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Why Us</span>
            <h2>Travel With Confidence</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h4>Safe & Secure</h4>
                <p>All payments are encrypted and secure. Book with complete peace of mind.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-medal"></i></div>
                <h4>Expert Guides</h4>
                <p>Our certified local guides bring destinations to life with insider knowledge.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-undo-alt"></i></div>
                <h4>Free Cancellation</h4>
                <p>Cancel up to 48 hours before your tour for a full refund, hassle-free.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h4>24/7 Support</h4>
                <p>Our dedicated team is always available to assist you before and during your trip.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-dollar-sign"></i></div>
                <h4>Best Price Guarantee</h4>
                <p>We guarantee the best prices. Find it cheaper? We'll match it or refund.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-star"></i></div>
                <h4>Verified Reviews</h4>
                <p>All reviews come from verified travelers who completed their booking with us.</p>
            </div>
        </div>
    </div>
</section>

<!-- Top Rated Tours -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="section-label">Highly Rated</span>
                <h2>Top Rated Tours</h2>
            </div>
            <a href="<?php echo e(route('tours.index', ['sort' => 'rating'])); ?>" class="btn btn-outline">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="tours-grid tours-grid--4">
            <?php $__currentLoopData = $topRatedTours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('partials.tour-card', ['tour' => $tour], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<?php if($latestReviews->count() > 0): ?>
<section class="section section-gray">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-label">Travelers Say</span>
            <h2>Real Reviews, Real Experiences</h2>
        </div>
        <div class="reviews-grid">
            <?php $__currentLoopData = $latestReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="review-card">
                    <div class="review-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo e($i <= $review->rating ? 'text-yellow' : 'text-gray'); ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-body">"<?php echo e(Str::limit($review->body, 150)); ?>"</p>
                    <div class="review-meta">
                        <div class="reviewer-avatar"><?php echo e(strtoupper(substr($review->user->name, 0, 1))); ?></div>
                        <div>
                            <strong><?php echo e($review->user->name); ?></strong>
                            <span><?php echo e($review->tour->title); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content text-center">
            <h2>Ready for Your Next Adventure?</h2>
            <p>Join thousands of happy travelers and book your dream tour today</p>
            <div class="cta-btns">
                <a href="<?php echo e(route('tours.index')); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Explore Tours
                </a>
                <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-outline btn-lg btn-white">
                        <i class="fas fa-user-plus"></i> Sign Up Free
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/macbookair/Desktop/discovergrp-new/resources/views/home/index.blade.php ENDPATH**/ ?>