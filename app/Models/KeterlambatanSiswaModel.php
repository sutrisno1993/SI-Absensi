<?php

namespace App\Models;

use CodeIgniter\Model;

class KeterlambatanSiswaModel extends Model
{
    protected $table            = 'keterlambatan_siswa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tanggal',
        'siswa_id',
        'shift',
        'jam_masuk',
        'menit_terlambat',
        'alasan',
        'tindakan',
        'dicatat_oleh',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil data siswa terlambat beserta data siswa dan kelas
     */
    public function getTerlambatList(?string $tanggal = null): array
    {
        $builder = $this->select('keterlambatan_siswa.*, siswa.nama_siswa, siswa.nisn, kelas.nama_kelas, users.username as pencatat')
                        ->join('siswa', 'siswa.id = keterlambatan_siswa.siswa_id')
                        ->join('kelas', 'kelas.id = siswa.kelas_id', 'left')
                        ->join('users', 'users.id = keterlambatan_siswa.dicatat_oleh', 'left');

        if ($tanggal) {
            $builder->where('keterlambatan_siswa.tanggal', $tanggal);
        }

        return $builder->orderBy('keterlambatan_siswa.jam_masuk', 'DESC')->findAll();
    }

    /**
     * Catat keterlambatan dan otomatis sinkronkan ke absensi kelas harian
     */
    public function catatKeterlambatan(array $data): int
    {
        $tanggal   = $data['tanggal'] ?? date('Y-m-d');
        $shift     = $data['shift'] ?? 'Pagi';
        $jamMasuk  = $data['jam_masuk'] ?? date('H:i:s');
        $siswaId   = (int)$data['siswa_id'];
        $alasan    = $data['alasan'] ?? null;
        $tindakan  = $data['tindakan'] ?? null;
        $pencatat  = $data['dicatat_oleh'] ?? null;

        // Hitung menit terlambat
        $jamStandar = ($shift === 'Siang') ? '12:30:00' : '07:00:00';
        $standarTimestamp = strtotime("$tanggal $jamStandar");
        $actualTimestamp  = strtotime("$tanggal $jamMasuk");
        $menitTerlambat   = 0;

        if ($actualTimestamp > $standarTimestamp) {
            $menitTerlambat = (int)ceil(($actualTimestamp - $standarTimestamp) / 60);
        }

        // Insert ke log keterlambatan_siswa
        $insertId = $this->insert([
            'tanggal'         => $tanggal,
            'siswa_id'        => $siswaId,
            'shift'           => $shift,
            'jam_masuk'       => $jamMasuk,
            'menit_terlambat' => $menitTerlambat,
            'alasan'          => $alasan,
            'tindakan'        => $tindakan,
            'dicatat_oleh'    => $pencatat,
        ]);

        // SINKRONISASI OTOMATIS: Update status absensi siswa pada hari ini menjadi Hadir (H)
        $absensiModel = new \App\Models\AbsensiModel();
        $absensiModel->syncSiswaTerlambat($siswaId, $tanggal, $jamMasuk, $menitTerlambat);

        return (int)$insertId;
    }

    /**
     * Ambil data keterlambatan dalam rentang periode
     */
    public function getRekapPeriode(?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->select('keterlambatan_siswa.*, siswa.nama_siswa, siswa.nisn, kelas.nama_kelas, users.username as pencatat')
                        ->join('siswa', 'siswa.id = keterlambatan_siswa.siswa_id')
                        ->join('kelas', 'kelas.id = siswa.kelas_id', 'left')
                        ->join('users', 'users.id = keterlambatan_siswa.dicatat_oleh', 'left');

        if ($startDate) {
            $builder->where('keterlambatan_siswa.tanggal >=', $startDate);
        }
        if ($endDate) {
            $builder->where('keterlambatan_siswa.tanggal <=', $endDate);
        }

        return $builder->orderBy('keterlambatan_siswa.tanggal', 'DESC')
                       ->orderBy('keterlambatan_siswa.jam_masuk', 'DESC')
                       ->findAll();
    }

