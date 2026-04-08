<?php
require_once __DIR__ . "/../../config.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(["ok" => false, "message" => "Unauthorized"]);
  exit;
}

function out($ok, $message = "", $data = []) {
  echo json_encode(array_merge(["ok" => $ok, "message" => $message], $data));
  exit;
}

$action = $_POST['action'] ?? '';

function has_column(mysqli $conn, $table, $column) {
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
          LIMIT 1";
  $st = $conn->prepare($sql);
  $st->bind_param("ss", $table, $column);
  $st->execute();
  $rs = $st->get_result();
  $ok = ($rs && $rs->num_rows > 0);
  $st->close();
  return $ok;
}

$hasAkunDicetak = has_column($conn, "mahasiswa", "akun_dicetak");
$hasAkunDicetakPada = has_column($conn, "mahasiswa", "akun_dicetak_pada");
$hasIdUser = has_column($conn, "mahasiswa", "id_user");

if (!$hasAkunDicetak) {
  out(false, "Kolom mahasiswa.akun_dicetak belum ada. Jalankan ALTER TABLE dulu.");
}

// PRODI MASTER (biar tetap muncul walau sudah semua dicetak)
$prodiMaster = ["Teknik Informatika S1","Sistem Informasi S1"];

/**
 * SEARCH (autocomplete) - hanya yang belum dicetak
 */
if ($action === 'search') {
  $q = trim($_POST['q'] ?? '');
  if ($q === '') out(true, "OK", ["items" => []]);

  $qLike = "%" . $q . "%";
  $sql = "SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi
          FROM mahasiswa
          WHERE IFNULL(akun_dicetak,0)=0
            AND (nim LIKE ? OR nama_mahasiswa LIKE ?)
          ORDER BY nama_mahasiswa ASC
          LIMIT 10";
  $st = $conn->prepare($sql);
  $st->bind_param("ss", $qLike, $qLike);
  $st->execute();
  $rs = $st->get_result();

  $items = [];
  if ($rs) {
    while ($r = $rs->fetch_assoc()) {
      $items[] = [
        "id"    => (int)$r["id_mahasiswa"],
        "nim"   => $r["nim"],
        "nama"  => $r["nama_mahasiswa"],
        "prodi" => $r["program_studi"]
      ];
    }
  }
  $st->close();
  out(true, "OK", ["items" => $items]);
}

/**
 * COUNTS
 * - remaining: belum dicetak (buat mass print)
 * - made: sudah dicetak (buat tampilan bawah)
 */
