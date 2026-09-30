<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Task;
use App\SubKegiatan;
use App\User;
use App\master_group;
use App\group;
use Carbon\Carbon;

class KetuaTimController extends Controller
{
    /**
     * Menampilkan daftar kegiatan (Kelola Kegiatan)
     */
    public function index()
    {
        Task::updateExpiredKegiatanStatus();

        $totalAgenda     = Task::where('jenis', 'Kegiatan')->count();
        $agendaTerjadwal = Task::where('jenis', 'Kegiatan')->where(function($q) {
            $q->where('status', '!=', 'Selesai')->orWhereNull('status');
        })->count();
        $agendaSelesai   = Task::where('jenis', 'Kegiatan')->where('status', 'Selesai')->count();
        
        $kegiatans = Task::with('subKegiatans')
            ->where('jenis', 'Kegiatan')
            ->orderBy('id', 'desc')
            ->get();

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('ketua_tim.index', compact(
            'totalAgenda',
            'agendaTerjadwal',
            'agendaSelesai',
            'kegiatans',
            'notifications',
            'jumlah_notif'
        ));
    }

    /**
     * Menampilkan form buat kegiatan baru (3 Tahap)
     */
    public function create()
    {
        $masterGroups = master_group::all();
        $allUsers = User::getPegawaiBps();
        $eligiblePJs = User::getEligiblePJs();
        
        // Group employees by master group (exclude admin)
        $usersByGroup = group::join('users', 'users.niplama', '=', 'groups.niplama')
            ->where('users.username', '!=', 'admin')
            ->where('users.nama_lengkap', '!=', 'Administrator')
            ->select('groups.grup', 'users.id', 'users.niplama', 'users.nama_lengkap', 'users.username')
            ->get()
            ->groupBy('grup');

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        // Master Proyek SIMPATI (80 Proyek & Penugasan SDM)
        $masterProyeks = \App\Proyek::with(['anggota:proyekid,nama_lengkap,niplama'])
            ->orderBy('nm_tim', 'asc')
            ->orderBy('namaproyek', 'asc')
            ->get();
        $masterProyeksByTim = $masterProyeks->groupBy('nm_tim');

        return view('ketua_tim.create', compact(
            'masterGroups',
            'allUsers',
            'eligiblePJs',
            'usersByGroup',
            'masterProyeks',
            'masterProyeksByTim',
            'notifications',
            'jumlah_notif'
        ));
    }

