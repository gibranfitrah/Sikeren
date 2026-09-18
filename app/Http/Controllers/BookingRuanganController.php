<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\RoomBooking;
use App\Venue;
use App\Task;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingRuanganController extends Controller
{
    /**
     * Menampilkan Halaman Booking Ruangan & Time Schedule
     */
    public function index(Request $request)
    {
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->query('month', Carbon::parse($selectedDate)->format('Y-m'));
        $viewMode = $request->query('view', 'daily'); // 'daily' | 'monthly'
        $rapatId = $request->query('rapat_id');

        // 1. Data 3 Ruangan Fisik Resmi
        $venues = Venue::whereIn('id', [1, 2, 3])->orderBy('id', 'asc')->get();
        if ($venues->isEmpty()) {
            // Fallback default jika belum ada
            $venues = collect([
                (object)[
                    'id' => 1,
                    'name' => 'Ruang Rapat Lantai 1',
                    'capacity' => 24,
                    'description' => 'Lantai 1 Gedung BPS • Sound System, Proyektor, AC, Podium (Kapasitas s.d 24 Orang)'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Vicon Lantai 3',
                    'capacity' => 58,
                    'description' => 'Lantai 3 Gedung BPS • Smart TV Display, Camera Vicon 360, Mic Conference Polycom, AC (Kapasitas s.d 58 Orang)'
                ],
                (object)[
                    'id' => 3,
                    'name' => 'Aula Lantai 4',
                    'capacity' => 100,
                    'description' => 'Lantai 4 Gedung BPS • Videotron LED Screen, Sound System Besar, AC Central, Panggung (Kapasitas s.d 100 Orang)'
                ],
            ]);
        }

        // 2. Data 2 Akun Zoom Resmi
        $zoomAccounts = RoomBooking::$zoomAccounts;

        // 3. Data Booking pada Tanggal Terpilih (untuk View Harian & Real-time Availability)
        $dailyBookings = RoomBooking::with('task')
            ->where('booking_date', $selectedDate)
            ->where('status', '!=', 'Dibatalkan')
            ->orderBy('start_time', 'asc')
            ->get();

        // 4. Hitung Status Realtime Ketersediaan Ruangan & Zoom pada Tanggal Terpilih
        $currentTime = Carbon::now()->format('H:i:s');
        $isToday = ($selectedDate === Carbon::today()->format('Y-m-d'));

        $venuesStatus = [];
        foreach ($venues as $venue) {
            $roomBookings = $dailyBookings->where('venue_id', $venue->id);
            $activeNow = null;

            if ($isToday) {
                $activeNow = $roomBookings->first(function ($b) use ($currentTime) {
                    return $currentTime >= $b->start_time && $currentTime <= $b->end_time;
                });
            }

            $venuesStatus[$venue->id] = [
                'venue'        => $venue,
                'is_in_use'    => !is_null($activeNow),
                'current_task' => $activeNow,
                'bookings'     => $roomBookings,
                'total_today'  => $roomBookings->count(),
                'status_label' => $activeNow ? 'Sedang Digunakan' : ($roomBookings->count() > 0 ? 'Ada Jadwal' : 'Kosong / Tersedia'),
                'status_color' => $activeNow ? 'rose' : ($roomBookings->count() > 0 ? 'amber' : 'emerald'),
            ];
        }

        $zoomStatus = [];
        foreach ($zoomAccounts as $key => $zoom) {
            $zBookings = $dailyBookings->where('zoom_account', $key);
            $activeNow = null;

            if ($isToday) {
                $activeNow = $zBookings->first(function ($b) use ($currentTime) {
                    return $currentTime >= $b->start_time && $currentTime <= $b->end_time;
                });
            }

            $zoomStatus[$key] = [
                'info'         => $zoom,
                'is_in_use'    => !is_null($activeNow),
                'current_task' => $activeNow,
                'bookings'     => $zBookings,
                'total_today'  => $zBookings->count(),
                'status_label' => $activeNow ? 'Sedang Digunakan' : ($zBookings->count() > 0 ? 'Ada Jadwal' : 'Kosong / Tersedia'),
                'status_color' => $activeNow ? 'rose' : ($zBookings->count() > 0 ? 'amber' : 'emerald'),
            ];
        }

        // 5. Data Booking Bulanan (untuk View Bulanan)
        $monthCarbon = Carbon::parse($selectedMonth . '-01');
        $monthlyBookings = RoomBooking::with('task')
            ->whereYear('booking_date', $monthCarbon->year)
            ->whereMonth('booking_date', $monthCarbon->month)
            ->where('status', '!=', 'Dibatalkan')
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // 6. Rapat yang baru dibuat (jika ada param rapat_id)
        $selectedRapat = null;
        if ($rapatId) {
            $selectedRapat = Task::find($rapatId);
        }

        // 7. Daftar Rapat yang belum memiliki booking ruangan atau rapat terbaru
        $availableRapats = Task::where('jenis', 'Rapat')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // 8. Time Slots untuk Tampilan Harian (08:00 s.d 17:00)
        $timeSlots = [
            '08:00', '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00', '17:00'
        ];

        // 9. Notifikasi
        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif  = Auth::user()->unreadNotifications()->count();

        return view('booking_ruangan.index', compact(
            'venues',
            'zoomAccounts',
            'venuesStatus',
            'zoomStatus',
            'dailyBookings',
            'monthlyBookings',
            'selectedDate',
            'selectedMonth',
            'viewMode',
            'isToday',
            'selectedRapat',
            'availableRapats',
            'timeSlots',
            'notifications',
            'jumlah_notif'
        ));
    }

    /**
     * Menyimpan Booking Ruangan & Akun Zoom Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_acara'        => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'jumlah_peserta'    => 'nullable|integer|min:1',
            'tipe_pertemuan'    => 'required|in:offline,online,hybrid',
            'booking_date'      => 'required|date',
            'start_time'        => 'required',
            'end_time'          => 'required|after:start_time',
            'venue_id'          => 'nullable|exists:venues,id',
            'zoom_account'      => 'nullable|in:zoom_1,zoom_2,eksternal,none',
            'zoom_link_custom'  => 'nullable|string',
            'task_id'           => 'nullable|exists:tasks,id',
            'mic_count'         => 'nullable|string',
            'fasilitas_list'    => 'nullable|array',
            'layout_meja'       => 'nullable|string',
            'keterangan'        => 'nullable|string',
        ], [
            'nama_acara.required'     => 'Nama agenda / rapat wajib diisi.',
            'penyelenggara.required'  => 'Penyelenggara / PJ wajib diisi.',
            'booking_date.required'   => 'Tanggal pelaksanaan wajib diisi.',
            'start_time.required'     => 'Jam mulai wajib diisi.',
            'end_time.required'       => 'Jam selesai wajib diisi.',
            'end_time.after'          => 'Jam selesai harus setelah jam mulai.',
        ]);

        $venueId = $request->venue_id ?: null;
        $zoomAccount = $request->zoom_account ?: 'none';

        // Normalisasi sesuai tipe: radio yang disembunyikan (x-show) tetap ter-submit,
        // jadi abaikan zoom untuk offline dan abaikan ruangan untuk online.
        if ($request->tipe_pertemuan === 'offline') {
            $zoomAccount = 'none';
        } elseif ($request->tipe_pertemuan === 'online') {
            $venueId = null;
        }

        // Validasi kebutuhan ruangan/zoom berdasarkan tipe pertemuan
        if ($request->tipe_pertemuan === 'offline' && !$venueId) {
            return back()->withInput()->with('error', 'Silakan pilih Ruangan Rapat fisik untuk pertemuan Offline.');
        }

        if ($request->tipe_pertemuan === 'online' && $zoomAccount === 'none') {
            return back()->withInput()->with('error', 'Silakan pilih Akun Zoom atau masukkan link Zoom untuk pertemuan Online.');
        }

        if ($request->tipe_pertemuan === 'hybrid') {
            if (!$venueId) {
                return back()->withInput()->with('error', 'Pertemuan Hybrid memerlukan pemilihan Ruangan Rapat fisik.');
            }
            if ($zoomAccount === 'none') {
                return back()->withInput()->with('error', 'Pertemuan Hybrid memerlukan Akun Zoom atau link meeting.');
            }
        }

        // Cek Konflik Waktu (Anti Double-Booking)
        $conflicts = RoomBooking::checkConflict(
            $request->booking_date,
            $request->start_time,
            $request->end_time,
            $venueId,
            $zoomAccount
        );

        if (!empty($conflicts)) {
            $errMsg = implode(' ', $conflicts);
            return back()->withInput()->with('error', 'Bentrok Jadwal Terdeteksi! ' . $errMsg);
        }

        // Nama Ruangan
        $venue = $venueId ? Venue::find($venueId) : null;
        $namaRuangan = $venue ? $venue->name : null;

        // Data Zoom
        $zoomLink = null;
        $zoomMeetingId = null;
        $zoomPasscode = null;
        $zoomHostKey = null;

        if (in_array($zoomAccount, ['zoom_1', 'zoom_2'])) {
            $zInfo = RoomBooking::$zoomAccounts[$zoomAccount];
            $zoomLink = $zInfo['link'];
            $zoomMeetingId = $zInfo['meeting_id'];
            $zoomPasscode = $zInfo['passcode'];
        } elseif ($zoomAccount === 'eksternal') {
            $zoomLink = $request->zoom_link_custom;
            $zoomMeetingId = $request->zoom_meeting_id_custom ?? null;
            $zoomPasscode = $request->zoom_passcode_custom ?? null;
        }

        // Susun Data Section A, B, C Sarpras
        $layoutMeja = $request->input('layout_meja', 'Classroom');
        $sofaDepan = $request->input('sofa_depan', 'tanpa');

        // Section B: Setup Podium
        $setupPodium = [
            'tipe'               => $request->input('tipe_podium', 'Tanpa Podium'),
            'jumlah_kursi'       => (int) $request->input('jumlah_kursi_podium', 0),
            'sofa_depan'         => $sofaDepan,
            'pasang_spanduk'     => $request->boolean('pasang_spanduk'),
            'keterangan_spanduk' => $request->input('keterangan_spanduk'),
        ];
        $setupPodiumJson = json_encode($setupPodium);

        // Section C: Special Requests (11 Items)
        $specialReqItems = (array) $request->input('special_requests_list', []);
        $micCount = $request->input('mic_count', '0');
        if ($micCount && $micCount !== '0' && $micCount !== 'none') {
            $micLabel = 'Tambah Mic (' . $micCount . ' Mic)';
            if (!in_array($micLabel, $specialReqItems)) {
                array_unshift($specialReqItems, $micLabel);
            }
        }
        $specialRequestsData = [
            'items'     => array_values(array_unique($specialReqItems)),
            'mic_count' => ($micCount && $micCount !== '0' && $micCount !== 'none') ? $micCount : null,
            'lainnya'   => $request->input('special_lainnya'),
        ];
        $specialRequestsJson = json_encode($specialRequestsData);

        // Ringkasan Fasilitas Gabungan untuk Kompatibilitas
        $summaryParts = [];
        if ($layoutMeja) {
            $summaryParts[] = 'Layout: ' . $layoutMeja;
        }
        if ($sofaDepan === 'dengan') {
            $summaryParts[] = 'Dengan Sofa VIP Depan Panggung';
        }
        if (!empty($setupPodium['tipe']) && $setupPodium['tipe'] !== 'Tanpa Podium') {
            $podiumTxt = 'Podium: ' . $setupPodium['tipe'];
            if ($setupPodium['jumlah_kursi'] > 0) {
                $podiumTxt .= ' (' . $setupPodium['jumlah_kursi'] . ' Kursi/Sofa)';
            }
            if ($setupPodium['pasang_spanduk']) {
                $podiumTxt .= ' + Spanduk';
            }
            $summaryParts[] = $podiumTxt;
        }
        if (!empty($specialReqItems)) {
            $summaryParts = array_merge($summaryParts, $specialReqItems);
        }
        if (!empty($request->special_lainnya)) {
            $summaryParts[] = 'Catatan: ' . $request->special_lainnya;
        }
        $fasilitasString = !empty($summaryParts) ? implode(', ', $summaryParts) : null;

        // 1. Simpan ke RoomBooking
        $booking = RoomBooking::create([
            'task_id'          => $request->task_id ?: null,
            'nama_acara'       => $request->nama_acara,
            'penyelenggara'    => $request->penyelenggara,
            'jumlah_peserta'   => $request->jumlah_peserta ?: null,
            'tipe_pertemuan'   => $request->tipe_pertemuan,
            'venue_id'         => $venueId,
            'nama_ruangan'     => $namaRuangan,
            'fasilitas'        => $fasilitasString,
            'layout_meja'      => $layoutMeja,
            'setup_podium'     => $setupPodiumJson,
            'special_requests' => $specialRequestsJson,
            'zoom_account'     => $zoomAccount,
            'zoom_link'        => $zoomLink,
            'zoom_meeting_id'  => $zoomMeetingId,
            'zoom_passcode'    => $zoomPasscode,
            'zoom_host_key'    => $zoomHostKey,
            'booking_date'     => $request->booking_date,
            'start_time'       => $request->start_time,
            'end_time'         => $request->end_time,
            'status'           => 'Disetujui',
            'keterangan'       => $request->keterangan,
            'created_by'       => Auth::id(),
        ]);

        // 2. Jika terhubung dengan Task/Rapat, perbarui detail tempat di tabel Task
        if ($request->task_id) {
            $task = Task::find($request->task_id);
            if ($task) {
                $tempatDesc = '';
                if ($request->tipe_pertemuan === 'offline') {
                    $tempatDesc = $namaRuangan;
                } elseif ($request->tipe_pertemuan === 'online') {
                    $tempatDesc = 'Online (' . ($zoomAccount === 'eksternal' ? 'Link Eksternal' : ($zInfo['name'] ?? 'Zoom')) . ')';
                } elseif ($request->tipe_pertemuan === 'hybrid') {
                    $tempatDesc = $namaRuangan . ' & ' . ($zoomAccount === 'eksternal' ? 'Zoom Eksternal' : ($zInfo['name'] ?? 'Zoom'));
                }

                $task->venue_id       = $venueId;
                $task->tempat         = $tempatDesc;
                $task->status_ruangan = 'Disetujui';
                $task->save();
            }
        }

        return redirect()->route('booking-ruangan.index', ['date' => $request->booking_date])
            ->with('success', 'Reservasi ruangan & akun Zoom untuk "' . $request->nama_acara . '" berhasil dikonfirmasi!');
    }

    /**
     * Membatalkan / Menghapus Booking Ruangan
     */
    public function destroy($id)
    {
        $booking = RoomBooking::findOrFail($id);

        if ($booking->task_id) {
            $task = Task::find($booking->task_id);
            if ($task) {
                $task->status_ruangan = 'Dibatalkan';
                $task->save();
            }
        }

        $booking->delete();

        return back()->with('success', 'Booking ruangan berhasil dibatalkan.');
    }

    /**
     * API JSON Jadwal Booking untuk Interactive Calendar / Scheduler
     */
    public function apiSchedule(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $query = RoomBooking::with('task')->where('status', '!=', 'Dibatalkan');

        if ($start && $end) {
            $query->whereBetween('booking_date', [$start, $end]);
        }

        $bookings = $query->get()->map(function ($b) {
            $color = '#3b82f6'; // default blue
            if ($b->venue_id == 1) $color = '#2563eb'; // Aula Lt 1 (Blue)
            elseif ($b->venue_id == 2) $color = '#7c3aed'; // Vicon Lt 3 (Purple)
            elseif ($b->venue_id == 3) $color = '#059669'; // Aula Lt 4 (Emerald)
            elseif ($b->tipe_pertemuan === 'online') $color = '#0284c7'; // Online (Sky)

            return [
                'id'              => $b->id,
                'title'           => '[' . ($b->nama_ruangan ?: 'Online') . '] ' . $b->nama_acara,
                'start'           => $b->booking_date . 'T' . $b->start_time,
                'end'             => $b->booking_date . 'T' . $b->end_time,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'ruangan'          => $b->nama_ruangan ?? 'Online (Tanpa Ruangan)',
                    'tipe'             => ucfirst($b->tipe_pertemuan),
                    'penyelenggara'    => $b->penyelenggara,
                    'jumlah_peserta'   => $b->jumlah_peserta,
                    'layout_meja'      => $b->layout_meja,
                    'setup_podium'     => $b->setup_podium_data,
                    'special_requests' => $b->special_requests_data,
                    'fasilitas'        => $b->fasilitas,
                    'zoom_account'     => $b->zoom_account,
                    'zoom_link'        => $b->zoom_link,
                    'keterangan'       => $b->keterangan
                ]
            ];
        });

        return response()->json($bookings);
    }
}