if ($action === 'counts') {
  // init
  $remainingTotal = 0;
  $remainingBy = [];
  $madeTotal = 0;
  $madeBy = [];

  foreach ($prodiMaster as $p) {
    $remainingBy[$p] = 0;
    $madeBy[$p] = 0;
  }

  // remaining total
  $qRemT = $conn->query("SELECT COUNT(*) AS total FROM mahasiswa WHERE IFNULL(akun_dicetak,0)=0");
  if ($qRemT) $remainingTotal = (int)($qRemT->fetch_assoc()['total'] ?? 0);

  // remaining by prodi
  $qRemB = $conn->query("SELECT program_studi, COUNT(*) AS total
                         FROM mahasiswa
                         WHERE IFNULL(akun_dicetak,0)=0
                         GROUP BY program_studi
                         ORDER BY program_studi ASC");
  if ($qRemB) {
    while ($r = $qRemB->fetch_assoc()) {
      $p = $r['program_studi'];
      if ($p !== null && $p !== '') {
        if (!isset($remainingBy[$p])) $remainingBy[$p] = 0;
        $remainingBy[$p] = (int)$r['total'];
      }
    }
  }

  // made total
  $qMadeT = $conn->query("SELECT COUNT(*) AS total FROM mahasiswa WHERE IFNULL(akun_dicetak,0)=1");
  if ($qMadeT) $madeTotal = (int)($qMadeT->fetch_assoc()['total'] ?? 0);

  // made by prodi
  $qMadeB = $conn->query("SELECT program_studi, COUNT(*) AS total
                          FROM mahasiswa
                          WHERE IFNULL(akun_dicetak,0)=1
                          GROUP BY program_studi
                          ORDER BY program_studi ASC");
  if ($qMadeB) {
    while ($r = $qMadeB->fetch_assoc()) {
      $p = $r['program_studi'];
      if ($p !== null && $p !== '') {
        if (!isset($madeBy[$p])) $madeBy[$p] = 0;
        $madeBy[$p] = (int)$r['total'];
      }
    }
  }

  out(true, "OK", [
    "remaining_total" => $remainingTotal,
    "remaining_by_prodi" => $remainingBy,
    "made_total" => $madeTotal,
    "made_by_prodi" => $madeBy,
    "prodi_master" => $prodiMaster
  ]);
}

function cetak_satu(mysqli $conn, int $id_mahasiswa, bool $hasAkunDicetakPada, bool $hasIdUser) {
  $st = $conn->prepare("SELECT id_mahasiswa, nim, nama_mahasiswa, program_studi, IFNULL(akun_dicetak,0) AS akun_dicetak
                        FROM mahasiswa
                        WHERE id_mahasiswa=?
                        LIMIT 1");
  $st->bind_param("i", $id_mahasiswa);
  $st->execute();
  $rs = $st->get_result();
  $m = ($rs && $rs->num_rows === 1) ? $rs->fetch_assoc() : null;
  $st->close();

  if (!$m) return ["ok"=>false, "message"=>"Data mahasiswa tidak ditemukan."];
  if ((int)$m['akun_dicetak'] === 1) {
    return ["ok"=>false, "message"=>"Akun mahasiswa ini sudah dicetak.", "nama"=>$m['nama_mahasiswa'], "nim"=>$m['nim']];
  }

  $nim  = trim((string)$m['nim']);
  $nama = trim((string)$m['nama_mahasiswa']);

  if ($nim === '' || $nama === '') {
    return ["ok"=>false, "message"=>"NIM/Nama kosong, tidak bisa cetak akun."];
  }

  $conn->begin_transaction();

  try {
    $uid = null;
    $stU = $conn->prepare("SELECT id_user FROM users WHERE username=? LIMIT 1");
    $stU->bind_param("s", $nim);
    $stU->execute();
    $rsU = $stU->get_result();
    if ($rsU && $rsU->num_rows === 1) {
      $uid = (int)($rsU->fetch_assoc()['id_user'] ?? 0);
    }
    $stU->close();

    if ($uid === null) {
      $hash = password_hash($nim, PASSWORD_BCRYPT);
      $role = 'mahasiswa';
      $status = 'aktif';

      $stI = $conn->prepare("INSERT INTO users (role, username, password_hash, nama_lengkap, status)
                             VALUES (?,?,?,?,?)");
      $stI->bind_param("sssss", $role, $nim, $hash, $nama, $status);
      if (!$stI->execute()) {
        $stI->close();
        throw new Exception("Gagal membuat akun user.");
      }
      $stI->close();

      $uid = (int)$conn->insert_id;
    }

    if ($hasAkunDicetakPada && $hasIdUser) {
      $stM = $conn->prepare("UPDATE mahasiswa
                             SET akun_dicetak=1, akun_dicetak_pada=NOW(), id_user=?
                             WHERE id_mahasiswa=?");
      $stM->bind_param("ii", $uid, $id_mahasiswa);
    } elseif ($hasAkunDicetakPada) {
      $stM = $conn->prepare("UPDATE mahasiswa
                             SET akun_dicetak=1, akun_dicetak_pada=NOW()
                             WHERE id_mahasiswa=?");
      $stM->bind_param("i", $id_mahasiswa);
    } elseif ($hasIdUser) {
      $stM = $conn->prepare("UPDATE mahasiswa
                             SET akun_dicetak=1, id_user=?
                             WHERE id_mahasiswa=?");
      $stM->bind_param("ii", $uid, $id_mahasiswa);
    } else {
      $stM = $conn->prepare("UPDATE mahasiswa SET akun_dicetak=1 WHERE id_mahasiswa=?");
      $stM->bind_param("i", $id_mahasiswa);
    }

    if (!$stM->execute()) {
      $stM->close();
      throw new Exception("Gagal update status cetak di mahasiswa.");
    }
    $stM->close();

    $conn->commit();

    return ["ok"=>true, "message"=>"OK", "nama"=>$nama, "nim"=>$nim, "prodi"=>$m['program_studi'], "id_user"=>$uid];
  } catch (Throwable $e) {
    $conn->rollback();
    return ["ok"=>false, "message"=>$e->getMessage()];
  }
}

/**
 * SINGLE
 */
if ($action === 'single') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) out(false, "ID mahasiswa tidak valid.");

  $r = cetak_satu($conn, $id, $hasAkunDicetakPada, $hasIdUser);
  if (!$r["ok"]) out(false, $r["message"], ["nama"=>$r["nama"] ?? "", "nim"=>$r["nim"] ?? ""]);

  out(true, "Berhasil cetak akun: ".$r["nama"], [
    "nama" => $r["nama"],
    "nim"  => $r["nim"],
    "prodi"=> $r["prodi"] ?? ""
  ]);
}

/**
 * MASSAL - hanya yang belum dicetak
 */
if ($action === 'massal') {
  $prodi = trim($_POST['prodi'] ?? 'ALL');

  if ($prodi === 'ALL') {
    $sql = "SELECT id_mahasiswa FROM mahasiswa WHERE IFNULL(akun_dicetak,0)=0 ORDER BY id_mahasiswa ASC";
    $st = $conn->prepare($sql);
  } else {
    $sql = "SELECT id_mahasiswa FROM mahasiswa
            WHERE IFNULL(akun_dicetak,0)=0 AND program_studi=?
            ORDER BY id_mahasiswa ASC";
    $st = $conn->prepare($sql);
    $st->bind_param("s", $prodi);
  }

  $st->execute();
  $rs = $st->get_result();
  $ids = [];
  if ($rs) {
    while ($r = $rs->fetch_assoc()) $ids[] = (int)$r['id_mahasiswa'];
  }
  $st->close();

  if (count($ids) === 0) {
    out(false, "Tidak ada data yang bisa dicetak untuk pilihan ini.");
  }

  $okCount = 0;
  $failCount = 0;

  foreach ($ids as $idm) {
    $r = cetak_satu($conn, $idm, $hasAkunDicetakPada, $hasIdUser);
    if ($r["ok"]) $okCount++;
    else $failCount++;
  }

  $label = ($prodi === 'ALL') ? "Semua Program Studi" : $prodi;
  out(true, "Berhasil cetak akun massal: {$okCount} akun ({$label})", [
    "printed" => $okCount,
    "failed"  => $failCount,
    "label"   => $label
  ]);
}

out(false, "Aksi tidak dikenal.");