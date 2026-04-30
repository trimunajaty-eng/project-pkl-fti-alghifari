<?php
require_once __DIR__ . "/../../config.php";
header('Content-Type: application/json; charset=utf-8');

// Wajib login akademik
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  echo json_encode(["ok" => false, "message" => "Unauthorized"]);
  exit;
}

function out($ok, $message = "", $nim = ""){
  echo json_encode(["ok"=>$ok, "message"=>$message, "nim"=>$nim]);
  exit;
}

$program_studi = trim($_POST['program_studi'] ?? '');
$tanggal_registrasi = trim($_POST['tanggal_registrasi'] ?? '');

if ($program_studi === '') out(false, "Program Studi wajib dipilih.");
if ($tanggal_registrasi === '') out(false, "Tanggal Registrasi wajib diisi.");

// FIX: strtotime bukan strtime!
$ts = strtotime($tanggal_registrasi);
if (!$ts) out(false, "Tanggal Registrasi tidak valid.");

$year = (int)date('Y', $ts);
$yy = substr((string)$year, -2);

// Ambil kode program studi dari master dropdown
$stmtProdi = $conn->prepare("
  SELECT kode_ref, kode_nim
  FROM master_opsi_dropdown
  WHERE grup='program_studi' AND value=? AND is_active=1
  LIMIT 1
");

if (!$stmtProdi) {
  out(false, "Prepare program studi gagal: " . $conn->error);
}

$stmtProdi->bind_param("s", $program_studi);
$stmtProdi->execute();
$rsProdi = $stmtProdi->get_result();
$rowProdi = $rsProdi ? $rsProdi->fetch_assoc() : null;
$stmtProdi->close();

if (!$rowProdi) {
  out(false, "Program studi tidak ditemukan di master dropdown.");
}

$kodeRef = trim((string)($rowProdi['kode_ref'] ?? ''));
$kodeNim = trim((string)($rowProdi['kode_nim'] ?? ''));

if ($kodeRef === '' || $kodeNim === '') {
  out(false, "Kode awalan atau kode NIM program studi belum diset. Silakan hubungi Admin.");
}

// Hitung urutan per prodi
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM mahasiswa WHERE program_studi = ?");
$stmt->bind_param("s", $program_studi);
$stmt->execute();
$rs = $stmt->get_result();
$total = 0;
if ($rs && ($row = $rs->fetch_assoc())) {
  $total = (int)$row['total'];
}
$stmt->close();

$seq = $total + 1;

// Anti duplikat - coba generate NIM unik
for ($i = 0; $i < 5000; $i++){
  $urut = str_pad((string)$seq, 2, "0", STR_PAD_LEFT);
  $nim = $kodeRef . $yy . $kodeNim . $urut;

  $cek = $conn->prepare("SELECT 1 FROM mahasiswa WHERE nim = ? LIMIT 1");
  $cek->bind_param("s", $nim);
  $cek->execute();
  $cekRs = $cek->get_result();
  $exists = ($cekRs && $cekRs->num_rows > 0);
  $cek->close();

  if (!$exists) {
    out(true, "NIM berhasil dibuat.", $nim);
  }
  $seq++;
}

out(false, "Gagal membuat NIM (terlalu banyak konflik).");