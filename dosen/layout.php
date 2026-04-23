<?php
require_once __DIR__ . "/../config.php";

if (!function_exists('e')) {
  function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
  }
}

function renderDosenLayoutStart(array $opts = [])
{
  if (empty($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai dosen."));
    exit;
  }

  $title       = $opts['title'] ?? 'Dashboard Dosen';
    $pageTitle   = $opts['page_title'] ?? 'Dashboard';
    $pageSub     = $opts['page_sub'] ?? 'Panel dosen';
    $namaTampil  = $opts['nama_tampil'] ?? ($_SESSION['nama_lengkap'] ?? 'Dosen');
    $username    = $opts['username'] ?? ($_SESSION['username'] ?? '-');
    $assetsBase  = $opts['assetsBase'] ?? '..';
    $menu        = $opts['menu'] ?? 'dashboard';
  ?>
  <!doctype html>
  <html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= e($assetsBase) ?>/css/css_dosen/dashboard.css?v=1">
  </head>
  <body>
    <!-- loading -->
    <div class="dosen-loader" id="dosenLoader" aria-hidden="false">
      <div class="dosen-loader-box">
        <div class="spinner-ring"></div>
        <div class="loader-title">Memuat Dashboard Dosen</div>
        <div class="loader-sub">Mohon tunggu sebentar...</div>
      </div>
    </div>

    <div class="dosen-shell" id="dosenApp">
      <aside class="dosen-sidebar">
        <div class="brand-box">
          <div class="brand-logo">DS</div>
          <div>
            <div class="brand-title">Panel Dosen</div>
            <div class="brand-sub">Universitas Al-Ghifari</div>
          </div>
        </div>

        <nav class="dosen-menu">
        <!-- === PERUBAHAN MENU DOSEN MULAI === -->
        <a href="dashboard.php" class="menu-link <?= (($opts['menu'] ?? 'dashboard') === 'dashboard') ? 'active' : '' ?>">Dashboard</a>
        <a href="nilai.php" class="menu-link <?= (($opts['menu'] ?? '') === 'nilai') ? 'active' : '' ?>">Nilai Mahasiswa</a>
        <a href="logout.php" class="menu-link danger">Logout</a>
        <!-- === PERUBAHAN MENU DOSEN SELESAI === -->
        </nav>
      </aside>

      <main class="dosen-main">
        <header class="dosen-topbar">
          <div>
            <div class="page-title"><?= e($pageTitle) ?></div>
            <div class="page-sub"><?= e($pageSub) ?></div>
          </div>

          <div class="user-chip">
            <div class="user-avatar"><?= e(strtoupper(substr($namaTampil, 0, 1))) ?></div>
            <div>
              <div class="user-name"><?= e($namaTampil) ?></div>
              <div class="user-id"><?= e($username) ?></div>
            </div>
          </div>
        </header>

        <section class="dosen-content">
  <?php
}

function renderDosenLayoutEnd(array $opts = [])
{
  $assetsBase = $opts['assetsBase'] ?? '..';
  ?>
        </section>
      </main>
    </div>

    <script src="<?= e($assetsBase) ?>/js/js_dosen/dashboard.js?v=1"></script>
  </body>
  </html>
  <?php
}