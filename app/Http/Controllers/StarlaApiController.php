<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\SubKegiatan;
use App\penugasan;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StarlaApiController extends Controller
{
    /**
     * GET /api/starla/pst-jadwal
     * Mengambil daftar jadwal Petugas PST untuk dilempar / dikonsumsi oleh STARLA
     * Format per item: TANGGAL, SESI, ANGGOTA (NIP LAMA + DETAIL)
     * Mendukung query filter: ?tanggal=YYYY-MM-DD, ?bulan=YYYY-MM, ?sesi=1|2, ?niplama=xxx
     */
    public function getJadwalPst(Request $request)
    {
        $filterTanggal = $request->query('tanggal');
        $filterBulan   = $request->query('bulan');
        $filterSesi    = $request->query('sesi');
        $filterNiplama = $request->query('niplama');

        // Ambil penugasan PST
        $query = penugasan::where(function ($q) {
                $q->where('keterangan', 'like', '%Petugas PST%')
                  ->orWhere('keterangan', 'like', '%PST Sesi%');
            })
            ->whereHas('task', function ($q) use ($filterTanggal, $filterBulan) {
                $q->where(function ($sq) {
                    $sq->where('jenis_kegiatan', 'Pelayanan Statistik Terpadu (PST)')
                       ->orWhere('text', 'like', '%Pelayanan Statistik Terpadu%')
                       ->orWhere('text', 'like', '%PST%');
                });

                if ($filterTanggal) {
                    $q->where('start_date', $filterTanggal);
                } elseif ($filterBulan) {
                    $q->where('start_date', 'like', $filterBulan . '%');
                }
            })
            ->with(['task', 'user']);

        if ($filterNiplama) {
            $query->where('niplama', $filterNiplama);
        }

        $penugasans = $query->get();

        // Kelompokkan data per TANGGAL dan SESI
        $grouped = [];

        foreach ($penugasans as $p) {
            $task = $p->task;
            if (!$task || empty($task->start_date)) {
                continue;
            }

            $tanggal = $task->start_date;

            // Ekstrak sesi (1 atau 2) dari string keterangan
            $sesi = 1;
            if (preg_match('/Sesi\s*(\d+)/i', $p->keterangan ?? '', $matches)) {
                $sesi = (int)$matches[1];
            }

            // Jika ada filter sesi dan tidak cocok, lewati
            if ($filterSesi && (int)$filterSesi !== $sesi) {
                continue;
            }

            $groupKey = $tanggal . '_' . $sesi;

            if (!isset($grouped[$groupKey])) {
                $carbonTgl = Carbon::parse($tanggal);
                $grouped[$groupKey] = [
                    'tanggal'           => $tanggal,
                    'hari'              => $carbonTgl->translatedFormat('l'),
                    'tanggal_format'    => $carbonTgl->translatedFormat('d F Y'),
                    'sesi'              => $sesi,
                    'sesi_label'        => 'Sesi ' . $sesi . ' (' . ($sesi === 1 ? '08:00 - 12:00 WITA' : '13:00 - 16:00 WITA') . ')',
                    'jam_mulai'         => $sesi === 1 ? '08:00:00' : '13:00:00',
                    'jam_selesai'       => $sesi === 1 ? '12:00:00' : '16:00:00',
                    'tempat'            => $task->tempat ?? 'Ruang PST BPS Provinsi Sulawesi Tenggara',
                    'anggota'           => [],
                    'total_petugas'     => 0,
                ];
            }

            $user = $p->user;

            $grouped[$groupKey]['anggota'][] = [
                'niplama'          => (string) $p->niplama,
                'nipbaru'          => $user ? (string) $user->nipbaru : null,
                'nama'             => $p->peserta ?: ($user ? $user->nama_lengkap : '-'),
                'email'            => $user ? $user->email : null,
                'status_kehadiran' => $p->status_kehadiran ?: 'Belum Konfirmasi',
                'waktu_kehadiran'  => $p->waktu_kehadiran,
            ];

            $grouped[$groupKey]['total_petugas'] = count($grouped[$groupKey]['anggota']);
        }

        // Urutkan berdasarkan tanggal & sesi ASC
        ksort($grouped);
        $result = array_values($grouped);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Berhasil mengambil data jadwal Petugas PST untuk STARLA.',
            'meta'    => [
                'total_jadwal'  => count($result),
                'filter'        => [
                    'tanggal' => $filterTanggal,
                    'bulan'   => $filterBulan,
                    'sesi'    => $filterSesi ? (int)$filterSesi : null,
                    'niplama' => $filterNiplama,
                ],
                'timestamp'     => Carbon::now()->toIso8601String(),
            ],
            'data'    => $result,
        ], 200, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /api/starla/pst-jadwal
     * Menerima payload jadwal Petugas PST dari STARLA untuk disinkronkan ke SIKEREN
     * Format payload:
     * {
     *   "tanggal": "2026-10-15",
     *   "sesi": 1,
     *   "anggota": [
     *      "340017760",
     *      "340055840"
     *   ]
     * }
     * Atau array anggota dengan objek:
     * {
     *   "tanggal": "2026-10-15",
     *   "sesi": 1,
     *   "anggota": [
     *      {"niplama": "340017760", "nama": "Wa Ode Vitria"}
     *   ]
     * }
     */
    public function storeJadwalPst(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'   => 'required|date_format:Y-m-d',
            'sesi'      => 'required|in:1,2',
            'anggota'   => 'required|array|min:1',
        ], [
            'tanggal.required'    => 'Field tanggal wajib diisi dengan format YYYY-MM-DD.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD (contoh: 2026-10-15).',
            'sesi.required'       => 'Field sesi wajib diisi (1 atau 2).',
            'sesi.in'             => 'Sesi hanya boleh bernilai 1 atau 2.',
            'anggota.required'    => 'Field anggota wajib diisi berupa array NIP Lama.',
            'anggota.array'       => 'Field anggota harus berupa array.',
            'anggota.min'         => 'Minimal sertakan 1 anggota (NIP Lama).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 422,
                'message' => 'Validasi payload gagal.',
                'errors'  => $validator->errors(),
            ], 422, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT);
        }

        $tanggal = $request->input('tanggal');
        $sesi    = (int) $request->input('sesi');
        $anggota = $request->input('anggota');

        DB::beginTransaction();
        try {
            $carbonTgl = Carbon::parse($tanggal);
            $isPast = $carbonTgl->lt(Carbon::today());
            $judulKegiatan = "Pelayanan Statistik Terpadu (PST) (" . $carbonTgl->translatedFormat('d M Y') . ")";

            // 1. Temukan atau Buat Task Utama PST per Hari
            $task = Task::where('start_date', $tanggal)
                ->where(function ($q) {
                    $q->where('jenis_kegiatan', 'Pelayanan Statistik Terpadu (PST)')
                      ->orWhere('text', 'like', '%Pelayanan Statistik Terpadu%')
                      ->orWhere('text', 'like', '%PST%');
                })
                ->first();

            if (!$task) {
                $task = new Task();
                $task->start_date       = $tanggal;
                $task->date_akhir       = $tanggal;
                $task->start_jam        = '08:00:00';
                $task->end_jam          = '16:00:00';
                $task->jenis            = 'Kegiatan';
                $task->jenis_kegiatan   = 'Pelayanan Statistik Terpadu (PST)';
                $task->tim              = 'Diseminasi dan Layanan Statistik';
                $task->tempat           = 'Ruang PST BPS Provinsi Sulawesi Tenggara';
                $task->setuju_rapat     = 1;
            }

            $task->text             = $judulKegiatan;
            $task->penanggung_jawab = 'Ketua Tim PST';
            $task->status           = $isPast ? 'Selesai' : 'Sedang Berjalan';
            $task->progress         = $isPast ? 100 : 0;
            $task->agenda           = "Pelayanan Statistik Terpadu (PST) BPS Provinsi Sulawesi Tenggara pada " . $carbonTgl->translatedFormat('d F Y') . " (Sesi 1: 08:00 - 12:00 WITA, Sesi 2: 13:00 - 16:00 WITA).";
            $task->save();

            // 2. Normalisasi Anggota Petugas (NIP Lama & Nama)
            $petugasList = [];
            foreach ($anggota as $item) {
                $niplamaInput = null;
                $namaInput    = null;

                if (is_array($item)) {
                    $niplamaInput = trim((string)($item['niplama'] ?? ''));
                    $namaInput    = trim((string)($item['nama'] ?? ''));
                } else {
                    $niplamaInput = trim((string)$item);
                }

                if (empty($niplamaInput)) {
                    continue;
                }

                // Cari data pegawai di tabel users
                $user = User::where('niplama', $niplamaInput)->first();
                if (!$user && $namaInput) {
                    $user = User::where('nama_lengkap', 'like', '%' . $namaInput . '%')->first();
                }

                $petugasList[] = [
                    'niplama' => $niplamaInput,
                    'nama'    => $user ? $user->nama_lengkap : ($namaInput ?: 'Petugas ' . $niplamaInput),
                    'user_id' => $user ? $user->id : null,
                ];
            }

            if (empty($petugasList)) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'code'    => 400,
                    'message' => 'Tidak ada NIP Lama yang valid dalam payload.',
                ], 400);
            }

            $namesOnly = array_column($petugasList, 'nama');
            $sesiName = "PST Sesi " . $sesi . " (" . ($sesi === 1 ? 'Pagi 08:00 - 12:00' : 'Siang 13:00 - 16:00') . ")";

            // 3. Update / Buat Sub Kegiatan untuk Sesi ini
            $sub = SubKegiatan::where('task_id', $task->id)
                ->where(function ($q) use ($sesi) {
                    $q->where('nama_sub', 'like', '%Sesi ' . $sesi . '%')
                      ->orWhere('nama_sub', 'like', 'PST Sesi ' . $sesi . '%');
                })
                ->first();

            if (!$sub) {
                $sub = new SubKegiatan();
                $sub->task_id    = $task->id;
                $sub->start_date = $tanggal;
                $sub->end_date   = $tanggal;
                $sub->tim        = 'Diseminasi dan Layanan Statistik';
            }

            $sub->nama_sub   = $sesiName;
            $sub->pj         = $namesOnly[0] ?? 'Petugas PST';
            $sub->anggota    = json_encode($namesOnly);
            $sub->status     = $isPast ? 'Selesai' : 'Sedang Berjalan';
            $sub->progress   = $isPast ? 100 : 0;
            $sub->keterangan = "Petugas PST Sesi " . $sesi . " (" . ($sesi === 1 ? '08:00 - 12:00' : '13:00 - 16:00') . " WITA): " . implode(', ', $namesOnly);
            $sub->save();

            // 4. Update / Tambah Penugasan Petugas Sesi Ini
            $savedPetugas = [];
            foreach ($petugasList as $p) {
                $penugasanItem = penugasan::where('id_kegiatan', $task->id)
                    ->where('niplama', $p['niplama'])
                    ->where('keterangan', 'like', '%Sesi ' . $sesi . '%')
                    ->first();

                if (!$penugasanItem) {
                    $penugasanItem = new penugasan();
                    $penugasanItem->id_kegiatan       = $task->id;
                    $penugasanItem->niplama           = $p['niplama'];
                    $penugasanItem->status_kehadiran = 'Belum Konfirmasi';
                }

                $penugasanItem->peserta    = $p['nama'];
                $penugasanItem->keterangan = "Petugas PST Sesi " . $sesi;
                if ($isPast && empty($penugasanItem->status_kehadiran)) {
                    $penugasanItem->status_kehadiran = 'Hadir';
                }
                $penugasanItem->save();

                // Notifikasi ke Petugas
                if (!empty($p['user_id'])) {
                    $notifExists = DB::table('notifications')
                        ->where('notifiable_id', $p['user_id'])
                        ->where('data', 'like', '%"task_id":' . $task->id . '%')
                        ->where('data', 'like', '%"sesi":' . $sesi . '%')
                        ->exists();

                    if (!$notifExists) {
                        DB::table('notifications')->insert([
                            'id'              => (string) Str::uuid(),
                            'type'            => 'App\Notifications\PenugasanPstNotification',
                            'notifiable_type' => 'App\User',
                            'notifiable_id'   => $p['user_id'],
                            'data'            => json_encode([
                                'judul'       => "Penugasan Petugas PST Sesi {$sesi}",
                                'pesan'       => "Anda ditugaskan sebagai Petugas PST Sesi {$sesi} pada {$carbonTgl->translatedFormat('d F Y')}. Silakan konfirmasi kehadiran Anda.",
                                'url'         => '/daftarkegiatan/' . $task->id,
                                'task_id'     => $task->id,
                                'sesi'        => $sesi,
                            ]),
                            'read_at'         => null,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }

                $savedPetugas[] = [
                    'niplama'          => $p['niplama'],
                    'nama'             => $p['nama'],
                    'status_kehadiran' => $penugasanItem->status_kehadiran,
                ];
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'code'    => 201,
                'message' => "Jadwal Petugas PST Sesi {$sesi} pada tanggal {$tanggal} berhasil disinkronkan ke SIKEREN.",
                'data'    => [
                    'task_id'       => $task->id,
                    'tanggal'       => $tanggal,
                    'sesi'          => $sesi,
                    'sub_kegiatan'  => $sesiName,
                    'anggota'       => $savedPetugas,
                    'total_petugas' => count($savedPetugas),
                ],
            ], 201, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'code'    => 500,
                'message' => 'Gagal memproses jadwal PST: ' . $e->getMessage(),
            ], 500);
        }
    }
}
