<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMutasiDanGenderToSiswaTable extends Migration
{
    public function up()
    {
        $fields = [
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'default'    => 'L',
                'after'      => 'nama_siswa',
            ],
            'alasan_nonaktif' => [
                'type'       => 'ENUM',
                'constraint' => ['pindah_sekolah', 'mengundurkan_diri', 'dikeluarkan', 'lainnya'],
                'null'       => true,
                'after'      => 'status',
            ],
            'tanggal_nonaktif' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'alasan_nonaktif',
            ],
            'catatan_nonaktif' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'tanggal_nonaktif',
            ],
            'file_surat_nonaktif' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'catatan_nonaktif',
            ],
        ];

        // Cek jika kolom belum ada baru tambahkan
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('siswa');

        $toAdd = [];
        foreach ($fields as $colName => $def) {
            if (! in_array($colName, $existingFields, true)) {
                $toAdd[$colName] = $def;
            }
        }

        if (! empty($toAdd)) {
            $this->forge->addColumn('siswa', $toAdd);
        }
    }

    public function down()
    {
        $cols = ['jenis_kelamin', 'alasan_nonaktif', 'tanggal_nonaktif', 'catatan_nonaktif', 'file_surat_nonaktif'];
        foreach ($cols as $c) {
            if ($this->db->fieldExists($c, 'siswa')) {
                $this->forge->dropColumn('siswa', $c);
            }
        }
    }
}
