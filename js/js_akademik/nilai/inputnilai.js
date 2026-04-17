(function () {
  const sidebar = document.getElementById("sidebar");
  const menuToggle = document.getElementById("menuToggle");

  const filterModal = document.getElementById("filterModal");
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

  if (sidebar && menuToggle) {
    menuToggle.addEventListener("click", function () {
      sidebar.classList.toggle("show");
    });

    document.addEventListener("click", function (e) {
      if (window.innerWidth > 860) return;

      const insideSidebar = sidebar.contains(e.target);
      const clickToggle = menuToggle.contains(e.target);

      if (!insideSidebar && !clickToggle) {
        sidebar.classList.remove("show");
      }
    });
  }

  if (btnOpenFilter && filterModal) {
    btnOpenFilter.addEventListener("click", function () {
      filterModal.classList.add("show");
      filterModal.setAttribute("aria-hidden", "false");
    });
  }

  if (btnCloseFilter && filterModal) {
    btnCloseFilter.addEventListener("click", function () {
      filterModal.classList.remove("show");
      filterModal.setAttribute("aria-hidden", "true");
    });
  }

  if (filterModal) {
    filterModal.addEventListener("click", function (e) {
      if (e.target === filterModal) {
        filterModal.classList.remove("show");
        filterModal.setAttribute("aria-hidden", "true");
      }
    });
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

  hitungNilai();
})();