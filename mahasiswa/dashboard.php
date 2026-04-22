<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// Wajib login mahasiswa
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: ../login.php?pesan=" . urlencode("Silakan login sebagai mahasiswa."));
  exit;
}

$nim = $_SESSION['username'] ?? '';
$nama_session = $_SESSION['nama'] ?? 'Mahasiswa';

if ($nim === '') {
  header("Location: ../login.php?pesan=" . urlencode("Sesi tidak valid. Silakan login ulang."));
  exit;
}

/**
 * =========================
 * CEK STATUS AKUN (aktif/nonaktif) dari admin
 * =========================
 */
$status_user = 'aktif';
if ($nim !== '') {
  $stU = $conn->prepare("SELECT status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
  if ($stU) {
    $stU->bind_param("s", $nim);
    $stU->execute();
    $rsU = $stU->get_result();
    if ($rsU && ($rowU = $rsU->fetch_assoc())) {
      $status_user = $rowU['status'] ?? 'aktif';
    }
    $stU->close();
  }
}

$is_blocked = false;
$blocked_msg = "";
if ($status_user === 'nonaktif') {
  $is_blocked = true;
  $blocked_msg = "Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.";
}

/**
 * =========================
 * AMBIL DATA MAHASISWA
 * =========================
 */
$m = [
  'nim'             => $nim,
  'nama_mahasiswa'  => $nama_session,
  'program_studi'   => '-',
  'kelas'           => '-',
  'angkatan'        => '-',
  'status'          => 'AKTIF',
  'jenis_kelamin'   => '-',
  'email'           => '-',
  'hp'              => '-',
  'semester'        => '1',
  'sks_diambil'     => 0,
  'ipk'             => '0.00',
  'kehadiran'       => 0,
];

if ($nim !== '') {
  $stmt = $conn->prepare("
    SELECT nim, nama_mahasiswa, program_studi, kelas, tanggal_registrasi, jenis_kelamin, email, hp, sks_diakui
    FROM mahasiswa
    WHERE nim=?
    LIMIT 1
  ");
  if ($stmt) {
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $rs = $stmt->get_result();
    if ($rs && ($row = $rs->fetch_assoc())) {
      $m['nim']            = $row['nim'] ?? $m['nim'];
      $m['nama_mahasiswa'] = $row['nama_mahasiswa'] ?? $m['nama_mahasiswa'];
      $m['program_studi']  = $row['program_studi'] ?? $m['program_studi'];
      $m['kelas']          = $row['kelas'] ?? $m['kelas'];
      $m['jenis_kelamin']  = $row['jenis_kelamin'] ?? $m['jenis_kelamin'];
      $m['email']          = !empty($row['email']) ? $row['email'] : '-';
      $m['hp']             = !empty($row['hp']) ? $row['hp'] : '-';
      $m['sks_diambil']    = (int)($row['sks_diakui'] ?? 0);

      if (!empty($row['tanggal_registrasi'])) {
        $m['angkatan'] = date("Y", strtotime($row['tanggal_registrasi']));
      }
    }
    $stmt->close();
  }
}

/**
 * =========================
 * DATA SEMENTARA / DEFAULT
 * =========================
 */
$stat = [
  'kehadiran'      => 0,
  'pertemuan'      => 0,
  'hadir'          => 0,
  'izin'           => 0,
  'sakit'          => 0,
  'alpha'          => 0,
  'sks_aktif'      => $m['sks_diambil'],
  'ipk'            => '0.00',
  'jumlah_krs'     => 0,
  'jumlah_nilai'   => 0,
  'jumlah_tagihan' => 0,
];

$menu = $_GET['menu'] ?? 'dashboard';

renderMahasiswaLayoutStart([
  "title"       => "Mahasiswa - Dashboard",
  "page_title"  => ($menu === 'dashboard' ? "Dashboard" : "Menu: " . ucfirst($menu)),
  "page_sub"    => "Halo, " . $m['nama_mahasiswa'],
  "menu"        => $menu,
  "nama_tampil" => $m['nama_mahasiswa'],
  "username"    => $m['nim'],
  "assetsBase"  => "..",
  "basePath"    => "",
  "is_blocked"  => $is_blocked,
  "blocked_msg" => $blocked_msg,
]);

if ($menu === 'dashboard'): ?>

<section class="dashboard-page"
         id="dashboardPage"
         data-api="api_akun.php?action=status"
         data-blocked-msg="<?= e($blocked_msg ?: 'Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.') ?>">

  <section class="hero">
    <div class="hero-top">
      <div>
        <div class="hero-title">Ringkasan Akademik Mahasiswa</div>
        <div class="hero-sub">Akses cepat informasi akun, akademik, dan aktivitas mahasiswa.</div>
      </div>

      <div class="hero-pill">
        <span class="dot"></span>
        Status: <b><?= e($m['status']) ?></b>
      </div>
    </div>

    <div class="hero-cards">
      <div class="hcard">
        <div class="hlabel">NIM</div>
        <div class="hvalue"><?= e($m['nim']) ?></div>
        <div class="hmeta">Angkatan: <b><?= e($m['angkatan']) ?></b></div>
      </div>

      <div class="hcard">
        <div class="hlabel">Program Studi</div>
        <div class="hvalue"><?= e($m['program_studi']) ?></div>
        <div class="hmeta">Kelas: <b><?= e($m['kelas']) ?></b></div>
      </div>

      <div class="hcard">
        <div class="hlabel">Kontak</div>
        <div class="hvalue"><?= e($m['hp']) ?></div>
        <div class="hmeta">Email: <b><?= e($m['email']) ?></b></div>
      </div>
    </div>
  </section>

  <section class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Kehadiran</div>
      <div class="stat-value"><?= e($stat['kehadiran']) ?>%</div>
      <div class="stat-meta">Hadir <?= e($stat['hadir']) ?> dari <?= e($stat['pertemuan']) ?> pertemuan</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">SKS Diambil</div>
      <div class="stat-value"><?= e($stat['sks_aktif']) ?></div>
      <div class="stat-meta">Semester aktif</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">IPK</div>
      <div class="stat-value"><?= e($stat['ipk']) ?></div>
      <div class="stat-meta">Belum ada perhitungan</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Jumlah Nilai</div>
      <div class="stat-value"><?= e($stat['jumlah_nilai']) ?></div>
      <div class="stat-meta">Data nilai masuk</div>
    </div>
  </section>

  <section class="grid">
    <div class="card">
      <div class="card-title">Profil Mahasiswa</div>
      <div class="card-sub">Informasi singkat mahasiswa yang sedang login.</div>

      <div class="info">
        <div class="info-row">
          <span class="info-k">Nama</span>
          <span class="info-v"><?= e($m['nama_mahasiswa']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">NIM</span>
          <span class="info-v"><?= e($m['nim']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Program Studi</span>
          <span class="info-v"><?= e($m['program_studi']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Kelas</span>
          <span class="info-v"><?= e($m['kelas']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Jenis Kelamin</span>
          <span class="info-v"><?= e($m['jenis_kelamin']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Email</span>
          <span class="info-v"><?= e($m['email']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">No. HP</span>
          <span class="info-v"><?= e($m['hp']) ?></span>
        </div>
      </div>

      <div class="card-actions">
        <a class="btn" href="profile.php">Lihat Profil</a>
        <a class="btn ghost" href="settings.php">Pengaturan</a>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Kehadiran Mahasiswa</div>
      <div class="card-sub">Data kehadiran masih default 0 dan bisa dihubungkan ke tabel presensi nanti.</div>

      <div class="attendance-box">
        <div class="attendance-circle">
          <div class="attendance-circle-inner">
            <strong><?= e($stat['kehadiran']) ?>%</strong>
            <span>Kehadiran</span>
          </div>
        </div>

        <div class="attendance-list">
          <div class="attendance-item">
            <span>Hadir</span>
            <b><?= e($stat['hadir']) ?></b>
          </div>
          <div class="attendance-item">
            <span>Izin</span>
            <b><?= e($stat['izin']) ?></b>
          </div>
          <div class="attendance-item">
            <span>Sakit</span>
            <b><?= e($stat['sakit']) ?></b>
          </div>
          <div class="attendance-item">
            <span>Alpha</span>
            <b><?= e($stat['alpha']) ?></b>
          </div>
        </div>
      </div>

      <div class="card-actions">
        <a class="btn" href="dashboard.php?menu=presensi">Detail Presensi</a>
      </div>
    </div>
  </section>

  <section class="grid">
    <div class="card">
      <div class="card-title">Jadwal Terdekat</div>
      <div class="card-sub">Masih contoh tampilan sementara sebelum dihubungkan ke tabel jadwal.</div>

      <div class="list">
        <div class="li">
          <div class="li-left">
            <div class="li-title">Belum ada jadwal</div>
            <div class="li-sub">Data jadwal masih kosong</div>
          </div>
          <span class="tag">0</span>
        </div>

        <div class="li">
          <div class="li-left">
            <div class="li-title">Belum ada perkuliahan</div>
            <div class="li-sub">Silakan hubungkan ke data akademik</div>
          </div>
          <span class="tag ghost">0</span>
        </div>
      </div>

      <div class="card-actions">
        <a class="btn" href="dashboard.php?menu=jadwal">Lihat Jadwal</a>
        <a class="btn ghost" href="dashboard.php?menu=krs">Buka KRS</a>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Informasi Akademik</div>
      <div class="card-sub">Data akademik tambahan default masih kosong.</div>

      <div class="info">
        <div class="info-row">
          <span class="info-k">KRS Diambil</span>
          <span class="info-v"><?= e($stat['jumlah_krs']) ?> mata kuliah</span>
        </div>
        <div class="info-row">
          <span class="info-k">Nilai Tersedia</span>
          <span class="info-v"><?= e($stat['jumlah_nilai']) ?> data</span>
        </div>
        <div class="info-row">
          <span class="info-k">Tagihan Akademik</span>
          <span class="info-v"><?= e($stat['jumlah_tagihan']) ?> item</span>
        </div>
        <div class="info-row">
          <span class="info-k">IPK Sementara</span>
          <span class="info-v"><?= e($stat['ipk']) ?></span>
        </div>
      </div>

      <div class="card-actions">
        <a class="btn" href="nilai.php">Rekap Nilai</a>
        <a class="btn ghost" href="dashboard.php?menu=transkrip">Transkrip</a>
      </div>
    </div>
  </section>

  <section class="card">
    <div class="card-title">Notifikasi Akademik</div>
    <div class="card-sub">Panel informasi singkat untuk mahasiswa.</div>

    <div class="notice-wrap">
      <div class="notice-item">
        <div class="notice-title">Presensi</div>
        <div class="notice-text">Belum ada data presensi masuk.</div>
      </div>

      <div class="notice-item">
        <div class="notice-title">Nilai</div>
        <div class="notice-text">Belum ada nilai yang dipublikasikan.</div>
      </div>

      <div class="notice-item">
        <div class="notice-title">KRS</div>
        <div class="notice-text">Pengisian KRS belum tersedia.</div>
      </div>
    </div>
  </section>
</section>

<?php else: ?>
  <section class="card">
    <div class="card-title">Halaman belum tersedia</div>
    <div class="card-sub">Menu <b><?= e($menu) ?></b> masih placeholder.</div>
    <div class="card-actions">
      <a class="btn" href="dashboard.php">Kembali</a>
    </div>
  </section>
<?php endif;

renderMahasiswaLayoutEnd([
  "assetsBase" => ".."
]);