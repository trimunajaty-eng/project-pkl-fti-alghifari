<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$currentPage = 'dashboard';
$baseUrl = '';
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
  <link rel="stylesheet" href="../css/css_akademik/dashboard.css?v=2">
</head>
<body>
  <div class="app" id="app">
    <?php include __DIR__ . "/sidebarmenu.php"; ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <button type="button" id="menuToggle" class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="topbar-text">
            <h1 class="page-title">Dashboard Akademik</h1>
            <p class="page-subtitle">Halo, <?= htmlspecialchars($namaLogin); ?>.</p>
          </div>
        </div>

        <div class="topbar-right">
          <span class="topbar-pill">Panel Akademik</span>
        </div>
      </header>

      <div class="content">
        <section class="hero">
          <div class="hero-content">
            <div class="hero-kicker">Sistem Akademik</div>
            <h2 class="hero-title">Pantau data mahasiswa, nilai, dan tren pendaftaran dalam tampilan yang lebih ringkas, rapi, dan mudah digunakan.</h2>
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
              <div class="stat-meta">
                <div class="stat-label">Total Mahasiswa</div>
                <div class="stat-value"><?= number_format($totalMahasiswa); ?></div>
              </div>
            </div>
          </div>

          <div class="card stat-card accent-green">
            <div class="stat-head">
              <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M9 11h6m-6 4h6M8 3h8l5 5v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
                </svg>
              </span>
              <div class="stat-meta">
                <div class="stat-label">Data Nilai</div>
                <div class="stat-value"><?= number_format($totalNilai); ?></div>
              </div>
            </div>
          </div>
        </section>

        <section class="content-grid">
          <div class="card chart-card">
            <div class="card-head card-head-flex">
              <div>
                <h2>Tren Mahasiswa per Tahun</h2>
                <p class="section-subtitle">Filter berdasarkan program studi dan jenis kelamin.</p>
              </div>

              <div class="filter-popups">
                <div class="filter-popup">
                  <button type="button" class="filter-toggle">
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
                  <button type="button" class="filter-toggle">
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
              <div class="chart-summary">
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

        <section class="bottom-grid">
          <div class="card">
            <div class="card-head">
              <h2>5 Mahasiswa Terbaru</h2>
              <p class="section-subtitle">Klik baris untuk melihat biodata singkat mahasiswa.</p>
            </div>
            <div class="card-body">
              <?php if (!empty($mahasiswaTerbaru)): ?>
                <div class="table-wrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Program Studi</th>
                        <th>Kelas</th>
                        <th>JK</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($mahasiswaTerbaru as $m): ?>
                        <tr
                          class="student-row"
                          data-nim="<?= htmlspecialchars($m['nim']); ?>"
                          data-nama="<?= htmlspecialchars($m['nama_mahasiswa']); ?>"
                          data-prodi="<?= htmlspecialchars($m['program_studi']); ?>"
                          data-kelas="<?= htmlspecialchars($m['kelas']); ?>"
                          data-jk="<?= htmlspecialchars($m['jenis_kelamin']); ?>"
                          tabindex="0"
                        >
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
      </div>
    </main>
  </div>

  <div class="student-modal" id="studentModal">
    <div class="student-modal-overlay" id="studentModalOverlay"></div>
    <div class="student-modal-box" role="dialog" aria-modal="true" aria-labelledby="studentModalTitle">
      <div class="student-modal-head">
        <h3 id="studentModalTitle">Biodata Mahasiswa</h3>
        <button type="button" class="student-modal-close" id="studentModalClose" aria-label="Tutup popup">×</button>
      </div>
      <div class="student-modal-body">
        <div class="student-detail-item"><span>NIM</span><strong id="modalNim">-</strong></div>
        <div class="student-detail-item"><span>Nama</span><strong id="modalNama">-</strong></div>
        <div class="student-detail-item"><span>Program Studi</span><strong id="modalProdi">-</strong></div>
        <div class="student-detail-item"><span>Kelas</span><strong id="modalKelas">-</strong></div>
        <div class="student-detail-item"><span>Jenis Kelamin</span><strong id="modalJk">-</strong></div>
      </div>
    </div>
  </div>

  <script>
    window.dashboardChartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>
  <script src="../js/js_akademik/dashboard.js?v=2"></script>
</body>
</html>