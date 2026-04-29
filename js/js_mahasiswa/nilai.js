/**
 * Mahasiswa Nilai Page - JavaScript Module v6.0
 * Fitur:
 * - Auto check status akun (blocker jika nonaktif)
 * - Smooth scroll untuk pagination
 * - Grade badge animation
 * - Responsive table helper
 */
(function () {
    'use strict';

    if (window.__MHS_NILAI__) {
        return;
    }
    window.__MHS_NILAI__ = true;

    const app = document.getElementById('app');
    if (!app) {
        return;
    }

    // ===== Account Blocker =====
    const page = document.getElementById('nilaiPage');
    const apiUrl = page?.dataset?.api || '';
    const blockedMsg = (page?.dataset?.blockedMsg || '').trim();

    const blocker = document.getElementById('blocker');
    const blockerMsg = document.getElementById('blockerMsg');
    const blocked = (app.dataset.accountBlocked || '0').trim() === '1';

    let blockerQueued = false;
    let blockerShown = false;

    function showBlocker() {
        if (!blocker || blockerShown) {
            return;
        }
        blockerShown = true;
        if (blockedMsg && blockerMsg) {
            blockerMsg.textContent = blockedMsg;
        }
        blocker.classList.add('show');
        blocker.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    if (blocked) {
        blockerQueued = true;
        setTimeout(showBlocker, 1000);
    }

    async function checkAccountStatus() {
        if (!apiUrl || blockerShown || blockerQueued) {
            return;
        }
        try {
            const res = await fetch(apiUrl, {
                method: 'GET',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data && data.ok && data.status === 'nonaktif') {
                blockerQueued = true;
                setTimeout(showBlocker, 1000);
            }
        } catch (err) {
            // Silent fail - don't block UX on network error
        }
    }

    // Check on load and periodically
    checkAccountStatus();
    setInterval(checkAccountStatus, 30000); // Check every 30 seconds

    // ===== Smooth Scroll for Pagination =====
    const pageNavLinks = document.querySelectorAll('.page-nav:not(.disabled)');
    pageNavLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            // Allow default navigation but scroll to top smoothly after
            setTimeout(function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 100);
        });
    });

    // ===== Grade Badge Animation on Load =====
    function animateGradeBadges() {
        const gradeCells = document.querySelectorAll('td[class*="grade-"]');
        gradeCells.forEach(function (cell, index) {
            // Staggered animation
            setTimeout(function () {
                cell.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
                cell.style.transform = 'scale(1.05)';
                setTimeout(function () {
                    cell.style.transform = 'scale(1)';
                }, 150);
            }, index * 50);
        });
    }

    // Run animation after page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', animateGradeBadges);
    } else {
        animateGradeBadges();
    }

    // ===== Table Responsive Helper =====
    function initTableScroll() {
        const tableWrap = document.querySelector('.nilai-table-wrap');
        if (!tableWrap) {
            return;
        }

        // Add visual indicator if table is scrollable
        function updateScrollIndicator() {
            const isScrollable = tableWrap.scrollWidth > tableWrap.clientWidth;
            tableWrap.classList.toggle('is-scrollable', isScrollable);
        }

        updateScrollIndicator();
        window.addEventListener('resize', updateScrollIndicator);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTableScroll);
    } else {
        initTableScroll();
    }

    // ===== Keyboard Navigation for Pagination =====
    document.addEventListener('keydown', function (e) {
        // Arrow left/right to navigate pages (if pagination exists)
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const prevLink = document.querySelector('.page-nav:not(.disabled):first-of-type');
            const nextLink = document.querySelector('.page-nav:not(.disabled):last-of-type');
            
            if (e.key === 'ArrowLeft' && prevLink && !prevLink.classList.contains('disabled')) {
                e.preventDefault();
                prevLink.click();
            }
            if (e.key === 'ArrowRight' && nextLink && !nextLink.classList.contains('disabled')) {
                e.preventDefault();
                nextLink.click();
            }
        }
    });

    // ===== Debug Export (localhost only) =====
    if (window.location.hostname === 'localhost') {
        window.__MhsNilai = {
            checkAccountStatus: checkAccountStatus,
            showBlocker: showBlocker,
            animateGradeBadges: animateGradeBadges
        };
    }

})();