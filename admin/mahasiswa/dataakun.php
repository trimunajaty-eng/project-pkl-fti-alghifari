<?php
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../layout.php";

// Wajib login admin
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php?tipe=error&pesan=" . urlencode("Silakan login sebagai admin dulu."));
  exit;
}

$username = $_SESSION['username'] ?? 'unfari';

// ambil nama_lengkap dari DB agar selalu sinkron
$nama_tampil = 'Universitas Al-ghifari';
$stmtMe = $conn->prepare("SELECT nama_lengkap FROM users WHERE username=? LIMIT 1");
$stmtMe->bind_param("s", $username);
$stmtMe->execute();
$rsMe = $stmtMe->get_result();
if ($rsMe && ($rowMe = $rsMe->fetch_assoc())) {
  $nama_tampil = $rowMe['nama_lengkap'] ?: $nama_tampil;
}
$stmtMe->close();

$menu = 'mhs_data';

$tipe  = $_GET['tipe']  ?? '';
$pesan = $_GET['pesan'] ?? '';

/**
 * =========================================================
 * AJAX ACTIONS (toggle status / delete)
 * =========================================================
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');

  if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
  }

  $action = $_POST['action'] ?? '';

  // Toggle aktif/nonaktif user mahasiswa
  if ($action === 'toggle_status') {
    $id_mahasiswa = (int)($_POST['id'] ?? 0);
    if ($id_mahasiswa <= 0) {
      echo json_encode(["ok" => false, "message" => "ID tidak valid."]);
      exit;
    }

    // Ambil id_user: prioritas mahasiswa.id_user, fallback join username=nim
    $sql = "SELECT m.id_mahasiswa, m.nim, m.id_user,
                   u.id_user AS u_id, u.status
            FROM mahasiswa m
            LEFT JOIN users u
              ON (u.id_user = m.id_user)
              OR (u.username = m.nim AND u.role='mahasiswa')
            WHERE m.id_mahasiswa=?
              AND IFNULL(m.akun_dicetak,0)=1
            LIMIT 1";
    $st = $conn->prepare($sql);
    if (!$st) {
      echo json_encode(["ok" => false, "message" => "Prepare gagal: ".$conn->error]);
      exit;
    }
    $st->bind_param("i", $id_mahasiswa);
    $st->execute();
    $rs = $st->get_result();
    $row = ($rs && $rs->num_rows === 1) ? $rs->fetch_assoc() : null;
    $st->close();

    if (!$row) {
      echo json_encode(["ok" => false, "message" => "Data akun tidak ditemukan."]);
      exit;
    }

    $id_user = (int)($row['id_user'] ?: $row['u_id']);
    if ($id_user <= 0) {
      echo json_encode(["ok" => false, "message" => "ID user tidak ditemukan pada akun ini."]);
      exit;
    }

    $cur = ($row['status'] ?? 'aktif');
    $next = ($cur === 'aktif') ? 'nonaktif' : 'aktif';

    $st2 = $conn->prepare("UPDATE users SET status=? WHERE id_user=? LIMIT 1");
    if (!$st2) {
      echo json_encode(["ok" => false, "message" => "Prepare update gagal: ".$conn->error]);
      exit;
    }
    $st2->bind_param("si", $next, $id_user);
    $ok = $st2->execute();
    $st2->close();

    if (!$ok) {
      echo json_encode(["ok" => false, "message" => "Gagal update status."]);
      exit;
    }

    echo json_encode(["ok" => true, "message" => "Status berhasil diubah.", "status" => $next]);
    exit;
  }

  // Delete akun (hapus user mahasiswa + reset flag akun_dicetak)
  if ($action === 'delete_akun') {
    $id_mahasiswa = (int)($_POST['id'] ?? 0);
    if ($id_mahasiswa <= 0) {
      echo json_encode(["ok" => false, "message" => "ID tidak valid."]);
      exit;
    }

    // Ambil data dulu
    $sql = "SELECT m.id_mahasiswa, m.nim, m.id_user,
                   u.id_user AS u_id
            FROM mahasiswa m
            LEFT JOIN users u
              ON (u.id_user = m.id_user)
              OR (u.username = m.nim AND u.role='mahasiswa')
            WHERE m.id_mahasiswa=?
              AND IFNULL(m.akun_dicetak,0)=1
            LIMIT 1";
    $st = $conn->prepare($sql);
    if (!$st) {
      echo json_encode(["ok" => false, "message" => "Prepare gagal: ".$conn->error]);
      exit;
    }
    $st->bind_param("i", $id_mahasiswa);
    $st->execute();
    $rs = $st->get_result();
    $row = ($rs && $rs->num_rows === 1) ? $rs->fetch_assoc() : null;
    $st->close();

    if (!$row) {
      echo json_encode(["ok" => false, "message" => "Data akun tidak ditemukan."]);
      exit;
    }

    $id_user = (int)($row['id_user'] ?: $row['u_id']); // boleh 0 jika memang tidak ada

    $conn->begin_transaction();
    try {
      // reset akun_dicetak + kosongkan relasi
      $stM = $conn->prepare("UPDATE mahasiswa
                             SET akun_dicetak=0, akun_dicetak_pada=NULL, id_user=NULL
                             WHERE id_mahasiswa=? LIMIT 1");
      if (!$stM) throw new Exception("Prepare update mahasiswa gagal: ".$conn->error);
      $stM->bind_param("i", $id_mahasiswa);
      if (!$stM->execute()) { $stM->close(); throw new Exception("Gagal update mahasiswa."); }
      $stM->close();

      // hapus user jika ada
      if ($id_user > 0) {
        $stU = $conn->prepare("DELETE FROM users WHERE id_user=? AND role='mahasiswa' LIMIT 1");
        if (!$stU) throw new Exception("Prepare delete user gagal: ".$conn->error);
        $stU->bind_param("i", $id_user);
        $stU->execute();
        $stU->close();
      }

      $conn->commit();
      echo json_encode(["ok" => true, "message" => "Akun berhasil dihapus."]);
      exit;

    } catch (Throwable $e) {
      $conn->rollback();
      echo json_encode(["ok" => false, "message" => $e->getMessage()]);
      exit;
    }
  }

  echo json_encode(["ok" => false, "message" => "Aksi tidak dikenal."]);
  exit;
}

/**
 * =========================================================
 * LISTING (GET)
 * =========================================================
 */
