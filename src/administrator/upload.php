<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Lokasi upload TIDAK diduga: dikubur dalam sarang folder berlapis di
// bawah images/ (menyamar sebagai struktur cache/asset internal)
$uploadDir = __DIR__ . '/../images/.cache/assets/gen/thumbs/.store/lain/avatar';
$webPath = '../images/.cache/assets/gen/thumbs/.store/lain/avatar';

// Auto-wipe: hapus semua file upload yang berumur > 10 menit.
// Dijalankan setiap kali halaman upload dibuka, jadi shell/avatar yang
// ditinggalkan tidak bertahan lama dan peserta harus upload ulang.
$TTL = 600; // detik
foreach (glob($uploadDir . '/*') ?: [] as $old) {
    $name = basename($old);
    if ($name === '.htaccess') {
        continue; // handler PHP jangan sampai terhapus
    }
    if (is_file($old) && (time() - filemtime($old)) > $TTL) {
        @unlink($old);
    }
}

$msg = '';
$err = '';
$uploadedPath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $f = $_FILES['avatar'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $err = 'Upload gagal (error code ' . $f['error'] . ').';
    } elseif ($f['size'] > 2 * 1024 * 1024) {
        $err = 'File terlalu besar. Maksimal 2 MB.';
    } else {
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));

        // ---- KECOH UTAMA ----
        // Cek "keamanan": kalau ekstensi php (dan variannya), TAMPILKAN
        // pesan penolakan ... tapi file TETAP disimpan & URL-nya TIDAK
        // diberitahu. Peserta percaya filenya ditolak, padahal tersimpan
        // dan (via .htaccess SetHandler di folder upload) dieksekusi.
        // Peserta harus menyadari: respons = kebohongan, bukan kebenaran.
        $blockedExts = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'pht', 'phar'];
        $denied = in_array($ext, $blockedExts, true);

        if ($denied) {
            // Simpan diam-diam ke nama acak, JANGAN tampilkan path
            $raw = file_get_contents($f['tmp_name']);
            $randName = bin2hex(random_bytes(4)) . '.' . $ext;
            @file_put_contents($uploadDir . '/' . $randName, $raw);
            @chmod($uploadDir . '/' . $randName, 0644);

            // Pesan bohong
            $err = 'Upload ditolak: file PHP/berbahaya terdeteksi. '
                 . 'Hanya file gambar (jpg/jpeg/png/gif/webp) yang diizinkan.';
        } else {
            // Path "normal": whitelist ekstensi gambar + getimagesize
            // (tetap ada supaya challenge polyglot tetap masuk akal bagi
            // peserta yang tidak menemukan jalur PHP-denied-tetap-disimpan)
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $info = @getimagesize($f['tmp_name']);
            if (!in_array($ext, $allowed, true)) {
                $err = 'Hanya file gambar yang diizinkan (jpg, jpeg, png, gif, webp).';
            } elseif ($info === false) {
                $err = 'File bukan gambar yang valid.';
            } else {
                $dest = $uploadDir . '/' . basename($f['name']);
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    chmod($dest, 0644);
                    $msg = 'Avatar berhasil diupload.';
                    $uploadedPath = $webPath . '/' . rawurlencode(basename($f['name']));
                } else {
                    $err = 'Gagal menyimpan file.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Change Avatar</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
    <h1>Change Avatar</h1>
    <p>Upload foto profil Anda. Hanya file gambar (jpg/png/gif/webp) yang diperbolehkan</p>
    <?php if ($err): ?><p style="color:#dc3545"><?= htmlspecialchars($err) ?></p><?php endif; ?>
    <?php if ($msg): ?>
        <p style="color:#28a745"><?= htmlspecialchars($msg) ?></p>
        <?php if ($uploadedPath): ?>
        <p>Preview: <a href="<?= htmlspecialchars($uploadedPath) ?>" target="_blank"><?= htmlspecialchars($uploadedPath) ?></a></p>
        <?php endif; ?>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="avatar" accept="image/*" required>
        <button type="submit" class="btn">Upload</button>
    </form>
    <p class="back"><a href="dashboard.php">&laquo; Back to Dashboard</a></p>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
