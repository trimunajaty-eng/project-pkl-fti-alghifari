<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$currentPage = 'dashboard';
$namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

$totalMahasiswa = 0;
$totalNilai = 0;

$q1 = $conn->query("SELECT COUNT(*) AS total FROM mahasiswa");
if ($q1 && $row = $q1->fetch_assoc()) {
  $totalMahasiswa = (int)$row['total'];
}

$q2 = $conn->query("SELECT COUNT(*) AS total FROM nilai_mahasiswa");
if ($q2 && $row = $q2->fetch_assoc()) {
  $totalNilai = (int)$row['total'];
}

$mahasiswaTerbaru = [];
$sqlMahasiswa = "SELECT nim, nama_mahasiswa, program_studi, kelas, jenis_kelamin
                 FROM mahasiswa
                 ORDER BY id_mahasiswa DESC
                 LIMIT 5";
$resMahasiswa = $conn->query($sqlMahasiswa);
if ($resMahasiswa) {
  while ($row = $resMahasiswa->fetch_assoc()) {
    $mahasiswaTerbaru[] = $row;
  }
}

$chartData = [];
$sqlChart = "
  SELECT 
    YEAR(tanggal_registrasi) AS tahun,
    program_studi,
    jenis_kelamin,
    COUNT(*) AS total
  FROM mahasiswa
  WHERE tanggal_registrasi IS NOT NULL
  GROUP BY YEAR(tanggal_registrasi), program_studi, jenis_kelamin
  ORDER BY YEAR(tanggal_registrasi) ASC
";
$resChart = $conn->query($sqlChart);
if ($resChart) {
  while ($row = $resChart->fetch_assoc()) {
    $chartData[] = [
      'tahun' => (int)$row['tahun'],
      'program_studi' => $row['program_studi'],
      'jenis_kelamin' => $row['jenis_kelamin'],
      'total' => (int)$row['total'],
    ];
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Akademik</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/css_akademik/dashboard.css">
</head>
<body>
  <div class="app">
    <?php include __DIR__ . "/sidebarmenu.php"; ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button type="button" id="menuToggle" class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div>
            <h1 class="page-title">Dashboard Akademik</h1>
            <p class="page-subtitle">Halo, <?= htmlspecialchars($namaLogin); ?>.</p>
          </div>
        </div>
      </header>

      <section class="hero">
        <div class="hero-content">
          <div class="hero-kicker">Sistem Akademik</div>
          <h2 class="hero-title">Pantau ringkasan data mahasiswa dan perkembangan tren pendaftaran dengan tampilan yang lebih ringkas.</h2>
        </div>
      </section>

      <section class="stats">
        <div class="card stat-card accent-red">
          <div class="stat-head">
            <span class="stat-icon">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
              </svg>
            </span>
            <div class="stat-label">Total Mahasiswa</div>
          </div>
          <div class="stat-value"><?= number_format($totalMahasiswa); ?></div>
        </div>

        <div class="card stat-card accent-green">
          <div class="stat-head">
            <span class="stat-icon">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M9 11h6m-6 4h6M8 3h8l5 5v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
              </svg>
            </span>
            <div class="stat-label">Data Nilai</div>
          </div>
          <div class="stat-value"><?= number_format($totalNilai); ?></div>
        </div>
      </section>

      <section class="content-grid single-grid">
        <div class="card chart-card">
          <div class="card-head card-head-flex">
            <div>
              <h2>Tren Mahasiswa per Tahun</h2>
              <p class="section-subtitle">Analisis jumlah mahasiswa berdasarkan filter program studi dan jenis kelamin.</p>
            </div>

            <div class="filter-popups">
              <div class="filter-popup">
                <button type="button" class="filter-toggle" data-popup-target="popupProdi">
                  <span>Program Studi</span>
                  <strong id="filterProdiLabel">Semua Program Studi</strong>
                </button>

                <div class="popup-menu" id="popupProdi">
                  <button type="button" class="popup-item active" data-filter-group="prodi" data-value="all">Semua Program Studi</button>
                  <button type="button" class="popup-item" data-filter-group="prodi" data-value="Teknik Informatika S1">Teknik Informatika S1</button>
                  <button type="button" class="popup-item" data-filter-group="prodi" data-value="Sistem Informasi S1">Sistem Informasi S1</button>
                </div>
              </div>

              <div class="filter-popup">
                <button type="button" class="filter-toggle" data-popup-target="popupGender">
                  <span>Jenis Kelamin</span>
                  <strong id="filterGenderLabel">Semua</strong>
                </button>

                <div class="popup-menu" id="popupGender">
                  <button type="button" class="popup-item active" data-filter-group="gender" data-value="all">Semua</button>
                  <button type="button" class="popup-item" data-filter-group="gender" data-value="Laki-laki">Laki-laki</button>
                  <button type="button" class="popup-item" data-filter-group="gender" data-value="Perempuan">Perempuan</button>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="chart-summary" id="chartSummary">
              <div class="summary-chip">
                <span class="summary-label">Total Filter</span>
                <strong id="summaryTotal">0</strong>
              </div>
              <div class="summary-chip">
                <span class="summary-label">Tahun Puncak</span>
                <strong id="summaryPeak">-</strong>
              </div>
              <div class="summary-chip">
                <span class="summary-label">Arah Tren</span>
                <strong id="summaryTrend">Stabil</strong>
              </div>
            </div>

            <div class="chart-box">
              <div class="chart-y-axis">
                <span id="yMax">0</span>
                <span id="yMid">0</span>
                <span>0</span>
              </div>
              <div class="chart-canvas" id="chartCanvas"></div>
            </div>
          </div>
        </div>
      </section>

      <section class="bottom-grid single-grid">
        <div class="card">
          <div class="card-head">
            <h2>5 Mahasiswa Terbaru</h2>
            <p class="section-subtitle">Data mahasiswa terbaru yang masuk ke sistem.</p>
          </div>
          <div class="card-body">
            <?php if (!empty($mahasiswaTerbaru)): ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th style="width: 18%;">NIM</th>
                      <th style="width: 24%;">Nama</th>
                      <th style="width: 24%;">Program Studi</th>
                      <th style="width: 18%;">Kelas</th>
                      <th style="width: 16%;">JK</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mahasiswaTerbaru as $m): ?>
                      <tr>
                        <td><?= htmlspecialchars($m['nim']); ?></td>
                        <td><?= htmlspecialchars($m['nama_mahasiswa']); ?></td>
                        <td><?= htmlspecialchars($m['program_studi']); ?></td>
                        <td><?= htmlspecialchars($m['kelas']); ?></td>
                        <td>
                          <span class="gender-badge <?= strtolower($m['jenis_kelamin']) === 'laki-laki' ? 'male' : 'female'; ?>">
                            <?= htmlspecialchars($m['jenis_kelamin']); ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="empty-text">Belum ada data mahasiswa.</div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    window.dashboardChartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>
  <script src="../js/js_akademik/dashboard.js"></script>
</body>
</html>