(() => {
  if (window.__DOSEN_NILAI_PAGE__) return;
  window.__DOSEN_NILAI_PAGE__ = true;

  const searchInput = document.getElementById("searchNilai");
  const tableBody = document.getElementById("nilaiTableBody");
  const modal = document.getElementById("nilaiModal");
  const modalBackdrop = document.getElementById("nilaiModalBackdrop");
  const modalClose = document.getElementById("nilaiModalClose");

  const detailNim = document.getElementById("detailNim");
  const detailNama = document.getElementById("detailNama");
  const detailProdi = document.getElementById("detailProdi");
  const detailKelas = document.getElementById("detailKelas");
  const detailTahun = document.getElementById("detailTahun");
  const detailSemester = document.getElementById("detailSemester");
  const detailTugas = document.getElementById("detailTugas");
  const detailUts = document.getElementById("detailUts");
  const detailUas = document.getElementById("detailUas");
  const detailKehadiran = document.getElementById("detailKehadiran");
  const detailAkhir = document.getElementById("detailAkhir");
  const detailGrade = document.getElementById("detailGrade");
  const detailKeterangan = document.getElementById("detailKeterangan");

  function openModal(button) {
    if (!button || !modal) return;

    detailNim.textContent = button.dataset.nim || "-";
    detailNama.textContent = button.dataset.nama || "-";
    detailProdi.textContent = button.dataset.prodi || "-";
    detailKelas.textContent = button.dataset.kelas || "-";
    detailTahun.textContent = button.dataset.tahun || "-";
    detailSemester.textContent = button.dataset.semester || "-";
    detailTugas.textContent = button.dataset.tugas || "0.00";
    detailUts.textContent = button.dataset.uts || "0.00";
    detailUas.textContent = button.dataset.uas || "0.00";
    detailKehadiran.textContent = button.dataset.kehadiran || "0.00";
    detailAkhir.textContent = button.dataset.akhir || "0.00";
    detailGrade.textContent = button.dataset.grade || "-";
    detailKeterangan.textContent = button.dataset.keterangan || "-";

    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  if (tableBody) {
    tableBody.addEventListener("click", (e) => {
      const btn = e.target.closest(".btn-detail");
      if (!btn) return;
      openModal(btn);
    });
  }

  if (modalBackdrop) {
    modalBackdrop.addEventListener("click", closeModal);
  }

  if (modalClose) {
    modalClose.addEventListener("click", closeModal);
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeModal();
    }
  });

  if (searchInput && tableBody) {
    searchInput.addEventListener("input", () => {
      const keyword = searchInput.value.trim().toLowerCase();
      const rows = tableBody.querySelectorAll("tr");

      rows.forEach((row) => {
        if (row.classList.contains("empty-row")) return;

        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(keyword) ? "" : "none";
      });
    });
  }
})();