    /**
     * Menyimpan kegiatan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'agenda'       => 'required|string|max:255',
            'start_date'   => 'required|date',
            'date_akhir'   => 'nullable|date|after_or_equal:start_date',
            'start_jam'    => 'nullable|string',
            'end_jam'      => 'nullable|string',
            'tim'          => 'required|string',
            'pj'           => 'required|string',
            'wilayah'      => 'nullable|array',
            'anggota'      => 'nullable|array',
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->date_akhir ?: $startDate;
        $startJam  = $request->start_jam ?: '08:00';
        $endJam    = $request->end_jam ?: '16:00';

        // Calculate duration in days
        $startCarbon = Carbon::parse($startDate);
        $endCarbon   = Carbon::parse($endDate);
        $duration    = $startCarbon->diffInDays($endCarbon) + 1;

        $wilayahJson = $request->has('wilayah') && is_array($request->wilayah)
            ? json_encode($request->wilayah)
            : ($request->wilayah ? json_encode([$request->wilayah]) : null);

        $anggotaList = $request->has('anggota') && is_array($request->anggota)
            ? $request->anggota
            : [];

        // 1. Simpan ke tabel tasks
        $task = Task::create([
            'text'             => $request->agenda,
            'agenda'           => $request->perihal ?: $request->agenda,
            'tim'              => $request->tim,
            'wilayah'          => $wilayahJson,
            'start_date'       => $startDate,
            'date_akhir'       => $endDate,
            'start_jam'        => $startJam,
            'end_jam'          => $endJam,
            'duration'         => $duration,
            'penanggung_jawab' => $request->pj,
            'pemimpin'         => $request->pj,
            'owners'           => $anggotaList,
            'jenis'            => 'Kegiatan',
            'status'           => 'Menunggu Persetujuan',
            'status_pemimpin'  => 'Menunggu',
            'setuju_rapat'     => 0,
            'surat'            => $request->dasar,
        ]);

        // 2. Simpan juga ke penugasans jika ada anggota
        foreach ($anggotaList as $namaOrNip) {
            $user = User::where('nama_lengkap', $namaOrNip)
                ->orWhere('niplama', $namaOrNip)
                ->orWhere('username', $namaOrNip)
                ->first();

            \App\penugasan::create([
                'id_kegiatan' => $task->id,
                'niplama'     => $user ? $user->niplama : $namaOrNip,
                'peserta'     => $user ? $user->nama_lengkap : $namaOrNip,
            ]);

            // Kirim notifikasi ke user yang ditugaskan
            if ($user && $user->id !== Auth::id()) {
                $pjNama = $request->pj ?: (Auth::check() ? Auth::user()->nama_lengkap : 'Ketua Tim / PJ');
                DB::table('notifications')->insert([
                    'id'              => (string) \Illuminate\Support\Str::uuid(),
                    'type'            => 'App\Notifications\PenugasanKegiatanNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $user->id,
                    'data'            => json_encode([
                        'judul' => 'Undangan Penugasan Kegiatan: ' . $request->agenda,
                        'pesan' => 'Anda ditugaskan oleh ' . $pjNama . ' (Ketua Tim / PJ) untuk mengikuti kegiatan "' . $request->agenda . '" mulai tanggal ' . date('d M Y', strtotime($startDate)) . '.',
                        'url'   => ($request->jenis === 'Rapat' || !empty($request->start_jam)) ? ('/rapat/tiket-qr/' . $task->id) : '/tugas-saya',
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // Notifikasi khusus untuk PJ jika ditugaskan orang lain
        if (!empty($request->pj)) {
            $userPj = User::where('nama_lengkap', $request->pj)->orWhere('username', $request->pj)->first();
            if ($userPj && $userPj->id !== Auth::id()) {
                DB::table('notifications')->insert([
                    'id'              => (string) \Illuminate\Support\Str::uuid(),
                    'type'            => 'App\Notifications\PenugasanKegiatanNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $userPj->id,
                    'data'            => json_encode([
                        'judul' => 'Penunjukan Penanggung Jawab (PJ): ' . $request->agenda,
                        'pesan' => 'Anda ditunjuk sebagai Penanggung Jawab (PJ) untuk kegiatan "' . $request->agenda . '" yang dimulai pada ' . date('d M Y', strtotime($startDate)) . '.',
                        'url'   => '/daftarkegiatan/' . $task->id,
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // 3. Simpan juga ke agenda_ketua_tim for backward compatibility
        DB::table('agenda_ketua_tim')->insert([
            'perihal'      => $request->perihal,
            'agenda'       => $request->agenda,
            'sub_kegiatan' => $request->sub_kegiatan,
            'dasar'        => $request->dasar,
            'tanggal'      => $startDate,
            'jam'          => (strlen($startJam) == 5 ? $startJam . ':00' : $startJam),
            'tempat'       => 'Wilayah Pelaksanaan',
            'pj'           => $request->pj,
            'tujuan'       => is_array($request->wilayah) ? implode(', ', $request->wilayah) : ($request->wilayah ?: '-'),
            'anggota'      => !empty($anggotaList) ? json_encode($anggotaList) : null,
            'status'       => 'terjadwal',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('kegiatan.daftar')->with('success', 'Kegiatan baru berhasil dibuat dan diterbitkan!');
    }

    /**
     * Menampilkan detail kegiatan
     */
    public function show($id)
    {
        $task = Task::with('subKegiatans')->find($id);

        if (!$task) {
            $agenda = DB::table('agenda_ketua_tim')->where('id', $id)->first();
            if (!$agenda) {
                abort(404, 'Kegiatan tidak ditemukan');
            }
            return redirect()->route('kegiatan.daftar');
        }

        return view('displayKegiatan', ['task' => $task]);
    }

