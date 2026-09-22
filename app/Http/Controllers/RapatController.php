<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Task;
use App\User;
use App\penugasan;
use App\group;
use App\master_group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

class RapatController extends Controller
{
    /**
     * Menampilkan form buat rapat baru dengan data pendukung
     */
    public function index()
    {
        // 1. Data Pegawai BPS untuk Pemimpin, Notulis, Dokumentasi, & Peserta (Non-Admin)
        $peserta = User::getPegawaiBps();
        $allUsers = $peserta;
        $eligiblePJs = User::getEligiblePJs();
        
        // 2. Data Kegiatan Ketua Tim untuk relasi rapat & Daftar Ketua Tim (PJ)
        $kegiatans = DB::table('agenda_ketua_tim')->orderBy('created_at', 'desc')->get();
        $ketua_tims = DB::table('agenda_ketua_tim')
            ->whereNotNull('pj')
            ->where('pj', '!=', '')
            ->where('pj', '!=', 'Administrator')
            ->pluck('pj')
            ->unique()
            ->values();

        // 3. Data Ruangan / Venues untuk mode Offline/Hybrid
        $venues = DB::table('venues')->get();
        if ($venues->isEmpty()) {
            // Fallback default venues jika tabel belum terisi data
            $venues = collect([
                (object)['id' => 1, 'name' => 'Aula Utama BPS', 'capacity' => 100, 'description' => 'Lantai 1 Gedung Utama'],
                (object)['id' => 2, 'name' => 'Ruang Rapat Pimpinan', 'capacity' => 30, 'description' => 'Lantai 2 Gedung Utama'],
                (object)['id' => 3, 'name' => 'Ruang Rapat Kecil (Vicon)', 'capacity' => 15, 'description' => 'Lantai 1 Dekat Pelayanan'],
                (object)['id' => 4, 'name' => 'Ruang Studio Diseminasi', 'capacity' => 20, 'description' => 'Lantai 2 Sayap Timur'],
            ]);
        }

        // 4. Pengelompokan Peserta per Divisi/Kelompok (Hanya Pegawai BPS)
        $groups = DB::table('groups')
            ->join('users', 'users.niplama', '=', 'groups.niplama')
            ->where('users.username', '!=', 'admin')
            ->where('users.nama_lengkap', '!=', 'Administrator')
            ->select('groups.grup', 'users.*')
            ->get();

        $master_groups = DB::table('master_groups')->get();

        // 5. ID task terakhir untuk referensi penugasan
        $latestTask = Task::latest()->first();
        $id = $latestTask ? $latestTask->id : 0;

        // 6. Notifikasi bawaan
        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('rapat', compact(
            'id',
            'peserta',
            'allUsers',
            'eligiblePJs',
            'kegiatans',
            'ketua_tims',
            'venues',
            'groups',
            'master_groups',
            'notifications',
            'jumlah_notif'
        ));
    }

