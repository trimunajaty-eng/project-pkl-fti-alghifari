(function(){
  const tabs = document.querySelectorAll('.im-tab');

  const panels = {
    profil: document.getElementById('tab-profil'),
    alamat: document.getElementById('tab-alamat'),
    ortu: document.getElementById('tab-ortu'),
    wali: document.getElementById('tab-wali'),
    asalsekolah: document.getElementById('tab-asalsekolah'),
    asalpt: document.getElementById('tab-asalpt'),
  };

  function openTab(key){
    tabs.forEach(t => {
      const active = t.dataset.tab === key;
      t.classList.toggle('active', active);
      t.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    Object.keys(panels).forEach(k => {
      if (panels[k]) panels[k].classList.toggle('active', k === key);
    });
  }

  tabs.forEach(t => t.addEventListener('click', () => openTab(t.dataset.tab)));
  openTab('profil');

  // =========================
  // Toast helper
  // =========================
  const toast = document.getElementById("toast");
  const toastCard = document.getElementById("toastCard");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");

  let toastTimer = null;

  function showToast(type, title, msg){
    if (!toast) return;

    toastCard.classList.remove("ok","err");
    if (type === "success") toastCard.classList.add("ok");
    else if (type === "error") toastCard.classList.add("err");

    toastTitle.textContent = title || "Info";
    toastMsg.textContent = msg || "";

    toast.classList.add("open");
    toast.setAttribute("aria-hidden","false");

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.classList.remove("open");
      toast.setAttribute("aria-hidden","true");
    }, 3200);
  }

  // flash dari URL (?tipe&pesan)
  const ft = window.__FLASH_TIPE__ || "";
  const fp = window.__FLASH_PESAN__ || "";
  if (ft && fp){
    showToast(ft === "success" ? "success" : "error", ft === "success" ? "Berhasil" : "Gagal", fp);
  }

  // =========================
  // Loading helper
  // =========================
  const loading = document.getElementById("loading");
  function openLoading(){
    loading?.classList.add("open");
    loading?.setAttribute("aria-hidden","false");
  }
  function closeLoading(){
    loading?.classList.remove("open");
    loading?.setAttribute("aria-hidden","true");
  }

  // =========================
  // Validasi + badge tanda seru di tab
  // =========================
  const optionalNames = new Set([
    // KPS optional
    "penerima_kps", "no_kps",
    // Wali optional
    "nama_wali","tanggal_lahir_wali","pendidikan_wali","pekerjaan_wali","penghasilan_wali",
    // Asal PT optional
    "sks_diakui","asal_perguruan_tinggi","asal_program_studi",
  ]);

  function isEmpty(el){
    if (!el) return true;
    const v = (el.value || "").trim();
    return v === "";
  }

  function panelHasError(panel){
    if (!panel) return false;
    const inputs = panel.querySelectorAll("input, select, textarea");
    for (const el of inputs){
      const name = el.getAttribute("name") || "";
      if (optionalNames.has(name)) continue;

      // hanya cek yang required ATAU name-nya memang wajib (karena sebagian sudah required di HTML)
      const required = el.hasAttribute("required");
      if (!required) continue;

      if (isEmpty(el)) return true;
    }
    return false;
  }

  function refreshBadges(){
    const mapTabToPanel = {
      profil: panels.profil,
      alamat: panels.alamat,
      ortu: panels.ortu,
      wali: panels.wali,
      asalsekolah: panels.asalsekolah,
      asalpt: panels.asalpt,
    };

    Object.keys(mapTabToPanel).forEach(key => {
      const bad = panelHasError(mapTabToPanel[key]);
      const badge = document.querySelector('.im-alert[data-alert="'+key+'"]');
      if (badge) badge.classList.toggle("show", bad);
    });
  }

  // refresh saat user mengetik
  document.addEventListener("input", refreshBadges);
  document.addEventListener("change", refreshBadges);
  refreshBadges();

  // =========================
  // Submit form
  // =========================
  const form = document.getElementById("imForm");
  if (form){
    form.addEventListener("submit", (e) => {
      refreshBadges();

      // cek semua required di form (kecuali optional)
      const requiredEls = form.querySelectorAll("[required]");
      for (const el of requiredEls){
        const name = el.getAttribute("name") || "";
        if (optionalNames.has(name)) continue;

        if (isEmpty(el)){
          e.preventDefault();

          // buka tab yang berisi field kosong
          const panel = el.closest(".im-panel");
          if (panel){
            const id = panel.id; // tab-profil dst
            const key = (id || "").replace("tab-","");
            if (key) openTab(key);
          }

          el.focus();
          showToast("error","Gagal","Masih ada field wajib yang belum diisi.");
          return;
        }
      }

      // lanjut submit dengan loading
      e.preventDefault();
      openLoading();
      setTimeout(() => form.submit(), 700);
    });
  }
})();