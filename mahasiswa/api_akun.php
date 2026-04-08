<?php
require_once __DIR__ . "/../config.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  echo json_encode([
    "ok" => false,
    "message" => "Unauthorized"
  ]);
  exit;
}

$nim = $_SESSION['username'] ?? '';
if ($nim === '') {
  echo json_encode([
    "ok" => false,
    "message" => "NIM kosong"
  ]);
  exit;
}

$action = $_GET['action'] ?? 'status';

if ($action === 'status') {
  $status_user = 'aktif';

  $st = $conn->prepare("SELECT status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
  if ($st) {
    $st->bind_param("s", $nim);
    $st->execute();
    $rs = $st->get_result();
    if ($rs && ($row = $rs->fetch_assoc())) {
      $status_user = $row['status'] ?? 'aktif';
    }
    $st->close();
  }

  echo json_encode([
    "ok"     => true,
    "status" => ($status_user === 'nonaktif' ? 'nonaktif' : 'aktif')
  ]);
  exit;
}

if ($action === 'ringkasan') {
  $data = [
    "ok" => true,
    "kehadiran" => 0,
    "sks" => 0,
    "ipk" => "0.00",
    "nilai" => 0
  ];

  $st = $conn->prepare("SELECT sks_diakui FROM mahasiswa WHERE nim=? LIMIT 1");
  if ($st) {
    $st->bind_param("s", $nim);
    $st->execute();
    $rs = $st->get_result();
    if ($rs && ($row = $rs->fetch_assoc())) {
      $data["sks"] = (int)($row['sks_diakui'] ?? 0);
    }
    $st->close();
  }

  echo json_encode($data);
  exit;
}

echo json_encode([
  "ok" => false,
  "message" => "Aksi tidak dikenal"
]);
exit;