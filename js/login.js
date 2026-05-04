document.addEventListener("DOMContentLoaded", () => {
  
  // ===== Animation Controller =====
  const Animation = {
    init: function(){
      document.documentElement.classList.add('page-loading');
      
      window.addEventListener("load", () => {
        setTimeout(() => {
          const loader = document.getElementById("loader");
          if(loader){
            loader.classList.add('hidden');
            setTimeout(() => {
              loader.style.display = "none";
              // ===== SHOW FLASH MESSAGE AFTER LOADER HIDES =====
              showFlashMessage();
            }, 300);
          }
        }, 800);
      });
    }
  };

  Animation.init();

  // ===== Show Flash Message with Animation =====
  function showFlashMessage(){
    const flash = window.__FLASH__ || {};
    if(!flash.pesan) return;
    
    const alert = document.querySelector('.alert');
    if(!alert) return;
    
    // Re-trigger animation
    alert.style.animation = 'none';
    alert.offsetHeight; // Trigger reflow
    alert.style.animation = '';
    
    // Auto-hide success message after 4 seconds
    if(flash.tipe === 'success'){
      setTimeout(() => {
        alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-8px)';
        setTimeout(() => {
          alert.style.display = 'none';
          // Clean URL
          try{
            const url = new URL(window.location.href);
            url.searchParams.delete('pesan');
            url.searchParams.delete('tipe');
            window.history.replaceState({}, document.title, url.toString());
          }catch(e){}
        }, 300);
      }, 4000);
    }
  }

  // ===== Toggle Password =====
  const password = document.getElementById("password");
  const toggle = document.getElementById("togglePassword");

  if (toggle && password) {
    toggle.addEventListener("click", () => {
      const isPass = password.type === "password";
      password.type = isPass ? "text" : "password";
      toggle.classList.toggle("is-show", isPass);
      toggle.style.transform = 'scale(0.9)';
      setTimeout(() => toggle.style.transform = '', 100);
    });
  }

  // ===== Input Focus Animations =====
  document.querySelectorAll('.input-group').forEach(group => {
    const input = group.querySelector('input');
    if(input){
      input.addEventListener('focus', () => group.classList.add('focused'));
      input.addEventListener('blur', () => group.classList.remove('focused'));
    }
  });

  // ===== Form Submit =====
  const form = document.querySelector(".login-form");
  const btn = document.getElementById("loginBtn");
  const text = btn?.querySelector(".text");

  if (form && btn) {
    form.addEventListener("submit", (e) => {
      const username = form.querySelector('input[name="username"]')?.value.trim();
      const pass = password?.value;
      
      if(!username || !pass){
        e.preventDefault();
        btn.style.animation = 'shake 0.3s ease';
        setTimeout(() => btn.style.animation = '', 300);
        
        // Show inline alert
        let alert = form.querySelector('.alert');
        if(!alert){
          alert = document.createElement('div');
          alert.className = 'alert alert-error';
          alert.innerHTML = '<span class="alert-icon">✗</span><span class="alert-text">Username dan password wajib diisi.</span><button class="alert-close" onclick="this.parentElement.remove()">×</button>';
          form.insertBefore(alert, form.firstChild);
          alert.style.animation = 'slideInAlert 0.3s ease';
        }
        return;
      }
      
      btn.classList.add('loading');
      btn.disabled = true;
      if(text) text.textContent = "Memproses...";
      setTimeout(() => {
        btn.classList.remove('loading');
        btn.disabled = false;
        if(text) text.textContent = "Masuk";
      }, 7000);
    });
  }

  // ===== CapsLock Warning =====
  if(password){
    password.addEventListener("keydown", (e) => {
      if(typeof e.getModifierState === "function" && e.getModifierState("CapsLock")){
        const alert = document.createElement('div');
        alert.className = 'alert alert-info';
        alert.innerHTML = '<span class="alert-icon">ℹ</span><span class="alert-text">CapsLock sedang aktif</span><button class="alert-close" onclick="this.parentElement.remove()">×</button>';
        alert.style.position = 'absolute';
        alert.style.top = '10px';
        alert.style.left = '50%';
        alert.style.transform = 'translateX(-50%)';
        alert.style.zIndex = '1000';
        alert.style.minWidth = '200px';
        document.querySelector('.login-box').appendChild(alert);
        setTimeout(() => alert.remove(), 2000);
      }
    });
  }

  // ===== Add Shake Animation =====
  const style = document.createElement('style');
  style.textContent = `@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-4px)}75%{transform:translateX(4px)}}`;
  document.head.appendChild(style);

  // ===== Debug Export =====
  if(window.location.hostname === 'localhost'){
    window.__LoginAnim = Animation;
  }

});