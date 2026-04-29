/**
 * Mahasiswa Nilai Page - JavaScript Module v8.0
 * Fitur:
 * - Pagination sequential 1-8 dengan ellipsis
 * - Smooth scroll untuk navigasi
 * - Grade badge animation on load
 * - Account blocker untuk status nonaktif
 * - Responsive table helper
 * - Horizontal pagination scroll di mobile
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

    // ===== Account Blocker (Nonaktif) =====
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
            // Silent fail - jangan ganggu UX kalau network error
        }
    }

    // Check on load dan periodic
    checkAccountStatus();
    setInterval(checkAccountStatus, 30000);

    // ===== Smooth Scroll untuk Pagination =====
    const pageLinks = document.querySelectorAll('.page-nav:not(.disabled), .page-number');
    
    pageLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            // Biarkan navigasi default, tapi scroll ke atas dengan smooth setelahnya
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
            // Staggered animation agar tidak serentak
            setTimeout(function () {
                cell.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
                cell.style.transform = 'scale(1.05)';
                cell.style.opacity = '0.9';
                
                setTimeout(function () {
                    cell.style.transform = 'scale(1)';
                    cell.style.opacity = '1';
                }, 150);
            }, index * 40);
        });
    }

    // Run animation setelah DOM ready
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

        // Tambah visual indicator kalau table bisa discroll
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

    // ===== Pagination Horizontal Scroll Helper (Mobile) =====
    function initPaginationScroll() {
        const pagination = document.querySelector('.nilai-pagination');
        if (!pagination) return;

        // Enable horizontal scroll on mobile
        function updatePaginationScroll() {
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                pagination.style.overflowX = 'auto';
            } else {
                pagination.style.overflowX = 'visible';
            }
        }

        updatePaginationScroll();
        window.addEventListener('resize', updatePaginationScroll);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPaginationScroll);
    } else {
        initPaginationScroll();
    }

    // ===== Keyboard Navigation untuk Pagination =====
    document.addEventListener('keydown', function (e) {
        // Arrow left/right untuk navigasi halaman
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const currentPage = parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10);
            const totalPages = 8; // Selalu 8 semester
            
            let nextPage = null;
            
            if (e.key === 'ArrowLeft' && currentPage > 1) {
                nextPage = currentPage - 1;
            }
            if (e.key === 'ArrowRight' && currentPage < totalPages) {
                nextPage = currentPage + 1;
            }
            
            if (nextPage) {
                e.preventDefault();
                window.location.href = 'nilai.php?page=' + nextPage;
            }
        }
    });

    // ===== Highlight Active Page on Load =====
    function highlightActivePage() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = parseInt(urlParams.get('page') || '1', 10);
        const pageNumbers = document.querySelectorAll('.page-number');
        
        pageNumbers.forEach(function (num) {
            const pageNum = parseInt(num.textContent.trim(), 10);
            if (pageNum === currentPage) {
                num.classList.add('active');
            } else {
                num.classList.remove('active');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', highlightActivePage);
    } else {
        highlightActivePage();
    }

    // ===== Ellipsis Click Handler (Opsional: klik "..." load lebih) =====
    function initEllipsisClick() {
        const ellipsisElements = document.querySelectorAll('.page-ellipsis');
        
        ellipsisElements.forEach(function (el) {
            el.addEventListener('click', function () {
                // Opsional: scroll ke page numbers atau tampilkan semua
                const pageNumbers = document.querySelector('.page-numbers');
                if (pageNumbers) {
                    pageNumbers.scrollLeft = pageNumbers.scrollWidth / 2;
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEllipsisClick);
    } else {
        initEllipsisClick();
    }

    // ===== Debug Export (localhost only) =====
    if (window.location.hostname === 'localhost') {
        window.__MhsNilai = {
            checkAccountStatus: checkAccountStatus,
            showBlocker: showBlocker,
            animateGradeBadges: animateGradeBadges,
            highlightActivePage: highlightActivePage,
            initPaginationScroll: initPaginationScroll
        };
    }

})();