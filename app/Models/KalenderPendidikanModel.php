<?php

namespace App\Models;

use CodeIgniter\Model;

class KalenderPendidikanModel extends Model
{
    protected $table            = 'kalender_pendidikan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_agenda',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe',
        'keterangan_kbm',
        'target_tingkat',
        'target_kelas_id',
        'keterangan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil seluruh agenda dengan urutan tanggal terbaru
     */
    public function getAllAgenda(): array
    {
        return $this->select('kalender_pendidikan.*, kelas.nama_kelas')
                    ->join('kelas', 'kelas.id = kalender_pendidikan.target_kelas_id', 'left')
                    ->orderBy('tanggal_mulai', 'DESC')
                    ->findAll();
    }

    /**
     * Cek apakah suatu tanggal adalah hari bebas presensi (Libur resmi atau Acara sekolah bebas absen)
     * yang berlaku untuk tingkat kelas tertentu (X, XI, XII) atau kelas tertentu
     */
    public function isHariBebasAbsen(string $tanggal, ?string $tingkat = null, ?int $kelasId = null): ?array
    {
        $builder = $this->where('tanggal_mulai <=', $tanggal)
                        ->where('tanggal_selesai >=', $tanggal)
                        ->groupStart()
                            ->where('tipe', 'libur')
                            ->orWhere('keterangan_kbm', 'bebas_absen')
                        ->groupEnd();

        // Filter target peserta: berlaku jika 'semua', atau cocok dengan tingkat / kelasId
        $builder->groupStart()
            ->where('target_tingkat', 'semua');
        
        if (!empty($tingkat)) {
            $builder->orWhere('target_tingkat', $tingkat);
        }

        if (!empty($kelasId)) {
            $builder->orWhere('target_kelas_id', $kelasId);
        }

        $builder->groupEnd();

        return $builder->first();
    }

    /**
     * Ambil daftar semua hari bebas absen dalam suatu rentang tanggal
     */
    public function getHariBebasAbsenMap(string $startDate, string $endDate): array
    {
        return $this->where('tanggal_mulai <=', $endDate)
                    ->where('tanggal_selesai >=', $startDate)
                    ->groupStart()
                        ->where('tipe', 'libur')
                        ->orWhere('keterangan_kbm', 'bebas_absen')
                    ->groupEnd()
                    ->findAll();
    }

    /**
     * Ambil agenda kalender yang relevan untuk date picker suatu kelas/tingkat
     */
    public function getEventsForPicker(?string $tingkat = null, ?int $kelasId = null): array
    {
        $builder = $this->builder();
        $builder->select('id, nama_agenda, tanggal_mulai, tanggal_selesai, tipe, keterangan_kbm, target_tingkat, target_kelas_id');
        $builder->groupStart()
                ->where('target_tingkat', 'semua');

        if (!empty($tingkat)) {
            $builder->orWhere('target_tingkat', $tingkat);
        }
        if (!empty($kelasId)) {
            $builder->orWhere('target_kelas_id', $kelasId);
        }
        $builder->groupEnd();

        return $builder->orderBy('tanggal_mulai', 'ASC')->get()->getResultArray();
    }
}

