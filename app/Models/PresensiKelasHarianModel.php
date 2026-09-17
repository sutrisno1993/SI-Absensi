<?php

namespace App\Models;

use CodeIgniter\Model;

class PresensiKelasHarianModel extends Model
{
    protected $table            = 'presensi_kelas_harian';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kelas_id',
        'tanggal',
        'foto_kelas',
        'share_token',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil atau generate sesi presensi kelas hari ini beserta token publiknya
     */
    public function getOrCreateForToday(int $kelasId, string $tanggal): array
    {
        $existing = $this->where('kelas_id', $kelasId)
                         ->where('tanggal', $tanggal)
                         ->first();

        if ($existing) {
            return $existing;
        }

        // Generate token acak yang aman dan ramah URL (32 karakter hex)
        $token = bin2hex(random_bytes(16));

        $id = $this->insert([
            'kelas_id'    => $kelasId,
            'tanggal'     => $tanggal,
            'share_token' => $token,
            'foto_kelas'  => null,
            'catatan'     => null,
        ]);

        return $this->find($id);
    }

    /**
     * Ambil data sesi lengkap beserta informasi kelas dan wali kelas berdasarkan token publik
     */
    public function getByToken(string $token): ?array
    {
        return $this->select('presensi_kelas_harian.*, kelas.nama_kelas, kelas.shift, guru.nama_guru as nama_walas, guru.nip as nip_walas, guru.no_hp as hp_walas')
                    ->join('kelas', 'kelas.id = presensi_kelas_harian.kelas_id', 'inner')
                    ->join('guru', 'guru.id = kelas.walas_id', 'left')
                    ->where('presensi_kelas_harian.share_token', $token)
                    ->first();
    }
}
