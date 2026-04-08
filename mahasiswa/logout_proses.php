<?php
require_once __DIR__ . "/../config.php";

session_unset();
session_destroy();

// kirim tipe=success biar alert login jadi hijau
header("Location: ../login.php?tipe=success&pesan=" . urlencode("Berhasil logout."));
exit;