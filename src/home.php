<?php
require_once __DIR__ . '/includes/db.php';

$FLAG_HTML = getenv('FLAG_HTML') ?: 'LainCTF{fl4g_1n_pl41n_s1ght}';

$result = $conn->query("SELECT id, title FROM episodes ORDER BY id ASC");
$episodes = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>List of Episodes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<!-- NAVIGATORS: nothing interesting here. Stop looking at the source code. -->
<!-- ... /connect to the Wired ... -->
<!-- FLAG: <?= htmlspecialchars($FLAG_HTML) ?> -->
<div class="container">
    <h1>List of Episodes</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Action</th>
        </tr>
        <?php foreach ($episodes as $ep): ?>
        <tr>
            <td><?= (int)$ep['id'] ?></td>
            <td><?= htmlspecialchars($ep['title']) ?></td>
            <td><a href="detail.php?id=<?= (int)$ep['id'] ?>">View Details</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
