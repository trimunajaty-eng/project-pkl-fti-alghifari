<?php
require_once __DIR__ . "/../../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: inputnilai.php");
  exit;
}

$id_mahasiswa   = (int)($_POST['id_mahasiswa'] ?? 0);
$id_dosen       = (int)($_POST['id_dosen'] ?? 0);
$tahun_akademik = trim($_POST['tahun_akademik'] ?? '');
$semester       = trim($_POST['semester'] ?? '');
$periode        = trim($_POST['periode'] ?? '');
$jurusan        = trim($_POST['jurusan'] ?? '');
$page           = max(1, (int)($_POST['page'] ?? 1));

$tugas          = (float)($_POST['tugas'] ?? 0);
$uts            = (float)($_POST['uts'] ?? 0);
$uas            = (float)($_POST['uas'] ?? 0);
$kehadiran      = (float)($_POST['kehadiran'] ?? 0);

if ($id_mahasiswa <= 0 || $id_dosen <= 0 || $tahun_akademik === '' || $semester === '' || $periode === '' || $jurusan === '') {
  header("Location: inputnilai.php?tipe=error&pesan=" . urlencode("Data input nilai belum lengkap."));
  exit;
}

$nilai_akhir = round((($tugas * 0.25) + ($uts * 0.25) + ($uas * 0.35) + ($kehadiran * 0.15)), 2);

$grade = 'E';
$keterangan = 'Tidak Lulus';

if ($nilai_akhir >= 85) {
  $grade = 'A';
  $keterangan = 'Lulus';
} elseif ($nilai_akhir >= 75) {
  $grade = 'B';
  $keterangan = 'Lulus';
} elseif ($nilai_akhir >= 65) {
  $grade = 'C';
  $keterangan = 'Lulus';
} elseif ($nilai_akhir >= 50) {
  $grade = 'D';
  $keterangan = 'Tidak Lulus';
}

$id_user_input = (int)($_SESSION['id_user'] ?? 0);

$stmtCek = $conn->prepare("SELECT id_nilai
                           FROM nilai_mahasiswa
                           WHERE id_mahasiswa = ?
                             AND id_dosen = ?
                             AND tahun_akademik = ?
                             AND semester = ?
                           LIMIT 1");
$stmtCek->bind_param("iiss", $id_mahasiswa, $id_dosen, $tahun_akademik, $semester);
$stmtCek->execute();
$resCek = $stmtCek->get_result();

if ($resCek && $resCek->num_rows === 1) {
  $row = $resCek->fetch_assoc();
  $id_nilai = (int)$row['id_nilai'];
  $stmtCek->close();

  $stmtUpdate = $conn->prepare("UPDATE nilai_mahasiswa
                                SET tugas = ?, uts = ?, uas = ?, kehadiran = ?, nilai_akhir = ?, grade = ?, keterangan = ?, id_user_input = ?, id_dosen = ?
                                WHERE id_nilai = ?");
  $stmtUpdate->bind_param(
    "dddddssiii",
    $tugas,
    $uts,
    $uas,
    $kehadiran,
    $nilai_akhir,
    $grade,
    $keterangan,
    $id_user_input,
    $id_dosen,
    $id_nilai
  );
  $stmtUpdate->execute();
  $stmtUpdate->close();

  header("Location: inputnilai.php?" . http_build_query([
    'periode' => $periode,
    'jurusan' => $jurusan,
    'id_dosen' => $id_dosen,
    'id_mahasiswa' => $id_mahasiswa,
    'page' => $page,
    'tipe' => 'success',
    'pesan' => 'Nilai berhasil diperbarui.'
  ]));
  exit;
}

$stmtCek->close();

$stmtInsert = $conn->prepare("INSERT INTO nilai_mahasiswa
  (id_mahasiswa, id_dosen, tahun_akademik, semester, tugas, uts, uas, kehadiran, nilai_akhir, grade, keterangan, id_user_input)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtInsert->bind_param(
  "iissddddddsi",
  $id_mahasiswa,
  $id_dosen,
  $tahun_akademik,
  $semester,
  $tugas,
  $uts,
  $uas,
  $kehadiran,
  $nilai_akhir,
  $grade,
  $keterangan,
  $id_user_input
);
$stmtInsert->execute();
$stmtInsert->close();

header("Location: inputnilai.php?" . http_build_query([
  'periode' => $periode,
  'jurusan' => $jurusan,
  'id_dosen' => $id_dosen,
  'id_mahasiswa' => $id_mahasiswa,
  'page' => $page,
  'tipe' => 'success',
  'pesan' => 'Nilai berhasil disimpan.'
]));
exit;