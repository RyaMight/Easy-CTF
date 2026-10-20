<?php
// Dipanggil via Docker CMD setiap kali container start.
// Menimpa isi tabel secrets (flag) dan memastikan akun admin MD5 sinkron dengan env.

require_once __DIR__ . '/db.php';

$FLAG_SQLI = getenv('FLAG_SQLI') ?: 'LainCTF{bl1nd_but_n0t_br0k3n}';
$FLAG_DECOY = getenv('FLAG_DECOY') ?: 'LainCTF{d3c0y_tr4p_th1s_1s_n0t_th3_fl4g}';
$ADMIN_USERNAME = getenv('ADMIN_USERNAME') ?: 'lain_chrono';
$ADMIN_PASSWORD = getenv('ADMIN_PASSWORD') ?: 'Tr1gger_Sh1nji_1998';

// FLAG 1 - hidden di kolom file_priv profil (butuh UNION kolom ke-4)
$conn->query("DELETE FROM lain.secrets WHERE bucket='flag_sqli'");
$conn->query("INSERT INTO lain.secrets (bucket, content) VALUES ('flag_sqli', '" . $conn->real_escape_string($FLAG_SQLI) . "')");

// FLAG 2 TIDAK disimpan di database - hanya muncul di dashboard admin
// setelah login (diambil dari env langsung). Dump tabel secrets via SQLi
// tidak akan pernah mendapatkan flag ini.
// Hapus sisa flag_admin lama bila ada (dari init.sql versi sebelumnya).
$conn->query("DELETE FROM lain.secrets WHERE bucket='flag_admin'");

// Decoy
$conn->query("DELETE FROM lain.secrets WHERE bucket='decoy'");
$conn->query("INSERT INTO lain.secrets (bucket, content) VALUES ('decoy', '" . $conn->real_escape_string($FLAG_DECOY) . "')");

// Admin: MD5 sinkron setiap start.
// Easy: hash = md5(password), crackable di hashcat -m 0 / john / crackstation
// dengan wordlist standar. Laptop biasa sanggup.
$hash = md5($ADMIN_PASSWORD);
$conn->query("DELETE FROM lain.admin_users WHERE role='admin'");
$stmt = $conn->prepare("INSERT INTO lain.admin_users (username, password_hash, role) VALUES (?, ?, 'admin')");
$stmt->bind_param('ss', $ADMIN_USERNAME, $hash);
$stmt->execute();
$stmt->close();

echo "Seeder OK\n";
