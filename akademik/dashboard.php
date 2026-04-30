<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$currentPage = 'dashboard';
$baseUrl = '';
$namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

// Filter sederhana
$filterProdi = trim($_GET['prodi'] ?? 'all');
$filterTahun = trim($_GET['tahun'] ?? date('Y'));

// Total mahasiswa
$totalMahasiswa = 0;
$qTotal = $conn->prepare("SELECT COUNT(*) AS total FROM mahasiswa WHERE program_studi = ? OR ? = 'all'");
$qTotal->bind_param("ss", $filterProdi, $filterProdi);
$qTotal->execute();
$resTotal = $qTotal->get_result();
if ($row = $resTotal->fetch_assoc()) {
  $totalMahasiswa = (int)$row['total'];
}
$qTotal->close();

// Data chart: mahasiswa per tahun (5 tahun terakhir)
$chartData = [];
$qChart = $conn->prepare("
  SELECT YEAR(tanggal_registrasi) AS tahun, COUNT(*) AS total
  FROM mahasiswa
  WHERE tanggal_registrasi IS NOT NULL
  GROUP BY YEAR(tanggal_registrasi)
  ORDER BY tahun ASC
  LIMIT 5
");
$qChart->execute();
$resChart = $qChart->get_result();
while ($row = $resChart->fetch_assoc()) {
  $chartData[] = ['tahun' => (int)$row['tahun'], 'total' => (int)$row['total']];
}
$qChart->close();

// Data tabel: 10 mahasiswa terbaru
$mahasiswaList = [];
$qMhs = $conn->prepare("
  SELECT nim, nama_mahasiswa, program_studi, kelas, jenis_kelamin, tanggal_registrasi
  FROM mahasiswa
  WHERE program_studi = ? OR ? = 'all'
  ORDER BY tanggal_registrasi DESC, id_mahasiswa DESC
  LIMIT 10
");
$qMhs->bind_param("ss", $filterProdi, $filterProdi);
$qMhs->execute();
$resMhs = $qMhs->get_result();
while ($row = $resMhs->fetch_assoc()) {
  $mahasiswaList[] = $row;
}
$qMhs->close();

// Opsi program studi untuk filter
$prodiList = ['all' => 'Semua Program Studi'];
$qProdi = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'program_studi' AND is_active = 1 ORDER BY urutan ASC");
if ($qProdi) {
  while ($r = $qProdi->fetch_assoc()) {
    $prodiList[$r['value']] = $r['value'];
  }
}

// Hitung tren untuk badge (opsional)
$trendBadge = null;
if (count($chartData) >= 2) {
  $last = end($chartData)['total'];
  $prev = prev($chartData)['total'];
  if ($prev > 0) {
    $diff = (($last - $prev) / $prev) * 100;
    $trendBadge = [
      'class' => $diff >= 0 ? '' : 'down',
      'icon' => $diff > 10 ? '📈' : ($diff < -10 ? '📉' : ($diff >= 0 ? '↑' : '↓')),
      'text' => $diff >= 0 ? '+' . number_format($diff, 1) . '%' : number_format($diff, 1) . '%'
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
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- CSS -->
  <link rel="stylesheet" href="../css/css_akademik/dashboard.css?v=5">
  
  <!-- Sidebar Collapse Init -->
  <script>
    (function () {
      try {
        if (window.innerWidth > 860 && localStorage.getItem('ak_sidebar_collapsed') === '1') {
          document.documentElement.classList.add('sidebar-collapsed-init');
        }
      } catch (e) {}
    })();
  </script>
</head>
<body>
  <div class="app" id="app">
    <?php include __DIR__ . "/sidebarmenu.php"; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <div class="top-left">
          <button type="button" id="menuToggle" class="menu-toggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
          </button>
          <div class="topbar-text">
            <h1 class="page-title">Dashboard Akademik</h1>
            <p class="page-subtitle">Pantau data mahasiswa secara ringkas</p>
          </div>
        </div>
        <div class="top-right">
          <span class="user-greeting">Halo, <strong><?= htmlspecialchars($namaLogin); ?></strong></span>
        </div>
      </header>

      <!-- Content -->
      <div class="content">
        
        <!-- Summary Cards -->
        <section class="summary-strip">
          <div class="summary-card">
            <span class="summary-label">Total Mahasiswa</span>
            <strong class="summary-value"><?= number_format($totalMahasiswa); ?></strong>
          </div>
          <div class="summary-card">
            <span class="summary-label">Tahun Aktif</span>
            <strong class="summary-value"><?= htmlspecialchars($filterTahun); ?></strong>
          </div>
          <div class="summary-card">
            <span class="summary-label">Program Studi</span>
            <strong class="summary-value"><?= $filterProdi === 'all' ? 'Semua' : htmlspecialchars($filterProdi); ?></strong>
          </div>
        </section>

        <!-- Chart Section: Trading-Style Wave -->
        <section class="card chart-section">
          <div class="card-head card-head-flex">
            <div class="chart-title-group">
              <h2>Tren Pendaftaran Mahasiswa</h2>
              <p class="section-subtitle">Data 5 tahun terakhir</p>
            </div>
            <?php if ($trendBadge): ?>
              <span class="chart-trend-badge <?= $trendBadge['class']; ?>">
                <span><?= $trendBadge['icon']; ?></span> <?= $trendBadge['text']; ?>
              </span>
            <?php endif; ?>
          </div>
          
          <div class="card-body">
            <div class="chart-box">
              <!-- Y-Axis Labels -->
              <div class="chart-y-axis">
                <span id="yMax">0</span>
                <span id="yMid">0</span>
                <span>0</span>
              </div>
              
              <!-- Chart Canvas (SVG will be injected here) -->
              <div class="chart-canvas" id="chartCanvas" tabindex="0" aria-label="Grafik tren pendaftaran mahasiswa"></div>
            </div>
            
            <!-- X-Axis Labels (rendered by JS, but container here for structure) -->
            <div class="chart-x-labels" id="chartXLabels"></div>
          </div>
        </section>

        <!-- Table Section: Latest Students -->
        <section class="card table-section">
          <div class="card-head card-head-flex">
            <div>
              <h2>10 Mahasiswa Terbaru</h2>
              <p class="section-subtitle">Data mahasiswa yang baru terdaftar</p>
            </div>
            <form method="get" class="filter-form" id="filterForm">
              <select name="prodi" id="filterProdi" onchange="this.form.submit()" aria-label="Filter program studi">
                <?php foreach ($prodiList as $val => $label): ?>
                  <option value="<?= htmlspecialchars($val); ?>" <?= $filterProdi === $val ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($label); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
          
          <div class="card-body">
            <?php if (empty($mahasiswaList)): ?>
              <div class="empty-box empty-box-large">
                <div class="empty-icon">🔍</div>
                <strong>Belum ada data</strong><br>
                <span style="font-size:10.5px;color:var(--text-muted);">
                  Tidak ada mahasiswa untuk filter yang dipilih.
                </span>
              </div>
            <?php else: ?>
              <div class="table-wrap" role="region" aria-label="Tabel mahasiswa terbaru" tabindex="0">
                <table class="table" role="table">
                  <thead>
                    <tr>
                      <th scope="col">No</th>
                      <th scope="col">NIM</th>
                      <th scope="col">Nama Mahasiswa</th>
                      <th scope="col">Program Studi</th>
                      <th scope="col">Kelas</th>
                      <th scope="col">JK</th>
                      <th scope="col">Tanggal Daftar</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mahasiswaList as $i => $m): ?>
                      <tr>
                        <td><?= $i + 1; ?></td>
                        <td><strong><?= htmlspecialchars($m['nim']); ?></strong></td>
                        <td><?= htmlspecialchars($m['nama_mahasiswa']); ?></td>
                        <td><?= htmlspecialchars($m['program_studi']); ?></td>
                        <td><?= htmlspecialchars($m['kelas']); ?></td>
                        <td>
                          <span class="badge-gender <?= strtolower($m['jenis_kelamin']) === 'laki-laki' ? 'male' : 'female'; ?>">
                            <?= htmlspecialchars($m['jenis_kelamin']); ?>
                          </span>
                        </td>
                        <td><?= $m['tanggal_registrasi'] ? date('d M Y', strtotime($m['tanggal_registrasi'])) : '-'; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </section>
        
      </div> <!-- /.content -->
    </main>
  </div> <!-- /.app -->

  <!-- Chart Data for JS -->
  <script>
    window.__CHART_DATA__ = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.__CHART_CONFIG__ = {
      gradientColors: ['#b91c1c', '#dc2626', '#ea580c'],
      animationDuration: 1200,
      tooltipDelay: 150
    };
  </script>
  
  <!-- Main JS -->
  <script src="../js/js_akademik/dashboard.js?v=5"></script>
</body>
</html>