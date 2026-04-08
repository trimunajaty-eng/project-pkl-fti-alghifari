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

$menu = 'mhs_input';

$tipe  = $_GET['tipe']  ?? '';
$pesan = $_GET['pesan'] ?? '';

function getDropdownOptions(mysqli $conn, string $grup): array {
  $hasil = [];
  $stmt = $conn->prepare("SELECT value FROM master_opsi_dropdown WHERE grup=? AND is_active=1 ORDER BY urutan ASC, id_opsi ASC");
  if ($stmt) {
    $stmt->bind_param("s", $grup);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $hasil[] = $row['value'];
    }
    $stmt->close();
  }
  return $hasil;
}

$ops_periode = getDropdownOptions($conn, 'periode_pendaftaran');
$ops_jenis_pendaftaran = getDropdownOptions($conn, 'jenis_pendaftaran');
$ops_jalur_pendaftaran = getDropdownOptions($conn, 'jalur_pendaftaran');
$ops_prodi = getDropdownOptions($conn, 'program_studi');
$ops_kelas = getDropdownOptions($conn, 'kelas');

$ops_jk = ["Laki-laki","Perempuan"];
$ops_goldar = ["A","B","AB","O"];
$ops_agama = ["Islam","Kristen","Katolik","Hindu","Buddha","Konghucu","Lainnya"];
$ops_seragam = ["S","M","L","XL","XXL","XXXL"];

$ops_wn = ["WNI","WNA"];
$ops_jenis_tinggal = ["Bersama Orang Tua","Wali","Kos","Asrama","Panti Asuhan","Lainnya"];
$ops_transport = ["Jalan Kaki","Angkutan umum/bus","Mobil/bus antar jemput","Kereta api","Ojek","Andong/bendi/sado/dokar/delman/becak","Perahu penyabrnag/rakit/getek","Kuda","Sepeda","Mobil pribadi","Lainnya"];

$ops_pendidikan = ["Tidak Sekolah","PAUD","TK","SD","SMP","SMA","SMK","Paket A","Paket B","Paket C","D1","D2","D3","D4","S1","S2","S3","Profesi","Spesialis 1","Spesialis 2","Non Formal","Kursus","Lainnya"];
$ops_pekerjaan = ["Tidak Bekerja","PNS/TNI/Polri","Karyawan Swasta","Wiraswasta","Wirausaha","Petani","Nelayan","Buruh","Pedagang Kecil","Pedagang Besar","Guru/Dosen","Pensiunan","Sudah Meninggal","Lainnya"];
$ops_penghasilan = ["Tidak Berpenghasilan","Kurang dari Rp. 500.000","Rp. 500.000 - Rp. 999.000","Rp. 1.000.000 - Rp. 1.999.999","Rp. 2.000.000 - Rp. 4.999.999","Rp. 5.000.000 - Rp. 20.000.000","Lebih dari 20.000.000"];

renderAdminLayoutStart([
  "title"       => "Admin - Input Mahasiswa",
  "page_title"  => "Input Data Mahasiswa",
  "page_sub"    => "Mahasiswa / Input Data",
  "menu"        => $menu,
  "nama_tampil" => $nama_tampil,
  "username"    => $username,
  "assetsBase"  => "../..",
  "basePath"    => "..",
  "extra_css"   => ["css/css_admin/mahasiswa/inputmahasiswa.css"],
]);
?>

