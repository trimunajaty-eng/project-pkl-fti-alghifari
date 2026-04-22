<?php
require_once __DIR__ . "/../../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$currentPage = 'inputnilai';
$baseUrl = '../';
$namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

$selectedPeriode = trim($_GET['periode'] ?? '');
$selectedJurusan = trim($_GET['jurusan'] ?? '');
$idDosen         = (int)($_GET['id_dosen'] ?? 0);
$idMahasiswa     = (int)($_GET['id_mahasiswa'] ?? 0);
$page            = max(1, (int)($_GET['page'] ?? 1));
$perPage         = 10;

$selectedTahunAkademik = '';
$selectedSemester = '';

if ($selectedPeriode !== '') {
  if (preg_match('/^(.+?)\s+(Ganjil|Genap)$/i', $selectedPeriode, $matches)) {
    $selectedTahunAkademik = trim($matches[1]);
    $selectedSemester = trim($matches[2]);
  }
}

/**
 * ==================================
 * Ambil list periode akademik
 * ==================================
 */
$periodeList = [];
$qPeriode = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'periode_pendaftaran' AND is_active = 1 ORDER BY urutan ASC");
if ($qPeriode) {
  while ($r = $qPeriode->fetch_assoc()) {
    $periodeList[] = $r['value'];
  }
}

/**
 * ==================================
 * Ambil list jurusan
 * ==================================
 */
$jurusanList = [];
$qJurusan = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'program_studi' AND is_active = 1 ORDER BY urutan ASC");
if ($qJurusan) {
  while ($r = $qJurusan->fetch_assoc()) {
    $jurusanList[] = $r['value'];
  }
}

/**
 * ==================================
 * Ambil list dosen sesuai jurusan
 * ==================================
 */
