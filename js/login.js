document.addEventListener("DOMContentLoaded", () => {
  
  // ===== Animation Controller =====
  const Animation = {
    init: function(){
      // Trigger page load animation
      document.documentElement.classList.add('page-loading');
      
      // Hide loader after delay
      window.addEventListener("load", () => {
        setTimeout(() => {
          const loader = document.getElementById("loader");
          if(loader){
            loader.classList.add('hidden');
            setTimeout(() => {
              loader.style.display = "none";
            }, 300);
          }
        }, 800);
      });
    },
    
    reset: function(){
      // Reset all animations (for testing)
      document.querySelectorAll('[class*="animate-"]').forEach(el => {
        el.style.animation = 'none';
        el.offsetHeight; // Trigger reflow
        el.style.animation = '';
      });
    }
  };

  // Initialize animations
  Animation.init();

  // ===== Toggle Password =====
  const password = document.getElementById("password");
  const toggle = document.getElementById("togglePassword");

  if (toggle && password) {
    toggle.addEventListener("click", () => {
      const isPass = password.type === "password";
      password.type = isPass ? "text" : "password";
      toggle.classList.toggle("is-show", isPass);
      
      // Micro animation on toggle
      toggle.style.transform = 'scale(0.9)';
      setTimeout(() => toggle.style.transform = '', 100);
    });
  }

  // ===== Input Focus Animations =====
  document.querySelectorAll('.input-group').forEach(group => {
    const input = group.querySelector('input');
    if(input){
      input.addEventListener('focus', () => {
        group.classList.add('focused');
      });
      input.addEventListener('blur', () => {
        group.classList.remove('focused');
      });
    }
  });

  // ===== Form Submit with Animation =====
  const form = document.querySelector(".login-form");
  const btn = document.getElementById("loginBtn");
  const loaderBtn = document.querySelector(".btn-loader");
  const text = btn?.querySelector(".text");

  if (form && btn) {
    form.addEventListener("submit", (e) => {
      const username = form.querySelector('input[name="username"]')?.value.trim();
      const pass = password?.value;
      
      if(!username || !pass){
        e.preventDefault();
        
        // Shake animation on error
        btn.style.animation = 'shake 0.3s ease';
        setTimeout(() => btn.style.animation = '', 300);
        
        // Show alert if not exists
        let alert = form.querySelector('.alert');
        if(!alert){
          alert = document.createElement('div');
          alert.className = 'alert';
          alert.textContent = 'Username dan password wajib diisi.';
          form.insertBefore(alert, form.firstChild);
          setTimeout(() => alert.remove(), 3000);
        }
        
        return;
      }
      
      // Loading state
      btn.classList.add('loading');
      btn.disabled = true;
      if(text) text.textContent = "Memproses...";
      
      // Auto reset after 7 seconds (prevent stuck)
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
        // Show temporary alert
        const alert = document.createElement('div');
        alert.className = 'alert info';
        alert.textContent = '⚠ CapsLock sedang aktif';
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

  // ===== Add Shake Animation Dynamically =====
  const style = document.createElement('style');
  style.textContent = `
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-4px); }
      75% { transform: translateX(4px); }
    }
  `;
  document.head.appendChild(style);

  // ===== Handle Page Refresh =====
  if(performance.getEntriesByType("navigation")[0]?.type === 'reload'){
    document.body.classList.add('page-refreshed');
  }

  // ===== Debug Export =====
  if(window.location.hostname === 'localhost'){
    window.__LoginAnim = Animation;
  }

});