<div class="panel">
  <div class="panel-title">Data Mahasiswa Baru</div>
  <div class="panel-body">

    <form class="im-form" id="imForm" action="proses_input.php" method="post" autocomplete="off">
      <div class="im-grid">
        <div class="im-field">
          <label>Tanggal Registrasi <span>*</span></label>
          <input type="date" id="tanggal_registrasi" name="tanggal_registrasi" required>
        </div>

        <div class="im-field">
          <label>Periode Pendaftaran <span>*</span></label>
          <input type="hidden" name="periode_pendaftaran" id="periode_pendaftaran" required>
          <button
            type="button"
            class="im-picker"
            data-picker="1"
            data-group="periode_pendaftaran"
            data-target="periode_pendaftaran"
            data-title="Periode Pendaftaran">
            <span class="im-picker-text" id="periode_pendaftaran_text"></span>
            <span class="im-picker-arrow">⌄</span>
          </button>
        </div>

        <div class="im-field">
          <label>Jenis Pendaftaran <span>*</span></label>
          <input type="hidden" name="jenis_pendaftaran" id="jenis_pendaftaran" required>
          <button
            type="button"
            class="im-picker"
            data-picker="1"
            data-group="jenis_pendaftaran"
            data-target="jenis_pendaftaran"
            data-title="Jenis Pendaftaran">
            <span class="im-picker-text" id="jenis_pendaftaran_text"></span>
            <span class="im-picker-arrow">⌄</span>
          </button>
        </div>

        <div class="im-field">
          <label>Jalur Pendaftaran <span>*</span></label>
          <input type="hidden" name="jalur_pendaftaran" id="jalur_pendaftaran" required>
          <button
            type="button"
            class="im-picker"
            data-picker="1"
            data-group="jalur_pendaftaran"
            data-target="jalur_pendaftaran"
            data-title="Jalur Pendaftaran">
            <span class="im-picker-text" id="jalur_pendaftaran_text"></span>
            <span class="im-picker-arrow">⌄</span>
          </button>
        </div>

        <div class="im-field">
          <label>Program Studi <span>*</span></label>
          <input type="hidden" name="program_studi" id="program_studi" required>
          <button
            type="button"
            class="im-picker"
            data-picker="1"
            data-group="program_studi"
            data-target="program_studi"
            data-title="Program Studi">
            <span class="im-picker-text" id="program_studi_text"></span>
            <span class="im-picker-arrow">⌄</span>
          </button>
        </div>

        <div class="im-field">
          <label>Kelas <span>*</span></label>
          <input type="hidden" name="kelas" id="kelas" required>
          <button
            type="button"
            class="im-picker"
            data-picker="1"
            data-group="kelas"
            data-target="kelas"
            data-title="Kelas">
            <span class="im-picker-text" id="kelas_text"></span>
            <span class="im-picker-arrow">⌄</span>
          </button>
        </div>

        <div class="im-field">
          <label>NIM <span>*</span></label>
          <div class="im-inline">
            <input type="text" id="nim" name="nim" required>
            <button class="im-btn im-get" type="button" id="btnGetNim">Get NIM</button>
          </div>
        </div>

        <div class="im-field">
          <label>Jalur Keuangan <span>*</span></label>
          <input type="text" name="jalur_keuangan" required>
        </div>
      </div>

      <div class="im-tabs" role="tablist" aria-label="Tab Input Mahasiswa">
        <button type="button" class="im-tab active" data-tab="profil" aria-controls="tab-profil">Profil</button>
        <button type="button" class="im-tab" data-tab="alamat" aria-controls="tab-alamat">Alamat</button>
        <button type="button" class="im-tab" data-tab="ortu" aria-controls="tab-ortu">Orang Tua</button>
        <button type="button" class="im-tab" data-tab="wali" aria-controls="tab-wali">Wali</button>
        <button type="button" class="im-tab" data-tab="asalsekolah" aria-controls="tab-asalsekolah">Asal Sekolah</button>
        <button type="button" class="im-tab" data-tab="asalpt" aria-controls="tab-asalpt">Asal Perguruan Tinggi</button>
      </div>

      <div class="im-panels">
        <section class="im-panel active" id="tab-profil" role="tabpanel">
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama Mahasiswa <span>*</span></label>
              <input type="text" name="nama_mahasiswa" required>
            </div>

            <div class="im-field">
              <label>Tempat Lahir <span>*</span></label>
              <input type="text" name="tempat_lahir" required>
            </div>

            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir" required>
            </div>

            <div class="im-field">
              <label>Jenis Kelamin <span>*</span></label>
              <select name="jenis_kelamin" required>
                <option value=""></option>
                <?php foreach($ops_jk as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Golongan Darah <span>*</span></label>
              <select name="golongan_darah" required>
                <option value=""></option>
                <?php foreach($ops_goldar as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Agama <span>*</span></label>
              <select name="agama" required>
                <option value=""></option>
                <?php foreach($ops_agama as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Ukuran Seragam <span>*</span></label>
              <select name="ukuran_seragam" required>
                <option value=""></option>
                <?php foreach($ops_seragam as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <section class="im-panel" id="tab-alamat" role="tabpanel">
          <div class="im-grid">
            <div class="im-field">
              <label>NIK <span>*</span></label>
              <input type="text" name="nik" required>
            </div>
            <div class="im-field">
              <label>NISN <span>*</span></label>
              <input type="text" name="nisn" required>
            </div>
            <div class="im-field">
              <label>NPWP <span>*</span></label>
              <input type="text" name="npwp" required>
            </div>
            <div class="im-field">
              <label>Kewarganegaraan <span>*</span></label>
              <select name="kewarganegaraan" required>
                <option value=""></option>
                <?php foreach($ops_wn as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Provinsi <span>*</span></label>
              <input type="text" name="provinsi" required>
            </div>
            <div class="im-field">
              <label>Kab/Kota <span>*</span></label>
              <input type="text" name="kab_kota" required>
            </div>
            <div class="im-field">
              <label>Kecamatan <span>*</span></label>
              <input type="text" name="kecamatan" required>
            </div>
            <div class="im-field">
              <label>Kelurahan <span>*</span></label>
              <input type="text" name="kelurahan" required>
            </div>

            <div class="im-field im-full">
              <label>Jalan <span>*</span></label>
              <input type="text" name="jalan" required>
            </div>

            <div class="im-field">
              <label>Dusun <span>*</span></label>
              <input type="text" name="dusun" required>
            </div>
            <div class="im-field">
              <label>RT <span>*</span></label>
              <input type="text" name="rt" required>
            </div>
            <div class="im-field">
              <label>RW <span>*</span></label>
              <input type="text" name="rw" required>
            </div>
            <div class="im-field">
              <label>Kode Pos <span>*</span></label>
              <input type="text" name="kode_pos" required>
            </div>

            <div class="im-field">
              <label>Jenis Tinggal <span>*</span></label>
              <select name="jenis_tinggal" required>
                <option value=""></option>
                <?php foreach($ops_jenis_tinggal as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Alat Transportasi <span>*</span></label>
              <select name="alat_transportasi" required>
                <option value=""></option>
                <?php foreach($ops_transport as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Telepon <span>*</span></label>
              <input type="text" name="telepon" required>
            </div>
            <div class="im-field">
              <label>HP <span>*</span></label>
              <input type="text" name="hp" required>
            </div>
            <div class="im-field">
              <label>Email <span>*</span></label>
              <input type="email" name="email" required>
            </div>

            <div class="im-field">
              <label>Penerima KPS ? <span>*</span></label>
              <select name="penerima_kps">
                <option value=""></option>
                <option value="Ya">Ya</option>
                <option value="Tidak">Tidak</option>
              </select>
            </div>

            <div class="im-field">
              <label>No KPS <span>*</span></label>
              <input type="text" name="no_kps">
            </div>
          </div>
        </section>

        <section class="im-panel" id="tab-ortu" role="tabpanel">
          <div class="im-subtitle">Ayah</div>
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama <span>*</span></label>
              <input type="text" name="nama_ayah" required>
            </div>
            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir_ayah" required>
            </div>
            <div class="im-field">
              <label>Pendidikan <span>*</span></label>
              <select name="pendidikan_ayah" required>
                <option value=""></option>
                <?php foreach($ops_pendidikan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Pekerjaan <span>*</span></label>
              <select name="pekerjaan_ayah" required>
                <option value=""></option>
                <?php foreach($ops_pekerjaan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Penghasilan <span>*</span></label>
              <select name="penghasilan_ayah" required>
                <option value=""></option>
                <?php foreach($ops_penghasilan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="im-subtitle">Ibu</div>
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama <span>*</span></label>
              <input type="text" name="nama_ibu" required>
            </div>
            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir_ibu" required>
            </div>
            <div class="im-field">
              <label>Pendidikan <span>*</span></label>
              <select name="pendidikan_ibu" required>
                <option value=""></option>
                <?php foreach($ops_pendidikan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Pekerjaan <span>*</span></label>
              <select name="pekerjaan_ibu" required>
                <option value=""></option>
                <?php foreach($ops_pekerjaan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Penghasilan <span>*</span></label>
              <select name="penghasilan_ibu" required>
                <option value=""></option>
                <?php foreach($ops_penghasilan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <section class="im-panel" id="tab-wali" role="tabpanel">
          <div class="im-subtitle">Wali</div>
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama <span>*</span></label>
              <input type="text" name="nama_wali">
            </div>
            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir_wali">
            </div>
            <div class="im-field">
              <label>Pendidikan <span>*</span></label>
              <select name="pendidikan_wali">
                <option value=""></option>
                <?php foreach($ops_pendidikan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Pekerjaan <span>*</span></label>
              <select name="pekerjaan_wali">
                <option value=""></option>
                <?php foreach($ops_pekerjaan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Penghasilan <span>*</span></label>
              <select name="penghasilan_wali">
                <option value=""></option>
                <?php foreach($ops_penghasilan as $v): ?>
                  <option value="<?= e($v) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <section class="im-panel" id="tab-asalsekolah" role="tabpanel">
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Asal Sekolah <span>*</span></label>
              <input type="text" name="asal_sekolah" required>
            </div>
            <div class="im-field">
              <label>No Ijazah <span>*</span></label>
              <input type="text" name="no_ijazah" required>
            </div>
            <div class="im-field im-full">
              <label>Alamat Sekolah <span>*</span></label>
              <input type="text" name="alamat_sekolah" required>
            </div>
            <div class="im-field">
              <label>Kodepos Sekolah <span>*</span></label>
              <input type="text" name="kodepos_sekolah" required>
            </div>
            <div class="im-field">
              <label>Email Sekolah <span>*</span></label>
              <input type="email" name="email_sekolah" required>
            </div>
            <div class="im-field">
              <label>Telepon Sekolah <span>*</span></label>
              <input type="text" name="telepon_sekolah" required>
            </div>
            <div class="im-field">
              <label>Website Sekolah <span>*</span></label>
              <input type="text" name="website_sekolah" required>
            </div>
            <div class="im-field">
              <label>Asal Jurusan <span>*</span></label>
              <input type="text" name="asal_jurusan" required>
            </div>
          </div>
        </section>

        <section class="im-panel" id="tab-asalpt" role="tabpanel">
          <div class="im-grid">
            <div class="im-field">
              <label>SKS diakui <span>*</span></label>
              <input type="number" name="sks_diakui" min="0">
            </div>
            <div class="im-field">
              <label>Asal Perguruan Tinggi <span>*</span></label>
              <input type="text" name="asal_perguruan_tinggi">
            </div>
            <div class="im-field">
              <label>Asal Program Studi <span>*</span></label>
              <input type="text" name="asal_program_studi">
            </div>
          </div>
        </section>
      </div>

      <div class="im-actions">
        <button class="im-btn" type="submit" id="btnSubmit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="im-modal" id="opsiModal" aria-hidden="true">
  <div class="im-modal-backdrop" data-close-modal="1"></div>
  <div class="im-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="opsiModalTitle">
    <div class="im-modal-head">
      <div>
        <div class="im-modal-title" id="opsiModalTitle">Kelola Opsi</div>
        <div class="im-modal-subtitle" id="opsiModalSubtitle">Pilih data atau kelola create, edit, delete.</div>
      </div>
      <button type="button" class="im-modal-close" id="btnCloseOpsiModal" aria-label="Tutup">×</button>
    </div>

    <div class="im-modal-body">
      <div class="im-modal-form">
        <input type="hidden" id="opsiEditId" value="">
        <div class="im-field">
          <label>Nama Opsi</label>
          <input type="text" id="opsiNamaInput" placeholder="Tulis nama opsi...">
        </div>

        <div class="im-field" id="wrapKodeRef" hidden>
          <label>Kode Awalan Huruf</label>
          <input type="text" id="opsiKodeRefInput" placeholder="Contoh: FTI">
        </div>

        <div class="im-field" id="wrapKodeNim" hidden>
          <label>Kode NIM Angka</label>
          <input type="text" id="opsiKodeNimInput" placeholder="Contoh: 57201">
        </div>

        <div class="im-modal-actions-top">
          <button type="button" class="im-btn im-btn-secondary" id="btnResetOpsi">Reset</button>
          <button type="button" class="im-btn" id="btnSaveOpsi">Create</button>
        </div>
      </div>

      <div class="im-option-list-wrap">
        <div class="im-option-list-title">Daftar Opsi</div>
        <div class="im-option-list" id="opsiList"></div>
      </div>
    </div>
  </div>
</div>

<div class="loading" id="loading" aria-hidden="true">
  <div class="loading-card">
    <div class="spinner" aria-hidden="true"></div>
    <div class="loading-text">Sedang memproses...</div>
  </div>
</div>

<div class="toast" id="toast" aria-hidden="true">
  <div class="toast-card" id="toastCard">
    <div class="toast-title" id="toastTitle">Info</div>
    <div class="toast-msg" id="toastMsg">...</div>
  </div>
</div>

<script>
  window.__MENU_AKTIF__ = <?= json_encode($menu) ?>;
  window.__FLASH_TIPE__ = <?= json_encode($tipe) ?>;
  window.__FLASH_PESAN__ = <?= json_encode($pesan) ?>;
</script>

<?php
renderAdminLayoutEnd([
  "assetsBase" => "../..",
  "extra_js"   => ["js/js_admin/mahasiswa/inputmahasiswa.js"],
]);