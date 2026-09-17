<?php

namespace App\Models;

use CodeIgniter\Model;

class PembinaanModel extends Model
{
    protected $table            = 'pembinaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tanggal_tindakan',
        'siswa_id',
        'walas_id',
        'jenis_tindakan',
        'catatan_pembinaan',
        'file_bukti',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil riwayat pembinaan per wali kelas atau per kelas
     */
    public function getRiwayatPembinaan(?int $walasId = null, ?int $kelasId = null): array
    {
        $builder = $this->select('
            pembinaan.*,
            s.nama_siswa,
            s.nisn,
            k.nama_kelas,
            g.nama_guru as nama_walas
        ')
        ->join('siswa s', 's.id = pembinaan.siswa_id', 'inner')
        ->join('kelas k', 'k.id = s.kelas_id', 'inner')
        ->join('guru g', 'g.id = pembinaan.walas_id', 'left');

        if ($walasId !== null) {
            $builder->where('pembinaan.walas_id', $walasId);
        }

        if ($kelasId !== null) {
            $builder->where('s.kelas_id', $kelasId);
        }

        return $builder->orderBy('pembinaan.tanggal_tindakan', 'DESC')->findAll();
    }

    /**
     * Mengambil riwayat pembinaan dengan filter komprehensif (Kelas, Jenis Tindakan, Keyword)
     */
    public function getFilteredPembinaan(?int $kelasId = null, ?string $jenisTindakan = null, ?string $keyword = null): array
    {
        $builder = $this->select('
            pembinaan.*,
            s.nama_siswa,
            s.nisn,
            k.id as kelas_id,
            k.nama_kelas,
            g.nama_guru as nama_walas
        ')
        ->join('siswa s', 's.id = pembinaan.siswa_id', 'inner')
        ->join('kelas k', 'k.id = s.kelas_id', 'inner')
        ->join('guru g', 'g.id = pembinaan.walas_id', 'left');

        if (!empty($kelasId)) {
            $builder->where('s.kelas_id', $kelasId);
        }

        if (!empty($jenisTindakan)) {
            $builder->where('pembinaan.jenis_tindakan', $jenisTindakan);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                    ->like('s.nama_siswa', $keyword)
                    ->orLike('s.nisn', $keyword)
                    ->orLike('pembinaan.catatan_pembinaan', $keyword)
                    ->groupEnd();
        }

        return $builder->orderBy('pembinaan.tanggal_tindakan', 'DESC')->findAll();
    }
}
