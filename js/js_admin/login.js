(function () {
  // ===== toast helper =====
  const toast = document.getElementById("toast");
  const toastCard = document.getElementById("toastCard");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");

  function showToast(type, message) {
    if (!toast || !toastCard || !toastTitle || !toastMsg) return;
    if (!message) return;

    const t = (type || "info").toLowerCase();
    toastCard.classList.remove("success", "error", "info");
    toastCard.classList.add(t === "success" ? "success" : (t === "error" ? "error" : "info"));

    toastTitle.textContent = t === "success" ? "Berhasil" : (t === "error" ? "Gagal" : "Info");
    toastMsg.textContent = message;

    toast.classList.add("show");
    toast.setAttribute("aria-hidden", "false");

    clearTimeout(showToast.__t);
    showToast.__t = setTimeout(() => {
      toast.classList.remove("show");
      toast.setAttribute("aria-hidden", "true");
    }, 2800);
  }

  // tampilkan pesan dari server (logout / error)
  if (window.__FLASH__ && window.__FLASH__.pesan) {
    const tipe = window.__FLASH__.tipe || "info";
    showToast(tipe, window.__FLASH__.pesan);

    // bersihkan query string supaya toast tidak muncul lagi saat refresh
    try {
      const url = new URL(window.location.href);
      url.searchParams.delete("pesan");
      url.searchParams.delete("tipe");
      window.history.replaceState({}, document.title, url.toString());
    } catch (e) {}
  }

  // ===== toggle password =====
  const password = document.getElementById("password");
  const toggle = document.getElementById("togglePass");

  toggle?.addEventListener("click", () => {
    if (!password) return;
    const isPass = password.getAttribute("type") === "password";
    password.setAttribute("type", isPass ? "text" : "password");
    toggle.classList.toggle("is-on", isPass);
  });

  // ===== UX: prevent double submit + basic validation =====
  const form = document.getElementById("formLogin");
  const btn = document.getElementById("btnMasuk");
  const user = document.getElementById("username");

  function setLoading(isLoading) {
    if (!btn) return;
    btn.disabled = !!isLoading;
    btn.dataset._text = btn.dataset._text || btn.textContent;
    btn.textContent = isLoading ? "MEMPROSES..." : btn.dataset._text;
  }

  form?.addEventListener("submit", (e) => {
    const u = (user?.value || "").trim();
    const p = (password?.value || "");

    if (!u || !p) {
      e.preventDefault();
      showToast("error", "Username dan password wajib diisi.");
      return;
    }

    // anti double click
    setLoading(true);

    // kalau request gagal/halaman tidak pindah, balikin tombol setelah beberapa detik
    setTimeout(() => setLoading(false), 7000);
  });

  // ===== CapsLock warning (opsional toast) =====
  password?.addEventListener("keydown", (e) => {
    // beberapa browser support getModifierState
    if (typeof e.getModifierState === "function") {
      if (e.getModifierState("CapsLock")) {
        showToast("info", "CapsLock sedang aktif.");
      }
    }
  });

})();