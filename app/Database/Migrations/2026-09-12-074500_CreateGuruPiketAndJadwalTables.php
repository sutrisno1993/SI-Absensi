<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGuruPiketAndJadwalTables extends Migration
{
    public function up()
    {
        // 1. Update users.role to include 'guru_piket'
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin','walas','pj_kelas','guru_piket') NOT NULL DEFAULT 'pj_kelas'");

        // 2. Table guru_jadwal (jadwal mengajar hari dan shift per guru)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'guru_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'hari' => [
                'type'       => 'ENUM',
                'constraint' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            ],
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['Pagi', 'Siang'],
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
        $this->forge->addKey(['guru_id', 'hari', 'shift'], false, true); // Unique key
        $this->forge->addForeignKey('guru_id', 'guru', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('guru_jadwal', true);

        // 3. Table absensi_guru (presensi kedatangan guru per shift)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'hari' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['Pagi', 'Siang'],
            ],
            'guru_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['H', 'S', 'I', 'A'],
                'default'    => 'H',
            ],
            'jam_masuk' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'menit_terlambat' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dicatat_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey(['tanggal', 'shift', 'guru_id'], false, true); // Unique key
        $this->forge->addForeignKey('guru_id', 'guru', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absensi_guru', true);

        // 4. Table keterlambatan_siswa (pencatatan siswa terlambat oleh piket)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'siswa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['Pagi', 'Siang'],
                'default'    => 'Pagi',
            ],
            'jam_masuk' => [
                'type' => 'TIME',
            ],
            'menit_terlambat' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'alasan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tindakan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dicatat_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('keterlambatan_siswa', true);

        // 5. Table piket_petugas (penugasan jadwal piket harian oleh admin)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'hari' => [
                'type'       => 'ENUM',
                'constraint' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            ],
            'shift' => [
                'type'       => 'ENUM',
                'constraint' => ['Pagi', 'Siang'],
            ],
            'guru_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey(['hari', 'shift', 'guru_id'], false, true);
        $this->forge->addForeignKey('guru_id', 'guru', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('piket_petugas', true);
    }

    public function down()
    {
        $this->forge->dropTable('piket_petugas', true);
        $this->forge->dropTable('keterlambatan_siswa', true);
        $this->forge->dropTable('absensi_guru', true);
        $this->forge->dropTable('guru_jadwal', true);
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin','walas','pj_kelas') NOT NULL DEFAULT 'pj_kelas'");
    }
}
