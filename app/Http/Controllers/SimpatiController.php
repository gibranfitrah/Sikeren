<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpatiApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    public function index()
    {
        $this->authorizeAccess();

        $baseUrl = config('services.simpati.base_url', 'http://localhost:3000');
        $apiKey  = config('services.simpati.api_key', 'si-ke-ren74_K9xM2pL8vR5wQ1zY4tN7bC0jF3hG6dS8aE1uW4iO9qX2zV5mP0');

        return view('admin.simpati.index', compact('baseUrl', 'apiKey'));
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
     * AJAX Trigger Sinkronisasi Data SIMPATI -> SIKEREN
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

        $result = $this->simpatiService->syncAll();
        return response()->json($result);
    }

    /**
     * AJAX Sinkronisasi Simulasi / Mock Data SIMPATI (Untuk Demo / Testing)
     */
    public function syncMockData()
    {
        $this->authorizeAccess();

        $result = $this->simpatiService->syncDummyData();
        return response()->json($result);
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
}
