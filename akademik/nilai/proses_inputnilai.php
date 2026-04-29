<?php
/**
 * Proses Input Nilai Massal - Akademik
 * Menangani submit dari inputnilai.php secara aman & terstruktur
 * Version: 5.0 (Fixed bind_param types)
 */

require_once __DIR__ . "/../../config.php";

// ===== Security & Validation =====
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
    header("Location: ../admin/login.php?tipe=error&pesan=" . urlencode("Akses ditolak."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: inputnilai.php");
    exit;
}

// ===== Ambil Data Global dari Form =====
$periode          = trim($_POST['periode'] ?? '');
$jurusan          = trim($_POST['jurusan'] ?? '');
$tahun_akademik   = trim($_POST['tahun_akademik'] ?? '');
$semester_text    = trim($_POST['semester'] ?? '');
$semester_angka   = (int)($_POST['semester_angka'] ?? 0);
$nama_mata_kuliah = trim($_POST['nama_mata_kuliah'] ?? '');
$nama_dosen_manual= trim($_POST['nama_dosen_manual'] ?? '');
$page             = max(1, (int)($_POST['page'] ?? 1));
$id_user_input    = (int)($_SESSION['id_user'] ?? 0);

// Validasi data global wajib
if (
    empty($periode) ||
    empty($jurusan) ||
    empty($tahun_akademik) ||
    empty($semester_text) ||
    $semester_angka <= 0 ||
    empty($nama_mata_kuliah) ||
    empty($nama_dosen_manual)
) {
    header("Location: inputnilai.php?tipe=error&pesan=" . urlencode("Data input nilai tidak lengkap."));
    exit;
}

// ===== Ambil Array Nilai Massal =====
$nilaiData = $_POST['nilai'] ?? [];

if (empty($nilaiData) || !is_array($nilaiData)) {
    header("Location: inputnilai.php?" . http_build_query([
        'periode' => $periode,
        'jurusan' => $jurusan,
        'semester_angka' => $semester_angka,
        'nama_mata_kuliah' => $nama_mata_kuliah,
        'nama_dosen_manual' => $nama_dosen_manual,
        'page' => $page,
        'tipe' => 'error',
        'pesan' => 'Tidak ada data nilai yang diproses.'
    ]));
    exit;
}

// ===== Helper: Hitung Grade =====
function hitungGrade($nilai_akhir)
{
    $nilai_akhir = floatval($nilai_akhir);

    if ($nilai_akhir >= 85) {
        return ['grade' => 'A', 'keterangan' => 'Lulus'];
    } elseif ($nilai_akhir >= 75) {
        return ['grade' => 'B', 'keterangan' => 'Lulus'];
    } elseif ($nilai_akhir >= 65) {
        return ['grade' => 'C', 'keterangan' => 'Lulus'];
    } elseif ($nilai_akhir >= 50) {
        return ['grade' => 'D', 'keterangan' => 'Tidak Lulus'];
    } else {
        return ['grade' => 'E', 'keterangan' => 'Tidak Lulus'];
    }
}

// ===== Helper: Hitung Nilai Akhir =====
function hitungNilaiAkhir($tugas, $uts, $uas, $kehadiran)
{
    return round(
        ($tugas * 0.25) +
        ($uts * 0.25) +
        ($uas * 0.35) +
        ($kehadiran * 0.15),
        2
    );
}

// ===== Proses Setiap Mahasiswa =====
$processedCount = 0;
$errorCount = 0;
$errors = [];

foreach ($nilaiData as $id_mahasiswa => $data) {
    $id_mahasiswa = (int)$id_mahasiswa;
    if ($id_mahasiswa <= 0) {
        continue;
    }

    // Ambil dan sanitasi nilai (clamp 0-100)
    $tugas     = max(0, min(100, floatval($data['tugas'] ?? 0)));
    $uts       = max(0, min(100, floatval($data['uts'] ?? 0)));
    $uas       = max(0, min(100, floatval($data['uas'] ?? 0)));
    $kehadiran = max(0, min(100, floatval($data['kehadiran'] ?? 0)));

    // Hitung nilai akhir dan grade (server-side validation)
    $nilai_akhir = hitungNilaiAkhir($tugas, $uts, $uas, $kehadiran);
    $gradeInfo = hitungGrade($nilai_akhir);
    $grade = $gradeInfo['grade'];
    $keterangan = $gradeInfo['keterangan'];

    // ===== Cek Apakah Data Sudah Ada =====
    // Unique key: id_mahasiswa + tahun_akademik + semester_angka + nama_mata_kuliah
    $stmtCek = $conn->prepare("
        SELECT id_nilai 
        FROM nilai_mahasiswa 
        WHERE id_mahasiswa = ? 
          AND tahun_akademik = ? 
          AND semester_angka = ? 
          AND nama_mata_kuliah = ?
        LIMIT 1
    ");

    if (!$stmtCek) {
        $errorCount++;
        $errors[] = "Mahasiswa #{$id_mahasiswa}: Error prepare cek data";
        continue;
    }

    $stmtCek->bind_param("isis", $id_mahasiswa, $tahun_akademik, $semester_angka, $nama_mata_kuliah);
    $stmtCek->execute();
    $resCek = $stmtCek->get_result();

    if ($resCek && $resCek->num_rows > 0) {
        // ===== UPDATE Existing Record =====
        $row = $resCek->fetch_assoc();
        $id_nilai = (int)$row['id_nilai'];
        $stmtCek->close();

        $stmtUpdate = $conn->prepare("
            UPDATE nilai_mahasiswa 
            SET tugas = ?, 
                uts = ?, 
                uas = ?, 
                kehadiran = ?, 
                nilai_akhir = ?, 
                grade = ?, 
                keterangan = ?, 
                id_user_input = ?, 
                diubah_pada = NOW()
            WHERE id_nilai = ?
        ");

        if (!$stmtUpdate) {
            $errorCount++;
            $errors[] = "Mahasiswa #{$id_mahasiswa}: Error prepare update";
            continue;
        }

        // Types: d-d-d-d-d-s-s-i-i (9 params)
        $stmtUpdate->bind_param(
            "dddddssii",
            $tugas,
            $uts,
            $uas,
            $kehadiran,
            $nilai_akhir,
            $grade,
            $keterangan,
            $id_user_input,
            $id_nilai
        );

        if ($stmtUpdate->execute()) {
            $processedCount++;
        } else {
            $errorCount++;
            $errors[] = "Mahasiswa #{$id_mahasiswa}: Gagal update - " . $stmtUpdate->error;
        }
        $stmtUpdate->close();

    } else {
        // ===== INSERT New Record =====
        $stmtCek->close();

        $stmtInsert = $conn->prepare("
            INSERT INTO nilai_mahasiswa (
                id_mahasiswa,
                id_dosen,
                tahun_akademik,
                semester,
                semester_angka,
                nama_mata_kuliah,
                nama_dosen_manual,
                tugas,
                uts,
                uas,
                kehadiran,
                nilai_akhir,
                grade,
                keterangan,
                id_user_input,
                dibuat_pada
            ) VALUES (
                ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");

        if (!$stmtInsert) {
            $errorCount++;
            $errors[] = "Mahasiswa #{$id_mahasiswa}: Error prepare insert";
            continue;
        }

        // ===== FIX PENTING: Types string harus sesuai urutan variabel =====
        // Urutan: id_mhs(i), tahun(s), semester_text(s), semester_angka(i), 
        //         nama_makul(s), nama_dosen(s), tugas(d), uts(d), uas(d), 
        //         kehadiran(d), nilai_akhir(d), grade(s), keterangan(s), id_user(i)
        // Types:  i    s    s              i              s           s           d    d    d    d          d           s     s           i
        //         ↓    ↓    ↓              ↓              ↓           ↓           ↓    ↓    ↓    ↓          ↓           ↓     ↓           ↓
        //         "ississsddddssi"  ← 14 karakter, sesuai 14 variabel
        $stmtInsert->bind_param(
            "ississsddddssi",
            $id_mahasiswa,      // 1. i  - id_mahasiswa
            $tahun_akademik,    // 2. s  - tahun_akademik
            $semester_text,     // 3. s  - semester ('Ganjil'/'Genap')
            $semester_angka,    // 4. i  - semester_angka (1,2,3...) ← FIX: i bukan s
            $nama_mata_kuliah,  // 5. s  - nama_mata_kuliah
            $nama_dosen_manual, // 6. s  - nama_dosen_manual
            $tugas,             // 7. d  - tugas
            $uts,               // 8. d  - uts
            $uas,               // 9. d  - uas
            $kehadiran,         // 10. d - kehadiran
            $nilai_akhir,       // 11. d - nilai_akhir
            $grade,             // 12. s - grade ('A'/'B'/'C') ← FIX: s bukan d
            $keterangan,        // 13. s - keterangan
            $id_user_input      // 14. i - id_user_input
        );

        if ($stmtInsert->execute()) {
            $processedCount++;
        } else {
            $errorCount++;
            $errors[] = "Mahasiswa #{$id_mahasiswa}: Gagal insert - " . $stmtInsert->error;
        }
        $stmtInsert->close();
    }
}

// ===== Build Redirect URL dengan Flash Message =====
$redirectParams = [
    'periode' => $periode,
    'jurusan' => $jurusan,
    'semester_angka' => $semester_angka,
    'nama_mata_kuliah' => $nama_mata_kuliah,
    'nama_dosen_manual' => $nama_dosen_manual,
    'page' => $page
];

if ($errorCount > 0 && $processedCount > 0) {
    // Partial success
    $redirectParams['tipe'] = 'warning';
    $redirectParams['pesan'] = "✅ {$processedCount} nilai berhasil disimpan, ⚠️ {$errorCount} gagal.";

} elseif ($errorCount > 0) {
    // All failed
    $redirectParams['tipe'] = 'error';
    $errorMsg = implode('; ', array_slice($errors, 0, 3));
    $redirectParams['pesan'] = "❌ Gagal menyimpan: " . substr($errorMsg, 0, 200);

} else {
    // All success
    $redirectParams['tipe'] = 'success';
    $redirectParams['pesan'] = "🎉 Berhasil menyimpan {$processedCount} data nilai!";
}

// Redirect back to input page
header("Location: inputnilai.php?" . http_build_query($redirectParams));
exit;