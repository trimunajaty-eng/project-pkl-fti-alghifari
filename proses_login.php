<?php
require_once "config.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
  $user = $result->fetch_assoc();

  // (opsional) pastikan role mahasiswa
  if ($user['role'] !== 'mahasiswa') {
    header("Location: login.php?pesan=" . urlencode("Akun bukan mahasiswa."));
    exit;
  }

  // BLOKIR LOGIN JIKA NONAKTIF
  if (($user['status'] ?? 'aktif') === 'nonaktif') {
    header("Location: login.php?tipe=error&pesan=" . urlencode("Akun dinonaktifkan oleh admin. Silakan hubungi pihak kampus."));
    exit;
  }

  if (password_verify($password, $user['password_hash'])) {
    $_SESSION['id_user']  = $user['id_user'];
    $_SESSION['username'] = $user['username'];     // NIM
    $_SESSION['nama']     = $user['nama_lengkap'];
    $_SESSION['role']     = $user['role'];

    // flash sukses untuk toast di dashboard
    $_SESSION['flash_success'] = "Login berhasil. Selamat datang!";

    header("Location: mahasiswa/dashboard.php?login=1");
    exit;
  }
}

header("Location: login.php?tipe=error&pesan=" . urlencode("Username atau password salah"));
exit;