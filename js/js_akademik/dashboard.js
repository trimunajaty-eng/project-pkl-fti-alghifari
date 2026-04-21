(function () {
  const body = document.body;
  const app = document.getElementById("app");
  const sidebar = document.getElementById("sidebar");
  const menuToggle = document.getElementById("menuToggle");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  const chartCanvas = document.getElementById("chartCanvas");
  const yMax = document.getElementById("yMax");
  const yMid = document.getElementById("yMid");

  const summaryTotal = document.getElementById("summaryTotal");
  const summaryPeak = document.getElementById("summaryPeak");
  const summaryTrend = document.getElementById("summaryTrend");

  const filterProdiLabel = document.getElementById("filterProdiLabel");
  const filterGenderLabel = document.getElementById("filterGenderLabel");

  const rawData = Array.isArray(window.dashboardChartData) ? window.dashboardChartData : [];
  const KEY_COLLAPSE = "ak_sidebar_collapsed";

  let selectedProdi = "all";
  let selectedGender = "all";

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
    if (isMobile()) return;

    const saved = localStorage.getItem(KEY_COLLAPSE);
    if (saved === "1") body.classList.add("sidebar-collapsed");
    else body.classList.remove("sidebar-collapsed");

    syncBurgerIcon();
  }

  function saveCollapseState() {
    const v = body.classList.contains("sidebar-collapsed") ? "1" : "0";
    localStorage.setItem(KEY_COLLAPSE, v);
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
    sidebarOverlay.addEventListener("click", closeMobileSidebar);
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
    renderChart();
  });

  const filterPopups = document.querySelectorAll(".filter-popup");
  const filterToggles = document.querySelectorAll(".filter-toggle");
  const popupItems = document.querySelectorAll(".popup-item");

  function closeAllPopups() {
    filterPopups.forEach(function (popup) {
      popup.classList.remove("open");
    });
  }

  filterToggles.forEach(function (toggle) {
    toggle.addEventListener("click", function (e) {
      e.stopPropagation();

      const wrapper = toggle.closest(".filter-popup");
      const isOpen = wrapper.classList.contains("open");

      closeAllPopups();

      if (!isOpen) {
        wrapper.classList.add("open");
      }
    });
  });

  popupItems.forEach(function (item) {
    item.addEventListener("click", function () {
      const group = item.getAttribute("data-filter-group");
      const value = item.getAttribute("data-value");
      const text = item.textContent.trim();

      document.querySelectorAll('.popup-item[data-filter-group="' + group + '"]').forEach(function (el) {
        el.classList.remove("active");
      });

      item.classList.add("active");

      if (group === "prodi") {
        selectedProdi = value;
        if (filterProdiLabel) filterProdiLabel.textContent = text;
      }

      if (group === "gender") {
        selectedGender = value;
        if (filterGenderLabel) filterGenderLabel.textContent = text;
      }

      closeAllPopups();
      renderChart();
    });
  });

  document.addEventListener("click", function (e) {
    if (!e.target.closest(".filter-popup")) {
      closeAllPopups();
    }
  });

  function getFilteredSeries() {
    const filtered = rawData.filter(function (item) {
      const matchProdi = selectedProdi === "all" || item.program_studi === selectedProdi;
      const matchGender = selectedGender === "all" || item.jenis_kelamin === selectedGender;
      return matchProdi && matchGender;
    });

    const grouped = {};

    filtered.forEach(function (item) {
      const tahun = String(item.tahun || "");
      if (!tahun) return;

      if (!grouped[tahun]) grouped[tahun] = 0;
      grouped[tahun] += Number(item.total || 0);
    });

    return Object.keys(grouped)
      .sort(function (a, b) { return Number(a) - Number(b); })
      .map(function (tahun) {
        return { tahun: tahun, total: grouped[tahun] };
      });
  }

  function formatNumber(number) {
    return new Intl.NumberFormat("id-ID").format(number || 0);
  }

  function updateSummary(series) {
    const total = series.reduce(function (sum, item) {
      return sum + item.total;
    }, 0);

    let peak = "-";
    let peakValue = 0;

    series.forEach(function (item) {
      if (item.total >= peakValue) {
        peakValue = item.total;
        peak = item.tahun;
      }
    });

    let trend = "Stabil";
    if (series.length >= 2) {
      const first = series[0].total;
      const last = series[series.length - 1].total;

      if (last > first) trend = "Naik";
      else if (last < first) trend = "Turun";
    }

    if (summaryTotal) summaryTotal.textContent = formatNumber(total);
    if (summaryPeak) summaryPeak.textContent = peak === "-" ? "-" : peak + " (" + formatNumber(peakValue) + ")";
    if (summaryTrend) summaryTrend.textContent = trend;
  }

  function buildPath(points) {
    if (!points.length) return "";
    if (points.length === 1) return "M " + points[0].x + " " + points[0].y;

    let d = "M " + points[0].x + " " + points[0].y;

    for (let i = 0; i < points.length - 1; i++) {
      const current = points[i];
      const next = points[i + 1];
      const cx = (current.x + next.x) / 2;

      d += " C " + cx + " " + current.y + ", " + cx + " " + next.y + ", " + next.x + " " + next.y;
    }

    return d;
  }

  const studentRows = document.querySelectorAll(".student-row");
  const studentModal = document.getElementById("studentModal");
  const studentModalOverlay = document.getElementById("studentModalOverlay");
  const studentModalClose = document.getElementById("studentModalClose");

  const modalNim = document.getElementById("modalNim");
  const modalNama = document.getElementById("modalNama");
  const modalProdi = document.getElementById("modalProdi");
  const modalKelas = document.getElementById("modalKelas");
  const modalJk = document.getElementById("modalJk");

  function openStudentModal(data) {
    if (!studentModal) return;

    if (modalNim) modalNim.textContent = data.nim || "-";
    if (modalNama) modalNama.textContent = data.nama || "-";
    if (modalProdi) modalProdi.textContent = data.prodi || "-";
    if (modalKelas) modalKelas.textContent = data.kelas || "-";
    if (modalJk) modalJk.textContent = data.jk || "-";

    studentModal.classList.add("show");
    body.style.overflow = "hidden";
  }

  function closeStudentModal() {
    if (!studentModal) return;
    studentModal.classList.remove("show");
    body.style.overflow = "";
  }

  studentRows.forEach(function (row) {
    row.addEventListener("click", function () {
      openStudentModal({
        nim: row.getAttribute("data-nim"),
        nama: row.getAttribute("data-nama"),
        prodi: row.getAttribute("data-prodi"),
        kelas: row.getAttribute("data-kelas"),
        jk: row.getAttribute("data-jk")
      });
    });

    row.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        row.click();
      }
    });
  });

  if (studentModalOverlay) {
    studentModalOverlay.addEventListener("click", closeStudentModal);
  }

  if (studentModalClose) {
    studentModalClose.addEventListener("click", closeStudentModal);
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeStudentModal();
      closeAllPopups();
      closeMobileSidebar();
    }
  });

  function renderChart() {
    if (!chartCanvas) return;

    const series = getFilteredSeries();
    updateSummary(series);

    const width = Math.max(chartCanvas.clientWidth, 320);
    const height = window.innerWidth <= 640 ? 200 : 220;

    if (!series.length) {
      chartCanvas.innerHTML = `
        <div style="height:${height}px;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:11px;">
          Belum ada data yang cocok dengan filter.
        </div>
      `;
      if (yMax) yMax.textContent = "0";
      if (yMid) yMid.textContent = "0";
      return;
    }

    const padding = { top: 16, right: 16, bottom: 30, left: 8 };
    const innerWidth = width - padding.left - padding.right;
    const innerHeight = height - padding.top - padding.bottom;

    const maxRaw = Math.max.apply(null, series.map(function (item) {
      return item.total;
    }).concat([1]));

    const maxValue = maxRaw <= 4 ? 4 : Math.ceil(maxRaw / 2) * 2;
    const midValue = Math.round(maxValue / 2);

    if (yMax) yMax.textContent = formatNumber(maxValue);
    if (yMid) yMid.textContent = formatNumber(midValue);

    const points = series.map(function (item, index) {
      const x = series.length === 1
        ? padding.left + innerWidth / 2
        : padding.left + (index * (innerWidth / (series.length - 1)));

      const y = padding.top + innerHeight - ((item.total / maxValue) * innerHeight);

      return {
        x: x,
        y: y,
        label: item.tahun,
        value: item.total
      };
    });

    const linePath = buildPath(points);
    const areaPath = linePath +
      " L " + points[points.length - 1].x + " " + (padding.top + innerHeight) +
      " L " + points[0].x + " " + (padding.top + innerHeight) +
      " Z";

    const gridLines = [0, 0.5, 1].map(function (step) {
      const y = padding.top + innerHeight - (step * innerHeight);
      return `<line x1="${padding.left}" y1="${y}" x2="${padding.left + innerWidth}" y2="${y}" stroke="rgba(148,163,184,.18)" stroke-dasharray="4 6"></line>`;
    }).join("");

    const xLabels = points.map(function (point) {
      return `<text x="${point.x}" y="${height - 8}" text-anchor="middle" fill="#64748b" font-size="9.5" font-family="Poppins, Arial, sans-serif">${point.label}</text>`;
    }).join("");

    const pointNodes = points.map(function (point) {
      return `
        <g>
          <circle cx="${point.x}" cy="${point.y}" r="4.2" fill="#fff" stroke="#ef4444" stroke-width="2.4"></circle>
          <text x="${point.x}" y="${point.y - 9}" text-anchor="middle" fill="#111827" font-size="9.5" font-weight="600" font-family="Poppins, Arial, sans-serif">${point.value}</text>
        </g>
      `;
    }).join("");

    chartCanvas.innerHTML = `
      <svg class="chart-svg" viewBox="0 0 ${width} ${height}" preserveAspectRatio="none">
        <defs>
          <linearGradient id="chartLineGradient" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0%" stop-color="#ef4444"></stop>
            <stop offset="100%" stop-color="#f97316"></stop>
          </linearGradient>
          <linearGradient id="chartAreaGradient" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stop-color="rgba(239,68,68,0.28)"></stop>
            <stop offset="100%" stop-color="rgba(249,115,22,0.03)"></stop>
          </linearGradient>
        </defs>

        ${gridLines}
        <path d="${areaPath}" fill="url(#chartAreaGradient)"></path>
        <path d="${linePath}" fill="none" stroke="url(#chartLineGradient)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
        ${pointNodes}
        ${xLabels}
      </svg>
    `;
  }

  renderChart();
  syncBurgerIcon();
})();