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
            $status = in_array($data['status'] ?? 'H', ['H', 'S', 'I', 'A', 'P']) ? $data['status'] : 'H';
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
     * Helper untuk memetakan filter periode (all, hari, minggu, bulan, semester, tahun) ke startDate, endDate, dan label
     */
    public function resolveDateFilter(?string $periode = null, $bulan = null, $tahun = null, ?string $semester = null, ?string $tanggal = null, ?string $startDateCustom = null, ?string $endDateCustom = null): array
    {
        $startDate = null;
        $endDate   = null;
        $tahun     = (int)($tahun ?: date('Y'));
        $tanggal   = !empty($tanggal) ? $tanggal : date('Y-m-d');
        $label     = 'Semua Waktu (Keseluruhan)';

        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        if ($periode === 'rentang' || $periode === 'custom' || (!empty($startDateCustom) && !empty($endDateCustom) && $periode !== 'bulan' && $periode !== 'semester' && $periode !== 'tahun' && $periode !== 'all' && $periode !== 'hari' && $periode !== 'minggu')) {
            $startDate = !empty($startDateCustom) ? $startDateCustom : date('Y-m-01');
            $endDate   = !empty($endDateCustom) ? $endDateCustom : date('Y-m-d');

            if (strtotime($startDate) > strtotime($endDate)) {
                $temp = $startDate;
                $startDate = $endDate;
                $endDate = $temp;
            }

            $sTs = strtotime($startDate);
            $eTs = strtotime($endDate);
            $sFormatted = date('j', $sTs) . ' ' . ($namaBulan[(int)date('n', $sTs)] ?? '') . ' ' . date('Y', $sTs);
            $eFormatted = date('j', $eTs) . ' ' . ($namaBulan[(int)date('n', $eTs)] ?? '') . ' ' . date('Y', $eTs);

            $label = "Rentang: {$sFormatted} s/d {$eFormatted}";
        } elseif ($periode === 'hari') {
            $startDate = $tanggal;
            $endDate   = $tanggal;
            $tTime     = strtotime($tanggal);
            $hariList  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $hariNama  = $hariList[(int)date('w', $tTime)];
            $blnNama   = $namaBulan[(int)date('n', $tTime)];
            $label     = "Hari {$hariNama}, " . date('j', $tTime) . " {$blnNama} " . date('Y', $tTime);
        } elseif ($periode === 'minggu') {
            $ts        = strtotime($tanggal);
            $dayOfWeek = (int)date('N', $ts); // 1 = Senin, 7 = Minggu
            $startDate = date('Y-m-d', strtotime('-' . ($dayOfWeek - 1) . ' days', $ts));
            $endDate   = date('Y-m-d', strtotime('+' . (7 - $dayOfWeek) . ' days', $ts));
            
            $startTs = strtotime($startDate);
            $endTs   = strtotime($endDate);
            $label   = "Mingguan (" . date('d', $startTs) . " " . ($namaBulan[(int)date('n', $startTs)]) . " - " . date('d', $endTs) . " " . ($namaBulan[(int)date('n', $endTs)]) . " " . date('Y', $endTs) . ")";
        } elseif ($periode === 'bulan' || (!empty($bulan) && $periode !== 'semester' && $periode !== 'tahun' && $periode !== 'all' && $periode !== 'hari' && $periode !== 'minggu' && $periode !== 'rentang' && $periode !== 'custom')) {
            $bulan     = (int)$bulan;
            $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
            $endDate   = date('Y-m-t', strtotime($startDate));
            $label     = "Bulan " . ($namaBulan[$bulan] ?? '') . " " . $tahun;
        } elseif ($periode === 'semester' || (!empty($semester) && $periode !== 'tahun' && $periode !== 'all' && $periode !== 'rentang' && $periode !== 'custom')) {
            $sem = strtolower((string)$semester) === 'genap' ? 'genap' : 'ganjil';
            if ($sem === 'genap') {
                $startDate = sprintf('%04d-01-01', $tahun);
                $endDate   = sprintf('%04d-06-30', $tahun);
                $label     = "Semester Genap (Jan - Jun) {$tahun}";
            } else {
                $startDate = sprintf('%04d-07-01', $tahun);
                $endDate   = sprintf('%04d-12-31', $tahun);
                $label     = "Semester Ganjil (Juli - Des) {$tahun}";
            }
        } elseif ($periode === 'tahun') {
            $startDate = sprintf('%04d-01-01', $tahun);
            $endDate   = sprintf('%04d-12-31', $tahun);
            $label     = "Tahun {$tahun}";
        } elseif ($periode === 'all') {
            $label = 'Semua Waktu (Keseluruhan)';
        }

        return [$startDate, $endDate, $label];
    }

    /**
     * Menghitung rekapitulasi kehadiran (H, S, I, A) per siswa dalam satu kelas
     * Mendukung format rentang tanggal (YYYY-MM-DD) atau format bulan & tahun
     */
    public function getRekapKelas(int $kelasId, ?string $startDateOrBulan = null, ?string $endDateOrTahun = null): array
    {
        (new \App\Models\SiswaModel())->ensureTableColumns();
        $db = $this->db;

        $terlambatSubquery = '(SELECT COUNT(ks.id) FROM keterlambatan_siswa ks WHERE ks.siswa_id = s.id';
        if ($startDateOrBulan && $endDateOrTahun) {
            if (strpos($startDateOrBulan, '-') !== false && strpos($endDateOrTahun, '-') !== false) {
                $terlambatSubquery .= ' AND ks.tanggal >= ' . $db->escape($startDateOrBulan) . ' AND ks.tanggal <= ' . $db->escape($endDateOrTahun);
            } else {
                $terlambatSubquery .= ' AND MONTH(ks.tanggal) = ' . (int)$startDateOrBulan . ' AND YEAR(ks.tanggal) = ' . (int)$endDateOrTahun;
            }
        } elseif ($endDateOrTahun) {
            $terlambatSubquery .= ' AND YEAR(ks.tanggal) = ' . (int)$endDateOrTahun;
        }
        $terlambatSubquery .= ') as total_t';

        $builder = $db->table('siswa s');
        $builder->select('
            s.id as siswa_id,
            s.nisn,
            s.nama_siswa,
            COALESCE(s.jenis_kelamin, "L") as jenis_kelamin,
            s.status as status_siswa,
            COUNT(CASE WHEN a.status = "H" THEN 1 END) as total_h,
            COUNT(CASE WHEN a.status = "S" THEN 1 END) as total_s,
            COUNT(CASE WHEN a.status = "I" THEN 1 END) as total_i,
            COUNT(CASE WHEN a.status = "A" THEN 1 END) as total_a,
            COUNT(CASE WHEN a.status = "P" THEN 1 END) as total_p,
            ' . $terlambatSubquery . ',
            MAX(a.keterangan) as keterangan
        ');
        
        $joinCondition = 'a.siswa_id = s.id';
        if ($startDateOrBulan && $endDateOrTahun) {
            if (strpos($startDateOrBulan, '-') !== false && strpos($endDateOrTahun, '-') !== false) {
                // Range tanggal spesifik (misal dari semester atau tanggal kustom)
                $joinCondition .= ' AND a.tanggal >= ' . $db->escape($startDateOrBulan) . ' AND a.tanggal <= ' . $db->escape($endDateOrTahun);
            } else {
                // Bulan dan tahun konvensional
                $joinCondition .= ' AND MONTH(a.tanggal) = ' . (int)$startDateOrBulan . ' AND YEAR(a.tanggal) = ' . (int)$endDateOrTahun;
            }
        } elseif ($endDateOrTahun) {
            $joinCondition .= ' AND YEAR(a.tanggal) = ' . (int)$endDateOrTahun;
        }

        $builder->join('absensi a', $joinCondition, 'left');
        $builder->where('s.kelas_id', $kelasId);
        $builder->groupBy('s.id, s.nisn, s.nama_siswa, s.jenis_kelamin, s.status');
        $builder->orderBy('s.nama_siswa', 'ASC');

        $result = $builder->get()->getResultArray();
        foreach ($result as &$row) {
            $h = (int)($row['total_h'] ?? 0);
            $s = (int)($row['total_s'] ?? 0);
            $i = (int)($row['total_i'] ?? 0);
            $a = (int)($row['total_a'] ?? 0);
            $tot = $h + $s + $i + $a;
            $row['persen_kehadiran'] = $tot > 0 ? round(($h / $tot) * 100, 1) : 0;
        }
        unset($row);

        return $result;
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
     * Mengambil rekapitulasi global sekolah per kelas dengan filter rentang tanggal (Bulan/Semester)
     */
    public function getRekapGlobal(?string $startDate = null, ?string $endDate = null): array
    {
        $db = $this->db;
        $builder = $db->table('kelas k');
        $builder->select('
            k.id as kelas_id,
            k.nama_kelas,
            k.shift,
            k.walas_id,
            g.nama_guru as nama_walas,
            g.no_hp as no_hp_walas,
            COUNT(DISTINCT s.id) as total_siswa,
            COUNT(CASE WHEN a.status = "H" THEN 1 END) as total_h,
            COUNT(CASE WHEN a.status = "S" THEN 1 END) as total_s,
            COUNT(CASE WHEN a.status = "I" THEN 1 END) as total_i,
            COUNT(CASE WHEN a.status = "A" THEN 1 END) as total_a,
            COUNT(CASE WHEN a.status = "P" THEN 1 END) as total_p
        ');
        $builder->join('guru g', 'g.id = k.walas_id', 'left');
        $builder->join('siswa s', 's.kelas_id = k.id', 'left');

        $joinCondition = 'a.siswa_id = s.id';
        if ($startDate && $endDate) {
            $joinCondition .= ' AND a.tanggal >= ' . $db->escape($startDate) . ' AND a.tanggal <= ' . $db->escape($endDate);
        }
        $builder->join('absensi a', $joinCondition, 'left');
        $builder->groupBy('k.id, k.nama_kelas, k.shift, k.walas_id, g.nama_guru, g.no_hp');
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
            SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as total_alfa,
            SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as total_pkl
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
        $db = $this->db;
        $builderT = $db->table('keterlambatan_siswa');
        if ($startDate && $endDate) {
            $builderT->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        }
        $totalTerlambat = $builderT->countAllResults();

        $totalPkl   = (int)($row['total_pkl'] ?? 0);
        $totalHadirSah = $totalHadir + $totalPkl;
        $persenHadir = $totalSesi > 0 ? round(($totalHadirSah / $totalSesi) * 100, 1) : 0;
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
        $db = $this->db;
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

    /**
     * Mengambil status pengisian presensi seluruh kelas pada tanggal tertentu (default hari ini).
     * Mengetahui kelas mana saja yang sudah lengkap, sebagian, atau belum input absensi sama sekali.
     */
    public function getStatusInputPresensiKelas(?string $tanggal = null): array
    {
        $db = $this->db;
        $tanggal = $tanggal ?: date('Y-m-d');

        $sql = "
            SELECT 
                k.id as kelas_id,
                k.nama_kelas,
                k.shift,
                k.walas_id,
                g.nama_guru as nama_walas,
                g.no_hp as no_hp_walas,
                COUNT(DISTINCT CASE WHEN s.status = 'Aktif' THEN s.id END) as total_siswa_aktif,
                COUNT(DISTINCT a.siswa_id) as total_diabsen,
                COUNT(CASE WHEN a.status = 'H' THEN 1 END) as total_h,
                COUNT(CASE WHEN a.status = 'S' THEN 1 END) as total_s,
                COUNT(CASE WHEN a.status = 'I' THEN 1 END) as total_i,
                COUNT(CASE WHEN a.status = 'A' THEN 1 END) as total_a,
                COUNT(CASE WHEN a.status = 'P' THEN 1 END) as total_p,
                MAX(a.updated_at) as waktu_update,
                pkh.foto_kelas,
                pkh.share_token
            FROM kelas k
            LEFT JOIN guru g ON g.id = k.walas_id
            LEFT JOIN siswa s ON s.kelas_id = k.id AND s.status = 'Aktif'
            LEFT JOIN absensi a ON a.siswa_id = s.id AND a.tanggal = " . $db->escape($tanggal) . "
            LEFT JOIN presensi_kelas_harian pkh ON pkh.kelas_id = k.id AND pkh.tanggal = " . $db->escape($tanggal) . "
            GROUP BY k.id, k.nama_kelas, k.shift, k.walas_id, g.nama_guru, g.no_hp, pkh.foto_kelas, pkh.share_token
            ORDER BY k.nama_kelas ASC
        ";

        $rows = $db->query($sql)->getResultArray();

        // Ambil agenda kalender pendidikan (libur / bebas absen) pada tanggal ini
        $kalenderModel = new \App\Models\KalenderPendidikanModel();
        $agendaLibur = $kalenderModel->getHariBebasAbsenMap($tanggal, $tanggal);

        $result = [];
        foreach ($rows as $r) {
            $totalSiswa   = (int)$r['total_siswa_aktif'];
            $totalDiabsen = (int)$r['total_diabsen'];
            $tingkat      = $this->getTingkatKelas($r['nama_kelas']);

            // Cek apakah rombel kelas ini masuk dalam cakupan agenda bebas absen
            $agendaBebas = null;
            foreach ($agendaLibur as $agenda) {
                $targetTingkat = $agenda['target_tingkat'] ?? 'semua';
                $targetKelasId = !empty($agenda['target_kelas_id']) ? (int)$agenda['target_kelas_id'] : null;

                if ($targetTingkat === 'semua' || $targetTingkat === $tingkat || $targetKelasId === (int)$r['kelas_id']) {
                    $agendaBebas = $agenda;
                    break;
                }
            }

            $isBebasAbsen = ($agendaBebas !== null);

            if ($totalDiabsen === 0) {
                if ($isBebasAbsen) {
                    $statusInput = 'bebas_absen';
                    $statusLabel = 'Bebas Absen';
                    $statusBadge = 'info';
                } else {
                    $statusInput = 'belum';
                    $statusLabel = 'Belum Input';
                    $statusBadge = 'danger';
                }
            } elseif ($totalDiabsen < $totalSiswa) {
                $statusInput = 'sebagian';
                $statusLabel = 'Sebagian (' . $totalDiabsen . '/' . $totalSiswa . ')';
                $statusBadge = 'warning';
            } else {
                $statusInput = 'lengkap';
                $statusLabel = 'Sudah Lengkap';
                $statusBadge = 'success';
            }

            $r['total_siswa_aktif']  = $totalSiswa;
            $r['total_diabsen']      = $totalDiabsen;
            $r['status_input']       = $statusInput;
            $r['status_label']       = $statusLabel;
            $r['status_badge']       = $statusBadge;
            $r['is_bebas_absen']     = $isBebasAbsen;
            $r['agenda_bebas_absen'] = $agendaBebas['nama_agenda'] ?? null;
            $r['agenda_tipe']        = $agendaBebas['tipe'] ?? null;

            $result[] = $r;
        }

        return $result;
    }

    /**
     * Deteksi tingkat kelas dari nama kelas (X, XI, XII)
     */
    public function getTingkatKelas(string $namaKelas): string
    {
        if (preg_match('/(^|\b)(XII|12)\b/i', $namaKelas)) {
            return 'XII';
        }
        if (preg_match('/(^|\b)(XI|11)\b/i', $namaKelas)) {
            return 'XI';
        }
        if (preg_match('/(^|\b)(X|10)\b/i', $namaKelas)) {
            return 'X';
        }
        return 'semua';
    }

    /**
     * Cek apakah kelas merupakan Kelas 2 (Tingkat XI / 11) untuk penentuan opsi PKL (P)
     */
    public function isKelas2(string $namaKelas): bool
    {
        return $this->getTingkatKelas($namaKelas) === 'XI';
    }

    /**
     * Mengambil daftar tanggal aktif belajar (Senin-Sabtu) yang kelewat / NULL / belum pernah diinput
     * dengan mengecek apakah tanggal tersebut adalah hari libur / acara bebas absen di Kalender Pendidikan.
     */
    public function getTanggalKelewatKelas(int $kelasId, ?string $startDate = null, ?string $endDate = null): array
    {
        $db = $this->db;
        $endDate   = $endDate ?: date('Y-m-d');
        // Default mulai tanggal tahun ajaran baru: 21 Juli 2026
        $startDate = $startDate ?: '2026-07-21';

        $kelas = $db->table('kelas')->where('id', $kelasId)->get()->getRowArray();
        if (!$kelas) {
            return [];
        }

        $tingkat = $this->getTingkatKelas($kelas['nama_kelas']);

        // Ambil data kalender pendidikan
        $kalenderModel = new \App\Models\KalenderPendidikanModel();
        $agendaLibur = $kalenderModel->getHariBebasAbsenMap($startDate, $endDate);

        // Ambil tanggal-tanggal yang SUDAH diinput oleh kelas ini
        $sqlDates = "
            SELECT DISTINCT a.tanggal 
            FROM absensi a 
            JOIN siswa s ON s.id = a.siswa_id 
            WHERE s.kelas_id = " . (int)$kelasId . " 
              AND a.tanggal >= " . $db->escape($startDate) . " 
              AND a.tanggal <= " . $db->escape($endDate) . "
        ";
        $datesDiinput = $db->query($sqlDates)->getResultArray();
        $diinputMap = array_column($datesDiinput, 'tanggal');

        $daftarHariIndo = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $daftarBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $tanggalKelewat = [];
        $current = strtotime($startDate);
        $last    = strtotime($endDate);

        while ($current <= $last) {
            $tglStr = date('Y-m-d', $current);
            $dayOfWeek = (int)date('w', $current); // 0 = Minggu

            // 1. Abaikan hari Minggu (0) -> Hanya Senin sampai Sabtu
            if ($dayOfWeek !== 0) {
                // 2. Cek apakah tanggal ini adalah Libur / Acara Bebas Absen di Kalender Pendidikan
                $isLibur = false;
                foreach ($agendaLibur as $agenda) {
                    if ($tglStr >= $agenda['tanggal_mulai'] && $tglStr <= $agenda['tanggal_selesai']) {
                        $targetTingkat = $agenda['target_tingkat'] ?? 'semua';
                        $targetKelasId = !empty($agenda['target_kelas_id']) ? (int)$agenda['target_kelas_id'] : null;

                        if ($targetTingkat === 'semua' || $targetTingkat === $tingkat || $targetKelasId === $kelasId) {
                            $isLibur = true;
                            break;
                        }
                    }
                }

                // 3. Jika bukan libur dan belum pernah diinput absensinya
                if (!$isLibur && !in_array($tglStr, $diinputMap, true)) {
                    $dayNum = (int)date('j', $current);
                    $monthNum = (int)date('n', $current);
                    $yearNum = date('Y', $current);

                    $tanggalKelewat[] = [
                        'tanggal'   => $tglStr,
                        'hari'      => $daftarHariIndo[$dayOfWeek],
                        'formatted' => $daftarHariIndo[$dayOfWeek] . ', ' . $dayNum . ' ' . ($daftarBulanIndo[$monthNum] ?? '') . ' ' . $yearNum,
                        'is_today'  => ($tglStr === date('Y-m-d')),
                    ];
                }
            }

            $current = strtotime('+1 day', $current);
        }

        // Urutkan dari tanggal terbaru ke terlama
        usort($tanggalKelewat, function ($a, $b) {
            return strcmp($b['tanggal'], $a['tanggal']);
        });

        return $tanggalKelewat;
    }

    /**
     * Mengambil daftar seluruh kelas yang memiliki tanggal aktif belajar belum diinput (untuk Admin)
     */
    public function getAllKelasTanggalKelewat(?string $startDate = null, ?string $endDate = null): array
    {
        $db = $this->db;
        $kelasList = $db->table('kelas k')
                        ->select('k.id, k.nama_kelas, k.shift, g.nama_guru as nama_walas, g.no_hp as no_hp_walas')
                        ->join('guru g', 'g.id = k.walas_id', 'left')
                        ->orderBy('k.nama_kelas', 'ASC')
                        ->get()
                        ->getResultArray();

        $result = [];
        foreach ($kelasList as $k) {
            $kelewat = $this->getTanggalKelewatKelas((int)$k['id'], $startDate, $endDate);
            if (!empty($kelewat)) {
                $k['daftar_kelewat'] = $kelewat;
                $k['total_kelewat']  = count($kelewat);
                $result[] = $k;
            }
        }

        return $result;
    }

    /**
     * Menghapus seluruh data presensi suatu kelas pada tanggal tertentu (Set Null / Reset).
     * Juga membersihkan sesi dan berkas foto dokumentasi kelas harian jika ada.
     */
    public function deletePresensiByDateAndKelas(string $tanggal, int $kelasId): int
    {
        $db = $this->db;

        // Ambil ID seluruh siswa di kelas ini
        $siswaIds = $db->table('siswa')
            ->select('id')
            ->where('kelas_id', $kelasId)
            ->get()
            ->getResultArray();

        $deletedCount = 0;
        if (!empty($siswaIds)) {
            $ids = array_column($siswaIds, 'id');
            $db->table('absensi')
                ->where('tanggal', $tanggal)
                ->whereIn('siswa_id', $ids)
                ->delete();
            $deletedCount = $db->affectedRows();
        }

        // Hapus juga catatan & foto di presensi_kelas_harian
        $pkh = $db->table('presensi_kelas_harian')
            ->where('kelas_id', $kelasId)
            ->where('tanggal', $tanggal)
            ->get()
            ->getRowArray();

        if ($pkh) {
            if (!empty($pkh['foto_kelas'])) {
                $filePath = FCPATH . ltrim($pkh['foto_kelas'], '/\\');
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            $db->table('presensi_kelas_harian')
                ->where('id', $pkh['id'])
                ->delete();
        }

        return $deletedCount;
    }

    /**
     * Menghapus presensi pada rentang tanggal untuk target tingkat tertentu atau seluruh sekolah.
     * Digunakan saat admin atau walas menetapkan hari libur / acara bebas KBM.
     */
    public function deletePresensiByDateRangeAndTarget(string $startDate, string $endDate, string $targetTingkat = 'semua', ?int $targetKelasId = null): int
    {
        $db = $this->db;

        $siswaBuilder = $db->table('siswa s')
                           ->select('s.id, s.kelas_id')
                           ->join('kelas k', 'k.id = s.kelas_id');

        if (!empty($targetKelasId)) {
            $siswaBuilder->where('s.kelas_id', $targetKelasId);
        } elseif (!empty($targetTingkat) && $targetTingkat !== 'semua') {
            if ($targetTingkat === 'XII') {
                $siswaBuilder->groupStart()
                             ->like('k.nama_kelas', 'XII', 'after')
                             ->orLike('k.nama_kelas', '12', 'after')
                             ->groupEnd();
            } elseif ($targetTingkat === 'XI') {
                $siswaBuilder->groupStart()
                             ->like('k.nama_kelas', 'XI', 'after')
                             ->orLike('k.nama_kelas', '11', 'after')
                             ->groupEnd();
            } elseif ($targetTingkat === 'X') {
                $siswaBuilder->groupStart()
                             ->like('k.nama_kelas', 'X', 'after')
                             ->orLike('k.nama_kelas', '10', 'after')
                             ->groupEnd();
            }
        }

        $siswaList = $siswaBuilder->get()->getResultArray();
        if (empty($siswaList)) {
            return 0;
        }

        $siswaIds = array_column($siswaList, 'id');
        $kelasIds = array_values(array_unique(array_column($siswaList, 'kelas_id')));

        // Hapus absensi
        $db->table('absensi')
           ->where('tanggal >=', $startDate)
           ->where('tanggal <=', $endDate)
           ->whereIn('siswa_id', $siswaIds)
           ->delete();
        $deletedCount = $db->affectedRows();

        // Hapus foto dokumentasi dan catatan presensi_kelas_harian pada rentang tanggal
        if (!empty($kelasIds)) {
            $pkhList = $db->table('presensi_kelas_harian')
                          ->where('tanggal >=', $startDate)
                          ->where('tanggal <=', $endDate)
                          ->whereIn('kelas_id', $kelasIds)
                          ->get()
                          ->getResultArray();

            foreach ($pkhList as $pkh) {
                if (!empty($pkh['foto_kelas'])) {
                    $filePath = FCPATH . ltrim($pkh['foto_kelas'], '/\\');
                    if (is_file($filePath)) {
                        @unlink($filePath);
                    }
                }
            }

            $db->table('presensi_kelas_harian')
               ->where('tanggal >=', $startDate)
               ->where('tanggal <=', $endDate)
               ->whereIn('kelas_id', $kelasIds)
               ->delete();
        }

        return $deletedCount;
    }
}

