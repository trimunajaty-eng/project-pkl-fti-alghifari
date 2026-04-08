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

$menu = 'mhs_data'; // biar menu sidebar aktif ke data mahasiswa (sesuaikan kalau di layout pakai key lain)

$tipe  = $_GET['tipe']  ?? '';
$pesan = $_GET['pesan'] ?? '';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header("Location: data.php?tipe=error&pesan=" . urlencode("ID mahasiswa tidak valid."));
  exit;
}

// ambil data mahasiswa
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$rs = $stmt->get_result();
$m = $rs ? $rs->fetch_assoc() : null;
$stmt->close();

if (!$m) {
  header("Location: data.php?tipe=error&pesan=" . urlencode("Data mahasiswa tidak ditemukan."));
  exit;
}

/* opsi dropdown (TEKS TIDAK DIUBAH) */
$ops_periode = ["2025/2026 Ganjil","2025/2026 Genap","2026/2027 Ganjil","2026/2027 Genap"];
$ops_jenis_pendaftaran = ["Peserta didik baru","Pindahan","Alih jenjang","Lintas jalur"];
$ops_jalur_pendaftaran = ["","Prestasi","Reguler","RPL"];
$ops_prodi = ["Teknik Informatika S1","Sistem Informasi S1"];
$ops_kelas = ["Reguler","Reguler Sore B","Reguler Sore A","Karyawan MJ","AMIK HASS WD","AMIK HASS WE","Miftahul Huda","Reguler A1","Reguler A2","Reguler A3","Reguler A4","Reguler B1","Reguler B2"];

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

function sel($current, $value){
  return ((string)$current === (string)$value) ? 'selected' : '';
}

renderAdminLayoutStart([
  "title"       => "Admin - Edit Mahasiswa",
  "page_title"  => "Edit Data Mahasiswa",
  "page_sub"    => "Mahasiswa / Edit Data",
  "menu"        => $menu,
  "nama_tampil" => $nama_tampil,
  "username"    => $username,
  "assetsBase"  => "../..",
  "basePath"    => "..",
  "extra_css"   => [
    "css/css_admin/mahasiswa/inputmahasiswa.css",
    "css/css_admin/mahasiswa/editdata.css",
  ],
]);
?>

