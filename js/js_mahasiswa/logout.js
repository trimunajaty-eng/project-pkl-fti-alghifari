(() => {
  if (window.__MHS_LOGOUT__) return;
  window.__MHS_LOGOUT__ = true;

  const overlay = document.getElementById("logoutOverlay");
  const btnCancel = document.getElementById("btnCancel");

  // klik area luar card -> batal (kembali dashboard)
  if (overlay) {
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        if (btnCancel) btnCancel.click();
      }
    });
  }

  // ESC -> batal
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (btnCancel) btnCancel.click();
    }
  });
})();