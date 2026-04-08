<?php
require_once __DIR__ . "/../../config.php";

header('Content-Type: application/json; charset=UTF-8');

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode([
    'ok' => false,
    'message' => 'Akses ditolak.'
  ]);
  exit;
}

$allowedGroups = [
  'periode_pendaftaran',
  'jenis_pendaftaran',
  'jalur_pendaftaran',
  'program_studi',
  'kelas',
];

$fieldMapMahasiswa = [
  'periode_pendaftaran' => 'periode_pendaftaran',
  'jenis_pendaftaran'   => 'jenis_pendaftaran',
  'jalur_pendaftaran'   => 'jalur_pendaftaran',
  'program_studi'       => 'program_studi',
  'kelas'               => 'kelas',
];

function jsonOut($ok, $message = '', $items = []){
  echo json_encode([
    'ok' => $ok,
    'message' => $message,
    'items' => $items,
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

function clean($v){
  return trim((string)$v);
}

function getItems(mysqli $conn, string $group): array {
  $items = [];
  $stmt = $conn->prepare("
    SELECT id_opsi, grup, label, value, urutan, kode_ref, kode_nim
    FROM master_opsi_dropdown
    WHERE grup=? AND is_active=1
    ORDER BY urutan ASC, id_opsi ASC
  ");
  if (!$stmt) return $items;

  $stmt->bind_param("s", $group);
  $stmt->execute();
  $res = $stmt->get_result();

  while ($row = $res->fetch_assoc()) {
    $items[] = [
      'id' => (int)$row['id_opsi'],
      'group' => $row['grup'],
      'label' => $row['label'],
      'value' => $row['value'],
      'urutan' => (int)$row['urutan'],
      'kode_ref' => $row['kode_ref'],
      'kode_nim' => $row['kode_nim'],
    ];
  }

  $stmt->close();
  return $items;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $group = clean($_GET['group'] ?? '');
  if (!in_array($group, $allowedGroups, true)) {
    jsonOut(false, 'Grup dropdown tidak valid.');
  }
  jsonOut(true, 'OK', getItems($conn, $group));
}

if ($method !== 'POST') {
  jsonOut(false, 'Metode tidak valid.');
}

$action = clean($_POST['action'] ?? '');
$group  = clean($_POST['group'] ?? '');

if (!in_array($group, $allowedGroups, true)) {
  jsonOut(false, 'Grup dropdown tidak valid.');
}

$isProgramStudi = ($group === 'program_studi');
$kodeRef = strtoupper(clean($_POST['kode_ref'] ?? ''));
$kodeNim = clean($_POST['kode_nim'] ?? '');

if ($isProgramStudi) {
  if ($kodeRef === '') {
    jsonOut(false, 'Kode awalan huruf wajib diisi.');
  }
  if ($kodeNim === '') {
    jsonOut(false, 'Kode NIM angka wajib diisi.');
  }
}

if ($action === 'create') {
  $value = clean($_POST['value'] ?? '');

  if ($value === '') {
    jsonOut(false, 'Nama opsi wajib diisi.');
  }

  $cek = $conn->prepare("SELECT id_opsi FROM master_opsi_dropdown WHERE grup=? AND value=? LIMIT 1");
  $cek->bind_param("ss", $group, $value);
  $cek->execute();
  $cek->store_result();
  if ($cek->num_rows > 0) {
    $cek->close();
    jsonOut(false, 'Opsi sudah ada.');
  }
  $cek->close();

  if ($isProgramStudi) {
    $cekKode = $conn->prepare("SELECT id_opsi FROM master_opsi_dropdown WHERE grup='program_studi' AND (kode_ref=? OR kode_nim=?) LIMIT 1");
    $cekKode->bind_param("ss", $kodeRef, $kodeNim);
    $cekKode->execute();
    $cekKode->store_result();
    if ($cekKode->num_rows > 0) {
      $cekKode->close();
      jsonOut(false, 'Kode awalan atau kode NIM sudah digunakan.');
    }
    $cekKode->close();
  }

  $urutan = 1;
  $stmtMax = $conn->prepare("SELECT COALESCE(MAX(urutan), 0) + 1 AS next_urutan FROM master_opsi_dropdown WHERE grup=?");
  $stmtMax->bind_param("s", $group);
  $stmtMax->execute();
  $resMax = $stmtMax->get_result();
  if ($rowMax = $resMax->fetch_assoc()) {
    $urutan = (int)$rowMax['next_urutan'];
  }
  $stmtMax->close();

  $label = $value;
  $stmt = $conn->prepare("
    INSERT INTO master_opsi_dropdown (grup, label, value, urutan, is_active, kode_ref, kode_nim)
    VALUES (?, ?, ?, ?, 1, ?, ?)
  ");
  if (!$stmt) {
    jsonOut(false, 'Prepare insert gagal: ' . $conn->error);
  }
  $stmt->bind_param("sssiss", $group, $label, $value, $urutan, $kodeRef, $kodeNim);

  if (!$stmt->execute()) {
    $err = $stmt->error ?: 'Gagal menyimpan.';
    $stmt->close();
    jsonOut(false, $err);
  }
  $stmt->close();

  jsonOut(true, 'Opsi berhasil ditambahkan.', getItems($conn, $group));
}

if ($action === 'update') {
  $id    = (int)($_POST['id'] ?? 0);
  $value = clean($_POST['value'] ?? '');

  if ($id <= 0) {
    jsonOut(false, 'ID opsi tidak valid.');
  }
  if ($value === '') {
    jsonOut(false, 'Nama opsi wajib diisi.');
  }

  $stmtOld = $conn->prepare("SELECT value, kode_ref, kode_nim FROM master_opsi_dropdown WHERE id_opsi=? AND grup=? LIMIT 1");
  $stmtOld->bind_param("is", $id, $group);
  $stmtOld->execute();
  $resOld = $stmtOld->get_result();
  $rowOld = $resOld->fetch_assoc();
  $stmtOld->close();

  if (!$rowOld) {
    jsonOut(false, 'Data opsi tidak ditemukan.');
  }

  $oldValue = $rowOld['value'];

  $cek = $conn->prepare("SELECT id_opsi FROM master_opsi_dropdown WHERE grup=? AND value=? AND id_opsi<>? LIMIT 1");
  $cek->bind_param("ssi", $group, $value, $id);
  $cek->execute();
  $cek->store_result();
  if ($cek->num_rows > 0) {
    $cek->close();
    jsonOut(false, 'Nama opsi sudah digunakan.');
  }
  $cek->close();

  if ($isProgramStudi) {
    $cekKode = $conn->prepare("SELECT id_opsi FROM master_opsi_dropdown WHERE grup='program_studi' AND (kode_ref=? OR kode_nim=?) AND id_opsi<>? LIMIT 1");
    $cekKode->bind_param("ssi", $kodeRef, $kodeNim, $id);
    $cekKode->execute();
    $cekKode->store_result();
    if ($cekKode->num_rows > 0) {
      $cekKode->close();
      jsonOut(false, 'Kode awalan atau kode NIM sudah digunakan.');
    }
    $cekKode->close();
  }

  $label = $value;
  $stmt = $conn->prepare("
    UPDATE master_opsi_dropdown
    SET label=?, value=?, kode_ref=?, kode_nim=?
    WHERE id_opsi=? AND grup=?
  ");
  if (!$stmt) {
    jsonOut(false, 'Prepare update gagal: ' . $conn->error);
  }
  $stmt->bind_param("ssssis", $label, $value, $kodeRef, $kodeNim, $id, $group);

  if (!$stmt->execute()) {
    $err = $stmt->error ?: 'Gagal update.';
    $stmt->close();
    jsonOut(false, $err);
  }
  $stmt->close();

  if (isset($fieldMapMahasiswa[$group])) {
    $field = $fieldMapMahasiswa[$group];
    $sqlSync = "UPDATE mahasiswa SET {$field}=? WHERE {$field}=?";
    $stmtSync = $conn->prepare($sqlSync);
    if ($stmtSync) {
      $stmtSync->bind_param("ss", $value, $oldValue);
      $stmtSync->execute();
      $stmtSync->close();
    }
  }

  jsonOut(true, 'Opsi berhasil diupdate.', getItems($conn, $group));
}

if ($action === 'delete') {
  $id = (int)($_POST['id'] ?? 0);

  if ($id <= 0) {
    jsonOut(false, 'ID opsi tidak valid.');
  }

  $stmtRow = $conn->prepare("SELECT value FROM master_opsi_dropdown WHERE id_opsi=? AND grup=? LIMIT 1");
  $stmtRow->bind_param("is", $id, $group);
  $stmtRow->execute();
  $resRow = $stmtRow->get_result();
  $row = $resRow->fetch_assoc();
  $stmtRow->close();

  if (!$row) {
    jsonOut(false, 'Data opsi tidak ditemukan.');
  }

  $value = $row['value'];

  if (isset($fieldMapMahasiswa[$group])) {
    $field = $fieldMapMahasiswa[$group];
    $sqlUse = "SELECT id_mahasiswa FROM mahasiswa WHERE {$field}=? LIMIT 1";
    $stmtUse = $conn->prepare($sqlUse);
    if ($stmtUse) {
      $stmtUse->bind_param("s", $value);
      $stmtUse->execute();
      $stmtUse->store_result();
      if ($stmtUse->num_rows > 0) {
        $stmtUse->close();
        jsonOut(false, 'Opsi tidak bisa dihapus karena sudah dipakai data mahasiswa.');
      }
      $stmtUse->close();
    }
  }

  $stmt = $conn->prepare("DELETE FROM master_opsi_dropdown WHERE id_opsi=? AND grup=?");
  if (!$stmt) {
    jsonOut(false, 'Prepare delete gagal: ' . $conn->error);
  }
  $stmt->bind_param("is", $id, $group);

  if (!$stmt->execute()) {
    $err = $stmt->error ?: 'Gagal hapus.';
    $stmt->close();
    jsonOut(false, $err);
  }
  $stmt->close();

  jsonOut(true, 'Opsi berhasil dihapus.', getItems($conn, $group));
}

jsonOut(false, 'Action tidak valid.');