<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\kegiatan;
use App\penugasan;
use App\master_organisasi;
use App\user_jabatan;
use App\city;
use App\state;
use App\Task;
use Illuminate\Support\Str;
use Validator;
use PDF;
use App\User;
use DB;
use Session;
use Auth;
use App\Notification;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\group;
use App\master_group;

class KegiatanController extends Controller
{
    public function index(Request $request) 
    {
        $peserta       = User::all();
        $calon_peserta = User::all();
        $category      = DB::table("master_organisasi")->pluck("nm_organisasi", "id");
        
        $start = $request->input('start');
        $end   = $request->input('end');

        Session::put('key', $start);
        Session::put('key2', $end); 

        $a = Session::get('key');
        $b = Session::get('key2');

        $groups        = group::join('users', 'users.niplama', 'groups.niplama')->get();
        $master_groups = master_group::all();

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('kegiatan', compact(
            'peserta',
            'calon_peserta',
            'category',
            'start',
            'end',
            'groups',
            'master_groups',
            'notifications',
            'jumlah_notif'
        ));
    }

    public function index_penugasan(Request $request) 
    {
        $peserta       = User::all();
        $calon_peserta = User::all();
        $id            = Task::latest()->first()->id ?? null;
        $category      = DB::table("master_organisasi")->pluck("nm_organisasi", "id");
        
        $start = $request->input('start');
        $end   = $request->input('end');

        Session::put('key', $start);
        Session::put('key2', $end); 

        $a = Session::get('key');
        $b = Session::get('key2');

        $groups        = group::join('users', 'users.niplama', 'groups.niplama')->get();
        $master_groups = master_group::all();

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('penugasan', compact(
            'id',
            'peserta',
            'calon_peserta',
            'category',
            'start',
            'end',
            'groups',
            'master_groups',
            'notifications',
            'jumlah_notif'
        ));
    }   

    public function search(Request $request)
    {
        $search = $request->get('term');
        $result = User::where('nama_lengkap', 'LIKE', '%'. $search. '%')->get();
        return response()->json($result);
    } 

