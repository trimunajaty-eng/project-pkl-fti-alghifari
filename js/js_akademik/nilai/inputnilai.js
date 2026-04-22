(function () {
  const body = document.body;
  const html = document.documentElement;

  const sidebar = document.getElementById("sidebar");
  const menuToggle = document.getElementById("menuToggle");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  const filterModal = document.getElementById("filterModal");
  const filterModalOverlay = document.getElementById("filterModalOverlay");
  const btnOpenFilter = document.getElementById("btnOpenFilter");
  const btnCloseFilter = document.getElementById("btnCloseFilter");

  const toast = document.getElementById("toast");
  const toastCard = document.getElementById("toastCard");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");

  const tugas = document.querySelector('input[name="tugas"]');
  const uts = document.querySelector('input[name="uts"]');
  const uas = document.querySelector('input[name="uas"]');
  const kehadiran = document.querySelector('input[name="kehadiran"]');
  const nilaiAkhir = document.getElementById("nilai_akhir");
  const grade = document.getElementById("grade");
  const keterangan = document.getElementById("keterangan");

  const studentSearch = document.getElementById("studentSearch");
  const studentList = document.getElementById("studentList");
  const emptySearch = document.getElementById("emptySearch");
  const workspace = document.getElementById("workspace");
  const studentLinks = document.querySelectorAll("[data-student-link='1']");

  const filterJurusan = document.getElementById("filterJurusan");
  const filterDosen = document.getElementById("filterDosen");

  const KEY_COLLAPSE = "ak_sidebar_collapsed";

  function isMobile() {
    return window.innerWidth <= 860;
  }

  function syncBurgerIcon() {
    if (!menuToggle) return;

    const isX =
      (isMobile() && body.classList.contains("sidebar-open")) ||
      (!isMobile() && body.classList.contains("sidebar-collapsed"));

    menuToggle.classList.toggle("is-x", isX);
  }

  function applyPersistedCollapse() {
    if (isMobile()) {
      body.classList.remove("sidebar-collapsed");
      html.classList.remove("sidebar-collapsed-init");
      return;
    }

    const saved = localStorage.getItem(KEY_COLLAPSE);

    if (saved === "1") {
      body.classList.add("sidebar-collapsed");
    } else {
      body.classList.remove("sidebar-collapsed");
    }

    html.classList.remove("sidebar-collapsed-init");
    syncBurgerIcon();
  }

  function saveCollapseState() {
    const value = body.classList.contains("sidebar-collapsed") ? "1" : "0";
    localStorage.setItem(KEY_COLLAPSE, value);
  }

  function closeMobileSidebar() {
    body.classList.remove("sidebar-open");
    syncBurgerIcon();
  }

  function toggleSidebar() {
    if (isMobile()) {
      body.classList.toggle("sidebar-open");
    } else {
      body.classList.toggle("sidebar-collapsed");
      saveCollapseState();
    }
    syncBurgerIcon();
  }

  applyPersistedCollapse();

  if (menuToggle) {
    menuToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      toggleSidebar();
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function () {
      closeMobileSidebar();
    });
  }

  document.addEventListener("click", function (e) {
    if (isMobile() && body.classList.contains("sidebar-open")) {
      const insideSidebar = sidebar && sidebar.contains(e.target);
      const clickToggle = menuToggle && menuToggle.contains(e.target);

      if (!insideSidebar && !clickToggle) {
        closeMobileSidebar();
      }
    }
  });

  window.addEventListener("resize", function () {
    if (!isMobile()) {
      closeMobileSidebar();
      applyPersistedCollapse();
    } else {
      syncBurgerIcon();
    }
  });

  function openModal() {
    if (!filterModal) return;
    filterModal.classList.add("show");
    filterModal.setAttribute("aria-hidden", "false");
    body.style.overflow = "hidden";
  }

  function closeModal() {
    if (!filterModal) return;
    filterModal.classList.remove("show");
    filterModal.setAttribute("aria-hidden", "true");
    body.style.overflow = "";
  }

  if (btnOpenFilter) {
    btnOpenFilter.addEventListener("click", openModal);
  }

  if (btnCloseFilter) {
    btnCloseFilter.addEventListener("click", closeModal);
  }

  if (filterModalOverlay) {
    filterModalOverlay.addEventListener("click", closeModal);
  }

  function showToast(type, message) {
    if (!toast || !toastCard || !toastTitle || !toastMsg || !message) return;

    const finalType = ["success", "error", "info"].includes(type) ? type : "info";

    toastCard.classList.remove("success", "error", "info");
    toastCard.classList.add(finalType);

    toastTitle.textContent =
      finalType === "success" ? "Berhasil" :
      finalType === "error" ? "Gagal" : "Info";

    toastMsg.textContent = message;
    toast.classList.add("show");

    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(function () {
      toast.classList.remove("show");
    }, 2800);
  }

  if (window.__FLASH__ && window.__FLASH__.pesan) {
    showToast(window.__FLASH__.tipe || "info", window.__FLASH__.pesan);

    try {
      const url = new URL(window.location.href);
      url.searchParams.delete("pesan");
      url.searchParams.delete("tipe");
      window.history.replaceState({}, document.title, url.toString());
    } catch (e) {}
  }

  function hitungNilai() {
    if (!tugas || !uts || !uas || !kehadiran || !nilaiAkhir || !grade || !keterangan) return;

    const nTugas = parseFloat(tugas.value || 0);
    const nUts = parseFloat(uts.value || 0);
    const nUas = parseFloat(uas.value || 0);
    const nKehadiran = parseFloat(kehadiran.value || 0);

    const hasil = ((nTugas * 0.25) + (nUts * 0.25) + (nUas * 0.35) + (nKehadiran * 0.15));
    const finalNilai = isNaN(hasil) ? 0 : hasil;

    nilaiAkhir.value = finalNilai.toFixed(2);

    let finalGrade = "E";
    let finalKet = "Tidak Lulus";

    if (finalNilai >= 85) {
      finalGrade = "A";
      finalKet = "Lulus";
    } else if (finalNilai >= 75) {
      finalGrade = "B";
      finalKet = "Lulus";
    } else if (finalNilai >= 65) {
      finalGrade = "C";
      finalKet = "Lulus";
    } else if (finalNilai >= 50) {
      finalGrade = "D";
      finalKet = "Tidak Lulus";
    }

    grade.value = finalGrade;
    keterangan.value = finalKet;
  }

  [tugas, uts, uas, kehadiran].forEach(function (el) {
    if (el) {
      el.addEventListener("input", hitungNilai);
    }
  });

  if (studentSearch && studentList) {
    studentSearch.addEventListener("input", function () {
      const keyword = studentSearch.value.trim().toLowerCase();
      const items = studentList.querySelectorAll(".student-item");
      let visibleCount = 0;

      items.forEach(function (item) {
        const haystack = (item.getAttribute("data-search") || "").toLowerCase();
        const match = keyword === "" || haystack.indexOf(keyword) !== -1;
        item.hidden = !match;
        if (match) visibleCount++;
      });

      if (emptySearch) {
        emptySearch.hidden = visibleCount !== 0;
      }
    });
  }

  studentLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      if (!workspace) return;
      e.preventDefault();

      studentLinks.forEach(function (item) {
        item.classList.remove("is-leaving");
      });

      link.classList.add("is-leaving");
      workspace.classList.add("selected");

      setTimeout(function () {
        window.location.href = link.href;
      }, 220);
    });
  });

  const allDosen = Array.isArray(window.__ALL_DOSEN__) ? window.__ALL_DOSEN__ : [];
  const selectedDosenId = Number(window.__SELECTED_DOSEN_ID__ || 0);

  function renderDosenOptions(jurusan, selectedId) {
    if (!filterDosen) return;

    filterDosen.innerHTML = '<option value="">-- Pilih Dosen --</option>';

    if (!jurusan) return;

    const filtered = allDosen.filter(function (item) {
      return String(item.program_studi || '').trim() === String(jurusan).trim();
    });

    filtered.forEach(function (dosen) {
      const option = document.createElement("option");
      option.value = dosen.id_dosen;
      option.textContent = dosen.nama_dosen + " (" + dosen.kode_dosen + ")";
      if (Number(dosen.id_dosen) === Number(selectedId)) {
        option.selected = true;
      }
      filterDosen.appendChild(option);
    });
  }

  if (filterJurusan && filterDosen) {
    renderDosenOptions(filterJurusan.value, selectedDosenId);

    filterJurusan.addEventListener("change", function () {
      renderDosenOptions(filterJurusan.value, 0);
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeModal();
      closeMobileSidebar();
    }
  });

  hitungNilai();
  syncBurgerIcon();
})();