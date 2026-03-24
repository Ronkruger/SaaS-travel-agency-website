/* ================================================
   DiscoverGRP — Admin JS
   ================================================ */

(function () {
    'use strict';

    /* --- Sidebar toggle ------------------------- */
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose  = document.getElementById('sidebarClose');
    const sidebar       = document.querySelector('.admin-sidebar');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        getOrCreateOverlay().classList.add('visible');
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        const overlay = document.getElementById('sidebarOverlay');
        if (overlay) overlay.classList.remove('visible');
    }

    function getOrCreateOverlay() {
        let overlay = document.getElementById('sidebarOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            overlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:150;';
            document.body.appendChild(overlay);
            overlay.addEventListener('click', closeSidebar);
        }
        overlay.style.display = 'block';
        return overlay;
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose)  sidebarClose.addEventListener('click', closeSidebar);

    /* --- Active nav link ----------------------- */
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-nav a').forEach(function (link) {
        if (link.getAttribute('href') && currentPath.startsWith(link.getAttribute('href'))
            && link.getAttribute('href') !== '/') {
            link.classList.add('active');
        }
    });

    /* --- Confirm delete prompts ---------------- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.dataset.confirm || 'Are you sure you want to perform this action?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    /* --- Auto-dismiss alerts ------------------- */
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                if (alert.parentNode) alert.parentNode.removeChild(alert);
            }, 420);
        }, 5000);
    });

    /* --- Table row click navigation ------------ */
    document.querySelectorAll('tr[data-href]').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, form, input, select')) return;
            window.location.href = row.dataset.href;
        });
    });

    /* --- Modal helpers ------------------------- */
    window.openModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.add('open'); }
    };

    window.closeModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.remove('open'); }
    };

    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.openModal(btn.dataset.modalOpen);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = btn.closest('.modal');
            if (modal) modal.classList.remove('open');
        });
    });

    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function () {
            const modal = backdrop.closest('.modal');
            if (modal) modal.classList.remove('open');
        });
    });

})();
