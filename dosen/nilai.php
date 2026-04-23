<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai dosen."));
  exit;
}

$idUser   = (int)($_SESSION['id_user'] ?? 0);
$username = $_SESSION['username'] ?? '';
$namaUser = $_SESSION['nama_lengkap'] ?? 'Dosen';

$dosen = [
  'id_dosen'      => 0,
  'kode_dosen'    => $username,
  'nama_dosen'    => $namaUser,
  'email'         => '-',
  'program_studi' => '-',
];

if ($idUser > 0) {
  $sqlDosen = "SELECT id_dosen, kode_dosen, nama_dosen, email, program_studi
               FROM dosen
               WHERE id_user = ?
               LIMIT 1";
  $stmtDosen = $conn->prepare($sqlDosen);

  if ($stmtDosen) {
    $stmtDosen->bind_param("i", $idUser);
    $stmtDosen->execute();
    $resDosen = $stmtDosen->get_result();

    if ($resDosen && ($rowDosen = $resDosen->fetch_assoc())) {
      $dosen = [
        'id_dosen'      => (int)($rowDosen['id_dosen'] ?? 0),
        'kode_dosen'    => $rowDosen['kode_dosen'] ?? $username,
        'nama_dosen'    => $rowDosen['nama_dosen'] ?? $namaUser,
        'email'         => $rowDosen['email'] ?? '-',
        'program_studi' => $rowDosen['program_studi'] ?? '-',
      ];
    }

    $stmtDosen->close();
  }
}

$rows = [];

if ($dosen['id_dosen'] > 0) {
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
      n.dibuat_pada,
      m.id_mahasiswa,
      m.nim,
      m.nama_mahasiswa,
      m.program_studi,
      m.kelas
    FROM nilai_mahasiswa n
    INNER JOIN mahasiswa m ON m.id_mahasiswa = n.id_mahasiswa
    WHERE n.id_dosen = ?
    ORDER BY n.dibuat_pada DESC, n.id_nilai DESC
  ";

  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $dosen['id_dosen']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
      }
    }

    $stmt->close();
  }
}

$totalData = count($rows);

renderDosenLayoutStart([
  'title'       => 'Dosen - Data Nilai',
  'page_title'  => 'Data Nilai Mahasiswa',
  'page_sub'    => 'Daftar mahasiswa yang sudah diberi nilai',
  'nama_tampil' => $dosen['nama_dosen'],
  'username'    => $dosen['kode_dosen'],
  'assetsBase'  => '..',
  'menu'        => 'nilai'
]);
?>

<link rel="stylesheet" href="../css/css_dosen/nilai.css?v=1">

