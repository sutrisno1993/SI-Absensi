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
}
