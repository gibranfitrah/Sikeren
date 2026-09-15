<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\kegiatan;
use App\User;
use App\Task;
use Auth;
use DB;
use Session;
use App\penugasan;
class LihatKegiatanController extends Controller
{
    //


    public function lihatKegiatan(Request $request, $id) {
        $task = is_numeric($id) ? Task::find($id) : null;
        if (!$task) {
            $task = Task::where('id', $id)
                ->orWhere('text', $id)
                ->orWhere('title', $id)
                ->orWhere('text', urldecode($id))
                ->first();
        }

        $notifications = Auth::check() ? Auth::user()->notifications()->latest()->take(5)->get() : collect();
        $jumlah_notif  = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;

        // Jika jenis bukan Rapat, tampilkan Halaman Detail Kegiatan khusus
        if ($task && $task->jenis !== 'Rapat') {
            $subKegiatans = \App\SubKegiatan::where('task_id', $task->id)->orderBy('id', 'asc')->get();
            $totalSub = $subKegiatans->count();
            $completedSub = $subKegiatans->where('status', 'Selesai')->count();
            $overallProgress = $totalSub > 0 ? round($subKegiatans->avg('progress')) : ($task->status === 'Selesai' ? 100 : 0);

            // Resolve assigned users (owners & penugasans)
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

            $penugasanNips = \App\penugasan::where('id_kegiatan', $task->id)->pluck('niplama')->filter()->toArray();
            $penugasanPeserta = \App\penugasan::where('id_kegiatan', $task->id)->pluck('peserta')->filter()->toArray();
            $combinedList = array_unique(array_merge($ownerList, $penugasanNips, $penugasanPeserta));

            $assignedUsers = collect();
            if (!empty($combinedList)) {
                $assignedUsers = User::whereIn('niplama', $combinedList)
                    ->orWhereIn('nama_lengkap', $combinedList)
                    ->orWhereIn('username', $combinedList)
                    ->get();
            }

            // Resolve PJ User
            $pjName = $task->penanggung_jawab ?? ($task->pemimpin ?? '');
            $pjUser = null;
            if (!empty($pjName)) {
                $pjUser = User::where('nama_lengkap', $pjName)
                    ->orWhere('username', $pjName)
                    ->orWhere('niplama', $pjName)
                    ->first();
            }

            $masterGroups = \App\master_group::all();
            $allUsers = User::orderBy('nama_lengkap', 'asc')->get();
            $usersByGroup = \App\group::join('users', 'users.niplama', '=', 'groups.niplama')
                ->select('groups.grup', 'users.id', 'users.niplama', 'users.nama_lengkap', 'users.username')
                ->get()
                ->groupBy('grup');

            return view('detail_kegiatan', compact(
                'task',
                'subKegiatans',
                'totalSub',
                'completedSub',
                'overallProgress',
                'assignedUsers',
                'pjUser',
                'masterGroups',
                'allUsers',
                'usersByGroup',
                'notifications',
                'jumlah_notif'
            ));
        }

        $realId = $task ? $task->id : (is_numeric($id) ? $id : 0);

        // Ambil data peserta dari tabel penugasans (termasuk yang sudah presensi)
        $existingPenugasans = penugasan::leftJoin('users', 'penugasans.niplama', '=', 'users.niplama')
            ->where('penugasans.id_kegiatan', $realId)
            ->select(
                'users.nama_lengkap as abc',
                'users.niplama as def',
                'users.nipbaru',
                'penugasans.id as id_penugasan',
                'penugasans.peserta as custom_peserta',
                'penugasans.status_kehadiran',
                'penugasans.keterangan',
                'penugasans.waktu_kehadiran',
                'penugasans.updated_at as waktu_update',
                'penugasans.created_at as waktu_create'
            )
            ->get();

        // Ambil list penugasan dari task->owners jika ada
        $rawOwners = $task ? $task->owners : null;
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

        $kegiatans = collect();
        $seen = [];

        foreach ($existingPenugasans as $p) {
            $nama = $p->abc ?: ($p->custom_peserta ?: $p->def);
            $nip  = $p->def ?: ($p->nipbaru ?: '-');
            $status = $p->status_kehadiran ?: 'Belum Hadir';

            $seen[] = $nip;
            $seen[] = $nama;

            $kegiatans->push((object)[
                'text'             => $task ? $task->text : '',
                'abc'              => $nama,
                'def'              => $nip,
                'nipbaru'          => $p->nipbaru ?: '-',
                'id_penugasan'     => $p->id_penugasan,
                'agenda'           => $task ? $task->agenda : '',
                'tempat'           => $task ? $task->tempat : '',
                'start_date'       => $task ? $task->start_date : '',
                'date_akhir'       => $task ? $task->date_akhir : '',
                'setuju_rapat'     => $task ? $task->setuju_rapat : 0,
                'id'               => $task ? $task->id : $realId,
                'start_jam'        => $task ? $task->start_jam : '',
                'end_jam'          => $task ? $task->end_jam : '',
                'pemimpin'         => $task ? $task->pemimpin : '',
                'notulis'          => $task ? $task->notulis : '',
                'tim_dokumentasi'  => $task ? $task->tim_dokumentasi : '',
                'surat'            => $task ? $task->surat : '',
                'notulen'          => $task ? $task->notulen : '',
                'materi_link'      => $task ? $task->materi_link : '',
                'foto_link'        => $task ? $task->foto_link : '',
                'notulen_selesai'  => $task ? $task->notulen_selesai : 0,
                'created_at'       => $task ? $task->created_at : now(),
                'id_keg'           => $realId,
                'status_kehadiran' => $status,
                'keterangan'       => $p->keterangan,
                'waktu_kehadiran'  => ($status !== 'Belum Hadir' && !empty($p->waktu_kehadiran)) ? $p->waktu_kehadiran : null,
            ]);
        }

        if (!empty($ownerList)) {
            $users = User::whereIn('niplama', $ownerList)
                ->orWhereIn('nipbaru', $ownerList)
                ->orWhereIn('nama_lengkap', $ownerList)
                ->get();

            foreach ($ownerList as $own) {
                $u = $users->first(function($item) use ($own) {
                    return $item->niplama == $own || $item->nipbaru == $own || strcasecmp($item->nama_lengkap, $own) === 0;
                });

                $nama = $u ? $u->nama_lengkap : (string)$own;
                $nip  = $u ? $u->niplama : (string)$own;

                if (!in_array($nip, $seen) && !in_array($nama, $seen)) {
                    $seen[] = $nip;
                    $seen[] = $nama;

                    $kegiatans->push((object)[
                        'text'             => $task ? $task->text : '',
                        'abc'              => $nama,
                        'def'              => $nip,
                        'nipbaru'          => $u ? $u->nipbaru : '-',
                        'id_penugasan'     => null,
                        'agenda'           => $task ? $task->agenda : '',
                        'tempat'           => $task ? $task->tempat : '',
                        'start_date'       => $task ? $task->start_date : '',
                        'date_akhir'       => $task ? $task->date_akhir : '',
                        'setuju_rapat'     => $task ? $task->setuju_rapat : 0,
                        'id'               => $task ? $task->id : $realId,
                        'start_jam'        => $task ? $task->start_jam : '',
                        'end_jam'          => $task ? $task->end_jam : '',
                        'pemimpin'         => $task ? $task->pemimpin : '',
                        'notulis'          => $task ? $task->notulis : '',
                        'tim_dokumentasi'  => $task ? $task->tim_dokumentasi : '',
                        'surat'            => $task ? $task->surat : '',
                        'notulen'          => $task ? $task->notulen : '',
                        'materi_link'      => $task ? $task->materi_link : '',
                        'foto_link'        => $task ? $task->foto_link : '',
                        'notulen_selesai'  => $task ? $task->notulen_selesai : 0,
                        'created_at'       => $task ? $task->created_at : now(),
                        'id_keg'           => $realId,
                        'status_kehadiran' => 'Belum Hadir',
                        'keterangan'       => null,
                        'waktu_kehadiran'  => null,
                    ]);
                }
            }
        }

        $a = 1;    
        $category = DB::table("master_organisasi")->pluck("nm_organisasi","id");

        $start = $request->input('start');
        $end = $request->input('end');

        Session::put('key', $start);
        Session::put('key2', $end); 

        $a = Session::get('key');
        $b = Session::get('key2');

        $notifications = Auth::check() ? Auth::user()->notifications()->latest()->take(5)->get() : collect();
        $jumlah_notif  = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
        $qrUrlHadir    = \App\Http\Controllers\KegiatanController::getQrUrl('daftarhadir/' . ($task ? $task->id : $id));

        return view('lihatKegiatan', compact('kegiatans', 'task', 'a', 'id', 'start', 'end', 'category', 'notifications', 'jumlah_notif', 'qrUrlHadir'));
    }
        
