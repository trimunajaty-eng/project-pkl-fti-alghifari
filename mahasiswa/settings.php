<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/layout.php";

// wajib login mahasiswa
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: ../login.php?pesan=" . urlencode("Silakan login."));
  exit;
}

$nim  = $_SESSION['username'] ?? '';
$nama = $_SESSION['nama'] ?? 'Mahasiswa';

if ($nim === '') {
  header("Location: ../login.php?pesan=" . urlencode("Sesi tidak valid. Silakan login ulang."));
  exit;
}

// cek status akun (aktif/nonaktif) untuk modal blocker, bukan redirect
$status_user = 'aktif';
$stU = $conn->prepare("SELECT status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
if ($stU) {
  $stU->bind_param("s", $nim);
  $stU->execute();
  $rsU = $stU->get_result();
  if ($rsU && ($rowU = $rsU->fetch_assoc())) {
    $status_user = ($rowU['status'] ?? 'aktif');
  }
  $stU->close();
}

$is_blocked = ($status_user === 'nonaktif');
$blocked_msg = "Akun kamu dinonaktifkan oleh admin. Silakan hubungi pihak kampus.";

// handle submit reset sandi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old = (string)($_POST['old_password'] ?? '');
  $new = (string)($_POST['new_password'] ?? '');
  $con = (string)($_POST['confirm_password'] ?? '');

  if (trim($old) === '' || trim($new) === '' || trim($con) === '') {
    $_SESSION['flash_error'] = "Semua field wajib diisi.";
    header("Location: settings.php");
    exit;
  }

  if ($new !== $con) {
    $_SESSION['flash_error'] = "Konfirmasi password tidak sama.";
    header("Location: settings.php");
    exit;
  }

  if (strlen($new) < 6) {
    $_SESSION['flash_error'] = "Password baru minimal 6 karakter.";
    header("Location: settings.php");
    exit;
  }

  if ($old === $new) {
    $_SESSION['flash_error'] = "Password baru tidak boleh sama dengan password lama.";
    header("Location: settings.php");
    exit;
  }

  $st = $conn->prepare("SELECT id_user, password_hash, status FROM users WHERE username=? AND role='mahasiswa' LIMIT 1");
  if (!$st) {
    $_SESSION['flash_error'] = "Server error (prepare).";
    header("Location: settings.php");
    exit;
  }

  $st->bind_param("s", $nim);
  $st->execute();
  $rs = $st->get_result();
  $u  = ($rs && $rs->num_rows === 1) ? $rs->fetch_assoc() : null;
  $st->close();

  if (!$u) {
    $_SESSION['flash_error'] = "Akun tidak ditemukan.";
    header("Location: settings.php");
    exit;
  }

  if (($u['status'] ?? 'aktif') === 'nonaktif') {
    $_SESSION['flash_error'] = "Akun dinonaktifkan oleh admin.";
    header("Location: settings.php");
    exit;
  }

  if (!password_verify($old, $u['password_hash'])) {
    $_SESSION['flash_error'] = "Password lama salah.";
    header("Location: settings.php");
    exit;
  }

  $newHash = password_hash($new, PASSWORD_BCRYPT);

  $st2 = $conn->prepare("UPDATE users SET password_hash=? WHERE id_user=? AND role='mahasiswa' LIMIT 1");
  if (!$st2) {
    $_SESSION['flash_error'] = "Server error (update).";
    header("Location: settings.php");
    exit;
  }

  $id_user = (int)$u['id_user'];
  $st2->bind_param("si", $newHash, $id_user);
  $ok = $st2->execute();
  $st2->close();

  if (!$ok) {
    $_SESSION['flash_error'] = "Gagal menyimpan password baru.";
    header("Location: settings.php");
    exit;
  }

  $_SESSION['flash_success'] = "Password berhasil diperbarui.";
  header("Location: settings.php");
  exit;
}

