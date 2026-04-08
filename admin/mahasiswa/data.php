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

$qRaw = trim($_GET['q'] ?? '');
$q = $qRaw;
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$perPage = 15;
$offset = ($page - 1) * $perPage;

// ============ helper render rows + pagination ============
function buildWhereAndParams($q){
  $where = "";
  $params = [];
  $types = "";

  if ($q !== '') {
    $like = "%".$q."%";
    $where = "WHERE
      nama_mahasiswa LIKE ? OR
      nim LIKE ? OR
      tempat_lahir LIKE ? OR
      program_studi LIKE ? OR
      kelas LIKE ? OR
      hp LIKE ?
    ";
    $params = [$like,$like,$like,$like,$like,$like];
    $types = "ssssss";
  }
  return [$where, $types, $params];
}

function renderPagination($total, $page, $perPage, $q){
  $totalPages = (int)ceil($total / $perPage);
  if ($totalPages <= 1) return '';

  $qEnc = urlencode($q);

  $makeLink = function($p) use ($qEnc) {
    return "data.php?page=".$p."&q=".$qEnc;
  };

  $html = '<div class="pager">';

  // Prev
  $prev = $page - 1;
  $html .= '<a class="pg-btn '.($page<=1?'disabled':'').'" href="'.($page<=1?'#':$makeLink($prev)).'" data-page="'.$prev.'">‹</a>';

  // window pages
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

  // Next
  $next = $page + 1;
  $html .= '<a class="pg-btn '.($page>=$totalPages?'disabled':'').'" href="'.($page>=$totalPages?'#':$makeLink($next)).'" data-page="'.$next.'">›</a>';

  $html .= '</div>';

  return $html;
}

function renderRows($rows, $offset){
  if (!$rows || count($rows) === 0) {
    return '<tr><td class="empty" colspan="9">Belum ada data mahasiswa.</td></tr>';
  }

  $no = $offset + 1;
  $html = '';
  foreach ($rows as $r){
    $nama = e($r['nama_mahasiswa'] ?? '');
    $nim  = e($r['nim'] ?? '');
    $ttl  = e(($r['tempat_lahir'] ?? '-') . " / " . ($r['tanggal_lahir'] ?? '-'));
    $jk   = e($r['jenis_kelamin'] ?? '-');
    $prodi= e($r['program_studi'] ?? '-');
    $kelas= e($r['kelas'] ?? '-');
    $hp   = e($r['hp'] ?? '-');
    $id   = (int)($r['id_mahasiswa'] ?? 0);

    $html .= '<tr>';
    $html .= '<td class="col-no">'.$no.'</td>';
    $html .= '<td>'.$nama.'</td>';
    $html .= '<td class="mono">'.$nim.'</td>';
    $html .= '<td>'.$ttl.'</td>';
    $html .= '<td>'.$jk.'</td>';
    $html .= '<td>'.$prodi.'</td>';
    $html .= '<td>'.$kelas.'</td>';
    $html .= '<td>'.$hp.'</td>';
    $html .= '<td class="aksi">
            <a class="icon-btn edit" href="editdata.php?id='.$id.'" title="Edit">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 20h4l10-10-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
            </a>

            <a class="icon-btn del" 
               href="hapusdata.php?id='.$id.'" 
               data-del="1" 
               data-name="'.$nama.'" 
               title="Hapus">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M3 6h18" stroke="currentColor" stroke-width="2"/>
                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2"/>
                <path d="M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="2"/>
              </svg>
            </a>
          </td>';
    $html .= '</tr>';
    $no++;
  }

  return $html;
}

// ============ ambil data ============
list($where, $types, $params) = buildWhereAndParams($q);

// total
$sqlTotal = "SELECT COUNT(*) AS total FROM mahasiswa $where";
$stmtT = $conn->prepare($sqlTotal);
if (!$stmtT) {
  $msg = "Prepare total gagal: ".$conn->error;
  if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["ok"=>false,"message"=>$msg]);
    exit;
  }
  header("Location: data.php?tipe=error&pesan=".urlencode($msg));
  exit;
}
if ($types !== '') {
  $stmtT->bind_param($types, ...$params);
}
$stmtT->execute();
$rsT = $stmtT->get_result();
$total = 0;
if ($rsT && ($rowT = $rsT->fetch_assoc())) $total = (int)$rowT['total'];
$stmtT->close();

// rows
$sql = "SELECT id_mahasiswa, nama_mahasiswa, nim, tempat_lahir, tanggal_lahir, jenis_kelamin, program_studi, kelas, hp
        FROM mahasiswa
        $where
        ORDER BY id_mahasiswa ASC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  $msg = "Prepare data gagal: ".$conn->error;
  if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["ok"=>false,"message"=>$msg]);
    exit;
  }
  header("Location: data.php?tipe=error&pesan=".urlencode($msg));
  exit;
}

if ($types !== '') {
  // tambah limit & offset
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

$tbodyHtml = renderRows($rows, $offset);
$pagerHtml = renderPagination($total, $page, $perPage, $q);

// AJAX mode (live search/pagination tanpa reload)
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
  "title"       => "Admin - Data Mahasiswa",
  "page_title"  => "Data Mahasiswa",
  "page_sub"    => "Mahasiswa / Data",
  "menu"        => $menu,
  "nama_tampil" => $nama_tampil,
  "username"    => $username,
  "assetsBase"  => "../..",
  "basePath"    => "..",
  "extra_css"   => ["css/css_admin/mahasiswa/data.css"],
]);
?>

<div class="panel">
  <div class="panel-head">
    <div class="panel-left">
      <div class="panel-title">Daftar Mahasiswa</div>
      <div class="panel-sub">Total: <b id="totalText"><?= (int)$total ?></b></div>
    </div>

    <div class="panel-actions">
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
          <th class="mono">NIM</th>
          <th>Tempat Tgl Lahir</th>
          <th>JK</th>
          <th>Program Studi</th>
          <th>Kelas</th>
          <th>No HP</th>
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

<!-- TOAST (ikut gaya input mahasiswa biar konsisten) -->
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
  "extra_js"   => ["js/js_admin/mahasiswa/data.js"],
]);