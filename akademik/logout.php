<?php
require_once __DIR__ . "/../config.php";

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'akademik') {
  header("Location: ../admin/login.php");
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Logout Akademik</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box}
    body{
      margin:0;
      min-height:100vh;
      display:grid;
      place-items:center;
      font-family:"Poppins",Arial,sans-serif;
      background:#f6f8fb;
      color:#1f2937;
      padding:16px;
    }
    .card{
      width:min(360px,100%);
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:14px;
      box-shadow:0 10px 24px rgba(15,23,42,.06);
      padding:18px;
      text-align:center;
    }
    h1{
      margin:0 0 8px;
      font-size:18px;
      font-weight:600;
    }
    p{
      margin:0 0 16px;
      font-size:13px;
      color:#6b7280;
      line-height:1.6;
    }
    .actions{
      display:flex;
      gap:10px;
      justify-content:center;
    }
    .btn{
      border:none;
      border-radius:10px;
      padding:10px 14px;
      font-size:13px;
      font-weight:500;
      cursor:pointer;
      text-decoration:none;
    }
    .btn-cancel{
      background:#f3f4f6;
      color:#111827;
    }
    .btn-logout{
      background:#dc2626;
      color:#fff;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Logout Akademik</h1>
    <p>Apakah Anda yakin ingin keluar dari sistem akademik?</p>

    <div class="actions">
      <a href="dashboard.php" class="btn btn-cancel">Batal</a>
      <a href="logout_proses.php" class="btn btn-logout">Logout</a>
    </div>
  </div>
</body>
</html>