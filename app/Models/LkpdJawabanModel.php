<?php

namespace App\Models;

use CodeIgniter\Model;

class LkpdJawabanModel extends Model
{
    protected $table            = 'lkpd_jawaban';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kelompok_id',
        'lkpd_id',
        'ringkasan_kerja',
        'file_lampiran',
        'link_eksternal',
        'skor_kelompok',
        'feedback_guru',
        'status_pengumpulan',
        'waktu_kumpul',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

