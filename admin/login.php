<?php
require_once __DIR__ . "/../config.php";

// kalau sudah login sebagai admin / akademik, langsung redirect sesuai role
if (!empty($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit;
  }

  if ($_SESSION['role'] === 'akademik') {
    header("Location: ../akademik/dashboard.php");
    exit;
  }
}

// pesan dari redirect
$pesan = trim($_GET['pesan'] ?? '');
$tipe  = trim($_GET['tipe'] ?? 'info'); // info | error | success
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin & Akademik</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/css_admin/login.css">
</head>
<body>

  <!-- Toast -->
  <div class="toast" id="toast" aria-hidden="true">
    <div class="toast-card" id="toastCard">
      <div class="toast-title" id="toastTitle">Info</div>
      <div class="toast-msg" id="toastMsg">-</div>
    </div>
  </div>

  <main class="login-page">
    <section class="login-card">

      <!-- kiri -->
      <div class="login-left">
        <div class="brand-chip">Sistem Penilaian Mahasiswa</div>

        <h1 class="login-title">Masuk ke Sistem</h1>
        <p class="login-subtitle">
          Login untuk mengakses panel <strong>Admin</strong> atau <strong>Akademik</strong>.
        </p>

        <form method="post" action="proses_login.php" class="login-form" autocomplete="off" id="formLogin">
          <div class="field">
            <label for="username" class="field-label">Username</label>
            <input
              type="text"
              name="username"
              id="username"
              class="field-input"
              placeholder="Masukkan username"
              required
            >
          </div>

          <div class="field">
            <label for="password" class="field-label">Password</label>
            <div class="password-box">
              <input
                type="password"
                name="password"
                id="password"
                class="field-input"
                placeholder="Masukkan password"
                required
              >
              <button class="toggle-pass" type="button" id="togglePass" aria-label="Tampilkan password">
                <svg class="ico-eye" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                  <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                </svg>

                <svg class="ico-eyeoff" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <path d="M10.6 10.6A2.9 2.9 0 0 0 9 12c0 1.7 1.3 3 3 3 .5 0 1-.1 1.4-.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <path d="M6.2 6.2C3.9 8 2 12 2 12s3.5 7 10 7c2.2 0 4.1-.6 5.7-1.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                  <path d="M9.9 5.2C10.6 5.1 11.3 5 12 5c6.5 0 10 7 10 7s-1.1 2.1-3.2 4.1" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-login" id="btnMasuk">MASUK</button>

          <div class="login-note">
            Akses yang didukung saat ini: <strong>Admin</strong> dan <strong>Akademik</strong>
          </div>

          <div class="login-footer">
            Copyright © 2026 Unfari Jabar
          </div>
        </form>
      </div>

      <!-- kanan -->
      <div class="login-right" aria-hidden="true">
        <div class="overlay"></div>
        <div class="right-content">
          <div class="campus-badge">UNFARI</div>
          <h2 class="right-title">Kampus Ghifari</h2>
          <p class="right-text">
            Sistem informasi akademik untuk pengelolaan data mahasiswa, akun pengguna, dan proses penilaian.
          </p>
        </div>
      </div>

    </section>
  </main>

  <script>
    window.__FLASH__ = <?= json_encode([
      "tipe" => $tipe,
      "pesan" => $pesan
    ], JSON_UNESCAPED_UNICODE); ?>;
  </script>

  <script src="../js/js_admin/login.js"></script>
</body>
</html>