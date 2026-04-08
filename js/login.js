document.addEventListener("DOMContentLoaded", () => {
  // =========================
  // Loader halaman login
  // =========================
  window.addEventListener("load", () => {
    window.setTimeout(() => {
      const loader = document.getElementById("loader");
      const wrapper = document.querySelector(".login-wrapper");

      if (loader) loader.style.display = "none";
      if (wrapper) wrapper.style.display = "block";
    }, 800);
  });

  // =========================
  // Toggle password
  // =========================
  const password = document.getElementById("password");
  const toggle = document.getElementById("togglePassword");

  if (toggle && password) {
    toggle.addEventListener("click", () => {
      const isPass = password.type === "password";
      password.type = isPass ? "text" : "password";

      // rapih: pakai class untuk ganti icon
      toggle.classList.toggle("is-show", isPass);
    });
  }

  // =========================
  // Animasi tombol login saat submit
  // =========================
  const form = document.querySelector(".login-form");
  const btn = document.getElementById("loginBtn");
  const loaderBtn = document.querySelector(".btn-loader");
  const text = document.querySelector(".login-btn .text");

  if (form) {
    form.addEventListener("submit", () => {
      if (loaderBtn) loaderBtn.style.display = "inline-block";
      if (text) text.textContent = "Memproses...";
      if (btn) btn.disabled = true;
    });
  }
});