<?php
require_once __DIR__ . "/../../config.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  echo json_encode(["ok" => false, "message" => "Unauthorized"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["ok" => false, "message" => "Method tidak valid"]);
  exit;
}

function post($key){
  if (!isset($_POST[$key])) return null;
  $v = trim((string)$_POST[$key]);
  return ($v === '') ? null : $v;
}

try {
  // Validasi field wajib
  $required = [
    'tanggal_registrasi', 'periode_pendaftaran', 'jenis_pendaftaran', 
    'jalur_pendaftaran', 'program_studi', 'kelas', 'nim', 'jalur_keuangan',
    'nama_mahasiswa', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
    'golongan_darah', 'agama', 'ukuran_seragam',
    'nik', 'nisn', 'npwp', 'kewarganegaraan',
    'provinsi', 'kab_kota', 'kecamatan', 'kelurahan', 'jalan',
    'dusun', 'rt', 'rw', 'kode_pos',
    'jenis_tinggal', 'alat_transportasi', 'telepon', 'hp', 'email',
    'nama_ayah', 'tanggal_lahir_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
    'nama_ibu', 'tanggal_lahir_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
    'asal_sekolah', 'no_ijazah', 'alamat_sekolah', 'kodepos_sekolah',
    'email_sekolah', 'telepon_sekolah', 'website_sekolah', 'asal_jurusan'
  ];

  foreach ($required as $field) {
    if (post($field) === null) {
      throw new Exception("Field \"$field\" wajib diisi.");
    }
  }

  // Kumpulkan data
  $data = [];
  foreach ($required as $f) $data[$f] = post($f);
  
  $optional = ['penerima_kps','no_kps','nama_wali','tanggal_lahir_wali','pendidikan_wali',
               'pekerjaan_wali','penghasilan_wali','sks_diakui','asal_perguruan_tinggi','asal_program_studi'];
  foreach ($optional as $f) $data[$f] = post($f);
  
  if ($data['sks_diakui'] !== null) $data['sks_diakui'] = (int)$data['sks_diakui'];

  // Cek duplikat NIM
  $stmt = $conn->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE nim = ? LIMIT 1");
  $stmt->bind_param("s", $data['nim']);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    $stmt->close();
    throw new Exception("NIM {$data['nim']} sudah terdaftar.");
  }
  $stmt->close();

  // START TRANSACTION
  $conn->begin_transaction();

  // INSERT MAHASISWA
  $columns = array_keys($data);
  $placeholders = str_repeat('?,', count($columns) - 1) . '?';
  $sql = "INSERT INTO mahasiswa (" . implode(',', $columns) . ") VALUES ($placeholders)";
  
  $stmtMhs = $conn->prepare($sql);
  if (!$stmtMhs) {
    throw new Exception("Prepare mahasiswa gagal: " . $conn->error);
  }

  $types = '';
  foreach ($columns as $col) {
    $types .= ($col === 'sks_diakui') ? 'i' : 's';
  }

  $bindValues = [$types];
  foreach ($columns as $col) {
    $bindValues[] = $data[$col];
  }

  $stmtMhsRef = new ReflectionClass('mysqli_stmt');
  $bind_method = $stmtMhsRef->getMethod('bind_param');
  $bind_params = [];
  foreach ($bindValues as $key => $value) {
    $bind_params[$key] = &$bindValues[$key];
  }
  $bind_method->invokeArgs($stmtMhs, $bind_params);

  if (!$stmtMhs->execute()) {
    throw new Exception("Insert mahasiswa gagal: " . $stmtMhs->error);
  }
  
  $idMahasiswa = $stmtMhs->insert_id;
  $stmtMhs->close();

  // CREATE USER ACCOUNT (TANPA EMAIL)
  $username = $data['nim'];
  $passwordPlain = substr(md5(uniqid(rand(), true)), 0, 8);
  $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
  $namaLengkap = $data['nama_mahasiswa'];
  $role = 'mahasiswa';
  $status = 'aktif';

  $sqlUser = "INSERT INTO users (username, password_hash, nama_lengkap, role, status, dibuat_pada) 
              VALUES (?, ?, ?, ?, ?, NOW())";
  
  $stmtUser = $conn->prepare($sqlUser);
  if (!$stmtUser) {
    throw new Exception("Prepare users gagal: " . $conn->error);
  }

  // Bind 5 parameter (tanpa email)
  $stmtUser->bind_param("sssss", $username, $passwordHash, $namaLengkap, $role, $status);
  
  if (!$stmtUser->execute()) {
    if ($conn->errno === 1062) {
      throw new Exception("Username (NIM) sudah terdaftar.");
    }
    throw new Exception("Insert users gagal: " . $stmtUser->error);
  }
  
  $idUser = $stmtUser->insert_id;
  $stmtUser->close();

  // COMMIT
  $conn->commit();

  echo json_encode([
    "ok" => true,
    "message" => "Data mahasiswa dan akun berhasil dibuat!",
    "username" => $username,
    "password_temp" => $passwordPlain,
    "nim" => $data['nim']
  ]);

} catch (Exception $e) {
  if (isset($conn)) $conn->rollback();
  error_log("[InputMhs Error] " . $e->getMessage());
  
  echo json_encode([
    "ok" => false,
    "message" => $e->getMessage()
  ]);
  exit;
}