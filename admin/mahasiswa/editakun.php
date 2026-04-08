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

$menu = 'mhs_data';

$tipe  = $_GET['tipe']  ?? '';
$pesan = $_GET['pesan'] ?? '';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("ID tidak valid."));
  exit;
}

// ambil data akun (mahasiswa sudah dicetak) + user mahasiswa
$sql = "SELECT
          m.id_mahasiswa, m.nim, m.nama_mahasiswa, m.program_studi,
          m.akun_dicetak, m.akun_dicetak_pada, m.id_user,
          u.id_user AS u_id, u.username, u.nama_lengkap, u.status
        FROM mahasiswa m
        LEFT JOIN users u
          ON (u.id_user = m.id_user)
          OR (u.username = m.nim AND u.role='mahasiswa')
        WHERE m.id_mahasiswa=?
          AND IFNULL(m.akun_dicetak,0)=1
        LIMIT 1";
$st = $conn->prepare($sql);
if (!$st) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Prepare gagal: " . $conn->error));
  exit;
}
$st->bind_param("i", $id);
$st->execute();
$rs = $st->get_result();
$row = ($rs && $rs->num_rows === 1) ? $rs->fetch_assoc() : null;
$st->close();

if (!$row) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Data akun tidak ditemukan."));
  exit;
}

$id_user = (int)($row['id_user'] ?: $row['u_id']);
if ($id_user <= 0) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Akun user tidak ditemukan untuk mahasiswa ini."));
  exit;
}

$nim = $row['nim'] ?? '';
$nama_mhs = $row['nama_mahasiswa'] ?? '';
$prodi = $row['program_studi'] ?? '';
$dicetak_pada = $row['akun_dicetak_pada'] ?? '-';

$nama_lengkap_user = $row['nama_lengkap'] ?? $nama_mhs;
$status_user = ($row['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

renderAdminLayoutStart([
  "title"       => "Admin - Edit Akun Mahasiswa",
  "page_title"  => "Edit Akun Mahasiswa",
  "page_sub"    => "Mahasiswa / Data Akun / Edit",
  "menu"        => $menu,
  "nama_tampil" => $nama_tampil,
  "username"    => $username,
  "assetsBase"  => "../..",
  "basePath"    => "..",
  "extra_css"   => ["css/css_admin/mahasiswa/editakun.css"],
]);
?>

<div class="panel">
  <div class="panel-head">
    <div class="panel-left">
      <div class="panel-title">Edit Akun</div>
      <div class="panel-sub">Perbarui status / nama / password akun mahasiswa.</div>
    </div>

    <div class="panel-actions">
      <a class="btn-ghost" href="dataakun.php" title="Kembali">Kembali</a>
    </div>
  </div>

  <div class="panel-body">
    <div class="info-grid">
      <div class="info-item">
        <div class="k">Nama Mahasiswa</div>
        <div class="v"><?= e($nama_mhs) ?></div>
      </div>
      <div class="info-item">
        <div class="k">NIM (Username)</div>
        <div class="v"><?= e($nim) ?></div>
      </div>
      <div class="info-item">
        <div class="k">Program Studi</div>
        <div class="v"><?= e($prodi) ?></div>
      </div>
      <div class="info-item">
        <div class="k">Dicetak Pada</div>
        <div class="v"><?= e($dicetak_pada) ?></div>
      </div>
    </div>

    <form class="form" action="proses_editakun.php" method="post" autocomplete="off">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <input type="hidden" name="id_user" value="<?= (int)$id_user ?>">

      <div class="form-grid">
        <div class="field">
          <label>Username (NIM)</label>
          <input type="text" value="<?= e($nim) ?>" readonly>
        </div>

        <div class="field">
          <label>Status Akun</label>
          <select name="status" required>
            <option value="aktif" <?= $status_user==='aktif'?'selected':''; ?>>aktif</option>
            <option value="nonaktif" <?= $status_user==='nonaktif'?'selected':''; ?>>nonaktif</option>
          </select>
        </div>

        <div class="field full">
          <label>Nama Lengkap (di tabel users)</label>
          <input type="text" name="nama_lengkap" value="<?= e($nama_lengkap_user) ?>" required>
          <div class="hint">Nama ini yang akan tampil saat login (profil pengguna).</div>
        </div>

        <div class="field full">
          <label>Password Baru (opsional)</label>
          <input type="password" name="password_baru" id="password_baru" placeholder="Kosongkan jika tidak ingin mengubah">
          <div class="row-inline">
            <label class="chk">
              <input type="checkbox" id="showPass">
              <span>Lihat password</span>
            </label>

            <label class="chk">
              <input type="checkbox" name="reset_ke_nim" value="1" id="resetNim">
              <span>Reset password = NIM</span>
            </label>
          </div>
          <div class="hint">Jika centang “Reset password = NIM”, kolom password baru akan diabaikan.</div>
        </div>
      </div>

      <div class="actions">
        <button class="btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast" aria-hidden="true">
  <div class="toast-card" id="toastCard">
    <div class="toast-title" id="toastTitle">Info</div>
    <div class="toast-msg" id="toastMsg">...</div>
  </div>
</div>

<script>
  window.__FLASH_TIPE__  = <?= json_encode($tipe) ?>;
  window.__FLASH_PESAN__ = <?= json_encode($pesan) ?>;
</script>

<?php
renderAdminLayoutEnd([
  "assetsBase" => "../..",
  "extra_js"   => ["js/js_admin/mahasiswa/editakun.js"],
]);