<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// Wajib login mahasiswa
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: ../login.php?pesan=" . urlencode("Silakan login sebagai mahasiswa."));
  exit;
}

$nim = $_SESSION['username'] ?? '';
$nama_session = $_SESSION['nama'] ?? 'Mahasiswa';

if ($nim === '') {
  header("Location: ../login.php?pesan=" . urlencode("Sesi tidak valid. Silakan login ulang."));
  exit;
}

/**
 * =========================
 * CEK STATUS AKUN
 * =========================
 */
$status_user = 'aktif';
$stU = $conn->prepare("SELECT status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
if ($stU) {
  $stU->bind_param("s", $nim);
  $stU->execute();
  $rsU = $stU->get_result();
  if ($rsU && ($rowU = $rsU->fetch_assoc())) {
    $status_user = $rowU['status'] ?? 'aktif';
  }
  $stU->close();
}

$is_blocked = false;
$blocked_msg = "";
if ($status_user === 'nonaktif') {
  $is_blocked = true;
  $blocked_msg = "Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.";
}

/**
 * =========================
 * AMBIL DATA MAHASISWA
 * =========================
 */
$m = [
  'id_mahasiswa'     => 0,
  'nim'              => $nim,
  'nama_mahasiswa'   => $nama_session,
  'program_studi'    => '-',
  'kelas'            => '-',
  'angkatan'         => '-',
  'jenis_kelamin'    => '-',
  'email'            => '-',
  'hp'               => '-',
];

$stmtMhs = $conn->prepare("
  SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas, tanggal_registrasi, jenis_kelamin, email, hp
  FROM mahasiswa
  WHERE nim = ?
  LIMIT 1
");
if ($stmtMhs) {
  $stmtMhs->bind_param("s", $nim);
  $stmtMhs->execute();
  $rsMhs = $stmtMhs->get_result();
  if ($rsMhs && ($row = $rsMhs->fetch_assoc())) {
    $m['id_mahasiswa']   = (int)($row['id_mahasiswa'] ?? 0);
    $m['nim']            = $row['nim'] ?? $m['nim'];
    $m['nama_mahasiswa'] = $row['nama_mahasiswa'] ?? $m['nama_mahasiswa'];
    $m['program_studi']  = $row['program_studi'] ?? $m['program_studi'];
    $m['kelas']          = $row['kelas'] ?? $m['kelas'];
    $m['jenis_kelamin']  = $row['jenis_kelamin'] ?? $m['jenis_kelamin'];
    $m['email']          = !empty($row['email']) ? $row['email'] : '-';
    $m['hp']             = !empty($row['hp']) ? $row['hp'] : '-';

    if (!empty($row['tanggal_registrasi'])) {
      $m['angkatan'] = date("Y", strtotime($row['tanggal_registrasi']));
    }
  }
  $stmtMhs->close();
}

/**
 * =========================
 * FILTER PERIODE
 * Menggunakan format gabungan:
 * 2026/2027 Ganjil
 * =========================
 */
$periodeList = [];
$qPeriode = $conn->query("SELECT value FROM master_opsi_dropdown WHERE grup = 'periode_pendaftaran' AND is_active = 1 ORDER BY urutan ASC");
if ($qPeriode) {
  while ($r = $qPeriode->fetch_assoc()) {
    $periodeList[] = $r['value'];
  }
}

$selectedPeriode = trim($_GET['periode'] ?? '');
$selectedTahunAkademik = '';
$selectedSemester = '';

if ($selectedPeriode !== '') {
  if (preg_match('/^(.+?)\s+(Ganjil|Genap)$/i', $selectedPeriode, $matches)) {
    $selectedTahunAkademik = trim($matches[1]);
    $selectedSemester = trim($matches[2]);
  }
}

$qSearch = trim($_GET['q'] ?? '');

/**
 * =========================
 * AMBIL DATA NILAI MAHASISWA
 * =========================
 */
$nilaiRows = [];
$totalNilai = 0;
$rataNilai = 0;
$jumlahLulus = 0;
$jumlahBelum = 0;
$gradeTerbaik = '-';
$periodeTerbaru = '-';

if ($m['id_mahasiswa'] > 0) {
  $sql = "
    SELECT
      n.id_nilai,
      n.tahun_akademik,
      n.semester,
      n.tugas,
      n.uts,
      n.uas,
      n.kehadiran,
      n.nilai_akhir,
      n.grade,
      n.keterangan,
      d.nama_dosen,
      d.kode_dosen
    FROM nilai_mahasiswa n
    LEFT JOIN dosen d ON d.id_dosen = n.id_dosen
    WHERE n.id_mahasiswa = ?
  ";

  $types = "i";
  $params = [$m['id_mahasiswa']];

  if ($selectedTahunAkademik !== '' && $selectedSemester !== '') {
    $sql .= " AND n.tahun_akademik = ? AND n.semester = ? ";
    $types .= "ss";
    $params[] = $selectedTahunAkademik;
    $params[] = $selectedSemester;
  }

  if ($qSearch !== '') {
    $sql .= " AND (n.grade LIKE ? OR n.keterangan LIKE ? OR d.nama_dosen LIKE ?) ";
    $types .= "sss";
    $like = "%" . $qSearch . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
  }

  $sql .= " ORDER BY n.tahun_akademik DESC, FIELD(n.semester, 'Genap', 'Ganjil'), n.id_nilai DESC ";

  $stmtNilai = $conn->prepare($sql);
  if ($stmtNilai) {
    $stmtNilai->bind_param($types, ...$params);
    $stmtNilai->execute();
    $rsNilai = $stmtNilai->get_result();

    if ($rsNilai) {
      while ($row = $rsNilai->fetch_assoc()) {
        $nilaiRows[] = $row;
      }
    }

    $stmtNilai->close();
  }
}

/**
 * =========================
 * RINGKASAN
 * =========================
 */
if (!empty($nilaiRows)) {
  $totalNilai = count($nilaiRows);

  $sumNilai = 0;
  $bestGradeOrder = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1];
  $bestGradeScore = 0;

  foreach ($nilaiRows as $row) {
    $sumNilai += (float)($row['nilai_akhir'] ?? 0);

    $ket = strtolower(trim((string)($row['keterangan'] ?? '')));
    if ($ket === 'lulus') $jumlahLulus++;
    else $jumlahBelum++;

    $g = strtoupper(trim((string)($row['grade'] ?? '')));
    $score = $bestGradeOrder[$g] ?? 0;
    if ($score > $bestGradeScore) {
      $bestGradeScore = $score;
      $gradeTerbaik = $g !== '' ? $g : '-';
    }
  }

  $rataNilai = $totalNilai > 0 ? $sumNilai / $totalNilai : 0;
  $periodeTerbaru = $nilaiRows[0]['tahun_akademik'] . ' ' . $nilaiRows[0]['semester'];
}

renderMahasiswaLayoutStart([
  "title"       => "Mahasiswa - Nilai",
  "page_title"  => "Nilai Saya",
  "page_sub"    => "Halo, " . $m['nama_mahasiswa'],
  "menu"        => "nilai",
  "nama_tampil" => $m['nama_mahasiswa'],
  "username"    => $m['nim'],
  "assetsBase"  => "..",
  "basePath"    => "",
  "is_blocked"  => $is_blocked,
  "blocked_msg" => $blocked_msg,
]);

echo '<link rel="stylesheet" href="../css/css_mahasiswa/nilai.css?v=1">';
?>

<section class="nilai-page"
         id="nilaiPage"
         data-api="api_akun.php?action=status"
         data-blocked-msg="<?= e($blocked_msg ?: 'Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.') ?>">

  <section class="hero">
    <div class="hero-top">
      <div>
        <div class="hero-title">Rekap Nilai Mahasiswa</div>
        <div class="hero-sub">Lihat ringkasan dan detail nilai yang sudah diinput oleh pihak akademik/dosen.</div>
      </div>

      <div class="hero-pill">
        <span class="dot"></span>
        Status Data: <b><?= !empty($nilaiRows) ? 'Tersedia' : 'Belum Ada' ?></b>
      </div>
    </div>

    <div class="hero-cards">
      <div class="hcard">
        <div class="hlabel">Nama Mahasiswa</div>
        <div class="hvalue"><?= e($m['nama_mahasiswa']) ?></div>
        <div class="hmeta">NIM: <b><?= e($m['nim']) ?></b></div>
      </div>

      <div class="hcard">
        <div class="hlabel">Program Studi</div>
        <div class="hvalue"><?= e($m['program_studi']) ?></div>
        <div class="hmeta">Kelas: <b><?= e($m['kelas']) ?></b></div>
      </div>

      <div class="hcard">
        <div class="hlabel">Periode Terbaru</div>
        <div class="hvalue"><?= e($periodeTerbaru) ?></div>
        <div class="hmeta">Angkatan: <b><?= e($m['angkatan']) ?></b></div>
      </div>
    </div>
  </section>

  <section class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Jumlah Data Nilai</div>
      <div class="stat-value"><?= e($totalNilai) ?></div>
      <div class="stat-meta">Total nilai yang tersedia</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Rata-rata Nilai</div>
      <div class="stat-value"><?= number_format((float)$rataNilai, 2) ?></div>
      <div class="stat-meta">Rerata nilai akhir</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Jumlah Lulus</div>
      <div class="stat-value"><?= e($jumlahLulus) ?></div>
      <div class="stat-meta">Status keterangan lulus</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Grade Terbaik</div>
      <div class="stat-value"><?= e($gradeTerbaik) ?></div>
      <div class="stat-meta">Grade tertinggi yang diperoleh</div>
    </div>
  </section>

  <section class="grid">
    <div class="card">
      <div class="card-title">Filter Nilai</div>
      <div class="card-sub">Saring data berdasarkan periode akademik atau kata kunci tertentu.</div>

      <form method="get" action="nilai.php" class="filter-form">
        <div class="filter-grid">
          <label class="fgroup">
            <span>Periode Akademik</span>
            <select name="periode">
              <option value="">-- Semua Periode --</option>
              <?php foreach ($periodeList as $periode): ?>
                <option value="<?= e($periode) ?>" <?= $selectedPeriode === $periode ? 'selected' : '' ?>>
                  <?= e($periode) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label class="fgroup">
            <span>Pencarian</span>
            <input type="text" name="q" value="<?= e($qSearch) ?>" placeholder="Cari grade, status, atau dosen...">
          </label>
        </div>

        <div class="card-actions">
          <button type="submit" class="btn">Terapkan Filter</button>
          <a class="btn ghost" href="nilai.php">Reset</a>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="card-title">Ringkasan Akademik</div>
      <div class="card-sub">Informasi singkat hasil nilai mahasiswa.</div>

      <div class="info">
        <div class="info-row">
          <span class="info-k">Nama</span>
          <span class="info-v"><?= e($m['nama_mahasiswa']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">NIM</span>
          <span class="info-v"><?= e($m['nim']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Program Studi</span>
          <span class="info-v"><?= e($m['program_studi']) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Jumlah Nilai</span>
          <span class="info-v"><?= e($totalNilai) ?> data</span>
        </div>
        <div class="info-row">
          <span class="info-k">Rata-rata Nilai</span>
          <span class="info-v"><?= number_format((float)$rataNilai, 2) ?></span>
        </div>
        <div class="info-row">
          <span class="info-k">Grade Terbaik</span>
          <span class="info-v"><?= e($gradeTerbaik) ?></span>
        </div>
      </div>
    </div>
  </section>

  <section class="card">
    <div class="card-title">Daftar Nilai</div>
    <div class="card-sub">Berikut data nilai yang berhasil ditampilkan untuk akun mahasiswa ini.</div>

    <?php if (!empty($nilaiRows)): ?>
      <div class="table-wrap">
        <table class="nilai-table">
          <thead>
            <tr>
              <th>Periode</th>
              <th>Dosen</th>
              <th>Tugas</th>
              <th>UTS</th>
              <th>UAS</th>
              <th>Kehadiran</th>
              <th>Nilai Akhir</th>
              <th>Grade</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($nilaiRows as $row): ?>
              <?php
                $periodeGabung = trim(($row['tahun_akademik'] ?? '-') . ' ' . ($row['semester'] ?? '-'));
                $namaDosen = trim((string)($row['nama_dosen'] ?? ''));
                $kodeDosen = trim((string)($row['kode_dosen'] ?? ''));
                $statusKet = trim((string)($row['keterangan'] ?? '-'));
                $statusLower = strtolower($statusKet);
              ?>
              <tr>
                <td>
                  <div class="cell-main"><?= e($periodeGabung) ?></div>
                  <div class="cell-sub"><?= e($row['semester'] ?? '-') ?></div>
                </td>
                <td>
                  <div class="cell-main"><?= e($namaDosen !== '' ? $namaDosen : '-') ?></div>
                  <div class="cell-sub"><?= e($kodeDosen !== '' ? $kodeDosen : '-') ?></div>
                </td>
                <td><?= e(number_format((float)($row['tugas'] ?? 0), 2)) ?></td>
                <td><?= e(number_format((float)($row['uts'] ?? 0), 2)) ?></td>
                <td><?= e(number_format((float)($row['uas'] ?? 0), 2)) ?></td>
                <td><?= e(number_format((float)($row['kehadiran'] ?? 0), 2)) ?></td>
                <td><strong><?= e(number_format((float)($row['nilai_akhir'] ?? 0), 2)) ?></strong></td>
                <td>
                  <span class="grade-badge"><?= e($row['grade'] ?? '-') ?></span>
                </td>
                <td>
                  <span class="status-badge <?= $statusLower === 'lulus' ? 'ok' : 'wait' ?>">
                    <?= e($statusKet !== '' ? $statusKet : '-') ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-title">Belum ada data nilai</div>
        <div class="empty-sub">Nilai kamu belum tersedia atau tidak cocok dengan filter yang dipilih.</div>
      </div>
    <?php endif; ?>
  </section>
</section>

<?php
echo '<script src="../js/js_mahasiswa/nilai.js?v=1"></script>';

renderMahasiswaLayoutEnd([
  "assetsBase" => ".."
]);