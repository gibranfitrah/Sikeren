<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\SubKegiatan;
use App\User;
use App\master_group;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeScheduleController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = User::orderBy('nama_lengkap', 'asc')->get();
        $masterGroups = master_group::all();
        $selectedPerson = $request->input('person', '');
        $selectedTeam = $request->input('team', '');
        $defaultView = $request->input('view', 'month');

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif = Auth::user()->unreadNotifications()->count();

        return view('time_schedule.index', compact(
            'pegawais',
            'masterGroups',
            'selectedPerson',
            'selectedTeam',
            'defaultView',
            'notifications',
            'jumlah_notif'
        ));
    }

    public function events(Request $request)
    {
        $person = $request->input('person');
        $team = $request->input('team');

        $events = [];

        // 1. Fetch Tasks (Rapat & Kegiatan)
        $tasksQuery = Task::query();

        if (!empty($team)) {
            $tasksQuery->where('tim', $team);
        }

        if (!empty($person)) {
            $tasksQuery->where(function($q) use ($person) {
                $q->where('pemimpin', 'LIKE', '%' . $person . '%')
                  ->orWhere('penanggung_jawab', 'LIKE', '%' . $person . '%')
                  ->orWhere('notulis', 'LIKE', '%' . $person . '%')
                  ->orWhere('tim_dokumentasi', 'LIKE', '%' . $person . '%')
                  ->orWhere('owners', 'LIKE', '%' . $person . '%');
            });
        }

        $tasks = $tasksQuery->get();

        foreach ($tasks as $task) {
            $startDate = $task->start_date ? $task->start_date : Carbon::today()->format('Y-m-d');
            $endDate = $task->date_akhir ? Carbon::parse($task->date_akhir)->addDay()->format('Y-m-d') : $startDate;

            $isRapat = ($task->jenis === 'Rapat');
            $bgColor = $isRapat ? '#6366f1' : '#0284c7';

            $events[] = [
                'id'          => 'task_' . $task->id,
                'title'       => ($isRapat ? '[Rapat] ' : '[Kegiatan] ') . $task->text,
                'start'       => $startDate . ($task->start_jam ? 'T' . $task->start_jam : ''),
                'end'         => $endDate . ($task->end_jam ? 'T' . $task->end_jam : ''),
                'url'         => url('/daftarkegiatan/' . $task->id),
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'textColor'   => '#ffffff',
                'extendedProps' => [
                    'type'   => $task->jenis,
                    'pj'     => $task->penanggung_jawab ?? $task->pemimpin ?? '-',
                    'tim'    => $task->tim ?? '-',
                    'tempat' => $task->tempat ?? '-',
                    'status' => $task->status ?? 'Terjadwal'
                ]
            ];
        }

        // 2. Fetch Sub-Kegiatans
        $subQuery = SubKegiatan::with('task');

        if (!empty($team)) {
            $subQuery->where('tim', $team);
        }

        if (!empty($person)) {
            $subQuery->where(function($q) use ($person) {
                $q->where('pj', 'LIKE', '%' . $person . '%')
                  ->orWhere('anggota', 'LIKE', '%' . $person . '%');
            });
        }

        $subs = $subQuery->get();

        foreach ($subs as $sub) {
            $startDate = $sub->start_date ? $sub->start_date : Carbon::today()->format('Y-m-d');
            $endDate = $sub->end_date ? Carbon::parse($sub->end_date)->addDay()->format('Y-m-d') : $startDate;

            $bgColor = ($sub->status === 'Selesai') ? '#10b981' : '#f59e0b';

            $events[] = [
                'id'          => 'sub_' . $sub->id,
                'title'       => '[Sub] ' . $sub->nama_sub,
                'start'       => $startDate,
                'end'         => $endDate,
                'url'         => url('/sub-kegiatan?task_id=' . $sub->task_id),
                'backgroundColor' => $bgColor,
                'borderColor' => $bgColor,
                'textColor'   => '#ffffff',
                'extendedProps' => [
                    'type'   => 'Sub Kegiatan',
                    'pj'     => $sub->pj ?? '-',
                    'tim'    => $sub->tim ?? '-',
                    'status' => $sub->status,
                    'parent' => $sub->task ? $sub->task->text : '-'
                ]
            ];
        }

        return response()->json($events);
    }
}