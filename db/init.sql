-- Database "lain": episodes + secrets + tabel decoy
CREATE DATABASE IF NOT EXISTS lain CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lain;

CREATE TABLE IF NOT EXISTS episodes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  description TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO episodes (title, description) VALUES
('WEIRD', 'Lain Iwakura menerima email dari Chisa Yomoda, teman sekelas yang dikabarkan meninggal dunia. Di dalam email, Chisa mengatakan bahwa ia tidak mati, melainkan hanya melepas tubuhnya dan sekarang hidup di dalam Wired. Pesan itu menyebar di kalangan murid lain, dan rasa penasaran Lain terhadap dunia digital pun mulai tumbuh.'),
('GIRLS', 'Lain meminta Navi (komputer pribadi) baru kepada ayahnya dan pergi ke Tachibana General Laboratories untuk merakitnya. Bersama Alice, Julie, dan Reika, ia berkunjung ke klub Cyberia. Di sana Lain mendengar rumor tentang seorang hacker berbahaya di Wired, dan malam itu terjadi insiden menembak di Cyberia yang disebabkan pengguna narkoba Accela.'),
('PSYCHE', 'Sebuah chip misterius bernama Psyche dikirim ke rumah Lain tanpa nama pengirim. Setelah dipasang pada Navi-nya, kemampuan komputer Lain melonjak luar biasa. Para pria berjas lab terus menguntitnya, sementara rumor di Cyberia mulai mengaitkan kejadian-kejadian aneh dengan kehadiran Lain.'),
('RELIGION', 'Nama kelompok misterius Knights of the Eastern Calculus mulai muncul. Lain diterorir oleh orang-orang tak dikenal dan menerima kiriman barang yang tidak ia minta. Ia mulai memasang sensor pada tubuhnya untuk bereksperimen, dan sosok Masami Iwakura perlahan mulai mendekati kehidupannya.'),
('DISTORTION', 'Obat biotechnological Accela menyebar di Cyberia dan membuat penggunanya bisa berpikir ribuan kali lebih cepat. Lain menyelidiki hubungan Accela dengan Knights, sementara kejadian penembakan di klub dan perilaku aneh adiknya, Mika, mulai mengacaukan kehidupan nyata Lain. Batas antara dunia nyata dan Wired semakin kabur.'),
('KIDS', 'Fenomena aneh terjadi: anak-anak di seluruh dunia menatap ke langit sambil menonton layar berisi derau statis. Lain juga melihat sosok dirinya sendiri yang berjalan di tempat lain pada saat bersamaan. Eksperimen lama tentang pencitraan pikiran anak-anak ke dalam Wired mulai terungkap.'),
('SOCIETY', 'Lain mencoba mengubah citranya agar terlihat lebih ekspresif dan terhubung dengan teman-temannya. Namun di Cyberia, rumor menyebar tentang sesosok "Lain" di Wired yang berbeda dari dirinya. Penggerebekan terhadap Knights terjadi, dan keberadaan dua Lain yang berbeda semakin dirasakan oleh orang-orang di sekitarnya.'),
('LANDMARK', 'Masami Iwakura muncul di depan Lain dan mengaku sebagai penguasa sekaligus "tuhan" dari Wired. Lain bertemu dengan seorang murid perempuan yang mengaku pernah diselamatkan oleh Lain di dunia maya. Arus data dan kekuatan yang tak seharusnya dimiliki manusia mulai terkumpul di sekitar Lain.'),
('PROTOCOL', 'Sejarah protokol jaringan terungkap: Masami Eiri, mantan insinyur Tachibana Labs, menyisipkan Protocol 7 ke dalam arsitektur internet untuk menghubungkan Wired langsung dengan kesadaran manusia melalui resonansi Schumann. Lain mulai mengenal sosok asli Eiri dan maksud tersembunyinya.'),
('LOVE', 'Rahasia gelap Alice tentang hubungannya dengan seorang guru menyebar di seluruh Wired. Untuk menyelamatkan Alice, Lain menggunakan kekuatannya menghapus ingatan semua orang tentang rumor tersebut, termasuk ingatan Alice akan dirinya sendiri. Namun tindakan itu menimbulkan konsekuensi besar bagi eksistensi Lain.'),
('INFO', 'Identitas asli Lain mulai terkuak: dirinya adalah program buatan yang diciptakan oleh Eiri untuk menjadi jembatan antara dunia nyata dan Wired. Berbagai "Lain" dari lapisan realitas yang berbeda saling berbicara, dan Lain mulai mempertanyakan ingatan serta masa lalunya yang sebenarnya.'),
('LANDSCAPE', 'Dunia di sekitar Lain perlahan membubarkan diri menjadi data. Keluarganya ternyata tidak pernah benar-benar ada, rumahnya menjadi kosong, dan kota yang ia tempati hanyalah lapisan informasi. Lain kini benar-benar berdiri di antara dua dunia yang tidak lagi memiliki batas.'),
('EGO', 'Di dalam Wired, Lain berhadapan dengan Masami Eiri yang mengaku sebagai dewa. Lain menyadari bahwa ia sendirilah yang sesungguhnya omnipresent, lalu menghapus Eiri. Pada akhirnya Lain memilih menjadi semua dan tidak menjadi siapa-siapa, mengamati dunia dari mana-mana, termasuk dari sisi Alice yang ia cintai.');

-- Flag disimpan di sini (di-seed ulang oleh seed.php setiap container start)
CREATE TABLE IF NOT EXISTS secrets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bucket VARCHAR(50) NOT NULL,
  content TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel umpan (decoy) supaya enumeration lebih menipu
CREATE TABLE IF NOT EXISTS knights_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  alias VARCHAR(64) NOT NULL,
  message TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO knights_messages (alias, message) VALUES
('MenInBlack', 'We have been watching you, Lain.'),
('cyberia_lore', 'The Wired remembers everything.'),
('phantom', 'Present day. Present time. Ha ha ha...');

-- Database "admin": TIDAK ada kredensial asli, hanya tabel jebakan
CREATE DATABASE IF NOT EXISTS admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE admin;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password_hash CHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (username, password_hash, role) VALUES
('Lain', '$2y$10$Q8Z0m5YcH0jChS09dPScR.lXWLaSxhWigvDFcgX2mn1/h9DcRm/yC', 'admin'),
('eiri', '$2y$10$deadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbeefdeadbe', 'superadmin');

USE lain;

-- Kredensial admin asli ada di sini (bcrypt username:password), di-seed oleh seed.php
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

GRANT ALL PRIVILEGES ON lain.* TO 'lain'@'%';
GRANT ALL PRIVILEGES ON admin.* TO 'lain'@'%';
FLUSH PRIVILEGES;
