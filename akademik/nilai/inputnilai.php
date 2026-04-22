<?php
require_once __DIR__ . "/../../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

$currentPage = 'inputnilai';
$baseUrl = '../';
$namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

$periodeList = [];
$qPeriode = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'periode_pendaftaran' AND is_active = 1 ORDER BY urutan ASC");
if ($qPeriode) {
  while ($r = $qPeriode->fetch_assoc()) {
    $periodeList[] = $r['value'];
  }
}

$selectedPeriode = trim($_GET['periode'] ?? '');
$selectedSemester = trim($_GET['semester'] ?? '');
$idMahasiswa = (int)($_GET['id_mahasiswa'] ?? 0);

$mahasiswaList = [];
if ($selectedPeriode !== '') {
  $qMhs = $conn->query("SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas FROM mahasiswa ORDER BY nama_mahasiswa ASC");
  if ($qMhs) {
    while ($r = $qMhs->fetch_assoc()) {
      $mahasiswaList[] = $r;
    }
  }
}

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

if ($selectedPeriode !== '' && $selectedSemester !== '' && $idMahasiswa > 0) {
  $stmtMhs = $conn->prepare("SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas FROM mahasiswa WHERE id_mahasiswa = ? LIMIT 1");
  $stmtMhs->bind_param("i", $idMahasiswa);
  $stmtMhs->execute();
  $resMhs = $stmtMhs->get_result();
  if ($resMhs && $resMhs->num_rows === 1) {
    $dataMahasiswa = $resMhs->fetch_assoc();
  }
  $stmtMhs->close();

  $stmtNilai = $conn->prepare("SELECT tugas, uts, uas, kehadiran, nilai_akhir, grade, keterangan
                               FROM nilai_mahasiswa
                               WHERE id_mahasiswa = ? AND tahun_akademik = ? AND semester = ?
                               LIMIT 1");
  $stmtNilai->bind_param("iss", $idMahasiswa, $selectedPeriode, $selectedSemester);
  $stmtNilai->execute();
  $resNilai = $stmtNilai->get_result();
  if ($resNilai && $resNilai->num_rows === 1) {
    $dataNilai = $resNilai->fetch_assoc();
  }
  $stmtNilai->close();
}

$canInput = ($dataMahasiswa !== null);

$pesan = trim($_GET['pesan'] ?? '');
$tipe  = trim($_GET['tipe'] ?? 'info');
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
  <link rel="stylesheet" href="../../css/css_akademik/nilai/inputnilai.css?v=2">
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
<body>
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
            <p class="page-subtitle">Halo, <?= htmlspecialchars($namaLogin); ?>. Kelola nilai mahasiswa dengan tampilan yang lebih rapi.</p>
          </div>
        </div>

        <div class="topbar-right">
          <button type="button" class="action-btn" id="btnOpenFilter">Filter Tahun</button>
        </div>
      </header>

      <div class="content">
        <section class="hero">
          <div class="hero-content">
            <div class="hero-kicker">Panel Nilai Akademik</div>
            <h2 class="hero-title">Pilih periode akademik, semester, dan mahasiswa untuk mulai menginput atau memperbarui nilai dengan cepat dan terstruktur.</h2>
          </div>
        </section>

        <section class="summary-strip">
          <div class="summary-card">
            <span class="summary-label">Periode</span>
            <strong class="summary-value"><?= $selectedPeriode !== '' ? htmlspecialchars($selectedPeriode) : '-' ?></strong>
          </div>

          <div class="summary-card">
            <span class="summary-label">Semester</span>
            <strong class="summary-value"><?= $selectedSemester !== '' ? htmlspecialchars($selectedSemester) : '-' ?></strong>
          </div>

          <div class="summary-card">
            <span class="summary-label">Mahasiswa Terpilih</span>
            <strong class="summary-value"><?= $dataMahasiswa ? htmlspecialchars($dataMahasiswa['nama_mahasiswa']) : '-' ?></strong>
          </div>
        </section>

        <section class="content-grid">
          <div class="card student-card">
            <div class="card-head card-head-flex">
              <div>
                <h2>Daftar Mahasiswa</h2>
                <p class="section-subtitle">Pilih mahasiswa untuk menampilkan form input nilai.</p>
              </div>
              <div class="badge-soft"><?= count($mahasiswaList); ?> Data</div>
            </div>

            <div class="card-body">
              <?php if ($selectedPeriode === '' || $selectedSemester === ''): ?>
                <div class="empty-box">
                  Silakan pilih <strong>periode</strong> dan <strong>semester</strong> terlebih dahulu melalui tombol <strong>Filter Tahun</strong>.
                </div>
              <?php elseif (empty($mahasiswaList)): ?>
                <div class="empty-box">Data mahasiswa belum tersedia.</div>
              <?php else: ?>
                <div class="student-list">
                  <?php foreach ($mahasiswaList as $m): ?>
                    <a
                      class="student-item <?= ($idMahasiswa === (int)$m['id_mahasiswa']) ? 'active' : '' ?>"
                      href="inputnilai.php?periode=<?= urlencode($selectedPeriode) ?>&semester=<?= urlencode($selectedSemester) ?>&id_mahasiswa=<?= (int)$m['id_mahasiswa'] ?>"
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
              <?php endif; ?>
            </div>
          </div>

          <div class="card form-card">
            <div class="card-head">
              <h2>Form Nilai Mahasiswa</h2>
              <p class="section-subtitle">Masukkan nilai tugas, UTS, UAS, dan kehadiran. Nilai akhir akan dihitung otomatis.</p>
            </div>

            <div class="card-body">
              <?php if (!$canInput): ?>
                <div class="empty-box">
                  Form belum aktif. Pilih <strong>filter tahun</strong> lalu pilih <strong>mahasiswa</strong> terlebih dahulu.
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
                </div>

                <form action="proses_inputnilai.php" method="post" id="formNilai">
                  <input type="hidden" name="id_mahasiswa" value="<?= (int)$dataMahasiswa['id_mahasiswa']; ?>">
                  <input type="hidden" name="tahun_akademik" value="<?= htmlspecialchars($selectedPeriode); ?>">
                  <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemester); ?>">

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
        <h3>Pilih Periode Akademik</h3>
        <button type="button" class="modal-close" id="btnCloseFilter" aria-label="Tutup popup">×</button>
      </div>

      <form method="get" action="inputnilai.php" class="modal-body">
        <label class="field">
          <span>Tahun Akademik</span>
          <select name="periode" required>
            <option value="">-- Pilih Tahun Akademik --</option>
            <?php foreach ($periodeList as $periode): ?>
              <option value="<?= htmlspecialchars($periode); ?>" <?= $selectedPeriode === $periode ? 'selected' : '' ?>>
                <?= htmlspecialchars($periode); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field">
          <span>Semester</span>
          <select name="semester" required>
            <option value="">-- Pilih Semester --</option>
            <option value="Ganjil" <?= $selectedSemester === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
            <option value="Genap" <?= $selectedSemester === 'Genap' ? 'selected' : '' ?>>Genap</option>
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
  </script>
  <script src="../../js/js_akademik/nilai/inputnilai.js?v=2"></script>
</body>
</html>