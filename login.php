<?php
require_once "config.php";

if (isset($_SESSION['role']) && $_SESSION['role'] == "mahasiswa") {
    header("Location: mahasiswa/dashboard.php");
    exit;
}

// Ambil pesan dari URL (untuk logout/login error)
$pesan = trim($_GET['pesan'] ?? "");
$tipe  = trim($_GET['tipe'] ?? ""); // success | error | info
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login Mahasiswa | SIA+</title>
  <link rel="icon" type="image/png" href="img/foto/logosia.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/login.css?v=3">
</head>
<body class="login-body">

  <!-- LOADER -->
  <div id="loader">
    <div class="loader-box">
      <div class="spinner"></div>
      <p>Memuat halaman...</p>
    </div>
  </div>

  <!-- LOGIN -->
  <div class="login-wrapper">
    <div class="login-box animate-on-load">

      <div class="login-header">
        <div class="logo-circle animate-scale-in" style="animation-delay: 0.1s">SIA+</div>
        <h1 class="animate-slide-up" style="animation-delay: 0.2s">SIA+ STMIK JABAR</h1>
        <p class="animate-slide-up" style="animation-delay: 0.3s">Sign In Mahasiswa</p>
      </div>

      <!-- ALERT / FLASH MESSAGE -->
      <?php if ($pesan): ?>
      <div 
        class="alert alert-<?= $tipe === 'success' ? 'success' : ($tipe === 'info' ? 'info' : 'error') ?> animate-slide-up" 
        style="animation-delay: 0.35s"
        role="alert"
        aria-live="polite"
      >
        <span class="alert-icon"><?= $tipe === 'success' ? '✓' : ($tipe === 'info' ? 'ℹ' : '✗') ?></span>
        <span class="alert-text"><?= htmlspecialchars($pesan) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'" aria-label="Tutup">×</button>
      </div>
      <?php endif; ?>

      <form method="POST" action="proses_login.php" class="login-form">
        
        <div class="input-group animate-slide-up" style="animation-delay: 0.4s">
          <label for="username">NIM</label>
          <input type="text" name="username" id="username" placeholder="Masukkan NIM" required autocomplete="username">
        </div>

        <div class="input-group animate-slide-up" style="animation-delay: 0.5s">
          <label for="password">Password</label>
          <div class="password-field">
            <input type="password" name="password" id="password" placeholder="Masukkan password" required autocomplete="current-password">
            <button type="button" id="togglePassword" class="eye-btn" aria-label="Tampilkan password">
              <svg class="ico-eye" viewBox="0 0 24 24" fill="none">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
              </svg>
              <svg class="ico-eyeoff" viewBox="0 0 24 24" fill="none">
                <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M10.6 10.6A2.9 2.9 0 0 0 9 12c0 1.7 1.3 3 3 3 .5 0 1-.1 1.4-.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M6.2 6.2C3.9 8 2 12 2 12s3.5 7 10 7c2.2 0 4.1-.6 5.7-1.5" stroke="currentColor" stroke-width="1.8"/>
                <path d="M9.9 5.2C10.6 5.1 11.3 5 12 5c6.5 0 10 7 10 7" stroke="currentColor" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <button class="login-btn animate-slide-up" id="loginBtn" style="animation-delay: 0.6s" type="submit">
          <span class="btn-loader"></span>
          <span class="text">Masuk</span>
        </button>

      </form>

      <div class="login-footer animate-fade-in" style="animation-delay: 0.7s">
        © <?= date("Y") ?> STMIK JABAR
      </div>

    </div>
  </div>

  <!-- Pass flash data to JS -->
  <script>
    window.__FLASH__ = {
      tipe: <?= json_encode($tipe) ?>,
      pesan: <?= json_encode($pesan) ?>
    };
  </script>
  <script src="js/login.js?v=3"></script>
</body>
</html>