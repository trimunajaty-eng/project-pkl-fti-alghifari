<?php
require_once __DIR__ . "/../../config.php";

// wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
  exit;
}

function post($key){
  if (!isset($_POST[$key])) return null;
  $v = trim((string)$_POST[$key]);
  return ($v === '') ? null : $v;
}

$id = (int)($_POST['id_mahasiswa'] ?? 0);
if ($id <= 0) {
  header("Location: data.php?tipe=error&pesan=" . urlencode("ID mahasiswa tidak valid."));
  exit;
}

// WAJIB (sesuai form edit kamu)
// yang optional: KPS(no_kps+penerima_kps), wali, asal PT (termasuk sks_diakui)
$wajib = [
  'tanggal_registrasi',
  'periode_pendaftaran',
  'jenis_pendaftaran',
  'jalur_pendaftaran',
  'program_studi',
  'kelas',
  'nim',
  'jalur_keuangan',

  'nama_mahasiswa',
  'tempat_lahir',
  'tanggal_lahir',
  'jenis_kelamin',
  'golongan_darah',
  'agama',
  'ukuran_seragam',

  'nik','nisn','npwp','kewarganegaraan',
  'provinsi','kab_kota','kecamatan','kelurahan','jalan','dusun','rt','rw','kode_pos',
  'jenis_tinggal','alat_transportasi','telepon','hp','email',

  'nama_ayah','tanggal_lahir_ayah','pendidikan_ayah','pekerjaan_ayah','penghasilan_ayah',
  'nama_ibu','tanggal_lahir_ibu','pendidikan_ibu','pekerjaan_ibu','penghasilan_ibu',

  'asal_sekolah','no_ijazah','alamat_sekolah','kodepos_sekolah','email_sekolah','telepon_sekolah','website_sekolah','asal_jurusan',
];

foreach ($wajib as $f) {
  if (post($f) === null) {
    header("Location: editdata.php?id=".$id."&tipe=error&pesan=" . urlencode("Field wajib belum lengkap."));
    exit;
  }
}

$data = [
  'tanggal_registrasi'       => post('tanggal_registrasi'),
  'periode_pendaftaran'      => post('periode_pendaftaran'),
  'jenis_pendaftaran'        => post('jenis_pendaftaran'),
  'jalur_pendaftaran'        => post('jalur_pendaftaran'),
  'program_studi'            => post('program_studi'),
  'kelas'                    => post('kelas'),
  'nim'                      => post('nim'),
  'jalur_keuangan'           => post('jalur_keuangan'),

  'nama_mahasiswa'           => post('nama_mahasiswa'),
  'tempat_lahir'             => post('tempat_lahir'),
  'tanggal_lahir'            => post('tanggal_lahir'),
  'jenis_kelamin'            => post('jenis_kelamin'),
  'golongan_darah'           => post('golongan_darah'),
  'agama'                    => post('agama'),
  'ukuran_seragam'           => post('ukuran_seragam'),

  'nik'                      => post('nik'),
  'nisn'                     => post('nisn'),
  'npwp'                     => post('npwp'),
  'kewarganegaraan'          => post('kewarganegaraan'),
  'provinsi'                 => post('provinsi'),
  'kab_kota'                 => post('kab_kota'),
  'kecamatan'                => post('kecamatan'),
  'kelurahan'                => post('kelurahan'),
  'jalan'                    => post('jalan'),
  'dusun'                    => post('dusun'),
  'rt'                       => post('rt'),
  'rw'                       => post('rw'),
  'kode_pos'                 => post('kode_pos'),
  'jenis_tinggal'            => post('jenis_tinggal'),
  'alat_transportasi'        => post('alat_transportasi'),
  'telepon'                  => post('telepon'),
  'hp'                       => post('hp'),
  'email'                    => post('email'),

  // optional KPS
  'penerima_kps'             => post('penerima_kps'),
  'no_kps'                   => post('no_kps'),

  // ayah
  'nama_ayah'                => post('nama_ayah'),
  'tanggal_lahir_ayah'       => post('tanggal_lahir_ayah'),
  'pendidikan_ayah'          => post('pendidikan_ayah'),
  'pekerjaan_ayah'           => post('pekerjaan_ayah'),
  'penghasilan_ayah'         => post('penghasilan_ayah'),

  // ibu
  'nama_ibu'                 => post('nama_ibu'),
  'tanggal_lahir_ibu'        => post('tanggal_lahir_ibu'),
  'pendidikan_ibu'           => post('pendidikan_ibu'),
  'pekerjaan_ibu'            => post('pekerjaan_ibu'),
  'penghasilan_ibu'          => post('penghasilan_ibu'),

  // wali (optional)
  'nama_wali'                => post('nama_wali'),
  'tanggal_lahir_wali'       => post('tanggal_lahir_wali'),
  'pendidikan_wali'          => post('pendidikan_wali'),
  'pekerjaan_wali'           => post('pekerjaan_wali'),
  'penghasilan_wali'         => post('penghasilan_wali'),

  // asal sekolah
  'asal_sekolah'             => post('asal_sekolah'),
  'no_ijazah'                => post('no_ijazah'),
  'alamat_sekolah'           => post('alamat_sekolah'),
  'kodepos_sekolah'          => post('kodepos_sekolah'),
  'email_sekolah'            => post('email_sekolah'),
  'telepon_sekolah'          => post('telepon_sekolah'),
  'website_sekolah'          => post('website_sekolah'),
  'asal_jurusan'             => post('asal_jurusan'),

  // asal PT (optional)
  'sks_diakui'               => post('sks_diakui'),
  'asal_perguruan_tinggi'    => post('asal_perguruan_tinggi'),
  'asal_program_studi'       => post('asal_program_studi'),
];