    /**
     * Menghapus kegiatan
     */
    public function destroy($id)
    {
        $task = Task::find($id);
        if ($task) {
            SubKegiatan::where('task_id', $id)->delete();
            \App\penugasan::where('id_kegiatan', $id)->delete();
            $task->delete();
        }

        DB::table('agenda_ketua_tim')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus!');
    }

    /**
     * Import Jadwal Petugas PST dari Template Starla (.xlsx)
     */
    public function uploadTemplatePst(Request $request)
    {
        $defaultPath = 'D:/MAGANG/yang mau dijadikan database/template-petugas-pst september_.xlsx';
        $filePath = null;

        if ($request->hasFile('file_pst') && $request->file('file_pst')->isValid()) {
            $filePath = $request->file('file_pst')->getRealPath();
        } elseif (file_exists($defaultPath)) {
            $filePath = $defaultPath;
        }

        if (!$filePath || !file_exists($filePath)) {
            return redirect()->back()->with('error', 'File template PST tidak ditemukan. Silakan unggah file template .xlsx.');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);

            // 1. Baca master petugas dari sheet 'petugas'
            $sheetPetugas = $spreadsheet->getSheetByName('petugas');
            $petugasMap = [];
            if ($sheetPetugas) {
                $maxRowP = $sheetPetugas->getHighestRow();
                for ($r = 2; $r <= $maxRowP; $r++) {
                    $nip       = trim((string)$sheetPetugas->getCellByColumnAndRow(1, $r)->getValue());
                    $nama      = trim((string)$sheetPetugas->getCellByColumnAndRow(2, $r)->getValue());
                    $panggilan = strtolower(trim((string)$sheetPetugas->getCellByColumnAndRow(3, $r)->getValue()));

                    if ($panggilan && $nama) {
                        $petugasMap[$panggilan] = [
                            'nip'  => $nip,
                            'nama' => $nama
                        ];
                    }
                }
            }

            // 2. Baca jadwal harian dari sheet 'Entri Disini'
            $sheetEntri = $spreadsheet->getSheetByName('Entri Disini');
            if (!$sheetEntri) {
                return redirect()->back()->with('error', 'Sheet "Entri Disini" tidak ditemukan dalam file Excel.');
            }

            $maxRowE = $sheetEntri->getHighestRow();
            $importedCount = 0;

            for ($r = 2; $r <= $maxRowE; $r++) {
                $rawTanggal = trim((string)$sheetEntri->getCellByColumnAndRow(1, $r)->getValue());
                $sesi       = trim((string)$sheetEntri->getCellByColumnAndRow(2, $r)->getValue());

                if (empty($rawTanggal) || empty($sesi)) {
                    continue;
                }

                // Parse tanggal
                try {
                    // Cek jika excel numeric date
                    if (is_numeric($rawTanggal)) {
                        $carbonTgl = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawTanggal));
                    } else {
                        $carbonTgl = \Carbon\Carbon::parse($rawTanggal);
                    }
                } catch (\Exception $e) {
                    continue;
                }

                // Lewati hari Sabtu dan Minggu (hanya hari kerja)
                if ($carbonTgl->isWeekend()) {
                    continue;
                }

                $tglStr = $carbonTgl->format('Y-m-d');
                $startJam = ($sesi == '1') ? '08:00' : '13:00';
                $endJam   = ($sesi == '1') ? '12:00' : '16:00';

