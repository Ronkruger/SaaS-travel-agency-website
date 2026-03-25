<div class="tour-card">
    <div class="tour-card-img">
        <img src="<?php echo e($tour->main_image ? asset('storage/' . $tour->main_image) : asset('images/tour-placeholder.jpg')); ?>"
             alt="<?php echo e($tour->title); ?>"
             loading="lazy"
             onerror="this.src='<?php echo e(asset('images/tour-placeholder.jpg')); ?>'">

        <?php if($tour->discount_percent > 0): ?>
            <span class="tour-badge tour-badge--discount">-<?php echo e($tour->discount_percent); ?>%</span>
        <?php endif; ?>
        <?php if($tour->is_featured): ?>
            <span class="tour-badge tour-badge--featured">Featured</span>
        <?php endif; ?>

        <button class="wishlist-btn <?php echo e(isset($wishedIds) && in_array($tour->id, $wishedIds) ? 'active' : ''); ?>"
                data-tour="<?php echo e($tour->id); ?>"
                aria-label="Add to wishlist">
            <i class="fas fa-heart"></i>
        </button>
    </div>

    <div class="tour-card-body">
        <div class="tour-card-meta">
            <?php if($tour->line): ?>
                <span class="tour-category">
                    <i class="fas fa-tag"></i> <?php echo e($tour->line); ?>

                </span>
            <?php endif; ?>
            <?php if($tour->continent): ?>
                <span class="tour-location">
                    <i class="fas fa-globe"></i> <?php echo e($tour->continent); ?>

                </span>
            <?php endif; ?>
        </div>

        <h3 class="tour-card-title">
            <a href="<?php echo e(route('tours.show', $tour->slug)); ?>"><?php echo e($tour->title); ?></a>
        </h3>

        <p class="tour-card-desc"><?php echo e(Str::limit($tour->short_description, 100)); ?></p>

        <div class="tour-card-details">
            <span><i class="fas fa-clock"></i> <?php echo e($tour->duration_days); ?> Days</span>
            <?php if($tour->guaranteed_departure): ?>
                <span><i class="fas fa-check-circle text-green"></i> Guaranteed</span>
            <?php endif; ?>
            <?php if($tour->average_rating > 0): ?>
                <span><i class="fas fa-star text-yellow"></i> <?php echo e(number_format($tour->average_rating, 1)); ?>

                    <small>(<?php echo e($tour->total_reviews); ?>)</small>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="tour-card-footer">
        <div class="tour-price">
            <?php if($tour->promo_price_per_person): ?>
                <span class="price-original">₱<?php echo e(number_format($tour->regular_price_per_person, 2)); ?></span>
                <span class="price-current">₱<?php echo e(number_format($tour->promo_price_per_person, 2)); ?></span>
            <?php elseif($tour->regular_price_per_person): ?>
                <span class="price-current">₱<?php echo e(number_format($tour->regular_price_per_person, 2)); ?></span>
            <?php else: ?>
                <span class="price-current">Contact Us</span>
            <?php endif; ?>
            <?php if($tour->regular_price_per_person): ?>
                <small>per person</small>
            <?php endif; ?>
        </div>
        <a href="<?php echo e(route('tours.show', $tour->slug)); ?>" class="btn btn-primary btn-sm">
            View Details
        </a>
    </div>
</div>
<?php /**PATH /Users/macbookair/Desktop/discovergrp-new/resources/views/partials/tour-card.blade.php ENDPATH**/ ?>