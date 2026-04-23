<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai dosen."));
  exit;
}

$idUser   = (int)($_SESSION['id_user'] ?? 0);
$username = $_SESSION['username'] ?? '';
$namaUser = $_SESSION['nama_lengkap'] ?? 'Dosen';

$dosen = [
  'id_dosen'       => 0,
  'kode_dosen'     => $username,
  'nama_dosen'     => $namaUser,
  'jenis_kelamin'  => '-',
  'email'          => '-',
  'program_studi'  => '-',
];

if ($idUser > 0) {
  $sql = "SELECT id_dosen, kode_dosen, nama_dosen, jenis_kelamin, email, program_studi
          FROM dosen
          WHERE id_user = ?
          LIMIT 1";
  $stmt = $conn->prepare($sql);

  if ($stmt) {
    $stmt->bind_param("i", $idUser);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && ($row = $res->fetch_assoc())) {
      $dosen = [
        'id_dosen'       => (int)($row['id_dosen'] ?? 0),
        'kode_dosen'     => $row['kode_dosen'] ?? $username,
        'nama_dosen'     => $row['nama_dosen'] ?? $namaUser,
        'jenis_kelamin'  => $row['jenis_kelamin'] ?? '-',
        'email'          => $row['email'] ?? '-',
        'program_studi'  => $row['program_studi'] ?? '-',
      ];
    }

    $stmt->close();
  }
}

$totalNilaiInput = 0;
if ($dosen['id_dosen'] > 0) {
  $st = $conn->prepare("SELECT COUNT(*) AS total FROM nilai_mahasiswa WHERE id_dosen = ?");
  if ($st) {
    $st->bind_param("i", $dosen['id_dosen']);
    $st->execute();
    $rs = $st->get_result();
    if ($rs && ($rw = $rs->fetch_assoc())) {
      $totalNilaiInput = (int)($rw['total'] ?? 0);
    }
    $st->close();
  }
}

renderDosenLayoutStart([
  'title'       => 'Dosen - Dashboard',
  'page_title'  => 'Dashboard Dosen',
  'page_sub'    => 'Selamat datang di panel dosen',
  'nama_tampil' => $dosen['nama_dosen'],
  'username'    => $dosen['kode_dosen'],
  'assetsBase'  => '..',
]);
?>

<div class="welcome-card">
  <div class="welcome-badge">AKSES DOSEN</div>
  <h1 class="welcome-title">Selamat Datang, <?= e($dosen['nama_dosen']) ?></h1>
  <p class="welcome-text">
    Anda berhasil login ke sistem dosen. Halaman ini sudah disiapkan
    dengan animasi loading saat masuk ke dashboard.
  </p>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-label">Kode Dosen</div>
    <div class="stat-value"><?= e($dosen['kode_dosen']) ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Program Studi</div>
    <div class="stat-value stat-long"><?= e($dosen['program_studi']) ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Email</div>
    <div class="stat-value stat-long"><?= e($dosen['email']) ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Total Input Nilai</div>
    <div class="stat-value"><?= e($totalNilaiInput) ?></div>
  </div>
</div>

<div class="panel-grid">
  <div class="panel-card">
    <div class="panel-title">Profil Dosen</div>
    <div class="panel-list">
      <div class="panel-row">
        <span>Nama</span>
        <strong><?= e($dosen['nama_dosen']) ?></strong>
      </div>
      <div class="panel-row">
        <span>Jenis Kelamin</span>
        <strong><?= e($dosen['jenis_kelamin']) ?></strong>
      </div>
      <div class="panel-row">
        <span>Email</span>
        <strong><?= e($dosen['email']) ?></strong>
      </div>
      <div class="panel-row">
        <span>Program Studi</span>
        <strong><?= e($dosen['program_studi']) ?></strong>
      </div>
    </div>
  </div>

  <div class="panel-card">
    <div class="panel-title">Informasi</div>
    <div class="info-box">
      Menu dosen sudah aktif. Nanti kamu bisa lanjut menambahkan fitur seperti:
      data kelas, input nilai, daftar mahasiswa, dan rekap penilaian dosen.
    </div>

    <div class="action-row">
      <a href="logout.php" class="btn-action btn-danger">Logout</a>
    </div>
  </div>
</div>

<?php
renderDosenLayoutEnd([
  'assetsBase' => '..'
]);