    public function setuju_rapat(Request $request)
    {
        $statusRapat = $request->input('setuju_rapat');
        $statusPemimpin = $statusRapat == 1 ? 'Disetujui' : ($statusRapat == 3 ? 'Ditolak' : 'Menunggu');
        $statusTeks = $statusRapat == 1 ? 'Disetujui' : ($statusRapat == 3 ? 'Ditolak' : 'Menunggu Persetujuan');

        Task::where('id', $request->input('id'))->update([
            'setuju_rapat'    => $statusRapat,
            'status_pemimpin' => $statusPemimpin,
            'status'          => $statusTeks,
        ]);

        return redirect()->back()->with(['success' => 'Berhasil memperbarui status persetujuan rapat.']);
    }    
        

        public function tambah_penugasan(Request $request)
    {
        /// membuat validasi untuk title dan content wajib diisi
   
         
        /// insert setiap request dari form ke dalam database via model
        /// jika menggunakan metode ini, maka nama field dan nama form harus sama


        $input['peserta'] = $request->input('peserta');

        $jumlah_peserta = $input['peserta'];
    

      
        for ($i = 0; $i <= count($jumlah_peserta) - 1; $i++) {
            $tugas = new penugasan;
            $tugas->id_kegiatan = $request->tes;
            $tugas->peserta = $input['peserta'][$i];
            $simpan = $tugas->save();
        }
      
         
        /// redirect jika sukses menyimpan data
        return redirect()->back()->with(['success' => 'Berhasil Membuat Penugasan'])->withInput($request->all);
    }

