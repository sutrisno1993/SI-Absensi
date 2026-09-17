<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengaturanAndPresensiKelasTables extends Migration
{
    public function up()
    {
        // 1. Table: pengaturan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pengaturan', true);

        // Insert default values jika belum ada
        $db = \Config\Database::connect();
        $db->query("
            INSERT INTO pengaturan (setting_key, setting_value, keterangan, updated_at)
            VALUES 
            ('jam_masuk_pagi', '06:30', 'Standar batas jam masuk kedatangan guru shift pagi', NOW()),
            ('jam_masuk_siang', '12:30', 'Standar batas jam masuk kedatangan guru shift siang', NOW()),
            ('toleransi_menit', '0', 'Toleransi keterlambatan dalam menit (0 = tidak ada toleransi)', NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW();
        ");

        // 2. Table: presensi_kelas_harian
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kelas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'foto_kelas' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'share_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'unique'     => true,
            ],
            'catatan' => [
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
        $this->forge->addKey('kelas_id');
        $this->forge->addKey('tanggal');
        $this->forge->addUniqueKey(['kelas_id', 'tanggal']);
        $this->forge->createTable('presensi_kelas_harian', true);
    }

    public function down()
    {
        $this->forge->dropTable('presensi_kelas_harian', true);
        $this->forge->dropTable('pengaturan', true);
    }
}
