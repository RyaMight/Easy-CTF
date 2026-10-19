<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// FLAG 2 hanya ada di sini (diambil dari DB, bukan env langsung)
$flag = '(unable to load)';
$res = $conn->query("SELECT content FROM lain.secrets WHERE bucket = 'flag_admin' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $flag = $row['content'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin']) ?></h1>
    <p>You have admin access.</p>
    <div class="flag">[FLAG] <?= htmlspecialchars($flag) ?></div>
    <p class="back"><a href="../home.php">&laquo; Back to list</a> | <a href="logout.php">Logout</a></p>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
