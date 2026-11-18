<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesmenJawabanSiswaModel extends Model
{
    protected $table            = 'asesmen_jawaban_siswa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'asesmen_id',
        'soal_id',
        'siswa_id',
        'jawaban_pg',
        'jawaban_essay',
        'is_benar',
        'skor',
        'catatan_guru',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Rekap hasil asesmen per siswa
     */
    public function getRekapHasilSiswa(int $asesmenId)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                s.id as siswa_id,
                s.nama_siswa,
                s.nisn,
                COUNT(ajs.id) as total_dijawab,
                SUM(ajs.is_benar) as total_pg_benar,
                SUM(ajs.skor) as total_skor,
                MAX(ajs.updated_at) as waktu_selesai
            FROM asesmen_jawaban_siswa ajs
            JOIN siswa s ON s.id = ajs.siswa_id
            WHERE ajs.asesmen_id = ?
            GROUP BY s.id, s.nama_siswa, s.nisn
            ORDER BY total_skor DESC, s.nama_siswa ASC
        ", [$asesmenId])->getResultArray();
    }
}