$qRaw = trim($_GET['q'] ?? '');
$q = $qRaw;
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$perPage = 15;
$offset = ($page - 1) * $perPage;

// ============ helper render rows + pagination ============
function buildWhereAndParamsAkun($q){
  $where = "WHERE IFNULL(m.akun_dicetak,0)=1";
  $params = [];
  $types = "";

  if ($q !== '') {
    $like = "%".$q."%";
    $where .= " AND (
      m.nama_mahasiswa LIKE ? OR
      m.nim LIKE ? OR
      m.program_studi LIKE ? OR
      IFNULL(u.status,'') LIKE ?
    )";
    $params = [$like,$like,$like,$like];
    $types = "ssss";
  }
  return [$where, $types, $params];
}

function renderPaginationAkun($total, $page, $perPage, $q){
  $totalPages = (int)ceil($total / $perPage);
  if ($totalPages <= 1) return '';

  $qEnc = urlencode($q);

  $makeLink = function($p) use ($qEnc) {
    return "dataakun.php?page=".$p."&q=".$qEnc;
  };

  $html = '<div class="pager">';

  $prev = $page - 1;
  $html .= '<a class="pg-btn '.($page<=1?'disabled':'').'" href="'.($page<=1?'#':$makeLink($prev)).'" data-page="'.$prev.'">‹</a>';

  $start = max(1, $page - 2);
  $end   = min($totalPages, $page + 2);

  if ($start > 1) {
    $html .= '<a class="pg-btn" href="'.$makeLink(1).'" data-page="1">1</a>';
    if ($start > 2) $html .= '<span class="pg-dots">…</span>';
  }

  for ($p=$start; $p<=$end; $p++){
    $html .= '<a class="pg-btn '.($p===$page?'active':'').'" href="'.$makeLink($p).'" data-page="'.$p.'">'.$p.'</a>';
  }

  if ($end < $totalPages) {
    if ($end < $totalPages-1) $html .= '<span class="pg-dots">…</span>';
    $html .= '<a class="pg-btn" href="'.$makeLink($totalPages).'" data-page="'.$totalPages.'">'.$totalPages.'</a>';
  }

  $next = $page + 1;
  $html .= '<a class="pg-btn '.($page>=$totalPages?'disabled':'').'" href="'.($page>=$totalPages?'#':$makeLink($next)).'" data-page="'.$next.'">›</a>';

  $html .= '</div>';

  return $html;
}

