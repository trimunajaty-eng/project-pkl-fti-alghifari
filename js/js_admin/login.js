(function(){
  'use strict';

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

  // Show flash message from PHP
  if(window.__FLASH__ && window.__FLASH__.pesan){
    showToast(window.__FLASH__.tipe || "info", window.__FLASH__.pesan);
    try{
      const url = new URL(window.location.href);
      url.searchParams.delete("pesan");
      url.searchParams.delete("tipe");
      window.history.replaceState({}, document.title, url.toString());
    }catch(e){}
  }

  // ===== Password Toggle =====
  const password = document.getElementById("password");
  const toggle = document.getElementById("togglePass");
  
  toggle?.addEventListener("click", () => {
    if(!password) return;
    const isPass = password.type === "password";
    password.type = isPass ? "text" : "password";
    toggle.classList.toggle("is-on", isPass);
  });

  // ===== Form Submit Handling =====
  const form = document.getElementById("formLogin");
  const btn = document.getElementById("btnMasuk");
  const user = document.getElementById("username");

  function setLoading(isLoading){
    if(!btn) return;
    btn.disabled = !!isLoading;
    btn.dataset._text = btn.dataset._text || btn.textContent;
    btn.textContent = isLoading ? "⏳ Memproses..." : btn.dataset._text;
  }

  form?.addEventListener("submit", (e) => {
    const u = (user?.value || "").trim();
    const p = (password?.value || "");
    
    if(!u || !p){
      e.preventDefault();
      showToast("error", "Username dan password wajib diisi.");
      return;
    }
    
    setLoading(true);
    setTimeout(() => setLoading(false), 7000);
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

})();