    public function destroy($id)
    {

        penugasan::where('id', $id)->delete();
       
   


        return redirect()->back();
    }

    public function index(Request $request)
    {
        {
            $pegawais = User::all();
        $pegawai = $request->input('peserta');

            $events = [];
            $data = task::join('penugasans', 'penugasans.id_kegiatan','=','tasks.id')
            ->where('penugasans.peserta',$pegawai )
           ->get();
            
            if($data->count()) {
                foreach ($data as $key => $value) {
                    $events[] = [
                        'title' => $value->title,
                        'allDay' => true,
                        'start' => (new \DateTime($value->start))->format('Y-m-d H:i:s'),
                        'end' => (new \DateTime($value->end.' +1 day'))->format('Y-m-d H:i:s'),
                        'color' => '#f05050',
                        'url' => url('/daftarkegiatan/' . $value->id),
                    ];
                }
            }
            return view('fullcalender', compact('events', 'pegawais'));
        }
}
public function updateNotulen(Request $request)
{
    $task = Task::findOrFail($request->id);

    if($request->has('notulen')){
        $task->notulen = $request->notulen;
        $task->notulen_selesai = 1;
    }

    if($request->has('materi_link')){
        $task->materi_link = $request->materi_link;
    }

    if($request->has('foto_link')){
        $task->foto_link = $request->foto_link;
    }

    // Ketika notulen & dokumentasi disimpan, status rapat diselesaikan
    $task->status   = 'Selesai';
    $task->progress = 100;
    $task->save();

    return back()->with('success', 'Notulen & dokumentasi berhasil disimpan. Status kegiatan rapat telah Selesai.');
}

public function detailKegiatan($id)
{
    $kegiatan = Task::findOrFail($id);

    return view('displayKegiatan', compact('kegiatan'));
}

public function setupAkunKetuaTim()
{
    $defaultPassword = 'password';
    $pjs = DB::table('agenda_ketua_tim')
        ->whereNotNull('pj')
        ->where('pj', '!=', '')
        ->pluck('pj')
        ->unique()
        ->values();

    $listKetua = $pjs->push('A. Ranuwirawan Rahim')->unique()->values();
    $createdAccounts = [];

    foreach ($listKetua as $nama) {
        $nama = trim($nama);
        if (empty($nama)) continue;

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
        $email = $slug . '@bps.go.id';

        $user = User::where('nama_lengkap', $nama)->orWhere('email', $email)->first();

        if (!$user) {
            $user = new User();
            $user->nama_lengkap = $nama;
            $user->username = $slug;
            $user->email = $email;
            $user->password = \Illuminate\Support\Facades\Hash::make($defaultPassword);
            $user->niplama = '7400' . rand(10000, 99999);
            $user->nipbaru = '19800101' . date('Y') . '01100' . rand(1, 9);
            $user->save();

            DB::table('users_jabatan')->insert([
                'id_users' => $user->id,
                'id_satker' => 7400,
                'id_organisasi' => 1,
                'id_jabatan' => 1,
                'nm_jabatan' => 'Ketua Tim / PJ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $user->nama_lengkap = $nama;
            $user->username = $slug;
            $user->password = \Illuminate\Support\Facades\Hash::make($defaultPassword);
            $user->save();
        }

        $createdAccounts[] = [
            'nama' => $nama,
            'email' => $user->email,
            'password' => $defaultPassword,
            'niplama' => $user->niplama,
        ];
    }

    $notifications = Auth::check() ? Auth::user()->notifications()->latest()->take(5)->get() : collect();
    $jumlah_notif = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;

    return view('setup_akun_sukses', compact('createdAccounts', 'notifications', 'jumlah_notif'));
}

public function switchAccount(Request $request)
{
    $email = $request->input('email');
    $user = User::where('email', $email)->first();
    if ($user) {
        Auth::login($user);
        return redirect()->back()->with(['success' => 'Berhasil beralih ke akun: ' . $user->nama_lengkap]);
    }
    return redirect()->back()->withErrors(['error' => 'Akun tidak ditemukan.']);
}
}