    public function getPegawai(Request $request)
    {
        $a = Session::get('key');
        $b = Session::get('key2');

        $cities = DB::table("users_jabatan")
            ->join('users', 'users.id', '=', 'users_jabatan.id_users')
            ->leftJoin('kegiatans', DB::raw('find_in_set(users_jabatan.id_users, 
                REPLACE(REPLACE(REPLACE(kegiatans.peserta, "[", "" ),\'"\',""),"]","" ))
                AND kegiatans.start BETWEEN "'.$a.'" AND "'.$b.'" '),
                ">", \DB::raw("'0'"))
            ->where("kegiatans.peserta", NULL)
            ->where("users_jabatan.id_satker", 7400)
            ->where("id_organisasi", $request->id_organisasi)
            ->groupBy('users_jabatan.id_users')
            ->groupBy('users.nama_lengkap')
            ->pluck("nama_lengkap", "token_google");

        return response()->json($cities);
    }

    public static function getQrUrl($path = '')
    {
        $host   = request()->getHost();
        $port   = request()->getPort();
        $scheme = request()->getScheme();

        // Jika diakses via localhost / 127.0.0.1, gunakan IP lokal LAN (Wi-Fi) agar HP bisa memindai & membuka halaman
        if ($host === '127.0.0.1' || $host === 'localhost') {
            $localIp = gethostbyname(gethostname());
            if ($localIp && $localIp !== '127.0.0.1') {
                $host = $localIp;
            }
        }

        $portStr = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
        return $scheme . '://' . $host . $portStr . '/' . ltrim($path, '/');
    }

    public function generate($id)
    {
        $task = Task::find($id) ?? kegiatan::find($id);
        $urlHadir = self::getQrUrl('daftarhadir/' . $id);
        $qrcode = QrCode::size(340)->generate($urlHadir);
        return view('qrcode', compact('qrcode', 'task', 'id', 'urlHadir'));
    }

        public function index2() 
    {
        $user     = Auth::user();
        $nip      = $user->niplama ?? '';
        $nama     = $user->nama_lengkap ?? '';
        $username = $user->username ?? '';

        $taskIdsFromPenugasan = \App\penugasan::where('niplama', $nip)
            ->orWhere('peserta', $nip)
            ->orWhere('peserta', 'LIKE', '%' . $nama . '%')
            ->pluck('id_kegiatan')
            ->filter()
            ->unique()
            ->toArray();

        // Query all rapat & kegiatan
        // Ketua tim can see all, or user filter if not ketua tim
        $kegiatans = Task::with('subKegiatans')
            ->where('tasks.jenis', 'Rapat')
            ->orderBy('tasks.id', 'desc')
            ->get();

        $kegiatans2 = Task::with('subKegiatans')
            ->where('tasks.jenis', 'Kegiatan')
            ->orderBy('tasks.id', 'desc')
            ->get();

        $qrcode2 = base64_encode(QrCode::format('svg')->size(400)->generate("''74caca''"));
        $a       = 1;  
        
        $master_groups = \App\master_group::all();
        $all_users = User::orderBy('nama_lengkap', 'asc')->get();
        $usersByGroup = \App\group::join('users', 'users.niplama', '=', 'groups.niplama')
            ->select('groups.grup', 'users.id', 'users.niplama', 'users.nama_lengkap', 'users.username')
            ->get()
            ->groupBy('grup');

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('daftar_kegiatan', compact(
            'kegiatans2',
            'kegiatans',
            'a',
            'qrcode2',
            'master_groups',
            'all_users',
            'usersByGroup',
            'notifications',
            'jumlah_notif'
        ));
    }

    public function index3() 
    {
        $kegiatans     = Task::all();
        $a             = 1;    
        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('notulis', compact(
            'kegiatans',
            'a',
            'notifications',
            'jumlah_notif'
        ));
    }
        
    public function displayKegiatan($id)
    {
        $kegiatans = penugasan::join('tasks', 'penugasans.id_kegiatan', '=', 'tasks.id')
            ->join('users', 'penugasans.niplama', '=', 'users.niplama')
            ->where('tasks.id', $id)
            ->select(
                'tasks.text',
                'tasks.agenda',
                'tasks.tempat',
                'tasks.start_date',
                'tasks.date_akhir',
                'tasks.start_jam',
                'tasks.end_jam',
                'tasks.pemimpin',
                'tasks.notulis',
                'users.nama_lengkap'
            )
            ->get();

        return view('displayKegiatan', compact('kegiatans'));
    }
            
    public function store_notulen(Request $request)
    {
        $user = kegiatan::find($request->id);
        $path = public_path('documents'.DIRECTORY_SEPARATOR);

        if ($request->hasFile('notulen')) {
            $name = time(). '.' . $request->file('notulen')->getClientOriginalName(); 
            $user->notulen = $name;
            $request->file('notulen')->move($path, $name);
        }

        if ($request->hasFile('materi')) {
            $name_2 = time(). '.' . $request->file('materi')->getClientOriginalName();
            $user->materi = $name_2;
            $request->file('materi')->move($path, $name_2);
        }
        
        if ($request->hasFile('foto')) {
            $name_3 = time(). '.' . $request->file('foto')->getClientOriginalName(); 
            $user->foto = $name_3;
            $request->file('foto')->move($path, $name_3);
        }
        
        if (!is_null($request->materi_link)) {
            $user->materi_link = $request->materi_link;
        }

        if (!is_null($request->foto_link)) {
            $user->foto_link = $request->foto_link;
        }

        $user->save();

        return back()->with(['success' => 'Upload Berhasil']);
    }   

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required',
            'agenda'   => 'required',
            'tempat'   => 'required',
            'start'    => 'required',
            'end'      => 'required',
            'pemimpin' => 'required',
            'notulis'  => 'required',
            'peserta'  => 'required',
        ]);
         
        $input = $request->all();
        $input['peserta'] = $request->input('peserta');

        kegiatan::create($input);

        return redirect()->back()->with(['success' => 'Berhasil Buat'])->withInput($request->all());
    }

