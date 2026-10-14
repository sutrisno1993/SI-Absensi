-- ====================================================================
-- SKRIP UPDATE DATABASE PRODUCTION - SI-ABSEN
-- Tanggal: 14 Oktober 2026
-- Deskripsi:
-- 1. Menambahkan status 'P' (PKL) ke tabel `absensi`
-- 2. Membuat tabel baru `kalender_pendidikan` untuk hari libur & acara
-- ====================================================================

-- 1. Tambah enum 'P' (PKL) ke kolom status di tabel absensi
ALTER TABLE `absensi` 
MODIFY COLUMN `status` ENUM('H', 'S', 'I', 'A', 'P') NOT NULL DEFAULT 'H';

-- 2. Buat tabel kalender_pendidikan
CREATE TABLE IF NOT EXISTS `kalender_pendidikan` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_agenda` VARCHAR(150) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `tipe` ENUM('libur', 'acara') NOT NULL DEFAULT 'libur',
  `keterangan_kbm` ENUM('bebas_absen', 'wajib_absen') NOT NULL DEFAULT 'bebas_absen',
  `target_tingkat` VARCHAR(20) NOT NULL DEFAULT 'semua',
  `target_kelas_id` INT(11) UNSIGNED DEFAULT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tanggal_mulai` (`tanggal_mulai`),
  KEY `tanggal_selesai` (`tanggal_selesai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. (Opsional) Catat ke tabel migrations CodeIgniter agar spark migrate sinkron
INSERT INTO `migrations` (`version`, `class`, `group`, `namespace`, `time`, `batch`)
SELECT '2026-10-14-193000', 'App\\Database\\Migrations\\CreateKalenderPendidikanAndAddStatusP', 'default', 'App', UNIX_TIMESTAMP(), 
       COALESCE((SELECT MAX(batch) + 1 FROM `migrations` m), 1)
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `version` = '2026-10-14-193000'
);
