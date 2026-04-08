<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Silakan login."));
  exit;
}

$nama = $_SESSION['nama'] ?? 'Mahasiswa';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Logout</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/css_mahasiswa/logout.css">
</head>
<body>

  <!-- OVERLAY + MODAL -->
  <div class="logout-overlay" id="logoutOverlay" aria-hidden="false">
    <div class="logout-card" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">

      <div class="logout-ic" aria-hidden="true">
        <!-- icon logout/keluar -->
        <svg viewBox="0 0 24 24" fill="none">
          <path d="M10 7V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-1"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M6 9l-3 3 3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <div class="logout-title" id="logoutTitle">Logout</div>
      <div class="logout-msg">
        Halo <b><?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?></b>, kamu yakin ingin keluar dari sistem?
      </div>

      <div class="logout-actions">
        <a class="btn" href="dashboard.php" id="btnCancel">Batal</a>
        <a class="btn danger" href="logout_proses.php" id="btnOk">Ya, Logout</a>
      </div>

    </div>
  </div>

  <script src="../js/js_mahasiswa/logout.js"></script>
</body>
</html>