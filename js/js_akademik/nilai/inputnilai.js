/**
 * Input Nilai Akademik - JavaScript Module
 * Handles: Sidebar toggle, Modal, Toast, Grade Calculation, Form submission
 * Version: 2.0
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
  
  // Toast
  const toast = document.getElementById('toast');
  const toastCard = document.getElementById('toastCard');
  const toastTitle = document.getElementById('toastTitle');
  const toastMsg = document.getElementById('toastMsg');
  
  // Form
  const formNilaiMassal = document.getElementById('formNilaiMassal');
  
  // Constants
  const KEY_COLLAPSE = 'ak_sidebar_collapsed';
  const GRADE_WEIGHTS = { tugas: 0.25, uts: 0.25, uas: 0.35, kehadiran: 0.15 };

  // ===== Utility Functions =====
  
  /**
   * Check if viewport is mobile size
   */
  function isMobile() {
    return window.innerWidth <= 860;
  }

  /**
   * Sync hamburger icon animation state
   */
  function syncBurgerIcon() {
    if (!menuToggle) return;
    const isX = 
      (isMobile() && body.classList.contains('sidebar-open')) ||
      (!isMobile() && body.classList.contains('sidebar-collapsed'));
    menuToggle.classList.toggle('is-x', isX);
  }

  /**
   * Apply persisted sidebar collapse state from localStorage
   */
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

  /**
   * Save sidebar collapse state to localStorage
   */
  function saveCollapseState() {
    const value = body.classList.contains('sidebar-collapsed') ? '1' : '0';
    try {
      localStorage.setItem(KEY_COLLAPSE, value);
    } catch (e) {
      console.warn('LocalStorage not available');
    }
  }

  /**
   * Close mobile sidebar
   */
  function closeMobileSidebar() {
    body.classList.remove('sidebar-open');
    syncBurgerIcon();
  }

  /**
   * Toggle sidebar (mobile: open/close overlay, desktop: collapse/expand)
   */
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

  // Close sidebar when clicking outside on mobile
  document.addEventListener('click', function (e) {
    if (isMobile() && body.classList.contains('sidebar-open')) {
      const insideSidebar = sidebar && sidebar.contains(e.target);
      const clickToggle = menuToggle && menuToggle.contains(e.target);
      if (!insideSidebar && !clickToggle) {
        closeMobileSidebar();
      }
    }
  });

  // Handle window resize
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

  // Setup Modal Event Listeners
  const openSetupModal = () => openModal(setupModal);
  const closeSetupModal = () => closeModal(setupModal);

  [btnOpenSetup, btnOpenSetup2, btnOpenSetup3].forEach(btn => {
    if (btn) btn.addEventListener('click', openSetupModal);
  });

  if (btnCloseSetup) btnCloseSetup.addEventListener('click', closeSetupModal);
  if (btnCancelSetup) btnCancelSetup.addEventListener('click', closeSetupModal);
  if (setupModalOverlay) setupModalOverlay.addEventListener('click', closeSetupModal);

  // ===== Toast Notification =====
  
  function showToast(type, message) {
    if (!toast || !toastCard || !toastTitle || !toastMsg || !message) return;
    
    const validTypes = ['success', 'error', 'info'];
    const finalType = validTypes.includes(type) ? type : 'info';
    
    // Update toast content
    toastCard.className = 'toast-card ' + finalType;
    toastTitle.textContent = 
      finalType === 'success' ? '✓ Berhasil' :
      finalType === 'error' ? '✗ Gagal' : 'ℹ Info';
    toastMsg.textContent = message;
    
    // Show toast
    toast.classList.add('show');
    
    // Auto hide after delay
    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => {
      toast.classList.remove('show');
    }, 3000);
  }

  // Show flash message from PHP if exists
  if (window.__FLASH__ && window.__FLASH__.pesan) {
    showToast(window.__FLASH__.tipe || 'info', window.__FLASH__.pesan);
    
    // Clean URL params
    try {
      const url = new URL(window.location.href);
      url.searchParams.delete('pesan');
      url.searchParams.delete('tipe');
      window.history.replaceState({}, document.title, url.toString());
    } catch (e) {}
  }

  // ===== Grade Calculation =====
  
  /**
   * Calculate final grade for a single row
   * @param {HTMLElement} row - The nilai-row element
   */
  function calculateGradeForRow(row) {
    if (!row) return;
    
    // Get input elements within this row
    const inputs = {
      tugas: row.querySelector('input[data-score="tugas"]'),
      uts: row.querySelector('input[data-score="uts"]'),
      uas: row.querySelector('input[data-score="uas"]'),
      kehadiran: row.querySelector('input[data-score="kehadiran"]'),
      nilai_akhir: row.querySelector('input[data-score="nilai_akhir"]'),
      grade: row.querySelector('input[data-score="grade"]'),
      keterangan: row.querySelector('input[data-score="keterangan"]')
    };
    
    // Skip if required inputs don't exist
    if (!inputs.tugas || !inputs.nilai_akhir || !inputs.grade || !inputs.keterangan) return;
    
    // Parse values (default to 0 if empty)
    const getValue = (el) => {
      const val = parseFloat(el?.value || '0');
      return isNaN(val) ? 0 : Math.min(100, Math.max(0, val)); // Clamp 0-100
    };
    
    const nTugas = getValue(inputs.tugas);
    const nUts = getValue(inputs.uts);
    const nUas = getValue(inputs.uas);
    const nKehadiran = getValue(inputs.kehadiran);
    
    // Calculate weighted average
    const finalNilai = 
      (nTugas * GRADE_WEIGHTS.tugas) +
      (nUts * GRADE_WEIGHTS.uts) +
      (nUas * GRADE_WEIGHTS.uas) +
      (nKehadiran * GRADE_WEIGHTS.kehadiran);
    
    // Update nilai_akhir field
    if (inputs.nilai_akhir) {
      inputs.nilai_akhir.value = finalNilai.toFixed(2);
    }
    
    // Determine grade and keterangan
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
    
    // Update grade and keterangan fields
    if (inputs.grade) inputs.grade.value = finalGrade;
    if (inputs.keterangan) inputs.keterangan.value = finalKet;
  }

  /**
   * Initialize grade calculation for all rows
   */
  function initGradeCalculation() {
    const rows = document.querySelectorAll('.nilai-row[data-row="nilai"]');
    
    rows.forEach(row => {
      // Attach input listeners to score fields
      ['tugas', 'uts', 'uas', 'kehadiran'].forEach(field => {
        const input = row.querySelector(`input[data-score="${field}"]`);
        if (input) {
          input.addEventListener('input', () => calculateGradeForRow(row));
          input.addEventListener('change', () => calculateGradeForRow(row));
        }
      });
      
      // Initial calculation for pre-filled values
      calculateGradeForRow(row);
    });
  }

  // ===== Form Submission =====
  
  if (formNilaiMassal) {
    formNilaiMassal.addEventListener('submit', function (e) {
      // Optional: Add client-side validation here
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg viewBox="0 0 24 24" class="spinner"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="60" stroke-dashoffset="30"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/></circle></svg> Memproses...';
      }
    });
  }

  // ===== Keyboard Shortcuts =====
  
  document.addEventListener('keydown', function (e) {
    // ESC to close modal and mobile sidebar
    if (e.key === 'Escape') {
      closeSetupModal();
      closeMobileSidebar();
    }
    
    // Ctrl+S or Cmd+S to save form (prevent default browser save)
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
    
    // Add smooth scroll for pagination links
    document.querySelectorAll('.page-btn:not(.disabled)').forEach(link => {
      link.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });
  });

  // ===== Expose for debugging (dev only) =====
  if (window.location.hostname === 'localhost') {
    window.__NilaiApp = {
      calculateGradeForRow,
      initGradeCalculation,
      showToast,
      toggleSidebar
    };
  }

})();