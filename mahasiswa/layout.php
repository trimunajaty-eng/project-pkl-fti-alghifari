<?php
// mahasiswa/layout.php
if (session_status() === PHP_SESSION_NONE) session_start();

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function initials($name){
  $name = trim((string)$name);
  if ($name === '') return 'M';
  $parts = preg_split('/\s+/', $name);
  $a = strtoupper(substr($parts[0] ?? 'M', 0, 1));
  $b = strtoupper(substr($parts[1] ?? '', 0, 1));
  return $a . ($b ?: '');
}

function renderMahasiswaLayoutStart(array $opt = []){
  $title      = $opt['title'] ?? 'Mahasiswa - Dashboard';
  $page_title = $opt['page_title'] ?? 'Dashboard';
  $page_sub   = $opt['page_sub'] ?? '';
  $menu       = $opt['menu'] ?? 'dashboard';

  $nama_tampil = $opt['nama_tampil'] ?? ($_SESSION['nama'] ?? 'Mahasiswa');
  $username    = $opt['username'] ?? ($_SESSION['username'] ?? '');
  $assetsBase  = $opt['assetsBase'] ?? '..';
  $basePath    = $opt['basePath'] ?? '';

  // flash toast (opsional)
  $toastType = '';
  $toastMsg  = '';
  if (!empty($_SESSION['flash_success'])) {
    $toastType = 'success';
    $toastMsg  = (string)$_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
  } elseif (!empty($_SESSION['flash_error'])) {
    $toastType = 'error';
    $toastMsg  = (string)$_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
  }

  $is_blocked  = !empty($opt['is_blocked']);
  $blocked_msg = $opt['blocked_msg'] ?? '';

  $ava = initials($nama_tampil);
  ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($title) ?></title>
  <link rel="icon" type="image/png" href="<?= e($assetsBase) ?>/img/foto/logosia.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= e($assetsBase) ?>/css/css_mahasiswa/dashboard.css">
</head>
<body>

<div id="app" class="app"
     data-toast-type="<?= e($toastType) ?>"
     data-toast-msg="<?= e($toastMsg) ?>"
     data-login="<?= e($_GET['login'] ?? '') ?>"
     data-account-blocked="<?= $is_blocked ? '1' : '0' ?>"
     data-blocked-msg="<?= e($blocked_msg) ?>">

  <!-- SIDEBAR -->
  <aside class="sidebar" aria-label="Sidebar Mahasiswa">
    <div class="brand">
      <img class="brand-logo" src="<?= e($assetsBase) ?>/img/logo.png" alt="Logo" onerror="this.style.display='none'">
      <div class="brand-text">
        <div class="brand-name">
          <span class="brand-full">SIA+ STMIK JABAR</span>
          <span class="brand-mini">SIA+</span>
        </div>
        <div class="brand-sub">Portal Mahasiswa</div>
      </div>
    </div>

    <nav class="nav" id="nav">
      <!-- SCROLL AREA (MENU) -->
      <div class="nav-scroll">
        <div class="nav-group">
          <div class="nav-title">MENU</div>

          <a class="nav-item <?= $menu==='dashboard'?'active':'' ?>" href="<?= e($basePath) ?>dashboard.php">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 11l8-7 8 7v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M9 22v-8h6v8" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
              </svg>
            </span>
            <span class="tx">Dashboard</span>
          </a>

          <a class="nav-item <?= $menu==='profil'?'active':'' ?>" href="<?= e($basePath) ?>profile.php">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8"/>
              </svg>
            </span>
            <span class="tx">Data Profil</span>
          </a>

          <a class="nav-item <?= $menu==='absen'?'active':'' ?>" href="<?= e($basePath) ?>dashboard.php?menu=absen">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M8 7h8M8 12h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8"/>
              </svg>
            </span>
            <span class="tx">Daftar Hadir</span>
          </a>

          <a class="nav-item <?= $menu==='jadwal'?'active':'' ?>" href="<?= e($basePath) ?>dashboard.php?menu=jadwal">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M8 2v3M16 2v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M3 9h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M5 5h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8"/>
              </svg>
            </span>
            <span class="tx">Jadwal</span>
          </a>

          <a class="nav-item <?= $menu==='krs'?'active':'' ?>" href="<?= e($basePath) ?>dashboard.php?menu=krs">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M6 4h12v16H6z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
            </span>
            <span class="tx">KRS</span>
          </a>

          <a class="nav-item <?= $menu==='nilai'?'active':'' ?>" href="<?= e($basePath) ?>dashboard.php?menu=nilai">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M20 19H4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M8 15l3-3 2 2 5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            <span class="tx">Nilai</span>
          </a>

          <a class="nav-item <?= $menu==='transkrip'?'active':'' ?>" href="<?= e($basePath) ?>dashboard.php?menu=transkrip">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M7 3h10v18H7z" stroke="currentColor" stroke-width="1.8"/>
                <path d="M9 7h6M9 11h6M9 15h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
            </span>
            <span class="tx">Transkrip</span>
          </a>

        </div>
      </div>

      <!-- BOTTOM AREA (LOGOUT + WHO) -->
      <div class="nav-bottom">
        <a class="nav-item danger" href="<?= e($basePath) ?>logout.php">
          <span class="ic">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M10 7V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              <path d="M15 12H3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              <path d="M6 9l-3 3 3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <span class="tx">Logout</span>
        </a>

        <div class="who">
          <div class="who-ava"><?= e($ava) ?></div>
          <div class="who-txt">
            <div class="who-name"><?= e($nama_tampil) ?></div>
            <div class="who-role"><?= e($username) ?></div>
          </div>
        </div>
      </div>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <header class="topbar">
      <div class="top-left">
        <button class="burger" id="btnBurger" aria-label="Toggle sidebar" type="button">
          <span></span><span></span><span></span>
        </button>

        <div class="top-title">
          <div class="t1"><?= e($page_title) ?></div>
          <div class="t2"><?= e($page_sub) ?></div>
        </div>
      </div>

      <div class="top-actions">

        <!-- Bell -->
        <div class="drop" id="bellWrap">
          <button class="iconbtn" id="btnBell" aria-label="Notifikasi" type="button">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 7h18s-3 0-3-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              <path d="M13.73 21a2 2 0 01-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </button>

          <div class="dd" id="bellDrop" aria-hidden="true">
            <div class="dd-title">Notifikasi</div>
            <div class="dd-sub">Belum ada notifikasi.</div>
          </div>
        </div>

        <!-- Profile -->
        <div class="profile drop" id="profileWrap">
          <button class="miniava" id="btnProfile" aria-label="Profil" type="button"><?= e($ava) ?></button>

          <div class="dd pdrop" id="profileDrop" aria-hidden="true">
            <div class="phead">
              <div class="pava"><?= e($ava) ?></div>
              <div>
                <div class="pn"><?= e($nama_tampil) ?></div>
                <div class="pr">Mahasiswa • <?= e($username) ?></div>
              </div>
            </div>
            <div class="pbody">
              <a class="pitem" href="<?= e($basePath) ?>settings.php">Pengaturan</a>

            </div>
          </div>
        </div>

      </div>
    </header>

    <div class="overlay" id="overlay"></div>

    <!-- LOADER -->
    <div class="page-loader" id="pageLoader" aria-hidden="true">
      <div class="page-loader-card">
        <div class="spinner"></div>
        <div class="loader-title">Memuat dashboard...</div>
        <div class="loader-sub">Mohon tunggu sebentar</div>
      </div>
    </div>

    <!-- TOAST -->
    <div class="toast" id="toast" aria-hidden="true">
      <div class="toast-ic" id="toastIcon">✓</div>
      <div class="toast-tx">
        <div class="toast-title" id="toastTitle">Berhasil</div>
        <div class="toast-msg" id="toastMsg">Login berhasil</div>
      </div>
      <button class="toast-x" id="toastClose" aria-label="Tutup" type="button">✕</button>
    </div>

    <!-- MODAL BLOKIR AKUN -->
    <div class="blocker" id="blocker" aria-hidden="true">
      <div class="blocker-card" role="dialog" aria-modal="true" aria-labelledby="blockerTitle">
        <div class="blocker-ic">!</div>
        <div class="blocker-title" id="blockerTitle">Akun Dinonaktifkan</div>
        <div class="blocker-msg" id="blockerMsg">Akun kamu dinonaktifkan oleh admin.</div>
        <div class="blocker-actions">
          <a class="blocker-btn" id="blockerBtn" href="../login.php">Kembali ke Login</a>
        </div>
      </div>
    </div>

    <section class="content">
<?php
}

function renderMahasiswaLayoutEnd(array $opt = []){
  $assetsBase = $opt['assetsBase'] ?? '..';
  ?>
    </section>
  </main>
</div>

<script src="<?= e($assetsBase) ?>/js/js_mahasiswa/dashboard.js"></script>
</body>
</html>
<?php
}