if ($data['sks_diakui'] !== null) $data['sks_diakui'] = (int)$data['sks_diakui'];

// jika penerima_kps kosong, kosongkan no_kps juga biar rapi
if ($data['penerima_kps'] === null) {
  $data['no_kps'] = null;
}

// CEK DUPLIKAT NIM (kecuali dirinya sendiri)
$cek = $conn->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim=? AND id_mahasiswa<>? LIMIT 1");
$cek->bind_param("si", $data['nim'], $id);
$cek->execute();
$cek->store_result();
if ($cek->num_rows > 0) {
  $cek->close();
  header("Location: editdata.php?id=".$id."&tipe=error&pesan=" . urlencode("NIM sudah dipakai mahasiswa lain."));
  exit;
}
$cek->close();

$sql = "UPDATE mahasiswa SET
  tanggal_registrasi=?,
  periode_pendaftaran=?,
  jenis_pendaftaran=?,
  jalur_pendaftaran=?,
  program_studi=?,
  kelas=?,
  nim=?,
  jalur_keuangan=?,

  nama_mahasiswa=?,
  tempat_lahir=?,
  tanggal_lahir=?,
  jenis_kelamin=?,
  golongan_darah=?,
  agama=?,
  ukuran_seragam=?,

  nik=?,
  nisn=?,
  npwp=?,
  kewarganegaraan=?,
  provinsi=?,
  kab_kota=?,
  kecamatan=?,
  kelurahan=?,
  jalan=?,
  dusun=?,
  rt=?,
  rw=?,
  kode_pos=?,
  jenis_tinggal=?,
  alat_transportasi=?,
  telepon=?,
  hp=?,
  email=?,
  penerima_kps=?,
  no_kps=?,

  nama_ayah=?,
  tanggal_lahir_ayah=?,
  pendidikan_ayah=?,
  pekerjaan_ayah=?,
  penghasilan_ayah=?,

  nama_ibu=?,
  tanggal_lahir_ibu=?,
  pendidikan_ibu=?,
  pekerjaan_ibu=?,
  penghasilan_ibu=?,

  nama_wali=?,
  tanggal_lahir_wali=?,
  pendidikan_wali=?,
  pekerjaan_wali=?,
  penghasilan_wali=?,

  asal_sekolah=?,
  no_ijazah=?,
  alamat_sekolah=?,
  kodepos_sekolah=?,
  email_sekolah=?,
  telepon_sekolah=?,
  website_sekolah=?,
  asal_jurusan=?,

  sks_diakui=?,
  asal_perguruan_tinggi=?,
  asal_program_studi=?
WHERE id_mahasiswa=?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  header("Location: editdata.php?id=".$id."&tipe=error&pesan=" . urlencode("Prepare SQL gagal."));
  exit;
}

$values = [
  $data['tanggal_registrasi'],
  $data['periode_pendaftaran'],
  $data['jenis_pendaftaran'],
  $data['jalur_pendaftaran'],
  $data['program_studi'],
  $data['kelas'],
  $data['nim'],
  $data['jalur_keuangan'],

  $data['nama_mahasiswa'],
  $data['tempat_lahir'],
  $data['tanggal_lahir'],
  $data['jenis_kelamin'],
  $data['golongan_darah'],
  $data['agama'],
  $data['ukuran_seragam'],

  $data['nik'],
  $data['nisn'],
  $data['npwp'],
  $data['kewarganegaraan'],
  $data['provinsi'],
  $data['kab_kota'],
  $data['kecamatan'],
  $data['kelurahan'],
  $data['jalan'],
  $data['dusun'],
  $data['rt'],
  $data['rw'],
  $data['kode_pos'],
  $data['jenis_tinggal'],
  $data['alat_transportasi'],
  $data['telepon'],
  $data['hp'],
  $data['email'],
  $data['penerima_kps'],
  $data['no_kps'],

  $data['nama_ayah'],
  $data['tanggal_lahir_ayah'],
  $data['pendidikan_ayah'],
  $data['pekerjaan_ayah'],
  $data['penghasilan_ayah'],

  $data['nama_ibu'],
  $data['tanggal_lahir_ibu'],
  $data['pendidikan_ibu'],
  $data['pekerjaan_ibu'],
  $data['penghasilan_ibu'],

  $data['nama_wali'],
  $data['tanggal_lahir_wali'],
  $data['pendidikan_wali'],
  $data['pekerjaan_wali'],
  $data['penghasilan_wali'],

  $data['asal_sekolah'],
  $data['no_ijazah'],
  $data['alamat_sekolah'],
  $data['kodepos_sekolah'],
  $data['email_sekolah'],
  $data['telepon_sekolah'],
  $data['website_sekolah'],
  $data['asal_jurusan'],

  $data['sks_diakui'],
  $data['asal_perguruan_tinggi'],
  $data['asal_program_studi'],

  $id
];

$types = str_repeat('s', count($values));
$idxSks = count($values) - 4; // sks_diakui posisinya 4 dari belakang (sks, asal_pt, asal_prodi, id)
if ($values[$idxSks] !== null) $types[$idxSks] = 'i';
$types[count($values)-1] = 'i'; // id_mahasiswa

$params = [];
$params[] = $types;
for ($i=0; $i<count($values); $i++) $params[] = &$values[$i];
call_user_func_array([$stmt, 'bind_param'], $params);

if ($stmt->execute()) {
  $nama = $data['nama_mahasiswa'] ?? 'Mahasiswa';
  header("Location: data.php?tipe=success&pesan=" . urlencode("Berhasil edit data mahasiswa: ".$nama));
} else {
  header("Location: editdata.php?id=".$id."&tipe=error&pesan=" . urlencode("Gagal edit data. (".$stmt->error.")"));
}

$stmt->close();
exit;