    public function store_penugasan(Request $request)
    {
        $input = $request->all();
        $input['surat'] = $request->link;
        $pegawai = $request->input('owners', []);
        $pegawai2 = is_array($pegawai) ? implode(',', $pegawai) : (string)$pegawai;
        
        $path = public_path('documents'.DIRECTORY_SEPARATOR);

        if ($request->hasFile('surat')) {
            $name = time(). '.' . $request->file('surat')->getClientOriginalName(); 
            $input['surat'] = $name;
            $request->file('surat')->move($path, $name);
        }

        $input['jenis']  = 'Kegiatan';
        $input['status'] = 'Belum';
        $input['owners'] = $pegawai2;
        
        $input['jenis_kegiatan']   = $request->input('jenis_kegiatan', 'Non-Rapat');
        $input['start_jam']        = $request->input('start_jam');
        $input['end_jam']          = $request->input('end_jam');
        $input['pemimpin']         = $request->input('pemimpin');
        $input['notulis']          = $request->input('notulis');
        $input['tim_dokumentasi']  = $request->input('tim_dokumentasi');
        $input['penanggung_jawab'] = $request->input('penanggung_jawab');
        $input['parent_id']        = $request->input('parent_id');
        $input['surat_id']         = $request->input('surat_id');
        
        if ($input['jenis_kegiatan'] == 'Non-Rapat') {
            $input['tempat'] = $request->input('jenis_tujuan') == 'kabupaten' ? $request->input('tujuan') : 'Provinsi';
        } else {
            if ($request->input('tempat')) {
                $bentrok = Task::where('tempat', $request->input('tempat'))
                    ->where('start_date', $request->input('start_date'))
                    ->where(function($query) use ($request) {
                        $query->whereBetween('start_jam', [$request->input('start_jam'), $request->input('end_jam')])
                              ->orWhereBetween('end_jam', [$request->input('start_jam'), $request->input('end_jam')])
                              ->orWhere(function($q) use ($request) {
                                  $q->where('start_jam', '<=', $request->input('start_jam'))
                                    ->where('end_jam', '>=', $request->input('end_jam'));
                              });
                    })->exists();

                if ($bentrok) {
                    return redirect()->back()->withErrors(['tempat' => 'Tempat yang sudah terjadwal tidak available pada waktu tersebut! Harap hubungi Divisi Umum.'])->withInput($request->all());
                }
            }
            $input['status_pemimpin'] = 'Menunggu';
        }
        
        $start_time  = \Carbon\Carbon::parse($request->input('start_date'));
        $finish_time = \Carbon\Carbon::parse($request->input('date_akhir'));
        $durasi      = $start_time->diffInDays($finish_time, false);
        $input['duration'] = ($durasi) + 1;

        Task::create($input);

        $kunci = [];

        // Notifikasi ke seluruh peserta penugasan
        if (is_array($pegawai)) {
            foreach ($pegawai as $nip) {
                $tugas = new penugasan();
                $tugas->id_kegiatan = $request->tes;
                $tugas->niplama     = $nip;
                $tugas->save();

                $user = User::where('niplama', $nip)->first();
                if ($user) {
                    DB::table('notifications')->insert([
                        'id'              => (string) Str::uuid(),
                        'type'            => 'App\Notifications\PenugasanNotification',
                        'notifiable_type' => 'App\Models\User',
                        'notifiable_id'   => $user->id,
                        'data'            => json_encode([
                            'judul' => 'Penugasan Baru',
                            'pesan' => 'Anda mendapat penugasan: ' . ($request->text ?? $request->agenda),
                            'url'   => '/daftar_kegiatan',
                        ]),
                        'read_at'         => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    if (!empty($user->token_google)) {
                        $kunci[] = $user->token_google;
                    }
                }
            }
        }

        // Notifikasi Peran Khusus
        $roles_to_notify = [
            ['role' => 'Pemimpin Rapat', 'name' => $request->input('pemimpin'), 'msg' => 'Anda ditunjuk sebagai Pemimpin Rapat. Harap tinjau kegiatan ini.'],
            ['role' => 'Notulis', 'name' => $request->input('notulis'), 'msg' => 'Anda ditunjuk sebagai Notulis untuk rapat ini.'],
            ['role' => 'Tim Dokumentasi', 'name' => $request->input('tim_dokumentasi'), 'msg' => 'Anda ditunjuk sebagai Tim Dokumentasi untuk kegiatan ini.'],
            ['role' => 'Penanggung Jawab', 'name' => $request->input('penanggung_jawab'), 'msg' => 'Anda ditunjuk sebagai Penanggung Jawab untuk kegiatan ini.']
        ];

        foreach ($roles_to_notify as $role) {
            if (!empty($role['name'])) {
                $userRole = User::where('nama_lengkap', $role['name'])->first();
                if ($userRole) {
                    DB::table('notifications')->insert([
                        'id'              => (string) Str::uuid(),
                        'type'            => 'App\Notifications\PeranKhususNotification',
                        'notifiable_type' => 'App\Models\User',
                        'notifiable_id'   => $userRole->id,
                        'data'            => json_encode([
                            'judul' => 'Penugasan ' . $role['role'],
                            'pesan' => $role['msg'] . ' Topik: ' . $request->text,
                            'url'   => '/daftar_kegiatan',
                        ]),
                        'read_at'         => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        // Kirim FCM Push Notification
        if (!empty($kunci)) {
            $path_to_fcm = "https://fcm.googleapis.com/fcm/send";
            $server_key  = "AAAAt-obBhI:APA91bG4Dp9xeJoq7HmrZ1aQbhfErrBUdDk-kczCgo9wiFvQubkuYuPpQ5knzqDw4gnPt9bikoQpLyAjvzpoIBH_65mK7gY5EK7J-u9GI4KkOnL4z2x4Haxr0_oRD91yLiRHMCT27KYM";

            $headers = [
                "Authorization:key=" . $server_key,
                "Content-Type:application/json",
            ];

            $fields = [
                "registration_ids" => $kunci,
                "priority"         => "normal",
                "notification"     => [
                    "title"              => $request->text,
                    "body"               => $request->agenda,
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

        return redirect()->back()->with(['success' => 'Berhasil Membuat Penugasan'])->withInput($request->all());
    }

    public function createPDF(Request $request, $id) 
    {
        $data = Task::findOrFail($id);

        $data2 = User::select(['users.niplama as a'])
            ->join('tasks', 'users.nama_lengkap', '=', 'tasks.pemimpin')
            ->where('tasks.id', $id)
            ->get();

        $data3 = '';
        foreach ($data2 as $b) {
            $data3 = $b->a;
        }
        $data4 = (string)$data->id;
        
        $qrcode  = base64_encode(QrCode::format('svg')->size(100)->generate($data4));
        $qrcode2 = base64_encode(QrCode::format('svg')->size(100)->generate('https://webapps.bps.go.id/sultra/idcard/'.$data3));
      
        view()->share('employee', $data);
        $pdf = \PDF::loadView('pdf_kegiatan', $data, compact('qrcode', 'qrcode2'));
  
        return $pdf->stream('pdf.pdf', array("Attachment" => 0));
    }

    public function daftarHadir($id) 
    {
        $task = is_numeric($id) ? Task::find($id) : null;
        if (!$task) {
            $task = Task::where('id', $id)
                ->orWhere('text', $id)
                ->orWhere('text', urldecode($id))
                ->first() ?? kegiatan::find($id);
        }

        $realId = $task ? $task->id : $id;
        $user   = Auth::user();
        
        $pesertaList = collect();
        if ($task) {
            $pesertaList = penugasan::leftJoin('users', 'penugasans.niplama', '=', 'users.niplama')
                ->where('penugasans.id_kegiatan', $realId)
                ->select(
                    'penugasans.id as id_penugasan',
                    'penugasans.peserta as nama_peserta_custom',
                    'penugasans.niplama',
                    'penugasans.created_at as waktu_hadir',
                    'penugasans.updated_at',
                    'users.nama_lengkap',
                    'users.nipbaru'
                )
                ->orderBy('penugasans.updated_at', 'desc')
                ->get()
                ->map(function ($p) {
                    return (object)[
                        'id_penugasan' => $p->id_penugasan,
                        'nama_lengkap' => $p->nama_lengkap ?: $p->nama_peserta_custom,
                        'niplama'      => $p->niplama,
                        'nipbaru'      => $p->nipbaru,
                        'waktu_hadir'  => $p->updated_at ?: $p->waktu_hadir,
                    ];
                });

            if ($pesertaList->isEmpty() && !empty($task->owners)) {
                $rawOwners = $task->owners;
                $ownerList = [];
                if (is_array($rawOwners)) {
                    $ownerList = $rawOwners;
                } elseif (is_string($rawOwners) && !empty($rawOwners)) {
                    $decoded = json_decode($rawOwners, true);
                    if (is_array($decoded)) {
                        $ownerList = $decoded;
                    } else {
                        $ownerList = array_filter(array_map('trim', explode(',', $rawOwners)));
                    }
                }

                if (!empty($ownerList)) {
                    $users = User::whereIn('niplama', $ownerList)
                        ->orWhereIn('nama_lengkap', $ownerList)
                        ->get();

                    if ($users->isNotEmpty()) {
                        $pesertaList = $users->map(function($u) {
                            return (object)[
                                'id_penugasan' => null,
                                'nama_lengkap' => $u->nama_lengkap,
                                'niplama'      => $u->niplama,
                                'nipbaru'      => $u->nipbaru,
                                'waktu_hadir'  => null,
                            ];
                        });
                    } else {
                        $pesertaList = collect($ownerList)->map(function($nameOrNip) {
                            return (object)[
                                'id_penugasan' => null,
                                'nama_lengkap' => (string)$nameOrNip,
                                'niplama'      => (string)$nameOrNip,
                                'nipbaru'      => '-',
                                'waktu_hadir'  => null,
                            ];
                        });
                    }
                }
            }
        }

        $allPegawai = User::select('niplama', 'nipbaru', 'nama_lengkap')
            ->orderBy('nama_lengkap', 'ASC')
            ->get();

        return view('daftar_hadir', compact('task', 'user', 'id', 'realId', 'pesertaList', 'allPegawai'));
    }

    public function submitDaftarHadir(Request $request)
    {
        $idKegiatan = $request->input('id_kegiatan');
        $niplama    = $request->input('niplama');
        $namaManual = trim($request->input('peserta_manual', ''));

        if (empty($niplama) && empty($namaManual)) {
            return redirect()->back()->with('error_presensi', 'Silakan pilih nama pegawai atau masukkan nama Anda.');
        }

        $nama = $namaManual;
        if (!empty($niplama)) {
            $u = User::where('niplama', $niplama)->orWhere('nipbaru', $niplama)->first();
            if ($u) {
                $nama    = $u->nama_lengkap;
                $niplama = $u->niplama;
            }
        }

        // Cek apakah sudah ada di penugasans untuk kegiatan ini
        $penugasan = penugasan::where('id_kegiatan', $idKegiatan)
            ->where(function ($q) use ($niplama, $nama) {
                if (!empty($niplama) && $niplama !== '-') {
                    $q->where('niplama', $niplama);
                } else {
                    $q->where('peserta', $nama);
                }
            })
            ->first();

        if ($penugasan) {
            $penugasan->updated_at = now();
            if ($nama && (empty($penugasan->peserta) || $penugasan->peserta === '-')) {
                $penugasan->peserta = $nama;
            }
            if ($niplama && empty($penugasan->niplama)) {
                $penugasan->niplama = $niplama;
            }
            $penugasan->save();
        } else {
            penugasan::create([
                'id_kegiatan' => $idKegiatan,
                'niplama'     => $niplama ?: '-',
                'peserta'     => $nama ?: 'Peserta',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()->back()->with('success_presensi', 'Presensi kehadiran berhasil dicatat! Terima kasih, ' . ($nama ?: 'Peserta') . '.');
    }

    public function approve(Request $request, $id) 
    {
        $kegiatan = Task::findOrFail($id);
        $kegiatan->status_pemimpin = 'Disetujui';
        $kegiatan->save();

        $pembuat = User::where('nama_lengkap', $kegiatan->nama_lengkap)->first();
        if ($pembuat) {
            DB::table('notifications')->insert([
                'id'              => (string) Str::uuid(),
                'type'            => 'App\Notifications\RapatStatusNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id'   => $pembuat->id,
                'data'            => json_encode([
                    'judul' => 'Rapat Disetujui',
                    'pesan' => 'Pemimpin Rapat telah menyetujui kegiatan: ' . $kegiatan->text,
                    'url'   => '/detail_kegiatan/' . $kegiatan->id,
                ]),
                'read_at'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Kegiatan berhasil disetujui.');
    }

    public function reject(Request $request, $id) 
    {
        $kegiatan = Task::findOrFail($id);
        $kegiatan->status_pemimpin = 'Ditolak';
        $kegiatan->save();

        $pembuat = User::where('nama_lengkap', $kegiatan->nama_lengkap)->first();
        if ($pembuat) {
            DB::table('notifications')->insert([
                'id'              => (string) Str::uuid(),
                'type'            => 'App\Notifications\RapatStatusNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id'   => $pembuat->id,
                'data'            => json_encode([
                    'judul' => 'Rapat Ditolak',
                    'pesan' => 'Pemimpin Rapat menolak kegiatan: ' . $kegiatan->text . '. Alasan: ' . $request->input('alasan', 'Tidak ada alasan.'),
                    'url'   => '/detail_kegiatan/' . $kegiatan->id,
                ]),
                'read_at'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Kegiatan berhasil ditolak.');
    }
}