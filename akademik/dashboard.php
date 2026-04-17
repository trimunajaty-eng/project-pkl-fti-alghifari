<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

$totalMahasiswa = 0;
$totalAdmin = 0;
$totalAkademik = 0;

$q1 = $conn->query("SELECT COUNT(*) AS total FROM mahasiswa");
if ($q1 && $row = $q1->fetch_assoc()) {
  $totalMahasiswa = (int)$row['total'];
}

$q2 = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND status = 'aktif'");
if ($q2 && $row = $q2->fetch_assoc()) {
  $totalAdmin = (int)$row['total'];
}

$q3 = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'akademik' AND status = 'aktif'");
if ($q3 && $row = $q3->fetch_assoc()) {
  $totalAkademik = (int)$row['total'];
}

$mahasiswaTerbaru = [];
$sqlMahasiswa = "SELECT nim, nama_mahasiswa, program_studi, kelas
                 FROM mahasiswa
                 ORDER BY id_mahasiswa DESC
                 LIMIT 5";
$resMahasiswa = $conn->query($sqlMahasiswa);
if ($resMahasiswa) {
  while ($row = $resMahasiswa->fetch_assoc()) {
    $mahasiswaTerbaru[] = $row;
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/css_akademik/dashboard.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="brand">
          <div class="brand-icon">AK</div>
          <div>
            <div class="brand-title">Akademik</div>
            <div class="brand-subtitle">Unfari</div>
          </div>
        </div>

        <nav class="menu">
          <a href="dashboard.php" class="menu-item active">Dashboard</a>
          <a href="nilai/inputnilai.php" class="menu-item">Input Nilai</a>
          <a href="#" class="menu-item">Data Nilai</a>
          <a href="#" class="menu-item">Laporan</a>
          <a href="logout.php" class="menu-item menu-item-danger">Logout</a>
        </nav>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button type="button" id="menuToggle" class="menu-toggle">☰</button>
          <div>
            <h1 class="page-title">Dashboard Akademik</h1>
            <p class="page-subtitle">Halo, <?= htmlspecialchars($namaLogin); ?></p>
          </div>
        </div>

        <div class="user-box">
          <div class="user-box-label">Role</div>
          <div class="user-box-value">Akademik</div>
        </div>
      </header>

      <section class="stats">
        <div class="card stat-card">
          <div class="stat-label">Mahasiswa</div>
          <div class="stat-value"><?= number_format($totalMahasiswa); ?></div>
        </div>

        <div class="card stat-card">
          <div class="stat-label">Admin Aktif</div>
          <div class="stat-value"><?= number_format($totalAdmin); ?></div>
        </div>

        <div class="card stat-card">
          <div class="stat-label">Akademik Aktif</div>
          <div class="stat-value"><?= number_format($totalAkademik); ?></div>
        </div>
      </section>

      <section class="grid">
        <div class="card">
          <div class="card-head">
            <h2>Menu Cepat</h2>
          </div>
          <div class="card-body">
            <div class="quick-links">
              <a href="inputnilai.php" class="quick-link">Input Nilai Mahasiswa</a>
              <a href="#" class="quick-link">Lihat Data Nilai</a>
              <a href="#" class="quick-link">Cetak Rekap</a>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-head">
            <h2>Mahasiswa Terbaru</h2>
          </div>
          <div class="card-body">
            <?php if (!empty($mahasiswaTerbaru)): ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>NIM</th>
                      <th>Nama</th>
                      <th>Prodi</th>
                      <th>Kelas</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mahasiswaTerbaru as $m): ?>
                      <tr>
                        <td><?= htmlspecialchars($m['nim']); ?></td>
                        <td><?= htmlspecialchars($m['nama_mahasiswa']); ?></td>
                        <td><?= htmlspecialchars($m['program_studi']); ?></td>
                        <td><?= htmlspecialchars($m['kelas']); ?></td>
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

  <script src="../js/js_akademik/dashboard.js"></script>
</body>
</html>