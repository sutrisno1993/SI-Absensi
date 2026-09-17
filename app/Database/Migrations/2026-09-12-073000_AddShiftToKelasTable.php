<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShiftToKelasTable extends Migration
{
    public function up()
    {
        $fields = [
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['Pagi', 'Siang'],
                'default'    => 'Pagi',
                'after'      => 'nama_kelas',
            ],
        ];

        $this->forge->addColumn('kelas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('kelas', 'shift');
    }
}
