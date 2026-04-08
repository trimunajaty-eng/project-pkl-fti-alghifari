<?php
// admin/layout.php
// layout helper untuk semua halaman admin (sidebar + topbar + wrapper)

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function isPrefix($menu, $prefix){ return strpos($menu, $prefix) === 0; }

function icon($name){
  if ($name === 'dash') return '<svg viewBox="0 0 24 24" fill="none"><path d="M4 13h7v7H4v-7Zm9-9h7v9h-7V4ZM4 4h7v7H4V4Zm9 11h7v5h-7v-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
  if ($name === 'mhs')  return '<svg viewBox="0 0 24 24" fill="none"><path d="M12 3 22 8 12 13 2 8 12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 12v6c0 1 2.7 3 6 3s6-2 6-3v-6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
  if ($name === 'dsn')  return '<svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="1.8"/><path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
  if ($name === 'wallet') return '<svg viewBox="0 0 24 24" fill="none"><path d="M4 8h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M18 12h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
  if ($name === 'layers') return '<svg viewBox="0 0 24 24" fill="none"><path d="M12 3 22 8 12 13 2 8 12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 16.5l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
  if ($name === 'users') return '<svg viewBox="0 0 24 24" fill="none"><path d="M16 11a3.5 3.5 0 1 0-3.5-3.5A3.5 3.5 0 0 0 16 11Z" stroke="currentColor" stroke-width="1.8"/><path d="M3 21a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
  if ($name === 'calendar') return '<svg viewBox="0 0 24 24" fill="none"><path d="M7 3v3M17 3v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 7h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M5 6.5h14A2 2 0 0 1 21 8.5v12A2 2 0 0 1 19 22H5a2 2 0 0 1-2-2v-12A2 2 0 0 1 5 6.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
  if ($name === 'database') return '<svg viewBox="0 0 24 24" fill="none"><path d="M12 3c4.4 0 8 1.3 8 3s-3.6 3-8 3-8-1.3-8-3 3.6-3 8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>';
  if ($name === 'settings') return '<svg viewBox="0 0 24 24" fill="none"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15a8.3 8.3 0 0 0 .1-1l2-1.1-2-3.5-2.2.6a7.7 7.7 0 0 0-1.7-1l-.3-2.2H9.7L9.4 8a7.7 7.7 0 0 0-1.7 1l-2.2-.6-2 3.5 2 1.1a8.3 8.3 0 0 0 .1 1l-2 1.1 2 3.5 2.2-.6a7.7 7.7 0 0 0 1.7 1l.3 2.2h5.6l.3-2.2a7.7 7.7 0 0 0 1.7-1l2.2.6 2-3.5-2-1.1Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>';
  if ($name === 'logout') return '<svg viewBox="0 0 24 24" fill="none"><path d="M10 17h-1a6 6 0 1 1 0-12h1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M14 7l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 12H10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
  if ($name === 'msg') return '<svg viewBox="0 0 24 24" fill="none"><path d="M21 14a4 4 0 0 1-4 4H8l-5 3V6a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M7 8h10M7 12h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
  return '';
}

/**
 * render sidebar (basePath: prefix link untuk path)
 * contoh:
 *  - dashboard.php ada di /admin => basePath = "" (atau "./")
 *  - inputmahasiswa.php ada di /admin/mahasiswa => basePath = ".."
 */
