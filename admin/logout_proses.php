<?php
require_once __DIR__ . "/../config.php";

// bersihkan session
session_unset();
session_destroy();

// balik ke login admin
header("Location: login.php?tipe=info&pesan=" . urlencode("Kamu sudah keluar."));
exit;