                // Kumpulkan petugas (Kolom C, D, E, F)
                $petugasList = [];
                for ($c = 3; $c <= 6; $c++) {
                    $pCall = strtolower(trim((string)$sheetEntri->getCellByColumnAndRow($c, $r)->getValue()));
                    if ($pCall && isset($petugasMap[$pCall])) {
                        $petugasList[] = $petugasMap[$pCall];
                    } elseif ($pCall) {
                        // Coba cari nama user yang mirip di DB
                        $u = User::where('nama_lengkap', 'like', '%' . $pCall . '%')->first();
                        if ($u) {
                            $petugasList[] = [
                                'nip'  => $u->niplama,
                                'nama' => $u->nama_lengkap
                            ];
                        } else {
                            $petugasList[] = [
                                'nip'  => 'NIP' . rand(100000, 999999),
                                'nama' => ucfirst($pCall)
                            ];
                        }
                    }
                }

                if (empty($petugasList)) {
                    continue;
                }

                $namesOnly = array_column($petugasList, 'nama');
                $nipsOnly  = array_column($petugasList, 'nip');
                $judulKegiatan = "Petugas PST Sesi " . $sesi . " (" . $carbonTgl->translatedFormat('d M Y') . ")";
                $agenda = "Jadwal Petugas Pelayanan Statistik Terpadu (PST) Sesi " . $sesi . " (" . $startJam . " - " . $endJam . " WITA). Petugas: " . implode(', ', $namesOnly);

                // Buat atau Update Task Kegiatan
                $task = Task::where('start_date', $tglStr)
                    ->where('jenis_kegiatan', 'Pelayanan Statistik Terpadu (PST)')
                    ->where('text', 'like', '%Sesi ' . $sesi . '%')
                    ->first();

                if (!$task) {
                    $task = new Task();
                    $task->text             = $judulKegiatan;
                    $task->start_date       = $tglStr;
                    $task->date_akhir       = $tglStr;
                    $task->start_jam        = $startJam;
                    $task->end_jam          = $endJam;
                    $task->jenis            = 'Kegiatan';
                    $task->jenis_kegiatan   = 'Pelayanan Statistik Terpadu (PST)';
                    $task->tim              = 'Diseminasi dan Layanan Statistik';
                    $task->tempat           = 'Ruang PST BPS Provinsi Sulawesi Tenggara';
                    $isPast = \Carbon\Carbon::parse($tglStr)->lt(\Carbon\Carbon::today());
                    $task->status           = $isPast ? 'Selesai' : 'Sedang Berjalan';
                    $task->progress         = $isPast ? 100 : 0;
                    $task->setuju_rapat     = 1;
                }

                $task->agenda           = $agenda;
                $task->penanggung_jawab = $namesOnly[0] ?? 'Ketua Tim PST';
                $task->owners           = json_encode($nipsOnly);
                $task->save();

                // Simpan penugasan petugas
                foreach ($petugasList as $p) {
                    $existingP = \App\penugasan::where('id_kegiatan', $task->id)
                        ->where(function($q) use ($p) {
                            $q->where('niplama', $p['nip'])
                              ->orWhere('peserta', $p['nama']);
                        })->first();

                    if (!$existingP) {
                        $existingP = new \App\penugasan();
                        $existingP->id_kegiatan = $task->id;
                        $existingP->niplama     = $p['nip'];
                        $existingP->peserta     = $p['nama'];
                    }

                    $existingP->keterangan       = "Petugas PST Sesi " . $sesi;
                    $existingP->status_kehadiran = 'Hadir';
                    $existingP->waktu_kehadiran  = $tglStr . ' ' . $startJam . ':00';
                    $existingP->save();
                }

                $importedCount++;
            }

            return redirect()->route('kegiatan.daftar')->with('success', "Berhasil mengimpor {$importedCount} sesi jadwal Petugas PST (Senin - Jumat) dari template Starla! Jadwal kini tampil di kalender dashboard.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses template PST: ' . $e->getMessage());
        }
    }
}