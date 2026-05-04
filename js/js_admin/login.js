(function(){
  'use strict';

  // ===== Animation Controller =====
  const Animation = {
    // Trigger staggered animations on page load
    init: function(){
      // Remove loading class after animations complete
      setTimeout(() => {
        document.documentElement.classList.remove('page-loading');
      }, 100);
      
      // Re-trigger animations on refresh (already handled by CSS)
      // But we can add extra JS effects here if needed
    },
    
    // Animate element with custom delay
    animate: function(el, animation, delay = 0){
      if(!el) return;
      el.style.animationDelay = `${delay}s`;
      el.classList.add(animation);
    },
    
    // Reset animations (for testing)
    reset: function(){
      document.querySelectorAll('[class*="animate-"]').forEach(el => {
        el.style.animation = 'none';
        el.offsetHeight; // Trigger reflow
        el.style.animation = '';
      });
    }
  };

  // ===== Toast Helper =====
  const toast = document.getElementById("toast");
  const toastCard = document.getElementById("toastCard");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");

  function showToast(type, message){
    if(!toast || !toastCard || !toastTitle || !toastMsg || !message) return;
    
    const t = (type || "info").toLowerCase();
    toastCard.classList.remove("success","error","info");
    toastCard.classList.add(t==="success"?"success":t==="error"?"error":"info");
    
    toastTitle.textContent = t==="success"?"✓ Berhasil":t==="error"?"✗ Gagal":"ℹ Info";
    toastMsg.textContent = message;
    
    toast.classList.add("show");
    toast.setAttribute("aria-hidden","false");
    
    clearTimeout(showToast.__t);
    showToast.__t = setTimeout(() => {
      toast.classList.remove("show");
      toast.setAttribute("aria-hidden","true");
    }, 2800);
  }

  // Show flash message from PHP (with animation)
  if(window.__FLASH__ && window.__FLASH__.pesan){
    // Small delay so toast animation plays after page load animation
    setTimeout(() => {
      showToast(window.__FLASH__.tipe || "info", window.__FLASH__.pesan);
    }, 600);
    
    try{
      const url = new URL(window.location.href);
      url.searchParams.delete("pesan");
      url.searchParams.delete("tipe");
      window.history.replaceState({}, document.title, url.toString());
    }catch(e){}
  }

  // ===== Password Toggle (with animation) =====
  const password = document.getElementById("password");
  const toggle = document.getElementById("togglePass");
  
  toggle?.addEventListener("click", () => {
    if(!password) return;
    const isPass = password.type === "password";
    password.type = isPass ? "text" : "password";
    toggle.classList.toggle("is-on", isPass);
    
    // Add micro animation on toggle
    toggle.style.transform = 'scale(0.9)';
    setTimeout(() => toggle.style.transform = '', 100);
  });

  // ===== Form Submit Handling =====
  const form = document.getElementById("formLogin");
  const btn = document.getElementById("btnMasuk");
  const user = document.getElementById("username");

  function setLoading(isLoading){
    if(!btn) return;
    btn.disabled = !!isLoading;
    btn.dataset._text = btn.dataset._text || btn.textContent;
    
    if(isLoading){
      btn.innerHTML = '<span class="btn-loader"><span class="spinner"></span> Memproses...</span>';
      btn.style.opacity = '0.9';
    } else {
      btn.textContent = btn.dataset._text;
      btn.style.opacity = '1';
    }
  }

  form?.addEventListener("submit", (e) => {
    const u = (user?.value || "").trim();
    const p = (password?.value || "");
    
    if(!u || !p){
      e.preventDefault();
      showToast("error", "Username dan password wajib diisi.");
      // Shake animation on error
      form.style.animation = 'shake 0.3s ease';
      setTimeout(() => form.style.animation = '', 300);
      return;
    }
    
    setLoading(true);
    setTimeout(() => setLoading(false), 7000);
  });

  // ===== Input Focus Animations =====
  document.querySelectorAll('.field-input').forEach(input => {
    input.addEventListener('focus', function(){
      this.closest('.field')?.classList.add('focused');
    });
    input.addEventListener('blur', function(){
      this.closest('.field')?.classList.remove('focused');
    });
  });

  // ===== CapsLock Warning =====
  password?.addEventListener("keydown", (e) => {
    if(typeof e.getModifierState === "function" && e.getModifierState("CapsLock")){
      showToast("info", "⚠ CapsLock sedang aktif");
    }
  });

  // ===== Enter key on username focuses password =====
  user?.addEventListener("keydown", (e) => {
    if(e.key === "Enter" && password){
      e.preventDefault();
      password.focus();
    }
  });

  // ===== Initialize Animations =====
  document.addEventListener('DOMContentLoaded', () => {
    Animation.init();
    
    // Add CSS for shake animation dynamically
    const style = document.createElement('style');
    style.textContent = `
      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
      }
      .btn-loader { display: inline-flex; align-items: center; gap: 6px; }
      .btn-loader .spinner {
        width: 14px; height: 14px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
      }
      @keyframes spin { to { transform: rotate(360deg); } }
    `;
    document.head.appendChild(style);
  });

  // ===== Handle Page Refresh Animation =====
  // CSS handles this via :root animation, but we can enhance with JS
  if(performance.getEntriesByType("navigation")[0]?.type === 'reload'){
    // Add extra class for refresh-specific animation if needed
    document.body.classList.add('page-refreshed');
  }

  // ===== Debug Export =====
  if(window.location.hostname === 'localhost'){
    window.__LoginAnim = Animation;
  }

})();