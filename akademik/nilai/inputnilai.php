<?php
require_once __DIR__ . "/../../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai akademik terlebih dahulu."));
  exit;
}

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
  <title>Input Nilai</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../css/css_akademik/nilai/inputnilai.css">
</head>
<body>
  <div class="app">
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="brand">
          <div class="brand-icon">AK</div>
          <div>
            <div class="brand-title">Akademik</div>
            <div class="brand-subtitle">Unfari</div>
          </div>
        </div>

        <nav class="menu">
          <a href="../dashboard.php" class="menu-item">Dashboard</a>
          <a href="inputnilai.php" class="menu-item active">Input Nilai</a>
          <a href="#" class="menu-item">Data Nilai</a>
          <a href="#" class="menu-item">Laporan</a>
          <a href="../logout.php" class="menu-item menu-item-danger">Logout</a>
        </nav>
      </div>
    </aside>

    <main class="main">
      <div class="toast" id="toast" aria-hidden="true">
        <div class="toast-card" id="toastCard">
          <div class="toast-title" id="toastTitle">Info</div>
          <div class="toast-msg" id="toastMsg">-</div>
        </div>
      </div>

      <header class="topbar">
        <div class="topbar-left">
          <button type="button" id="menuToggle" class="menu-toggle">☰</button>
          <div>
            <h1 class="page-title">Input Nilai</h1>
            <p class="page-subtitle">Pilih periode dan mahasiswa terlebih dahulu</p>
          </div>
        </div>

        <div class="top-actions">
          <button type="button" class="btn btn-filter" id="btnOpenFilter">Filter Tahun</button>
        </div>
      </header>

      <section class="toolbar card">
        <div class="toolbar-item">
          <div class="toolbar-label">Periode</div>
          <div class="toolbar-value"><?= $selectedPeriode !== '' ? htmlspecialchars($selectedPeriode) : '-' ?></div>
        </div>
        <div class="toolbar-item">
          <div class="toolbar-label">Semester</div>
          <div class="toolbar-value"><?= $selectedSemester !== '' ? htmlspecialchars($selectedSemester) : '-' ?></div>
        </div>
        <div class="toolbar-item">
          <div class="toolbar-label">Mahasiswa</div>
          <div class="toolbar-value"><?= $dataMahasiswa ? htmlspecialchars($dataMahasiswa['nama_mahasiswa']) : '-' ?></div>
        </div>
      </section>

      <section class="grid">
        <div class="card">
          <div class="card-head">
            <h2>Daftar Mahasiswa</h2>
          </div>
          <div class="card-body">
            <?php if ($selectedPeriode === '' || $selectedSemester === ''): ?>
              <div class="empty-box">
                Silakan pilih <strong>periode</strong> dan <strong>semester</strong> dulu melalui tombol Filter Tahun.
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
                    <div class="student-name"><?= htmlspecialchars($m['nama_mahasiswa']); ?></div>
                    <div class="student-meta">
                      <?= htmlspecialchars($m['nim']); ?> · <?= htmlspecialchars($m['program_studi']); ?> · <?= htmlspecialchars($m['kelas']); ?>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-head">
            <h2>Form Nilai</h2>
          </div>
          <div class="card-body">
            <?php if (!$canInput): ?>
              <div class="empty-box">
                Form belum aktif.  
                Pilih <strong>filter tahun</strong> lalu pilih <strong>mahasiswa</strong> terlebih dahulu.
              </div>
            <?php else: ?>
              <div class="student-selected">
                <div><strong>Nama:</strong> <?= htmlspecialchars($dataMahasiswa['nama_mahasiswa']); ?></div>
                <div><strong>NIM:</strong> <?= htmlspecialchars($dataMahasiswa['nim']); ?></div>
                <div><strong>Prodi:</strong> <?= htmlspecialchars($dataMahasiswa['program_studi']); ?></div>
                <div><strong>Kelas:</strong> <?= htmlspecialchars($dataMahasiswa['kelas']); ?></div>
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

                  <label class="field">
                    <span>Nilai Akhir</span>
                    <input type="number" step="0.01" min="0" max="100" name="nilai_akhir" id="nilai_akhir" value="<?= htmlspecialchars((string)$dataNilai['nilai_akhir']); ?>" readonly>
                  </label>

                  <label class="field">
                    <span>Grade</span>
                    <input type="text" name="grade" id="grade" value="<?= htmlspecialchars((string)$dataNilai['grade']); ?>" readonly>
                  </label>
                </div>

                <label class="field">
                  <span>Keterangan</span>
                  <input type="text" name="keterangan" id="keterangan" value="<?= htmlspecialchars((string)$dataNilai['keterangan']); ?>" readonly>
                </label>

                <div class="form-actions">
                  <button type="submit" class="btn btn-save">Simpan Nilai</button>
                </div>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- modal filter -->
  <div class="modal" id="filterModal" aria-hidden="true">
    <div class="modal-box">
      <div class="modal-head">
        <h3>Pilih Periode Akademik</h3>
        <button type="button" class="modal-close" id="btnCloseFilter">×</button>
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
          <button type="submit" class="btn btn-save">Terapkan Filter</button>
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
  <script src="../../js/js_akademik/nilai/inputnilai.js"></script>
</body>
</html>