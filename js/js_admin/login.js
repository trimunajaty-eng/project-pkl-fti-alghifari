<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login terlebih dahulu."));
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Akademik</title>
</head>
<body>
  <h1>Dashboard Akademik</h1>
  <p>Halo, <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></p>
</body>
</html>