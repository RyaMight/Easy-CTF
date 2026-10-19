<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/ratelimit.php';

// Halaman ini "tersembunyi" - tapi flag TIDAK ada di header/view-source
header('X-Debug-Trace: tachibana-labs/protocol-7');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');

    if (!rate_limit_check('login', client_identifier(), ADMIN_RATELIMIT_MAX, ADMIN_RATELIMIT_WINDOW)) {
        http_response_code(429);
        $error = 'Too many attempts. Slow down.';
    } else {
        $stmt = $conn->prepare("SELECT password_hash FROM lain.admin_users WHERE username = ? AND role = 'admin'");
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($u . ':' . $p, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $u;
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>Admin Login</h1>
        <?php if ($error): ?><p style="color:#dc3545"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST" action="index.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p class="back"><a href="../home.php">&laquo; Back to list</a></p>
    </div>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
