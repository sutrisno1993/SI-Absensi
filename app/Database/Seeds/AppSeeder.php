<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AppSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Seed Guru
        $guruData = [
            [
                'id'         => 1,
                'nip'        => '198501152010011002',
                'nama_guru'  => 'Drs. Budi Santoso, M.Pd',
                'no_hp'      => '081234567890',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'nip'        => '198807202014022003',
                'nama_guru'  => 'Siti Rahmawati, S.Pd',
                'no_hp'      => '081298765432',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('guru')->ignore(true)->insertBatch($guruData);

        // 2. Seed Kelas
        $kelasData = [
            [
                'id'         => 1,
                'nama_kelas' => 'XII AK 1',
                'walas_id'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'nama_kelas' => 'XI RPL 1',
                'walas_id'   => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('kelas')->ignore(true)->insertBatch($kelasData);

        // 3. Seed Siswa
        $siswaData = [
            // Siswa Kelas XII AK 1 (ID 1)
            [
                'id'         => 1,
                'nisn'       => '0061234501',
                'nama_siswa' => 'Ahmad Fauzi',
                'kelas_id'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'nisn'       => '0061234502',
                'nama_siswa' => 'Anisa Bella',
                'kelas_id'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'nisn'       => '0061234503',
                'nama_siswa' => 'Bagus Pratama',
                'kelas_id'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 4,
                'nisn'       => '0061234504',
                'nama_siswa' => 'Citra Dewi',
                'kelas_id'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 5,
                'nisn'       => '0061234505',
                'nama_siswa' => 'Doni Hermawan',
                'kelas_id'   => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Siswa Kelas XI RPL 1 (ID 2)
            [
                'id'         => 6,
                'nisn'       => '0071234501',
                'nama_siswa' => 'Eka Saputra',
                'kelas_id'   => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 7,
                'nisn'       => '0071234502',
                'nama_siswa' => 'Fani Rahayu',
                'kelas_id'   => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 8,
                'nisn'       => '0071234503',
                'nama_siswa' => 'Gilang Ramadhan',
                'kelas_id'   => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('siswa')->ignore(true)->insertBatch($siswaData);

        // 4. Seed Users
        $usersData = [
            [
                'id'             => 1,
                'username'       => 'admin',
                'password_hash'  => password_hash('admin123', PASSWORD_BCRYPT),
                'role'           => 'admin',
                'is_first_login' => 0,
                'ref_id'         => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 2,
                'username'       => 'walas1',
                'password_hash'  => password_hash('walas123', PASSWORD_BCRYPT),
                'role'           => 'walas',
                'is_first_login' => 1, // Wajib ganti password di login pertama!
                'ref_id'         => 1, // Guru ID 1
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 3,
                'username'       => 'walas2',
                'password_hash'  => password_hash('walas123', PASSWORD_BCRYPT),
                'role'           => 'walas',
                'is_first_login' => 0,
                'ref_id'         => 2, // Guru ID 2
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 4,
                'username'       => 'pj_xii_ak1',
                'password_hash'  => password_hash('pj123', PASSWORD_BCRYPT),
                'role'           => 'pj_kelas',
                'is_first_login' => 0,
                'ref_id'         => 1, // Kelas ID 1 (XII AK 1)
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => 5,
                'username'       => 'pj_xi_rpl1',
                'password_hash'  => password_hash('pj123', PASSWORD_BCRYPT),
                'role'           => 'pj_kelas',
                'is_first_login' => 0,
                'ref_id'         => 2, // Kelas ID 2 (XI RPL 1)
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];
        $this->db->table('users')->ignore(true)->insertBatch($usersData);

        // 5. Seed sample Absensi (Doni Hermawan alpa 4x untuk memicu alert >3 alpa)
        $absensiData = [
            ['tanggal' => '2026-08-25', 'siswa_id' => 5, 'status' => 'A', 'keterangan' => 'Tanpa Keterangan', 'created_at' => $now, 'updated_at' => $now],
            ['tanggal' => '2026-08-26', 'siswa_id' => 5, 'status' => 'A', 'keterangan' => 'Tanpa Keterangan', 'created_at' => $now, 'updated_at' => $now],
            ['tanggal' => '2026-08-27', 'siswa_id' => 5, 'status' => 'A', 'keterangan' => 'Tanpa Keterangan', 'created_at' => $now, 'updated_at' => $now],
            ['tanggal' => '2026-08-28', 'siswa_id' => 5, 'status' => 'A', 'keterangan' => 'Tanpa Keterangan', 'created_at' => $now, 'updated_at' => $now],
            
            ['tanggal' => '2026-08-28', 'siswa_id' => 1, 'status' => 'H', 'keterangan' => null, 'created_at' => $now, 'updated_at' => $now],
            ['tanggal' => '2026-08-28', 'siswa_id' => 2, 'status' => 'H', 'keterangan' => null, 'created_at' => $now, 'updated_at' => $now],
            ['tanggal' => '2026-08-28', 'siswa_id' => 3, 'status' => 'S', 'keterangan' => 'Demam', 'created_at' => $now, 'updated_at' => $now],
            ['tanggal' => '2026-08-28', 'siswa_id' => 4, 'status' => 'I', 'keterangan' => 'Acara Keluarga', 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('absensi')->ignore(true)->insertBatch($absensiData);
    }
}
