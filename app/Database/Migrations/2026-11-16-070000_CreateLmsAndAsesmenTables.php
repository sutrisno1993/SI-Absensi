<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLmsAndAsesmenTables extends Migration
{
    public function up()
    {
        // 1. Tabel master mapel
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_mapel' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nama_mapel' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->createTable('mapel', true);

        // 2. Tabel relasi guru_kelas_mapel (guru mengajar mapel tertentu di kelas tertentu)
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
            'kelas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'mapel_id' => [
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
        $this->forge->addKey(['guru_id', 'kelas_id', 'mapel_id']);
        $this->forge->createTable('guru_kelas_mapel', true);

        // 3. Tabel kbm_sesi (Pertemuan KBM)
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
            'kelas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'mapel_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'jam_ke' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'judul_materi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi_materi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'berlangsung', 'selesai'],
                'default'    => 'draft',
            ],
            'token_sesi' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
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
        $this->forge->addKey(['guru_id', 'kelas_id', 'tanggal']);
        $this->forge->addKey('token_sesi');
        $this->forge->createTable('kbm_sesi', true);

        // 4. Tabel kbm_presensi (Presensi Siswa per Pertemuan Mapel)
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
        $this->forge->addKey(['sesi_id', 'siswa_id']);
        $this->forge->createTable('kbm_presensi', true);

        // 5. Tabel asesmen (Paket Soal Formatif/Diagnostik/Live)
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
            'judul_asesmen' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['formatif', 'diagnostik', 'kuis_live', 'refleksi'],
                'default'    => 'formatif',
            ],
            'durasi_menit' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 30,
            ],
            'token_live' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'aktif', 'ditutup'],
                'default'    => 'draft',
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
        $this->forge->addKey('token_live');
        $this->forge->createTable('asesmen', true);

        // 6. Tabel asesmen_soal (Butir Soal PG & Essay)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asesmen_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nomor_urut' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'tipe_soal' => [
                'type'       => 'ENUM',
                'constraint' => ['pg', 'essay'],
                'default'    => 'pg',
            ],
            'pertanyaan' => [
                'type' => 'TEXT',
            ],
            'opsi_a' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'opsi_b' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'opsi_c' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'opsi_d' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'opsi_e' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kunci_pg' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => true, // a, b, c, d, e
            ],
            'pedoman_essay' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'bobot' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 10,
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
        $this->forge->addKey('asesmen_id');
        $this->forge->createTable('asesmen_soal', true);

        // 7. Tabel asesmen_jawaban_siswa
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'asesmen_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'soal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'siswa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jawaban_pg' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => true,
            ],
            'jawaban_essay' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_benar' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'skor' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'catatan_guru' => [
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
        $this->forge->addKey(['asesmen_id', 'siswa_id']);
        $this->forge->addKey(['soal_id', 'siswa_id']);
        $this->forge->createTable('asesmen_jawaban_siswa', true);
    }

    public function down()
    {
        $this->forge->dropTable('asesmen_jawaban_siswa', true);
        $this->forge->dropTable('asesmen_soal', true);
        $this->forge->dropTable('asesmen', true);
        $this->forge->dropTable('kbm_presensi', true);
        $this->forge->dropTable('kbm_sesi', true);
        $this->forge->dropTable('guru_kelas_mapel', true);
        $this->forge->dropTable('mapel', true);
    }
}

