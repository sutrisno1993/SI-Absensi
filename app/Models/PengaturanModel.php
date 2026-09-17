<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanModel extends Model
{
    protected $table            = 'pengaturan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'setting_key',
        'setting_value',
        'keterangan',
        'updated_at',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key
     */
    public function getSetting(string $key, ?string $default = null): ?string
    {
        $row = $this->where('setting_key', $key)->first();
        return $row ? ($row['setting_value'] ?? $default) : $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan
     */
    public function setSetting(string $key, ?string $value, ?string $keterangan = null): bool
    {
        $row = $this->where('setting_key', $key)->first();
        if ($row) {
            $data = [
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
            if ($keterangan !== null) {
                $data['keterangan'] = $keterangan;
            }
            return $this->update($row['id'], $data);
        }

        return (bool)$this->insert([
            'setting_key'   => $key,
            'setting_value' => $value,
            'keterangan'    => $keterangan,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil seluruh pengaturan dalam bentuk associative array [key => value]
     */
    public function getAllSettings(): array
    {
        $rows = $this->findAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }
}
