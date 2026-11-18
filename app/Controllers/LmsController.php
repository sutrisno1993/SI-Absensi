<?php

namespace App\Controllers;

use App\Models\GuruModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\MapelModel;
use App\Models\GuruKelasMapelModel;
use App\Models\KbmSesiModel;
use App\Models\KbmPresensiModel;
use App\Models\AsesmenModel;
use App\Models\AsesmenSoalModel;
use App\Models\AsesmenJawabanSiswaModel;
use App\Models\LkpdModel;
use App\Models\LkpdKelompokModel;
use App\Models\LkpdAnggotaModel;
use App\Models\LkpdJawabanModel;

class LmsController extends BaseController
{
    protected $guruModel;
    protected $kelasModel;
    protected $siswaModel;
    protected $mapelModel;
    protected $guruKelasMapelModel;
    protected $kbmSesiModel;
    protected $kbmPresensiModel;
    protected $asesmenModel;
    protected $asesmenSoalModel;
    protected $asesmenJawabanSiswaModel;
    protected $lkpdModel;
    protected $lkpdKelompokModel;
    protected $lkpdAnggotaModel;
    protected $lkpdJawabanModel;

    public function __construct()
    {
        $this->guruModel                = new GuruModel();
        $this->kelasModel               = new KelasModel();
        $this->siswaModel               = new SiswaModel();
        $this->mapelModel               = new MapelModel();
        $this->guruKelasMapelModel      = new GuruKelasMapelModel();
        $this->kbmSesiModel             = new KbmSesiModel();
        $this->kbmPresensiModel         = new KbmPresensiModel();
        $this->asesmenModel             = new AsesmenModel();
        $this->asesmenSoalModel         = new AsesmenSoalModel();
        $this->asesmenJawabanSiswaModel = new AsesmenJawabanSiswaModel();
        $this->lkpdModel                = new LkpdModel();
        $this->lkpdKelompokModel        = new LkpdKelompokModel();
        $this->lkpdAnggotaModel         = new LkpdAnggotaModel();
        $this->lkpdJawabanModel         = new LkpdJawabanModel();
    }

    /**
     * Helper mendapatkan Guru ID dari sesi login
     */
    protected function getGuruId()
    {
        $role  = $this->session->get('role');
        $refId = (int)$this->session->get('ref_id');

        if (($role === 'walas' || $role === 'guru_piket') && $refId > 0) {
            return $refId;
        }

        if ($role === 'admin') {
            // Jika admin ingin akses LMS, gunakan guru pertama sebagai fallback atau dari param
            $firstGuru = $this->guruModel->first();
            return $firstGuru ? (int)$firstGuru['id'] : 1;
        }

        return 0;
    }

    /**
     * Dashboard / Halaman Utama KBM LMS Guru
     */
    public function index()
    {
        $guruId = $this->getGuruId();
        if ($guruId <= 0) {
            return redirect()->to('/')->with('error', 'Akses KBM LMS hanya untuk akun Guru.');
        }

        $guru = $this->guruModel->find($guruId);
        $kelasList = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        // Riwayat sesi KBM yang pernah dibuat guru ini
        $riwayatSesi = $this->kbmSesiModel
            ->select('kbm_sesi.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id = kbm_sesi.kelas_id')
            ->join('mapel', 'mapel.id = kbm_sesi.mapel_id')
            ->where('kbm_sesi.guru_id', $guruId)
            ->orderBy('kbm_sesi.tanggal', 'DESC')
            ->orderBy('kbm_sesi.id', 'DESC')
            ->findAll(20);

        return view('lms/index', [
            'title'       => 'LMS KBM & Asesmen Live',
            'guru'        => $guru,
            'kelasList'   => $kelasList,
            'riwayatSesi' => $riwayatSesi,
        ]);
    }

