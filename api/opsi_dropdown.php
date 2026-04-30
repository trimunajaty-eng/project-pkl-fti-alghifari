<?php
require_once __DIR__ . "/../config.php";
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','akademik'])) {
  echo json_encode(["ok" => false, "message" => "Unauthorized"]);
  exit;
}

$group = $_GET['group'] ?? '';
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

if ($action === '' && $group !== '') {
  // READ - Load options
  $stmt = $conn->prepare("SELECT id_opsi as id, value, grup as group, kode_ref, kode_nim 
                          FROM master_opsi_dropdown 
                          WHERE grup=? AND is_active=1 
                          ORDER BY urutan ASC");
  $stmt->bind_param("s", $group);
  $stmt->execute();
  $result = $stmt->get_result();
  $items = [];
  while($row = $result->fetch_assoc()) {
    $items[] = $row;
  }
  $stmt->close();
  echo json_encode(["ok" => true, "items" => $items]);
  exit;
}

// CREATE/UPDATE/DELETE logic here...
echo json_encode(["ok" => false, "message" => "Not implemented"]);