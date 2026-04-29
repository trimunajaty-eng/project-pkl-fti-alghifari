  <?php
  require_once __DIR__ . "/../../config.php";

  if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
      header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
      exit;
  }

  $currentPage = 'inputnilai';
  $baseUrl = '../';
  $namaLogin = $_SESSION['nama_lengkap'] ?? 'Akademik';

  $selectedPeriodeRaw = trim($_GET['periode'] ?? '');
  $selectedJurusan    = trim($_GET['jurusan'] ?? '');
  $semesterAngka      = (int)($_GET['semester_angka'] ?? 0);
  $namaMataKuliah     = trim($_GET['nama_mata_kuliah'] ?? '');
  $namaDosenManual    = trim($_GET['nama_dosen_manual'] ?? '');
  $page               = max(1, (int)($_GET['page'] ?? 1));
  $perPage            = 15;

  $pesan = trim($_GET['pesan'] ?? '');
  $tipe  = trim($_GET['tipe'] ?? 'info');

  function buildQuery($params = [])
  {
      return http_build_query(array_filter($params, function ($v) {
          return !($v === '' || $v === null || $v === 0);
      }));
  }

  function parsePeriode($periode)
  {
      $tahun = '';
      $semesterText = '';

      if (preg_match('/^(\d{4})\/(\d{4})\s+(Ganjil|Genap)$/i', trim($periode), $m)) {
          $a = $m[1];
          $b = $m[2];
          $semesterText = ucfirst(strtolower($m[3]));

          if ($semesterText === 'Ganjil') {
              $tahun = $a . '/' . $b;
          } else {
              $tahun = $b . '/' . $a;
          }
      }

      return [
          'tahun' => $tahun,
          'semester_text' => $semesterText
      ];
  }

  function getPeriodIndex($periodeRaw)
  {
      if (preg_match('/^(\d{4})\/(\d{4})\s+(Ganjil|Genap)$/i', trim($periodeRaw), $m)) {
          $base = 2020;
          $yearDiff = (int)$m[1] - $base;
          $offset = (ucfirst(strtolower($m[3])) === 'Ganjil') ? 0 : 1;
          return ($yearDiff * 2) + $offset + 1;
      }
      return 0;
  }

  function calculateStudentSemester($periodeDaftar, $periodeTarget)
  {
      if (empty($periodeDaftar) || empty($periodeTarget)) {
          return null;
      }

      $idxDaftar = getPeriodIndex($periodeDaftar);
      $idxTarget = getPeriodIndex($periodeTarget);

      if ($idxTarget < $idxDaftar) {
          return null;
      }

      $startSmt = (stripos($periodeDaftar, 'Ganjil') !== false) ? 1 : 2;
      $diff = $idxTarget - $idxDaftar;

      return max(1, min(8, $startSmt + ($diff * 2)));
  }

  function getSemesterOptions($type)
  {
      return (stripos($type, 'ganjil') !== false) ? [1, 3, 5, 7] : [2, 4, 6, 8];
  }

  $periodeInfo = parsePeriode($selectedPeriodeRaw);
  $selectedTahunAkademik = $periodeInfo['tahun'];
  $selectedSemesterText  = $periodeInfo['semester_text'];

  $periodeList = [];
  $qPeriode = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'periode_pendaftaran' AND is_active = 1 ORDER BY urutan ASC");

  if ($qPeriode) {
      while ($r = $qPeriode->fetch_assoc()) {
          $info = parsePeriode($r['value']);
          if ($info['tahun']) {
              $periodeList[] = [
                  'raw' => $r['value'],
                  'label' => $info['tahun'],
                  'type' => $info['semester_text']
              ];
          }
      }
  }

  $jurusanList = [];
  $qJurusan = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'program_studi' AND is_active = 1 ORDER BY urutan ASC");

  if ($qJurusan) {
      while ($r = $qJurusan->fetch_assoc()) {
          $jurusanList[] = $r['value'];
      }
  }

  $isSetupComplete = (
      $selectedPeriodeRaw !== '' &&
      $selectedJurusan !== '' &&
      $selectedTahunAkademik !== '' &&
      $selectedSemesterText !== '' &&
      $semesterAngka > 0 &&
      $namaMataKuliah !== '' &&
      $namaDosenManual !== ''
  );

  $totalMahasiswa = 0;
  $totalPages = 1;
  $offset = ($page - 1) * $perPage;
  $mahasiswaList = [];

  if ($isSetupComplete) {
      // ===== FIX 1: Filter berdasarkan tahun akademik saja (9 karakter pertama) =====
      $stmtCount = $conn->prepare("
          SELECT COUNT(*) AS total
          FROM mahasiswa
          WHERE LEFT(periode_pendaftaran, 9) = LEFT(?, 9)
            AND program_studi = ?
      ");

      $stmtCount->bind_param("ss", $selectedPeriodeRaw, $selectedJurusan);
      $stmtCount->execute();
      $resCount = $stmtCount->get_result();

      if ($resCount && $rc = $resCount->fetch_assoc()) {
          $totalMahasiswa = (int)$rc['total'];
      }

      $stmtCount->close();

      $totalPages = max(1, (int)ceil($totalMahasiswa / $perPage));

      if ($page > $totalPages) {
          $page = $totalPages;
          $offset = ($page - 1) * $perPage;
      }

      // ===== FIX 2: Query list mahasiswa dengan filter tahun akademik =====
      $stmtList = $conn->prepare("
          SELECT DISTINCT
              m.id_mahasiswa,
              m.nim,
              m.nama_mahasiswa,
              m.kelas,
              m.periode_pendaftaran,
              n.tugas,
              n.uts,
              n.uas,
              n.kehadiran,
              n.nilai_akhir,
              n.grade,
              n.keterangan
          FROM mahasiswa m
          LEFT JOIN nilai_mahasiswa n
              ON n.id_mahasiswa = m.id_mahasiswa
              AND n.tahun_akademik = ?
              AND n.semester_angka = ?
              AND n.nama_mata_kuliah = ?
          WHERE LEFT(m.periode_pendaftaran, 9) = LEFT(?, 9)
            AND m.program_studi = ?
          ORDER BY m.nama_mahasiswa ASC
          LIMIT ? OFFSET ?
      ");

      $stmtList->bind_param(
          "sisssii",
          $selectedTahunAkademik,
          $semesterAngka,
          $namaMataKuliah,
          $selectedPeriodeRaw,
          $selectedJurusan,
          $perPage,
          $offset
      );

      $stmtList->execute();
      $resList = $stmtList->get_result();

      if ($resList) {
          while ($r = $resList->fetch_assoc()) {
              // ===== FIX 3: Set badge_semester = semester filter, bukan hasil kalkulasi =====
              $r['badge_semester'] = $semesterAngka;
              // Tambahkan flag apakah nilai sudah diisi atau belum
              $r['is_filled'] = !empty($r['nilai_akhir']) && $r['nilai_akhir'] > 0;
              $mahasiswaList[] = $r;
          }
      }

      $stmtList->close();
  }

  $semesterOptions = [
      'ganjil' => getSemesterOptions('Ganjil'),
      'genap' => getSemesterOptions('Genap')
  ];
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

      <link rel="stylesheet" href="../../css/css_akademik/nilai/inputnilai.css?v=5.0">

      <script>
          window.__SEMESTER_OPTIONS__ = <?= json_encode($semesterOptions, JSON_UNESCAPED_UNICODE); ?>;

          (function () {
              try {
                  if (window.innerWidth > 860 && localStorage.getItem('ak_sidebar_collapsed') === '1') {
                      document.documentElement.classList.add('sidebar-collapsed-init');
                  }
              } catch (e) {}
          })();
      </script>
  </head>
  <body class="<?= $isSetupComplete ? 'is-ready' : ''; ?>">
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
                          <p class="page-subtitle">Kelola nilai mahasiswa per mata kuliah</p>
                      </div>
                  </div>
                  <div class="top-right">
                      <span class="user-greeting">Halo, <strong><?= htmlspecialchars($namaLogin); ?></strong></span>
                  </div>
              </header>

              <div class="content">
                  <section class="summary-strip">
                      <button type="button" class="summary-card clickable-card" id="btnOpenSetup">
                          <span class="summary-label">Periode Akademik</span>
                          <strong class="summary-value">
                              <?= $selectedTahunAkademik !== '' ? htmlspecialchars($selectedTahunAkademik) : 'Pilih periode'; ?>
                          </strong>
                      </button>
                      <button type="button" class="summary-card clickable-card" id="btnOpenSetup2">
                          <span class="summary-label">Jurusan</span>
                          <strong class="summary-value">
                              <?= $selectedJurusan !== '' ? htmlspecialchars($selectedJurusan) : 'Pilih jurusan'; ?>
                          </strong>
                      </button>
                      <button type="button" class="summary-card clickable-card" id="btnOpenSetup3">
                          <span class="summary-label">Mata Kuliah & Dosen</span>
                          <strong class="summary-value">
                              <?= $namaMataKuliah !== '' ? htmlspecialchars($namaMataKuliah) . ' • ' . htmlspecialchars($namaDosenManual) : 'Pilih matkul & dosen'; ?>
                          </strong>
                      </button>
                  </section>

                  <?php if (!$isSetupComplete): ?>
                      <section class="setup-empty card">
                          <div class="empty-box empty-box-large">
                              <div class="empty-icon">📋</div>
                              <strong>Konfigurasi belum lengkap.</strong><br>
                              <p style="margin:8px 0 0; color:var(--muted); font-size:11px;">
                                  Klik salah satu kotak di atas untuk mengatur periode, jurusan, semester, mata kuliah, dan dosen pengampu.
                              </p>
                          </div>
                      </section>
                  <?php else: ?>
                      <section class="nilai-header card">
                          <div class="nilai-header-content">
                              <span class="mini-label">Mata Kuliah</span>
                              <h2><?= htmlspecialchars($namaMataKuliah); ?></h2>
                              <p class="nilai-meta">
                                  <span class="meta-item"><strong>Dosen:</strong> <?= htmlspecialchars($namaDosenManual); ?></span>
                                  <span class="meta-separator">•</span>
                                  <span class="meta-item"><strong>Semester:</strong> <?= $semesterAngka; ?> (<?= htmlspecialchars($selectedSemesterText); ?>)</span>
                                  <span class="meta-separator">•</span>
                                  <span class="meta-item"><strong>Jurusan:</strong> <?= htmlspecialchars($selectedJurusan); ?></span>
                                  <span class="meta-separator">•</span>
                                  <span class="meta-item"><strong>Tahun:</strong> <?= htmlspecialchars($selectedTahunAkademik); ?></span>
                              </p>
                          </div>
                      </section>

                      <section class="nilai-panel card">
                          <div class="card-head card-head-flex">
                              <div>
                                  <h2>Daftar Mahasiswa</h2>
                                  <p class="section-subtitle">Isi nilai langsung dalam satu halaman. Maksimal 15 data per halaman.</p>
                              </div>
                              <div class="badge-soft"><?= number_format($totalMahasiswa); ?> Data</div>
                          </div>

                          <div class="card-body">
                              <?php if (empty($mahasiswaList)): ?>
                                  <div class="empty-box empty-box-large">
                                      <div class="empty-icon">🔍</div>
                                      Belum ada mahasiswa untuk filter yang dipilih.
                                  </div>
                              <?php else: ?>
                                  <form action="proses_inputnilai.php" method="post" id="formNilaiMassal">
                                      <input type="hidden" name="periode" value="<?= htmlspecialchars($selectedPeriodeRaw); ?>">
                                      <input type="hidden" name="jurusan" value="<?= htmlspecialchars($selectedJurusan); ?>">
                                      <input type="hidden" name="tahun_akademik" value="<?= htmlspecialchars($selectedTahunAkademik); ?>">
                                      <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemesterText); ?>">
                                      <input type="hidden" name="semester_angka" value="<?= $semesterAngka; ?>">
                                      <input type="hidden" name="nama_mata_kuliah" value="<?= htmlspecialchars($namaMataKuliah); ?>">
                                      <input type="hidden" name="nama_dosen_manual" value="<?= htmlspecialchars($namaDosenManual); ?>">
                                      <input type="hidden" name="page" value="<?= $page; ?>">

                                      <div class="nilai-list">
                                          <div class="nilai-row nilai-header-row">
                                              <div class="mhs-info mhs-info-header">
                                                  <span class="mhs-number">#</span>
                                                  <span>Nama Mahasiswa</span>
                                              </div>
                                              <div class="semester-badge-header">Smt</div>
                                              <label class="nilai-field field-header">
                                                  <span>Tugas<br><small>(25%)</small></span>
                                              </label>
                                              <label class="nilai-field field-header">
                                                  <span>UTS<br><small>(25%)</small></span>
                                              </label>
                                              <label class="nilai-field field-header">
                                                  <span>UAS<br><small>(35%)</small></span>
                                              </label>
                                              <label class="nilai-field field-header">
                                                  <span>LL<br><small>(15%)</small></span>
                                              </label>
                                              <label class="nilai-field readonly-field field-header">
                                                  <span>NA</span>
                                              </label>
                                              <label class="nilai-field small-field readonly-field field-header">
                                                  <span>Grade</span>
                                              </label>
                                              <label class="nilai-field ket-field readonly-field field-header">
                                                  <span>Ket</span>
                                              </label>
                                          </div>

                                          <?php foreach ($mahasiswaList as $i => $m):
                                              $idMhs = (int)$m['id_mahasiswa'];
                                              $t = $m['tugas'] !== null ? $m['tugas'] : '';
                                              $u = $m['uts'] !== null ? $m['uts'] : '';
                                              $ua = $m['uas'] !== null ? $m['uas'] : '';
                                              $k = $m['kehadiran'] !== null ? $m['kehadiran'] : '';
                                              $na = $m['nilai_akhir'] !== null ? $m['nilai_akhir'] : '';
                                              $g = $m['grade'] !== null ? $m['grade'] : '';
                                              $ket = $m['keterangan'] !== null ? $m['keterangan'] : '';
                                              
                                              // ===== FIX 4: Gunakan badge_semester dan is_filled yang sudah diset di PHP =====
                                              $badgeSemester = $m['badge_semester'] ?? $semesterAngka;
                                              $isFilled = $m['is_filled'] ?? false;
                                          ?>
                                          <div class="nilai-row" data-row="nilai">
                                              <input type="hidden" name="nilai[<?= $idMhs; ?>][id_mahasiswa]" value="<?= $idMhs; ?>">

                                              <div class="mhs-info">
                                                  <div class="mhs-number"><?= ($page - 1) * $perPage + $i + 1; ?></div>
                                                  <div class="mhs-detail">
                                                      <strong class="mhs-name"><?= htmlspecialchars($m['nama_mahasiswa']); ?></strong>
                                                      <span class="mhs-nim-kelas"><?= htmlspecialchars($m['nim']); ?> · <?= htmlspecialchars($m['kelas']); ?></span>
                                                  </div>
                                              </div>

                                              <div class="semester-cell">
                                                  <!-- ===== FIX 5: Badge pakai semesterAngka, warna berdasarkan is_filled ===== -->
                                                  <span class="semester-badge <?= $isFilled ? 'filled' : 'empty'; ?>">
                                                      <?= $badgeSemester; ?>
                                                  </span>
                                              </div>

                                              <label class="nilai-field">
                                                  <input type="number" step="0.01" min="0" max="100"
                                                        name="nilai[<?= $idMhs; ?>][tugas]"
                                                        value="<?= htmlspecialchars((string)$t); ?>"
                                                        class="score-input" data-score="tugas" placeholder="0">
                                              </label>

                                              <label class="nilai-field">
                                                  <input type="number" step="0.01" min="0" max="100"
                                                        name="nilai[<?= $idMhs; ?>][uts]"
                                                        value="<?= htmlspecialchars((string)$u); ?>"
                                                        class="score-input" data-score="uts" placeholder="0">
                                              </label>

                                              <label class="nilai-field">
                                                  <input type="number" step="0.01" min="0" max="100"
                                                        name="nilai[<?= $idMhs; ?>][uas]"
                                                        value="<?= htmlspecialchars((string)$ua); ?>"
                                                        class="score-input" data-score="uas" placeholder="0">
                                              </label>

                                              <label class="nilai-field">
                                                  <input type="number" step="0.01" min="0" max="100"
                                                        name="nilai[<?= $idMhs; ?>][kehadiran]"
                                                        value="<?= htmlspecialchars((string)$k); ?>"
                                                        class="score-input" data-score="kehadiran" placeholder="0">
                                              </label>

                                              <label class="nilai-field readonly-field">
                                                  <input type="number" step="0.01" min="0" max="100"
                                                        name="nilai[<?= $idMhs; ?>][nilai_akhir]"
                                                        value="<?= htmlspecialchars((string)$na); ?>"
                                                        class="result-input" data-score="nilai_akhir" readonly>
                                              </label>

                                              <label class="nilai-field small-field readonly-field">
                                                  <input type="text" name="nilai[<?= $idMhs; ?>][grade]"
                                                        value="<?= htmlspecialchars((string)$g); ?>"
                                                        class="result-input" data-score="grade" readonly>
                                              </label>

                                              <label class="nilai-field ket-field readonly-field">
                                                  <input type="text" name="nilai[<?= $idMhs; ?>][keterangan]"
                                                        value="<?= htmlspecialchars((string)$ket); ?>"
                                                        class="result-input" data-score="keterangan" readonly>
                                              </label>
                                          </div>
                                          <?php endforeach; ?>
                                      </div>

                                      <div class="form-actions massal-actions">
                                          <button type="submit" class="btn-save btn-save-large" id="btnSubmitNilai">
                                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                                  <polyline points="17 21 17 13 7 13 7 21"/>
                                                  <polyline points="7 3 7 8 15 8"/>
                                              </svg>
                                              Simpan Semua Nilai
                                          </button>
                                          <span class="save-hint">* Nilai & Grade dihitung otomatis • Server akan memvalidasi ulang saat submit</span>
                                      </div>
                                  </form>

                                  <?php if ($totalPages > 1):
                                      $pp = max(1, $page - 1);
                                      $np = min($totalPages, $page + 1);
                                      $baseQ = buildQuery([
                                          'periode' => $selectedPeriodeRaw,
                                          'jurusan' => $selectedJurusan,
                                          'semester_angka' => $semesterAngka,
                                          'nama_mata_kuliah' => $namaMataKuliah,
                                          'nama_dosen_manual' => $namaDosenManual
                                      ]);
                                  ?>
                                  <div class="pagination">
                                      <a class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>"
                                        href="<?= $page <= 1 ? '#' : "inputnilai.php?$baseQ&page=$pp"; ?>">
                                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                              <polyline points="15 18 9 12 15 6"/>
                                          </svg>
                                          Sebelumnya
                                      </a>
                                      <div class="page-info">
                                          Halaman <strong><?= $page; ?></strong> dari <strong><?= $totalPages; ?></strong>
                                          <span class="page-total">(<?= number_format($totalMahasiswa); ?> data)</span>
                                      </div>
                                      <a class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>"
                                        href="<?= $page >= $totalPages ? '#' : "inputnilai.php?$baseQ&page=$np"; ?>">
                                          Berikutnya
                                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                              <polyline points="9 18 15 12 9 6"/>
                                          </svg>
                                      </a>
                                  </div>
                                  <?php endif; ?>
                              <?php endif; ?>
                          </div>
                      </section>
                  <?php endif; ?>
              </div>
          </main>
      </div>

      <!-- Modal Setup -->
      <div class="modal" id="setupModal" aria-hidden="true">
          <div class="modal-overlay" id="setupModalOverlay"></div>
          <div class="modal-box">
              <div class="modal-head">
                  <h3>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path d="M12 20h9"/>
                          <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                      </svg>
                      Pengaturan Input
                  </h3>
                  <button type="button" class="modal-close" id="btnCloseSetup" aria-label="Tutup">×</button>
              </div>

              <form method="get" action="inputnilai.php" class="modal-body setup-form" id="setupForm">
                  <label class="field">
                      <span>Periode Akademik <span class="required">*</span></span>
                      <select name="periode" id="selectPeriode" required>
                          <option value="">-- Pilih Tahun Akademik --</option>
                          <?php foreach ($periodeList as $p): ?>
                              <option value="<?= htmlspecialchars($p['raw']); ?>"
                                      <?= $selectedPeriodeRaw === $p['raw'] ? 'selected' : ''; ?>
                                      data-semester-type="<?= htmlspecialchars($p['type']); ?>">
                                  <!-- ===== FIX 6: Hanya tampilkan tahun, hapus (Ganjil/Genap) di teks ===== -->
                                  <?= htmlspecialchars($p['label']); ?>
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </label>

                  <label class="field">
                      <span>Jurusan / Program Studi <span class="required">*</span></span>
                      <select name="jurusan" required>
                          <option value="">-- Pilih Jurusan --</option>
                          <?php foreach ($jurusanList as $j): ?>
                              <option value="<?= htmlspecialchars($j); ?>"
                                      <?= $selectedJurusan === $j ? 'selected' : ''; ?>>
                                  <?= htmlspecialchars($j); ?>
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </label>

                  <div class="semester-box">
                      <span class="field-title">Semester Aktif <span class="required">*</span></span>
                      <div class="semester-options" id="semesterOptions"></div>
                      <p class="semester-hint" id="semesterHint">Pilih periode terlebih dahulu</p>
                  </div>

                  <div class="manual-fields">
                      <label class="field">
                          <span>Nama Mata Kuliah <span class="required">*</span></span>
                          <input type="text" name="nama_mata_kuliah"
                                value="<?= htmlspecialchars($namaMataKuliah); ?>"
                                placeholder="Contoh: Pemrograman Web"
                                required maxlength="150">
                      </label>
                      <label class="field">
                          <span>Nama Dosen Pengampu <span class="required">*</span></span>
                          <input type="text" name="nama_dosen_manual"
                                value="<?= htmlspecialchars($namaDosenManual); ?>"
                                placeholder="Contoh: Rizky Pratama, S.Kom"
                                required maxlength="150">
                      </label>
                  </div>

                  <div class="modal-actions">
                      <button type="button" class="btn-cancel" id="btnCancelSetup">Batal</button>
                      <button type="submit" class="btn-save modal-submit">
                          Terapkan Filter
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                              <line x1="5" y1="12" x2="19" y2="12"/>
                              <polyline points="12 5 19 12 12 19"/>
                          </svg>
                      </button>
                  </div>
              </form>
          </div>
      </div>

      <!-- Confirmation Modal for Submit -->
      <div class="modal" id="confirmModal" aria-hidden="true">
          <div class="modal-overlay" id="confirmModalOverlay"></div>
          <div class="modal-box">
              <div class="modal-head">
                  <h3>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <circle cx="12" cy="12" r="10"/>
                          <line x1="12" y1="8" x2="12" y2="12"/>
                          <line x1="12" y1="16" x2="12.01" y2="16"/>
                      </svg>
                      Konfirmasi Penyimpanan
                  </h3>
                  <button type="button" class="modal-close" id="btnCloseConfirm" aria-label="Tutup">×</button>
              </div>
              <div class="modal-body">
                  <p style="margin:0 0 16px; font-size:12px; color:var(--text);">
                      Anda yakin ingin menyimpan semua nilai yang telah diisi?<br>
                      <strong>Data yang sudah tersimpan akan diperbarui.</strong>
                  </p>
                  <div class="modal-actions">
                      <button type="button" class="btn-cancel" id="btnCancelConfirm">Batal</button>
                      <button type="button" class="btn-save" id="btnConfirmSubmit">Ya, Simpan Sekarang</button>
                  </div>
              </div>
          </div>
      </div>

      <script>
          window.__FLASH__ = <?= json_encode(["tipe" => $tipe, "pesan" => $pesan], JSON_UNESCAPED_UNICODE); ?>;
          window.__SELECTED__ = {
              periode: <?= json_encode($selectedPeriodeRaw); ?>,
              periodeType: <?= json_encode($selectedSemesterText); ?>,
              semesterAngka: <?= json_encode($semesterAngka); ?>
          };
      </script>
      <script src="../../js/js_akademik/nilai/inputnilai.js?v=5.0"></script>
  </body>
  </html>