function renderRowsAkun($rows, $offset){
  if (!$rows || count($rows) === 0) {
    return '<tr><td class="empty" colspan="7">Belum ada data akun mahasiswa.</td></tr>';
  }

  $no = $offset + 1;
  $html = '';
  foreach ($rows as $r){
    $id     = (int)($r['id_mahasiswa'] ?? 0);
    $nama   = e($r['nama_mahasiswa'] ?? '');
    $nim    = e($r['nim'] ?? '');
    $prodi  = e($r['program_studi'] ?? '-');
    $dicetak= e($r['akun_dicetak_pada'] ?? '-');
    $status = ($r['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

    $lockTitle = ($status === 'aktif') ? 'Nonaktifkan' : 'Aktifkan';

    // icon lock/unlock
    $svgUnlock = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M7 10V8a5 5 0 0 1 9.6-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M7 10h10a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
      </svg>';
    $svgLock = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M7 10h10a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M12 14v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>';

    $html .= '<tr>';
    $html .= '<td class="col-no">'.$no.'</td>';
    $html .= '<td>'.$nama.'</td>';
    $html .= '<td>'.$nim.'</td>';
    $html .= '<td>'.$prodi.'</td>';
    $html .= '<td>'.$dicetak.'</td>';
    $html .= '<td><span class="st-pill '.($status==='aktif'?'on':'off').'">'.e($status).'</span></td>';

    $html .= '<td class="aksi">
      <a class="icon-btn edit" href="editakun.php?id='.$id.'" title="Edit">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M4 20h4l10-10-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
      </a>

      <button class="icon-btn lock '.($status==='aktif'?'on':'off').'" type="button"
              data-toggle="1" data-id="'.$id.'" data-status="'.$status.'"
              title="'.$lockTitle.'">
        '.(($status==='aktif') ? $svgUnlock : $svgLock).'
      </button>

      <button class="icon-btn del" type="button"
              data-del="1" data-id="'.$id.'" data-name="'.$nama.'"
              title="Hapus">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M3 6h18" stroke="currentColor" stroke-width="2"/>
          <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2"/>
          <path d="M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="2"/>
        </svg>
      </button>
    </td>';

    $html .= '</tr>';
    $no++;
  }

  return $html;
}

// ============ ambil data ============
list($where, $types, $params) = buildWhereAndParamsAkun($q);

// total
$sqlTotal = "SELECT COUNT(*) AS total
             FROM mahasiswa m
             LEFT JOIN users u
               ON (u.id_user = m.id_user)
               OR (u.username = m.nim AND u.role='mahasiswa')
             $where";
$stmtT = $conn->prepare($sqlTotal);
if (!$stmtT) {
  $msg = "Prepare total gagal: ".$conn->error;
  if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["ok"=>false,"message"=>$msg]);
    exit;
  }
  header("Location: dataakun.php?tipe=error&pesan=".urlencode($msg));
  exit;
}
if ($types !== '') {
  $stmtT->bind_param($types, ...$params);
}
$stmtT->execute();
$rsT = $stmtT->get_result();
$total = 0;
if ($rsT && ($rowT = $rsT->fetch_assoc())) $total = (int)($rowT['total'] ?? 0);
$stmtT->close();

