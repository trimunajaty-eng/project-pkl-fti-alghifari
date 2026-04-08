<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../layout.php";

// Wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai admin dulu."));
  exit;
}

$username = $_SESSION['username'] ?? 'unfari';

// ambil nama_lengkap dari DB agar selalu sinkron
$nama_tampil = 'Universitas Al-ghifari';
$stmtMe = $conn->prepare("SELECT nama_lengkap FROM users WHERE username=? LIMIT 1");
$stmtMe->bind_param("s", $username);
$stmtMe->execute();
$rsMe = $stmtMe->get_result();
if ($rsMe && ($rowMe = $rsMe->fetch_assoc())) {
  $nama_tampil = $rowMe['nama_lengkap'] ?: $nama_tampil;
}
$stmtMe->close();

$menu = 'mhs_cetak';
$tipe  = $_GET['tipe']  ?? '';
$pesan = $_GET['pesan'] ?? '';

// PRODI MASTER (wajib tetap tampil walau semua sudah dicetak)
$prodiMaster = ["Teknik Informatika S1","Sistem Informasi S1"];
$prodiList = $prodiMaster;

// =======================
// COUNTS "AKUN SUDAH DIBUAT" (akun_dicetak=1)
// =======================
$countsMade = [
  "total" => 0,
  "by_prodi" => []
];

// init 0 untuk semua prodi master
foreach ($prodiMaster as $p) $countsMade["by_prodi"][$p] = 0;

// total dibuat
$qTotalMade = $conn->query("SELECT COUNT(*) AS total FROM mahasiswa WHERE IFNULL(akun_dicetak,0)=1");
if ($qTotalMade) $countsMade["total"] = (int)($qTotalMade->fetch_assoc()['total'] ?? 0);

// per prodi dibuat
$qByMade = $conn->query("SELECT program_studi, COUNT(*) AS total
                         FROM mahasiswa
                         WHERE IFNULL(akun_dicetak,0)=1
                         GROUP BY program_studi
                         ORDER BY program_studi ASC");
if ($qByMade) {
  while ($r = $qByMade->fetch_assoc()) {
    $p = $r['program_studi'];
    if ($p !== null && $p !== '') {
      if (!isset($countsMade["by_prodi"][$p])) $countsMade["by_prodi"][$p] = 0;
      $countsMade["by_prodi"][$p] = (int)$r['total'];
    }
  }
}

renderAdminLayoutStart([
  "title"       => "Admin - Cetak Akun Mahasiswa",
  "page_title"  => "Cetak Akun Mahasiswa",
  "page_sub"    => "Mahasiswa / Cetak Akun",
  "menu"        => $menu,
  "nama_tampil" => $nama_tampil,
  "username"    => $username,
  "assetsBase"  => "../..",
  "basePath"    => "..",
  "extra_css"   => ["css/css_admin/mahasiswa/cetakakun.css"],
]);
?>

<div class="panel">
  <div class="ca-head">
    <div class="ca-title">CETAK AKUN MAHASISWA</div>
  </div>

  <div class="ca-body">

    <!-- SINGLE PRINT -->
    <div class="ca-card">
      <div class="ca-row">
        <div class="ca-label">NIM/NAMA MAHASISWA</div>

        <div class="ca-search">
          <input type="text" id="caQuery" class="ca-input" placeholder="Ketik NIM atau Nama Mahasiswa..." autocomplete="off">
          <div class="ca-suggest" id="caSuggest" aria-hidden="true"></div>
        </div>
      </div>

      <div class="ca-picked" id="caPicked" aria-hidden="true">
        <div class="ca-picked-line">
          <div class="ca-picked-nim" id="pickedNim">-</div>
          <div class="ca-picked-sep">-</div>
          <div class="ca-picked-nama" id="pickedNama">-</div>
        </div>
        <div class="ca-picked-sub" id="pickedProdi">-</div>
      </div>

      <div class="ca-actions">
        <!-- diganti ke DATA AKUN -->
        <a class="ca-link" href="dataakun.php">Data Akun</a>
        <button class="ca-btn" type="button" id="btnCetakSingle" disabled>Cetak</button>
      </div>
    </div>

    <!-- MASS PRINT -->
    <div class="ca-card">
      <div class="ca-mass-head">
        <div class="ca-mass-title">Cetak Akun Massal</div>
        <div class="ca-mass-sub">Pilih semua program studi atau salah satu program studi.</div>
      </div>

      <div class="ca-mass-grid">
        <div class="ca-mass-field">
          <label>Program Studi</label>
          <select id="massProdi" class="ca-select">
            <option value="ALL">Semua Program Studi</option>
            <?php foreach ($prodiList as $p): ?>
              <option value="<?= e($p) ?>"><?= e($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="ca-mass-field">
          <label>Jumlah Data (Belum Dicetak)</label>
          <div class="ca-badge" id="massCount">0</div>
        </div>

        <div class="ca-mass-field ca-mass-actions">
          <label>&nbsp;</label>
          <button class="ca-btn ghost" type="button" id="btnRefreshCount">Refresh</button>
          <button class="ca-btn" type="button" id="btnCetakMassal">Cetak Akun Massal</button>
        </div>
      </div>

      <!-- INI SEKARANG MENAMPILKAN TOTAL AKUN SUDAH DIBUAT -->
      <div class="ca-counts">
        <div class="ca-count-item">
          <div class="k">Semua (Akun Dibuat)</div>
          <div class="v" id="countAll"><?= (int)$countsMade["total"] ?></div>
        </div>

        <?php foreach($countsMade["by_prodi"] as $p => $n): ?>
          <div class="ca-count-item" data-prodi="<?= e($p) ?>">
            <div class="k"><?= e($p) ?> (Akun Dibuat)</div>
            <div class="v"><?= (int)$n ?></div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

  </div>
</div>

<!-- LOADING -->
<div class="loading" id="loading" aria-hidden="true">
  <div class="loading-card">
    <div class="spinner" aria-hidden="true"></div>
    <div class="loading-text" id="loadingText">Sedang memproses...</div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast" aria-hidden="true">
  <div class="toast-card" id="toastCard">
    <div class="toast-title" id="toastTitle">Info</div>
    <div class="toast-msg" id="toastMsg">...</div>
  </div>
</div>

<!-- MODAL CONFIRM -->
<div class="ca-modal" id="confirmModal" aria-hidden="true">
  <div class="ca-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="ca-modal-title" id="confirmTitle">Konfirmasi</div>
    <div class="ca-modal-msg" id="confirmMsg">...</div>
    <div class="ca-modal-actions">
      <button type="button" class="ca-btn ghost" id="btnCancelConfirm">Batal</button>
      <button type="button" class="ca-btn" id="btnOkConfirm">Ya, Cetak</button>
    </div>
  </div>
</div>

<script>
  window.__FLASH_TIPE__  = <?= json_encode($tipe) ?>;
  window.__FLASH_PESAN__ = <?= json_encode($pesan) ?>;

  // counts "akun dibuat" untuk bawah
  window.__COUNTS_MADE_INIT__ = <?= json_encode($countsMade) ?>;

  // prodi master untuk client
  window.__PRODI_MASTER__ = <?= json_encode($prodiMaster) ?>;
</script>

<?php
renderAdminLayoutEnd([
  "assetsBase" => "../..",
  "extra_js"   => ["js/js_admin/mahasiswa/cetakakun.js"],
]);