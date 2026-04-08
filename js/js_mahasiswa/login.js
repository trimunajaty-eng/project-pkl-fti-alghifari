(function(){
  const card = document.getElementById("kartuLogin");
  const btns = document.querySelectorAll(".seg-btn");
  const jenisAkun = document.getElementById("jenisAkun");
  const togglePass = document.getElementById("togglePass");
  const passInput = document.getElementById("password");

  // efek kecil pas load
  setTimeout(() => card?.classList.add("ready"), 50);

  // toggle password
  togglePass?.addEventListener("click", () => {
    const isPass = passInput.type === "password";
    passInput.type = isPass ? "text" : "password";
    togglePass.textContent = isPass ? "🙈" : "👁";
  });

  // ganti jenis akun + ganti tema asset (css/js) sesuai pilihan
  function setAktif(jenis){
    btns.forEach(b => b.classList.toggle("active", b.dataset.jenis === jenis));
    jenisAkun.value = jenis;

    const isMhs = jenis === "mahasiswa";
    const css = isMhs ? window.CSS_MHS : window.CSS_ADM;
    const js  = isMhs ? window.JS_MHS  : window.JS_ADM;

    // Ganti CSS
    const link = document.querySelector('link[rel="stylesheet"]');
    if (link && css) link.href = css;

    // Animasi “switch”
    card.animate(
      [{ transform:"translateY(0) scale(1)", opacity:1 },
       { transform:"translateY(6px) scale(.99)", opacity:.92 },
       { transform:"translateY(0) scale(1)", opacity:1 }],
      { duration: 260, easing:"cubic-bezier(.2,.9,.2,1)" }
    );

    // Catatan:
    // untuk JS, kita tidak reload file JS otomatis biar tidak dobel event.
    // Jadi cukup CSS yang berubah, behavior tetap sama.
  }

  btns.forEach(b => b.addEventListener("click", () => setAktif(b.dataset.jenis)));
})();
