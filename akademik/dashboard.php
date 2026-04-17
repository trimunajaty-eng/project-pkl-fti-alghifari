<?php
require_once __DIR__ . "/../config.php";

// proteksi login akademik
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

// hitung data sederhana untuk dashboard
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

// ambil mahasiswa terbaru
$mahasiswaTerbaru = [];
$sqlMahasiswa = "SELECT nim, nama_mahasiswa, program_studi, kelas, dibuat_pada
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/css_akademik/dashboard.css">
</head>
<body>

  <div class="app">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-top">
        <div class="logo-box">AK</div>
        <div>
          <h2 class="sidebar-title">Akademik</h2>
          <p class="sidebar-subtitle">Universitas Al-Ghifari</p>
        </div>
      </div>

      <nav class="menu">
        <a href="dashboard.php" class="menu-item active">Dashboard</a>
        <a href="#" class="menu-item">Input Nilai</a>
        <a href="#" class="menu-item">Data Nilai</a>
        <a href="#" class="menu-item">Jadwal Akademik</a>
        <a href="#" class="menu-item">Laporan</a>
      </nav>

      <div class="sidebar-bottom">
        <a href="../admin/logout.php" class="logout-btn">Logout</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="main">
      <header class="topbar">
        <div>
          <button class="menu-toggle" id="menuToggle" type="button">☰</button>
          <h1 class="page-title">Dashboard Akademik</h1>
          <p class="page-subtitle">Selamat datang, <?= htmlspecialchars($namaLogin); ?></p>
        </div>

        <div class="user-badge">
          <span class="user-role">Role</span>
          <strong>Akademik</strong>
        </div>
      </header>

      <!-- cards -->
      <section class="stats">
        <div class="stat-card">
          <div class="stat-label">Total Mahasiswa</div>
          <div class="stat-value"><?= number_format($totalMahasiswa); ?></div>
          <div class="stat-note">Data seluruh mahasiswa</div>
        </div>

        <div class="stat-card">
          <div class="stat-label">Admin Aktif</div>
          <div class="stat-value"><?= number_format($totalAdmin); ?></div>
          <div class="stat-note">User admin aktif</div>
        </div>

        <div class="stat-card">
          <div class="stat-label">Akademik Aktif</div>
          <div class="stat-value"><?= number_format($totalAkademik); ?></div>
          <div class="stat-note">User akademik aktif</div>
        </div>
      </section>

      <!-- content -->
      <section class="content-grid">
        <div class="panel">
          <div class="panel-head">
            <h2>Informasi Dashboard</h2>
          </div>
          <div class="panel-body">
            <p>
              Halaman ini digunakan untuk memantau data akademik secara umum.
              Nantinya bagian akademik dapat mengelola nilai mahasiswa, melihat rekap data,
              serta memantau aktivitas akademik lainnya.
            </p>

            <ul class="info-list">
              <li>Melihat ringkasan jumlah mahasiswa</li>
              <li>Mengelola data nilai akademik</li>
              <li>Melihat jadwal dan laporan akademik</li>
              <li>Monitoring data pengguna akademik</li>
            </ul>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head">
            <h2>Mahasiswa Terbaru</h2>
          </div>
          <div class="panel-body">
            <?php if (count($mahasiswaTerbaru) > 0): ?>
              <div class="table-wrap">
                <table class="table">
                  <thead>
                    <tr>
                      <th>NIM</th>
                      <th>Nama</th>
                      <th>Program Studi</th>
                      <th>Kelas</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($mahasiswaTerbaru as $mhs): ?>
                      <tr>
                        <td><?= htmlspecialchars($mhs['nim']); ?></td>
                        <td><?= htmlspecialchars($mhs['nama_mahasiswa']); ?></td>
                        <td><?= htmlspecialchars($mhs['program_studi']); ?></td>
                        <td><?= htmlspecialchars($mhs['kelas']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="empty-text">Belum ada data mahasiswa.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script src="../js/js_akademik/dashboard.js"></script>
</body>
</html>