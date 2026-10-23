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
        // Validasi 1: whitelist ekstensi
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        // Validasi 2: harus benar-benar gambar (getimagesize cek magic bytes)
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
    <p>Upload foto profil Anda. Hanya gambar (jpg/png/gif/webp) yang diperbolehkan.</p>
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
