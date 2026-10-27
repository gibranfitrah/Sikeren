<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Task;
use App\penugasan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresensiMobileController extends Controller
{
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->bearerToken() ?: $request->header('X-Token') ?: $request->input('token');
        if ($token) {
            $user = User::where('token_id', $token)->first();
            if ($user) return $user;
        }

        if ($request->has('niplama')) {
            return User::where('niplama', $request->niplama)->first();
        }

        return null;
    }

    /**
     * Get attendance status & logs today for user
     */
    public function todayStatus(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid atau user tidak ditemukan.'], 401);
        }

        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');

        $logs = DB::table('presensis')
            ->where('niplama', $user->niplama)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $statusLabels = [
            1 => 'Masuk',
            2 => 'Keluar (Pribadi)',
            3 => 'Kembali',
            4 => 'Pulang',
            5 => 'Keluar (Dinas)',
            6 => 'Cuti',
            7 => 'Tugas Luar'
        ];

        $formattedLogs = $logs->map(function ($l) use ($statusLabels) {
            return [
                'id'         => $l->id ?? null,
                'status_id'  => (int)$l->status,
                'status_text'=> $statusLabels[$l->status] ?? 'Status ' . $l->status,
                'ket_dinas'  => $l->ket_dinas,
                'jam'        => Carbon::parse($l->created_at)->format('H:i:s'),
                'created_at' => $l->created_at
            ];
        });

        $latestLog = $logs->last();
        $latestStatus = $latestLog ? (int) $latestLog->status : 0;

        // Determine available actions based on latest status
        $allowedNextStatus = [];
        if ($latestStatus == 0) {
            $allowedNextStatus[] = ['status' => 1, 'label' => 'Presensi Masuk', 'color' => '#FF6B00'];
        } elseif ($latestStatus == 1 || $latestStatus == 3) {
            $allowedNextStatus[] = ['status' => 2, 'label' => 'Keluar (Pribadi)', 'color' => '#E65100'];
            $allowedNextStatus[] = ['status' => 5, 'label' => 'Keluar (Dinas)', 'color' => '#FB8C00'];
            $allowedNextStatus[] = ['status' => 4, 'label' => 'Presensi Pulang', 'color' => '#10B981'];
        } elseif ($latestStatus == 2 || $latestStatus == 5) {
            $allowedNextStatus[] = ['status' => 3, 'label' => 'Kembali Masuk', 'color' => '#FF6B00'];
        } elseif ($latestStatus == 4) {
            // Already checked out for the day
        }

        return response()->json([
            'success' => true,
            'today'   => Carbon::now('Asia/Singapore')->translatedFormat('l, d F Y'),
            'latest_status' => [
                'code'  => $latestStatus,
                'label' => $statusLabels[$latestStatus] ?? 'Belum Hadir',
                'jam'   => $latestLog ? Carbon::parse($latestLog->created_at)->format('H:i') : null,
            ],
            'allowed_actions' => $allowedNextStatus,
            'logs'            => $formattedLogs
        ]);
    }

    /**
     * Submit attendance via QR scan or status button
     */
    public function submitScan(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid atau user tidak ditemukan.'], 401);
        }

        $request->validate([
            'status' => 'required|integer',
            'qr_code' => 'nullable|string',
            'ket_dinas' => 'nullable|string'
        ]);

        $status = (int) $request->status;
        $qrCode = trim($request->input('qr_code') ?: '');
        $ketDinas = $request->input('ket_dinas');

        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');

        // 1. CEK APAKAH QR CODE ADALAH QR KEGIATAN / RAPAT
        $detectedTaskId = null;
        if (!empty($qrCode)) {
            if (preg_match('/(?:scan-qr|daftarhadir|qrcode)\/(\d+)/i', $qrCode, $matches)) {
                $detectedTaskId = (int) $matches[1];
            } elseif (preg_match('/(?:SIKEREN:PRESENSI|KEGIATAN):(\d+)/i', $qrCode, $matches)) {
                $detectedTaskId = (int) $matches[1];
            } elseif (is_numeric($qrCode) && strlen($qrCode) <= 6 && !str_starts_with($qrCode, '7400')) {
                $detectedTaskId = (int) $qrCode;
            }
        }
        if ($request->filled('id_kegiatan')) {
            $detectedTaskId = (int) $request->id_kegiatan;
        }

        if ($detectedTaskId) {
            $task = Task::find($detectedTaskId);
            if ($task) {
                // Pastikan terdaftar di penugasans
                $penugasan = penugasan::where('id_kegiatan', $task->id)
                    ->where(function($q) use ($user) {
                        $q->where('niplama', $user->niplama)
                          ->orWhere('peserta', $user->nama_lengkap);
                    })->first();

                if (!$penugasan) {
                    $penugasan = new penugasan();
                    $penugasan->id_kegiatan = $task->id;
                    $penugasan->niplama     = $user->niplama;
                    $penugasan->peserta     = $user->nama_lengkap;
                    $penugasan->save();
                }

                // Catat log presensi di tabel presensis
                $now = Carbon::now('Asia/Singapore');
                DB::table('presensis')->insert([
                    'niplama'    => $user->niplama,
                    'status'     => 1, // Hadir
                    'ket_dinas'  => 'Hadir ' . $task->jenis . ': ' . $task->text,
                    'created_at' => $now
                ]);

                return response()->json([
                    'success' => true,
                    'type'    => 'kegiatan',
                    'message' => 'Kehadiran pada ' . $task->jenis . ' "' . $task->text . '" berhasil dicatat sebagai HADIR!',
                    'data'    => [
                        'id'            => $task->id,
                        'judul'         => $task->text,
                        'jenis'         => $task->jenis,
                        'tempat'        => $task->tempat,
                        'status'        => 'Hadir',
                        'waktu_presensi'=> $now->format('H:i') . ' WITA'
                    ]
                ]);
            }
        }

        // 2. JIKA BUKAN QR KEGIATAN, PROSES PRESENSI KANTOR HARIAN (MASUK/PULANG/DINAS)
        // Check if status 1 (Masuk) already recorded today
        if ($status == 1) {
            $hasMasuk = DB::table('presensis')
                ->where('niplama', $user->niplama)
                ->where('status', 1)
                ->whereDate('created_at', $today)
                ->first();

            if ($hasMasuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan Presensi Masuk hari ini pada pukul ' . Carbon::parse($hasMasuk->created_at)->format('H:i') . ' WITA.'
                ], 400);
            }
        }

        // Check if QR code was provided
        if (!empty($qrCode)) {
            $qrExists = DB::table('kode_qrs')
                ->where('kode_qr', $qrCode)
                ->whereDate('created_at', $today)
                ->first();

            if (!$qrExists) {
                // Record new QR code without updated_at column
                $kodeSatker = substr($qrCode, -4) ?: '7400';
                DB::table('kode_qrs')->insert([
                    'kode_satker' => is_numeric($kodeSatker) ? (int)$kodeSatker : 7400,
                    'kode_qr'     => $qrCode,
                    'created_at'  => Carbon::now('Asia/Singapore')
                ]);
            }
        }

        // Insert attendance log without updated_at column
        $now = Carbon::now('Asia/Singapore');
        DB::table('presensis')->insert([
            'niplama'    => $user->niplama,
            'status'     => $status,
            'ket_dinas'  => $ketDinas,
            'created_at' => $now
        ]);

        $statusLabels = [
            1 => 'Presensi Masuk',
            2 => 'Keluar Pribadi',
            3 => 'Kembali',
            4 => 'Presensi Pulang',
            5 => 'Keluar Dinas'
        ];

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mencatat ' . ($statusLabels[$status] ?? 'presensi') . ' pada pukul ' . $now->format('H:i') . ' WITA.',
            'data'    => [
                'status'     => $status,
                'status_text'=> $statusLabels[$status] ?? 'Tercatat',
                'jam'        => $now->format('H:i'),
                'created_at' => $now->toDateTimeString()
            ]
        ]);
    }

    /**
     * Get or generate active QR Code for attendance
     */
    public function getActiveQr(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        $today = Carbon::now('Asia/Singapore')->format('Ymd');
        $todayDate = Carbon::now('Asia/Singapore')->format('Y-m-d');

        // Satker code
        $satker = 7400;

        // Check if today already has active QR
        $latest = DB::table('kode_qrs')
            ->where('kode_satker', $satker)
            ->whereDate('created_at', $todayDate)
            ->latest('id')
            ->first();

        if ($latest) {
            $qrData = $latest->kode_qr;
        } else {
            $qrData = 'BPS7400-' . $today . '-' . Str::random(6);
            DB::table('kode_qrs')->insert([
                'kode_satker' => $satker,
                'kode_qr'     => $qrData,
                'created_at'  => Carbon::now('Asia/Singapore')
            ]);
        }

        // Generate user's personal attendance barcode data
        $userBarcode = 'PRESENSI:' . ($user ? $user->niplama : '340019879') . ':' . $today;

        return response()->json([
            'success' => true,
            'office_qr' => [
                'code'        => $qrData,
                'office_name' => 'BPS Provinsi Sulawesi Tenggara',
                'location'    => 'Jl. Dr. Soetomo No. 1, Kendari',
                'valid_until' => Carbon::now('Asia/Singapore')->endOfDay()->format('H:i')
            ],
            'personal_qr' => [
                'code'        => $userBarcode,
                'niplama'     => $user ? $user->niplama : '',
                'nama'        => $user ? $user->nama_lengkap : ''
            ]
        ]);
    }

    /**
     * User's attendance history
     */
    public function history(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 401);
        }

        $month = $request->input('month', Carbon::now('Asia/Singapore')->format('m'));
        $year  = $request->input('year', Carbon::now('Asia/Singapore')->format('Y'));

        $records = DB::table('presensis')
            ->where('niplama', $user->niplama)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        $statusLabels = [
            1 => 'Masuk',
            2 => 'Keluar Pribadi',
            3 => 'Kembali',
            4 => 'Pulang',
            5 => 'Keluar Dinas',
            6 => 'Cuti',
            7 => 'Tugas Luar'
        ];

        // Group by date
        $grouped = [];
        foreach ($records as $r) {
            $date = Carbon::parse($r->created_at)->format('Y-m-d');
            if (!isset($grouped[$date])) {
                $cDate = Carbon::parse($date);
                $grouped[$date] = [
                    'date'         => $date,
                    'tanggal_text' => $cDate->translatedFormat('l, d M Y'),
                    'logs'         => []
                ];
            }
            $grouped[$date]['logs'][] = [
                'status'      => $r->status,
                'status_text' => $statusLabels[$r->status] ?? 'Status ' . $r->status,
                'jam'         => Carbon::parse($r->created_at)->format('H:i'),
                'ket_dinas'   => $r->ket_dinas
            ];
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_hadir'  => $records->where('status', 1)->count(),
                'total_dinas'  => $records->where('status', 5)->count(),
                'total_cuti'   => $records->where('status', 6)->count(),
            ],
            'history' => array_values($grouped)
        ]);
    }

    /**
     * Presensi Kehadiran Kegiatan atau Lapor Berhalangan
     * Memungkinkan pegawai mengisi daftar hadir kegiatan atau melapor berhalangan jauh-jauh hari
     */
    public function presensiKehadiranKegiatan(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 401);
        }

        $taskId = $request->input('task_id');
        $status = $request->input('status', 'Hadir'); // 'Hadir' atau 'Berhalangan'
        $alasan = $request->input('alasan');

        if (!$taskId) {
            return response()->json(['success' => false, 'message' => 'ID Kegiatan diperlukan.'], 422);
        }

        $task = Task::find($taskId);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Kegiatan tidak ditemukan.'], 404);
        }

        // Cari atau buat record di tabel penugasans
        $penugasan = penugasan::where('id_kegiatan', $task->id)
            ->where(function($q) use ($user) {
                $q->where('niplama', $user->niplama)
                  ->orWhere('peserta', 'like', '%' . $user->nama_lengkap . '%');
            })->first();

        if (!$penugasan) {
            $penugasan = new penugasan();
            $penugasan->id_kegiatan = $task->id;
            $penugasan->niplama     = $user->niplama;
            $penugasan->peserta     = $user->nama_lengkap;
        }

        $now = Carbon::now('Asia/Singapore');

        if ($status === 'Berhalangan') {
            $penugasan->status_kehadiran = 'Berhalangan';
            $penugasan->keterangan = $alasan ?: 'Berhalangan hadir (memerlukan pengganti)';
            $penugasan->save();

            // Kirim notifikasi ke Ketua Tim / Pimpinan bahwa ada peserta yang berhalangan
            if ($task->pemimpin) {
                $pemimpinUser = User::where('nama_lengkap', 'like', '%' . $task->pemimpin . '%')->first();
                if ($pemimpinUser) {
                    \App\Notification::create([
                        'niplama'    => $pemimpinUser->niplama,
                        'judul'      => "Peserta Berhalangan: {$task->text}",
                        'pesan'      => "{$user->nama_lengkap} melaporkan berhalangan hadir pada agenda '{$task->text}' dengan alasan: " . ($alasan ?: 'Tidak dapat hadir') . ". Slot peserta dapat digantikan.",
                        'link'       => "task:{$task->id}:pengganti",
                        'tipe'       => 'pergantian_peserta',
                        'is_read'    => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Laporan berhalangan pada '{$task->text}' berhasil disimpan. Penyelenggara telah diberi tahu untuk menyiapkan pengganti.",
                'data'    => [
                    'id_kegiatan'      => $task->id,
                    'status_kehadiran' => 'Berhalangan',
                    'keterangan'       => $penugasan->keterangan
                ]
            ]);
        } else {
            // Status Hadir
            $penugasan->status_kehadiran = 'Hadir';
            $penugasan->waktu_kehadiran = $now->format('H:i') . ' WITA';
            $penugasan->save();

            // Insert log presensi
            DB::table('presensis')->insert([
                'niplama'    => $user->niplama,
                'status'     => 1,
                'ket_dinas'  => 'Hadir ' . ($task->jenis ?: 'Kegiatan') . ': ' . $task->text,
                'created_at' => $now
            ]);

            return response()->json([
                'success' => true,
                'message' => "Kehadiran pada '{$task->text}' berhasil dicatat sebagai HADIR!",
                'data'    => [
                    'id_kegiatan'      => $task->id,
                    'status_kehadiran' => 'Hadir',
                    'waktu_presensi'   => $now->format('H:i') . ' WITA'
                ]
            ]);
        }
    }

    /**
     * Submit attendance directly for a specific Kegiatan / Rapat
     */
    public function presensiKegiatan(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 401);
        }

        $task = Task::find($id);
        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Kegiatan atau rapat tidak ditemukan.'], 404);
        }

        $penugasan = penugasan::where('id_kegiatan', $task->id)
            ->where(function($q) use ($user) {
                $q->where('niplama', $user->niplama)
                  ->orWhere('peserta', $user->nama_lengkap);
            })->first();

        if (!$penugasan) {
            $penugasan = new penugasan();
            $penugasan->id_kegiatan = $task->id;
            $penugasan->niplama     = $user->niplama;
            $penugasan->peserta     = $user->nama_lengkap;
            $penugasan->save();
        }

        // Catat log presensi di tabel presensis
        $now = Carbon::now('Asia/Singapore');
        DB::table('presensis')->insert([
            'niplama'    => $user->niplama,
            'status'     => 1, // Hadir
            'ket_dinas'  => 'Hadir ' . $task->jenis . ': ' . $task->text,
            'created_at' => $now
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kehadiran pada ' . $task->jenis . ' "' . $task->text . '" berhasil dicatat sebagai HADIR.',
            'data'    => [
                'id'             => $task->id,
                'judul'          => $task->text,
                'jenis'          => $task->jenis,
                'tempat'         => $task->tempat,
                'status'         => 'Hadir',
                'waktu_presensi' => $now->format('H:i') . ' WITA'
            ]
        ]);
    }
}
