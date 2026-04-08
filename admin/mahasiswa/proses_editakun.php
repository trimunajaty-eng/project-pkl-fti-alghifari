<?php
require_once __DIR__ . "/../../config.php";

// wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Metode tidak valid."));
  exit;
}

$id = (int)($_POST['id'] ?? 0);
$id_user = (int)($_POST['id_user'] ?? 0);

$status = trim($_POST['status'] ?? '');
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');

$password_baru = (string)($_POST['password_baru'] ?? '');
$reset_ke_nim = (int)($_POST['reset_ke_nim'] ?? 0);

if ($id <= 0 || $id_user <= 0) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("ID tidak valid."));
  exit;
}

if ($status !== 'aktif' && $status !== 'nonaktif') {
  header("Location: editakun.php?id=".$id."&tipe=error&pesan=" . urlencode("Status tidak valid."));
  exit;
}

if ($nama_lengkap === '') {
  header("Location: editakun.php?id=".$id."&tipe=error&pesan=" . urlencode("Nama lengkap wajib diisi."));
  exit;
}

// pastikan record mahasiswa dicetak & user cocok
$sqlCek = "SELECT m.id_mahasiswa, m.nim,
                  u.id_user, u.role
           FROM mahasiswa m
           LEFT JOIN users u
             ON u.id_user = ?
           WHERE m.id_mahasiswa=?
             AND IFNULL(m.akun_dicetak,0)=1
           LIMIT 1";
$stC = $conn->prepare($sqlCek);
if (!$stC) {
  header("Location: editakun.php?id=".$id."&tipe=error&pesan=" . urlencode("Prepare cek gagal: ".$conn->error));
  exit;
}
$stC->bind_param("ii", $id_user, $id);
$stC->execute();
$rsC = $stC->get_result();
$cek = ($rsC && $rsC->num_rows === 1) ? $rsC->fetch_assoc() : null;
$stC->close();

if (!$cek) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Data akun tidak ditemukan."));
  exit;
}

if (($cek['role'] ?? '') !== 'mahasiswa') {
  header("Location: editakun.php?id=".$id."&tipe=error&pesan=" . urlencode("User bukan role mahasiswa."));
  exit;
}

$nim = (string)($cek['nim'] ?? '');
if ($nim === '') {
  header("Location: editakun.php?id=".$id."&tipe=error&pesan=" . urlencode("NIM kosong."));
  exit;
}

$updatePassword = false;
$newHash = null;

if ($reset_ke_nim === 1) {
  $updatePassword = true;
  $newHash = password_hash($nim, PASSWORD_BCRYPT);
} else {
  $password_baru = trim($password_baru);
  if ($password_baru !== '') {
    $updatePassword = true;
    $newHash = password_hash($password_baru, PASSWORD_BCRYPT);
  }
}

$conn->begin_transaction();
try {
  if ($updatePassword) {
    $stU = $conn->prepare("UPDATE users
                           SET nama_lengkap=?, status=?, password_hash=?
                           WHERE id_user=? AND role='mahasiswa'
                           LIMIT 1");
    if (!$stU) throw new Exception("Prepare update user gagal: ".$conn->error);
    $stU->bind_param("sssi", $nama_lengkap, $status, $newHash, $id_user);
  } else {
    $stU = $conn->prepare("UPDATE users
                           SET nama_lengkap=?, status=?
                           WHERE id_user=? AND role='mahasiswa'
                           LIMIT 1");
    if (!$stU) throw new Exception("Prepare update user gagal: ".$conn->error);
    $stU->bind_param("ssi", $nama_lengkap, $status, $id_user);
  }

  if (!$stU->execute()) {
    $err = $stU->error ?: "Execute gagal";
    $stU->close();
    throw new Exception("Gagal update user: ".$err);
  }
  $stU->close();

  $conn->commit();

  header("Location: editakun.php?id=".$id."&tipe=success&pesan=" . urlencode("Akun berhasil diperbarui."));
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  header("Location: editakun.php?id=".$id."&tipe=error&pesan=" . urlencode($e->getMessage()));
  exit;
}