<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToSiswaTable extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Nonaktif'],
                'default'    => 'Aktif',
                'after'      => 'kelas_id',
            ],
        ];

        $this->forge->addColumn('siswa', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('siswa', 'status');
    }
}
