<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nisn',
        'nama_siswa',
        'jenis_kelamin',
        'kelas_id',
        'status',
        'alasan_nonaktif',
        'tanggal_nonaktif',
        'catatan_nonaktif',
        'file_surat_nonaktif',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableColumns();
    }

    /**
     * Pastikan struktur kolom jenis_kelamin dan mutasi selalu tersedia di tabel siswa
     */
    public function ensureTableColumns(): void
    {
        try {
            $db = $this->db ?? \Config\Database::connect();
            $fields = $db->getFieldNames($this->table);

            if (! in_array('jenis_kelamin', $fields, true)) {
                $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `jenis_kelamin` ENUM('L', 'P') NOT NULL DEFAULT 'L' AFTER `nama_siswa`");
            }
            if (! in_array('alasan_nonaktif', $fields, true)) {
                $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `alasan_nonaktif` ENUM('pindah_sekolah', 'mengundurkan_diri', 'dikeluarkan', 'lainnya') NULL AFTER `status`");
            }
            if (! in_array('tanggal_nonaktif', $fields, true)) {
                $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `tanggal_nonaktif` DATE NULL AFTER `alasan_nonaktif`");
            }
            if (! in_array('catatan_nonaktif', $fields, true)) {
                $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `catatan_nonaktif` TEXT NULL AFTER `tanggal_nonaktif`");
            }
            if (! in_array('file_surat_nonaktif', $fields, true)) {
                $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `file_surat_nonaktif` VARCHAR(255) NULL AFTER `catatan_nonaktif`");
            }
        } catch (\Throwable $e) {
            log_message('error', 'ensureTableColumns error: ' . $e->getMessage());
        }
    }

    /**
     * Dapatkan siswa dengan nama kelas, opsional filter kelas dan status
     */
    public function getSiswaWithKelas(?int $kelasId = null, ?string $status = null)
    {
        $this->ensureTableColumns();

        $builder = $this->select('siswa.*, kelas.nama_kelas')
                        ->join('kelas', 'kelas.id = siswa.kelas_id', 'inner');
        
        if ($kelasId !== null) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        if ($status !== null && in_array($status, ['Aktif', 'Nonaktif'])) {
            $builder->where('siswa.status', $status);
        }

        return $builder->orderBy('siswa.nama_siswa', 'ASC')->findAll();
    }

    /**
     * Ambil hanya siswa aktif pada kelas tertentu
     */
    public function getSiswaAktifByKelas(int $kelasId): array
    {
        $this->ensureTableColumns();

        return $this->where('kelas_id', $kelasId)
                    ->where('status', 'Aktif')
                    ->orderBy('nama_siswa', 'ASC')
                    ->findAll();
    }

    /**
     * Rekap dinamika mutasi siswa untuk rapat evaluasi semester (Awal vs Keluar vs Akhir per gender)
     */
    public function getDinamikaSiswaKelas(int $kelasId, ?string $startDate = null, ?string $endDate = null): array
    {
        $this->ensureTableColumns();

        // Seluruh siswa yang tercatat di kelas ini
        $allSiswa = $this->where('kelas_id', $kelasId)->orderBy('nama_siswa', 'ASC')->findAll();

        $siswaAktif = [];
        $siswaMutasi = [];

        foreach ($allSiswa as $s) {
            $isNonaktif = ($s['status'] === 'Nonaktif');
            $tglKeluar  = $s['tanggal_nonaktif'] ?? null;

            // Jika dalam filter tanggal tertentu
            $keluarDalamPeriode = false;
            if ($isNonaktif) {
                if ($startDate && $endDate && $tglKeluar) {
                    if ($tglKeluar >= $startDate && $tglKeluar <= $endDate) {
                        $keluarDalamPeriode = true;
                    }
                } else {
                    $keluarDalamPeriode = true;
                }
            }

            if ($keluarDalamPeriode) {
                $siswaMutasi[] = $s;
            } else {
                $siswaAktif[] = $s;
            }
        }

        // Komposisi Akhir (Siswa Aktif Saat Ini)
        $akhirL = count(array_filter($siswaAktif, static fn($s) => ($s['jenis_kelamin'] ?? 'L') === 'L'));
        $akhirP = count(array_filter($siswaAktif, static fn($s) => ($s['jenis_kelamin'] ?? 'L') === 'P'));
        $akhirTotal = count($siswaAktif);

        // Komposisi Mutasi Keluar
        $mutasiL = count(array_filter($siswaMutasi, static fn($s) => ($s['jenis_kelamin'] ?? 'L') === 'L'));
        $mutasiP = count(array_filter($siswaMutasi, static fn($s) => ($s['jenis_kelamin'] ?? 'L') === 'P'));
        $mutasiTotal = count($siswaMutasi);

        // Komposisi Awal (Aktif Sekarang + Yang Keluar pada periode ini)
        $awalL = $akhirL + $mutasiL;
        $awalP = $akhirP + $mutasiP;
        $awalTotal = $akhirTotal + $mutasiTotal;

        return [
            'awal' => [
                'total' => $awalTotal,
                'l'     => $awalL,
                'p'     => $awalP,
            ],
            'mutasi' => [
                'total'  => $mutasiTotal,
                'l'      => $mutasiL,
                'p'      => $mutasiP,
                'daftar' => $siswaMutasi,
            ],
            'akhir' => [
                'total'  => $akhirTotal,
                'l'      => $akhirL,
                'p'      => $akhirP,
                'daftar' => $siswaAktif,
            ],
        ];
    }

    /**
     * Heuristik cerdas deteksi gender berdasarkan nama Indonesia
     */
    public static function guessGenderByName(string $nama): string
    {
        $namaLower = strtolower(trim($nama));
        $words = preg_split('/[^a-z]+/', $namaLower, -1, PREG_SPLIT_NO_EMPTY);

        // Kata kunci spesifik wanita (bobot tinggi)
        $femaleKeywords = [
            'putri', 'siti', 'ayu', 'dewi', 'anisa', 'annisa', 'rahma', 'rahmawati', 'lestari',
            'indah', 'wati', 'nia', 'mega', 'nabila', 'maulida', 'maya', 'rina', 'sarah',
            'zahra', 'aulia', 'risma', 'salma', 'novita', 'intan', 'safitri', 'bella', 'cindy',
            'desi', 'dian', 'eka', 'fanny', 'gita', 'hana', 'ika', 'jessica', 'kartika', 'laras',
            'mutia', 'nadia', 'olivia', 'pratiwi', 'qonita', 'ratih', 'shinta', 'tiara', 'utami',
            'vina', 'widya', 'yulia', 'zulfa', 'amelia', 'syifa', 'adinda', 'clara', 'fitri',
            'fitria', 'khairunisa', 'nurul', 'amalia', 'marwah', 'chandra', 'chika', 'natasya',
            'tasya', 'wulan', 'sekar', 'anggun', 'cantika', 'silvia', 'silvi', 'nadya', 'novi',
            'erika', 'elisa', 'sherly', 'vania', 'keysha', 'keisha', 'fathia', 'salwa', 'nayla',
            'nayra', 'najwa', 'hanifah', 'humaira', 'salsabila', 'khadijah', 'aisha', 'aisyah',
            'safira', 'intan', 'nur', 'tri'
        ];

        // Kata kunci spesifik pria (bobot tinggi)
        $maleKeywords = [
            'muhammad', 'moch', 'mohamad', 'ahmad', 'budi', 'putra', 'agung', 'bagus', 'dimas',
            'bayu', 'rizky', 'rizki', 'fajar', 'ilham', 'pratama', 'gilang', 'hendra', 'eko',
            'joko', 'dani', 'doni', 'kevin', 'david', 'wahyu', 'arif', 'andi', 'aditya', 'aldi',
            'alif', 'arya', 'bagas', 'danang', 'dedi', 'fauzi', 'galih', 'hafiz', 'iqbal', 'jaka',
            'krisna', 'lukman', 'maulana', 'naufal', 'oki', 'pandu', 'rian', 'ryan', 'surya',
            'taufik', 'umar', 'vicky', 'yoga', 'zaki', 'fahmi', 'raka', 'raditya', 'hafidz',
            'faiz', 'farhan', 'guntur', 'tegar', 'bima', 'satria', 'ridho', 'reza', 'alfian',
            'angga', 'anton', 'billy', 'caesar', 'darma', 'erik', 'fauzan', 'ferdi', 'habib',
            'imam', 'irfan', 'jamal', 'kurnia', 'miftah', 'nizar', 'panji', 'rendi', 'rendy',
            'salim', 'sigit', 'tomi', 'yuda', 'yusuf', 'zulkifli', 'firman', 'hadi', 'lukas'
        ];

        // Cek kata per kata
        foreach ($words as $w) {
            if (in_array($w, $femaleKeywords, true)) {
                return 'P';
            }
            if (in_array($w, $maleKeywords, true)) {
                return 'L';
            }
        }

        // Cek akhiran nama khas wanita Indonesia
        if (preg_match('/(wati|ningsih|astuti|safitri|fitria|dini)$/i', $namaLower)) {
            return 'P';
        }

        // Cek akhiran nama khas pria Indonesia
        if (preg_match('/(putra|saputra|syahputra|pratama|hidayat|wan|to|no)$/i', $namaLower)) {
            return 'L';
        }

        // Default: Laki-laki
        return 'L';
    }

    /**
     * Auto-detect dan update jenis kelamin seluruh siswa atau per kelas
     */
    public function autoDetectGender(?int $kelasId = null): array
    {
        $this->ensureTableColumns();

        $builder = $this->builder();
        if ($kelasId !== null) {
            $builder->where('kelas_id', $kelasId);
        }
        $siswaList = $builder->get()->getResultArray();

        $totalUpdated = 0;
        $totalL = 0;
        $totalP = 0;

        foreach ($siswaList as $s) {
            $detected = self::guessGenderByName($s['nama_siswa']);
            $this->update($s['id'], ['jenis_kelamin' => $detected]);
            $totalUpdated++;
            if ($detected === 'P') {
                $totalP++;
            } else {
                $totalL++;
            }
        }

        return [
            'total' => $totalUpdated,
            'l'     => $totalL,
            'p'     => $totalP,
        ];
    }
}
