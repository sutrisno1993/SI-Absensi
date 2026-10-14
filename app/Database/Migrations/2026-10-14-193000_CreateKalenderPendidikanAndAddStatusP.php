<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKalenderPendidikanAndAddStatusP extends Migration
{
    public function up()
    {
        // 1. Tambahkan status 'P' (PKL) ke tabel absensi
        $this->db->query("ALTER TABLE `absensi` MODIFY COLUMN `status` ENUM('H', 'S', 'I', 'A', 'P') NOT NULL DEFAULT 'H'");

        // 2. Buat tabel kalender_pendidikan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_agenda' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['libur', 'acara'],
                'default'    => 'libur',
            ],
            'keterangan_kbm' => [
                'type'       => 'ENUM',
                'constraint' => ['bebas_absen', 'wajib_absen'],
                'default'    => 'bebas_absen',
            ],
            'target_tingkat' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'semua', // 'semua', 'X', 'XI', 'XII'
            ],
            'target_kelas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tanggal_mulai');
        $this->forge->addKey('tanggal_selesai');
        $this->forge->createTable('kalender_pendidikan', true);
    }

    public function down()
    {
        $this->forge->dropTable('kalender_pendidikan', true);
        $this->db->query("ALTER TABLE `absensi` MODIFY COLUMN `status` ENUM('H', 'S', 'I', 'A') NOT NULL DEFAULT 'H'");
    }
}
