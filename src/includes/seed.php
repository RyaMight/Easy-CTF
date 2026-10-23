<?php
// Dipanggil via Docker CMD setiap kali container start.
// Menimpa isi tabel secrets (flag) dan memastikan akun admin MD5 sinkron dengan env.

require_once __DIR__ . '/db.php';

$FLAG_SQLI = getenv('FLAG_SQLI') ?: 'LainCTF{bl1nd_but_n0t_br0k3n}';
$FLAG_DECOY = getenv('FLAG_DECOY') ?: 'LainCTF{d3c0y_tr4p_th1s_1s_n0t_th3_fl4g}';
$ADMIN_USERNAME = getenv('ADMIN_USERNAME') ?: 'lain_chrono';
$ADMIN_PASSWORD = getenv('ADMIN_PASSWORD') ?: 'Tr1gger_Sh1nji_1998';

// FLAG 1 - bucket disamarkan jadi "notes" agar tidak langsung kelihatan;
// peserta harus membaca isi tiap baris, bukan hanya nama bucket-nya.
$conn->query("DELETE FROM lain.secrets");
$conn->query("INSERT INTO lain.secrets (bucket, content) VALUES ('notes', '" . $conn->real_escape_string($FLAG_SQLI) . "')");
$conn->query("INSERT INTO lain.secrets (bucket, content) VALUES ('notes', '" . $conn->real_escape_string($FLAG_DECOY) . "')");

// Konten episode di database terpisah agar dump database "lain" tetap ringan

// FLAG 2 TIDAK disimpan di database - hanya muncul di dashboard admin
// setelah login (diambil dari env langsung). Dump tabel secrets via SQLi
// tidak akan pernah mendapatkan flag ini.
// Hapus sisa flag_admin lama bila ada (dari init.sql versi sebelumnya).
$conn->query("DELETE FROM lain.secrets WHERE bucket='flag_admin'");

// Decoy - sudah dimasukkan bersama flag di atas, bucket sama-sama "notes"

// Admin: MD5 sinkron setiap start.
// Easy: hash = md5(password), crackable di hashcat -m 0 / john / crackstation
// dengan wordlist standar. Laptop biasa sanggup.
$hash = md5($ADMIN_PASSWORD);
$conn->query("DELETE FROM lain.admin_users WHERE role='admin'");
$stmt = $conn->prepare("INSERT INTO lain.admin_users (username, password_hash, role) VALUES (?, ?, 'admin')");
$stmt->bind_param('ss', $ADMIN_USERNAME, $hash);
$stmt->execute();
$stmt->close();

// Folder upload avatar: sengaja dikubur di dalam sarang folder berlapis
// di bawah images/ — menyamar sebagai struktur cache/asset internal.
// Dirsearch tidak akan menebak path sedalam ini; petunjuk sah hanya dari
// URL preview di response upload.
$nest = __DIR__ . '/../../images/.cache/assets/gen/thumbs/.store/lain/avatar';
if (!is_dir($nest)) {
    mkdir($nest, 0755, true);
}
// Seeder dijalankan sebagai root; Apache jalan sebagai www-data,
// jadi folder upload harus di-chown agar bisa ditulisi saat upload.
@chown($nest, 'www-data');
@chgrp($nest, 'www-data');
// .htaccess di folder akhir: eksekusi PHP di dalam file gambar
// (AddHandler salah konfigurasi ala developer)
file_put_contents($nest . '/.htaccess', "AddType application/x-httpd-php .png .jpg .jpeg .gif .webp\n");
@chown($nest . '/.htaccess', 'www-data');
@chgrp($nest . '/.htaccess', 'www-data');

// FLAG_UPLOAD: bukan env, tapi file flag.txt yang dikubur di lokasi
// berlapis-lapis yang tidak diduga di filesystem container (di luar
// docroot). Hanya bisa ditemukan setelah dapat RCE via polyglot upload.
$flagUpload = getenv('FLAG_UPLOAD') ?: 'LainCTF{p0lygl0t_sh3ll_1n_th3_w1r3d}';
$flagDir = '/srv/.config/tachibana/.internal/navi/lain/.wired';
if (!is_dir($flagDir)) {
    mkdir($flagDir, 0700, true);
}
file_put_contents($flagDir . '/flag.txt', $flagUpload . "\n");
chmod($flagDir . '/flag.txt', 0644);
// www-data harus bisa menelusuri path sampai ke file (folder 0700 root
// membuat cat dari webshell gagal).
@chown($flagDir, 'www-data');
@chgrp($flagDir, 'www-data');
@chown($flagDir . '/flag.txt', 'www-data');
@chgrp($flagDir . '/flag.txt', 'www-data');

echo "Seeder OK\n";