$dosenList = [];
if ($selectedJurusan !== '') {
  $stmtDosen = $conn->prepare("SELECT id_dosen, kode_dosen, nama_dosen, jenis_kelamin, email, program_studi
                               FROM dosen
                               WHERE program_studi = ?
                               ORDER BY nama_dosen ASC");
  $stmtDosen->bind_param("s", $selectedJurusan);
  $stmtDosen->execute();
  $resDosen = $stmtDosen->get_result();

  if ($resDosen) {
    while ($r = $resDosen->fetch_assoc()) {
      $dosenList[] = $r;
    }
  }

  $stmtDosen->close();
}

/**
 * ==================================
 * Data dosen terpilih
 * ==================================
 */
$dataDosen = null;
$allDosenList = [];
$qAllDosen = $conn->query("SELECT id_dosen, kode_dosen, nama_dosen, jenis_kelamin, email, program_studi
                           FROM dosen
                           ORDER BY nama_dosen ASC");
if ($qAllDosen) {
  while ($r = $qAllDosen->fetch_assoc()) {
    $allDosenList[] = $r;
  }
}
if ($idDosen > 0) {
  $stmtDosenSelected = $conn->prepare("SELECT id_dosen, kode_dosen, nama_dosen, jenis_kelamin, email, program_studi
                                       FROM dosen
                                       WHERE id_dosen = ? LIMIT 1");
  $stmtDosenSelected->bind_param("i", $idDosen);
  $stmtDosenSelected->execute();
  $resDosenSelected = $stmtDosenSelected->get_result();
  if ($resDosenSelected && $resDosenSelected->num_rows === 1) {
    $dataDosen = $resDosenSelected->fetch_assoc();
  }
  $stmtDosenSelected->close();
}

/**
 * ==================================
 * Hitung total mahasiswa untuk pagination
 * ==================================
 */
$totalMahasiswa = 0;
$totalPages = 1;
$offset = ($page - 1) * $perPage;

$mahasiswaList = [];

if ($selectedPeriode !== '' && $selectedJurusan !== '' && $idDosen > 0) {
  $stmtCount = $conn->prepare("SELECT COUNT(*) AS total
                               FROM mahasiswa
                               WHERE periode_pendaftaran = ?
                                 AND program_studi = ?");
  $stmtCount->bind_param("ss", $selectedPeriode, $selectedJurusan);
  $stmtCount->execute();
  $resCount = $stmtCount->get_result();
  if ($resCount && $rowCount = $resCount->fetch_assoc()) {
    $totalMahasiswa = (int)$rowCount['total'];
  }
  $stmtCount->close();

  $totalPages = max(1, (int)ceil($totalMahasiswa / $perPage));
  if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
  }

  $stmtList = $conn->prepare("SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas
                              FROM mahasiswa
                              WHERE periode_pendaftaran = ?
                                AND program_studi = ?
                              ORDER BY nama_mahasiswa ASC
                              LIMIT ? OFFSET ?");
  $stmtList->bind_param("ssii", $selectedPeriode, $selectedJurusan, $perPage, $offset);
  $stmtList->execute();
  $resList = $stmtList->get_result();

  if ($resList) {
    while ($r = $resList->fetch_assoc()) {
      $mahasiswaList[] = $r;
    }
  }

  $stmtList->close();
}

/**
 * ==================================
 * Ambil data mahasiswa terpilih
 * ==================================
 */
$dataMahasiswa = null;
$dataNilai = [
  'tugas' => '',
  'uts' => '',
  'uas' => '',
  'kehadiran' => '',
  'nilai_akhir' => '',
  'grade' => '',
  'keterangan' => ''
];

if ($selectedPeriode !== '' && $selectedJurusan !== '' && $selectedTahunAkademik !== '' && $selectedSemester !== '' && $idDosen > 0 && $idMahasiswa > 0) {
  $stmtMhs = $conn->prepare("SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas
                             FROM mahasiswa
                             WHERE id_mahasiswa = ?
                               AND periode_pendaftaran = ?
                               AND program_studi = ?
                             LIMIT 1");
  $stmtMhs->bind_param("iss", $idMahasiswa, $selectedPeriode, $selectedJurusan);
  $stmtMhs->execute();
  $resMhs = $stmtMhs->get_result();
  if ($resMhs && $resMhs->num_rows === 1) {
    $dataMahasiswa = $resMhs->fetch_assoc();
  }
  $stmtMhs->close();

  if ($dataMahasiswa) {
    $stmtNilai = $conn->prepare("SELECT tugas, uts, uas, kehadiran, nilai_akhir, grade, keterangan
                                 FROM nilai_mahasiswa
                                 WHERE id_mahasiswa = ?
                                   AND id_dosen = ?
                                   AND tahun_akademik = ?
                                   AND semester = ?
                                 LIMIT 1");
    $stmtNilai->bind_param("iiss", $idMahasiswa, $idDosen, $selectedTahunAkademik, $selectedSemester);
    $stmtNilai->execute();
    $resNilai = $stmtNilai->get_result();
    if ($resNilai && $resNilai->num_rows === 1) {
      $dataNilai = $resNilai->fetch_assoc();
    }
    $stmtNilai->close();
  }
}

$canInput = ($dataMahasiswa !== null);

$pesan = trim($_GET['pesan'] ?? '');
$tipe  = trim($_GET['tipe'] ?? 'info');

function buildQuery($params = []) {
  return http_build_query(array_filter($params, function ($v) {
    return !($v === '' || $v === null || $v === 0);
  }));
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Input Nilai Akademik</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../css/css_akademik/nilai/inputnilai.css?v=4">
  <script>
    (function () {
      try {
        if (window.innerWidth > 860 && localStorage.getItem('ak_sidebar_collapsed') === '1') {
          document.documentElement.classList.add('sidebar-collapsed-init');
        }
      } catch (e) {}
    })();
  </script>
</head>
<body class="<?= $canInput ? 'has-selected-student' : ''; ?>">
  <div class="app" id="app">
    <?php include __DIR__ . "/../sidebarmenu.php"; ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main">
      <div class="toast" id="toast" aria-hidden="true">
        <div class="toast-card" id="toastCard">
          <div class="toast-title" id="toastTitle">Info</div>
          <div class="toast-msg" id="toastMsg">-</div>
        </div>
      </div>

      <header class="topbar">
        <div class="top-left">
          <button type="button" id="menuToggle" class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="topbar-text">
            <h1 class="page-title">Input Nilai</h1>
            <p class="page-subtitle">Pilih periode, jurusan, dosen, lalu mahasiswa untuk membuka form pengisian nilai.</p>
          </div>
        </div>
      </header>

      <div class="content">
        <section class="toolbar-panel">
          <div class="summary-strip">
            <div class="summary-card">
              <span class="summary-label">Periode Akademik</span>
              <strong class="summary-value"><?= $selectedPeriode !== '' ? htmlspecialchars($selectedPeriode) : '-' ?></strong>
            </div>

            <div class="summary-card">
              <span class="summary-label">Jurusan</span>
              <strong class="summary-value"><?= $selectedJurusan !== '' ? htmlspecialchars($selectedJurusan) : '-' ?></strong>
            </div>

            <div class="summary-card">
              <span class="summary-label">Dosen</span>
              <strong class="summary-value"><?= $dataDosen ? htmlspecialchars($dataDosen['nama_dosen']) : '-' ?></strong>
            </div>
          </div>

          <div class="toolbar-actions">
            <button type="button" class="action-btn" id="btnOpenFilter">Filter Data</button>

            <?php if ($canInput): ?>
              <a href="inputnilai.php?<?= buildQuery([
                'periode' => $selectedPeriode,
                'jurusan' => $selectedJurusan,
                'id_dosen' => $idDosen,
                'page' => $page
              ]); ?>" class="ghost-btn">
                Ganti Mahasiswa
              </a>
            <?php endif; ?>
          </div>
        </section>

        <section class="workspace <?= $canInput ? 'selected' : 'idle'; ?>" id="workspace">
          <div class="student-panel card" id="studentPanel">
            <div class="card-head card-head-flex">
              <div>
                <h2>Pilih Mahasiswa</h2>
                <p class="section-subtitle">Cari berdasarkan NIM atau nama. Data ditampilkan 10 per halaman.</p>
              </div>
              <div class="badge-soft"><?= number_format($totalMahasiswa); ?> Data</div>
            </div>

            <div class="card-body">
              <?php if ($selectedPeriode === '' || $selectedJurusan === '' || $idDosen <= 0): ?>
                <div class="empty-box empty-box-large">
                  <strong>Filter belum lengkap.</strong><br>
                  Pilih <strong>periode akademik</strong>, <strong>jurusan</strong>, dan <strong>dosen</strong> terlebih dahulu melalui tombol <strong>Filter Data</strong>.
                </div>
              <?php elseif (empty($mahasiswaList)): ?>
                <div class="empty-box empty-box-large">
                  Belum ada data mahasiswa untuk filter yang dipilih.
                </div>
              <?php else: ?>
                <div class="search-wrap">
                  <input
                    type="text"
                    id="studentSearch"
                    class="search-input"
                    placeholder="Cari NIM atau nama mahasiswa..."
                    autocomplete="off"
                  >
                </div>

                <div class="student-list" id="studentList">
                  <?php foreach ($mahasiswaList as $m): ?>
                    <a
                      class="student-item student-select-link <?= ($idMahasiswa === (int)$m['id_mahasiswa']) ? 'active' : '' ?>"
                      data-student-link="1"
                      href="inputnilai.php?<?= buildQuery([
                        'periode' => $selectedPeriode,
                        'jurusan' => $selectedJurusan,
                        'id_dosen' => $idDosen,
                        'id_mahasiswa' => (int)$m['id_mahasiswa'],
                        'page' => $page
                      ]); ?>"
                      data-search="<?= htmlspecialchars(strtolower($m['nim'] . ' ' . $m['nama_mahasiswa'] . ' ' . $m['program_studi'] . ' ' . $m['kelas'])); ?>"
                    >
                      <div class="student-avatar">
                        <?= strtoupper(substr(trim($m['nama_mahasiswa']), 0, 1)); ?>
                      </div>

                      <div class="student-content">
                        <div class="student-name"><?= htmlspecialchars($m['nama_mahasiswa']); ?></div>
                        <div class="student-meta">
                          <?= htmlspecialchars($m['nim']); ?> · <?= htmlspecialchars($m['program_studi']); ?> · <?= htmlspecialchars($m['kelas']); ?>
                        </div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>

                <div class="empty-search" id="emptySearch" hidden>
                  Data mahasiswa yang dicari tidak ditemukan.
                </div>

                <?php if ($totalPages > 1): ?>
                  <div class="pagination">
                    <?php
                    $prevPage = max(1, $page - 1);
                    $nextPage = min($totalPages, $page + 1);
                    ?>
                    <a class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $page <= 1 ? '#' : 'inputnilai.php?' . buildQuery([
                      'periode' => $selectedPeriode,
                      'jurusan' => $selectedJurusan,
                      'id_dosen' => $idDosen,
                      'page' => $prevPage
                    ]); ?>">← Sebelumnya</a>

                    <div class="page-info">Halaman <?= $page; ?> dari <?= $totalPages; ?></div>

                    <a class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= $page >= $totalPages ? '#' : 'inputnilai.php?' . buildQuery([
                      'periode' => $selectedPeriode,
                      'jurusan' => $selectedJurusan,
                      'id_dosen' => $idDosen,
                      'page' => $nextPage
                    ]); ?>">Berikutnya →</a>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-panel card" id="formPanel">
            <div class="card-head card-head-flex">
              <div>
                <h2>Form Nilai Mahasiswa</h2>
                <p class="section-subtitle">Form tampil penuh setelah mahasiswa dipilih.</p>
              </div>

              <?php if ($canInput): ?>
                <div class="status-badge">Siap Diisi</div>
              <?php endif; ?>
            </div>

            <div class="card-body">
              <?php if (!$canInput): ?>
                <div class="empty-box empty-box-large">
                  <strong>Form belum aktif.</strong><br>
                  Pilih mahasiswa terlebih dahulu dari panel daftar mahasiswa.
                </div>
              <?php else: ?>
                <div class="student-selected">
                  <div class="student-selected-item">
                    <span>Nama</span>
                    <strong><?= htmlspecialchars($dataMahasiswa['nama_mahasiswa']); ?></strong>
                  </div>
                  <div class="student-selected-item">
                    <span>NIM</span>
                    <strong><?= htmlspecialchars($dataMahasiswa['nim']); ?></strong>
                  </div>
                  <div class="student-selected-item">
                    <span>Program Studi</span>
                    <strong><?= htmlspecialchars($dataMahasiswa['program_studi']); ?></strong>
                  </div>
                  <div class="student-selected-item">
                    <span>Kelas</span>
                    <strong><?= htmlspecialchars($dataMahasiswa['kelas']); ?></strong>
                  </div>
                  <div class="student-selected-item">
                    <span>Dosen</span>
                    <strong><?= htmlspecialchars($dataDosen['nama_dosen'] ?? '-'); ?></strong>
                  </div>
                  <div class="student-selected-item">
                    <span>Periode</span>
                    <strong><?= htmlspecialchars($selectedPeriode); ?></strong>
                  </div>
                </div>

                <form action="proses_inputnilai.php" method="post" id="formNilai">
                  <input type="hidden" name="id_mahasiswa" value="<?= (int)$dataMahasiswa['id_mahasiswa']; ?>">
                  <input type="hidden" name="id_dosen" value="<?= (int)$idDosen; ?>">
                  <input type="hidden" name="tahun_akademik" value="<?= htmlspecialchars($selectedTahunAkademik); ?>">
                  <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemester); ?>">
                  <input type="hidden" name="periode" value="<?= htmlspecialchars($selectedPeriode); ?>">
                  <input type="hidden" name="jurusan" value="<?= htmlspecialchars($selectedJurusan); ?>">
                  <input type="hidden" name="page" value="<?= (int)$page; ?>">

                  <div class="form-grid">
                    <label class="field">
                      <span>Tugas</span>
                      <input type="number" step="0.01" min="0" max="100" name="tugas" value="<?= htmlspecialchars((string)$dataNilai['tugas']); ?>" required>
                    </label>

                    <label class="field">
                      <span>UTS</span>
                      <input type="number" step="0.01" min="0" max="100" name="uts" value="<?= htmlspecialchars((string)$dataNilai['uts']); ?>" required>
                    </label>

                    <label class="field">
                      <span>UAS</span>
                      <input type="number" step="0.01" min="0" max="100" name="uas" value="<?= htmlspecialchars((string)$dataNilai['uas']); ?>" required>
                    </label>

                    <label class="field">
                      <span>Kehadiran</span>
                      <input type="number" step="0.01" min="0" max="100" name="kehadiran" value="<?= htmlspecialchars((string)$dataNilai['kehadiran']); ?>" required>
                    </label>

                    <label class="field readonly-field">
                      <span>Nilai Akhir</span>
                      <input type="number" step="0.01" min="0" max="100" name="nilai_akhir" id="nilai_akhir" value="<?= htmlspecialchars((string)$dataNilai['nilai_akhir']); ?>" readonly>
                    </label>

                    <label class="field readonly-field">
                      <span>Grade</span>
                      <input type="text" name="grade" id="grade" value="<?= htmlspecialchars((string)$dataNilai['grade']); ?>" readonly>
                    </label>
                  </div>

                  <label class="field readonly-field">
                    <span>Keterangan</span>
                    <input type="text" name="keterangan" id="keterangan" value="<?= htmlspecialchars((string)$dataNilai['keterangan']); ?>" readonly>
                  </label>

                  <div class="result-preview">
                    <div class="result-chip">
                      <span>Bobot Tugas</span>
                      <strong>25%</strong>
                    </div>
                    <div class="result-chip">
                      <span>Bobot UTS</span>
                      <strong>25%</strong>
                    </div>
                    <div class="result-chip">
                      <span>Bobot UAS</span>
                      <strong>35%</strong>
                    </div>
                    <div class="result-chip">
                      <span>Bobot Kehadiran</span>
                      <strong>15%</strong>
                    </div>
                  </div>

                  <div class="form-actions">
                    <button type="submit" class="btn-save">Simpan Nilai</button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <div class="modal" id="filterModal" aria-hidden="true">
    <div class="modal-overlay" id="filterModalOverlay"></div>
    <div class="modal-box">
      <div class="modal-head">
        <h3>Filter Input Nilai</h3>
        <button type="button" class="modal-close" id="btnCloseFilter" aria-label="Tutup popup">×</button>
      </div>

      <form method="get" action="inputnilai.php" class="modal-body">
        <label class="field">
          <span>Periode Akademik</span>
          <select name="periode" id="filterPeriode" required>
            <option value="">-- Pilih Periode Akademik --</option>
            <?php foreach ($periodeList as $periode): ?>
              <option value="<?= htmlspecialchars($periode); ?>" <?= $selectedPeriode === $periode ? 'selected' : '' ?>>
                <?= htmlspecialchars($periode); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field">
          <span>Jurusan</span>
          <select name="jurusan" id="filterJurusan" required>
            <option value="">-- Semua Jurusan / Pilih Jurusan --</option>
            <?php foreach ($jurusanList as $jurusan): ?>
              <option value="<?= htmlspecialchars($jurusan); ?>" <?= $selectedJurusan === $jurusan ? 'selected' : '' ?>>
                <?= htmlspecialchars($jurusan); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field">
          <span>Dosen</span>
          <select name="id_dosen" id="filterDosen" required>
            <option value="">-- Pilih Dosen --</option>
            <?php foreach ($dosenList as $dosen): ?>
              <option value="<?= (int)$dosen['id_dosen']; ?>" <?= $idDosen === (int)$dosen['id_dosen'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($dosen['nama_dosen'] . ' (' . $dosen['kode_dosen'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <div class="modal-actions">
          <button type="submit" class="btn-save modal-submit">Terapkan Filter</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    window.__FLASH__ = <?= json_encode([
      "tipe" => $tipe,
      "pesan" => $pesan
    ], JSON_UNESCAPED_UNICODE); ?>;

    window.__ALL_DOSEN__ = <?= json_encode($allDosenList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    window.__SELECTED_DOSEN_ID__ = <?= (int)$idDosen; ?>;
  </script>
  <script src="../../js/js_akademik/nilai/inputnilai.js?v=4"></script>
</body>
</html>