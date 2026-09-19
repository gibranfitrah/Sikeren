<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpatiApiService;
use Illuminate\Support\Facades\Auth;

class SimpatiController extends Controller
{
    protected SimpatiApiService $simpatiService;

    public function __construct(SimpatiApiService $simpatiService)
    {
        $this->middleware('auth');
        $this->simpatiService = $simpatiService;
    }

    /**
     * Halaman manajemen Integrasi SIMPATI API
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/dashboard')->with('error', 'Akses khusus Administrator.');
        }

        $baseUrl = config('services.simpati.base_url', 'http://localhost:3000');
        $apiKey  = config('services.simpati.api_key', '');

        return view('admin.simpati.index', compact('baseUrl', 'apiKey'));
    }

    /**
     * AJAX Test Koneksi ke SIMPATI API
     */
    public function testConnection()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->simpatiService->testConnection();
        return response()->json($result);
    }

    /**
     * AJAX Trigger Sinkronisasi Data SIMPATI -> SIKEREN
     */
    public function syncData()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->simpatiService->syncAll();
        return response()->json($result);
    }

    /**
     * Mengambil daftar pegawai dari SIMPATI API (Proxy JSON)
     */
    public function getPegawai(Request $request)
    {
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
}
