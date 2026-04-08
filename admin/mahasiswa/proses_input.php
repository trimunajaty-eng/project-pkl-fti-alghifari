<?php
require_once __DIR__ . "/../../config.php";

// wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("Metode tidak valid."));
  exit;
}

// helper: ambil POST, trim, kosong => null
function post($key){
  if (!isset($_POST[$key])) return null;
  $v = trim((string)$_POST[$key]);
  return ($v === '') ? null : $v;
}

// ========================
// FIELD OPTIONAL (tidak wajib)
// ========================
$optionalFields = [
  // KPS
  'penerima_kps','no_kps',

  // WALI (semua)
  'nama_wali','tanggal_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali',

  // ASAL PT (saya buat seluruh tab optional)
  'sks_diakui','asal_perguruan_tinggi','asal_program_studi',
];

// ========================
// VALIDASI: semua wajib kecuali optionalFields
// ========================
$allFields = [
  // header
  'tanggal_registrasi','periode_pendaftaran','jenis_pendaftaran','jalur_pendaftaran',
  'program_studi','kelas','nim','jalur_keuangan',

  // profil
  'nama_mahasiswa','tempat_lahir','tanggal_lahir','jenis_kelamin',
  'golongan_darah','agama','ukuran_seragam',

  // alamat & identitas
  'nik','nisn','npwp','kewarganegaraan',
  'provinsi','kab_kota','kecamatan','kelurahan','jalan','dusun','rt','rw','kode_pos',
  'jenis_tinggal','alat_transportasi','telepon','hp','email','penerima_kps','no_kps',

  // ayah
  'nama_ayah','tanggal_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah',

  // ibu
  'nama_ibu','tanggal_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu',

  // wali
  'nama_wali','tanggal_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali',

  // asal sekolah
  'asal_sekolah','no_ijazah','alamat_sekolah','kodepos_sekolah',
  'email_sekolah','telepon_sekolah','website_sekolah','asal_jurusan',

  // asal PT
  'sks_diakui','asal_perguruan_tinggi','asal_program_studi',
];

foreach ($allFields as $f) {
  if (in_array($f, $optionalFields, true)) continue;
  if (post($f) === null) {
    header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("Data belum lengkap. Pastikan semua field wajib terisi."));
    exit;
  }
}

// ========================
// AMBIL DATA (NULL kalau kosong)
// ========================
$data = [];
foreach ($allFields as $f) {
  $data[$f] = post($f);
}

// normalisasi int untuk sks_diakui (optional)
if ($data['sks_diakui'] !== null) {
  $data['sks_diakui'] = (int)$data['sks_diakui'];
}

// ========================
// CEK DUPLIKAT NIM
// ========================
$cek = $conn->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim=? LIMIT 1");
if (!$cek) {
  header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("Prepare cek NIM gagal: " . $conn->error));
  exit;
}
$cek->bind_param("s", $data['nim']);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
  $cek->close();
  header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("NIM sudah terdaftar."));
  exit;
}
$cek->close();

// ========================
// INSERT: AUTO MATCH kolom & values (ANTI MISMATCH)
// (id_mahasiswa auto, dibuat_pada/diubah_pada auto)
// ========================
$columns = [
  'tanggal_registrasi','periode_pendaftaran','jenis_pendaftaran','jalur_pendaftaran',
  'program_studi','kelas','nim','jalur_keuangan',

  'nama_mahasiswa','tempat_lahir','tanggal_lahir','jenis_kelamin',
  'golongan_darah','agama','ukuran_seragam',

  'nik','nisn','npwp','kewarganegaraan',
  'provinsi','kab_kota','kecamatan','kelurahan','jalan','dusun','rt','rw','kode_pos',
  'jenis_tinggal','alat_transportasi','telepon','hp','email','penerima_kps','no_kps',

  'nama_ayah','tanggal_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah',
  'nama_ibu','tanggal_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu',

  'nama_wali','tanggal_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali',

  'asal_sekolah','no_ijazah','alamat_sekolah','kodepos_sekolah',
  'email_sekolah','telepon_sekolah','website_sekolah','asal_jurusan',

  'sks_diakui','asal_perguruan_tinggi','asal_program_studi'
];

$placeholders = implode(',', array_fill(0, count($columns), '?'));
$sql = "INSERT INTO mahasiswa (" . implode(',', $columns) . ") VALUES ($placeholders)";

try {
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("Prepare insert gagal: " . $conn->error));
    exit;
  }

  // susun values sesuai kolom
  $values = [];
  foreach ($columns as $col) {
    $values[] = $data[$col] ?? null;
  }

  // types: default string, sks_diakui int jika tidak null
  $types = str_repeat('s', count($values));
  $idxSks = array_search('sks_diakui', $columns, true);
  if ($idxSks !== false && $values[$idxSks] !== null) {
    $types[$idxSks] = 'i';
  }

  // bind_param butuh reference
  $params = [];
  $params[] = $types;
  for ($i = 0; $i < count($values); $i++) {
    $params[] = &$values[$i];
  }
  call_user_func_array([$stmt, 'bind_param'], $params);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: inputmahasiswa.php?tipe=success&pesan=" . urlencode("Data mahasiswa berhasil disimpan."));
    exit;
  } else {
    $err = $stmt->error ?: "Execute gagal";
    $stmt->close();
    header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("Gagal menyimpan data: " . $err));
    exit;
  }

} catch (mysqli_sql_exception $e) {
  header("Location: inputmahasiswa.php?tipe=error&pesan=" . urlencode("SQL Error: " . $e->getMessage()));
  exit;
}