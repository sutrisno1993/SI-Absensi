<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInitialTables extends Migration
{
    public function up()
    {
        // 1. Table Guru
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'unique'     => true,
            ],
            'nama_guru' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
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
        $this->forge->createTable('guru', true);

        // 2. Table Kelas
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_kelas' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'walas_id' => [
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
        $this->forge->addForeignKey('walas_id', 'guru', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('kelas', true);

        // 3. Table Siswa
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nisn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'nama_siswa' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'kelas_id' => [
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
        $this->forge->addForeignKey('kelas_id', 'kelas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa', true);

        // 4. Table Users
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'walas', 'pj_kelas'],
                'default'    => 'pj_kelas',
            ],
            'is_first_login' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'ref_id' => [
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
        $this->forge->createTable('users', true);

        // 5. Table Absensi
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
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['H', 'S', 'I', 'A'],
                'default'    => 'H',
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
        $this->forge->addKey(['tanggal', 'siswa_id'], false, true); // Unique composite index
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absensi', true);

        // 6. Table Pembinaan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tanggal_tindakan' => [
                'type' => 'DATE',
            ],
            'siswa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'walas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenis_tindakan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'catatan_pembinaan' => [
                'type' => 'TEXT',
            ],
            'file_bukti' => [
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
        $this->forge->addForeignKey('siswa_id', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('walas_id', 'guru', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pembinaan', true);
    }

    public function down()
    {
        $this->forge->dropTable('pembinaan', true);
        $this->forge->dropTable('absensi', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('kelas', true);
        $this->forge->dropTable('guru', true);
    }
}
