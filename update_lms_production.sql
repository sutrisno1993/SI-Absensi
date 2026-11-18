-- =======================================================================
-- SQL SCRIPT MIGRATION / UPDATE DATABASE PRODUCTION
-- SISTEM INFORMASI PRESENSI & LMS SMKS 1 MARET JAKARTA
-- FITUR:
--   1. LMS KBM & Presensi Sesi Jam Pelajaran
--   2. Asesmen Live Interaktif (Diagnostik/Formatif/Kuis/Refleksi + PG A-E & Essay)
--   3. LKPD Kelompok Kolaboratif (Lembar Kerja Peserta Didik Digital)
-- =======================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------
-- BAGIAN 1: MODUL LMS & PRESENSI KBM
-- -----------------------------------------------------------------------

-- 1. Tabel Master Mata Pelajaran (mapel)
CREATE TABLE IF NOT EXISTS `mapel` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_mapel` VARCHAR(50) DEFAULT NULL,
  `nama_mapel` VARCHAR(150) NOT NULL,
  `kategori` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Relasi Guru Mengajar Mapel Spesifik di Kelas Tertentu
CREATE TABLE IF NOT EXISTS `guru_kelas_mapel` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guru_id` INT(11) UNSIGNED NOT NULL,
  `kelas_id` INT(11) UNSIGNED NOT NULL,
  `mapel_id` INT(11) UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guru_id_kelas_id_mapel_id` (`guru_id`, `kelas_id`, `mapel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Sesi Pertemuan KBM
CREATE TABLE IF NOT EXISTS `kbm_sesi` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guru_id` INT(11) UNSIGNED NOT NULL,
  `kelas_id` INT(11) UNSIGNED NOT NULL,
  `mapel_id` INT(11) UNSIGNED NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_ke` VARCHAR(50) DEFAULT NULL,
  `judul_materi` VARCHAR(255) NOT NULL,
  `deskripsi_materi` TEXT DEFAULT NULL,
  `status` ENUM('draft', 'berlangsung', 'selesai') NOT NULL DEFAULT 'draft',
  `token_sesi` VARCHAR(64) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guru_id_kelas_id_tanggal` (`guru_id`, `kelas_id`, `tanggal`),
  KEY `token_sesi` (`token_sesi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Presensi Siswa Mandiri per Jam Sesi KBM
CREATE TABLE IF NOT EXISTS `kbm_presensi` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sesi_id` INT(11) UNSIGNED NOT NULL,
  `siswa_id` INT(11) UNSIGNED NOT NULL,
  `status` ENUM('H', 'S', 'I', 'A') NOT NULL DEFAULT 'H',
  `catatan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sesi_id_siswa_id` (`sesi_id`, `siswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------
-- BAGIAN 2: MODUL ASESMEN LIVE INTERAKTIF (DIAGNOSTIK / FORMATIF)
-- -----------------------------------------------------------------------

