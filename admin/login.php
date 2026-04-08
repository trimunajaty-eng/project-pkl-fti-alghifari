<?php
require_once __DIR__ . "/../config.php";

// kalau sudah login admin, langsung ke dashboard
if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
  header("Location: dashboard.php");
  exit;
}

// pesan dari redirect (logout / error)
$pesan = trim($_GET['pesan'] ?? '');
$tipe  = trim($_GET['tipe'] ?? 'info'); // info | error | success
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/css_admin/login.css">
  
</head>
<body>

  <!-- Toast (pojok kanan atas) -->
  <div class="toast" id="toast" aria-hidden="true">
    <div class="toast-card" id="toastCard">
      <div class="toast-title" id="toastTitle">Info</div>
      <div class="toast-msg" id="toastMsg">-</div>
    </div>
  </div>

  <main class="auth">
    <section class="auth-card">

      <!-- Kiri: Form -->
      <div class="pane left">
        <div class="left-inner">
          <h1 class="title">Sign In</h1>
          <p class="subtitle">Masuk sebagai Admin</p>

          <form method="post" action="proses_login.php" class="form" autocomplete="off" id="formLogin">
            <label class="field">
              <span class="label">Username</span>
              <input type="text" name="username" id="username" placeholder="Masukkan username" required>
            </label>

            <label class="field">
              <span class="label">Password</span>
              <div class="pass-wrap">
                <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                <button class="eye-btn" type="button" id="togglePass" aria-label="Tampilkan password">
                  <!-- eye (default) -->
                  <svg class="ico-eye" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                  </svg>
                  <!-- eye-off -->
                  <svg class="ico-eyeoff" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M10.6 10.6A2.9 2.9 0 0 0 9 12c0 1.7 1.3 3 3 3 .5 0 1-.1 1.4-.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M6.2 6.2C3.9 8 2 12 2 12s3.5 7 10 7c2.2 0 4.1-.6 5.7-1.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M9.9 5.2C10.6 5.1 11.3 5 12 5c6.5 0 10 7 10 7s-1.1 2.1-3.2 4.1" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                  </svg>
                </button>
              </div>
            </label>

            <button type="submit" class="btn" id="btnMasuk">SIGN IN</button>

            <div class="footnote">Copyright ©2026 Unfari Jabar </div>
          </form>
        </div>
      </div>

      <!-- Kanan: Panel warna merah-kuning -->
      <div class="pane right" aria-hidden="true">
        <div class="right-inner">
          <div class="right-title">Halo, Admin!</div>
          <div class="right-sub">
            Silakan masuk untuk mengelola sistem.
          </div>
        </div>
      </div>

    </section>
  </main>

  <!-- data pesan untuk toast -->
  <script>
    window.__FLASH__ = <?= json_encode([
      "tipe" => $tipe,
      "pesan" => $pesan
    ], JSON_UNESCAPED_UNICODE); ?>;
  </script>

  <script src="../js/js_admin/login.js"></script>
</body>
</html>
