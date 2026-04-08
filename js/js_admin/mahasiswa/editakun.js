(function(){
  // Toast
  const toast = document.getElementById("toast");
  const toastCard = document.getElementById("toastCard");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");
  let toastTimer = null;

  function showToast(type, title, msg){
    if (!toast) return;
    toastCard.classList.remove("ok","err");
    if (type === "success") toastCard.classList.add("ok");
    else toastCard.classList.add("err");

    toastTitle.textContent = title || "Info";
    toastMsg.textContent = msg || "";

    toast.setAttribute("aria-hidden","false");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.setAttribute("aria-hidden","true"), 3200);
  }

  const ft = window.__FLASH_TIPE__ || "";
  const fp = window.__FLASH_PESAN__ || "";
  if (ft && fp){
    showToast(ft === "success" ? "success" : "error", ft === "success" ? "Berhasil" : "Gagal", fp);
  }

  // show password
  const showPass = document.getElementById("showPass");
  const passInput = document.getElementById("password_baru");
  showPass?.addEventListener("change", () => {
    if (!passInput) return;
    passInput.type = showPass.checked ? "text" : "password";
  });

  // reset nim checkbox: kalau dicentang, kosongkan password input biar tidak bingung
  const resetNim = document.getElementById("resetNim");
  resetNim?.addEventListener("change", () => {
    if (!passInput) return;
    if (resetNim.checked) passInput.value = "";
  });
})();