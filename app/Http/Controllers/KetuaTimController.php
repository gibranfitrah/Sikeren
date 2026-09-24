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
            'tim'          => 'required|string',
            'pj'           => 'required|string',
            'wilayah'      => 'nullable|array',
            'anggota'      => 'nullable|array',
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->date_akhir ?: $startDate;

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
                        'url'   => '/tugas-saya',
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
            'jam'          => '08:00:00',
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
}