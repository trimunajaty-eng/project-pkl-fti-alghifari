<?php
require_once 'config.php';

if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    header('Location: ' . base_url('/admin/dashboard.php'));
  } else {
    header('Location: ' . base_url('/mahasiswa/dashboard.php'));
  }
} else {
  header('Location: ' . base_url('/login.php'));
}
exit;
?>