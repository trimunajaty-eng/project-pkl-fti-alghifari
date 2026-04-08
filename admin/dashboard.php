<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// Wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai admin dulu."));
  exit;
}

$username = $_SESSION['username'] ?? 'unfari';

// ambil nama_lengkap dari DB
$nama_tampil = 'Universitas Al-ghifari';
$stmtMe = $conn->prepare("SELECT nama_lengkap FROM users WHERE username=? LIMIT 1");
$stmtMe->bind_param("s", $username);
$stmtMe->execute();
$rsMe = $stmtMe->get_result();
if ($rsMe && ($rowMe = $rsMe->fetch_assoc())) {
  $nama_tampil = $rowMe['nama_lengkap'] ?: $nama_tampil;
}
$stmtMe->close();

// ringkasan
$total_mahasiswa = 0;
$total_dosen = 0;

$q1 = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='mahasiswa'");
if ($q1) { $total_mahasiswa = (int)($q1->fetch_assoc()['total'] ?? 0); }

$q2 = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='dosen'");
if ($q2) { $total_dosen = (int)($q2->fetch_assoc()['total'] ?? 0); }

$menu = $_GET['menu'] ?? 'dashboard';

renderAdminLayoutStart([
  "title"      => "Admin - Dashboard",
  "page_title" => ($menu === 'dashboard' ? "Dashboard" : "Menu: ".$menu),
  "page_sub"   => "Halo, ".$nama_tampil."!",
  "menu"       => $menu,
  "nama_tampil"=> $nama_tampil,
  "username"   => $username,

  // dashboard.php berada di /admin
  "assetsBase" => "..",   // akses /css, /js, /img
  "basePath"   => "",     // link ke dashboard.php tetap dari /admin
]);

// ======== ISI HALAMAN =========
if ($menu === 'dashboard'): ?>
  <section class="hero">
    <div class="hero-title">Pusat Kendali Admin</div>
    <div class="hero-sub">Ringkasan cepat untuk pengelolaan akun dan data akademik.</div>

    <div class="hero-cards">
      <div class="hcard">
        <div class="hlabel">Mahasiswa</div>
        <div class="hnum"><?= (int)$total_mahasiswa ?></div>
        <a class="hlink" href="mahasiswa/data.php">Lihat Data</a>
      </div>
      <div class="hcard">
        <div class="hlabel">Dosen</div>
        <div class="hnum"><?= (int)$total_dosen ?></div>
        <a class="hlink" href="dashboard.php?menu=dsn_data">Lihat Data</a>
      </div>
      <div class="hcard">
        <div class="hlabel">Quick Action</div>
        <div class="hnum">+</div>
        <a class="hlink" href="mahasiswa/inputmahasiswa.php">Input Mahasiswa</a>
      </div>
    </div>
  </section>

  <section class="grid">
    <div class="card">
      <div class="card-title">Shortcut</div>
      <div class="card-sub">Menu placeholder (bisa diisi nanti)</div>
      <div class="card-actions">
        <a class="btn" href="dashboard.php?menu=master_1">Data Master</a>
        <a class="btn ghost" href="dashboard.php?menu=bkp_1">Backup</a>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Info</div>
      <div class="card-sub">Dashboard simpel: fokus rapi, cepat, dan responsif.</div>
    </div>
  </section>
<?php else: ?>
  <section class="card">
    <div class="card-title">Placeholder</div>
    <div class="card-sub">Halaman <b><?= e($menu) ?></b> belum dibuat.</div>
  </section>
<?php endif;

// ======== TUTUP LAYOUT =========
renderAdminLayoutEnd([
  "assetsBase" => ".."
]);