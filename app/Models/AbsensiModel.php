<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiModel extends Model
{
    protected $table            = 'absensi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tanggal',
        'siswa_id',
        'status',
        'keterangan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil data absensi per kelas pada tanggal tertentu beserta daftar seluruh siswa kelas tersebut
     * Default hanya menampilkan siswa yang berstatus 'Aktif'
     */
    public function getAbsensiByDateAndKelas(string $tanggal, int $kelasId, bool $onlyActive = true): array
    {
        $db = $this->db;
        $builder = $db->table('siswa s');
        $builder->select('s.id as siswa_id, s.nisn, s.nama_siswa, s.kelas_id, s.status as status_siswa, a.id as absensi_id, a.tanggal, COALESCE(a.status, "H") as status, a.keterangan');
        $builder->join('absensi a', 'a.siswa_id = s.id AND a.tanggal = ' . $db->escape($tanggal), 'left');
        $builder->where('s.kelas_id', $kelasId);
        
        if ($onlyActive) {
            $builder->where('s.status', 'Aktif');
        }

        $builder->orderBy('s.nama_siswa', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Simpan atau update absensi untuk kumpulan siswa pada tanggal tertentu
     */
    public function saveOrUpdateRecords(string $tanggal, array $attendances): bool
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        foreach ($attendances as $siswaId => $data) {
            $status = in_array($data['status'] ?? 'H', ['H', 'S', 'I', 'A']) ? $data['status'] : 'H';
            $keterangan = !empty($data['keterangan']) ? trim($data['keterangan']) : null;

            // Cek apakah data sudah ada
            $existing = $this->where('tanggal', $tanggal)->where('siswa_id', $siswaId)->first();

            if ($existing) {
                $this->update($existing['id'], [
                    'status'     => $status,
                    'keterangan' => $keterangan,
                    'updated_at' => $now,
                ]);
            } else {
                $this->insert([
                    'tanggal'    => $tanggal,
                    'siswa_id'   => $siswaId,
                    'status'     => $status,
                    'keterangan' => $keterangan,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Menghitung rekapitulasi kehadiran (H, S, I, A) per siswa dalam satu kelas
     */
    public function getRekapKelas(int $kelasId, ?string $bulan = null, ?string $tahun = null): array
    {
        $db = $this->db;
        $builder = $db->table('siswa s');
        $builder->select('
            s.id as siswa_id,
            s.nisn,
            s.nama_siswa,
            s.status as status_siswa,
            COUNT(CASE WHEN a.status = "H" THEN 1 END) as total_h,
            COUNT(CASE WHEN a.status = "S" THEN 1 END) as total_s,
            COUNT(CASE WHEN a.status = "I" THEN 1 END) as total_i,
            COUNT(CASE WHEN a.status = "A" THEN 1 END) as total_a
        ');
        
        $joinCondition = 'a.siswa_id = s.id';
        if ($bulan && $tahun) {
            $joinCondition .= ' AND MONTH(a.tanggal) = ' . (int)$bulan . ' AND YEAR(a.tanggal) = ' . (int)$tahun;
        } elseif ($tahun) {
            $joinCondition .= ' AND YEAR(a.tanggal) = ' . (int)$tahun;
        }

        $builder->join('absensi a', $joinCondition, 'left');
        $builder->where('s.kelas_id', $kelasId);
        $builder->groupBy('s.id, s.nisn, s.nama_siswa, s.status');
        $builder->orderBy('s.nama_siswa', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil daftar siswa yang jumlah Alpa (A) melebihi batas (misal > 3)
     */
    public function getSiswaPerluPembinaan(int $kelasId, int $minAlpa = 3): array
    {
        $db = $this->db;
        $builder = $db->table('siswa s');
        $builder->select('
            s.id as siswa_id,
            s.nisn,
            s.nama_siswa,
            k.nama_kelas,
            COUNT(a.id) as total_alpa
        ');
        $builder->join('absensi a', 'a.siswa_id = s.id AND a.status = "A"', 'inner');
        $builder->join('kelas k', 'k.id = s.kelas_id', 'inner');
        $builder->where('s.kelas_id', $kelasId);
        $builder->groupBy('s.id, s.nisn, s.nama_siswa, k.nama_kelas');
        $builder->having('COUNT(a.id) >', $minAlpa);
        $builder->orderBy('total_alpa', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil daftar anak yang paling banyak alpa (tanpa keterangan) di sekolah atau per kelas
     */
    public function getTopSiswaAlpa(int $limit = 10, ?int $kelasId = null): array
    {
        $db = $this->db;
        $builder = $db->table('siswa s');
        $builder->select('
            s.id as siswa_id,
            s.nisn,
            s.nama_siswa,
            k.id as kelas_id,
            k.nama_kelas,
            g.nama_guru as nama_walas,
            COUNT(a.id) as total_alpa
        ');
        $builder->join('absensi a', 'a.siswa_id = s.id AND a.status = "A"', 'inner');
        $builder->join('kelas k', 'k.id = s.kelas_id', 'inner');
        $builder->join('guru g', 'g.id = k.walas_id', 'left');
        
        if ($kelasId !== null) {
            $builder->where('s.kelas_id', $kelasId);
        }

        $builder->groupBy('s.id, s.nisn, s.nama_siswa, k.id, k.nama_kelas, g.nama_guru');
        $builder->having('COUNT(a.id) >', 0);
        $builder->orderBy('total_alpa', 'DESC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }


    /**
     * Mengambil rekapitulasi global sekolah per kelas
     */
    public function getRekapGlobal(): array
    {
        $db = $this->db;
        $builder = $db->table('kelas k');
        $builder->select('
            k.id as kelas_id,
            k.nama_kelas,
            g.nama_guru as nama_walas,
            COUNT(DISTINCT s.id) as total_siswa,
            COUNT(CASE WHEN a.status = "H" THEN 1 END) as total_h,
            COUNT(CASE WHEN a.status = "S" THEN 1 END) as total_s,
            COUNT(CASE WHEN a.status = "I" THEN 1 END) as total_i,
            COUNT(CASE WHEN a.status = "A" THEN 1 END) as total_a
        ');
        $builder->join('guru g', 'g.id = k.walas_id', 'left');
        $builder->join('siswa s', 's.kelas_id = k.id', 'left');
        $builder->join('absensi a', 'a.siswa_id = s.id', 'left');
        $builder->groupBy('k.id, k.nama_kelas, g.nama_guru');
        $builder->orderBy('k.nama_kelas', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil daftar seluruh tanggal ketidakhadiran (S, I, A) untuk satu siswa
     */
    public function getTrackRecordSiswa(int $siswaId): array
    {
        return $this->where('siswa_id', $siswaId)
                    ->whereIn('status', ['S', 'I', 'A'])
                    ->orderBy('tanggal', 'DESC')
                    ->findAll();
    }

    /**
     * Menghitung total Hadir, Sakit, Izin, Alpa untuk satu siswa
     */
    public function getStatistikSiswa(int $siswaId): array
    {
        $db = $this->db;
        $builder = $db->table('absensi');
        $builder->select('
            COUNT(CASE WHEN status = "H" THEN 1 END) as total_h,
            COUNT(CASE WHEN status = "S" THEN 1 END) as total_s,
            COUNT(CASE WHEN status = "I" THEN 1 END) as total_i,
            COUNT(CASE WHEN status = "A" THEN 1 END) as total_a,
            COUNT(id) as total_hari
        ');
        $builder->where('siswa_id', $siswaId);
        
        $row = $builder->get()->getRowArray();
        return $row ?: [
            'total_h'    => 0,
            'total_s'    => 0,
            'total_i'    => 0,
            'total_a'    => 0,
            'total_hari' => 0,
        ];
    }

    /**
     * Sinkronisasi status siswa terlambat dari piket: jadikan Hadir (H) dengan keterangan terlambat
     */
    public function syncSiswaTerlambat(int $siswaId, string $tanggal, string $jamMasuk, int $menitTerlambat = 0): bool
    {
        $existing = $this->where('tanggal', $tanggal)->where('siswa_id', $siswaId)->first();
        $ket = "Terlambat piket ({$jamMasuk}" . ($menitTerlambat > 0 ? ", +{$menitTerlambat} mnt" : "") . ")";

        if ($existing) {
            return (bool)$this->update($existing['id'], [
                'status'     => 'H',
                'keterangan' => $ket,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            return (bool)$this->insert([
                'tanggal'    => $tanggal,
                'siswa_id'   => $siswaId,
                'status'     => 'H',
                'keterangan' => $ket,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Hitung statistik persentase kehadiran siswa dalam rentang tanggal
     */
    public function getStatistikKehadiranSiswaRange(?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->select("
            COUNT(*) as total_sesi,
            SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as total_alfa
        ");

        if ($startDate && $endDate) {
            $builder->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        }

        $row = $builder->first();

        $totalSesi = (int)($row['total_sesi'] ?? 0);
        $totalHadir = (int)($row['total_hadir'] ?? 0);
        $totalSakit = (int)($row['total_sakit'] ?? 0);
        $totalIzin  = (int)($row['total_izin'] ?? 0);
        $totalAlfa  = (int)($row['total_alfa'] ?? 0);

        // Ambil total siswa terlambat dari tabel keterlambatan_siswa
        $db = \Config\Database::connect();
        $builderT = $db->table('keterlambatan_siswa');
        if ($startDate && $endDate) {
            $builderT->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        }
        $totalTerlambat = $builderT->countAllResults();

        $persenHadir = $totalSesi > 0 ? round(($totalHadir / $totalSesi) * 100, 1) : 0;
        $persenSakit = $totalSesi > 0 ? round(($totalSakit / $totalSesi) * 100, 1) : 0;
        $persenIzin  = $totalSesi > 0 ? round(($totalIzin / $totalSesi) * 100, 1) : 0;
        $persenAlfa  = $totalSesi > 0 ? round(($totalAlfa / $totalSesi) * 100, 1) : 0;

        return [
            'total_sesi'      => $totalSesi,
            'total_hadir'     => $totalHadir,
            'total_sakit'     => $totalSakit,
            'total_izin'      => $totalIzin,
            'total_alfa'      => $totalAlfa,
            'total_terlambat' => $totalTerlambat,
            'persen_hadir'    => $persenHadir,
            'persen_sakit'    => $persenSakit,
            'persen_izin'     => $persenIzin,
            'persen_alfa'     => $persenAlfa,
        ];
    }

    /**
     * Hitung performa kehadiran siswa per kelas dalam rentang tanggal
     */
    public function getPerformaPerKelas(?string $startDate = null, ?string $endDate = null): array
    {
        $db = \Config\Database::connect();
        $dateFilter = '';
        if ($startDate && $endDate) {
            $dateFilter = "AND a.tanggal BETWEEN " . $db->escape($startDate) . " AND " . $db->escape($endDate);
        }

        $sql = "
            SELECT 
                k.id,
                k.nama_kelas,
                k.shift,
                COUNT(DISTINCT s.id) as total_siswa,
                COUNT(a.id) as total_sesi,
                SUM(CASE WHEN a.status = 'H' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as total_sakit,
                SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as total_izin,
                SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as total_alfa
            FROM kelas k
            LEFT JOIN siswa s ON s.kelas_id = k.id
            LEFT JOIN absensi a ON a.siswa_id = s.id {$dateFilter}
            GROUP BY k.id, k.nama_kelas, k.shift
            ORDER BY k.nama_kelas ASC
        ";

        $rows = $db->query($sql)->getResultArray();
        foreach ($rows as &$r) {
            $total = (int)$r['total_sesi'];
            $hadir = (int)$r['total_hadir'];
            $r['persen_hadir'] = $total > 0 ? round(($hadir / $total) * 100, 1) : 100;
        }

        return $rows;
    }
}

