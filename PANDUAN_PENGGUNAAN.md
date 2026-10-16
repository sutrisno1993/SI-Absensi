# 📖 Panduan Penggunaan Lengkap SI-ABSEN
### Sistem Informasi Presensi & Pembinaan Siswa — SMK 11 Maret

Dokumen ini memuat petunjuk operasional dan panduan lengkap penggunaan seluruh fitur aplikasi **SI-ABSEN** untuk semua peran (*role*).

---

## 📑 Daftar Isi
1. [Sekilas Aplikasi & Akses Domain](#1-sekilas-aplikasi--akses-domain)
2. [Matriks Peran & Hak Akses Pengguna](#2-matriks-peran--hak-akses-pengguna)
3. [Panduan Role: Administrator (Admin)](#3-panduan-role-administrator-admin)
   - [3.1 Dashboard & Statistik Global](#31-dashboard--statistik-global)
   - [3.2 Master Data Guru & Import Excel](#32-master-data-guru--import-excel)
   - [3.3 Master Data Kelas & Wali Kelas](#33-master-data-kelas--wali-kelas)
   - [3.4 Master Data Siswa, Gender L/P & Status Mutasi](#34-master-data-siswa-gender-lp--status-mutasi)
   - [3.5 Manajemen Pengguna & Reset Password](#35-manajemen-pengguna--reset-password)
   - [3.6 Penugasan Guru Piket & Jadwal Mengajar](#36-penugasan-guru-piket--jadwal-mengajar)
   - [3.7 Kalender Pendidikan & Hari Libur](#37-kalender-pendidikan--hari-libur)
   - [3.8 Monitoring Keterlambatan Guru & Jam Masuk](#38-monitoring-keterlambatan-guru--jam-masuk)
   - [3.9 Laporan Presensi Hierarkis (Drill-Down) & Export Excel](#39-laporan-presensi-hierarkis-drill-down--export-excel)
   - [3.10 Monitoring Persentase Kehadiran Guru & Siswa](#310-monitoring-persentase-kehadiran-guru--siswa)
   - [3.11 Riwayat Pembinaan Siswa (Kasus Perilaku/SP)](#311-riwayat-pembinaan-siswa-kasus-perilakusp)
   - [3.12 Evaluasi Rombel Rapat Akhir Semester](#312-evaluasi-rombel-rapat-akhir-semester)
4. [Panduan Role: Wali Kelas (Walas)](#4-panduan-role-wali-kelas-walas)
   - [4.1 Dashboard Wali Kelas & Filter Rentang Tanggal](#41-dashboard-wali-kelas--filter-rentang-tanggal)
   - [4.2 Input & Koreksi Presensi Harian Siswa](#42-input--koreksi-presensi-harian-siswa)
   - [4.3 Fitur Tandai Libur / Acara Khusus Kelas](#43-fitur-tandai-libur--acara-khusus-kelas)
   - [4.4 Rekap Kehadiran Kelas & Export Excel](#44-rekap-kehadiran-kelas--export-excel)
   - [4.5 Laporan & Riwayat Keterlambatan Siswa Kelas](#45-laporan--riwayat-keterlambatan-siswa-kelas)
   - [4.6 Pencatatan Kasus & Pembinaan Siswa Binaan](#46-pencatatan-kasus--pembinaan-siswa-binaan)
   - [4.7 Evaluasi Rombel Semester (Dinamika L/P & Mutasi)](#47-evaluasi-rombel-semester-dinamika-lp--mutasi)
   - [4.8 Manajemen Siswa Kelas (Nonaktif/Mutasi & Gender)](#48-manajemen-siswa-kelas-nonaktifmutasi--gender)
   - [4.9 Manajemen Akun Perwakilan Kelas (PJ Kelas)](#49-manajemen-akun-perwakilan-kelas-pj-kelas)
5. [Panduan Role: Guru Piket (guru_piket)](#5-panduan-role-guru-piket-guru_piket)
   - [5.1 Dashboard Guru Piket](#51-dashboard-guru-piket)
   - [5.2 Presensi Kedatangan Guru Mengajar](#52-presensi-kedatangan-guru-mengajar)
   - [5.3 Pencatatan Siswa Terlambat & Auto-Sinkron](#53-pencatatan-siswa-terlambat--auto-sinkron)
   - [5.4 Rekapitulasi Presensi Piket & Share WhatsApp](#54-rekapitulasi-presensi-piket--share-whatsapp)
6. [Panduan Role: Penanggung Jawab Kelas (pj_kelas)](#6-panduan-role-penanggung-jawab-kelas-pj_kelas)
   - [6.1 Dashboard & Login PJ Kelas](#61-dashboard--login-pj-kelas)
   - [6.2 Pengisian Presensi Rekan Sekelas](#62-pengisian-presensi-rekan-sekelas)
   - [6.3 Upload Foto Suasana Belajar di Kelas](#63-upload-foto-suasana-belajar-di-kelas)
   - [6.4 Laporan WhatsApp Otomatis ke Grup Orang Tua](#64-laporan-whatsapp-otomatis-ke-grup-orang-tua)
7. [Panduan Akses Publik: Live Presensi Orang Tua Murid](#7-panduan-akses-publik-live-presensi-orang-tua-murid)
8. [Standar Operasional Prosedur (SOP) Rutin Sekolah](#8-standar-operasional-prosedur-sop-rutin-sekolah)
9. [Tanya Jawab & Pemecahan Masalah (FAQ)](#9-tanya-jawab--pemecahan-masalah-faq)

---

## 1. Sekilas Aplikasi & Akses Domain

**SI-ABSEN** adalah platform terintegrasi untuk pencatatan presensi siswa, absensi kedatangan guru, pemantauan ketertiban/keterlambatan, catatan pembinaan siswa bermasalah, hingga evaluasi rombel rapat akhir semester di SMK 11 Maret.

### 🌐 Akses Multi-Domain & Skema Tema
Aplikasi ini mendukung multi-subdomain dengan pembedaan visual otomatis:
1. **Portal Presensi Utama**: `https://presensi.smk11maret.id/`
   - **Tema**: **Hijau Tua (Dark Emerald / Forest Green `#10b981`)** khas almamater SMK 11 Maret.
   - **Fungsi**: Akses utama seluruh sivitas akademika (Admin, Walas, Piket, PJ Kelas).
2. **Portal OSIS**: `https://osis.smk11maret.id/`
   - **Tema**: **Modern Indigo / Deep Navy Blue (`#6366f1`)**.
   - **Fungsi**: Portal kegiatan siswa / OSIS SMK 11 Maret.

---

## 2. Matriks Peran & Hak Akses Pengguna

| Fitur / Modul | Admin | Wali Kelas | Guru Piket | PJ Kelas | Orang Tua (Publik) |
|---|:---:|:---:|:---:|:---:|:---:|
| Kelola Master Guru, Kelas, & Users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Kelola Siswa, Mutasi, & Gender L/P | ✅ | ✅ (Kelas Sendiri) | ❌ | ❌ | ❌ |
| Jadwal Mengajar & Penugasan Piket | ✅ | ❌ | ❌ | ❌ | ❌ |
| Kalender Pendidikan (Libur Sekolah) | ✅ | ❌ | ❌ | ❌ | ❌ |
| Absensi Guru per Shift (Pagi/Siang) | ✅ | ❌ | ✅ | ❌ | ❌ |
| Catat Siswa Terlambat di Gerbang | ✅ | ❌ | ✅ | ❌ | ❌ |
| Input Presensi Harian Siswa | ✅ | ✅ | ❌ | ✅ | ❌ |
| Upload Foto Suasana Belajar Kelas | ❌ | ✅ | ❌ | ✅ | ❌ |
| Rekap Laporan & Export Excel Kelas | ✅ | ✅ | ❌ | ✅ (Lihat) | ❌ |
| Catat Kasus & Pembinaan Siswa | ✅ | ✅ | ❌ | ❌ | ❌ |
| Rapat Evaluasi Rombel Akhir Semester | ✅ (Semua Kelas) | ✅ (Kelas Sendiri) | ❌ | ❌ | ❌ |
| Live Presensi Real-Time & Foto Kelas | ✅ | ✅ | ✅ | ✅ | ✅ (Via Link/QR) |

---

## 3. Panduan Role: Administrator (Admin)

Administrator memegang kendali penuh atas konfigurasi sistem, data induk master, kalender pendidikan, dan rekapitulasi sekolah secara komprehensif.

### 3.1 Dashboard & Statistik Global
- **Akses**: Menu `Dashboard` atau URL `/admin`.
- **Informasi yang Ditampilkan**:
  - Total Guru, Total Kelas, Total Siswa Aktif, dan Persentase Rata-rata Kehadiran Sekolah.
  - Kartu Ringkasan Hari Ini: Siswa Hadir, Sakit, Izin, Alpa, Terlambat.
  - Grafik tren kehadiran mingguan/bulanan.
  - Daftar kelas dengan tingkat kehadiran terendah (indikator perhatian khusus).

### 3.2 Master Data Guru & Import Excel
- **Akses**: Menu `Data Master` ➔ `Data Guru` (`/admin/guru`).
- **Tambah Guru Manual**:
  1. Klik tombol **Tambah Guru**.
  2. Isi NIP, Nama Lengkap beserta gelar, dan Nomor WhatsApp aktif.
  3. Klik **Simpan**.
- **Import Guru Massal**:
  1. Klik tombol **Download Template Excel Guru**.
  2. Isi kolom NIP, Nama Guru, dan No HP pada file `.xlsx` yang diunduh.
  3. Klik **Import Excel**, pilih berkas Anda, lalu klik **Upload & Proses**.

### 3.3 Master Data Kelas & Wali Kelas
- **Akses**: Menu `Data Master` ➔ `Data Kelas` (`/admin/kelas`).
- **Kelola Rombel**:
  1. Tambah nama rombel (contoh: `X TKJ 1`, `XI AKL 2`, `XII BDP 1`).
  2. Tentukan **Shift Belajar**:
     - **Pagi**: Jam masuk pukul `06:30` / `07:00`.
     - **Siang**: Jam masuk pukul `12:30`.
  3. Pilih **Wali Kelas** penanggung jawab dari daftar guru yang terdaftar.
  4. Klik **Simpan**.

### 3.4 Master Data Siswa, Gender L/P & Status Mutasi
- **Akses**: Menu `Data Master` ➔ `Data Siswa` (`/admin/siswa`).
- **Kelola Profil & Gender Siswa**:
  - Terdapat indikator badge jenis kelamin: **L (Laki-laki)** berwarna biru, **P (Perempuan)** berwarna pink.
- **Fitur Deteksi Otomatis Gender L/P (`Auto-Detect`)**:
  1. Jika banyak data siswa belum memiliki gender, klik tombol **⚡ Deteksi Otomatis Gender (L/P)**.
  2. Sistem berbasis AI linguistik akan menganalisis nama siswa (misal: Muhammad, Putri, Siti, Bagas, Rizky, Dewi) untuk menebak jenis kelamin.
  3. Muncul modal verifikasi tabel hasil deteksi.
  4. Anda dapat mengoreksi tebakan jika ada yang keliru, lalu klik **Simpan Semua Gender**.
- **Edit Batch Gender**: Memungkinkan pemilihan cepat jenis kelamin per baris tanpa harus membuka form edit satu per satu.
- **Status Keaktifan & Mutasi**:
  - Siswa yang keluar, pindah sekolah, atau mengundurkan diri dapat diubah statusnya menjadi **Non-Aktif / Mutasi** dengan mengklik tombol status atau tombol **Keluarkan/Mutasi**.
  - Masukkan alasan (Pindah Sekolah, Mengundurkan Diri, Dikeluarkan/DO, Lainnya), tanggal mutasi, dan catatan nomor surat/berkas.

### 3.5 Manajemen Pengguna & Reset Password
- **Akses**: Menu `Pengaturan` ➔ `Manajemen Pengguna` (`/admin/users`).
- **Fungsi**:
  - Membuat akun baru untuk Admin, Wali Kelas, atau Guru Piket.
  - Reset password pengguna jika guru atau wali kelas lupa kata sandi. Password default setelah reset adalah `12345678` (pengguna akan diwajibkan mengganti password pada login pertama).

### 3.6 Penugasan Guru Piket & Jadwal Mengajar
- **Jadwal Guru Mengajar** (`/admin/jadwal-guru`):
  - Mengatur jadwal mengajar setiap guru berdasarkan Hari (Senin–Sabtu) dan Shift (Pagi/Siang).
  - Data jadwal ini menjadi acuan daftar wajib hadir pada modul absensi Guru Piket.
- **Penugasan Guru Piket** (`/admin/penugasan-piket`):
  - Menugaskan guru-guru yang berjaga piket pada hari tertentu.

### 3.7 Kalender Pendidikan & Hari Libur
- **Akses**: Menu `Akademik` ➔ `Kalender Pendidikan` (`/admin/kalender-pendidikan`).
- **Fungsi**:
  - Mendaftarkan hari libur nasional, libur cuti bersama, atau agenda sekolah khusus.
  - **Dampak Sistem**: Tanggal yang ditandai libur otomatis **tidak dihitung sebagai alpa/kekurangan hari efektif** pada rekapitulasi persentase kehadiran bulanan dan semester.

### 3.8 Monitoring Keterlambatan Guru & Jam Masuk
- **Akses**: Menu `Guru` ➔ `Keterlambatan Guru` (`/admin/keterlambatan-guru`).
- **Konfigurasi Jam Masuk**:
  - Atur jam masuk resmi Shift Pagi (default `06:30`) dan Shift Siang (default `12:30`) beserta toleransi menit.
- **Rekap Keterlambatan**:
  - Melihat akumulasi keterlambatan guru dalam hitungan menit dan frekuensi kejadian.
  - Dilengkapi tombol **Export Excel Keterlambatan Guru**.

### 3.9 Laporan Presensi Hierarkis (Drill-Down) & Export Excel
- **Akses**: Menu `Laporan` ➔ `Laporan Presensi` (`/admin/laporan`).
- **Fitur Drill-Down 3 Tingkat**:
  1. **Tingkat 1 (Global Sekolah)**: Menampilkan rekapitulasi total per kelas (H, S, I, A, P, % Kehadiran). Terdapat tombol **Export Global Excel**.
  2. **Tingkat 2 (Per Rombel Kelas)**: Klik nama kelas untuk melihat daftar seluruh siswa di kelas tersebut beserta rincian jumlah ketidakhadiran. Terdapat tombol **Export Kelas Excel**.
  3. **Tingkat 3 (Per Siswa)**: Klik nama siswa untuk memeriksa buku catatan presensi detail siswa tersebut tanggal demi tanggal lengkap dengan status dan keterangannya. Terdapat tombol **Export Siswa Excel**.
- **Filter Fleksibel**: Dapat disaring berdasarkan tanggal tunggal, bulan, atau rentang tanggal custom (contoh: 1 semester penuh).

### 3.10 Monitoring Persentase Kehadiran Guru & Siswa
- **Akses**: Menu `Monitoring` (`/admin/monitoring`).
- Menampilkan perbandingan persentase performa kehadiran guru vs kehadiran siswa dengan grafik interaktif dan export Excel.

### 3.11 Riwayat Pembinaan Siswa (Kasus Perilaku/SP)
- **Akses**: Menu `Kesiswaan` ➔ `Riwayat Pembinaan` (`/admin/pembinaan`).
- **Pencatatan Pelanggaran Siswa**:
  - Mencatat siswa bermasalah: perkelahian/berantem, merokok, bolos, pornografi, perundungan (*bullying*), dll.
  - Memilih **Tindakan Sekolah**: Peringatan Lisan, Surat Teguran, Panggilan Orang Tua, Surat Peringatan (SP 1, SP 2, SP 3), hingga Skorsing.
  - Mengunggah berkas bukti atau foto surat panggilan jika diperlukan.

### 3.12 Evaluasi Rombel Rapat Akhir Semester
- **Akses**: Menu `Laporan` ➔ `Evaluasi Semester` (`/admin/evaluasi-semester`).
- **Kegunaan Khusus**: Modul resmi untuk pembahasan rapat pleno kenaikan kelas / kelulusan di akhir semester.
- **Data yang Dihasilkan**:
  1. **Dinamika Jumlah Siswa**: Jumlah awal semester (Laki-laki, Perempuan, Total) vs Jumlah siswa keluar/mutasi vs Jumlah aktif akhir semester.
  2. **Identifikasi Siswa Bermasalah**:
     - Filter threshold batas minimal: Siswa dengan Alpa $\ge X$, Keterlambatan $\ge Y$, atau Kasus Pembinaan/SP $\ge Z$.
     - Menampilkan rincian kasus pelanggaran (tanggal, jenis tindakan, catatan kasus).
  3. **Export Excel Rapat Semester (2 Sheet Resmi)**:
     - **Sheet 1**: Ringkasan Eksekutif Dinamika Rombel per Kelas (Komposisi Awal L/P, Mutasi, dan Komposisi Akhir).
     - **Sheet 2**: Rekap Detail Siswa Bermasalah (Alpa, Terlambat, Riwayat SP, Rincian Kasus Perilaku, dan Kolom Catatan Keputusan Rapat Pleno).

---

## 4. Panduan Role: Wali Kelas (Walas)

Wali Kelas memiliki tanggung jawab utama terhadap absensi harian kelas binaannya, verifikasi catatan keterlambatan, pembinaan siswa, dan pertanggungjawaban rombel pada rapat evaluasi semester.

### 4.1 Dashboard Wali Kelas & Filter Rentang Tanggal
- **Akses**: Menu `Dashboard` (`/walas`).
- **Fitur Baru (Filter Rentang Tanggal Kustom)**:
  - Walas dapat memilih rentang tanggal bebas: **Dari Tanggal s/d Sampai Tanggal** (misal: 1 Juli s/d 20 Desember).
  - Ringkasan kartu statistik (Hadir, Sakit, Izin, Alpa, Terlambat) dan grafik tren otomatis menyesuaikan dengan periode tanggal yang dipilih.
- **Alert Otomatis Siswa Butuh Pembinaan**:
  - Sistem menampilkan banner peringatan jika terdapat siswa di kelas binaan yang:
    - Terlambat lebih dari 1 kali (pantauan khusus).
    - Terlambat $\ge 3$ kali (wajib diberikan pembinaan/panggilan).
    - Memiliki alpa tinggi.

### 4.2 Input & Koreksi Presensi Harian Siswa
- **Akses**: Menu `Presensi Harian` (`/walas/absen`).
- **Langkah Pengisian**:
  1. Pilih tanggal yang diinginkan (bisa hari ini, kemarin, atau tanggal lampau untuk susulan).
  2. Gunakan tombol **Hadirkan Semua Siswa** untuk mempercepat proses (semua siswa otomatis tercentang Hadir).
  3. Ubah siswa yang berhalangan menjadi:
     - **S** : Sakit (isi keterangan jika ada surat dokter).
     - **I** : Izin (isi alasan keperluan izin keluarga).
     - **A** : Alpa (tanpa keterangan).
     - **P** : Pulang Cepat (disertai alasan sakit/izin pulang).
  4. Klik tombol **Simpan Presensi Kelas**.
- **Koreksi Data**: Presensi yang telah tersimpan dapat diedit kapan saja atau dikosongkan ulang dengan tombol **Kosongkan/Reset Presensi Tanggal Ini**.

### 4.3 Fitur Tandai Libur / Acara Khusus Kelas
- Jika pada hari tertentu kelas mengikuti *class meeting*, studi wisata, magang industri, atau acara khusus sehingga KBM ditiadakan:
  - Klik tombol **Tandai Hari Ini Libur / Acara Khusus Kelas**.
  - Masukkan nama kegiatan (contoh: *Kunjungan Industri Batch 1*).
  - Hari tersebut tidak akan dihitung sebagai alpa siswa.

### 4.4 Rekap Kehadiran Kelas & Export Excel
- **Akses**: Menu `Rekapitulasi` (`/walas/rekap`).
- Memilih rentang tanggal rekap presensi.
- Klik **Export Excel** untuk mengunduh berkas laporan kehadiran resmi kelas binaan berformat Excel siap cetak.

### 4.5 Laporan & Riwayat Keterlambatan Siswa Kelas
- **Akses**: Menu `Keterlambatan` (`/walas/keterlambatan`).
- **Fitur**:
  - Menampilkan seluruh catatan keterlambatan siswa kelas binaan yang dicatat oleh Guru Piket di gerbang sekolah.
  - Mengetahui waktu jam masuk aktual, jumlah menit terlambat, alasan, dan tindakan piket.
  - Membantu Walas menindaklanjuti siswa yang sering terlambat sebelum menjadi kebiasaan buruk.

### 4.6 Pencatatan Kasus & Pembinaan Siswa Binaan
- **Akses**: Form Pembinaan pada menu `Evaluasi Semester` atau `Keterlambatan`.
- Walas dapat langsung mendokumentasikan:
  - Panggilan siswa untuk konseling empat mata.
  - Surat peringatan atau panggilan orang tua ke sekolah.
  - Catatan solusi dan komitmen perubahan perilaku siswa.

### 4.7 Evaluasi Rombel Semester (Dinamika L/P & Mutasi)
- **Akses**: Menu `Evaluasi Semester` (`/walas/evaluasi-semester`).
- **Fungsi Rapat Akhir Semester**:
  - Walas tidak perlu lagi menghitung manual jumlah siswa laki-laki dan perempuan saat awal semester vs akhir semester.
  - Halaman ini menyajikan tabel resmi:
    - **Jumlah Siswa Awal Semester**: Terdiri dari Laki-Laki (L) dan Perempuan (P).
    - **Daftar Siswa Keluar / Pindah**: Lengkap dengan tanggal dan alasan mutasi.
    - **Jumlah Siswa Aktif Saat Ini**: Laki-Laki (L) dan Perempuan (P).
    - **Daftar Siswa Bermasalah**: Siswa yang alpa-nya banyak, terlambat $\ge 3$ kali, atau memiliki riwayat kasus pembinaan/SP.
  - Klik tombol **Export Excel Rapat Semester** untuk mengunduh laporan 2 sheet untuk diserahkan ke kepala sekolah dan kurikulum.

### 4.8 Manajemen Siswa Kelas (Nonaktif/Mutasi & Gender)
- **Akses**: Menu `Data Siswa` (`/walas/siswa`).
- **Menyatakan Siswa Nonaktif/Keluar**:
  - Jika seorang siswa mengundurkan diri atau pindah sekolah di tengah semester, klik tombol **Keluarkan / Mutasi**.
  - Pilih alasan: *Pindah Sekolah*, *Mengundurkan Diri*, *Dikeluarkan (DO)*, atau *Lainnya*.
  - Masukkan tanggal efektif dan keterangan dokumen. Siswa tersebut tidak akan muncul lagi di presensi harian berikutnya namun tetap tercatat dalam sejarah rombel.
- **Deteksi Otomatis Gender L/P**:
  - Walas juga dapat menggunakan fitur **⚡ Deteksi Otomatis Gender (L/P)** untuk kelasnya secara instan.

### 4.9 Manajemen Akun Perwakilan Kelas (PJ Kelas)
- **Akses**: Menu `Akun PJ Kelas` (`/walas/akun-siswa`).
- **Fungsi**:
  - Membuatkan (*generate*) akun login untuk siswa perwakilan kelas (Sekretaris Kelas / Ketua Kelas) yang bertugas mengabsen teman-temannya.
  - Reset password akun PJ kelas apabila perwakilan kelas lupa kata sandi.

---

## 5. Panduan Role: Guru Piket (`guru_piket`)

Guru Piket bertanggung jawab atas ketertiban kedatangan di gerbang sekolah, pencatatan absensi guru mengajar, dan penanganan siswa yang terlambat.

### 5.1 Dashboard Guru Piket
- **Akses**: URL `/piket`.
- Menampilkan informasi tanggal piket, jadwal shift aktif (Pagi / Siang), rekap kehadiran guru hari ini, dan jumlah siswa yang terjaring terlambat.

### 5.2 Presensi Kedatangan Guru Mengajar
- **Akses**: Menu `Absen Guru Mengajar` (`/piket/guru-absen`).
- **Langkah-langkah**:
  1. Pilih tanggal dan tentukan **Shift** (Pagi jam `06:30` atau Siang jam `12:30`).
  2. Sistem menampilkan daftar guru yang memiliki jam mengajar pada hari dan shift tersebut.
  3. Pilih status kedatangan untuk masing-masing guru:
     - **Hadir Tepat Waktu**: Guru tiba sebelum atau pas jam masuk.
     - **Terlambat**: Masukkan jumlah **menit keterlambatan** dan alasan.
     - **Izin / Sakit / Alpa**: Disertai catatan penugasan kelas / guru pengganti.
  4. Klik tombol **Simpan Absensi Guru**.
- **Fitur Share WhatsApp**:
  - Klik tombol **Share Laporan WA Grup**.
  - Sistem otomatis menyusun format pesan WhatsApp yang rapi dan siap dikirim ke grup internal guru/manajemen sekolah.

### 5.3 Pencatatan Siswa Terlambat & Auto-Sinkron
- **Akses**: Menu `Siswa Terlambat` (`/piket/siswa-terlambat`).
- **Langkah Pencatatan Cepat di Gerbang**:
  1. Tentukan tanggal dan shift kedatangan.
  2. Saring kelas siswa untuk mempercepat pencarian, lalu pilih **Nama Siswa**.
  3. Masukkan **Jam Datang Aktual** (sistem otomatis menghitung berapa menit siswa terlambat).
  4. Isi **Alasan Terlambat** (contoh: *Ban bocor*, *Kesiangan*, *Macet*).
  5. Masukkan **Tindakan Piket** (contoh: *Teguran lisan*, *Membersihkan musholla*, *Push-up 10x*).
  6. Klik **Simpan Keterlambatan**.
- **⚡ Fitur Sinkronisasi Otomatis**:
  - Saat data siswa terlambat disimpan oleh Piket, status presensi siswa di kelasnya pada hari tersebut otomatis diperbarui menjadi **Hadir (Terlambat)**. Walas dan PJ kelas tidak perlu mengubahnya secara manual lagi.

### 5.4 Rekapitulasi Presensi Piket & Share WhatsApp
- **Akses**: Menu `Rekap Piket` (`/piket/rekap`).
- Melihat catatan rekapitulasi guru dan siswa terlambat dalam rentang waktu tertentu.
- Dilengkapi tombol **Export Excel Piket** untuk laporan pertanggungjawaban mingguan/bulanan.

---

## 6. Panduan Role: Penanggung Jawab Kelas (`pj_kelas`)

PJ Kelas biasanya adalah Sekretaris Kelas atau Ketua Kelas yang diberikan amanah untuk menginput kehadiran teman sekelasnya secara langsung di ruang kelas menggunakan HP/Laptop.

### 6.1 Dashboard & Login PJ Kelas
- **Akses**: Login melalui `https://presensi.smk11maret.id/` menggunakan username dan password yang diberikan oleh Wali Kelas.
- Pada halaman utama, PJ Kelas langsung diarahkan ke form presensi kelasnya.

### 6.2 Pengisian Presensi Rekan Sekelas
- **Langkah-langkah**:
  1. Pastikan tanggal presensi sudah sesuai dengan hari ini.
  2. Tekan tombol **Hadirkan Semua** terlebih dahulu.
  3. Centang opsi **S (Sakit)**, **I (Izin)**, atau **A (Alpa)** hanya bagi teman sekelas yang tidak masuk.
  4. Berikan keterangan pendukung pada kolom keterangan.
  5. Klik tombol **Simpan Presensi**.

### 6.3 Upload Foto Suasana Belajar di Kelas
- **Tujuan**: Sebagai bukti otentik kegiatan belajar mengajar aktif di kelas dan transparansi ke orang tua siswa.
- **Langkah-langkah**:
  1. Klik tombol **Upload Foto Kelas** (atau *Ganti Foto* jika sudah pernah mengunggah).
  2. Ambil foto suasana anak-anak yang sedang belajar di kelas dari sudut pandang depan/belakang ruangan.
  3. Unggah file foto (JPG/PNG).
  4. Foto ini akan langsung terpublikasi secara otomatis di halaman **Live Presensi Orang Tua**.

### 6.4 Laporan WhatsApp Otomatis ke Grup Orang Tua
- PJ Kelas atau Wali Kelas dapat mengklik tombol **Laporan WA Ortu**.
- Sistem akan merangkum daftar kehadiran kelas hari ini dalam format teks yang rapi, lengkap dengan tautan Live Presensi, yang dapat langsung disalin (*copy-paste*) ke grup WhatsApp paguyuban wali murid.

---

## 7. Panduan Akses Publik: Live Presensi Orang Tua Murid

Fitur ini dirancang khusus untuk mewujudkan **transparansi sekolah** kepada orang tua/wali murid tanpa memerlukan akun atau kata sandi.

### 7.1 Cara Mengakses
1. Setiap sesi presensi kelas memiliki tautan unik (*token link*), contoh:  
   `https://presensi.smk11maret.id/live-presensi/a8f9c1b2d3e4`
2. Tautan ini dapat dibagikan oleh Wali Kelas atau PJ Kelas ke grup WhatsApp wali murid setiap pagi.

### 7.2 Informasi yang Dilihat Orang Tua
- **Nama Kelas, Hari/Tanggal, & Nama Wali Kelas**.
- **Persentase Tingkat Kehadiran Kelas** secara *real-time*.
- **Foto Dokumentasi Suasana Kelas Hari Ini**: Orang tua dapat mengklik foto untuk memperbesar dan melihat anak mereka sedang belajar di kelas.
- **Daftar Siswa yang Tidak Hadir**: Transparansi mengenai siapa saja siswa yang Sakit, Izin, atau Alpa pada hari tersebut.

---

## 8. Standar Operasional Prosedur (SOP) Rutin Sekolah

```mermaid
graph TD
    A[Pukul 06:15 - 07:00: Guru Piket Standby di Gerbang] --> B[Siswa Terlambat Dicatat di Modul Piket]
    B --> C[Status Absen Kelas Otomatis Ter-update Hadir Terlambat]
    D[Pukul 07:00 - 07:30: Bel Masuk Kelas] --> E[PJ Kelas / Walas Absen Siswa di Ruang Kelas]
    E --> F[PJ Kelas Upload Foto Kegiatan Kelas]
    F --> G[Walas / PJ Share Link Live Presensi ke WA Orang Tua]
    H[Pukul 07:30: Jam Mengajar Dimulai] --> I[Guru Piket Input Absensi Kedatangan Guru Shift Pagi]
    I --> J[Piket Share Laporan Guru ke Grup WA Sekolah]
    K[Akhir Jam Sekolah: Verifikasi Walas] --> L[Walas Pantau Rekap & Tindak Lanjut Siswa Terlambat / Alpa]
    M[Akhir Semester: Rapat Pleno Rombel] --> N[Walas & Admin Buka Modul Evaluasi Semester & Download Excel 2 Sheet]
```

### 1. SOP Pagi Hari (Pukul 06:15 – 07:30 WIB)
1. **Guru Piket**:
   - Membuka menu `piket/siswa-terlambat` untuk mencatat siswa yang datang melebihi toleransi bel masuk.
   - Menginput presensi guru pada menu `piket/guru-absen` untuk Shift Pagi.
2. **PJ Kelas / Wali Kelas**:
   - Mengecek kehadiran siswa di dalam ruangan kelas dan menyimpan data presensi.
   - Mengambil 1 foto dokumentasi suasana kelas dan mengunggahnya.
   - Membagikan rekap dan link Live Presensi ke grup WhatsApp orang tua murid.

### 2. SOP Siang Hari (Pukul 12:00 – 13:00 WIB)
1. **Guru Piket**:
   - Melakukan pencatatan siswa terlambat dan absensi guru untuk Shift Siang.
   - Membagikan rekap kedatangan guru ke grup internal guru.

### 3. SOP Mingguan / Bulanan
1. **Wali Kelas**:
   - Membuka menu `walas/keterlambatan` untuk mendeteksi siswa yang terlambat $>1$ kali.
   - Melakukan pembinaan khusus bagi siswa yang terlambat $\ge 3$ kali atau memiliki alpa berturut-turut.
2. **Admin / Kurikulum**:
   - Memantau menu `admin/monitoring` untuk memeriksa persentase kehadiran guru dan kelas secara menyeluruh.

### 4. SOP Rapat Akhir Semester (Evaluasi Rombel)
1. **Wali Kelas**:
   - Membuka menu `walas/evaluasi-semester`.
   - Memeriksa jumlah awal L/P, mencatat mutasi keluar, dan mengecek daftar siswa bermasalah.
   - Mengunduh berkas laporan Excel 2 sheet untuk dibawa ke forum rapat kenaikan kelas.
2. **Kepala Sekolah & Tim Manajemen**:
   - Membuka menu `admin/evaluasi-semester` untuk meninjau dinamika rombel seluruh sekolah dan menetapkan keputusan kenaikan kelas / kelulusan.

---

## 9. Tanya Jawab & Pemecahan Masalah (FAQ)

### Q1: Mengapa tampilan domain `presensi.` berwarna hijau sedangkan `osis.` berwarna biru?
> **Jawaban**: Sistem menerapkan *multi-tenant branding* otomatis. Domain `presensi.smk11maret.id` memakai tema almamater **Hijau Tua (Dark Emerald)**, sedangkan domain `osis.smk11maret.id` menggunakan tema **Modern Indigo (Biru)**.

### Q2: Bagaimana jika seorang siswa pindah sekolah di tengah semester?
> **Jawaban**: Buka menu `Data Siswa` pada akun Walas atau Admin, lalu klik tombol **Keluarkan / Mutasi**. Pilih alasan mutasi, tanggal keluar, dan nomor surat. Siswa tersebut akan otomatis diarsipkan, tidak muncul lagi di presensi harian berikutnya, namun **tetap tercatat secara akurat pada laporan evaluasi rapat akhir semester**.

### Q3: Apakah Walas bisa mengabsen tanggal kemarin yang terlewat?
> **Jawaban**: Ya. Buka menu `walas/absen`, klik pemilih tanggal (*datepicker*) atau pilih tombol jalan pintas **Kemarin** / **2 Hari Lalu**, lalu isi presensi dan klik Simpan.

### Q4: Siswa terlambat sudah dicatat oleh Guru Piket, apakah PJ Kelas harus mengubah absennya lagi?
> **Jawaban**: Tidak perlu. Modul Siswa Terlambat pada Guru Piket sudah memiliki fitur **Auto-Sync**. Status presensi siswa tersebut langsung terkonversi otomatis menjadi **Hadir (Terlambat)** di kelasnya.

### Q5: Nama siswa baru diimport tapi kolom jenis kelaminnya (L/P) masih kosong, apakah harus diedit satu per satu?
> **Jawaban**: Tidak perlu repot mengedit satu per satu. Admin atau Wali Kelas cukup mengklik tombol **⚡ Deteksi Otomatis Gender (L/P)** di halaman Data Siswa. Sistem akan menganalisis nama siswa secara cerdas dan mengisi gender L/P secara serentak.

### Q6: Lupa kata sandi akun Wali Kelas atau PJ Kelas, bagaimana solusinya?
> - Akun **PJ Kelas**: Dapat di-reset langsung oleh Wali Kelas melalui menu `walas/akun-siswa`.
> - Akun **Wali Kelas / Guru Piket**: Dapat di-reset oleh Admin melalui menu `admin/users`. Kata sandi baru akan kembali ke default `12345678`.

---
*Dokumentasi Sistem Informasi Presensi & Pembinaan Siswa (SI-ABSEN) — SMK 11 Maret*