<section class="nilai-page" id="dosenNilaiPage">
  <div class="nilai-hero">
    <div>
      <div class="nilai-badge">MENU NILAI DOSEN</div>
      <h1 class="nilai-title">Daftar Nilai Mahasiswa</h1>
      <p class="nilai-subtitle">
        Menampilkan data mahasiswa yang sudah dinilai oleh
        <strong><?= e($dosen['nama_dosen']) ?></strong>.
      </p>
    </div>

    <div class="nilai-hero-side">
      <div class="hero-mini-card">
        <div class="hero-mini-label">Total Data Nilai</div>
        <div class="hero-mini-value"><?= e($totalData) ?></div>
      </div>
    </div>
  </div>

  <div class="nilai-card">
    <div class="nilai-card-head">
      <div>
        <div class="section-title">Daftar Mahasiswa Ternilai</div>
        <div class="section-sub">Klik tombol detail untuk melihat rincian nilai mahasiswa.</div>
      </div>

      <div class="nilai-search-wrap">
        <input
          type="text"
          id="searchNilai"
          class="nilai-search"
          placeholder="Cari nama mahasiswa, NIM, kelas, semester..."
        >
      </div>
    </div>

    <div class="nilai-table-shell">
      <div class="nilai-table-wrap">
        <table class="nilai-table" id="nilaiTable">
          <thead>
            <tr>
              <th>No</th>
              <th>NIM</th>
              <th>Nama Mahasiswa</th>
              <th>Program Studi</th>
              <th>Kelas</th>
              <th>Tahun Akademik</th>
              <th>Semester</th>
              <th>Nilai Akhir</th>
              <th>Grade</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="nilaiTableBody">
            <?php if (!empty($rows)): ?>
              <?php $no = 1; ?>
              <?php foreach ($rows as $row): ?>
                <?php
                  $semester = trim((string)($row['semester'] ?? '-'));
                  $tahun    = trim((string)($row['tahun_akademik'] ?? '-'));
                  $tugas    = number_format((float)($row['tugas'] ?? 0), 2);
                  $uts      = number_format((float)($row['uts'] ?? 0), 2);
                  $uas      = number_format((float)($row['uas'] ?? 0), 2);
                  $hadir    = number_format((float)($row['kehadiran'] ?? 0), 2);
                  $akhir    = number_format((float)($row['nilai_akhir'] ?? 0), 2);
                  $grade    = trim((string)($row['grade'] ?? '-'));
                  $ket      = trim((string)($row['keterangan'] ?? '-'));
                ?>
                <tr>
                  <td class="center"><?= $no++; ?></td>
                  <td class="center"><?= e($row['nim'] ?? '-') ?></td>
                  <td class="left"><?= e($row['nama_mahasiswa'] ?? '-') ?></td>
                  <td class="left"><?= e($row['program_studi'] ?? '-') ?></td>
                  <td class="center"><?= e($row['kelas'] ?? '-') ?></td>
                  <td class="center"><?= e($tahun) ?></td>
                  <td class="center"><?= e($semester) ?></td>
                  <td class="center"><?= e($akhir) ?></td>
                  <td class="center"><?= e($grade !== '' ? $grade : '-') ?></td>
                  <td class="center">
                    <button
                      type="button"
                      class="btn-detail"
                      data-id="<?= e($row['id_nilai']) ?>"
                      data-nim="<?= e($row['nim'] ?? '-') ?>"
                      data-nama="<?= e($row['nama_mahasiswa'] ?? '-') ?>"
                      data-prodi="<?= e($row['program_studi'] ?? '-') ?>"
                      data-kelas="<?= e($row['kelas'] ?? '-') ?>"
                      data-tahun="<?= e($tahun) ?>"
                      data-semester="<?= e($semester) ?>"
                      data-tugas="<?= e($tugas) ?>"
                      data-uts="<?= e($uts) ?>"
                      data-uas="<?= e($uas) ?>"
                      data-kehadiran="<?= e($hadir) ?>"
                      data-akhir="<?= e($akhir) ?>"
                      data-grade="<?= e($grade !== '' ? $grade : '-') ?>"
                      data-keterangan="<?= e($ket !== '' ? $ket : '-') ?>"
                    >
                      Detail
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr class="empty-row">
                <td colspan="10">
                  Belum ada data nilai mahasiswa untuk dosen ini.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<div class="nilai-modal" id="nilaiModal" aria-hidden="true">
  <div class="nilai-modal-backdrop" id="nilaiModalBackdrop"></div>

  <div class="nilai-modal-card" role="dialog" aria-modal="true" aria-labelledby="nilaiModalTitle">
    <div class="nilai-modal-head">
      <div>
        <div class="modal-kicker">DETAIL NILAI</div>
        <h2 class="nilai-modal-title" id="nilaiModalTitle">Rincian Nilai Mahasiswa</h2>
      </div>

      <button type="button" class="modal-close" id="nilaiModalClose" aria-label="Tutup">
        ×
      </button>
    </div>

    <div class="nilai-modal-body">
      <div class="detail-grid">
        <div class="detail-item">
          <span>NIM</span>
          <strong id="detailNim">-</strong>
        </div>
        <div class="detail-item">
          <span>Nama Mahasiswa</span>
          <strong id="detailNama">-</strong>
        </div>
        <div class="detail-item">
          <span>Program Studi</span>
          <strong id="detailProdi">-</strong>
        </div>
        <div class="detail-item">
          <span>Kelas</span>
          <strong id="detailKelas">-</strong>
        </div>
        <div class="detail-item">
          <span>Tahun Akademik</span>
          <strong id="detailTahun">-</strong>
        </div>
        <div class="detail-item">
          <span>Semester</span>
          <strong id="detailSemester">-</strong>
        </div>
      </div>

      <div class="score-grid">
        <div class="score-box">
          <div class="score-label">Tugas</div>
          <div class="score-value" id="detailTugas">0.00</div>
        </div>
        <div class="score-box">
          <div class="score-label">UTS</div>
          <div class="score-value" id="detailUts">0.00</div>
        </div>
        <div class="score-box">
          <div class="score-label">UAS</div>
          <div class="score-value" id="detailUas">0.00</div>
        </div>
        <div class="score-box">
          <div class="score-label">Kehadiran</div>
          <div class="score-value" id="detailKehadiran">0.00</div>
        </div>
        <div class="score-box highlight">
          <div class="score-label">Nilai Akhir</div>
          <div class="score-value" id="detailAkhir">0.00</div>
        </div>
        <div class="score-box grade-box">
          <div class="score-label">Grade</div>
          <div class="score-value" id="detailGrade">-</div>
        </div>
      </div>

      <div class="keterangan-box">
        <div class="keterangan-title">Keterangan</div>
        <div class="keterangan-text" id="detailKeterangan">-</div>
      </div>
    </div>
  </div>
</div>

<script src="../js/js_dosen/nilai.js?v=1"></script>

<?php
renderDosenLayoutEnd([
  'assetsBase' => '..'
]);