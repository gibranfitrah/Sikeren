<?php

namespace App\Services;

use App\User;
use App\users_jabatan;
use App\group;
use App\master_group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SimpatiApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.simpati.base_url', 'http://localhost:3000'), '/');
        $this->apiKey  = config('services.simpati.api_key', 'si-ke-ren74_K9xM2pL8vR5wQ1zY4tN7bC0jF3hG6dS8aE1uW4iO9qX2zV5mP0');
        $this->timeout = (int) config('services.simpati.timeout', 15);
    }

    /**
     * Get pre-configured HTTP client with SIMPATI API key header.
     */
    protected function client()
    {
        return Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'Accept'    => 'application/json',
        ])->timeout($this->timeout);
    }

    /**
     * Test connection to SIMPATI API.
     *
     * @return array
     */
    public function testConnection(): array
    {
        try {
            $url = "{$this->baseUrl}/api/public/pegawai";
            $response = $this->client()->get($url);

            if ($response->successful()) {
                $json = $response->json();
                $data = $json['data'] ?? [];
                return [
                    'success'    => true,
                    'status'     => $response->status(),
                    'message'    => 'Berhasil terhubung ke SIMPATI API.',
                    'data_count' => is_array($data) ? count($data) : 0,
                    'raw'        => $json,
                ];
            }

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => 'Gagal terhubung ke SIMPATI API: ' . ($response->json('error') ?? $response->body() ?? 'Status ' . $response->status()),
                'raw'     => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('SIMPATI API connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'status'  => 500,
                'message' => 'Koneksi error / SIMPATI API tidak dapat dijangkau: ' . $e->getMessage(),
                'raw'     => null,
            ];
        }
    }

    /**
     * 1) GET /api/public/pegawai
     * Mengambil daftar seluruh pegawai aktif dari SIMPATI.
     *
     * @param string|null $idSatker
     * @return array
     */
    public function getPegawai(?string $idSatker = null): array
    {
        $url = "{$this->baseUrl}/api/public/pegawai";
        $params = [];
        if ($idSatker !== null) {
            $params['id_satker'] = $idSatker;
        }

        $response = $this->client()->get($url, $params);

        if (!$response->successful()) {
            throw new Exception('Gagal mengambil data pegawai dari SIMPATI API: ' . ($response->json('error') ?? $response->body() ?? 'HTTP ' . $response->status()));
        }

        $json = $response->json();
        return $json['data'] ?? [];
    }

    /**
     * 2) GET /api/public/pegawai/{niplama}
     * Mengambil data satu pegawai beserta tim dari SIMPATI.
     *
     * @param string $niplama
     * @return array|null
     */
    public function getPegawaiByNip(string $niplama): ?array
    {
        $url = "{$this->baseUrl}/api/public/pegawai/" . urlencode($niplama);
        $response = $this->client()->get($url);

        if ($response->status() === 404) {
            return null;
        }

        if (!$response->successful()) {
            throw new Exception('Gagal mengambil data pegawai (' . $niplama . ') dari SIMPATI API: ' . ($response->json('error') ?? $response->body()));
        }

        $json = $response->json();
        return $json['data'] ?? null;
    }

    /**
     * 3) GET /api/public/tim-kerja
     * Mengambil daftar tim kerja beserta anggotanya dari SIMPATI.
     *
     * @param string|null $idSatker
     * @param string|null $nmTim
     * @return array
     */
    public function getTimKerja(?string $idSatker = null, ?string $nmTim = null): array
    {
        $url = "{$this->baseUrl}/api/public/tim-kerja";
        $params = [];
        if ($idSatker !== null) {
            $params['id_satker'] = $idSatker;
        }
        if ($nmTim !== null) {
            $params['nm_tim'] = $nmTim;
        }

        $response = $this->client()->get($url, $params);

        if (!$response->successful()) {
            throw new Exception('Gagal mengambil data tim kerja dari SIMPATI API: ' . ($response->json('error') ?? $response->body() ?? 'HTTP ' . $response->status()));
        }

        $json = $response->json();
        return $json['tims'] ?? [];
    }

    /**
     * Sinkronisasi seluruh data Pegawai dan Tim Kerja dari SIMPATI ke Database Sikeren.
     *
     * @return array Summary hasil sinkronisasi
     */
    public function syncAll(): array
    {
        $summary = [
            'success'          => false,
            'pegawai_created'  => 0,
            'pegawai_updated'  => 0,
            'pegawai_total'    => 0,
            'tim_created'      => 0,
            'tim_total'        => 0,
            'anggota_synced'   => 0,
            'errors'           => [],
        ];

        try {
            // 1. Ambil data pegawai dari SIMPATI API
            $pegawaiList = $this->getPegawai();
            $summary['pegawai_total'] = count($pegawaiList);

            foreach ($pegawaiList as $item) {
                try {
                    $niplama = trim($item['niplama'] ?? '');
                    $nipbaru = trim($item['nipbaru'] ?? '');
                    $email   = trim($item['email'] ?? '');
                    $nama    = trim($item['nama_lengkap'] ?? '');
                    $jabatan = trim($item['nm_jabatan'] ?? '');
                    $idSatker = $item['id_satker'] ?? null;
                    $nmSatker = $item['nm_satker'] ?? null;

                    if (empty($niplama) && empty($email)) {
                        continue;
                    }

                    // Cari user yang sudah ada berdasarkan niplama, nipbaru, atau email
                    $user = null;
                    if (!empty($niplama)) {
                        $user = User::where('niplama', $niplama)->first();
                    }
                    if (!$user && !empty($nipbaru)) {
                        $user = User::where('nipbaru', $nipbaru)->first();
                    }
                    if (!$user && !empty($email)) {
                        $user = User::where('email', $email)->first();
                    }

                    if ($user) {
                        // Update existing user (jika bukan akun admin custom)
                        if (!$user->isAdmin()) {
                            $user->nama_lengkap = $nama ?: $user->nama_lengkap;
                            $user->niplama      = $niplama ?: $user->niplama;
                            $user->nipbaru      = $nipbaru ?: $user->nipbaru;
                            if (!empty($email)) {
                                $user->email = $email;
                            }
                            $user->save();
                            $summary['pegawai_updated']++;
                        }
                    } else {
                        // Create new user di Sikeren
                        $username = !empty($niplama) ? $niplama : (explode('@', $email)[0] ?? 'user_' . time());
                        
                        $user = new User();
                        $user->nama_lengkap = $nama;
                        $user->username     = $username;
                        $user->niplama      = $niplama;
                        $user->nipbaru      = $nipbaru;
                        $user->email        = $email ?: ($niplama . '@bps.go.id');
                        $user->password     = Hash::make($niplama ?: 'password');
                        $user->save();
                        $summary['pegawai_created']++;
                    }

                    // Update / insert users_jabatan
                    if ($user && (!empty($jabatan) || !empty($idSatker) || !empty($nmSatker))) {
                        $existingJabatan = DB::table('users_jabatan')
                            ->where('id_users', $user->id)
                            ->first();

                        $jabatanData = [
                            'id_users'   => $user->id,
                            'nm_jabatan' => $jabatan,
                            'id_satker'  => $idSatker,
                            'nm_satker'  => $nmSatker,
                            'updated_at' => now(),
                        ];

                        if ($existingJabatan) {
                            DB::table('users_jabatan')
                                ->where('id', $existingJabatan->id)
                                ->update($jabatanData);
                        } else {
                            $jabatanData['created_at'] = now();
                            DB::table('users_jabatan')->insert($jabatanData);
                        }
                    }
                } catch (Exception $e) {
                    $summary['errors'][] = "Error pegawai ({$item['nama_lengkap']}): " . $e->getMessage();
                }
            }

            // 2. Ambil data Tim Kerja beserta anggotanya dari SIMPATI API
            try {
                $timList = $this->getTimKerja();
                $summary['tim_total'] = count($timList);

                foreach ($timList as $tim) {
                    $nmTim = trim($tim['nm_tim'] ?? '');
                    if (empty($nmTim)) {
                        continue;
                    }

                    // Pastikan tim ada di master_groups
                    $masterGroup = master_group::firstOrCreate(
                        ['grup' => $nmTim]
                    );
                    if ($masterGroup->wasRecentlyCreated) {
                        $summary['tim_created']++;
                    }

                    // Sinkronisasi anggota tim
                    $anggotaList = $tim['anggota'] ?? [];
                    foreach ($anggotaList as $agt) {
                        $nipAgt = trim($agt['niplama'] ?? '');
                        if (empty($nipAgt)) {
                            continue;
                        }

                        // Periksa apakah relasi anggota dan tim sudah ada di tabel groups
                        $groupRel = DB::table('groups')
                            ->where('niplama', $nipAgt)
                            ->where('grup', $nmTim)
                            ->first();

                        if (!$groupRel) {
                            DB::table('groups')->insert([
                                'niplama'    => $nipAgt,
                                'grup'       => $nmTim,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $summary['anggota_synced']++;
                    }
                }
            } catch (Exception $e) {
                $summary['errors'][] = "Error tim kerja: " . $e->getMessage();
            }

            $summary['success'] = true;
        } catch (Exception $e) {
            $summary['success'] = false;
            $summary['errors'][] = 'Fatal sync error: ' . $e->getMessage();
            Log::error('SIMPATI full sync failed: ' . $e->getMessage());
        }

        return $summary;
    }
}
