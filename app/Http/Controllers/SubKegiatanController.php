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
        $tasks = Task::orderBy('id', 'desc')->get();
        $masterGroups = master_group::all();
        $allUsers = User::orderBy('nama_lengkap', 'asc')->get();
        $usersByGroup = group::join('users', 'users.niplama', '=', 'groups.niplama')
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
            'pj'         => 'nullable|string|max:255',
            'tim'        => 'nullable|string|max:255',
            'anggota'    => 'nullable|array',
        ]);

        $anggotaJson = $request->has('anggota') && is_array($request->anggota) 
            ? json_encode($request->anggota) 
            : null;

        $sub = SubKegiatan::create([
            'task_id'    => $request->task_id,
            'nama_sub'   => $request->nama_sub,
            'tim'        => $request->tim,
            'pj'         => $request->pj,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'anggota'    => $anggotaJson,
            'status'     => $request->input('status', 'Sedang Berjalan'),
            'progress'   => $request->input('progress', 0),
            'keterangan' => $request->keterangan,
        ]);

        // Send notifications to PJ & members
        $task = Task::find($request->task_id);
        $taskName = $task ? $task->text : 'Kegiatan';

        if ($request->has('anggota') && is_array($request->anggota)) {
            foreach ($request->anggota as $namaAnggota) {
                $userAnggota = User::where('nama_lengkap', $namaAnggota)
                    ->orWhere('username', $namaAnggota)
                    ->orWhere('niplama', $namaAnggota)
                    ->first();
                if ($userAnggota && $userAnggota->id !== Auth::id()) {
                    DB::table('notifications')->insert([
                        'type'            => 'App\Notifications\PenugasanSubKegiatanNotification',
                        'notifiable_type' => 'App\User',
                        'notifiable_id'   => $userAnggota->id,
                        'data'            => json_encode([
                            'judul' => 'Penugasan Sub Kegiatan Baru',
                            'pesan' => 'Anda ditugaskan pada sub kegiatan: ' . $request->nama_sub . ' (' . $taskName . ')',
                            'url'   => url('/sub-kegiatan'),
                        ]),
                        'read_at'         => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Sub Kegiatan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $sub = SubKegiatan::findOrFail($id);

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
        $sub->delete();

        return redirect()->back()->with('success', 'Sub Kegiatan berhasil dihapus!');
    }

    public function updateProgress(Request $request, $id)
    {
        $sub = SubKegiatan::findOrFail($id);
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