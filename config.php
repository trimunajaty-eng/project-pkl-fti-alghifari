<?php
// =====================================================
// CONFIG APLIKASI - kampusghifari
// =====================================================

// Mulai session (kalau belum)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ubah sesuai setting server kamu
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "kampusghifari";

// Koneksi mysqli
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
  die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

/**
 * Helper base_url
 * - Aman untuk project di root atau subfolder (contoh: /kampusghifari)
 * - Contoh: base_url("/admin/login.php")
 */
function base_url($path = "") {
  // deteksi folder project dari script yang diakses
  $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
  // kalau sudah di root, dirname bisa jadi "/" -> rapikan
  if ($dir === '/' || $dir === '\\') $dir = '';

  // normalisasi path
  $path = '/' . ltrim($path, '/');

  return $dir . $path;
}
