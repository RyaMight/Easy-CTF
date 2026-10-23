<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

$id = $_GET['id'] ?? '1';

// --- WAF ringan: blok SPASI dan operator ">" saja, supaya solusinya
// sederhana dan pasti: sqlmap --tamper=space2comment,between
//  - spasi diblok  -> space2comment mengubah spasi jadi /**/
//  - ">" diblok    -> between mengubah ">" jadi BETWEEN x AND y
// UNION/SELECT/information_schema tetap lolos -> dump tetap jalan.
function lain_waf_check(string $input): bool
{
    if (strpos($input, ' ') !== false) {
        return false; // spasi diblokir
    }
    if (strpos($input, '>') !== false) {
        return false; // operator > diblokir
    }
    return true;
}

$wafBlocked = !lain_waf_check($id);

$sql = "SELECT id, title, description FROM lain_content.episodes WHERE id = " . ($wafBlocked ? '0' : $id);

// Error DITAMPILKAN (easy) - sqlmap error-based jalan langsung
$errorPage = '<h1>Not Found</h1><p>Episode tidak ditemukan.</p><a class="btn" href="home.php">Back to Home</a>';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Episode Detail</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<?php
if ($wafBlocked) {
    echo '<p style="color:#dc3545;font-family:monospace">[WAF] Request blocked: forbidden keyword detected.</p>';
}
try {
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $imgId = (int)$row['id'];
        $imgExt = '';
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $e) {
            if (file_exists(__DIR__ . "/images/ep{$imgId}.{$e}")) {
                $imgExt = $e;
                break;
            }
        }
        ?>
        <h1>Episode Detail</h1>
        <div class="detail">
            <?php if ($imgExt): ?>
            <div class="detail-img">
                <img src="images/ep<?= $imgId ?>.<?= $imgExt ?>" alt="<?= htmlspecialchars($row['title']) ?>">
            </div>
            <?php endif; ?>
            <div class="detail-info">
                <h2>Title: <?= htmlspecialchars($row['title']) ?></h2>
                <p>Description: <?= nl2br(htmlspecialchars($row['description'])) ?></p>
                <p>ID: <?= $imgId ?></p>
            </div>
        </div>
        <a class="btn" href="home.php">Back to Home</a>
        <?php
    } else {
        echo $errorPage;
    }
} catch (mysqli_sql_exception $e) {
    // Easy: tampilkan pesan error asli untuk membantu error-based injection
    echo '<p style="color:#dc3545;font-family:monospace">SQL Error: '
        . htmlspecialchars($e->getMessage()) . '</p>';
    echo $errorPage;
}
?>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
