<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
  header("Location: ../admin/login.php");
  exit;
}

$nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Dosen';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Logout Dosen</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/css_dosen/dashboard.css?v=1">
</head>
<body class="logout-page">
  <div class="logout-card">
    <div class="logout-title">Keluar dari akun dosen?</div>
    <div class="logout-sub">
      Kamu login sebagai <strong><?= htmlspecialchars($nama) ?></strong>.
    </div>

    <div class="logout-actions">
      <a href="dashboard.php" class="btn-action btn-light">Batal</a>
      <a href="logout_proses.php" class="btn-action btn-danger">Ya, Logout</a>
    </div>
  </div>
</body>
</html>