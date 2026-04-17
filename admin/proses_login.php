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

if (!$stmt) {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Terjadi kesalahan pada sistem."));
  exit;
}

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

// cek status akun
if (($user['status'] ?? '') !== 'aktif') {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Akun sedang nonaktif."));
  exit;
}

// hanya izinkan admin dan akademik
$role = $user['role'] ?? '';
if (!in_array($role, ['admin', 'akademik'], true)) {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Akun ini tidak memiliki akses ke halaman ini."));
  exit;
}

// verifikasi password
if (!password_verify($password, $user['password_hash'])) {
  header("Location: login.php?tipe=error&pesan=" . urlencode("Username atau password salah."));
  exit;
}

// bersihkan session lama
session_regenerate_id(true);

// set session login
$_SESSION['id_user']      = (int)$user['id_user'];
$_SESSION['role']         = $user['role'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];

// flash sukses
$_SESSION['flash_login_ok'] = 1;

// redirect sesuai role
if ($role === 'admin') {
  header("Location: dashboard.php?login=1");
  exit;
}

if ($role === 'akademik') {
  header("Location: ../akademik/dashboard.php?login=1");
  exit;
}

// fallback
header("Location: login.php?tipe=error&pesan=" . urlencode("Role tidak dikenali."));
exit;