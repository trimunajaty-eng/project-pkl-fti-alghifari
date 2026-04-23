<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// wajib login mahasiswa
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: ../login.php?pesan=" . urlencode("Silakan login sebagai mahasiswa."));
  exit;
}

$nim  = $_SESSION['username'] ?? '';
$nama = $_SESSION['nama'] ?? 'Mahasiswa';

if ($nim === '') {
  header("Location: ../login.php?pesan=" . urlencode("Sesi tidak valid. Silakan login ulang."));
  exit;
}

/**
 * cek status akun
 */
$status_user = 'aktif';
if ($nim !== '') {
  $stU = $conn->prepare("SELECT status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
  if ($stU) {
    $stU->bind_param("s", $nim);
    $stU->execute();
    $rsU = $stU->get_result();
    if ($rsU && ($rowU = $rsU->fetch_assoc())) {
      $status_user = ($rowU['status'] ?? 'aktif');
    }
    $stU->close();
  }
}

$is_blocked = false;
$blocked_msg = "";
if ($status_user === 'nonaktif') {
  $is_blocked = true;
  $blocked_msg = "Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.";
}

/**
 * ambil data mahasiswa
 */
$m = [
  'id_mahasiswa'     => 0,
  'nim'              => $nim,
  'nama_mahasiswa'   => $nama,
  'program_studi'    => '-',
  'kelas'            => '-',
  'angkatan'         => '-',
  'status_mahasiswa' => 'AKTIF',
];

if ($nim !== '') {
  $sql = "SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas, tanggal_registrasi
          FROM mahasiswa
          WHERE nim=?
          LIMIT 1";
  $st = $conn->prepare($sql);
  if ($st) {
    $st->bind_param("s", $nim);
    $st->execute();
    $rs = $st->get_result();
    if ($rs && ($row = $rs->fetch_assoc())) {
      $m['id_mahasiswa']   = (int)($row['id_mahasiswa'] ?? 0);
      $m['nim']            = $row['nim'] ?? $m['nim'];
      $m['nama_mahasiswa'] = $row['nama_mahasiswa'] ?? $m['nama_mahasiswa'];
      $m['program_studi']  = $row['program_studi'] ?? $m['program_studi'];
      $m['kelas']          = $row['kelas'] ?? $m['kelas'];

      if (!empty($row['tanggal_registrasi'])) {
        $m['angkatan'] = date("Y", strtotime($row['tanggal_registrasi']));
      }
    }
    $st->close();
  }
}

/**
 * helper semester
 */
function semesterNumberLabel($semester)
{
  $s = strtolower(trim((string)$semester));
  if ($s === 'ganjil') return 'Semester 1';
  if ($s === 'genap') return 'Semester 2';
  return 'Semester 1';
}

/**
 * ambil data nilai
 */
$nilaiRows = [];

if ($m['id_mahasiswa'] > 0) {
  $sqlNilai = "
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
    ORDER BY n.tahun_akademik DESC,
             FIELD(LOWER(n.semester), 'genap', 'ganjil'),
             n.id_nilai ASC
  ";

  $stNilai = $conn->prepare($sqlNilai);
  if ($stNilai) {
    $stNilai->bind_param("i", $m['id_mahasiswa']);
    $stNilai->execute();
    $rsNilai = $stNilai->get_result();

    if ($rsNilai) {
      while ($row = $rsNilai->fetch_assoc()) {
        $nilaiRows[] = $row;
      }
    }
    $stNilai->close();
  }
}

/**
 * kelompokkan per semester
 */
$semesterGroups = [];

foreach ($nilaiRows as $row) {
  $tahun = trim((string)($row['tahun_akademik'] ?? ''));
  $semester = trim((string)($row['semester'] ?? ''));
  $key = $tahun . '|' . $semester;

  if (!isset($semesterGroups[$key])) {
    $semesterGroups[$key] = [
      'tahun_akademik' => $tahun,
      'semester'       => $semester,
      'semester_label' => semesterNumberLabel($semester),
      'rows'           => [],
      'total_sks'      => 0,
      'ip_semester'    => '-',
    ];
  }

  $semesterGroups[$key]['rows'][] = $row;
}

/**
 * jika belum ada data, tetap tampil 1 panel kosong
 */
if (empty($semesterGroups)) {
  $semesterGroups['default'] = [
    'tahun_akademik' => '2026/2027',
    'semester'       => 'ganjil',
    'semester_label' => 'Semester 1',
    'rows'           => [],
    'total_sks'      => 0,
    'ip_semester'    => '-',
  ];
}

/**
 * pagination semester
 * ubah ke 2 jika mau 2 semester per halaman
 */
$semesterPerPage = 1;

$groupList = array_values($semesterGroups);
$totalGroup = count($groupList);
$totalPages = max(1, (int)ceil($totalGroup / $semesterPerPage));
$page = max(1, (int)($_GET['page'] ?? 1));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $semesterPerPage;
$semesterPageItems = array_slice($groupList, $offset, $semesterPerPage);

renderMahasiswaLayoutStart([
  "title"       => "Mahasiswa - Nilai Semester",
  "page_title"  => "Nilai Semester",
  "page_sub"    => "Lihat rekap nilai semester mahasiswa",
  "menu"        => "nilai",
  "nama_tampil" => $m['nama_mahasiswa'],
  "username"    => $m['nim'],
  "assetsBase"  => "..",
  "basePath"    => "",
  "is_blocked"  => $is_blocked,
  "blocked_msg" => $blocked_msg,
]);
?>

<link rel="stylesheet" href="../css/css_mahasiswa/nilai.css?v=5">

<section class="nilai-page"
         id="nilaiPage"
         data-api="api_akun.php?action=status"
         data-blocked-msg="<?= e($blocked_msg ?: 'Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.') ?>">

  <div class="nilai-card">
    <div class="nilai-head">
      <div>
        <div class="nilai-title">Rekap Nilai Semester</div>
        <div class="nilai-sub">Data nilai mahasiswa ditampilkan per semester.</div>
      </div>
    </div>

    <?php foreach ($semesterPageItems as $group): ?>
      <div class="nilai-box">
        <div class="nilai-semester-title">
          <?= e($group['tahun_akademik']) ?> <?= e($group['semester_label']) ?>
        </div>

        <div class="nilai-table-shell">
          <div class="nilai-table-wrap">
            <table class="nilai-table">
              <thead>
                <tr>
                  <th style="width:70px;">No</th>
                  <th style="width:120px;">Kode MK</th>
                  <th style="min-width:220px;">Mata Kuliah</th>
                  <th style="width:80px;">SKS</th>
                  <th style="width:90px;">UTS</th>
                  <th style="width:90px;">UAS</th>
                  <th style="width:90px;">LL</th>
                  <th style="width:90px;">NA</th>
                  <th style="min-width:220px;">Dosen Pengajar</th>
                  <th style="min-width:140px;">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($group['rows'])): ?>
                  <?php
                  $no = 1;
                  $totalSks = 0;
                  foreach ($group['rows'] as $row):
                    $kodeMk = '-';
                    $mataKuliah = '-';
                    $sks = '-';
                    $uts = number_format((float)($row['uts'] ?? 0), 2);
                    $uas = number_format((float)($row['uas'] ?? 0), 2);
                    $ll  = number_format((float)($row['tugas'] ?? 0), 2);
                    $na  = trim((string)($row['grade'] ?? '-'));
                    $dosen = trim((string)($row['nama_dosen'] ?? ''));
                    $ket = trim((string)($row['keterangan'] ?? ''));
                  ?>
                    <tr>
                      <td class="center"><?= $no++; ?></td>
                      <td><?= e($kodeMk); ?></td>
                      <td class="left"><?= e($mataKuliah); ?></td>
                      <td class="center"><?= e($sks); ?></td>
                      <td class="center"><?= e($uts); ?></td>
                      <td class="center"><?= e($uas); ?></td>
                      <td class="center"><?= e($ll); ?></td>
                      <td class="center"><?= e($na !== '' ? $na : '-'); ?></td>
                      <td class="left"><?= e($dosen !== '' ? $dosen : '-'); ?></td>
                      <td><?= e($ket !== '' ? $ket : '-'); ?></td>
                    </tr>
                  <?php endforeach; ?>

                  <tr class="summary-row">
                    <td colspan="3" class="right strong">Total</td>
                    <td class="center strong"><?= e($totalSks > 0 ? $totalSks : '-') ?></td>
                    <td colspan="6"></td>
                  </tr>

                  <tr class="summary-row">
                    <td colspan="3" class="right strong">IP Semester</td>
                    <td class="center strong">-</td>
                    <td colspan="6"></td>
                  </tr>
                <?php else: ?>
                  <tr>
                    <td class="center">1</td>
                    <td>-</td>
                    <td class="left">-</td>
                    <td class="center">-</td>
                    <td class="center">-</td>
                    <td class="center">-</td>
                    <td class="center">-</td>
                    <td class="center">-</td>
                    <td class="left">-</td>
                    <td>-</td>
                  </tr>

                  <tr class="summary-row">
                    <td colspan="3" class="right strong">Total</td>
                    <td class="center strong">-</td>
                    <td colspan="6"></td>
                  </tr>

                  <tr class="summary-row">
                    <td colspan="3" class="right strong">IP Semester</td>
                    <td class="center strong">-</td>
                    <td colspan="6"></td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="nilai-pagination">
            <?php if ($page > 1): ?>
              <a class="page-nav" href="nilai.php?page=<?= $page - 1; ?>">‹</a>
            <?php else: ?>
              <span class="page-nav disabled">‹</span>
            <?php endif; ?>

            <div class="page-numbers">
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="nilai.php?page=<?= $i; ?>" class="page-number <?= $i === $page ? 'active' : ''; ?>">
                  <?= $i; ?>
                </a>
              <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
              <a class="page-nav" href="nilai.php?page=<?= $page + 1; ?>">›</a>
            <?php else: ?>
              <span class="page-nav disabled">›</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script src="../js/js_mahasiswa/nilai.js?v=5"></script>

<?php
renderMahasiswaLayoutEnd([
  "assetsBase" => ".."
]);
?>