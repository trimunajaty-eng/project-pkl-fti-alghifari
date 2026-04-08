<?php
require_once __DIR__ . "/../../config.php";

// wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
  exit;
}

$id_user = (int)($_GET['id_user'] ?? 0);
$id_mahasiswa = (int)($_GET['id_mahasiswa'] ?? 0);

if ($id_user <= 0 || $id_mahasiswa <= 0) {
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Parameter tidak valid."));
  exit;
}

$conn->begin_transaction();

try {
  // pastikan user role mahasiswa (aman)
  $stC = $conn->prepare("SELECT role FROM users WHERE id_user=? LIMIT 1");
  $stC->bind_param("i", $id_user);
  $stC->execute();
  $rsC = $stC->get_result();
  $role = '';
  if ($rsC && ($row = $rsC->fetch_assoc())) $role = $row['role'] ?? '';
  $stC->close();

  if ($role !== 'mahasiswa') {
    throw new Exception("User bukan mahasiswa.");
  }

  // hapus user
  $stD = $conn->prepare("DELETE FROM users WHERE id_user=? LIMIT 1");
  $stD->bind_param("i", $id_user);
  if (!$stD->execute()) {
    $stD->close();
    throw new Exception("Gagal hapus user.");
  }
  $stD->close();

  // reset mahasiswa
  // (kalau kolom ada, set null)
  $sqlM = "UPDATE mahasiswa
           SET akun_dicetak=0, akun_dicetak_pada=NULL, id_user=NULL
           WHERE id_mahasiswa=? LIMIT 1";
  $stM = $conn->prepare($sqlM);
  $stM->bind_param("i", $id_mahasiswa);
  if (!$stM->execute()) {
    $stM->close();
    throw new Exception("Gagal reset mahasiswa.");
  }
  $stM->close();

  $conn->commit();
  header("Location: dataakun.php?tipe=success&pesan=" . urlencode("Akun berhasil dihapus."));
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  header("Location: dataakun.php?tipe=error&pesan=" . urlencode("Gagal hapus: " . $e->getMessage()));
  exit;
}