<div class="panel">
  <div class="panel-title">Edit Data Mahasiswa</div>
  <div class="panel-body">

    <form class="im-form" id="imForm" action="proses_edit.php" method="post" autocomplete="off">
      <input type="hidden" name="id_mahasiswa" value="<?= (int)$m['id_mahasiswa'] ?>">

      <!-- HEADER FIELDS -->
      <div class="im-grid">
        <div class="im-field">
          <label>Tanggal Registrasi <span>*</span></label>
          <input type="date" id="tanggal_registrasi" name="tanggal_registrasi" required value="<?= e($m['tanggal_registrasi'] ?? '') ?>">
        </div>

        <div class="im-field">
          <label>Periode Pendaftaran <span>*</span></label>
          <select name="periode_pendaftaran" required>
            <option value="">-- pilih --</option>
            <?php foreach($ops_periode as $v): ?>
              <option value="<?= e($v) ?>" <?= sel($m['periode_pendaftaran'] ?? '', $v) ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="im-field">
          <label>Jenis Pendaftaran <span>*</span></label>
          <select name="jenis_pendaftaran" required>
            <option value="">-- pilih --</option>
            <?php foreach($ops_jenis_pendaftaran as $v): ?>
              <option value="<?= e($v) ?>" <?= sel($m['jenis_pendaftaran'] ?? '', $v) ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="im-field">
          <label>Jalur Pendaftaran <span>*</span></label>
          <select name="jalur_pendaftaran" required>
            <option value="">-- pilih --</option>
            <?php foreach($ops_jalur_pendaftaran as $v): ?>
              <option value="<?= e($v) ?>" <?= sel($m['jalur_pendaftaran'] ?? '', $v) ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="im-field">
          <label>Program Studi <span>*</span></label>
          <select id="program_studi" name="program_studi" required>
            <option value="">-- pilih --</option>
            <?php foreach($ops_prodi as $v): ?>
              <option value="<?= e($v) ?>" <?= sel($m['program_studi'] ?? '', $v) ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="im-field">
          <label>Kelas <span>*</span></label>
          <select name="kelas" required>
            <option value="">-- pilih --</option>
            <?php foreach($ops_kelas as $v): ?>
              <option value="<?= e($v) ?>" <?= sel($m['kelas'] ?? '', $v) ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="im-field">
          <label>NIM <span>*</span></label>
          <div class="im-inline">
            <!-- NIM boleh diedit kalau kamu mau. Kalau mau dikunci: tambah readonly -->
            <input type="text" id="nim" name="nim" required value="<?= e($m['nim'] ?? '') ?>">
            <!-- Edit: tidak pakai get NIM -->
            <button class="im-btn im-get is-disabled" type="button" disabled title="Tidak tersedia di Edit">Get NIM</button>
          </div>
          <div class="im-help">Untuk edit, tombol Get NIM dimatikan.</div>
        </div>

        <div class="im-field">
          <label>Jalur Keuangan <span>*</span></label>
          <input type="text" name="jalur_keuangan" required value="<?= e($m['jalur_keuangan'] ?? '') ?>">
        </div>
      </div>

      <!-- TABS -->
      <div class="im-tabs" role="tablist" aria-label="Tab Edit Mahasiswa">
        <button type="button" class="im-tab active" data-tab="profil" aria-controls="tab-profil">
          Profil <span class="im-alert" data-alert="profil" aria-hidden="true"></span>
        </button>
        <button type="button" class="im-tab" data-tab="alamat" aria-controls="tab-alamat">
          Alamat <span class="im-alert" data-alert="alamat" aria-hidden="true"></span>
        </button>
        <button type="button" class="im-tab" data-tab="ortu" aria-controls="tab-ortu">
          Orang Tua <span class="im-alert" data-alert="ortu" aria-hidden="true"></span>
        </button>
        <button type="button" class="im-tab" data-tab="wali" aria-controls="tab-wali">
          Wali <span class="im-alert" data-alert="wali" aria-hidden="true"></span>
        </button>
        <button type="button" class="im-tab" data-tab="asalsekolah" aria-controls="tab-asalsekolah">
          Asal Sekolah <span class="im-alert" data-alert="asalsekolah" aria-hidden="true"></span>
        </button>
        <button type="button" class="im-tab" data-tab="asalpt" aria-controls="tab-asalpt">
          Asal Perguruan Tinggi <span class="im-alert" data-alert="asalpt" aria-hidden="true"></span>
        </button>
      </div>

      <div class="im-panels">

        <!-- PROFIL -->
        <section class="im-panel active" id="tab-profil" role="tabpanel">
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama Mahasiswa <span>*</span></label>
              <input type="text" name="nama_mahasiswa" required value="<?= e($m['nama_mahasiswa'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Tempat Lahir <span>*</span></label>
              <input type="text" name="tempat_lahir" required value="<?= e($m['tempat_lahir'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir" required value="<?= e($m['tanggal_lahir'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Jenis Kelamin <span>*</span></label>
              <select name="jenis_kelamin" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_jk as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['jenis_kelamin'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Golongan Darah <span>*</span></label>
              <select name="golongan_darah" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_goldar as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['golongan_darah'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Agama <span>*</span></label>
              <select name="agama" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_agama as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['agama'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Ukuran Seragam <span>*</span></label>
              <select name="ukuran_seragam" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_seragam as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['ukuran_seragam'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <!-- ALAMAT -->
        <section class="im-panel" id="tab-alamat" role="tabpanel">
          <div class="im-grid">
            <div class="im-field">
              <label>NIK <span>*</span></label>
              <input type="text" name="nik" required value="<?= e($m['nik'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>NISN <span>*</span></label>
              <input type="text" name="nisn" required value="<?= e($m['nisn'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>NPWP <span>*</span></label>
              <input type="text" name="npwp" required value="<?= e($m['npwp'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Kewarganegaraan <span>*</span></label>
              <select name="kewarganegaraan" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_wn as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['kewarganegaraan'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Provinsi <span>*</span></label>
              <input type="text" name="provinsi" required value="<?= e($m['provinsi'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Kab/Kota <span>*</span></label>
              <input type="text" name="kab_kota" required value="<?= e($m['kab_kota'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Kecamatan <span>*</span></label>
              <input type="text" name="kecamatan" required value="<?= e($m['kecamatan'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Kelurahan <span>*</span></label>
              <input type="text" name="kelurahan" required value="<?= e($m['kelurahan'] ?? '') ?>">
            </div>

            <div class="im-field im-full">
              <label>Jalan <span>*</span></label>
              <input type="text" name="jalan" required value="<?= e($m['jalan'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Dusun <span>*</span></label>
              <input type="text" name="dusun" required value="<?= e($m['dusun'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>RT <span>*</span></label>
              <input type="text" name="rt" required value="<?= e($m['rt'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>RW <span>*</span></label>
              <input type="text" name="rw" required value="<?= e($m['rw'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Kode Pos <span>*</span></label>
              <input type="text" name="kode_pos" required value="<?= e($m['kode_pos'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Jenis Tinggal <span>*</span></label>
              <select name="jenis_tinggal" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_jenis_tinggal as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['jenis_tinggal'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Alat Transportasi <span>*</span></label>
              <select name="alat_transportasi" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_transport as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['alat_transportasi'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="im-field">
              <label>Telepon <span>*</span></label>
              <input type="text" name="telepon" required value="<?= e($m['telepon'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>HP <span>*</span></label>
              <input type="text" name="hp" required value="<?= e($m['hp'] ?? '') ?>">
            </div>

            <div class="im-field">
              <label>Email <span>*</span></label>
              <input type="email" name="email" required value="<?= e($m['email'] ?? '') ?>">
            </div>

            <!-- KPS optional (sesuai permintaan kamu sebelumnya) -->
            <div class="im-field">
              <label>Penerima KPS ?</label>
              <select name="penerima_kps">
                <option value="">-- pilih --</option>
                <option value="Ya" <?= sel($m['penerima_kps'] ?? '', 'Ya') ?>>Ya</option>
                <option value="Tidak" <?= sel($m['penerima_kps'] ?? '', 'Tidak') ?>>Tidak</option>
              </select>
            </div>

            <div class="im-field">
              <label>No KPS</label>
              <input type="text" name="no_kps" value="<?= e($m['no_kps'] ?? '') ?>">
            </div>

          </div>
        </section>

        <!-- ORANG TUA -->
        <section class="im-panel" id="tab-ortu" role="tabpanel">
          <div class="im-subtitle">Ayah</div>
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama <span>*</span></label>
              <input type="text" name="nama_ayah" required value="<?= e($m['nama_ayah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir_ayah" required value="<?= e($m['tanggal_lahir_ayah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Pendidikan <span>*</span></label>
              <select name="pendidikan_ayah" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_pendidikan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['pendidikan_ayah'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Pekerjaan <span>*</span></label>
              <select name="pekerjaan_ayah" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_pekerjaan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['pekerjaan_ayah'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Penghasilan <span>*</span></label>
              <select name="penghasilan_ayah" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_penghasilan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['penghasilan_ayah'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="im-subtitle">Ibu</div>
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama <span>*</span></label>
              <input type="text" name="nama_ibu" required value="<?= e($m['nama_ibu'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Tanggal Lahir <span>*</span></label>
              <input type="date" name="tanggal_lahir_ibu" required value="<?= e($m['tanggal_lahir_ibu'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Pendidikan <span>*</span></label>
              <select name="pendidikan_ibu" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_pendidikan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['pendidikan_ibu'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Pekerjaan <span>*</span></label>
              <select name="pekerjaan_ibu" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_pekerjaan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['pekerjaan_ibu'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Penghasilan <span>*</span></label>
              <select name="penghasilan_ibu" required>
                <option value="">-- pilih --</option>
                <?php foreach($ops_penghasilan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['penghasilan_ibu'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <!-- WALI (optional) -->
        <section class="im-panel" id="tab-wali" role="tabpanel">
          <div class="im-subtitle">Wali</div>
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Nama</label>
              <input type="text" name="nama_wali" value="<?= e($m['nama_wali'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir_wali" value="<?= e($m['tanggal_lahir_wali'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Pendidikan</label>
              <select name="pendidikan_wali">
                <option value="">-- pilih --</option>
                <?php foreach($ops_pendidikan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['pendidikan_wali'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Pekerjaan</label>
              <select name="pekerjaan_wali">
                <option value="">-- pilih --</option>
                <?php foreach($ops_pekerjaan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['pekerjaan_wali'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="im-field">
              <label>Penghasilan</label>
              <select name="penghasilan_wali">
                <option value="">-- pilih --</option>
                <?php foreach($ops_penghasilan as $v): ?>
                  <option value="<?= e($v) ?>" <?= sel($m['penghasilan_wali'] ?? '', $v) ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </section>

        <!-- ASAL SEKOLAH -->
        <section class="im-panel" id="tab-asalsekolah" role="tabpanel">
          <div class="im-grid">
            <div class="im-field im-full">
              <label>Asal Sekolah <span>*</span></label>
              <input type="text" name="asal_sekolah" required value="<?= e($m['asal_sekolah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>No Ijazah <span>*</span></label>
              <input type="text" name="no_ijazah" required value="<?= e($m['no_ijazah'] ?? '') ?>">
            </div>
            <div class="im-field im-full">
              <label>Alamat Sekolah <span>*</span></label>
              <input type="text" name="alamat_sekolah" required value="<?= e($m['alamat_sekolah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Kodepos Sekolah <span>*</span></label>
              <input type="text" name="kodepos_sekolah" required value="<?= e($m['kodepos_sekolah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Email Sekolah <span>*</span></label>
              <input type="email" name="email_sekolah" required value="<?= e($m['email_sekolah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Telepon Sekolah <span>*</span></label>
              <input type="text" name="telepon_sekolah" required value="<?= e($m['telepon_sekolah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Website Sekolah <span>*</span></label>
              <input type="text" name="website_sekolah" required value="<?= e($m['website_sekolah'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Asal Jurusan <span>*</span></label>
              <input type="text" name="asal_jurusan" required value="<?= e($m['asal_jurusan'] ?? '') ?>">
            </div>
          </div>
        </section>

        <!-- ASAL PT (optional) -->
        <section class="im-panel" id="tab-asalpt" role="tabpanel">
          <div class="im-grid">
            <div class="im-field">
              <label>SKS diakui</label>
              <input type="number" name="sks_diakui" min="0" value="<?= e($m['sks_diakui'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Asal Perguruan Tinggi</label>
              <input type="text" name="asal_perguruan_tinggi" value="<?= e($m['asal_perguruan_tinggi'] ?? '') ?>">
            </div>
            <div class="im-field">
              <label>Asal Program Studi</label>
              <input type="text" name="asal_program_studi" value="<?= e($m['asal_program_studi'] ?? '') ?>">
            </div>
          </div>
        </section>

      </div>

      <div class="im-actions">
        <button class="im-btn" type="submit" id="btnSubmit">Simpan Perubahan</button>
        <a class="im-btn ghost" href="data.php">Kembali</a>
      </div>

    </form>

  </div>
</div>

<!-- LOADING -->
<div class="loading" id="loading" aria-hidden="true">
  <div class="loading-card">
    <div class="spinner" aria-hidden="true"></div>
    <div class="loading-text">Sedang memproses...</div>
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
  window.__MENU_AKTIF__  = <?= json_encode($menu) ?>;
  window.__FLASH_TIPE__  = <?= json_encode($tipe) ?>;
  window.__FLASH_PESAN__ = <?= json_encode($pesan) ?>;
</script>

<?php
renderAdminLayoutEnd([
  "assetsBase" => "../..",
  "extra_js"   => ["js/js_admin/mahasiswa/editdata.js"],
]);