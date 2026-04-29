<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// Wajib login mahasiswa
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

// Cek status akun
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

// Ambil data mahasiswa
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
    $sql = "SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, kelas, tanggal_registrasi, periode_pendaftaran
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
            $m['periode_pendaftaran'] = $row['periode_pendaftaran'] ?? '';

            if (!empty($row['tanggal_registrasi'])) {
                $m['angkatan'] = date("Y", strtotime($row['tanggal_registrasi']));
            }
        }
        $st->close();
    }
}

// Helper: konversi semester text ke angka untuk sorting
function semesterToNumber($semester)
{
    $s = strtolower(trim((string)$semester));
    if ($s === 'ganjil') return 1;
    if ($s === 'genap') return 2;
    return 1;
}

// Helper: label semester untuk display
function semesterNumberLabel($semester)
{
    $s = strtolower(trim((string)$semester));
    if ($s === 'ganjil') return 'Semester 1';
    if ($s === 'genap') return 'Semester 2';
    return 'Semester 1';
}

// Ambil data nilai
$nilaiRows = [];

if ($m['id_mahasiswa'] > 0) {
    // Query mengambil semua nilai yang sudah diinput akademik
    $sqlNilai = "
        SELECT
            n.id_nilai,
            n.tahun_akademik,
            n.semester,
            n.semester_angka,
            n.nama_mata_kuliah,
            n.nama_dosen_manual,
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
        ORDER BY 
            n.tahun_akademik DESC,
            semesterToNumber(n.semester) DESC,
            n.semester_angka DESC,
            n.nama_mata_kuliah ASC
    ";

    // Register custom function untuk ORDER BY jika perlu
    // Atau gunakan FIELD seperti sebelumnya
    $sqlNilai = "
        SELECT
            n.id_nilai,
            n.tahun_akademik,
            n.semester,
            n.semester_angka,
            n.nama_mata_kuliah,
            n.nama_dosen_manual,
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
        ORDER BY 
            n.tahun_akademik DESC,
            FIELD(LOWER(n.semester), 'genap', 'ganjil'),
            n.semester_angka DESC,
            n.nama_mata_kuliah ASC
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

// Kelompokkan per semester (tahun + semester text)
$semesterGroups = [];

foreach ($nilaiRows as $row) {
    $tahun = trim((string)($row['tahun_akademik'] ?? ''));
    $semester = trim((string)($row['semester'] ?? ''));
    $semesterAngka = (int)($row['semester_angka'] ?? 0);
    
    // Key unik: tahun|semester|semester_angka agar lebih spesifik
    $key = $tahun . '|' . $semester . '|' . $semesterAngka;

    if (!isset($semesterGroups[$key])) {
        $semesterGroups[$key] = [
            'tahun_akademik' => $tahun,
            'semester'       => $semester,
            'semester_angka' => $semesterAngka,
            'semester_label' => 'Semester ' . $semesterAngka,
            'rows'           => [],
        ];
    }

    $semesterGroups[$key]['rows'][] = $row;
}

// Jika belum ada data, tampilkan placeholder
if (empty($semesterGroups)) {
    $periodeMhs = $m['periode_pendaftaran'] ?? '';
    $defaultTahun = !empty($periodeMhs) ? substr($periodeMhs, 0, 9) : '2026/2027';
    
    $semesterGroups['default'] = [
        'tahun_akademik' => $defaultTahun,
        'semester'       => 'ganjil',
        'semester_angka' => 1,
        'semester_label' => 'Semester 1',
        'rows'           => [],
    ];
}

// Pagination: 1 semester per halaman
$semesterPerPage = 1;
$groupList = array_values($semesterGroups);
$totalGroup = count($groupList);
$totalPages = max(1, (int)ceil($totalGroup / $semesterPerPage));
$page = max(1, (int)($_GET['page'] ?? 1));

if ($page > $totalPages) {
    $page = $totalPages;
}

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

<link rel="stylesheet" href="../css/css_mahasiswa/nilai.css?v=6.0">

<section class="nilai-page"
         id="nilaiPage"
         data-api="api_akun.php?action=status"
         data-blocked-msg="<?= e($blocked_msg ?: 'Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.') ?>">

    <div class="nilai-card">
        <div class="nilai-head">
            <div>
                <div class="nilai-title">Rekap Nilai Semester</div>
                <div class="nilai-sub">Data nilai ditampilkan per semester. Pilih halaman untuk melihat semester lain.</div>
            </div>
        </div>

        <?php foreach ($semesterPageItems as $group): ?>
            <div class="nilai-box">
                <div class="nilai-semester-title">
                    <?= e($group['tahun_akademik']) ?> - <?= e($group['semester_label']) ?>
                </div>

                <div class="nilai-table-shell">
                    <div class="nilai-table-wrap">
                        <table class="nilai-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th style="min-width:180px;">Mata Kuliah</th>
                                    <th style="width:80px;">Tugas</th>
                                    <th style="width:80px;">UTS</th>
                                    <th style="width:80px;">UAS</th>
                                    <th style="width:80px;">LL</th>
                                    <th style="width:70px;">NA</th>
                                    <th style="width:70px;">Grade</th>
                                    <th style="min-width:150px;">Dosen</th>
                                    <th style="min-width:100px;">Ket</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($group['rows'])): ?>
                                    <?php
                                    $no = 1;
                                    foreach ($group['rows'] as $row):
                                        $mataKuliah = trim((string)($row['nama_mata_kuliah'] ?? $row['nama_mata_kuliah'] ?? '-'));
                                        $dosen = trim((string)($row['nama_dosen_manual'] ?? $row['nama_dosen'] ?? '-'));
                                        
                                        $tugas = $row['tugas'] !== null && $row['tugas'] !== '' ? number_format((float)$row['tugas'], 2) : '-';
                                        $uts   = $row['uts'] !== null && $row['uts'] !== '' ? number_format((float)$row['uts'], 2) : '-';
                                        $uas   = $row['uas'] !== null && $row['uas'] !== '' ? number_format((float)$row['uas'], 2) : '-';
                                        $ll    = $row['kehadiran'] !== null && $row['kehadiran'] !== '' ? number_format((float)$row['kehadiran'], 2) : '-';
                                        $na    = $row['nilai_akhir'] !== null && $row['nilai_akhir'] !== '' ? number_format((float)$row['nilai_akhir'], 2) : '-';
                                        $grade = trim((string)($row['grade'] ?? '-'));
                                        $ket   = trim((string)($row['keterangan'] ?? '-'));
                                    ?>
                                        <tr>
                                            <td class="center"><?= $no++; ?></td>
                                            <td class="left"><?= e($mataKuliah !== '-' ? $mataKuliah : '-'); ?></td>
                                            <td class="center"><?= e($tugas); ?></td>
                                            <td class="center"><?= e($uts); ?></td>
                                            <td class="center"><?= e($uas); ?></td>
                                            <td class="center"><?= e($ll); ?></td>
                                            <td class="center"><strong><?= e($na); ?></strong></td>
                                            <td class="center grade-<?= strtolower(e($grade)); ?>"><?= e($grade !== '-' ? $grade : '-'); ?></td>
                                            <td class="left"><?= e($dosen !== '-' ? $dosen : '-'); ?></td>
                                            <td class="center ket-<?= strtolower(str_replace(' ', '-', e($ket))); ?>"><?= e($ket !== '-' ? $ket : '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="empty-row">
                                            <div class="empty-state">
                                                <span class="empty-icon">📭</span>
                                                <p>Belum ada nilai yang diinput untuk semester ini.</p>
                                                <small>Nilai akan muncul setelah dosen/akademik menginput.</small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="nilai-pagination">
                <?php if ($page > 1): ?>
                    <a class="page-nav" href="nilai.php?page=<?= $page - 1; ?>" aria-label="Previous">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="page-nav disabled" aria-disabled="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </span>
                <?php endif; ?>

                <div class="page-info">
                    Semester <strong><?= $page; ?></strong> dari <strong><?= $totalPages; ?></strong>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a class="page-nav" href="nilai.php?page=<?= $page + 1; ?>" aria-label="Next">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="page-nav disabled" aria-disabled="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Info tambahan jika tidak ada data sama sekali -->
        <?php if ($totalGroup === 0): ?>
            <div class="nilai-info">
                <p>💡 <strong>Info:</strong> Nilai akan muncul di sini setelah dosen atau bagian akademik menginput nilai untuk mata kuliah yang Anda ambil.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="../js/js_mahasiswa/nilai.js?v=6.0"></script>

<?php
renderMahasiswaLayoutEnd([
    "assetsBase" => ".."
]);
?>