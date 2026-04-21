<?php
if (!isset($currentPage)) {
  $currentPage = '';
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
        <a href="dashboard.php" class="menu-item <?= $currentPage === 'dashboard' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M4 13h7V4H4v9Zm0 7h7v-5H4v5Zm9 0h7v-9h-7v9Zm0-16v5h7V4h-7Z"/>
            </svg>
          </span>
          <span class="menu-text">Dashboard</span>
        </a>

        <a href="nilai/inputnilai.php" class="menu-item <?= $currentPage === 'inputnilai' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Zm1 7V4.5L19.5 10H15Zm-5 7h4m-4-4h6"/>
            </svg>
          </span>
          <span class="menu-text">Input Nilai</span>
        </a>

        <a href="#" class="menu-item <?= $currentPage === 'datanilai' ? 'active' : ''; ?>">
          <span class="menu-icon">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M4 5h16M4 10h16M4 15h10M4 20h16"/>
            </svg>
          </span>
          <span class="menu-text">Data Nilai</span>
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
      <a href="logout.php" class="menu-item menu-item-danger">
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