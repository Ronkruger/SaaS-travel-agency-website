/* ================================================
   DiscoverGRP — Main JS
   ================================================ */

(function () {
    'use strict';

    /* --- Navbar scroll shadow ------------------- */
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        }, { passive: true });
    }

    /* --- Mobile hamburger menu (enhanced) ---------- */
    const mobileBtn   = document.getElementById('mobileMenuBtn');
    const navbarNav   = document.querySelector('.navbar-nav');
    const navBackdrop = document.getElementById('navBackdrop');
    const navClose    = document.getElementById('navClose');
    const navParent   = navbarNav ? navbarNav.parentNode : null;
    const navNextSib  = navbarNav ? navbarNav.nextSibling : null;

    function openNav() {
        if (!navbarNav) return;
        // Move drawer to body so it escapes the navbar stacking context
        document.body.appendChild(navbarNav);
        navbarNav.classList.add('open');
        if (mobileBtn)   { mobileBtn.classList.add('open'); mobileBtn.setAttribute('aria-expanded', 'true'); }
        if (navBackdrop) navBackdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeNav() {
        if (!navbarNav) return;
        navbarNav.classList.remove('open');
        if (mobileBtn)   { mobileBtn.classList.remove('open'); mobileBtn.setAttribute('aria-expanded', 'false'); }
        if (navBackdrop) navBackdrop.classList.remove('open');
        document.body.style.overflow = '';
        // Move drawer back into navbar for desktop layout
        if (navParent) {
            if (navNextSib) { navParent.insertBefore(navbarNav, navNextSib); }
            else { navParent.appendChild(navbarNav); }
        }
    }

    if (mobileBtn)   mobileBtn.addEventListener('click', function () {
        navbarNav.classList.contains('open') ? closeNav() : openNav();
    });
    if (navClose)    navClose.addEventListener('click', closeNav);
    if (navBackdrop) navBackdrop.addEventListener('click', closeNav);

    // Close on nav link click (mobile only)
    document.querySelectorAll('.navbar-nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            // Leave dropdown toggles alone on mobile
            if (window.innerWidth < 992 && !this.closest('.dropdown-menu')) closeNav();
        });
    });

    // Close on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeNav();
    }, { passive: true });

    // Escape key closes nav
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNav();
    });

    /* --- User dropdown toggle ------------------- */
    document.querySelectorAll('.user-dropdown').forEach(function (dropdown) {
        const btn  = dropdown.querySelector('.user-btn');
        const menu = dropdown.querySelector('.user-menu');
        if (!btn || !menu) return;
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = menu.style.display !== 'block';
            menu.style.display = open ? 'block' : 'none';
        });
        document.addEventListener('click', function () {
            menu.style.display = 'none';
        });
    });

    /* --- Flash message auto-dismiss ------------- */
    document.querySelectorAll('.alert').forEach(function (alert) {
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dismissAlert(alert);
            });
        }
        setTimeout(function () { dismissAlert(alert); }, 6000);
    });

    function dismissAlert(el) {
        el.style.transition = 'opacity 0.4s ease, max-height 0.4s ease';
        el.style.opacity    = '0';
        el.style.maxHeight  = '0';
        el.style.overflow   = 'hidden';
        el.style.padding    = '0';
        el.style.margin     = '0';
        setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 420);
    }

    /* --- Mobile filter sidebar toggle ----------- */
    const filterBtn     = document.getElementById('mobileFilterBtn');
    const filterSidebar = document.querySelector('.filter-sidebar');
    if (filterBtn && filterSidebar) {
        filterBtn.addEventListener('click', function () {
            filterSidebar.classList.toggle('open');
        });
    }

    /* --- Wishlist AJAX toggle ------------------- */
    document.querySelectorAll('.wishlist-btn[data-tour]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const tourId  = btn.dataset.tour;
            const url     = btn.dataset.url || '/tours/' + tourId + '/wishlist';
            const token   = document.querySelector('meta[name="csrf-token"]');
            if (!token) { window.location.href = '/login'; return; }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token.content,
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                if (res.status === 401) { window.location.href = '/login'; return null; }
                return res.json();
            })
            .then(function (data) {
                if (!data) return;
                const icon = btn.querySelector('i');
                if (data.wishlisted) {
                    btn.classList.add('active');
                    if (icon) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
                    btn.title = 'Remove from wishlist';
                } else {
                    btn.classList.remove('active');
                    if (icon) { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
                    btn.title = 'Add to wishlist';
                }
            })
            .catch(function () { /* silently fail */ });
        });
    });

    /* --- Guest counter (used on booking/detail) - */
    window.changeCount = function (type, delta) {
        const input = document.getElementById(type + '_count');
        if (!input) return;
        const min = parseInt(input.min || 0, 10);
        const max = parseInt(input.max || 99, 10);
        let val   = parseInt(input.value || 0, 10) + delta;
        val       = Math.max(min, Math.min(max, val));
        input.value = val;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    /* --- Gallery thumbnail switcher ------------- */
    document.querySelectorAll('.gallery-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            const mainImg = document.querySelector('.gallery-main img');
            if (mainImg) {
                mainImg.src = thumb.src;
            }
            document.querySelectorAll('.gallery-thumb').forEach(function (t) { t.classList.remove('active'); });
            thumb.classList.add('active');
        });
    });

    /* --- Tour detail tab switching -------------- */
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tabId  = btn.dataset.tab;
            const parent = btn.closest('.tour-tabs');
            if (!parent) return;
            parent.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            parent.querySelectorAll('.tab-content').forEach(function (c) { c.classList.remove('active'); });
            btn.classList.add('active');
            const content = parent.querySelector('#tab-' + tabId);
            if (content) content.classList.add('active');
        });
    });

    /* --- Star rating input ---------------------- */
    document.querySelectorAll('.star-rating-input').forEach(function (wrapper) {
        const stars = wrapper.querySelectorAll('.fa-star');
        const input = wrapper.querySelector('input[name="rating"]') || document.getElementById('ratingInput');
        stars.forEach(function (star, idx) {
            star.addEventListener('mouseover', function () {
                stars.forEach(function (s, i) {
                    s.classList.toggle('text-yellow', i <= idx);
                });
            });
            star.addEventListener('mouseout', function () {
                const val = parseInt(input ? input.value : 0, 10) - 1;
                stars.forEach(function (s, i) {
                    s.classList.toggle('text-yellow', i <= val);
                });
            });
            star.addEventListener('click', function () {
                if (input) input.value = idx + 1;
                stars.forEach(function (s, i) {
                    s.classList.toggle('text-yellow', i <= idx);
                });
            });
        });
    });

    /* --- Password toggle visibility ------------- */
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const wrapper  = btn.closest('.password-wrapper');
            const field    = wrapper ? wrapper.querySelector('input') : null;
            if (!field) return;
            const visible = field.type === 'text';
            field.type    = visible ? 'password' : 'text';
            const icon    = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye',       visible);
                icon.classList.toggle('fa-eye-slash', !visible);
            }
        });
    });

})();