function renderSidebar($menu, $nama_tampil, $username, $basePath = ""){
  $bp = rtrim($basePath, "/");
  if ($bp !== "") $bp .= "/";

  $groups = [
    [
      "title" => "Dashboards",
      "items" => [
        ["type"=>"link","label"=>"Dashboard","href"=>$bp."dashboard.php?menu=dashboard","icon"=>"dash","active"=>$menu==='dashboard'],
      ]
    ],
    [
      "title" => "Akademik",
      "items" => [
        [
          "type"=>"sub","id"=>"subMahasiswa","label"=>"Mahasiswa","icon"=>"mhs",
          "open" => isPrefix($menu,'mhs_'),
          "children" => [
            ["label"=>"Data Mahasiswa","href"=>$bp."mahasiswa/data.php","active"=>$menu==='mhs_data'],
            ["label"=>"Input Data","href"=>$bp."mahasiswa/inputmahasiswa.php","active"=>$menu==='mhs_input'],
            ["label"=>"Cetak Akun","href"=>$bp."mahasiswa/cetakakun.php","active"=>$menu==='mhs_cetak'],
          ]
        ],
        [
          "type"=>"sub","id"=>"subDosen","label"=>"Dosen","icon"=>"dsn",
          "open" => isPrefix($menu,'dsn_'),
          "children" => [
            ["label"=>"Data Dosen","href"=>$bp."dashboard.php?menu=dsn_data","active"=>$menu==='dsn_data'],
            ["label"=>"Input Dosen","href"=>$bp."dashboard.php?menu=dsn_input","active"=>$menu==='dsn_input'],
          ]
        ],
      ]
    ],
    [
      "title" => "Lainnya",
      "items" => [
        ["type"=>"sub","id"=>"subKeu","label"=>"Keuangan","icon"=>"wallet","open"=>isPrefix($menu,'keu_'),
          "children"=>[
            ["label"=>"Menu 1","href"=>$bp."dashboard.php?menu=keu_1","active"=>$menu==='keu_1'],
            ["label"=>"Menu 2","href"=>$bp."dashboard.php?menu=keu_2","active"=>$menu==='keu_2'],
          ]
        ],
        ["type"=>"sub","id"=>"subMaster","label"=>"Data Master","icon"=>"layers","open"=>isPrefix($menu,'master_'),
          "children"=>[
            ["label"=>"Menu 1","href"=>$bp."dashboard.php?menu=master_1","active"=>$menu==='master_1'],
            ["label"=>"Menu 2","href"=>$bp."dashboard.php?menu=master_2","active"=>$menu==='master_2'],
          ]
        ],
        ["type"=>"sub","id"=>"subAkun","label"=>"Akun User","icon"=>"users","open"=>isPrefix($menu,'akun_'),
          "children"=>[
            ["label"=>"Menu 1","href"=>$bp."dashboard.php?menu=akun_1","active"=>$menu==='akun_1'],
            ["label"=>"Menu 2","href"=>$bp."dashboard.php?menu=akun_2","active"=>$menu==='akun_2'],
          ]
        ],
        ["type"=>"sub","id"=>"subKal","label"=>"Kalender Akademik","icon"=>"calendar","open"=>isPrefix($menu,'kal_'),
          "children"=>[
            ["label"=>"Menu 1","href"=>$bp."dashboard.php?menu=kal_1","active"=>$menu==='kal_1'],
            ["label"=>"Menu 2","href"=>$bp."dashboard.php?menu=kal_2","active"=>$menu==='kal_2'],
          ]
        ],
        ["type"=>"sub","id"=>"subBackup","label"=>"Backup & Restore","icon"=>"database","open"=>isPrefix($menu,'bkp_'),
          "children"=>[
            ["label"=>"Menu 1","href"=>$bp."dashboard.php?menu=bkp_1","active"=>$menu==='bkp_1'],
            ["label"=>"Menu 2","href"=>$bp."dashboard.php?menu=bkp_2","active"=>$menu==='bkp_2'],
          ]
        ],
        ["type"=>"sub","id"=>"subSet","label"=>"Pengaturan","icon"=>"settings","open"=>isPrefix($menu,'set_'),
          "children"=>[
            ["label"=>"Menu 1","href"=>$bp."dashboard.php?menu=set_1","active"=>$menu==='set_1'],
            ["label"=>"Menu 2","href"=>$bp."dashboard.php?menu=set_2","active"=>$menu==='set_2'],
          ]
        ],
      ]
    ],
  ];
  ?>
  <aside class="sidebar" id="sidebar" aria-label="Sidebar Admin">
    <div class="brand">
      <img src="<?= e($bp) ?>../img/unfari.png" class="brand-logo" alt="Logo" onerror="this.style.display='none'">
      <div class="brand-text">
        <div class="brand-name"><?= e($nama_tampil) ?></div>
        <div class="brand-sub">Admin</div>
      </div>
    </div>

    <nav class="nav" id="nav">
      <?php foreach($groups as $g): ?>
        <div class="nav-group">
          <div class="nav-title"><?= e($g['title']) ?></div>

          <?php foreach($g['items'] as $it): ?>
            <?php if ($it['type']==='link'): ?>
              <a class="nav-item <?= !empty($it['active'])?'active':'' ?>" href="<?= e($it['href']) ?>">
                <span class="ic"><?= icon($it['icon']) ?></span>
                <span class="tx"><?= e($it['label']) ?></span>
              </a>

            <?php else: ?>
              <button class="nav-item has-sub <?= !empty($it['open'])?'open':'' ?>" type="button"
                data-sub="<?= e($it['id']) ?>" aria-expanded="<?= !empty($it['open'])?'true':'false' ?>">
                <span class="ic"><?= icon($it['icon']) ?></span>
                <span class="tx"><?= e($it['label']) ?></span>
                <span class="ch" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
              </button>

              <div class="sub <?= !empty($it['open'])?'open':'' ?>" id="<?= e($it['id']) ?>">
                <?php foreach($it['children'] as $c): ?>
                  <a class="sub-item <?= !empty($c['active'])?'active':'' ?>" href="<?= e($c['href']) ?>">
                    <span class="dot"></span>
                    <span class="tx"><?= e($c['label']) ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <a class="nav-item danger" href="<?= e($bp) ?>logout.php">
        <span class="ic"><?= icon('logout') ?></span>
        <span class="tx">Logout</span>
      </a>
    </nav>

    <div class="who">
      <div class="who-ava" aria-hidden="true"><?= strtoupper(substr($username,0,1)) ?></div>
      <div class="who-txt">
        <div class="who-name"><?= e($nama_tampil) ?></div>
        <div class="who-role">Admin</div>
      </div>
    </div>
  </aside>
  <?php
}

