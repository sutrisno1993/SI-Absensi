<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table            = 'kelas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_kelas',
        'shift',
        'walas_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil kelas beserta nama wali kelas
     */
    public function getKelasWithWalas()
    {
        return $this->select('kelas.*, guru.nama_guru, guru.nip')
                    ->join('guru', 'guru.id = kelas.walas_id', 'left')
                    ->findAll();
    }

    /**
     * Cari kelas berdasarkan ID Guru yang mengampu
     */
    public function getKelasByWalas(int $walasId)
    {
        return $this->where('walas_id', $walasId)->first();
    }
}
