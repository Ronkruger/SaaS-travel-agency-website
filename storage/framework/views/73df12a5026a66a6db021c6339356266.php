<div class="tour-card">
    <?php
        $tourUrl = route('tours.show', $tour->slug);
        $salesMessage = "Hi Discover Group! I am interested in this tour: {$tour->title} - {$tourUrl}";
        $contactSalesUrl = 'https://www.facebook.com/messages/t/discovergrp';
    ?>

    <div class="tour-card-img">
        <img src="<?php echo e(cdn_url($tour->main_image, asset('images/tour-placeholder.jpg'))); ?>"
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
                <a href="<?php echo e($contactSalesUrl); ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="price-current js-contact-sales"
                   data-sales-message="<?php echo e($salesMessage); ?>">Contact Sales</a>
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

<?php if (! $__env->hasRenderedOnce('c8012885-e2a0-4de2-b9c3-84588eb1f1c4')): $__env->markAsRenderedOnce('c8012885-e2a0-4de2-b9c3-84588eb1f1c4'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            function showSalesCopyToast(message) {
                const existing = document.getElementById('salesCopyToast');
                if (existing) existing.remove();

                const toast = document.createElement('div');
                toast.id = 'salesCopyToast';
                toast.className = 'sales-copy-toast';
                toast.textContent = message;
                document.body.appendChild(toast);

                requestAnimationFrame(function () {
                    toast.classList.add('is-visible');
                });

                setTimeout(function () {
                    toast.classList.remove('is-visible');
                    setTimeout(function () {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 180);
                }, 1800);
            }

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('.js-contact-sales');
                if (!trigger) return;

                const message = trigger.getAttribute('data-sales-message');
                if (!message) return;

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(message)
                        .then(function () {
                            showSalesCopyToast('Message copied. Paste it in Facebook chat.');
                        })
                        .catch(function () {
                            showSalesCopyToast('Copy not allowed by browser. Please copy manually.');
                        });
                    return;
                }

                const input = document.createElement('textarea');
                input.value = message;
                input.setAttribute('readonly', 'readonly');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                input.style.pointerEvents = 'none';
                document.body.appendChild(input);
                input.focus();
                input.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(input);
                showSalesCopyToast('Message copied. Paste it in Facebook chat.');
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Users/macbookair/Desktop/discovergrp-new/resources/views/partials/tour-card.blade.php ENDPATH**/ ?>