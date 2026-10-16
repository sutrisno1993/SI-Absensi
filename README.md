# 🏫 SI-ABSEN: Sistem Informasi Presensi & Pembinaan Siswa
### SMK 11 Maret

Aplikasi web terpadu untuk pencatatan presensi siswa secara real-time, monitoring keterlambatan di gerbang, presensi kedatangan guru mengajar, riwayat kasus pembinaan kesiswaan, hingga evaluasi dinamika rombel pada rapat akhir semester.

---

## 📚 Petunjuk Penggunaan Lengkap
Panduan operasional detail untuk setiap peran (*role*) telah disusun lengkap pada berkas:
👉 **[PANDUAN_PENGGUNAAN.md](PANDUAN_PENGGUNAAN.md)**

Daftar panduan peran yang tersedia di dalamnya:
- 👑 **Administrator (Admin)**: Konfigurasi master guru, rombel kelas, siswa & mutasi, gender L/P, pengguna, jadwal guru, kalender libur, monitoring, riwayat pembinaan, dan rapat evaluasi semester.
- 👨‍🏫 **Wali Kelas (Walas)**: Filter presensi rentang tanggal bebas, input & koreksi presensi susulan, penandaan libur kelas, rekapitulasi keterlambatan siswa, laporan dinamika L/P rapat semester, dan akun PJ kelas.
- 👮‍♂️ **Guru Piket**: Absensi kedatangan guru mengajar per shift (Pagi/Siang), pencatatan siswa terlambat dengan sinkronisasi otomatis, dan share laporan WhatsApp grup.
- 🎓 **Penanggung Jawab Kelas (PJ Kelas)**: Presensi mandiri rekan sekelas di ruang kelas, upload foto dokumentasi kelas, dan laporan WhatsApp orang tua.
- 👨‍👩‍👧 **Orang Tua / Publik (Live Presensi)**: Monitoring real-time kehadiran kelas dan dokumentasi foto belajar tanpa perlu login.

---

## 🛠️ Tech Stack & Fitur Utama

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