    /**
     * Mengambil rekapitulasi keterlambatan seluruh siswa dalam satu kelas pada rentang tanggal tertentu
     */
    public function getRekapKeterlambatanKelas(int $kelasId, ?string $startDate = null, ?string $endDate = null): array
    {
        $db = $this->db;
        $builder = $db->table('siswa s');
        $builder->select('
            s.id as siswa_id,
            s.nisn,
            s.nama_siswa,
            s.jenis_kelamin,
            s.status as status_siswa,
            COUNT(ks.id) as total_terlambat,
            COALESCE(SUM(ks.menit_terlambat), 0) as total_menit,
            GROUP_CONCAT(CONCAT(ks.tanggal, " (", ks.menit_terlambat, "m)") ORDER BY ks.tanggal DESC SEPARATOR ", ") as riwayat_tgl
        ');

        $joinCondition = 'ks.siswa_id = s.id';
        if ($startDate) {
            $joinCondition .= ' AND ks.tanggal >= ' . $db->escape($startDate);
        }
        if ($endDate) {
            $joinCondition .= ' AND ks.tanggal <= ' . $db->escape($endDate);
        }

        $builder->join('keterlambatan_siswa ks', $joinCondition, 'left');
        $builder->where('s.kelas_id', $kelasId);
        $builder->where('s.status', 'Aktif');
        $builder->groupBy('s.id, s.nisn, s.nama_siswa, s.jenis_kelamin, s.status');
        $builder->orderBy('total_terlambat', 'DESC');
        $builder->orderBy('s.nama_siswa', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil daftar siswa yang frekuensi keterlambatannya >= ambang batas (default: >= 1 atau >= 3)
     */
    public function getSiswaTerlambatBermasalah(int $kelasId, int $minTerlambat = 3, ?string $startDate = null, ?string $endDate = null): array
    {
        $rekap = $this->getRekapKeterlambatanKelas($kelasId, $startDate, $endDate);
        return array_values(array_filter($rekap, static fn($r) => (int)$r['total_terlambat'] >= $minTerlambat));
    }

    /**
     * Mengambil rekapitulasi keterlambatan per rombel/kelas (untuk analitik dan leaderboard Admin Pusat)
     */
    public function getRekapKeterlambatanPerKelas(?string $startDate = null, ?string $endDate = null): array
    {
        $db = $this->db;
        $builder = $db->table('kelas k');
        $builder->select('
            k.id as kelas_id,
            k.nama_kelas,
            k.shift,
            g.nama_guru as nama_walas,
            COUNT(DISTINCT s.id) as total_siswa_kelas,
            COUNT(ks.id) as total_kejadian,
            COUNT(DISTINCT ks.siswa_id) as total_siswa_terlambat,
            COALESCE(SUM(ks.menit_terlambat), 0) as total_menit
        ');
        $builder->join('guru g', 'g.id = k.walas_id', 'left');
        $builder->join('siswa s', 's.kelas_id = k.id AND s.status = "Aktif"', 'left');

        $joinKs = 'ks.siswa_id = s.id';
        if ($startDate) {
            $joinKs .= ' AND ks.tanggal >= ' . $db->escape($startDate);
        }
        if ($endDate) {
            $joinKs .= ' AND ks.tanggal <= ' . $db->escape($endDate);
        }
        $builder->join('keterlambatan_siswa ks', $joinKs, 'left');
        $builder->groupBy('k.id, k.nama_kelas, k.shift, g.nama_guru');
        $builder->orderBy('total_kejadian', 'DESC');
        $builder->orderBy('total_siswa_terlambat', 'DESC');
        $builder->orderBy('k.nama_kelas', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil rekapitulasi keterlambatan siswa lintas rombel (Admin Pusat)
     */
    public function getRekapKeterlambatanGlobal(?string $startDate = null, ?string $endDate = null, ?int $kelasId = null, ?string $statusDisiplin = null, ?string $search = null): array
    {
        $db = $this->db;
        $builder = $db->table('siswa s');
        $builder->select('
            s.id as siswa_id,
            s.nisn,
            s.nama_siswa,
            s.jenis_kelamin,
            s.kelas_id,
            k.nama_kelas,
            COUNT(ks.id) as total_terlambat,
            COALESCE(SUM(ks.menit_terlambat), 0) as total_menit,
            GROUP_CONCAT(CONCAT(ks.tanggal, " (", ks.menit_terlambat, "m)") ORDER BY ks.tanggal DESC SEPARATOR ", ") as riwayat_tgl
        ');
        $builder->join('kelas k', 'k.id = s.kelas_id', 'left');

        $joinCondition = 'ks.siswa_id = s.id';
        if ($startDate) {
            $joinCondition .= ' AND ks.tanggal >= ' . $db->escape($startDate);
        }
        if ($endDate) {
            $joinCondition .= ' AND ks.tanggal <= ' . $db->escape($endDate);
        }
        $builder->join('keterlambatan_siswa ks', $joinCondition, 'left');
        $builder->where('s.status', 'Aktif');

        if ($kelasId) {
            $builder->where('s.kelas_id', $kelasId);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('s.nama_siswa', $search)
                    ->orLike('s.nisn', $search)
                    ->groupEnd();
        }

        $builder->groupBy('s.id, s.nisn, s.nama_siswa, s.jenis_kelamin, s.kelas_id, k.nama_kelas');

        if ($statusDisiplin === 'wajib_bina') {
            $builder->having('total_terlambat >', 3);
        } elseif ($statusDisiplin === 'perhatian') {
            $builder->having('total_terlambat >', 1);
            $builder->having('total_terlambat <=', 3);
        } elseif ($statusDisiplin === 'terlambat') {
            $builder->having('total_terlambat >', 0);
        } elseif ($statusDisiplin === 'tertib') {
            $builder->having('total_terlambat =', 0);
        } else {
            if (!$kelasId && !$search) {
                $builder->having('total_terlambat >', 0);
            }
        }

        $builder->orderBy('total_terlambat', 'DESC');
        $builder->orderBy('total_menit', 'DESC');
        $builder->orderBy('s.nama_siswa', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Ringkasan statistik keterlambatan siswa global
     */
    public function getStatistikKeterlambatanGlobal(?string $startDate = null, ?string $endDate = null, ?int $kelasId = null): array
    {
        $allStudentsLate = $this->getRekapKeterlambatanGlobal($startDate, $endDate, $kelasId, 'terlambat');

        $totalKejadian = 0;
        $totalMenit = 0;
        $siswaPerhatian = 0;
        $siswaWajibBina = 0;
        $kelasTerdampak = [];

        foreach ($allStudentsLate as $row) {
            $tot = (int)$row['total_terlambat'];
            $menit = (int)$row['total_menit'];
            $totalKejadian += $tot;
            $totalMenit += $menit;

            if ($tot > 3) {
                $siswaWajibBina++;
            } elseif ($tot > 1) {
                $siswaPerhatian++;
            }

            if (!empty($row['nama_kelas'])) {
                $kelasTerdampak[$row['nama_kelas']] = true;
            }
        }

        return [
            'total_kejadian'        => $totalKejadian,
            'total_menit'           => $totalMenit,
            'total_siswa_terlambat' => count($allStudentsLate),
            'siswa_perhatian'       => $siswaPerhatian,
            'siswa_wajib_bina'      => $siswaWajibBina,
            'total_kelas_terdampak' => count($kelasTerdampak),
        ];
    }
}