// rows
$sql = "SELECT m.id_mahasiswa, m.nama_mahasiswa, m.nim, m.program_studi, m.akun_dicetak_pada,
               IFNULL(u.status,'aktif') AS status
        FROM mahasiswa m
        LEFT JOIN users u
          ON (u.id_user = m.id_user)
          OR (u.username = m.nim AND u.role='mahasiswa')
        $where
        ORDER BY m.id_mahasiswa DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  $msg = "Prepare data gagal: ".$conn->error;
  if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["ok"=>false,"message"=>$msg]);
    exit;
  }
  header("Location: dataakun.php?tipe=error&pesan=".urlencode($msg));
  exit;
}

if ($types !== '') {
  $types2 = $types . "ii";
  $limit = $perPage;
  $off = $offset;
  $stmt->bind_param($types2, ...array_merge($params, [$limit,$off]));
} else {
  $limit = $perPage;
  $off = $offset;
  $stmt->bind_param("ii", $limit, $off);
}

$stmt->execute();
$rs = $stmt->get_result();
$rows = [];
if ($rs) {
  while ($r = $rs->fetch_assoc()) $rows[] = $r;
}
$stmt->close();

$tbodyHtml = renderRowsAkun($rows, $offset);
$pagerHtml = renderPaginationAkun($total, $page, $perPage, $q);

// AJAX mode
if (isset($_GET['ajax'])) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "ok" => true,
    "tbody" => $tbodyHtml,
    "pager" => $pagerHtml,
    "total" => $total,
    "page" => $page,
  ]);
  exit;
}

// ============ Render halaman ============
renderAdminLayoutStart([
  "title"       => "Admin - Data Akun Mahasiswa",
  "page_title"  => "Data Akun Mahasiswa",
  "page_sub"    => "Mahasiswa / Data Akun",
  "menu"        => $menu,
  "nama_tampil" => $nama_tampil,
  "username"    => $username,
  "assetsBase"  => "../..",
  "basePath"    => "..",
  "extra_css"   => ["css/css_admin/mahasiswa/dataakun.css"],
]);
?>

<div class="panel">
  <div class="panel-head">
    <div class="panel-left">
      <div class="panel-title">Daftar Akun Mahasiswa</div>
      <div class="panel-sub">Total: <b id="totalText"><?= (int)$total ?></b></div>
    </div>

    <div class="panel-actions">
      <a class="btn-ghost" href="cetakakun.php" title="Kembali">Kembali</a>

      <div class="searchbox">
        <input type="text" id="q" placeholder="Pencarian" value="<?= e($q) ?>" autocomplete="off">
        <button type="button" class="sbtn" aria-label="Cari">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2"/>
            <path d="M16.5 16.5 21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <div class="table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th class="col-no">No</th>
          <th>Nama Mahasiswa</th>
          <th>NIM</th>
          <th>Program Studi</th>
          <th>Dicetak Pada</th>
          <th>Status</th>
          <th class="col-aksi">Aksi</th>
        </tr>
      </thead>
      <tbody id="tbody">
        <?= $tbodyHtml ?>
      </tbody>
    </table>
  </div>

  <div class="panel-foot">
    <div id="pager"><?= $pagerHtml ?></div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast" aria-hidden="true">
  <div class="toast-card" id="toastCard">
    <div class="toast-title" id="toastTitle">Info</div>
    <div class="toast-msg" id="toastMsg">...</div>
  </div>
</div>

<script>
  window.__FLASH_TIPE__  = <?= json_encode($tipe) ?>;
  window.__FLASH_PESAN__ = <?= json_encode($pesan) ?>;
</script>

<?php
renderAdminLayoutEnd([
  "assetsBase" => "../..",
  "extra_js"   => ["js/js_admin/mahasiswa/dataakun.js"],
]);