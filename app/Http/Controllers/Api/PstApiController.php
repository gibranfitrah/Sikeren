<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\JadwalPst;
use App\Notification;
use App\User;
use App\Task;
use App\penugasan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PstApiController extends Controller
{
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->bearerToken() ?: $request->header('X-Token') ?: $request->input('token');
        if ($token) {
            $user = User::where('token_id', $token)->first();
            if ($user) return $user;
        }

        if ($request->has('niplama')) {
            $user = User::where('niplama', $request->niplama)->first();
            if ($user) return $user;
        }

        return User::first();
    }

    /**
     * GET /api/v1/pst/jadwal
     * Menampilkan jadwal PST hari ini, jadwal saya, dan permintaan pengganti
     */
    public function getJadwal(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        $today = Carbon::today()->format('Y-m-d');

        // Pastikan ada data jadwal untuk hari ini (jika kosong, bootstrap dari master petugas atau penugasan)
        $this->ensureTodaySchedule();

        // 1. Petugas yang bertugas hari ini
        $todaySchedules = JadwalPst::where('tanggal', $today)
            ->orderBy('sesi')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                 => $item->id,
                    'tanggal'            => $item->tanggal->format('Y-m-d'),
                    'tanggal_formatted'  => $item->tanggal->translatedFormat('l, d F Y'),
                    'sesi'               => $item->sesi,
                    'sesi_label'         => $item->sesi_label,
                    'jam'                => "{$item->jam_mulai} - {$item->jam_selesai} WITA",
                    'niplama'            => $item->niplama,
                    'nama_petugas'       => $item->nama_petugas,
                    'status_kehadiran'   => $item->status_kehadiran,
                    'status_tukar'       => $item->status_tukar,
                    'alasan_berhalangan' => $item->alasan_berhalangan,
                    'pengganti_niplama'  => $item->pengganti_niplama,
                    'pengganti_nama'     => $item->pengganti_nama,
                    'is_replaced'        => !empty($item->pengganti_nama),
                    'display_nama'       => !empty($item->pengganti_nama) 
                                            ? "{$item->pengganti_nama} (Gantikan {$item->nama_petugas})" 
                                            : $item->nama_petugas
                ];
            });

        // 2. Jadwal saya (user login)
        $userNip = $user ? $user->niplama : '';
        $userNama = $user ? $user->nama_lengkap : '';

        $mySchedules = JadwalPst::where(function ($q) use ($userNip, $userNama) {
                $q->where('niplama', $userNip)
                  ->orWhere('pengganti_niplama', $userNip)
                  ->orWhere('nama_petugas', 'like', "%{$userNama}%")
                  ->orWhere('pengganti_nama', 'like', "%{$userNama}%");
            })
            ->where('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->orderBy('sesi')
            ->take(10)
            ->get()
            ->map(function ($item) use ($userNip, $userNama) {
                $isSubstitute = ($item->pengganti_niplama === $userNip || 
                                 (!empty($userNama) && stripos($item->pengganti_nama ?? '', $userNama) !== false));
                return [
                    'id'                 => $item->id,
                    'tanggal'            => $item->tanggal->format('Y-m-d'),
                    'tanggal_formatted'  => $item->tanggal->translatedFormat('l, d F Y'),
                    'sesi'               => $item->sesi,
                    'sesi_label'         => $item->sesi_label,
                    'jam'                => "{$item->jam_mulai} - {$item->jam_selesai} WITA",
                    'status_kehadiran'   => $item->status_kehadiran,
                    'status_tukar'       => $item->status_tukar,
                    'is_substitute'      => $isSubstitute,
                    'can_report_absence' => ($item->status_kehadiran !== 'Berhalangan' && $item->status_kehadiran !== 'Digantikan')
                ];
            });

        // 3. Permintaan pengganti aktif (siapa saja yang berhalangan dan butuh pengganti)
        $needsReplacement = JadwalPst::where('status_tukar', 'minta_pengganti')
            ->where('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->orderBy('sesi')
            ->get()
            ->map(function ($item) use ($userNip) {
                return [
                    'id'                 => $item->id,
                    'tanggal'            => $item->tanggal->format('Y-m-d'),
                    'tanggal_formatted'  => $item->tanggal->translatedFormat('d M Y'),
                    'sesi'               => $item->sesi,
                    'sesi_label'         => $item->sesi_label,
                    'jam'                => "{$item->jam_mulai} - {$item->jam_selesai} WITA",
                    'petugas_berhalangan'=> $item->nama_petugas,
                    'alasan'             => $item->alasan_berhalangan ?: 'Tidak ada keterangan',
                    'is_mine'            => ($item->niplama === $userNip)
                ];
            });

        $isAdminPst = $user ? $user->isAdminPst() : false;
        $isPimpinan = $user ? $user->isPimpinan() : false;

        return response()->json([
            'success'           => true,
            'is_admin_pst'      => $isAdminPst,
            'is_pimpinan'       => $isPimpinan,
            'today_date'        => Carbon::today()->translatedFormat('l, d F Y'),
            'today_schedules'   => $todaySchedules,
            'my_schedules'      => $mySchedules,
            'needs_replacement' => $needsReplacement,
        ]);
    }

    /**
     * POST /api/v1/pst/berhalangan
     * Petugas menyatakan berhalangan dan meminta pengganti
     */
    public function laporkanBerhalangan(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        $jadwalId = $request->input('jadwal_id');
        $alasan   = trim($request->input('alasan', 'Ada tugas dinas mendadak'));

        $jadwal = JadwalPst::find($jadwalId);
        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Data jadwal tidak ditemukan.'], 404);
        }

        // Validasi: hanya petugas bersangkutan atau Admin PST yang boleh ubah
        $isOwner = ($jadwal->niplama === $user->niplama || 
                    stripos($jadwal->nama_petugas, $user->nama_lengkap) !== false ||
                    $jadwal->pengganti_niplama === $user->niplama);

        if (!$isOwner && !$user->isAdminPst()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses mengubah jadwal ini.'], 403);
        }

        $jadwal->status_kehadiran   = 'Berhalangan';
        $jadwal->status_tukar       = 'minta_pengganti';
        $jadwal->alasan_berhalangan = $alasan;
        $jadwal->save();

        // Kirim Notifikasi ke semua pegawai yang BUKAN pimpinan dan BUKAN yang sedang bertugas di sesi tersebut
        $tglFormat = $jadwal->tanggal->translatedFormat('d F Y');
        $sesiText = $jadwal->sesi_label;

        // Ambil NIP yang sedang bertugas di hari & sesi yang sama
        $busyNips = JadwalPst::where('tanggal', $jadwal->tanggal->format('Y-m-d'))
            ->where('sesi', $jadwal->sesi)
            ->pluck('niplama')
            ->toArray();
        $busyNips[] = $user->niplama;

        // Cari kandidat pengganti (bukan pimpinan, bukan admin, bukan yang sedang bertugas)
        $eligibleOfficers = User::getEligiblePstOfficers()->filter(function ($u) use ($busyNips) {
            return !in_array($u->niplama, $busyNips);
        });

        foreach ($eligibleOfficers as $targetUser) {
            DB::table('notifications')->insert([
                'niplama'    => $targetUser->niplama,
                'judul'      => "Butuh Pengganti PST: {$jadwal->nama_petugas} Berhalangan",
                'pesan'      => "{$jadwal->nama_petugas} berhalangan menjaga PST {$sesiText} pada tanggal {$tglFormat} (Alasan: {$alasan}). Apakah Anda bersedia menggantikan?",
                'link'       => '/pst/jadwal',
                'tipe'       => 'pst_replacement',
                'is_read'    => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhalangan tersimpan. Notifikasi permintaan pengganti telah dikirimkan ke pegawai lain.',
            'data'    => $jadwal
        ]);
    }

    /**
     * POST /api/v1/pst/gantikan
     * Pegawai lain bersedia menggantikan petugas yang berhalangan
     */
    public function ambilAlihShift(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        // Pimpinan dilarang menjadi penjaga PST
        if ($user->isPimpinan()) {
            return response()->json([
                'success' => false, 
                'message' => 'Pimpinan tidak diperbolehkan menjadi petugas penjaga PST.'
            ], 403);
        }

        $jadwalId = $request->input('jadwal_id');
        $jadwal = JadwalPst::find($jadwalId);
        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Data jadwal tidak ditemukan.'], 404);
        }

        if ($jadwal->status_tukar !== 'minta_pengganti') {
            return response()->json(['success' => false, 'message' => 'Shift ini sudah memiliki pengganti atau tidak membutuhkan pengganti.'], 400);
        }

        // Cek apakah user sudah bertugas di sesi yang sama
        $alreadyAssigned = JadwalPst::where('tanggal', $jadwal->tanggal->format('Y-m-d'))
            ->where('sesi', $jadwal->sesi)
            ->where(function ($q) use ($user) {
                $q->where('niplama', $user->niplama)
                  ->orWhere('pengganti_niplama', $user->niplama);
            })
            ->exists();

        if ($alreadyAssigned) {
            return response()->json(['success' => false, 'message' => 'Anda sudah memiliki jadwal piket PST di sesi yang sama.'], 400);
        }

        // Update shift
        $jadwal->pengganti_niplama = $user->niplama;
        $jadwal->pengganti_nama    = $user->nama_lengkap;
        $jadwal->status_kehadiran  = 'Digantikan';
        $jadwal->status_tukar      = 'selesai';
        $jadwal->save();

        // Notifikasi ke petugas asli yang digantikan
        $tglFormat = $jadwal->tanggal->translatedFormat('d F Y');
        DB::table('notifications')->insert([
            'niplama'    => $jadwal->niplama,
            'judul'      => "Pengganti PST Ditemukan: {$user->nama_lengkap}",
            'pesan'      => "{$user->nama_lengkap} telah bersedia menggantikan Anda menjaga PST {$jadwal->sesi_label} pada tanggal {$tglFormat}.",
            'link'       => '/pst/jadwal',
            'tipe'       => 'pst_replacement_success',
            'is_read'    => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Terima kasih! Anda resmi terdaftar sebagai pengganti penjaga PST untuk {$jadwal->nama_petugas}.",
            'data'    => $jadwal
        ]);
    }

    /**
     * POST /api/v1/pst/assign-pengganti
     * Admin PST secara langsung menunjuk pengganti
     */
    public function assignPengganti(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user || !$user->isAdminPst()) {
            return response()->json(['success' => false, 'message' => 'Hanya Admin PST yang dapat menunjuk pengganti.'], 403);
        }

        $jadwalId        = $request->input('jadwal_id');
        $penggantiNip    = $request->input('pengganti_niplama');
        $penggantiUser   = User::where('niplama', $penggantiNip)->first();

        if (!$penggantiUser) {
            return response()->json(['success' => false, 'message' => 'Data pegawai pengganti tidak ditemukan.'], 404);
        }

        if ($penggantiUser->isPimpinan()) {
            return response()->json(['success' => false, 'message' => 'Pimpinan tidak dapat ditugaskan sebagai petugas PST.'], 422);
        }

        $jadwal = JadwalPst::find($jadwalId);
        if (!$jadwal) {
            return response()->json(['success' => false, 'message' => 'Data jadwal tidak ditemukan.'], 404);
        }

        $jadwal->pengganti_niplama = $penggantiUser->niplama;
        $jadwal->pengganti_nama    = $penggantiUser->nama_lengkap;
        $jadwal->status_kehadiran  = 'Digantikan';
        $jadwal->status_tukar      = 'selesai';
        $jadwal->save();

        // Notifikasi ke petugas asli dan ke petugas yang ditunjuk
        $tglFormat = $jadwal->tanggal->translatedFormat('d F Y');
        DB::table('notifications')->insert([
            'niplama'    => $penggantiUser->niplama,
            'judul'      => "Penugasan Pengganti PST dari Admin",
            'pesan'      => "Anda ditugaskan oleh Admin PST untuk menggantikan {$jadwal->nama_petugas} pada tanggal {$tglFormat} ({$jadwal->sesi_label}).",
            'link'       => '/pst/jadwal',
            'tipe'       => 'pst_replacement',
            'is_read'    => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Petugas {$penggantiUser->nama_lengkap} berhasil ditugaskan menggantikan {$jadwal->nama_petugas}."
        ]);
    }

    /**
     * GET /api/v1/pst/eligible-officers
     * Daftar pegawai yang eligible menjaga PST (Bukan Admin & Bukan Pimpinan)
     */
    public function getEligibleOfficers()
    {
        $officers = User::getEligiblePstOfficers()->map(function ($u) {
            return [
                'niplama'      => $u->niplama,
                'nipbaru'      => $u->nipbaru,
                'nama_lengkap' => $u->nama_lengkap,
                'jabatan'      => $u->role_label
            ];
        });

        return response()->json([
            'success' => true,
            'total'   => count($officers),
            'data'    => $officers
        ]);
    }

    /**
     * Pastikan jadwal hari ini dan besok tersedia di jadwal_psts
     */
    private function ensureTodaySchedule()
    {
        $today = Carbon::today()->format('Y-m-d');
        $count = JadwalPst::where('tanggal', $today)->count();

        if ($count === 0) {
            // Ambil dari master petugas_pst atau pegawai eligible (8 petugas: 4 sesi 1, 4 sesi 2)
            $sampleOfficers = DB::table('petugas_pst')
                ->where('id', '!=', 123456789)
                ->take(8)
                ->get();

            if ($sampleOfficers->isEmpty()) {
                $sampleOfficers = User::getEligiblePstOfficers()->take(8);
            }

            $officerArray = $sampleOfficers->values()->all();

            // Sesi 1 (4 petugas)
            for ($i = 0; $i < 4; $i++) {
                if (isset($officerArray[$i])) {
                    JadwalPst::create([
                        'tanggal'          => $today,
                        'sesi'             => 1,
                        'niplama'          => $officerArray[$i]->id ?? $officerArray[$i]->niplama,
                        'nama_petugas'     => $officerArray[$i]->nama ?? $officerArray[$i]->nama_lengkap,
                        'status_kehadiran' => ($i === 0 ? 'Hadir' : 'Terjadwal'),
                        'status_tukar'     => 'none',
                    ]);
                }
            }

            // Sesi 2 (4 petugas)
            for ($i = 4; $i < 8; $i++) {
                if (isset($officerArray[$i])) {
                    JadwalPst::create([
                        'tanggal'          => $today,
                        'sesi'             => 2,
                        'niplama'          => $officerArray[$i]->id ?? $officerArray[$i]->niplama,
                        'nama_petugas'     => $officerArray[$i]->nama ?? $officerArray[$i]->nama_lengkap,
                        'status_kehadiran' => 'Terjadwal',
                        'status_tukar'     => 'none',
                    ]);
                }
            }
        }
    }
}