    /**
     * API json untuk mendapatkan daftar pegawai
     */
    public function getPegawai(Request $request)
    {
        $a = Session::get('key');
        $b = Session::get('key2');
   
        $cities = DB::table("users_jabatan")
            ->join('users', 'users.id', '=', 'users_jabatan.id_users')
            ->leftJoin('kegiatans', DB::raw('find_in_set(users_jabatan.id_users, 
                REPLACE(REPLACE(REPLACE(kegiatans.peserta, "[", "" ),\'"\',""),"]","" ))
                AND kegiatans.start BETWEEN "'.$a.'" AND "'.$b.'" '),
                ">", DB::raw("'0'"))
            ->where("kegiatans.peserta", NULL)
            ->where("users_jabatan.id_satker", 7400)
            ->where("id_organisasi", $request->id_organisasi)
            ->groupBy('users_jabatan.id_users')
            ->groupBy('users.nama_lengkap')
            ->pluck("nama_lengkap", "token_google");

        return response()->json($cities);
    }

    /**
     * Menyimpan data rapat baru sesuai diagram alur sistem
     */
    public function store_rapat(Request $request)
    {
        // 1. Validasi Input Lengkap
        $request->validate([
            'text'            => 'required|string|max:255', // Topik Rapat
            'agenda'          => 'required|string',
            'start_date'      => 'required|date',
            'start_jam'       => 'required',
            'end_jam'         => 'required',
            'tipe_tempat'     => 'required|in:online,offline,hybrid',
            'pemimpin'        => 'required|string',
            'notulis'         => 'required|string',
            'tim_dokumentasi' => 'nullable|string',
            'owners'          => 'required|array|min:1', // Minimal 1 peserta
            'undangan_file'   => 'nullable|mimes:pdf|max:10240', // File PDF undangan jika diunggah
            'materi_file'     => 'nullable|mimes:pdf,ppt,pptx,doc,docx,zip,rar|max:20480',
        ], [
            'text.required'        => 'Topik rapat wajib diisi.',
            'agenda.required'      => 'Agenda pembahasan rapat wajib diisi.',
            'start_date.required'  => 'Tanggal pelaksanaan rapat wajib diisi.',
            'start_jam.required'   => 'Jam mulai rapat wajib diisi.',
            'end_jam.required'     => 'Jam akhir rapat wajib diisi.',
            'pemimpin.required'    => 'Silakan pilih Pemimpin Rapat.',
            'notulis.required'     => 'Silakan pilih Notulis Rapat.',
            'owners.required'      => 'Silakan pilih/centang minimal satu peserta rapat.',
            'undangan_file.mimes'  => 'Berkas Undangan harus berformat PDF.',
            'materi_file.mimes'    => 'Berkas Materi harus berformat PDF, PPT, Word, atau Arsip (ZIP/RAR).',
        ]);

        // 2. Format Lokasi & Tempat
        $tempatDesc = '';
        if ($request->tipe_tempat === 'online') {
            $tempatDesc = 'Online (' . ($request->link_meeting ?? 'Zoom / Google Meet') . ')';
        } elseif ($request->tipe_tempat === 'offline') {
            $tempatDesc = 'Offline: ' . ($request->tempat_offline ?? 'Ruang Rapat');
        } elseif ($request->tipe_tempat === 'hybrid') {
            $tempatDesc = 'Hybrid: ' . ($request->tempat_offline ?? 'Ruang Rapat') . ' & Online (' . ($request->link_meeting ?? 'Link Meeting') . ')';
        }

        // 3. Handle File Uploads ke folder public/documents
        $path = public_path('documents' . DIRECTORY_SEPARATOR);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $undanganName = null;
        if ($request->hasFile('undangan_file')) {
            $undanganName = 'undangan_' . time() . '_' . Str::slug(pathinfo($request->file('undangan_file')->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $request->file('undangan_file')->getClientOriginalExtension();
            $request->file('undangan_file')->move($path, $undanganName);
        }

        $materiName = null;
        if ($request->hasFile('materi_file')) {
            $materiName = 'materi_' . time() . '_' . Str::slug(pathinfo($request->file('materi_file')->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $request->file('materi_file')->getClientOriginalExtension();
            $request->file('materi_file')->move($path, $materiName);
        }

        // 4. Hitung Durasi Rapat (Tanggal tunggal pelaksanaan)
        $startDate   = $request->input('start_date');
        $dateAkhir   = $request->input('date_akhir', $startDate) ?: $startDate;
        $durasi      = 1;

        $pegawai = $request->input('owners', []);
        $pegawaiStr = is_array($pegawai) ? implode(',', $pegawai) : (string)$pegawai;

        $pjNama = $request->penanggung_jawab ?: ($request->pemimpin ?: (Auth::user()->nama_lengkap ?? Auth::user()->username));

        // 5. Simpan Record Task / Rapat
        $task = new Task();
        $task->text              = $request->text;
        $task->agenda            = $request->agenda;
        $task->tempat            = $tempatDesc;
        $task->pemimpin          = $request->pemimpin;
        $task->notulis           = $request->notulis;
        $task->tim_dokumentasi   = $request->tim_dokumentasi;
        $task->penanggung_jawab  = $pjNama;
        $task->start_date        = $startDate;
        $task->date_akhir        = $dateAkhir;
        $task->start_jam         = $request->start_jam;
        $task->end_jam           = $request->end_jam;
        $task->duration          = $durasi;
        $task->owners            = $pegawaiStr;
        $task->jenis             = 'Rapat';
        $task->jenis_kegiatan    = $request->is_kegiatan == '1' ? 'Terkait Kegiatan' : 'Non-Kegiatan';
        $task->parent_id         = $request->kegiatan_id ?? null;
        $task->status            = 'Menunggu Persetujuan'; // Menunggu approval pemimpin rapat
        $task->status_pemimpin   = 'Menunggu';
        $task->surat             = $undanganName ?? $request->link;
        $task->materi_link       = $materiName ? url('documents/' . $materiName) : $request->materi_link;
        $task->venue_id          = $request->venue_id ?? null;
        $task->progress          = 0;
        $task->save();

        $taskId = $task->id;

        // 6. Simpan Penugasan untuk seluruh peserta rapat
        $kunciFcm = [];
        foreach ($pegawai as $nip) {
            $tugas = new penugasan();
            $tugas->id_kegiatan = $taskId;
            $tugas->niplama     = $nip;
            $tugas->save();

            // Cari User penerima notifikasi
            $user = User::where('niplama', $nip)->first();
            if ($user && $user->id !== Auth::id()) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\Notifications\UndanganRapatNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $user->id,
                    'data'            => json_encode([
                        'judul' => 'Undangan Rapat: ' . $request->text,
                        'pesan' => 'Anda diundang oleh ' . $pjNama . ' (Ketua Tim / PJ) untuk mengikuti rapat "' . $request->text . '" pada ' . date('d M Y', strtotime($request->start_date)) . ' pukul ' . substr($request->start_jam, 0, 5) . ' WITA. Tempat: ' . $tempatDesc,
                        'url'   => '/rapat/tiket-qr/' . $taskId,
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                if (!empty($user->token_google)) {
                    $kunciFcm[] = $user->token_google;
                }
            }
        }

        // 7. Notifikasi Khusus untuk Pemimpin Rapat (Untuk Persetujuan)
        $userPemimpin = User::where('nama_lengkap', $request->pemimpin)->orWhere('username', $request->pemimpin)->first();
        if ($userPemimpin && $userPemimpin->id !== Auth::id()) {
            DB::table('notifications')->insert([
                'id'              => (string) Str::uuid(),
                'type'            => 'App\Notifications\ApprovalRapatNotification',
                'notifiable_type' => 'App\User',
                'notifiable_id'   => $userPemimpin->id,
                'data'            => json_encode([
                    'judul' => 'Permohonan Persetujuan Rapat',
                    'pesan' => 'Anda ditunjuk sebagai Pemimpin Rapat untuk "' . $request->text . '" oleh ' . $pjNama . '. Silakan periksa dan berikan persetujuan pelaksanaan rapat.',
                    'url'   => '/daftar_kegiatan',
                ]),
                'read_at'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // 7b. Notifikasi Khusus untuk Penanggung Jawab (PJ) jika ditunjuk oleh Admin / User Lain
        if (!empty($pjNama) && $pjNama !== $request->pemimpin) {
            $userPj = User::where('nama_lengkap', $pjNama)->orWhere('username', $pjNama)->first();
            if ($userPj && $userPj->id !== Auth::id() && (!$userPemimpin || $userPemimpin->id !== $userPj->id)) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\Notifications\PenugasanKegiatanNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $userPj->id,
                    'data'            => json_encode([
                        'judul' => 'Penunjukan Penanggung Jawab Rapat: ' . $request->text,
                        'pesan' => 'Anda ditunjuk sebagai Penanggung Jawab (PJ) untuk rapat "' . $request->text . '" pada ' . date('d M Y', strtotime($request->start_date)) . '.',
                        'url'   => '/daftar_kegiatan',
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // 8. Notifikasi Khusus untuk Notulis
        $userNotulis = User::where('nama_lengkap', $request->notulis)->orWhere('username', $request->notulis)->first();
        if ($userNotulis && $userNotulis->id !== Auth::id()) {
            DB::table('notifications')->insert([
                'id'              => (string) Str::uuid(),
                'type'            => 'App\Notifications\NotulisRapatNotification',
                'notifiable_type' => 'App\User',
                'notifiable_id'   => $userNotulis->id,
                'data'            => json_encode([
                    'judul' => 'Penugasan Notulis (Menunggu Persetujuan)',
                    'pesan' => 'Anda ditugaskan sebagai Notulis pada rapat "' . $request->text . '" oleh ' . $pjNama . '. Status saat ini sedang menunggu persetujuan pemimpin rapat.',
                    'url'   => '/daftarkegiatan/' . $task->id,
                ]),
                'read_at'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // 8b. Notifikasi Khusus untuk Tim Dokumentasi
        if (!empty($request->tim_dokumentasi)) {
            $userDok = User::where('nama_lengkap', $request->tim_dokumentasi)->orWhere('username', $request->tim_dokumentasi)->first();
            if ($userDok && $userDok->id !== Auth::id()) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\Notifications\PeranKhususNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $userDok->id,
                    'data'            => json_encode([
                        'judul' => 'Penugasan Tim Dokumentasi (Menunggu Persetujuan)',
                        'pesan' => 'Anda ditugaskan sebagai Tim Dokumentasi pada rapat "' . $request->text . '" oleh ' . $pjNama . '. Status saat ini sedang menunggu persetujuan pemimpin rapat.',
                        'url'   => '/daftarkegiatan/' . $task->id,
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // 9. Kirim Push Notification ke Google FCM jika token tersedia
        if (!empty($kunciFcm)) {
            $path_to_fcm = "https://fcm.googleapis.com/fcm/send";
            $server_key  = "AAAAt-obBhI:APA91bG4Dp9xeJoq7HmrZ1aQbhfErrBUdDk-kczCgo9wiFvQubkuYuPpQ5knzqDw4gnPt9bikoQpLyAjvzpoIBH_65mK7gY5EK7J-u9GI4KkOnL4z2x4Haxr0_oRD91yLiRHMCT27KYM";

            $headers = [
                "Authorization:key=" . $server_key,
                "Content-Type:application/json",
            ];

            $fields = [
                "registration_ids" => $kunciFcm,
                "priority"         => "normal",
                "notification"     => [
                    "title"              => 'Undangan Rapat: ' . $request->text,
                    "body"               => 'Jadwal: ' . date('d M Y', strtotime($request->start_date)) . ' ' . $request->start_jam,
                    "android_channel_id" => "default",
                ],
            ];

            $payload = json_encode($fields);

            $curl_session = curl_init();
            curl_setopt($curl_session, CURLOPT_URL, $path_to_fcm);
            curl_setopt($curl_session, CURLOPT_POST, true);
            curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
            curl_exec($curl_session);
            curl_close($curl_session);
        }

        return redirect()->route('booking-ruangan.index', ['rapat_id' => $task->id, 'date' => $task->start_date])
            ->with('success', 'Rapat "' . $request->text . '" berhasil diterbitkan! Silakan konfirmasi pilihan Ruangan Rapat & Akun Zoom di bawah ini.');
    }
}