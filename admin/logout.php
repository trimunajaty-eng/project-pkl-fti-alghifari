<?php
require_once __DIR__ . "/../config.php";

// kalau belum login admin, langsung ke login
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php?tipe=info&pesan=" . urlencode("Kamu sudah keluar."));
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Keluar...</title>

  <!-- ✅ ganti ke css admin -->
  <link rel="stylesheet" href="../css/css_admin/logout.css">
</head>
<body>
  <div class="box">
    <div class="spinner" aria-hidden="true"></div>
    <div class="title">Sedang keluar...</div>
    <div class="sub">Mohon tunggu sebentar</div>
  </div>

  <!-- ✅ ganti ke js admin -->
  <script src="../js/js_admin/logout.js"></script>
</body>
</html>