-- 5. Tabel Paket Asesmen Live (Formatif, Diagnostik, Kuis Live, Refleksi)
CREATE TABLE IF NOT EXISTS `asesmen` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sesi_id` INT(11) UNSIGNED NOT NULL,
  `guru_id` INT(11) UNSIGNED NOT NULL,
  `judul_asesmen` VARCHAR(200) NOT NULL,
  `tipe` ENUM('formatif', 'diagnostik', 'kuis_live', 'refleksi') NOT NULL DEFAULT 'formatif',
  `durasi_menit` INT(5) NOT NULL DEFAULT 30,
  `token_live` VARCHAR(64) DEFAULT NULL,
  `status` ENUM('draft', 'aktif', 'ditutup') NOT NULL DEFAULT 'draft',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sesi_id` (`sesi_id`),
  KEY `token_live` (`token_live`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Butir Soal Asesmen (Pilihan Ganda A-E & Essay)
CREATE TABLE IF NOT EXISTS `asesmen_soal` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asesmen_id` INT(11) UNSIGNED NOT NULL,
  `nomor_urut` INT(5) NOT NULL DEFAULT 1,
  `tipe_soal` ENUM('pg', 'essay') NOT NULL DEFAULT 'pg',
  `pertanyaan` TEXT NOT NULL,
  `opsi_a` TEXT DEFAULT NULL,
  `opsi_b` TEXT DEFAULT NULL,
  `opsi_c` TEXT DEFAULT NULL,
  `opsi_d` TEXT DEFAULT NULL,
  `opsi_e` TEXT DEFAULT NULL,
  `kunci_pg` CHAR(1) DEFAULT NULL,
  `pedoman_essay` TEXT DEFAULT NULL,
  `bobot` INT(5) NOT NULL DEFAULT 10,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asesmen_id` (`asesmen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Jawaban & Skor Siswa (Otomatis PG + Manual Essay)
CREATE TABLE IF NOT EXISTS `asesmen_jawaban_siswa` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asesmen_id` INT(11) UNSIGNED NOT NULL,
  `soal_id` INT(11) UNSIGNED NOT NULL,
  `siswa_id` INT(11) UNSIGNED NOT NULL,
  `jawaban_pg` CHAR(1) DEFAULT NULL,
  `jawaban_essay` TEXT DEFAULT NULL,
  `is_benar` TINYINT(1) DEFAULT NULL,
  `skor` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `catatan_guru` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asesmen_id_siswa_id` (`asesmen_id`, `siswa_id`),
  KEY `soal_id_siswa_id` (`soal_id`, `siswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------
-- BAGIAN 3: MODUL LKPD (LEMBAR KERJA PESERTA DIDIK) KELOMPOK
-- -----------------------------------------------------------------------

-- 8. Tabel Master LKPD Lembar Kerja Digital
CREATE TABLE IF NOT EXISTS `lkpd` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sesi_id` INT(11) UNSIGNED NOT NULL,
  `guru_id` INT(11) UNSIGNED NOT NULL,
  `judul_lkpd` VARCHAR(255) NOT NULL,
  `petunjuk` TEXT DEFAULT NULL,
  `file_panduan` VARCHAR(255) DEFAULT NULL,
  `metode_kelompok` ENUM('acak', 'manual_guru', 'mandiri_siswa') NOT NULL DEFAULT 'mandiri_siswa',
  `tenggat_waktu` DATETIME DEFAULT NULL,
  `token_lkpd` VARCHAR(64) DEFAULT NULL,
  `status` ENUM('draft', 'aktif', 'ditutup') NOT NULL DEFAULT 'aktif',
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sesi_id` (`sesi_id`),
  KEY `token_lkpd` (`token_lkpd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabel Kelompok pada LKPD
CREATE TABLE IF NOT EXISTS `lkpd_kelompok` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lkpd_id` INT(11) UNSIGNED NOT NULL,
  `nama_kelompok` VARCHAR(100) NOT NULL,
  `nomor_kelompok` INT(5) NOT NULL DEFAULT 1,
  `ketua_siswa_id` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lkpd_id` (`lkpd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabel Anggota Kelompok & Peran
CREATE TABLE IF NOT EXISTS `lkpd_anggota` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kelompok_id` INT(11) UNSIGNED NOT NULL,
  `siswa_id` INT(11) UNSIGNED NOT NULL,
  `peran` VARCHAR(100) DEFAULT NULL,
  `nilai_individu` DECIMAL(5,2) DEFAULT NULL,
  `catatan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelompok_id_siswa_id` (`kelompok_id`, `siswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabel Pengumpulan Jawaban, Dokumentasi Praktik & Nilai Kelompok
CREATE TABLE IF NOT EXISTS `lkpd_jawaban` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kelompok_id` INT(11) UNSIGNED NOT NULL,
  `lkpd_id` INT(11) UNSIGNED NOT NULL,
  `ringkasan_kerja` TEXT DEFAULT NULL,
  `file_lampiran` VARCHAR(255) DEFAULT NULL,
  `link_eksternal` VARCHAR(255) DEFAULT NULL,
  `skor_kelompok` DECIMAL(5,2) DEFAULT NULL,
  `feedback_guru` TEXT DEFAULT NULL,
  `status_pengumpulan` ENUM('draft', 'dikumpulkan', 'dinilai') NOT NULL DEFAULT 'draft',
  `waktu_kumpul` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelompok_id` (`kelompok_id`),
  KEY `lkpd_id` (`lkpd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =======================================================================
-- SELESAI. Silakan import berkas ini di phpMyAdmin / MySQL Production.
-- =======================================================================
