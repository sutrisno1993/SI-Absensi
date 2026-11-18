<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLkpdTables extends Migration
{
    public function up()
    {
        // 1. Tabel Master LKPD
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sesi_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'guru_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'judul_lkpd' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'petunjuk' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_panduan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'metode_kelompok' => [
                'type'       => 'ENUM',
                'constraint' => ['acak', 'manual_guru', 'mandiri_siswa'],
                'default'    => 'mandiri_siswa',
            ],
            'tenggat_waktu' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'token_lkpd' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'aktif', 'ditutup'],
                'default'    => 'aktif',
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
        $this->forge->addKey('sesi_id');
        $this->forge->addKey('token_lkpd');
        $this->forge->createTable('lkpd', true);

        // 2. Tabel Kelompok pada LKPD
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'lkpd_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_kelompok' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'nomor_kelompok' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'ketua_siswa_id' => [
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
        $this->forge->addKey('lkpd_id');
        $this->forge->createTable('lkpd_kelompok', true);

        // 3. Tabel Anggota Kelompok & Peran
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kelompok_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'siswa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'peran' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'nilai_individu' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'catatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey(['kelompok_id', 'siswa_id']);
        $this->forge->createTable('lkpd_anggota', true);

        // 4. Tabel Jawaban & Nilai Kelompok
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kelompok_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'lkpd_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'ringkasan_kerja' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_lampiran' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'link_eksternal' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'skor_kelompok' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'feedback_guru' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status_pengumpulan' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'dikumpulkan', 'dinilai'],
                'default'    => 'draft',
            ],
            'waktu_kumpul' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('kelompok_id');
        $this->forge->addKey('lkpd_id');
        $this->forge->createTable('lkpd_jawaban', true);
    }

    public function down()
    {
        $this->forge->dropTable('lkpd_jawaban', true);
        $this->forge->dropTable('lkpd_anggota', true);
        $this->forge->dropTable('lkpd_kelompok', true);
        $this->forge->dropTable('lkpd', true);
    }
}