    /**
     * API / Endpoint AJAX: Ambil daftar mapel guru pada kelas tertentu
     */
    public function getMapelByKelas($kelasId)
    {
        $guruId = $this->getGuruId();
        $mapelList = $this->guruKelasMapelModel->getMapelByGuruDanKelas($guruId, (int)$kelasId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $mapelList,
        ]);
    }

    /**
     * Tambah Mapel Baru & Langsung Di-link ke Guru dan Kelas ini
     */
    public function tambahMapel()
    {
        $guruId    = $this->getGuruId();
        $kelasId   = (int)$this->request->getPost('kelas_id');
        $namaMapel = trim((string)$this->request->getPost('nama_mapel'));

        if (empty($namaMapel) || $kelasId <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Nama Mata Pelajaran dan Kelas wajib diisi.',
                ]);
            }
            return redirect()->back()->with('error', 'Nama Mata Pelajaran dan Kelas wajib diisi.');
        }

        // Cek apakah mapel sudah ada di master
        $mapel = $this->mapelModel->where('nama_mapel', $namaMapel)->first();
        if (! $mapel) {
            $mapelId = (int)$this->mapelModel->insert([
                'nama_mapel' => $namaMapel,
            ]);
        } else {
            $mapelId = (int)$mapel['id'];
        }

        // Cek relasi guru_kelas_mapel
        $existing = $this->guruKelasMapelModel
            ->where('guru_id', $guruId)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->first();

        if (! $existing) {
            $this->guruKelasMapelModel->insert([
                'guru_id'  => $guruId,
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
            ]);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'     => 'success',
                'mapel_id'   => $mapelId,
                'nama_mapel' => $namaMapel,
                'kelas_id'   => $kelasId,
                'message'    => "Mata pelajaran \"{$namaMapel}\" berhasil ditambahkan!",
                'csrf_token' => csrf_hash(),
            ]);
        }

        return redirect()->to("/lms?open_kelas={$kelasId}&open_mapel={$mapelId}")
            ->with('success', "Mata pelajaran \"{$namaMapel}\" berhasil ditambahkan untuk kelas ini!");
    }

    /**
     * Buat Sesi KBM Baru (Pilih Kelas, Mapel, Judul Materi)
     */
    public function buatSesi()
    {
        $guruId          = $this->getGuruId();
        $kelasId         = (int)$this->request->getPost('kelas_id');
        $mapelId         = (int)$this->request->getPost('mapel_id');
        $tanggal         = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $jamKe           = $this->request->getPost('jam_ke');
        $judulMateri     = trim((string)$this->request->getPost('judul_materi'));
        $deskripsiMateri = trim((string)$this->request->getPost('deskripsi_materi'));

        if ($kelasId <= 0 || $mapelId <= 0 || empty($judulMateri)) {
            return redirect()->back()->with('error', 'Kelas, Mata Pelajaran, dan Judul Materi wajib diisi.');
        }

        $tokenSesi = bin2hex(random_bytes(16));

        $sesiId = (int)$this->kbmSesiModel->insert([
            'guru_id'          => $guruId,
            'kelas_id'         => $kelasId,
            'mapel_id'         => $mapelId,
            'tanggal'          => $tanggal,
            'jam_ke'           => $jamKe,
            'judul_materi'     => $judulMateri,
            'deskripsi_materi' => $deskripsiMateri,
            'status'           => 'draft',
            'token_sesi'       => $tokenSesi,
        ]);

        // Inisialisasi daftar presensi seluruh siswa aktif di kelas tersebut
        $siswaList = $this->siswaModel->getSiswaAktifByKelas($kelasId);
        foreach ($siswaList as $s) {
            $this->kbmPresensiModel->insert([
                'sesi_id'  => $sesiId,
                'siswa_id' => $s['id'],
                'status'   => 'H', // default Hadir
            ]);
        }

        return redirect()->to("/lms/sesi/{$sesiId}")->with('success', 'Sesi KBM berhasil dibuat! Silakan cek presensi dan mulai KBM.');
    }

    /**
     * Halaman Detail Sesi KBM (Kelola Presensi, Materi, dan Asesmen Live)
     */
    public function detailSesi($sesiId)
    {
        $guruId = $this->getGuruId();
        $sesi = $this->kbmSesiModel->getDetailSesi((int)$sesiId);

        if (! $sesi || ($this->session->get('role') !== 'admin' && (int)$sesi['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Sesi KBM tidak ditemukan.');
        }

        $presensiList = $this->kbmPresensiModel->getPresensiBySesi((int)$sesiId);
        $asesmenList  = $this->asesmenModel->where('sesi_id', (int)$sesiId)->orderBy('id', 'DESC')->findAll();
        $lkpdList     = $this->lkpdModel->where('sesi_id', (int)$sesiId)->orderBy('id', 'DESC')->findAll();

        return view('lms/detail_sesi', [
            'title'        => 'KBM: ' . $sesi['judul_materi'],
            'sesi'         => $sesi,
            'presensiList' => $presensiList,
            'asesmenList'  => $asesmenList,
            'lkpdList'     => $lkpdList,
        ]);
    }

    /**
     * Simpan Presensi KBM & Mulai KBM
     */
    public function simpanPresensi($sesiId)
    {
        $guruId = $this->getGuruId();
        $sesi = $this->kbmSesiModel->find((int)$sesiId);

        if (! $sesi || ($this->session->get('role') !== 'admin' && (int)$sesi['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Sesi KBM tidak ditemukan.');
        }

        $presensiData = $this->request->getPost('presensi') ?: [];
        foreach ($presensiData as $siswaId => $row) {
            $status  = $row['status'] ?? 'H';
            $catatan = $row['catatan'] ?? '';

            $existing = $this->kbmPresensiModel
                ->where('sesi_id', (int)$sesiId)
                ->where('siswa_id', (int)$siswaId)
                ->first();

            if ($existing) {
                $this->kbmPresensiModel->update($existing['id'], [
                    'status'  => $status,
                    'catatan' => $catatan,
                ]);
            }
        }

        // Update status KBM menjadi 'berlangsung' jika sebelumnya draft
        if ($sesi['status'] === 'draft') {
            $this->kbmSesiModel->update($sesi['id'], [
                'status' => 'berlangsung',
            ]);
        }

        return redirect()->back()->with('success', 'Presensi KBM berhasil disimpan dan status KBM aktif berlangsung!');
    }

    /**
     * Selesaikan Sesi KBM
     */
    public function selesaikanSesi($sesiId)
    {
        $guruId = $this->getGuruId();
        $sesi = $this->kbmSesiModel->find((int)$sesiId);

        if (! $sesi || ($this->session->get('role') !== 'admin' && (int)$sesi['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Sesi KBM tidak ditemukan.');
        }

        $this->kbmSesiModel->update($sesi['id'], [
            'status' => 'selesai',
        ]);

        return redirect()->back()->with('success', 'Sesi KBM telah dinyatakan selesai.');
    }

    /**
     * Buat Asesmen Baru atau Import via JSON
     */
    public function simpanAsesmen($sesiId)
    {
        $guruId = $this->getGuruId();
        $sesi = $this->kbmSesiModel->find((int)$sesiId);

        if (! $sesi || ($this->session->get('role') !== 'admin' && (int)$sesi['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Sesi KBM tidak ditemukan.');
        }

        $judul       = trim((string)$this->request->getPost('judul_asesmen'));
        $tipe        = $this->request->getPost('tipe') ?: 'formatif';
        $durasi      = (int)($this->request->getPost('durasi_menit') ?: 30);
        $tokenLive   = bin2hex(random_bytes(8)); // Kode link unik siswa

        $fileJson  = $this->request->getFile('file_json');
        $pasteJson = trim((string)$this->request->getPost('paste_json'));

        $asesmenId = (int)$this->asesmenModel->insert([
            'sesi_id'       => (int)$sesiId,
            'guru_id'       => $guruId,
            'judul_asesmen' => $judul,
            'tipe'          => $tipe,
            'durasi_menit'  => $durasi,
            'token_live'    => $tokenLive,
            'status'        => 'aktif', // langsung aktif untuk live
        ]);

        // Cek jika ada paste teks JSON atau unggahan file JSON
        $jsonData = null;
        if (!empty($pasteJson)) {
            // Bersihkan markdown code block ```json jika ada dari output AI
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $pasteJson);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
            $jsonData = json_decode($cleaned, true);
        } elseif ($fileJson && $fileJson->isValid() && ! $fileJson->hasMoved()) {
            $content = file_get_contents($fileJson->getTempName());
            $jsonData = json_decode($content, true);
        }

        if ($jsonData) {
            $soalItems = isset($jsonData['soal']) && is_array($jsonData['soal']) ? $jsonData['soal'] : (is_array($jsonData) ? $jsonData : []);
            $nomor = 1;
            foreach ($soalItems as $item) {
                if (!isset($item['pertanyaan'])) continue;
                $tipeSoal = strtolower($item['tipe'] ?? 'pg');
                $opsi = $item['opsi'] ?? [];

                $this->asesmenSoalModel->insert([
                    'asesmen_id'    => $asesmenId,
                    'nomor_urut'    => $nomor++,
                    'tipe_soal'     => in_array($tipeSoal, ['pg', 'essay']) ? $tipeSoal : 'pg',
                    'pertanyaan'    => $item['pertanyaan'] ?? '',
                    'opsi_a'        => $opsi['a'] ?? null,
                    'opsi_b'        => $opsi['b'] ?? null,
                    'opsi_c'        => $opsi['c'] ?? null,
                    'opsi_d'        => $opsi['d'] ?? null,
                    'opsi_e'        => $opsi['e'] ?? null,
                    'kunci_pg'      => isset($item['kunci']) ? strtolower(trim($item['kunci'])) : null,
                    'pedoman_essay' => $item['pedoman_jawaban'] ?? ($item['kunci'] ?? null),
                    'bobot'         => (int)($item['bobot'] ?? 10),
                ]);
            }
        }

        return redirect()->to("/lms/asesmen/{$asesmenId}")->with('success', 'Asesmen Live berhasil dibuat! Kode tautan & QR Code siap dibagikan.');
    }

    /**
     * Detail Asesmen Live (Papan Kontrol Guru, QR Code, dan Live Nilai)
     */
    public function detailAsesmen($asesmenId)
    {
        $guruId = $this->getGuruId();
        $asesmen = $this->asesmenModel->find((int)$asesmenId);

        if (! $asesmen || ($this->session->get('role') !== 'admin' && (int)$asesmen['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Asesmen tidak ditemukan.');
        }

        $sesi      = $this->kbmSesiModel->getDetailSesi($asesmen['sesi_id']);
        $soalList  = $this->asesmenSoalModel->getSoalByAsesmen((int)$asesmenId);
        $rekapNilai = $this->asesmenJawabanSiswaModel->getRekapHasilSiswa((int)$asesmenId);

        return view('lms/detail_asesmen', [
            'title'      => 'Asesmen Live: ' . $asesmen['judul_asesmen'],
            'asesmen'    => $asesmen,
            'sesi'       => $sesi,
            'soalList'   => $soalList,
            'rekapNilai' => $rekapNilai,
        ]);
    }

    /**
     * Input / Tambah Soal Manual ke Asesmen
     */
    public function tambahSoal($asesmenId)
    {
        $guruId = $this->getGuruId();
        $asesmen = $this->asesmenModel->find((int)$asesmenId);

        if (! $asesmen || ($this->session->get('role') !== 'admin' && (int)$asesmen['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Asesmen tidak ditemukan.');
        }

        $tipeSoal = $this->request->getPost('tipe_soal') === 'essay' ? 'essay' : 'pg';
        $totalSoal = $this->asesmenSoalModel->where('asesmen_id', (int)$asesmenId)->countAllResults();

        $this->asesmenSoalModel->insert([
            'asesmen_id'    => (int)$asesmenId,
            'nomor_urut'    => $totalSoal + 1,
            'tipe_soal'     => $tipeSoal,
            'pertanyaan'    => trim((string)$this->request->getPost('pertanyaan')),
            'opsi_a'        => $this->request->getPost('opsi_a'),
            'opsi_b'        => $this->request->getPost('opsi_b'),
            'opsi_c'        => $this->request->getPost('opsi_c'),
            'opsi_d'        => $this->request->getPost('opsi_d'),
            'opsi_e'        => $this->request->getPost('opsi_e'),
            'kunci_pg'      => strtolower(trim((string)$this->request->getPost('kunci_pg'))),
            'pedoman_essay' => trim((string)$this->request->getPost('pedoman_essay')),
            'bobot'         => (int)($this->request->getPost('bobot') ?: 10),
        ]);

        return redirect()->back()->with('success', 'Butir soal berhasil ditambahkan!');
    }

    /**
     * Import Butir Soal via Paste Teks JSON atau Upload File JSON
     */
    public function importSoalJson($asesmenId)
    {
        $guruId  = $this->getGuruId();
        $asesmen = $this->asesmenModel->find((int)$asesmenId);

        if (! $asesmen || ($this->session->get('role') !== 'admin' && (int)$asesmen['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Asesmen tidak valid.');
        }

        $fileJson  = $this->request->getFile('file_json');
        $pasteJson = trim((string)$this->request->getPost('paste_json'));

        $jsonData = null;
        if (!empty($pasteJson)) {
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $pasteJson);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
            $jsonData = json_decode($cleaned, true);
        } elseif ($fileJson && $fileJson->isValid() && ! $fileJson->hasMoved()) {
            $content = file_get_contents($fileJson->getTempName());
            $jsonData = json_decode($content, true);
        }

        if (!$jsonData) {
            return redirect()->back()->with('error', 'Format JSON tidak valid atau gagal didecode.');
        }

        $soalItems = isset($jsonData['soal']) && is_array($jsonData['soal']) ? $jsonData['soal'] : (is_array($jsonData) ? $jsonData : []);
        if (empty($soalItems)) {
            return redirect()->back()->with('error', 'Tidak ditemukan data butir soal dalam format JSON tersebut.');
        }

        $maxNomor = $this->asesmenSoalModel->where('asesmen_id', (int)$asesmenId)->selectMax('nomor_urut')->first();
        $nomor = ($maxNomor['nomor_urut'] ?? 0) + 1;
        $count = 0;

        foreach ($soalItems as $item) {
            if (!isset($item['pertanyaan'])) continue;
            $tipeSoal = strtolower($item['tipe'] ?? 'pg');
            $opsi = $item['opsi'] ?? [];

            $this->asesmenSoalModel->insert([
                'asesmen_id'    => (int)$asesmenId,
                'nomor_urut'    => $nomor++,
                'tipe_soal'     => in_array($tipeSoal, ['pg', 'essay']) ? $tipeSoal : 'pg',
                'pertanyaan'    => $item['pertanyaan'] ?? '',
                'opsi_a'        => $opsi['a'] ?? null,
                'opsi_b'        => $opsi['b'] ?? null,
                'opsi_c'        => $opsi['c'] ?? null,
                'opsi_d'        => $opsi['d'] ?? null,
                'opsi_e'        => $opsi['e'] ?? null,
                'kunci_pg'      => isset($item['kunci']) ? strtolower(trim($item['kunci'])) : null,
                'pedoman_essay' => $item['pedoman_jawaban'] ?? ($item['kunci'] ?? null),
                'bobot'         => (int)($item['bobot'] ?? 10),
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "Berhasil mengimpor {$count} butir soal dari JSON!");
    }

    /**
     * API AJAX: Mengambil hasil respon siswa secara realtime (Live polling guru)
     */
    public function getLiveScores($asesmenId)
    {
        $guruId = $this->getGuruId();
        $asesmen = $this->asesmenModel->find((int)$asesmenId);

        if (! $asesmen || ($this->session->get('role') !== 'admin' && (int)$asesmen['guru_id'] !== $guruId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $rekapNilai = $this->asesmenJawabanSiswaModel->getRekapHasilSiswa((int)$asesmenId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $rekapNilai,
        ]);
    }

    /**
     * Form Penilaian Essay Siswa oleh Guru
     */
    public function nilaiEssay($asesmenId, $siswaId)
    {
        $guruId = $this->getGuruId();
        $asesmen = $this->asesmenModel->find((int)$asesmenId);

        if (! $asesmen || ($this->session->get('role') !== 'admin' && (int)$asesmen['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Asesmen tidak ditemukan.');
        }

        $siswa = $this->siswaModel->find((int)$siswaId);
        $jawabanList = $this->asesmenJawabanSiswaModel
            ->select('asesmen_jawaban_siswa.*, asesmen_soal.nomor_urut, asesmen_soal.tipe_soal, asesmen_soal.pertanyaan, asesmen_soal.pedoman_essay, asesmen_soal.bobot')
            ->join('asesmen_soal', 'asesmen_soal.id = asesmen_jawaban_siswa.soal_id')
            ->where('asesmen_jawaban_siswa.asesmen_id', (int)$asesmenId)
            ->where('asesmen_jawaban_siswa.siswa_id', (int)$siswaId)
            ->orderBy('asesmen_soal.nomor_urut', 'ASC')
            ->findAll();

        return view('lms/nilai_essay', [
            'title'       => 'Penilaian: ' . $siswa['nama_siswa'],
            'asesmen'     => $asesmen,
            'siswa'       => $siswa,
            'jawabanList' => $jawabanList,
        ]);
    }

    /**
     * Simpan Skor Essay yang Diberikan Guru
     */
    public function simpanNilaiEssay($asesmenId, $siswaId)
    {
        $guruId = $this->getGuruId();
        $asesmen = $this->asesmenModel->find((int)$asesmenId);

        if (! $asesmen || ($this->session->get('role') !== 'admin' && (int)$asesmen['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Asesmen tidak ditemukan.');
        }

        $skorData = $this->request->getPost('skor') ?: [];
        $catatan  = $this->request->getPost('catatan') ?: [];

        foreach ($skorData as $jawabanId => $nilai) {
            $this->asesmenJawabanSiswaModel->update((int)$jawabanId, [
                'skor'         => (float)$nilai,
                'catatan_guru' => $catatan[$jawabanId] ?? null,
            ]);
        }

        return redirect()->to("/lms/asesmen/{$asesmenId}")->with('success', 'Nilai essay siswa berhasil disimpan!');
    }

    // =========================================================================
    // MODUL SISWA (PUBLIC LIVE QUIZ - TANPA LOGIN PASSWORD)
    // =========================================================================

    /**
     * Halaman Siswa Masuk Asesmen Live via Token/QR Code
     */
    public function liveQuiz($token)
    {
        $asesmen = $this->asesmenModel->getDetailByToken($token);

        if (! $asesmen) {
            return '<div style="text-align:center;padding:50px;font-family:sans-serif;"><h3>Asesmen tidak ditemukan atau tautan salah.</h3></div>';
        }

        if ($asesmen['status'] === 'ditutup') {
            return '<div style="text-align:center;padding:50px;font-family:sans-serif;"><h3>Asesmen ini telah ditutup oleh Guru.</h3></div>';
        }

        // Siswa aktif di kelas ini
        $siswaList = $this->siswaModel->getSiswaAktifByKelas((int)$asesmen['kelas_id']);

        return view('lms/live_quiz_gate', [
            'title'     => 'Kuis Live: ' . $asesmen['judul_asesmen'],
            'asesmen'   => $asesmen,
            'siswaList' => $siswaList,
        ]);
    }

    /**
     * Lembar Pengerjaan Soal Siswa
     */
    public function kerjakanQuiz($token)
    {
        $asesmen = $this->asesmenModel->getDetailByToken($token);
        $siswaId = (int)$this->request->getGet('siswa_id');

        if (! $asesmen || $siswaId <= 0) {
            return redirect()->to("/live-quiz/{$token}");
        }

        $siswa = $this->siswaModel->find($siswaId);
        $soalList = $this->asesmenSoalModel->getSoalByAsesmen((int)$asesmen['id']);

        // Cek apakah sudah pernah mengerjakan
        $sudahKerja = $this->asesmenJawabanSiswaModel
            ->where('asesmen_id', (int)$asesmen['id'])
            ->where('siswa_id', $siswaId)
            ->first();

        return view('lms/live_quiz_lembar', [
            'title'      => 'Mengerjakan: ' . $asesmen['judul_asesmen'],
            'asesmen'    => $asesmen,
            'siswa'      => $siswa,
            'soalList'   => $soalList,
            'sudahKerja' => !empty($sudahKerja),
        ]);
    }

    /**
     * Submit Jawaban Siswa & Auto-grading PG
     */
    public function submitQuiz($token)
    {
        $asesmen = $this->asesmenModel->getDetailByToken($token);
        $siswaId = (int)$this->request->getPost('siswa_id');

        if (! $asesmen || $siswaId <= 0) {
            return redirect()->to("/live-quiz/{$token}");
        }

        $jawabanPg    = $this->request->getPost('pg') ?: [];
        $jawabanEssay = $this->request->getPost('essay') ?: [];

        $soalList = $this->asesmenSoalModel->getSoalByAsesmen((int)$asesmen['id']);

        foreach ($soalList as $soal) {
            $soalId = (int)$soal['id'];

            if ($soal['tipe_soal'] === 'pg') {
                $jawab = strtolower(trim((string)($jawabanPg[$soalId] ?? '')));
                $isBenar = (!empty($jawab) && $jawab === strtolower(trim((string)$soal['kunci_pg']))) ? 1 : 0;
                $skor = $isBenar ? (float)$soal['bobot'] : 0.00;

                $this->asesmenJawabanSiswaModel->insert([
                    'asesmen_id' => (int)$asesmen['id'],
                    'soal_id'    => $soalId,
                    'siswa_id'   => $siswaId,
                    'jawaban_pg' => $jawab,
                    'is_benar'   => $isBenar,
                    'skor'       => $skor,
                ]);
            } else {
                // Essay
                $jawabE = trim((string)($jawabanEssay[$soalId] ?? ''));
                $this->asesmenJawabanSiswaModel->insert([
                    'asesmen_id'    => (int)$asesmen['id'],
                    'soal_id'       => $soalId,
                    'siswa_id'      => $siswaId,
                    'jawaban_essay' => $jawabE,
                    'is_benar'      => null, // Menunggu koreksi guru
                    'skor'          => 0.00,
                ]);
            }
        }

        return redirect()->to("/live-quiz/{$token}/selesai?siswa_id={$siswaId}");
    }

    /**
     * Halaman Selesai Kuis Siswa
     */
    public function selesaiQuiz($token)
    {
        $asesmen = $this->asesmenModel->getDetailByToken($token);
        $siswaId = (int)$this->request->getGet('siswa_id');
        $siswa   = $this->siswaModel->find($siswaId);

        // Hitung total skor PG
        $db = \Config\Database::connect();
        $hasil = $db->query("
            SELECT 
                COUNT(*) as total_soal,
                SUM(is_benar) as pg_benar,
                SUM(skor) as total_skor
            FROM asesmen_jawaban_siswa
            WHERE asesmen_id = ? AND siswa_id = ?
        ", [(int)$asesmen['id'], $siswaId])->getRowArray();

        return view('lms/live_quiz_selesai', [
            'title'   => 'Selesai - ' . $asesmen['judul_asesmen'],
            'asesmen' => $asesmen,
            'siswa'   => $siswa,
            'hasil'   => $hasil,
        ]);
    }

    // =======================================================================
    // MODUL LKPD (LEMBAR KERJA PESERTA DIDIK) KELOMPOK
    // =======================================================================

    /**
     * Buat LKPD Baru pada Sesi KBM
     */
    public function simpanLkpd($sesiId)
    {
        $guruId = $this->getGuruId();
        $sesi   = $this->kbmSesiModel->find((int)$sesiId);

        if (! $sesi || ($this->session->get('role') !== 'admin' && (int)$sesi['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'Sesi KBM tidak valid.');
        }

        $judulLkpd      = trim((string)$this->request->getPost('judul_lkpd'));
        $petunjuk       = trim((string)$this->request->getPost('petunjuk'));
        $metodeKelompok = $this->request->getPost('metode_kelompok') ?: 'mandiri_siswa';
        $tenggatWaktu   = $this->request->getPost('tenggat_waktu') ?: null;

        if (empty($judulLkpd)) {
            return redirect()->back()->with('error', 'Judul LKPD wajib diisi.');
        }

        // Upload file panduan jika ada
        $filePanduanName = null;
        $filePanduan = $this->request->getFile('file_panduan');
        if ($filePanduan && $filePanduan->isValid() && ! $filePanduan->hasMoved()) {
            $filePanduanName = $filePanduan->getRandomName();
            $filePanduan->move(FCPATH . 'uploads/lkpd', $filePanduanName);
        }

        $tokenLkpd = bin2hex(random_bytes(16));

        $lkpdId = (int)$this->lkpdModel->insert([
            'sesi_id'         => (int)$sesiId,
            'guru_id'         => $guruId,
            'judul_lkpd'      => $judulLkpd,
            'petunjuk'        => $petunjuk,
            'file_panduan'    => $filePanduanName,
            'metode_kelompok' => $metodeKelompok,
            'tenggat_waktu'   => $tenggatWaktu,
            'token_lkpd'      => $tokenLkpd,
            'status'          => 'aktif',
        ]);

        return redirect()->to("/lms/lkpd/{$lkpdId}")->with('success', 'LKPD berhasil dibuat! Anda dapat membagi kelompok atau membagikan link ke siswa.');
    }

    /**
     * Halaman Detail / Manajemen LKPD Guru
     */
    public function detailLkpd($lkpdId)
    {
        $guruId = $this->getGuruId();
        $lkpd   = $this->lkpdModel
            ->select('lkpd.*, kbm_sesi.judul_materi, kbm_sesi.kelas_id, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kbm_sesi', 'kbm_sesi.id = lkpd.sesi_id')
            ->join('kelas', 'kelas.id = kbm_sesi.kelas_id')
            ->join('mapel', 'mapel.id = kbm_sesi.mapel_id')
            ->where('lkpd.id', (int)$lkpdId)
            ->first();

        if (! $lkpd || ($this->session->get('role') !== 'admin' && (int)$lkpd['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'LKPD tidak ditemukan.');
        }

        $kelompokList = $this->lkpdKelompokModel->getKelompokByLkpd((int)$lkpdId);
        $siswaKelas   = $this->siswaModel->getSiswaAktifByKelas((int)$lkpd['kelas_id']);

        // Data siswa yang sudah masuk kelompok
        $db = \Config\Database::connect();
        $assignedRows = $db->query("
            SELECT siswa_id FROM lkpd_anggota 
            JOIN lkpd_kelompok ON lkpd_kelompok.id = lkpd_anggota.kelompok_id
            WHERE lkpd_kelompok.lkpd_id = ?
        ", [(int)$lkpdId])->getResultArray();
        $assignedIds = array_column($assignedRows, 'siswa_id');

        $siswaBelumAdaKelompok = array_filter($siswaKelas, function ($s) use ($assignedIds) {
            return ! in_array($s['id'], $assignedIds);
        });

        return view('lms/detail_lkpd', [
            'title'                 => 'LKPD: ' . $lkpd['judul_lkpd'],
            'lkpd'                  => $lkpd,
            'kelompokList'          => $kelompokList,
            'siswaKelas'            => $siswaKelas,
            'siswaBelumAdaKelompok' => $siswaBelumAdaKelompok,
        ]);
    }

    /**
     * Guru Membagi Siswa ke Kelompok Secara Acak / Otomatis
     */
    public function bagiKelompokOtomatis($lkpdId)
    {
        $guruId = $this->getGuruId();
        $lkpd   = $this->lkpdModel->find((int)$lkpdId);

        if (! $lkpd || ($this->session->get('role') !== 'admin' && (int)$lkpd['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'LKPD tidak valid.');
        }

        $sesi = $this->kbmSesiModel->find($lkpd['sesi_id']);
        $siswaList = $this->siswaModel->getSiswaAktifByKelas((int)$sesi['kelas_id']);

        if (empty($siswaList)) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
        }

        $jumlahKelompok = (int)$this->request->getPost('jumlah_kelompok');
        if ($jumlahKelompok < 2) {
            $jumlahKelompok = 4;
        }

        // Hapus kelompok lama di LKPD ini
        $db = \Config\Database::connect();
        $oldKelompok = $this->lkpdKelompokModel->where('lkpd_id', (int)$lkpdId)->findAll();
        foreach ($oldKelompok as $ok) {
            $this->lkpdAnggotaModel->where('kelompok_id', $ok['id'])->delete();
            $this->lkpdJawabanModel->where('kelompok_id', $ok['id'])->delete();
        }
        $this->lkpdKelompokModel->where('lkpd_id', (int)$lkpdId)->delete();

        // Acak urutan siswa
        shuffle($siswaList);

        // Buat kelompok
        $kelompokIds = [];
        for ($i = 1; $i <= $jumlahKelompok; $i++) {
            $kelompokIds[$i] = (int)$this->lkpdKelompokModel->insert([
                'lkpd_id'        => (int)$lkpdId,
                'nama_kelompok'  => 'Kelompok ' . $i,
                'nomor_kelompok' => $i,
            ]);
        }

        // Bagikan siswa secara merata (round-robin)
        $idx = 1;
        $ketuaAssigned = [];
        foreach ($siswaList as $s) {
            $kId = $kelompokIds[$idx];
            $isKetua = ! isset($ketuaAssigned[$idx]);
            $peran = $isKetua ? 'Ketua' : 'Anggota';

            if ($isKetua) {
                $ketuaAssigned[$idx] = $s['id'];
                $this->lkpdKelompokModel->update($kId, ['ketua_siswa_id' => $s['id']]);
            }

            $this->lkpdAnggotaModel->insert([
                'kelompok_id' => $kId,
                'siswa_id'    => $s['id'],
                'peran'       => $peran,
            ]);

            $idx++;
            if ($idx > $jumlahKelompok) {
                $idx = 1;
            }
        }

        return redirect()->back()->with('success', "Berhasil membagi {$jumlahKelompok} kelompok secara acak dan merata!");
    }

    /**
     * Tambah Kelompok Manual oleh Guru
     */
    public function tambahKelompokManual($lkpdId)
    {
        $guruId = $this->getGuruId();
        $lkpd   = $this->lkpdModel->find((int)$lkpdId);

        if (! $lkpd || ($this->session->get('role') !== 'admin' && (int)$lkpd['guru_id'] !== $guruId)) {
            return redirect()->to('/lms')->with('error', 'LKPD tidak valid.');
        }

        $namaKelompok   = trim((string)$this->request->getPost('nama_kelompok'));
        $ketuaSiswaId   = (int)$this->request->getPost('ketua_siswa_id');
        $anggotaSiswaIds = (array)$this->request->getPost('anggota_siswa_ids');

        if (empty($namaKelompok)) {
            return redirect()->back()->with('error', 'Nama kelompok wajib diisi.');
        }

        $maxNomor = $this->lkpdKelompokModel->where('lkpd_id', (int)$lkpdId)->selectMax('nomor_kelompok')->first();
        $nomor = ($maxNomor['nomor_kelompok'] ?? 0) + 1;

        $kelompokId = (int)$this->lkpdKelompokModel->insert([
            'lkpd_id'        => (int)$lkpdId,
            'nama_kelompok'  => $namaKelompok,
            'nomor_kelompok' => $nomor,
            'ketua_siswa_id' => $ketuaSiswaId ?: null,
        ]);

        if ($ketuaSiswaId > 0) {
            $this->lkpdAnggotaModel->insert([
                'kelompok_id' => $kelompokId,
                'siswa_id'    => $ketuaSiswaId,
                'peran'       => 'Ketua',
            ]);
        }

        foreach ($anggotaSiswaIds as $sId) {
            $sId = (int)$sId;
            if ($sId > 0 && $sId !== $ketuaSiswaId) {
                $this->lkpdAnggotaModel->insert([
                    'kelompok_id' => $kelompokId,
                    'siswa_id'    => $sId,
                    'peran'       => 'Anggota',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Kelompok baru berhasil ditambahkan!');
    }

    /**
     * Guru Menyimpan Nilai & Feedback Kelompok LKPD
     */
    public function simpanNilaiLkpd($lkpdId)
    {
        $guruId     = $this->getGuruId();
        $kelompokId = (int)$this->request->getPost('kelompok_id');
        $skor       = (float)$this->request->getPost('skor_kelompok');
        $feedback   = trim((string)$this->request->getPost('feedback_guru'));

        $jawaban = $this->lkpdJawabanModel->where('kelompok_id', $kelompokId)->first();
        if ($jawaban) {
            $this->lkpdJawabanModel->update($jawaban['id'], [
                'skor_kelompok'      => $skor,
                'feedback_guru'      => $feedback,
                'status_pengumpulan' => 'dinilai',
            ]);
        } else {
            $this->lkpdJawabanModel->insert([
                'kelompok_id'        => $kelompokId,
                'lkpd_id'            => (int)$lkpdId,
                'skor_kelompok'      => $skor,
                'feedback_guru'      => $feedback,
                'status_pengumpulan' => 'dinilai',
            ]);
        }

        // Simpan nilai individu jika ada
        $nilaiIndividu = (array)$this->request->getPost('nilai_individu');
        foreach ($nilaiIndividu as $anggotaId => $nilai) {
            if ($nilai !== '' && $nilai !== null) {
                $this->lkpdAnggotaModel->update((int)$anggotaId, [
                    'nilai_individu' => (float)$nilai,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Penilaian dan feedback LKPD kelompok berhasil disimpan!');
    }

    /**
     * Buka / Tutup Pengumpulan LKPD
     */
    public function toggleStatusLkpd($lkpdId)
    {
        $lkpd = $this->lkpdModel->find((int)$lkpdId);
        if ($lkpd) {
            $newStatus = $lkpd['status'] === 'aktif' ? 'ditutup' : 'aktif';
            $this->lkpdModel->update($lkpd['id'], ['status' => $newStatus]);
        }
        return redirect()->back()->with('success', 'Status LKPD berhasil diperbarui.');
    }

    /**
     * Hapus LKPD
     */
    public function hapusLkpd($lkpdId)
    {
        $lkpd = $this->lkpdModel->find((int)$lkpdId);
        if (! $lkpd) {
            return redirect()->to('/lms')->with('error', 'LKPD tidak ditemukan.');
        }

        $sesiId = $lkpd['sesi_id'];
        $kelompokList = $this->lkpdKelompokModel->where('lkpd_id', (int)$lkpdId)->findAll();
        foreach ($kelompokList as $k) {
            $this->lkpdAnggotaModel->where('kelompok_id', $k['id'])->delete();
            $this->lkpdJawabanModel->where('kelompok_id', $k['id'])->delete();
        }
        $this->lkpdKelompokModel->where('lkpd_id', (int)$lkpdId)->delete();
        $this->lkpdModel->delete($lkpdId);

        return redirect()->to("/lms/sesi/{$sesiId}")->with('success', 'LKPD berhasil dihapus.');
    }

    // =======================================================================
    // PENGERJAAN LKPD OLEH SISWA (PUBLIK / LIVE LINK TANPA PASSWORD)
    // =======================================================================

    /**
     * Halaman Siswa: Pintu Masuk / Lembar Kerja LKPD Kelompok
     */
    public function liveLkpd($token)
    {
        $lkpd = $this->lkpdModel->getDetailByToken($token);
        if (! $lkpd) {
            return redirect()->to('/')->with('error', 'Tautan LKPD tidak valid atau sudah kedaluwarsa.');
        }

        $siswaId = (int)$this->request->getGet('siswa_id');
        $siswaKelas = $this->siswaModel->getSiswaAktifByKelas((int)$lkpd['kelas_id']);

        // Ambil semua kelompok pada LKPD ini
        $kelompokList = $this->lkpdKelompokModel->getKelompokByLkpd((int)$lkpd['id']);

        // Cek apakah siswa saat ini sudah tergabung dalam suatu kelompok
        $kelompokSaya = null;
        if ($siswaId > 0) {
            foreach ($kelompokList as $k) {
                foreach ($k['anggota'] as $ang) {
                    if ((int)$ang['siswa_id'] === $siswaId) {
                        $kelompokSaya = $k;
                        break 2;
                    }
                }
            }
        }

        $siswaTerpilih = $siswaId > 0 ? $this->siswaModel->find($siswaId) : null;

        return view('lms/live_lkpd_lembar', [
            'title'         => 'LKPD: ' . $lkpd['judul_lkpd'],
            'lkpd'          => $lkpd,
            'siswaKelas'    => $siswaKelas,
            'siswaId'       => $siswaId,
            'siswaTerpilih' => $siswaTerpilih,
            'kelompokList'  => $kelompokList,
            'kelompokSaya'  => $kelompokSaya,
        ]);
    }

    /**
     * Siswa Bergabung ke Kelompok atau Membuat Kelompok Baru
     */
    public function gabungKelompokLkpd($token)
    {
        $lkpd = $this->lkpdModel->getDetailByToken($token);
        if (! $lkpd || $lkpd['status'] !== 'aktif') {
            return redirect()->back()->with('error', 'LKPD sudah ditutup atau tidak aktif.');
        }

        $siswaId    = (int)$this->request->getPost('siswa_id');
        $action     = $this->request->getPost('action'); // 'gabung' atau 'buat_baru'
        $kelompokId = (int)$this->request->getPost('kelompok_id');
        $namaBaru   = trim((string)$this->request->getPost('nama_kelompok_baru'));
        $peran      = trim((string)$this->request->getPost('peran')) ?: 'Anggota';

        if ($siswaId <= 0) {
            return redirect()->back()->with('error', 'Silakan pilih identitas Anda terlebih dahulu.');
        }

        // Cek apakah siswa sudah punya kelompok di LKPD ini
        $db = \Config\Database::connect();
        $existing = $db->query("
            SELECT lkpd_anggota.id, lkpd_anggota.kelompok_id 
            FROM lkpd_anggota 
            JOIN lkpd_kelompok ON lkpd_kelompok.id = lkpd_anggota.kelompok_id
            WHERE lkpd_kelompok.lkpd_id = ? AND lkpd_anggota.siswa_id = ?
        ", [(int)$lkpd['id'], $siswaId])->getRowArray();

        if ($existing) {
            return redirect()->to("/live-lkpd/{$token}?siswa_id={$siswaId}")->with('error', 'Anda sudah terdaftar dalam kelompok.');
        }

        if ($action === 'buat_baru') {
            if (empty($namaBaru)) {
                return redirect()->back()->with('error', 'Nama kelompok baru wajib diisi.');
            }
            $maxNomor = $this->lkpdKelompokModel->where('lkpd_id', (int)$lkpd['id'])->selectMax('nomor_kelompok')->first();
            $nomor = ($maxNomor['nomor_kelompok'] ?? 0) + 1;

            $kelompokId = (int)$this->lkpdKelompokModel->insert([
                'lkpd_id'        => (int)$lkpd['id'],
                'nama_kelompok'  => $namaBaru,
                'nomor_kelompok' => $nomor,
                'ketua_siswa_id' => $siswaId,
            ]);
            $peran = 'Ketua';
        }

        if ($kelompokId > 0) {
            $this->lkpdAnggotaModel->insert([
                'kelompok_id' => $kelompokId,
                'siswa_id'    => $siswaId,
                'peran'       => $peran,
            ]);
        }

        return redirect()->to("/live-lkpd/{$token}?siswa_id={$siswaId}")->with('success', 'Berhasil bergabung ke kelompok!');
    }

    /**
     * Siswa (Perwakilan Kelompok) Mengumpulkan Lembar Kerja LKPD
     */
    public function submitLkpdJawaban($token)
    {
        $lkpd = $this->lkpdModel->getDetailByToken($token);
        if (! $lkpd || $lkpd['status'] !== 'aktif') {
            return redirect()->back()->with('error', 'LKPD sudah ditutup untuk pengumpulan.');
        }

        $kelompokId    = (int)$this->request->getPost('kelompok_id');
        $siswaId       = (int)$this->request->getPost('siswa_id');
        $ringkasan     = trim((string)$this->request->getPost('ringkasan_kerja'));
        $linkEksternal = trim((string)$this->request->getPost('link_eksternal'));

        if ($kelompokId <= 0) {
            return redirect()->back()->with('error', 'Kelompok tidak valid.');
        }

        $jawaban = $this->lkpdJawabanModel->where('kelompok_id', $kelompokId)->first();
        $fileLampiranName = $jawaban['file_lampiran'] ?? null;

        $fileUpload = $this->request->getFile('file_lampiran');
        if ($fileUpload && $fileUpload->isValid() && ! $fileUpload->hasMoved()) {
            $fileLampiranName = $fileUpload->getRandomName();
            $fileUpload->move(FCPATH . 'uploads/lkpd', $fileLampiranName);
        }

        $dataSimpan = [
            'kelompok_id'        => $kelompokId,
            'lkpd_id'            => (int)$lkpd['id'],
            'ringkasan_kerja'    => $ringkasan,
            'link_eksternal'     => $linkEksternal,
            'file_lampiran'      => $fileLampiranName,
            'status_pengumpulan' => 'dikumpulkan',
            'waktu_kumpul'       => date('Y-m-d H:i:s'),
        ];

        if ($jawaban) {
            $this->lkpdJawabanModel->update($jawaban['id'], $dataSimpan);
        } else {
            $this->lkpdJawabanModel->insert($dataSimpan);
        }

        return redirect()->to("/live-lkpd/{$token}?siswa_id={$siswaId}")->with('success', 'Lembar kerja kelompok berhasil dikumpulkan! Guru akan memeriksa dan menilai.');
    }
}

