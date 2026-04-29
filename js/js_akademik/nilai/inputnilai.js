/**
 * Input Nilai Akademik - JavaScript Module v5.0
 * Fitur:
 * - Sidebar toggle dengan persist localStorage
 * - Modal setup dengan dynamic semester options
 * - Kalkulasi nilai & grade otomatis per baris
 * - Confirmation modal sebelum submit form
 * - Toast notification untuk feedback user
 */
(function () {
    'use strict';

    // ===== DOM Elements =====
    const body = document.body;
    const html = document.documentElement;

    // Sidebar
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Modal Setup
    const setupModal = document.getElementById('setupModal');
    const setupModalOverlay = document.getElementById('setupModalOverlay');
    const btnOpenSetup = document.getElementById('btnOpenSetup');
    const btnOpenSetup2 = document.getElementById('btnOpenSetup2');
    const btnOpenSetup3 = document.getElementById('btnOpenSetup3');
    const btnCloseSetup = document.getElementById('btnCloseSetup');
    const btnCancelSetup = document.getElementById('btnCancelSetup');

    // Modal Confirm Submit
    const confirmModal = document.getElementById('confirmModal');
    const confirmModalOverlay = document.getElementById('confirmModalOverlay');
    const btnCloseConfirm = document.getElementById('btnCloseConfirm');
    const btnCancelConfirm = document.getElementById('btnCancelConfirm');
    const btnConfirmSubmit = document.getElementById('btnConfirmSubmit');
    const btnSubmitNilai = document.getElementById('btnSubmitNilai');
    const formNilaiMassal = document.getElementById('formNilaiMassal');

    // Dynamic Semester
    const selectPeriode = document.getElementById('selectPeriode');
    const semesterOptionsContainer = document.getElementById('semesterOptions');
    const semesterHint = document.getElementById('semesterHint');

    // Toast
    const toast = document.getElementById('toast');
    const toastCard = document.getElementById('toastCard');
    const toastTitle = document.getElementById('toastTitle');
    const toastMsg = document.getElementById('toastMsg');

    // Constants
    const KEY_COLLAPSE = 'ak_sidebar_collapsed';
    const GRADE_WEIGHTS = {
        tugas: 0.25,
        uts: 0.25,
        uas: 0.35,
        kehadiran: 0.15
    };

    // ===== Utility Functions =====

    function isMobile() {
        return window.innerWidth <= 860;
    }

    function syncBurgerIcon() {
        if (!menuToggle) return;
        const isX =
            (isMobile() && body.classList.contains('sidebar-open')) ||
            (!isMobile() && body.classList.contains('sidebar-collapsed'));
        menuToggle.classList.toggle('is-x', isX);
    }

    function applyPersistedCollapse() {
        if (isMobile()) {
            body.classList.remove('sidebar-collapsed');
            html.classList.remove('sidebar-collapsed-init');
            return;
        }
        const saved = localStorage.getItem(KEY_COLLAPSE);
        if (saved === '1') {
            body.classList.add('sidebar-collapsed');
        } else {
            body.classList.remove('sidebar-collapsed');
        }
        html.classList.remove('sidebar-collapsed-init');
        syncBurgerIcon();
    }

    function saveCollapseState() {
        const value = body.classList.contains('sidebar-collapsed') ? '1' : '0';
        try {
            localStorage.setItem(KEY_COLLAPSE, value);
        } catch (e) {
            console.warn('LocalStorage not available');
        }
    }

    function closeMobileSidebar() {
        body.classList.remove('sidebar-open');
        syncBurgerIcon();
    }

    function toggleSidebar() {
        if (isMobile()) {
            body.classList.toggle('sidebar-open');
        } else {
            body.classList.toggle('sidebar-collapsed');
            saveCollapseState();
        }
        syncBurgerIcon();
    }

    // ===== Sidebar Event Listeners =====
    applyPersistedCollapse();

    if (menuToggle) {
        menuToggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeMobileSidebar);
    }

    document.addEventListener('click', function (e) {
        if (isMobile() && body.classList.contains('sidebar-open')) {
            const insideSidebar = sidebar && sidebar.contains(e.target);
            const clickToggle = menuToggle && menuToggle.contains(e.target);
            if (!insideSidebar && !clickToggle) {
                closeMobileSidebar();
            }
        }
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            closeMobileSidebar();
            applyPersistedCollapse();
        } else {
            syncBurgerIcon();
        }
    });

    // ===== Modal Functions =====
    function openModal(modalEl) {
        if (!modalEl) return;
        modalEl.classList.add('show');
        modalEl.setAttribute('aria-hidden', 'false');
        body.style.overflow = 'hidden';
    }

    function closeModal(modalEl) {
        if (!modalEl) return;
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        body.style.overflow = '';
    }

    const openSetupModal = () => openModal(setupModal);
    const closeSetupModal = () => closeModal(setupModal);
    const openConfirmModal = () => openModal(confirmModal);
    const closeConfirmModal = () => closeModal(confirmModal);

    [btnOpenSetup, btnOpenSetup2, btnOpenSetup3].forEach(btn => {
        if (btn) btn.addEventListener('click', openSetupModal);
    });

    if (btnCloseSetup) btnCloseSetup.addEventListener('click', closeSetupModal);
    if (btnCancelSetup) btnCancelSetup.addEventListener('click', closeSetupModal);
    if (setupModalOverlay) setupModalOverlay.addEventListener('click', closeSetupModal);

    if (btnCloseConfirm) btnCloseConfirm.addEventListener('click', closeConfirmModal);
    if (btnCancelConfirm) btnCancelConfirm.addEventListener('click', closeConfirmModal);
    if (confirmModalOverlay) confirmModalOverlay.addEventListener('click', closeConfirmModal);

    // ===== Dynamic Semester Options =====
    function updateSemesterOptions(semesterType) {
        if (!semesterOptionsContainer) return;

        const opts = window.__SEMESTER_OPTIONS__ || {
            ganjil: [1, 3, 5, 7],
            genap: [2, 4, 6, 8]
        };

        const arr = semesterType && semesterType.toLowerCase().includes('ganjil')
            ? opts.ganjil
            : opts.genap;

        semesterOptionsContainer.innerHTML = '';

        arr.forEach(smt => {
            const label = document.createElement('label');
            label.className = 'semester-option';

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'semester_angka';
            radio.value = smt;
            radio.required = true;

            if (window.__SELECTED__?.semesterAngka === smt) {
                radio.checked = true;
            }

            const span = document.createElement('span');
            span.className = 'semester-label';
            span.textContent = 'Semester ' + smt;

            label.appendChild(radio);
            label.appendChild(span);
            semesterOptionsContainer.appendChild(label);
        });

        if (semesterHint) {
            const typeText = semesterType && semesterType.toLowerCase().includes('ganjil')
                ? 'ganjil (1, 3, 5, 7)'
                : 'genap (2, 4, 6, 8)';
            semesterHint.textContent = '✓ Menampilkan semester ' + typeText;
        }
    }

    if (selectPeriode) {
        updateSemesterOptions(window.__SELECTED__?.periodeType || 'Ganjil');

        selectPeriode.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const semesterType = selectedOption?.dataset?.semesterType || 'Ganjil';
            updateSemesterOptions(semesterType);
        });
    }

    // ===== Toast Notification =====
    function showToast(type, message) {
        if (!toast || !toastCard || !toastTitle || !toastMsg || !message) return;

        const validTypes = ['success', 'error', 'info'];
        const finalType = validTypes.includes(type) ? type : 'info';

        toastCard.className = 'toast-card ' + finalType;
        toastTitle.textContent =
            finalType === 'success' ? '✓ Berhasil' :
            finalType === 'error' ? '✗ Gagal' : 'ℹ Info';
        toastMsg.textContent = message;

        toast.classList.add('show');
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(function () {
            toast.classList.remove('show');
        }, 3000);
    }

    if (window.__FLASH__ && window.__FLASH__.pesan) {
        showToast(window.__FLASH__.tipe || 'info', window.__FLASH__.pesan);
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('pesan');
            url.searchParams.delete('tipe');
            window.history.replaceState({}, document.title, url.toString());
        } catch (e) {}
    }

    // ===== Grade Calculation =====
    function calculateGradeForRow(row) {
        if (!row) return;

        const inputs = {
            tugas: row.querySelector('input[data-score="tugas"]'),
            uts: row.querySelector('input[data-score="uts"]'),
            uas: row.querySelector('input[data-score="uas"]'),
            kehadiran: row.querySelector('input[data-score="kehadiran"]'),
            nilai_akhir: row.querySelector('input[data-score="nilai_akhir"]'),
            grade: row.querySelector('input[data-score="grade"]'),
            keterangan: row.querySelector('input[data-score="keterangan"]')
        };

        if (!inputs.tugas || !inputs.nilai_akhir || !inputs.grade || !inputs.keterangan) {
            return;
        }

        const getValue = function (el) {
            const val = parseFloat(el?.value || '0');
            return isNaN(val) ? 0 : Math.min(100, Math.max(0, val));
        };

        const nTugas = getValue(inputs.tugas);
        const nUts = getValue(inputs.uts);
        const nUas = getValue(inputs.uas);
        const nKehadiran = getValue(inputs.kehadiran);

        const finalNilai =
            (nTugas * GRADE_WEIGHTS.tugas) +
            (nUts * GRADE_WEIGHTS.uts) +
            (nUas * GRADE_WEIGHTS.uas) +
            (nKehadiran * GRADE_WEIGHTS.kehadiran);

        if (inputs.nilai_akhir) {
            inputs.nilai_akhir.value = finalNilai.toFixed(2);
        }

        let finalGrade = 'E';
        let finalKet = 'Tidak Lulus';

        if (finalNilai >= 85) {
            finalGrade = 'A';
            finalKet = 'Lulus';
        } else if (finalNilai >= 75) {
            finalGrade = 'B';
            finalKet = 'Lulus';
        } else if (finalNilai >= 65) {
            finalGrade = 'C';
            finalKet = 'Lulus';
        } else if (finalNilai >= 50) {
            finalGrade = 'D';
            finalKet = 'Tidak Lulus';
        }

        if (inputs.grade) inputs.grade.value = finalGrade;
        if (inputs.keterangan) inputs.keterangan.value = finalKet;
    }

    function initGradeCalculation() {
        document.querySelectorAll('.nilai-row[data-row="nilai"]').forEach(function (row) {
            ['tugas', 'uts', 'uas', 'kehadiran'].forEach(function (field) {
                const input = row.querySelector('input[data-score="' + field + '"]');
                if (input) {
                    input.addEventListener('input', function () {
                        calculateGradeForRow(row);
                    });
                    input.addEventListener('change', function () {
                        calculateGradeForRow(row);
                    });
                }
            });
            calculateGradeForRow(row);
        });
    }

    // ===== Confirmation Modal for Submit =====
    if (btnSubmitNilai && formNilaiMassal) {
        btnSubmitNilai.addEventListener('click', function (e) {
            e.preventDefault();
            openConfirmModal();
        });
    }

    if (btnConfirmSubmit && formNilaiMassal) {
        btnConfirmSubmit.addEventListener('click', function () {
            closeConfirmModal();
            formNilaiMassal.submit();
        });
    }

    // Prevent double submit
    if (formNilaiMassal) {
        formNilaiMassal.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="30"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></circle></svg> Memproses...';
            }
        });
    }

    // ===== Keyboard Shortcuts =====
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSetupModal();
            closeConfirmModal();
            closeMobileSidebar();
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            if (formNilaiMassal) {
                showToast('info', 'Tekan tombol "Simpan Semua Nilai" untuk menyimpan');
            }
        }
    });

    // ===== Initialize =====
    document.addEventListener('DOMContentLoaded', function () {
        initGradeCalculation();
        syncBurgerIcon();

        document.querySelectorAll('.page-btn:not(.disabled)').forEach(function (link) {
            link.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    });

    // ===== Debug Export (localhost only) =====
    if (window.location.hostname === 'localhost') {
        window.__NilaiApp = {
            calculateGradeForRow: calculateGradeForRow,
            initGradeCalculation: initGradeCalculation,
            showToast: showToast,
            updateSemesterOptions: updateSemesterOptions,
            toggleSidebar: toggleSidebar
        };
    }

})();