/**
 * Start layout (mencetak HTML head + sidebar + topbar + opening content)
 * $assetsBase: prefix untuk folder css/js/img dari halaman saat ini
 *   - dashboard.php (/admin) => assetsBase = ".."
 *   - inputmahasiswa.php (/admin/mahasiswa) => assetsBase = "../.."
 */
function renderAdminLayoutStart($opts = []){
  $title      = $opts['title'] ?? 'Admin';
  $pageTitle  = $opts['page_title'] ?? 'Dashboard';
  $pageSub    = $opts['page_sub'] ?? '';
  $menu       = $opts['menu'] ?? 'dashboard';
  $nama       = $opts['nama_tampil'] ?? 'Admin';
  $username   = $opts['username'] ?? 'user';
  $assetsBase = rtrim($opts['assetsBase'] ?? '..', '/');   // css/js/img
  $basePath   = rtrim($opts['basePath'] ?? '', '/');       // link antar halaman admin

  // normalize
  if ($basePath !== '') $basePath = rtrim($basePath, '/');

  ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($title) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= e($assetsBase) ?>/css/css_admin/dashboard.css">
  <?php
    // extra css optional (mis: input mahasiswa)
    if (!empty($opts['extra_css']) && is_array($opts['extra_css'])) {
      foreach($opts['extra_css'] as $css) {
        echo '<link rel="stylesheet" href="'.e($assetsBase.'/'.$css).'">'."\n";
      }
    }
  ?>
</head>
<body>
  <div class="app" id="app">
    <?php renderSidebar($menu, $nama, $username, $basePath); ?>

    <div class="overlay" id="overlay" aria-hidden="true"></div>
    <div class="flyout" id="flyout" aria-hidden="true"></div>

    <main class="main">
      <header class="topbar">
        <div class="top-left">
          <button class="burger" id="btnBurger" type="button" aria-label="Buka/tutup sidebar">
            <span></span><span></span><span></span>
          </button>

          <div class="top-title">
            <div class="t1"><?= e($pageTitle) ?></div>
            <?php if ($pageSub !== ''): ?>
              <div class="t2"><?= e($pageSub) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="top-actions">
          <button class="iconbtn" id="btnBell" type="button" aria-label="Pesan (placeholder)">
            <?= icon('msg') ?>
            <span class="badge" id="bellBadge">1</span>
          </button>

          <div class="profile" id="profileWrap">
            <button class="miniava" id="btnProfile" type="button" aria-label="Profile">
              <?= e(strtoupper(substr($username,0,1))) ?>
            </button>

            <div class="pdrop" id="profileDrop" aria-hidden="true">
              <div class="phead">
                <div class="pava" aria-hidden="true"><?= e(strtoupper(substr($username,0,1))) ?></div>
                <div class="ptxt">
                  <div class="pn"><?= e($nama) ?></div>
                  <div class="pr">@<?= e($username) ?></div>
                </div>
              </div>
              <div class="pbody">
                <div class="pitem">Profile (placeholder)</div>
                <div class="pitem">Pengaturan (placeholder)</div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <section class="content">
  <?php
}

/**
 * End layout (menutup content + include js + closing tags)
 * $assetsBase sama seperti start
 */
function renderAdminLayoutEnd($opts = []){
  $assetsBase = rtrim($opts['assetsBase'] ?? '..', '/');

  // extra js optional
  $extraJs = $opts['extra_js'] ?? [];

  ?>
      </section>
    </main>
  </div>

  <script src="<?= e($assetsBase) ?>/js/js_admin/dashboard.js"></script>
  <?php
    if (!empty($extraJs) && is_array($extraJs)) {
      foreach($extraJs as $js) {
        echo '<script src="'.e($assetsBase.'/'.$js).'"></script>'."\n";
      }
    }
  ?>
</body>
</html>
  <?php
}