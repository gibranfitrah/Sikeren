<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpatiApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\User;
use Illuminate\Support\Facades\DB;

class SimpatiController extends Controller
{
    protected SimpatiApiService $simpatiService;

    public function __construct(SimpatiApiService $simpatiService)
    {
        $this->middleware('auth');
        $this->simpatiService = $simpatiService;
    }

    /**
     * Pastikan hanya Admin atau Ketua Tim/PJ resmi yang dapat mengakses Integrasi SIMPATI
     */
    protected function authorizeAccess()
    {
        $user = Auth::user();
        if (!$user || !$user->canAccessSimpati()) {
            abort(403, 'Akses terbatas! Menu Integrasi SIMPATI hanya dapat diakses oleh Administrator dan Ketua Tim / PJ yang ditunjuk.');
        }
    }

    /**
     * Halaman manajemen Integrasi SIMPATI API
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $baseUrl = config('services.simpati.base_url', 'http://127.0.0.1:3000');
        $apiKey  = config('services.simpati.api_key', 'si-ke-ren74_K9xM2pL8vR5wQ1zY4tN7bC0jF3hG6dS8aE1uW4iO9qX2zV5mP0');

        // Daftar Satker Resmi BPS se-Sulawesi Tenggara
        $satkerMap = self::getSatkerMap();
        $satkers = ['all' => 'Semua Satker BPS'];
        foreach ($satkerMap as $kode => $nama) {
            $satkers[$kode] = "{$kode} - {$nama}";
        }

        $selectedSatker = $request->query('satker', '7400');

        $syncedPegawai = $this->getFilteredPegawai($selectedSatker, $satkerMap);

        // Mengambil seluruh Tim Kerja dari master_groups (Database Tim)
        $syncedTims = DB::table('master_groups')->get();
        foreach ($syncedTims as $tim) {
            $tim->members = DB::table('groups')
                ->join('users', 'groups.niplama', '=', 'users.niplama')
                ->where('groups.grup', $tim->grup)
                ->select('users.nama_lengkap', 'users.niplama', 'users.id')
                ->get();
        }

        // Statistik
        $totalPegawai     = $syncedPegawai->count();
        $totalPindah      = $syncedPegawai->where('is_pindahsatker', 1)->count();
        $totalMultiTim    = $syncedPegawai->filter(fn($p) => count($p->tims) > 1)->count();
        $totalTimKerja    = $syncedTims->count();

        // Siapkan data siap-JSON untuk Alpine.js.
        // Jangan lakukan map() + closure di dalam @json Blade karena
        // compiler Blade gagal mengurai kurung/siku bersarang dan
        // menimbulkan ParseError "Unclosed '[' ... does not match ')'".
        $pegawaiList = $syncedPegawai->map(function ($p) {
            return [
                'id'              => $p->id,
                'nama_lengkap'    => $p->nama_lengkap,
                'formatted_nip'   => $p->formatted_nip,
                'email'           => $p->email,
                'nm_jabatan'      => $p->nm_jabatan ?: 'Pegawai BPS',
                'nm_satker'       => $p->nm_satker,
                'id_satker'       => $p->id_satker,
                'is_pindahsatker' => (int) ($p->is_pindahsatker ?? 0),
                'tims'            => array_values($p->tims ?? []),
                'initial'         => strtoupper(substr($p->nama_lengkap ?? 'U', 0, 1)),
            ];
        })->values()->toArray();

        // Database Kegiatan & Projek untuk monitoring penugasan 1 PJ per kegiatan
        $kegiatanList = \App\Task::orderBy('created_at', 'desc')->take(100)->get();

        return view('admin.simpati.index', compact(
            'baseUrl',
            'apiKey',
            'satkers',
            'selectedSatker',
            'syncedPegawai',
            'syncedTims',
            'pegawaiList',
            'kegiatanList',
            'totalPegawai',
            'totalPindah',
            'totalMultiTim',
            'totalTimKerja'
        ));
    }

    /**
     * Query pegawai tersinkronisasi sesuai filter satker (dipakai index & filter AJAX).
     */
    protected function getFilteredPegawai(string $selectedSatker, array $satkerMap)
    {
        $pegawaiQuery = User::bpsStaff()
            ->leftJoin(DB::raw('(SELECT uj1.* FROM users_jabatan uj1 INNER JOIN (SELECT id_users, MAX(id) as max_id FROM users_jabatan GROUP BY id_users) uj2 ON uj1.id = uj2.max_id) as latest_jabatan'), 'users.id', '=', 'latest_jabatan.id_users')
            ->select(
                'users.id',
                'users.nama_lengkap',
                'users.niplama',
                'users.nipbaru',
                'users.email',
                'users.username',
                'users.token_id',
                'latest_jabatan.nm_jabatan',
                'latest_jabatan.id_satker',
                'latest_jabatan.is_pindahsatker'
            );

        if ($selectedSatker !== 'all') {
            $pegawaiQuery->where('latest_jabatan.id_satker', $selectedSatker);
        }

        $syncedPegawai = $pegawaiQuery->orderBy('users.nama_lengkap', 'asc')->get();

        // Mengisi data tim & nama satker masing-masing pegawai (Mendukung Multi-Tim)
        foreach ($syncedPegawai as $pegawai) {
            $pegawai->nm_satker = $satkerMap[$pegawai->id_satker] ?? ($pegawai->id_satker ? "Satker {$pegawai->id_satker}" : 'BPS Provinsi Sulawesi Tenggara');
            $pegawai->tims = DB::table('groups')
                ->where('niplama', $pegawai->niplama)
                ->pluck('grup')
                ->toArray();
        }

        return $syncedPegawai;
    }

