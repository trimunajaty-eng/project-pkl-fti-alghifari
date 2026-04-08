<?php
require_once __DIR__ . "/../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: login.php");
  exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Username dan password wajib diisi."));
  exit;
}

$sql = "SELECT id_user, role, username, password_hash, nama_lengkap, status
        FROM users
        WHERE username = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows !== 1) {
  $stmt->close();
  header("Location: login.php?tipe=error&pesan=" . urlencode("Username atau password salah."));
  exit;
}

$user = $res->fetch_assoc();
$stmt->close();

// status akun
if (($user['status'] ?? '') !== 'aktif') {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Akun sedang nonaktif."));
  exit;
}

// hanya role admin
if (($user['role'] ?? '') !== 'admin') {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Akun ini bukan admin."));
  exit;
}

// verifikasi password
if (!password_verify($password, $user['password_hash'])) {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Username atau password salah."));
  exit;
}

// sukses login
$_SESSION['id_user']      = (int)$user['id_user'];
$_SESSION['role']         = $user['role'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];

// flash login sukses (1x)
$_SESSION['flash_login_ok'] = 1;

header("Location: dashboard.php?login=1");
exit;