// layout start
renderMahasiswaLayoutStart([
  "title"       => "Mahasiswa - Pengaturan",
  "page_title"  => "Pengaturan",
  "page_sub"    => "Reset sandi akun kamu",
  "menu"        => "settings",
  "nama_tampil" => $nama,
  "username"    => $nim,
  "assetsBase"  => "..",
  "basePath"    => "",
  "is_blocked"  => $is_blocked,
  "blocked_msg" => $blocked_msg,
]);
?>

<link rel="stylesheet" href="../css/css_mahasiswa/settings.css">

<section
  class="set-card"
  id="settingsPage"
  data-api="api_akun.php?action=status"
  data-blocked-msg="<?= e($blocked_msg) ?>"
>
  <div class="set-head">
    <div class="set-ic" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none">
        <path d="M8 11V8a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="6" y="11" width="12" height="9" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
      </svg>
    </div>

    <div class="set-head-text">
      <div class="set-title">Reset Password</div>
      <div class="set-sub">Masukkan password lama lalu tentukan password baru.</div>
    </div>
  </div>

  <form class="set-form" method="POST" action="settings.php" id="formSettings" autocomplete="off">
    <div class="set-field">
      <label for="old_password">Password Lama</label>
      <div class="set-pass">
        <input type="password" name="old_password" id="old_password" placeholder="Password lama" required>

        <button class="set-eye" type="button" data-eye="old_password" aria-label="Lihat password lama">
          <svg class="ico-eye" viewBox="0 0 24 24" fill="none">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
          </svg>

          <svg class="ico-eyeoff" viewBox="0 0 24 24" fill="none">
            <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M10.6 10.6A2.9 2.9 0 0 0 9 12c0 1.7 1.3 3 3 3 .5 0 1-.1 1.4-.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M6.2 6.2C3.9 8 2 12 2 12s3.5 7 10 7c2.2 0 4.1-.6 5.7-1.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9.9 5.2C10.6 5.1 11.3 5 12 5c6.5 0 10 7 10 7" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="set-field">
      <label for="new_password">Password Baru</label>
      <div class="set-pass">
        <input type="password" name="new_password" id="new_password" placeholder="Password baru (min 6 karakter)" required>

        <button class="set-eye" type="button" data-eye="new_password" aria-label="Lihat password baru">
          <svg class="ico-eye" viewBox="0 0 24 24" fill="none">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
          </svg>
          <svg class="ico-eyeoff" viewBox="0 0 24 24" fill="none">
            <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M10.6 10.6A2.9 2.9 0 0 0 9 12c0 1.7 1.3 3 3 3 .5 0 1-.1 1.4-.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M6.2 6.2C3.9 8 2 12 2 12s3.5 7 10 7c2.2 0 4.1-.6 5.7-1.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9.9 5.2C10.6 5.1 11.3 5 12 5c6.5 0 10 7 10 7" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="set-field">
      <label for="confirm_password">Konfirmasi Password Baru</label>
      <div class="set-pass">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Ulangi password baru" required>

        <button class="set-eye" type="button" data-eye="confirm_password" aria-label="Lihat konfirmasi password">
          <svg class="ico-eye" viewBox="0 0 24 24" fill="none">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
          </svg>
          <svg class="ico-eyeoff" viewBox="0 0 24 24" fill="none">
            <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M10.6 10.6A2.9 2.9 0 0 0 9 12c0 1.7 1.3 3 3 3 .5 0 1-.1 1.4-.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M6.2 6.2C3.9 8 2 12 2 12s3.5 7 10 7c2.2 0 4.1-.6 5.7-1.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M9.9 5.2C10.6 5.1 11.3 5 12 5c6.5 0 10 7 10 7" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </button>
      </div>

      <div class="set-hint" id="hintMatch" aria-live="polite"></div>
    </div>

    <div class="set-actions">
      <a class="set-btn ghost" href="dashboard.php">Kembali</a>
      <button class="set-btn" type="submit" id="btnSave">
        <span class="btnspin" id="btnSpin" aria-hidden="true"></span>
        <span class="btntext" id="btnText">Simpan</span>
      </button>
    </div>
  </form>
</section>

<script src="../js/js_mahasiswa/settings.js"></script>

<?php
renderMahasiswaLayoutEnd([
  "assetsBase" => ".."
]);
?>