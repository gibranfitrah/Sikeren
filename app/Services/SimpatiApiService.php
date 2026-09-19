<?php

namespace App\Services;

use App\User;
use App\users_jabatan;
use App\group;
use App\master_group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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

    public function setConfig(string $baseUrl, string $apiKey, int $timeout = 15): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey  = trim($apiKey);
        $this->timeout = $timeout;
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
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
     * Format friendly error message for users.
     */
    protected function formatErrorMessage(Exception $e, string $endpoint): string
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Failed to connect') || str_contains($msg, 'cURL error 7') || str_contains($msg, 'Connection refused')) {
            return "Tidak dapat terhubung ke server SIMPATI di ({$this->baseUrl}). Pastikan aplikasi SIMPATI sudah dijalankan di port tersebut atau periksa Base URL.";
        }
        if (str_contains($msg, 'timed out') || str_contains($msg, 'cURL error 28')) {
            return "Koneksi ke SIMPATI timeout setelah {$this->timeout} detik. Pastikan server SIMPATI tidak sedang mengalami kendala.";
        }
        if (str_contains($msg, '401') || str_contains($msg, 'Unauthorized')) {
            return "Akses ditolak (401 Unauthorized). Pastikan API Key valid dan memiliki hak akses 'read:pegawai'.";
        }
        if (str_contains($msg, '404') || str_contains($msg, 'Not Found')) {
            return "Endpoint {$endpoint} tidak ditemukan di server SIMPATI (404 Not Found).";
        }
        return "Error saat mengakses {$endpoint}: " . $msg;
    }

    /**
     * Test connection to SIMPATI API.
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

            $errMsg = $response->json('error') ?? $response->body() ?? 'HTTP ' . $response->status();
            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => 'SIMPATI API merespon: ' . $errMsg,
                'raw'     => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('SIMPATI API connection test error: ' . $e->getMessage());
            return [
                'success' => false,
                'status'  => 500,
                'message' => $this->formatErrorMessage($e, '/api/public/pegawai'),
                'raw'     => null,
            ];
        }
    }

    /**
     * 1) GET /api/public/pegawai
     */
    public function getPegawai(?string $idSatker = null): array
    {
        $url = "{$this->baseUrl}/api/public/pegawai";
        $params = [];
        if ($idSatker !== null) {
            $params['id_satker'] = $idSatker;
        }

        try {
            $response = $this->client()->get($url, $params);

            if (!$response->successful()) {
                $err = $response->json('error') ?? $response->body() ?? 'HTTP ' . $response->status();
                throw new Exception($err);
            }

            $json = $response->json();
            return $json['data'] ?? [];
        } catch (Exception $e) {
            throw new Exception($this->formatErrorMessage($e, '/api/public/pegawai'));
        }
    }

    /**
     * 2) GET /api/public/pegawai/{niplama}
     */
    public function getPegawaiByNip(string $niplama): ?array
    {
        $url = "{$this->baseUrl}/api/public/pegawai/" . urlencode($niplama);
        try {
            $response = $this->client()->get($url);

            if ($response->status() === 404) {
                return null;
            }

            if (!$response->successful()) {
                $err = $response->json('error') ?? $response->body() ?? 'HTTP ' . $response->status();
                throw new Exception($err);
            }

            $json = $response->json();
            return $json['data'] ?? null;
        } catch (Exception $e) {
            throw new Exception($this->formatErrorMessage($e, '/api/public/pegawai/' . $niplama));
        }
    }

    /**
     * 3) GET /api/public/tim-kerja
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

        try {
            $response = $this->client()->get($url, $params);

            if (!$response->successful()) {
                $err = $response->json('error') ?? $response->body() ?? 'HTTP ' . $response->status();
                throw new Exception($err);
            }

            $json = $response->json();
            return $json['tims'] ?? [];
        } catch (Exception $e) {
            throw new Exception($this->formatErrorMessage($e, '/api/public/tim-kerja'));
        }
    }

    /**
     * Core Sync Processing dari array data pegawai dan tim
     */
    public function syncFromPayload(array $pegawaiList, array $timList): array
    {
        $summary = [
            'success'          => false,
            'pegawai_created'  => 0,
            'pegawai_updated'  => 0,
            'pegawai_total'    => count($pegawaiList),
            'tim_created'      => 0,
            'tim_total'        => count($timList),
            'anggota_synced'   => 0,
            'errors'           => [],
        ];

        // Cache column lists to prevent SQL Column not found errors
        $userJabatanCols = Schema::getColumnListing('users_jabatan');
        $masterGroupCols = Schema::getColumnListing('master_groups');
        $groupCols       = Schema::getColumnListing('groups');

        // 1. Sinkronisasi Data Pegawai -> users & users_jabatan
        foreach ($pegawaiList as $item) {
            try {
                $niplama  = trim($item['niplama'] ?? '');
                $nipbaru  = trim($item['nipbaru'] ?? '');
                $email    = trim($item['email'] ?? '');
                $nama     = trim($item['nama_lengkap'] ?? '');
                $jabatan  = trim($item['nm_jabatan'] ?? '');
                $idSatker = $item['id_satker'] ?? null;
                $nmSatker = $item['nm_satker'] ?? null;

                if (empty($niplama) && empty($email) && empty($nama)) {
                    continue;
                }

                // Cari user yang sudah ada
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
                    $username = !empty($niplama) ? $niplama : (explode('@', $email)[0] ?? 'user_' . time());
                    
                    $user = new User();
                    $user->nama_lengkap = $nama;
                    $user->username     = $username;
                    $user->niplama      = $niplama;
                    $user->nipbaru      = $nipbaru;
                    $user->email        = $email ?: ($niplama ? $niplama . '@bps.go.id' : 'user_' . time() . '@bps.go.id');
                    $user->password     = Hash::make($niplama ?: 'password');
                    $user->save();
                    $summary['pegawai_created']++;
                }

                // Update users_jabatan (hanya kolom yang ada di database)
                if ($user && (!empty($jabatan) || !empty($idSatker))) {
                    $jabatanData = [];
                    if (in_array('id_users', $userJabatanCols)) {
                        $jabatanData['id_users'] = $user->id;
                    }
                    if (in_array('nm_jabatan', $userJabatanCols)) {
                        $jabatanData['nm_jabatan'] = $jabatan;
                    }
                    if (in_array('id_satker', $userJabatanCols)) {
                        $jabatanData['id_satker'] = $idSatker;
                    }
                    if (in_array('nm_satker', $userJabatanCols)) {
                        $jabatanData['nm_satker'] = $nmSatker;
                    }
                    if (in_array('updated_at', $userJabatanCols)) {
                        $jabatanData['updated_at'] = now();
                    }

                    if (!empty($jabatanData)) {
                        $existingJabatan = DB::table('users_jabatan')
                            ->where('id_users', $user->id)
                            ->first();

                        if ($existingJabatan) {
                            DB::table('users_jabatan')
                                ->where('id', $existingJabatan->id)
                                ->update($jabatanData);
                        } else {
                            if (in_array('created_at', $userJabatanCols)) {
                                $jabatanData['created_at'] = now();
                            }
                            DB::table('users_jabatan')->insert($jabatanData);
                        }
                    }
                }
            } catch (Exception $e) {
                $summary['errors'][] = "Error data pegawai ({$item['nama_lengkap']}): " . $e->getMessage();
            }
        }

        // 2. Sinkronisasi Tim Kerja -> master_groups & groups
        foreach ($timList as $tim) {
            try {
                $nmTim = trim($tim['nm_tim'] ?? '');
                if (empty($nmTim)) {
                    continue;
                }

                // Cek apakah tim sudah ada di master_groups
                $existingGroup = DB::table('master_groups')->where('grup', $nmTim)->first();
                if (!$existingGroup) {
                    $insertGroup = ['grup' => $nmTim];
                    if (in_array('created_at', $masterGroupCols)) {
                        $insertGroup['created_at'] = now();
                    }
                    if (in_array('updated_at', $masterGroupCols)) {
                        $insertGroup['updated_at'] = now();
                    }
                    DB::table('master_groups')->insert($insertGroup);
                    $summary['tim_created']++;
                }

                $anggotaList = $tim['anggota'] ?? [];
                foreach ($anggotaList as $agt) {
                    $nipAgt = trim($agt['niplama'] ?? '');
                    if (empty($nipAgt)) {
                        continue;
                    }

                    $groupRel = DB::table('groups')
                        ->where('niplama', $nipAgt)
                        ->where('grup', $nmTim)
                        ->first();

                    if (!$groupRel) {
                        $insertAgt = [
                            'niplama' => $nipAgt,
                            'grup'    => $nmTim,
                        ];
                        if (in_array('created_at', $groupCols)) {
                            $insertAgt['created_at'] = now();
                        }
                        if (in_array('updated_at', $groupCols)) {
                            $insertAgt['updated_at'] = now();
                        }
                        DB::table('groups')->insert($insertAgt);
                    }
                    $summary['anggota_synced']++;
                }
            } catch (Exception $e) {
                $summary['errors'][] = "Error tim ({$tim['nm_tim']}): " . $e->getMessage();
            }
        }

        $summary['success'] = true;
        return $summary;
    }

    /**
     * Sinkronisasi live dari SIMPATI API ke database SIKEREN
     */
    public function syncAll(): array
    {
        try {
            $pegawaiList = $this->getPegawai();
            $timList     = $this->getTimKerja();

            return $this->syncFromPayload($pegawaiList, $timList);
        } catch (Exception $e) {
            Log::error('SIMPATI live sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => [$e->getMessage()],
            ];
        }
    }

    /**
     * Sinkronisasi data contoh / simulasi SIMPATI (Mock Data) untuk pengujian
     */
    public function syncDummyData(): array
    {
        $mockPegawai = [
            [
                'id'           => 1,
                'nama_lengkap' => 'A. Ranuwirawan Rahim, S.Si., M.Si.',
                'niplama'      => '19750101',
                'nipbaru'      => '197501011998031001',
                'email'        => 'ranuwirawan@bps.go.id',
                'nm_jabatan'   => 'Kepala Bagian Umum',
                'id_satker'    => '7400',
                'nm_satker'    => 'BPS Provinsi Sulawesi Tenggara',
            ],
            [
                'id'           => 2,
                'nama_lengkap' => 'Budi Santoso, S.Stat.',
                'niplama'      => '19880202',
                'nipbaru'      => '198802022010121002',
                'email'        => 'budi.santoso@bps.go.id',
                'nm_jabatan'   => 'Statistisi Ahli Madya / Ketua Tim IPDS',
                'id_satker'    => '7400',
                'nm_satker'    => 'BPS Provinsi Sulawesi Tenggara',
            ],
            [
                'id'           => 3,
                'nama_lengkap' => 'Siti Aminah, S.Tr.Stat.',
                'niplama'      => '19920303',
                'nipbaru'      => '199203032015022001',
                'email'        => 'siti.aminah@bps.go.id',
                'nm_jabatan'   => 'Statistisi Ahli Muda / Ketua Tim Nerwilis',
                'id_satker'    => '7400',
                'nm_satker'    => 'BPS Provinsi Sulawesi Tenggara',
            ],
            [
                'id'           => 4,
                'nama_lengkap' => 'Muhammad Gibran Fitrah, S.Kom.',
                'niplama'      => '19990404',
                'nipbaru'      => '199904042022011001',
                'email'        => 'gibran.fitrah@bps.go.id',
                'nm_jabatan'   => 'Pranata Komputer Ahli Pertama',
                'id_satker'    => '7400',
                'nm_satker'    => 'BPS Provinsi Sulawesi Tenggara',
            ],
            [
                'id'           => 5,
                'nama_lengkap' => 'Dewi Sartika, S.E.',
                'niplama'      => '19950505',
                'nipbaru'      => '199505052018012002',
                'email'        => 'dewi.sartika@bps.go.id',
                'nm_jabatan'   => 'Pranata Keuangan APBN',
                'id_satker'    => '7400',
                'nm_satker'    => 'BPS Provinsi Sulawesi Tenggara',
            ],
            [
                'id'           => 6,
                'nama_lengkap' => 'Ahmad Fauzi, S.Si.',
                'niplama'      => '19900606',
                'nipbaru'      => '199006062014031001',
                'email'        => 'ahmad.fauzi@bps.go.id',
                'nm_jabatan'   => 'Statistisi Ahli Pertama',
                'id_satker'    => '7400',
                'nm_satker'    => 'BPS Provinsi Sulawesi Tenggara',
            ]
        ];

        $mockTims = [
            [
                'id'        => 101,
                'id_satker' => '7400',
                'nm_satker' => 'BPS Provinsi Sulawesi Tenggara',
                'nm_tim'    => 'Integrasi Pengolahan dan Diseminasi Statistik (IPDS)',
                'deskripsi' => 'Pengelolaan infrastruktur TI, integrasi data, dan diseminasi informasi statistik',
                'anggota'   => [
                    [
                        'id'                => 2,
                        'nama_lengkap'      => 'Budi Santoso, S.Stat.',
                        'niplama'           => '19880202',
                        'nipbaru'           => '198802022010121002',
                        'email'             => 'budi.santoso@bps.go.id',
                        'nm_jabatan'        => 'Statistisi Ahli Madya',
                        'jabatan_dalam_tim' => 'Ketua Tim',
                    ],
                    [
                        'id'                => 4,
                        'nama_lengkap'      => 'Muhammad Gibran Fitrah, S.Kom.',
                        'niplama'           => '19990404',
                        'nipbaru'           => '199904042022011001',
                        'email'             => 'gibran.fitrah@bps.go.id',
                        'nm_jabatan'        => 'Pranata Komputer',
                        'jabatan_dalam_tim' => 'Anggota',
                    ],
                ],
            ],
            [
                'id'        => 102,
                'id_satker' => '7400',
                'nm_satker' => 'BPS Provinsi Sulawesi Tenggara',
                'nm_tim'    => 'Neraca Wilayah dan Analisis Statistik (Nerwilis)',
                'deskripsi' => 'Penyusunan PDRB dan analisis statistik makro ekonomi wilayah',
                'anggota'   => [
                    [
                        'id'                => 3,
                        'nama_lengkap'      => 'Siti Aminah, S.Tr.Stat.',
                        'niplama'           => '19920303',
                        'nipbaru'           => '199203032015022001',
                        'email'             => 'siti.aminah@bps.go.id',
                        'nm_jabatan'        => 'Statistisi Ahli Muda',
                        'jabatan_dalam_tim' => 'Ketua Tim',
                    ],
                    [
                        'id'                => 6,
                        'nama_lengkap'      => 'Ahmad Fauzi, S.Si.',
                        'niplama'           => '19900606',
                        'nipbaru'           => '199006062014031001',
                        'email'             => 'ahmad.fauzi@bps.go.id',
                        'nm_jabatan'        => 'Statistisi Ahli Pertama',
                        'jabatan_dalam_tim' => 'Anggota',
                    ],
                ],
            ],
        ];

        return $this->syncFromPayload($mockPegawai, $mockTims);
    }
}
