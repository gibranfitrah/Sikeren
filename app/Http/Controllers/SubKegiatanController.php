<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\SubKegiatan;
use App\User;
use App\master_group;
use App\group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $taskId = $request->input('task_id');

        $query = SubKegiatan::with('task')->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama_sub', 'LIKE', '%' . $search . '%')
                  ->orWhere('pj', 'LIKE', '%' . $search . '%')
                  ->orWhere('tim', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if (!empty($taskId)) {
            $query->where('task_id', $taskId);
        }

        $subKegiatans = $query->get();

        // Master data for modal / creation
        $currentUser = Auth::user();
        $tasksQuery = Task::orderBy('id', 'desc');
        if ($currentUser && !$currentUser->isAdmin()) {
            $tasks = $tasksQuery->get()->filter(function ($t) use ($currentUser) {
                return $currentUser->canAccessDetailKegiatan($t);
            })->values();
        } else {
            $tasks = $tasksQuery->get();
        }

        $masterGroups = master_group::all();
        $allUsers = User::getPegawaiBps();
        $usersByGroup = group::join('users', 'users.niplama', '=', 'groups.niplama')
            ->where('users.username', '!=', 'admin')
            ->where('users.nama_lengkap', '!=', 'Administrator')
            ->select('groups.grup', 'users.id', 'users.niplama', 'users.nama_lengkap', 'users.username')
            ->get()
            ->groupBy('grup');

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif = Auth::user()->unreadNotifications()->count();

        return view('sub_kegiatan.index', compact(
            'subKegiatans',
            'tasks',
            'masterGroups',
            'allUsers',
            'usersByGroup',
            'search',
            'statusFilter',
            'taskId',
            'notifications',
            'jumlah_notif'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'task_id'    => 'required|exists:tasks,id',
            'nama_sub'   => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'tim'        => 'nullable|string|max:255',
            'anggota'    => 'nullable|array',
        ]);

        $task = Task::find($request->task_id);
        if (!$task) {
            return redirect()->back()->with('error', 'Kegiatan induk tidak ditemukan.');
        }

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->canAccessDetailKegiatan($task)) {
            return redirect()->back()->with('error_access', 'Akses Ditolak: Anda tidak dapat menambahkan sub-kegiatan pada kegiatan tim lain, kecuali kegiatan yang menugaskan Anda.');
        }

        $taskName = $task->text ?? 'Kegiatan';
        $taskTim  = $task->tim ?: 'Umum';
        $pjNama   = !empty($task->penanggung_jawab) ? $task->penanggung_jawab : (Auth::check() ? Auth::user()->nama_lengkap : 'Ketua Tim / PJ');

        $anggotaJson = $request->has('anggota') && is_array($request->anggota) 
            ? json_encode($request->anggota) 
            : null;

        $sub = SubKegiatan::create([
            'task_id'    => $request->task_id,
            'nama_sub'   => $request->nama_sub,
            'tim'        => $request->input('tim', $taskTim),
            'pj'         => $pjNama,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'anggota'    => $anggotaJson,
            'status'     => $request->input('status', 'Sedang Berjalan'),
            'progress'   => $request->input('progress', 0),
            'keterangan' => $request->keterangan,
        ]);

        // Send notifications to members
        if ($request->has('anggota') && is_array($request->anggota)) {
            foreach ($request->anggota as $namaAnggota) {
                $userAnggota = User::where('nama_lengkap', $namaAnggota)
                    ->orWhere('username', $namaAnggota)
                    ->orWhere('niplama', $namaAnggota)
                    ->first();
                if ($userAnggota && $userAnggota->id !== Auth::id()) {
                    DB::table('notifications')->insert([
                        'id'              => (string) Str::uuid(),
                        'type'            => 'App\Notifications\PenugasanSubKegiatanNotification',
                        'notifiable_type' => 'App\User',
                        'notifiable_id'   => $userAnggota->id,
                        'data'            => json_encode([
                            'judul' => 'Penugasan Sub Kegiatan: ' . $request->nama_sub,
                            'pesan' => 'Anda ditugaskan oleh ' . $pjNama . ' (Ketua Tim / PJ) pada sub kegiatan "' . $request->nama_sub . '" di bawah agenda ' . $taskName . '.',
                            'url'   => '/daftar_kegiatan',
                        ]),
                        'read_at'         => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        // Notifikasi ke PJ Sub Kegiatan jika orang lain
        if (!empty($request->pj)) {
            $userPjSub = User::where('nama_lengkap', $request->pj)->orWhere('username', $request->pj)->first();
            if ($userPjSub && $userPjSub->id !== Auth::id()) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\Notifications\PenugasanSubKegiatanNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $userPjSub->id,
                    'data'            => json_encode([
                        'judul' => 'Penunjukan PJ Sub Kegiatan: ' . $request->nama_sub,
                        'pesan' => 'Anda ditunjuk sebagai Penanggung Jawab untuk sub kegiatan "' . $request->nama_sub . '" (' . $taskName . ').',
                        'url'   => '/daftar_kegiatan',
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Sub Kegiatan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $sub = SubKegiatan::findOrFail($id);
        $task = $sub->task;
        $currentUser = Auth::user();
        if ($currentUser && $task && !$currentUser->canAccessDetailKegiatan($task)) {
            return redirect()->back()->with('error_access', 'Akses Ditolak: Anda tidak dapat mengubah sub-kegiatan dari tim lain.');
        }

        $request->validate([
            'nama_sub'   => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'pj'         => 'nullable|string|max:255',
            'tim'        => 'nullable|string|max:255',
            'anggota'    => 'nullable|array',
        ]);

        $anggotaJson = $request->has('anggota') && is_array($request->anggota) 
            ? json_encode($request->anggota) 
            : $sub->anggota;

        $sub->update([
            'nama_sub'   => $request->nama_sub,
            'tim'        => $request->tim,
            'pj'         => $request->pj,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'anggota'    => $anggotaJson,
            'status'     => $request->input('status', $sub->status),
            'progress'   => $request->input('progress', $sub->progress),
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Sub Kegiatan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $sub = SubKegiatan::findOrFail($id);
        $task = $sub->task;
        $currentUser = Auth::user();
        if ($currentUser && $task && !$currentUser->canAccessDetailKegiatan($task)) {
            return redirect()->back()->with('error_access', 'Akses Ditolak: Anda tidak dapat menghapus sub-kegiatan dari tim lain.');
        }

        $sub->delete();

        return redirect()->back()->with('success', 'Sub Kegiatan berhasil dihapus!');
    }

    public function updateProgress(Request $request, $id)
    {
        $sub = SubKegiatan::findOrFail($id);
        $task = $sub->task;
        $currentUser = Auth::user();
        if ($currentUser && $task && !$currentUser->canAccessDetailKegiatan($task)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            return redirect()->back()->with('error_access', 'Akses Ditolak: Anda tidak dapat memperbarui progress sub-kegiatan dari tim lain.');
        }

        $progress = intval($request->input('progress', $sub->progress));
        $status = $request->input('status', $sub->status);

        if ($progress >= 100) {
            $status = 'Selesai';
            $progress = 100;
        }

        $sub->update([
            'progress' => $progress,
            'status'   => $status
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $status, 'progress' => $progress]);
        }

        return redirect()->back()->with('success', 'Progress berhasil diperbarui!');
    }

    public function getByTask($taskId)
    {
        $subKegiatans = SubKegiatan::where('task_id', $taskId)->get();
        return response()->json($subKegiatans);
    }
}