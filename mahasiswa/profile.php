<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// wajib login mahasiswa
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: ../login.php?pesan=" . urlencode("Silakan login sebagai mahasiswa."));
  exit;
}

$nim  = $_SESSION['username'] ?? '';
$nama = $_SESSION['nama'] ?? 'Mahasiswa';

if ($nim === '') {
  header("Location: ../login.php?pesan=" . urlencode("Sesi tidak valid. Silakan login ulang."));
  exit;
}

/**
 * cek status akun
 */
$status_user = 'aktif';
if ($nim !== '') {
  $stU = $conn->prepare("SELECT status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
  if ($stU) {
    $stU->bind_param("s", $nim);
    $stU->execute();
    $rsU = $stU->get_result();
    if ($rsU && ($rowU = $rsU->fetch_assoc())) {
      $status_user = ($rowU['status'] ?? 'aktif');
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
 * ambil data mahasiswa lengkap
 */
$m = [
  'nim' => $nim,
  'nama_mahasiswa' => $nama,
  'program_studi' => '-',
  'kelas' => '-',
  'angkatan' => '-',
  'status_mahasiswa' => 'AKTIF',

  'tempat_lahir' => '-',
  'tanggal_lahir' => '-',
  'jenis_kelamin' => '-',
  'agama' => '-',
  'golongan_darah' => '-',
  'ukuran_seragam' => '-',
  'nik' => '-',
  'nisn' => '-',
  'npwp' => '-',
  'kewarganegaraan' => '-',

  'provinsi' => '-',
  'kab_kota' => '-',
  'kecamatan' => '-',
  'kelurahan' => '-',
  'jalan' => '-',
  'dusun' => '-',
  'rt' => '-',
  'rw' => '-',
  'kode_pos' => '-',
  'jenis_tinggal' => '-',
  'alat_transportasi' => '-',
  'telepon' => '-',
  'hp' => '-',
  'email' => '-',

  'nama_ayah' => '-',
  'tanggal_lahir_ayah' => '-',
  'pendidikan_ayah' => '-',
  'pekerjaan_ayah' => '-',
  'penghasilan_ayah' => '-',

  'nama_ibu' => '-',
  'tanggal_lahir_ibu' => '-',
  'pendidikan_ibu' => '-',
  'pekerjaan_ibu' => '-',
  'penghasilan_ibu' => '-',

  'nama_wali' => '-',
  'tanggal_lahir_wali' => '-',
  'pendidikan_wali' => '-',
  'pekerjaan_wali' => '-',
  'penghasilan_wali' => '-',
];

if ($nim !== '') {
  $sql = "SELECT *
          FROM mahasiswa
          WHERE nim=?
          LIMIT 1";
  $st = $conn->prepare($sql);
  if ($st) {
    $st->bind_param("s", $nim);
    $st->execute();
    $rs = $st->get_result();
    if ($rs && ($row = $rs->fetch_assoc())) {
      foreach ($m as $k => $v) {
        if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
          $m[$k] = $row[$k];
        }
      }

      if (!empty($row['tanggal_registrasi'])) {
        $m['angkatan'] = date("Y", strtotime($row['tanggal_registrasi']));
      }

      if (!empty($row['tanggal_lahir']) && $row['tanggal_lahir'] !== '0000-00-00') {
        $m['tanggal_lahir'] = date("d/m/Y", strtotime($row['tanggal_lahir']));
      }
      if (!empty($row['tanggal_lahir_ayah']) && $row['tanggal_lahir_ayah'] !== '0000-00-00') {
        $m['tanggal_lahir_ayah'] = date("d/m/Y", strtotime($row['tanggal_lahir_ayah']));
      }
      if (!empty($row['tanggal_lahir_ibu']) && $row['tanggal_lahir_ibu'] !== '0000-00-00') {
        $m['tanggal_lahir_ibu'] = date("d/m/Y", strtotime($row['tanggal_lahir_ibu']));
      }
      if (!empty($row['tanggal_lahir_wali']) && $row['tanggal_lahir_wali'] !== '0000-00-00') {
        $m['tanggal_lahir_wali'] = date("d/m/Y", strtotime($row['tanggal_lahir_wali']));
      }
    }
    $st->close();
  }
}

renderMahasiswaLayoutStart([
  "title"       => "Mahasiswa - Profil",
  "page_title"  => "Data Profil",
  "page_sub"    => "Lihat seluruh data pribadi mahasiswa",
  "menu"        => "profil",
  "nama_tampil" => $m['nama_mahasiswa'],
  "username"    => $m['nim'],
  "assetsBase"  => "..",
  "basePath"    => "",
  "is_blocked"  => $is_blocked,
  "blocked_msg" => $blocked_msg,
]);
?>

<link rel="stylesheet" href="../css/css_mahasiswa/profile.css">

<section class="pf-card"
         id="profilePage"
         data-api="api_akun.php?action=status"
         data-blocked-msg="<?= e($blocked_msg ?: 'Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.') ?>">

  <div class="pf-head">
    <div>
      <div class="pf-title">Profil Mahasiswa</div>
      <div class="pf-sub">Data ini ditampilkan dari formulir pendaftaran mahasiswa.</div>
    </div>
  </div>

  <div class="pf-top">
    <div class="pf-grid pf-grid-2">
      <div class="pf-row"><div class="k">NIM</div><div class="sep">:</div><div class="v"><?= e($m['nim']) ?></div></div>
      <div class="pf-row"><div class="k">Nama Mahasiswa</div><div class="sep">:</div><div class="v"><?= e($m['nama_mahasiswa']) ?></div></div>
      <div class="pf-row"><div class="k">Program Studi</div><div class="sep">:</div><div class="v"><?= e($m['program_studi']) ?></div></div>
      <div class="pf-row"><div class="k">Tempat, Tanggal Lahir</div><div class="sep">:</div><div class="v"><?= e($m['tempat_lahir']) ?><?= $m['tanggal_lahir'] !== '-' ? ', ' . e($m['tanggal_lahir']) : '' ?></div></div>
      <div class="pf-row"><div class="k">Angkatan</div><div class="sep">:</div><div class="v"><?= e($m['angkatan']) ?></div></div>
      <div class="pf-row"><div class="k">Jenis Kelamin</div><div class="sep">:</div><div class="v"><?= e($m['jenis_kelamin']) ?></div></div>
      <div class="pf-row"><div class="k">Status Mahasiswa</div><div class="sep">:</div><div class="v"><?= e($m['status_mahasiswa']) ?></div></div>
      <div class="pf-row"><div class="k">Agama</div><div class="sep">:</div><div class="v"><?= e($m['agama']) ?></div></div>
    </div>
  </div>

  <div class="pf-tabs" id="pfTabs">
    <button type="button" class="pf-tab active" data-tab="profil">Profil</button>
    <button type="button" class="pf-tab" data-tab="alamat">Alamat</button>
    <button type="button" class="pf-tab" data-tab="ortu">Orang Tua</button>
    <button type="button" class="pf-tab" data-tab="wali">Wali</button>
  </div>

  <div class="pf-panels">
    <section class="pf-panel active" data-panel="profil">
      <div class="pf-grid">
        <div class="pf-row"><div class="k">NIM</div><div class="sep">:</div><div class="v"><?= e($m['nim']) ?></div></div>
        <div class="pf-row"><div class="k">Nama Mahasiswa</div><div class="sep">:</div><div class="v"><?= e($m['nama_mahasiswa']) ?></div></div>
        <div class="pf-row"><div class="k">Program Studi</div><div class="sep">:</div><div class="v"><?= e($m['program_studi']) ?></div></div>
        <div class="pf-row"><div class="k">Kelas</div><div class="sep">:</div><div class="v"><?= e($m['kelas']) ?></div></div>
        <div class="pf-row"><div class="k">Angkatan</div><div class="sep">:</div><div class="v"><?= e($m['angkatan']) ?></div></div>
        <div class="pf-row"><div class="k">Tempat Lahir</div><div class="sep">:</div><div class="v"><?= e($m['tempat_lahir']) ?></div></div>
        <div class="pf-row"><div class="k">Tanggal Lahir</div><div class="sep">:</div><div class="v"><?= e($m['tanggal_lahir']) ?></div></div>
        <div class="pf-row"><div class="k">Jenis Kelamin</div><div class="sep">:</div><div class="v"><?= e($m['jenis_kelamin']) ?></div></div>
        <div class="pf-row"><div class="k">Agama</div><div class="sep">:</div><div class="v"><?= e($m['agama']) ?></div></div>
        <div class="pf-row"><div class="k">Golongan Darah</div><div class="sep">:</div><div class="v"><?= e($m['golongan_darah']) ?></div></div>
        <div class="pf-row"><div class="k">Ukuran Seragam</div><div class="sep">:</div><div class="v"><?= e($m['ukuran_seragam']) ?></div></div>
        <div class="pf-row"><div class="k">NIK</div><div class="sep">:</div><div class="v"><?= e($m['nik']) ?></div></div>
        <div class="pf-row"><div class="k">NISN</div><div class="sep">:</div><div class="v"><?= e($m['nisn']) ?></div></div>
        <div class="pf-row"><div class="k">NPWP</div><div class="sep">:</div><div class="v"><?= e($m['npwp']) ?></div></div>
        <div class="pf-row"><div class="k">Kewarganegaraan</div><div class="sep">:</div><div class="v"><?= e($m['kewarganegaraan']) ?></div></div>
      </div>
    </section>

    <section class="pf-panel" data-panel="alamat">
      <div class="pf-grid">
        <div class="pf-row"><div class="k">Provinsi</div><div class="sep">:</div><div class="v"><?= e($m['provinsi']) ?></div></div>
        <div class="pf-row"><div class="k">Kab/Kota</div><div class="sep">:</div><div class="v"><?= e($m['kab_kota']) ?></div></div>
        <div class="pf-row"><div class="k">Kecamatan</div><div class="sep">:</div><div class="v"><?= e($m['kecamatan']) ?></div></div>
        <div class="pf-row"><div class="k">Kelurahan</div><div class="sep">:</div><div class="v"><?= e($m['kelurahan']) ?></div></div>
        <div class="pf-row"><div class="k">Jalan</div><div class="sep">:</div><div class="v"><?= e($m['jalan']) ?></div></div>
        <div class="pf-row"><div class="k">Dusun</div><div class="sep">:</div><div class="v"><?= e($m['dusun']) ?></div></div>
        <div class="pf-row"><div class="k">RT</div><div class="sep">:</div><div class="v"><?= e($m['rt']) ?></div></div>
        <div class="pf-row"><div class="k">RW</div><div class="sep">:</div><div class="v"><?= e($m['rw']) ?></div></div>
        <div class="pf-row"><div class="k">Kode Pos</div><div class="sep">:</div><div class="v"><?= e($m['kode_pos']) ?></div></div>
        <div class="pf-row"><div class="k">Jenis Tinggal</div><div class="sep">:</div><div class="v"><?= e($m['jenis_tinggal']) ?></div></div>
        <div class="pf-row"><div class="k">Alat Transportasi</div><div class="sep">:</div><div class="v"><?= e($m['alat_transportasi']) ?></div></div>
        <div class="pf-row"><div class="k">Telepon</div><div class="sep">:</div><div class="v"><?= e($m['telepon']) ?></div></div>
        <div class="pf-row"><div class="k">HP</div><div class="sep">:</div><div class="v"><?= e($m['hp']) ?></div></div>
        <div class="pf-row"><div class="k">Email</div><div class="sep">:</div><div class="v"><?= e($m['email']) ?></div></div>
      </div>
    </section>

    <section class="pf-panel" data-panel="ortu">
      <div class="pf-split">
        <div class="pf-box">
          <div class="pf-box-title">Data Ayah</div>
          <div class="pf-grid">
            <div class="pf-row"><div class="k">Nama Ayah</div><div class="sep">:</div><div class="v"><?= e($m['nama_ayah']) ?></div></div>
            <div class="pf-row"><div class="k">Tanggal Lahir Ayah</div><div class="sep">:</div><div class="v"><?= e($m['tanggal_lahir_ayah']) ?></div></div>
            <div class="pf-row"><div class="k">Pendidikan Ayah</div><div class="sep">:</div><div class="v"><?= e($m['pendidikan_ayah']) ?></div></div>
            <div class="pf-row"><div class="k">Pekerjaan Ayah</div><div class="sep">:</div><div class="v"><?= e($m['pekerjaan_ayah']) ?></div></div>
            <div class="pf-row"><div class="k">Penghasilan Ayah</div><div class="sep">:</div><div class="v"><?= e($m['penghasilan_ayah']) ?></div></div>
          </div>
        </div>

        <div class="pf-box">
          <div class="pf-box-title">Data Ibu</div>
          <div class="pf-grid">
            <div class="pf-row"><div class="k">Nama Ibu</div><div class="sep">:</div><div class="v"><?= e($m['nama_ibu']) ?></div></div>
            <div class="pf-row"><div class="k">Tanggal Lahir Ibu</div><div class="sep">:</div><div class="v"><?= e($m['tanggal_lahir_ibu']) ?></div></div>
            <div class="pf-row"><div class="k">Pendidikan Ibu</div><div class="sep">:</div><div class="v"><?= e($m['pendidikan_ibu']) ?></div></div>
            <div class="pf-row"><div class="k">Pekerjaan Ibu</div><div class="sep">:</div><div class="v"><?= e($m['pekerjaan_ibu']) ?></div></div>
            <div class="pf-row"><div class="k">Penghasilan Ibu</div><div class="sep">:</div><div class="v"><?= e($m['penghasilan_ibu']) ?></div></div>
          </div>
        </div>
      </div>
    </section>

    <section class="pf-panel" data-panel="wali">
      <div class="pf-grid">
        <div class="pf-row"><div class="k">Nama Wali</div><div class="sep">:</div><div class="v"><?= e($m['nama_wali']) ?></div></div>
        <div class="pf-row"><div class="k">Tanggal Lahir Wali</div><div class="sep">:</div><div class="v"><?= e($m['tanggal_lahir_wali']) ?></div></div>
        <div class="pf-row"><div class="k">Pendidikan Wali</div><div class="sep">:</div><div class="v"><?= e($m['pendidikan_wali']) ?></div></div>
        <div class="pf-row"><div class="k">Pekerjaan Wali</div><div class="sep">:</div><div class="v"><?= e($m['pekerjaan_wali']) ?></div></div>
        <div class="pf-row"><div class="k">Penghasilan Wali</div><div class="sep">:</div><div class="v"><?= e($m['penghasilan_wali']) ?></div></div>
      </div>
    </section>
  </div>
</section>

<script src="../js/js_mahasiswa/profile.js"></script>

<?php
renderMahasiswaLayoutEnd([
  "assetsBase" => ".."
]);