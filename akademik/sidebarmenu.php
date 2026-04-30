<?php
if (!isset($currentPage)) {
  $currentPage = '';
}

if (!isset($baseUrl)) {
  $baseUrl = '';
}
?>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-inner">
    <div class="sidebar-top">
      <div class="brand">
        <div class="brand-icon">AK</div>
        <div class="brand-text">
          <div class="brand-title">Akademik</div>
          <div class="brand-subtitle">Universitas Al-Ghifari</div>
        </div>
      </div>

      <nav class="menu">
        <a href="<?= $baseUrl; ?>dashboard.php" class="menu-item <?= $currentPage === 'dashboard' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M4 13h7V4H4v9Zm0 7h7v-5H4v5Zm9 0h7v-9h-7v9Zm0-16v5h7V4h-7Z"/>
            </svg>
          </span>
          <span class="menu-text">Dashboard</span>
        </a>

        <a href="<?= $baseUrl; ?>nilai/inputnilai.php" class="menu-item <?= $currentPage === 'inputnilai' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Zm1 7V4.5L19.5 10H15Zm-5 7h4m-4-4h6"/>
            </svg>
          </span>
          <span class="menu-text">Input Nilai</span>
        </a>

        <!-- Ganti bagian ini di sidebarmenu.php -->
        <a href="<?= $baseUrl; ?>mahasiswa/inputmahasiswa.php" class="menu-item <?= $currentPage === 'inputmhs' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m8-9a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm9 4v-3a3 3 0 0 0-3-3h-2m5 0a3 3 0 0 1 3 3v3m-6-6h4"/>
            </svg>
          </span>
          <span class="menu-text">Input Mahasiswa</span>
        </a>

        <a href="#" class="menu-item <?= $currentPage === 'laporan' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M5 19V9m7 10V5m7 14v-7"/>
            </svg>
          </span>
          <span class="menu-text">Laporan</span>
        </a>
      </nav>
    </div>

    <div class="sidebar-bottom">
      <a href="<?= $baseUrl; ?>logout.php" class="menu-item menu-item-danger">
        <span class="menu-icon">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9"/>
          </svg>
        </span>
        <span class="menu-text">Logout</span>
      </a>
    </div>
  </div>
</aside>