    /**
     * AJAX Filter Satker tanpa reload (untuk penerapan smooth di halaman SIMPATI).
     * GET /admin/simpati/filter?satker=7471
     */
    public function filter(Request $request)
    {
        $this->authorizeAccess();

        $satkerMap = self::getSatkerMap();
        $selectedSatker = $request->query('satker', $request->input('satker', '7400'));

        // Validasi: 'all' atau kode satker resmi
        if ($selectedSatker !== 'all' && !array_key_exists($selectedSatker, $satkerMap)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode satker tidak dikenal.',
            ], 422);
        }

        $syncedPegawai = $this->getFilteredPegawai($selectedSatker, $satkerMap);
        $totalTimKerja = DB::table('master_groups')->count();

        $pegawai = $syncedPegawai->map(function ($p) {
            return [
                'id'              => $p->id,
                'nama_lengkap'    => $p->nama_lengkap,
                'formatted_nip'   => $p->formatted_nip,
                'email'           => $p->email,
                'nm_jabatan'      => $p->nm_jabatan ?: 'Pegawai BPS',
                'nm_satker'       => $p->nm_satker,
                'id_satker'       => $p->id_satker,
                'is_pindahsatker' => (int) ($p->is_pindahsatker ?? 0),
                'tims'            => array_values($p->tims ?? []),
                'initial'         => strtoupper(substr($p->nama_lengkap ?? 'U', 0, 1)),
            ];
        })->values();

        return response()->json([
            'success'         => true,
            'selected_satker' => $selectedSatker,
            'satker_label'    => $selectedSatker === 'all'
                ? 'Semua Satker BPS'
                : ($selectedSatker . ' - ' . ($satkerMap[$selectedSatker] ?? '')),
            'stats' => [
                'totalPegawai'  => $syncedPegawai->count(),
                'totalPindah'   => $syncedPegawai->where('is_pindahsatker', 1)->count(),
                'totalMultiTim' => $syncedPegawai->filter(fn($p) => count($p->tims ?? []) > 1)->count(),
                'totalTimKerja' => $totalTimKerja,
            ],
            'pegawai' => $pegawai,
        ]);
    }

    /**
     * AJAX Simpan Konfigurasi Base URL & API Key ke .env
     */
    public function updateConfig(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'base_url' => 'required|string',
            'api_key'  => 'required|string',
        ]);

        $baseUrl = rtrim($request->input('base_url'), '/');
        $apiKey  = trim($request->input('api_key'));

        // Update .env file
        $envUpdated = $this->updateEnvFile([
            'SIMPATI_API_BASE_URL' => $baseUrl,
            'SIMPATI_API_KEY'      => $apiKey,
        ]);

        config(['services.simpati.base_url' => $baseUrl]);
        config(['services.simpati.api_key'  => $apiKey]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan SIMPATI API berhasil disimpan ke konfigurasi sistem (.env).',
            'base_url' => $baseUrl,
            'api_key'  => $apiKey,
        ]);
    }

    /**
     * AJAX Test Koneksi ke SIMPATI API
     */
    public function testConnection(Request $request)
    {
        $this->authorizeAccess();

        if ($request->filled('base_url') && $request->filled('api_key')) {
            $this->simpatiService->setConfig(
                $request->input('base_url'),
                $request->input('api_key')
            );
        }

        $result = $this->simpatiService->testConnection();
        return response()->json($result);
    }

    /**
     * AJAX Trigger Sinkronisasi Data SIMPATI -> SIKEREN (Mendukung Filter SATKER)
     */
    public function syncData(Request $request)
    {
        $this->authorizeAccess();

        if ($request->filled('base_url') && $request->filled('api_key')) {
            $this->simpatiService->setConfig(
                $request->input('base_url'),
                $request->input('api_key')
            );
        }

        $idSatker = $request->input('id_satker');
        $result   = $this->simpatiService->syncAll($idSatker);
        return response()->json($result);
    }

    /**
     * AJAX Sinkronisasi Simulasi / Mock Data SIMPATI (Mendukung Filter SATKER)
     */
    public function syncMockData(Request $request)
    {
        $this->authorizeAccess();

        $idSatker = $request->input('id_satker');
        $result   = $this->simpatiService->syncDummyData($idSatker);
        return response()->json($result);
    }

    /**
     * AJAX Generate QR Nametag Pegawai SIMPATI
     */
    public function qrNametag($id)
    {
        $this->authorizeAccess();

        $user = User::findOrFail($id);
        $qrSvg = $this->simpatiService->generateQrCodeString($user, 220);

        // Ambil data jabatan & satker
        $jabatan = DB::table('users_jabatan')
            ->where('id_users', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        // Ambil daftar tim
        $tims = DB::table('groups')->where('niplama', $user->niplama)->pluck('grup')->toArray();

        return response()->json([
            'success'      => true,
            'user'         => [
                'id'            => $user->id,
                'nama_lengkap'  => $user->nama_lengkap,
                'niplama'       => $user->niplama,
                'nipbaru'       => $user->nipbaru ?: '-',
                'email'         => $user->email,
                'nm_jabatan'    => $jabatan->nm_jabatan ?? ($user->role_label ?: 'Pegawai BPS'),
                'id_satker'     => $jabatan->id_satker ?? '7400',
                'nm_satker'     => self::getSatkerMap()[$jabatan->id_satker ?? '7400'] ?? 'BPS Provinsi Sulawesi Tenggara',
                'tims'          => $tims,
                'pindah_satker' => ($jabatan->is_pindahsatker ?? 0) == 1,
            ],
            'qr_svg'       => $qrSvg,
        ]);
    }

    /**
     * Mengambil daftar pegawai dari SIMPATI API (Proxy JSON)
     */
    public function getPegawai(Request $request)
    {
        $this->authorizeAccess();

        try {
            $data = $this->simpatiService->getPegawai($request->query('id_satker'));
            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil daftar tim kerja dari SIMPATI API (Proxy JSON)
     */
    public function getTimKerja(Request $request)
    {
        $this->authorizeAccess();

        try {
            $data = $this->simpatiService->getTimKerja(
                $request->query('id_satker'),
                $request->query('nm_tim')
            );
            return response()->json(['status' => 'success', 'tims' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to write changes to .env file
     */
    protected function updateEnvFile(array $values): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);

        foreach ($values as $key => $value) {
            // Escape special chars if necessary
            $formatted = $value;
            if (str_contains($value, ' ') && !str_starts_with($value, '"')) {
                $formatted = '"' . $value . '"';
            }

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$formatted}", $content);
            } else {
                $content .= "\n{$key}={$formatted}";
            }
        }

        return (bool) file_put_contents($envPath, $content);
    }

    /**
     * Update data pegawai SIMPATI oleh Admin
     */
    public function updatePegawai(Request $request, $id)
    {
        $this->authorizeAccess();

        $user = User::findOrFail($id);
        
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'nm_jabatan'   => 'nullable|string|max:255',
            'id_satker'    => 'nullable|string|max:20',
        ]);

        $user->nama_lengkap = $request->input('nama_lengkap');
        if ($request->filled('email')) {
            $user->email = $request->input('email');
        }
        $user->save();

        // Update jabatan & satker
        $latestJabatan = DB::table('users_jabatan')
            ->where('id_users', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestJabatan) {
            DB::table('users_jabatan')
                ->where('id', $latestJabatan->id)
                ->update([
                    'nm_jabatan' => $request->input('nm_jabatan', $latestJabatan->nm_jabatan),
                    'id_satker'  => $request->input('id_satker', $latestJabatan->id_satker),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('users_jabatan')->insert([
                'id_users'   => $user->id,
                'nm_jabatan' => $request->input('nm_jabatan', 'Pegawai BPS'),
                'id_satker'  => $request->input('id_satker', '7400'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update tim kerja jika dipilih
        if ($request->filled('tim_utama')) {
            $timBaru = trim($request->input('tim_utama'));
            if (!empty($timBaru)) {
                DB::table('groups')->where('niplama', $user->niplama)->delete();
                DB::table('groups')->insert([
                    'niplama'    => $user->niplama,
                    'grup'       => $timBaru,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pegawai ' . $user->nama_lengkap . ' berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Data pegawai ' . $user->nama_lengkap . ' berhasil diperbarui.');
    }

    /**
     * Hapus data pegawai dari sistem SIKEREN oleh Admin
     */
    public function destroyPegawai(Request $request, $id)
    {
        $this->authorizeAccess();

        $user = User::findOrFail($id);
        $nama = $user->nama_lengkap;

        // Hapus relasi
        DB::table('users_jabatan')->where('id_users', $user->id)->delete();
        DB::table('groups')->where('niplama', $user->niplama)->delete();
        $user->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pegawai ' . $nama . ' berhasil dihapus dari SIKEREN.',
            ]);
        }

        return redirect()->back()->with('success', 'Pegawai ' . $nama . ' berhasil dihapus.');
    }

    /**
     * Peta Satuan Kerja BPS se-Sulawesi Tenggara
     */
    public static function getSatkerMap(): array
    {
        return [
            '7400' => 'BPS Provinsi Sulawesi Tenggara',
            '7401' => 'BPS Kabupaten Buton',
            '7402' => 'BPS Kabupaten Muna',
            '7403' => 'BPS Kabupaten Konawe',
            '7404' => 'BPS Kabupaten Kolaka',
            '7405' => 'BPS Kabupaten Konawe Selatan',
            '7406' => 'BPS Kabupaten Bombana',
            '7407' => 'BPS Kabupaten Wakatobi',
            '7408' => 'BPS Kabupaten Kolaka Utara',
            '7409' => 'BPS Kabupaten Buton Utara',
            '7410' => 'BPS Kabupaten Konawe Utara',
            '7411' => 'BPS Kabupaten Kolaka Timur',
            '7415' => 'BPS Kabupaten Buton Selatan',
            '7471' => 'BPS Kota Kendari',
            '7472' => 'BPS Kota Baubau',
        ];
    }
}
