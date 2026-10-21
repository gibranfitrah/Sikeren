<?php

namespace App\Http\Controllers;

use App\JadwalLayanan;
use App\Support\SimpatiRoles;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpatiJadwalController extends Controller
{
    public const STATUSES = ['Terjadwal', 'Hadir', 'Izin', 'Cuti', 'Sakit', 'Tugas Luar', 'Diganti', 'Alpa'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $this->authorizeSimpati();

        $grups = array_keys(SimpatiRoles::GRUPS);
        $grup  = $request->query('grup', 'pst');
        if (!in_array($grup, $grups, true)) {
            $grup = 'pst';
        }

        $bulanInput = $request->query('bulan', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $bulanInput)) {
            $bulanInput = now()->format('Y-m');
        }
        [$tahun, $bulan] = array_map('intval', explode('-', $bulanInput));

        $firstDay  = Carbon::create($tahun, $bulan, 1);
        $lastDay   = $firstDay->copy()->endOfMonth();
        $entries   = $this->entriesForMonth($grup, $tahun, $bulan);

        // Kalender: grid Senin-Minggu
        $gridStart = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd   = $lastDay->copy()->endOfWeek(Carbon::MONDAY);
        $cells     = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $cells[] = $d->toDateString();
        }

        $byDate = collect($entries)->groupBy('tanggal');

        $grupCounts = [];
        foreach ($grups as $g) {
            $grupCounts[$g] = count($this->entriesForMonth($g, $tahun, $bulan));
        }

        $manageable = SimpatiRoles::manageableGrups($user);
        $prevBulan  = $firstDay->copy()->subMonthNoOverflow()->format('Y-m');
        $nextBulan  = $firstDay->copy()->addMonthNoOverflow()->format('Y-m');

        return view('admin.simpati.jadwal', [
            'grup'        => $grup,
            'grups'       => $grups,
            'grupCounts'  => $grupCounts,
            'manageable'  => $manageable,
            'bulanInput'  => $bulanInput,
            'prevBulan'   => $prevBulan,
            'nextBulan'   => $nextBulan,
            'bulanLabel'  => $firstDay->translatedFormat('F Y'),
            'firstDay'    => $firstDay,
            'cells'       => $cells,
            'byDate'      => $byDate,
            'entries'     => $entries,
            'statuses'    => self::STATUSES,
            'grupLabel'   => SimpatiRoles::grupLabel($grup),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->authorizeSimpati();

        $request->validate([
            'grup'       => 'required|in:' . implode(',', array_keys(SimpatiRoles::GRUPS)),
            'tanggal'    => 'required|date',
            'user_id'    => 'required_without:nama|nullable|integer|exists:users,id',
            'nama'       => 'required_without:user_id|nullable|string|max:200',
            'niplama'    => 'nullable|string|max:15',
            'sesi'       => 'nullable|integer|in:1,2',
            'jam_mulai'  => 'nullable|date_format:H:i',
            'jam_selesai'=> 'nullable|date_format:H:i|after:jam_mulai',
            'status'     => 'required|in:' . implode(',', self::STATUSES),
            'keterangan' => 'nullable|string|max:300',
        ]);

        $grup = $request->input('grup');
        $this->authorizeGrup($user, $grup);

        $model = new JadwalLayanan();
        $model->grup      = $grup;
        $model->tanggal   = $request->input('tanggal');
        $model->sesi      = $request->input('sesi');
        $model->jam_mulai = $request->input('jam_mulai');
        $model->jam_selesai = $request->input('jam_selesai');
        $model->status    = $request->input('status');
        $model->sumber    = 'manual';
        $model->keterangan = $request->input('keterangan');

        if ($request->filled('user_id')) {
            $target = User::findOrFail($request->input('user_id'));
            $model->user_id = $target->id;
            $model->nama    = $target->nama_lengkap;
            $model->niplama = $target->niplama;
        } else {
            $model->nama    = trim($request->input('nama'));
            $model->niplama = $request->input('niplama');
        }

        $model->save();

        return $this->backWithSuccess($request, 'Jadwal ' . $model->nama . ' pada ' . Carbon::parse($model->tanggal)->translatedFormat('d F Y') . ' berhasil disimpan.');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $this->authorizeSimpati();

        $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUSES),
        ]);

        $model = JadwalLayanan::findOrFail($id);
        $this->authorizeGrup($user, $model->grup);

        $model->status = $request->input('status');
        $model->save();

        return $this->backWithSuccess($request, 'Status ' . $model->nama . ' diperbarui menjadi ' . $model->status . '.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->authorizeSimpati();

        $model = JadwalLayanan::findOrFail($id);
        $this->authorizeGrup($user, $model->grup);

        $nama = $model->nama;
        $tgl  = Carbon::parse($model->tanggal)->translatedFormat('d F Y');
        $model->delete();

        return $this->backWithSuccess($request, 'Jadwal ' . $nama . ' pada ' . $tgl . ' dihapus.');
    }

    /** Unduh template XLSX (hanya baris judul kolom). */
    public function template(Request $request)
    {
        $user = $this->authorizeSimpati();
        $grup = $request->query('grup', 'pst');
        $this->authorizeGrup($user, $grup);

        $headings = ['Tanggal', 'NIP Lama', 'Nama', 'Sesi', 'Jam Mulai', 'Jam Selesai', 'Status', 'Keterangan'];

        $file = new class($headings) implements FromArray, WithHeadings {
            public function __construct(private array $headings)
            {
            }

            public function headings(): array
            {
                return $this->headings;
            }

            public function array(): array
            {
                return [];
            }
        };

        return Excel::download($file, 'template-jadwal-' . $grup . '-' . now()->format('Ym') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /** Upload jadwal bulanan (XLSX/XLS) sesuai template. */
    public function upload(Request $request)
    {
        $user = $this->authorizeSimpati();

        $request->validate([
            'grup' => 'required|in:' . implode(',', array_keys(SimpatiRoles::GRUPS)),
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $grup = $request->input('grup');
        $this->authorizeGrup($user, $grup);

        $sheets = Excel::toArray(null, $request->file('file'));
        $rows   = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return $this->backWithError($request, 'File tidak berisi data. Gunakan template resmi dan isi minimal 1 baris jadwal.');
        }

        $headerMap = $this->mapHeader(array_shift($rows));
        if (!isset($headerMap['tanggal']) || (!isset($headerMap['nama']) && !isset($headerMap['niplama']))) {
            return $this->backWithError($request, 'Kolom wajib tidak ditemukan. Wajib ada kolom "Tanggal" serta "NIP Lama" atau "Nama".');
        }

        $imported = 0;
        $skipped  = [];
        $line     = 1;

        foreach ($rows as $row) {
            $line++;
            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $tanggal = $this->parseTanggal($row[$headerMap['tanggal']] ?? null);
            if ($tanggal === null) {
                $skipped[] = "Baris {$line}: format tanggal tidak valid.";
                continue;
            }

            $statusRaw = isset($headerMap['status']) ? trim((string) ($row[$headerMap['status']] ?? '')) : '';
            $status    = $this->normalizeStatus($statusRaw);
            if ($status === null) {
                $skipped[] = "Baris {$line}: status \"{$statusRaw}\" tidak dikenal.";
                continue;
            }

            $niplama = isset($headerMap['niplama']) ? trim((string) ($row[$headerMap['niplama']] ?? '')) : '';
            $nama    = isset($headerMap['nama']) ? trim((string) ($row[$headerMap['nama']] ?? '')) : '';

            $target = null;
            if ($niplama !== '') {
                $target = User::where('niplama', $niplama)->orWhere('nipbaru', $niplama)->first();
                if ($target) {
                    $nama = $target->nama_lengkap;
                }
            }

            if ($nama === '') {
                $skipped[] = "Baris {$line}: NIP tidak ditemukan dan kolom Nama kosong.";
                continue;
            }

            $model = new JadwalLayanan();
            $model->grup      = $grup;
            $model->tanggal   = $tanggal;
            $model->nama      = $nama;
            $model->status    = $status;
            $model->sumber    = 'upload';
            $model->user_id   = $target ? $target->id : null;
            $model->niplama   = $target ? $target->niplama : ($niplama !== '' ? $niplama : null);

            if (isset($headerMap['sesi']) && in_array((int) ($row[$headerMap['sesi']] ?? 0), [1, 2], true)) {
                $model->sesi = (int) $row[$headerMap['sesi']];
            }

            foreach (['jam_mulai' => 'jam_mulai', 'jam_selesai' => 'jam_selesai'] as $col => $attr) {
                if (isset($headerMap[$col])) {
                    $jam = $this->parseJam($row[$headerMap[$col]] ?? null);
                    if ($jam) {
                        $model->{$attr} = $jam;
                    }
                }
            }

            if (isset($headerMap['keterangan'])) {
                $model->keterangan = trim((string) ($row[$headerMap['keterangan']] ?? '')) ?: null;
            }

            $model->save();
            $imported++;
        }

        if ($imported === 0) {
            return $this->backWithError($request, 'Tidak ada baris yang valid untuk diimpor.' . (count($skipped) ? ' (' . count($skipped) . ' baris dilewati)' : ''));
        }

        $message = "{$imported} jadwal " . SimpatiRoles::grupLabel($grup) . ' berhasil diimpor.';
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' baris dilewati.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'imported' => $imported, 'skipped' => $skipped]);
        }

        return redirect()->back()->with('success', $message)->with('skipped_rows', $skipped);
    }

    /**
     * Entri jadwal satu bulan: data SIMPATI (manual/upload)
     * digabung dengan penugasan PST dari alur STARLA (baca-saja).
     */
    protected function entriesForMonth(string $grup, int $tahun, int $bulan): array
    {
        $items = [];

        if (Schema::hasTable('jadwal_layanans')) {
            foreach (JadwalLayanan::grup($grup)->bulan($tahun, $bulan)->orderBy('tanggal')->orderBy('nama')->get() as $r) {
                $items[] = [
                    'row_id'   => $r->id,
                    'tanggal'  => $r->tanggal->toDateString(),
                    'nama'     => $r->nama,
                    'niplama'  => $r->niplama ?: '-',
                    'sesi'     => $r->sesi,
                    'jam'      => $this->formatJam($r->jam_mulai, $r->jam_selesai),
                    'status'   => $r->status,
                    'sumber'   => $r->sumber === 'upload' ? 'Upload' : 'Manual',
                    'is_starla' => false,
                    'keterangan' => $r->keterangan ?: '-',
                ];
            }
        }

        if ($grup === 'pst' && Schema::hasTable('penugasans') && Schema::hasTable('tasks')) {
            $prefix = sprintf('%04d-%02d', $tahun, $bulan);

            $starlaRows = DB::table('penugasans')
                ->join('tasks', 'tasks.id', '=', 'penugasans.id_kegiatan')
                ->where('tasks.start_date', 'like', $prefix . '%')
                ->where(function ($q) {
                    $q->where('penugasans.keterangan', 'like', '%Petugas PST%')
                      ->orWhere('penugasans.keterangan', 'like', '%PST Sesi%')
                      ->orWhere('tasks.jenis_kegiatan', 'like', '%Pelayanan Statistik Terpadu%')
                      ->orWhere('tasks.jenis_kegiatan', 'like', '%PST%')
                      ->orWhere('tasks.text', 'like', '%PST%');
                })
                ->select(
                    'penugasans.id as penugasan_id', 'penugasans.niplama', 'penugasans.peserta',
                    'penugasans.status_kehadiran', 'penugasans.keterangan',
                    'tasks.start_date', 'tasks.start_jam', 'tasks.end_jam', 'tasks.tempat'
                )
                ->get();

            foreach ($starlaRows as $row) {
                $sesi = null;
                if (preg_match('/Sesi\s*(\d+)/i', $row->keterangan ?? '', $m)) {
                    $sesi = (int) $m[1];
                }

                $items[] = [
                    'row_id'    => $row->penugasan_id,
                    'tanggal'   => Carbon::parse($row->start_date)->toDateString(),
                    'nama'      => $row->peserta ?: ($row->niplama ?: '-'),
                    'niplama'   => $row->niplama ?: '-',
                    'sesi'      => $sesi,
                    'jam'       => $this->formatJam($row->start_jam, $row->end_jam),
                    'status'    => $row->status_kehadiran ?: 'Belum Konfirmasi',
                    'sumber'    => 'STARLA',
                    'is_starla' => true,
                    'keterangan' => $row->tempat ?? '-',
                ];
            }
        }

        usort($items, fn ($a, $b) => [$a['tanggal'], $a['nama']] <=> [$b['tanggal'], $b['nama']]);

        return $items;
    }

    protected function mapHeader(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $i => $cell) {
            $key = strtolower(trim((string) $cell));
            $key = str_replace(['.', '/', '-', '  '], ' ', $key);
            $key = preg_replace('/\s+/', ' ', $key);

            $aliases = [
                'tanggal'  => ['tanggal', 'date'],
                'niplama'  => ['nip lama', 'niplama', 'nip', 'niplama'],
                'nama'     => ['nama', 'nama lengkap', 'nama petugas'],
                'sesi'     => ['sesi', 'session'],
                'jam_mulai'  => ['jam mulai', 'mulai', 'jam masuk'],
                'jam_selesai' => ['jam selesai', 'selesai', 'jam pulang'],
                'status'   => ['status', 'status kehadiran'],
                'keterangan' => ['keterangan', 'catatan', 'lokasi'],
            ];

            foreach ($aliases as $canonical => $list) {
                if (in_array($key, $list, true)) {
                    $map[$canonical] = $i;
                    break;
                }
            }
        }

        return $map;
    }

    protected function parseTanggal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd M Y', 'd F Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value))->format('Y-m-d');
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseJam($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i:s');
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', trim((string) $value), $m)) {
            return sprintf('%02d:%s:00', (int) $m[1], $m[2]);
        }

        if (is_numeric($value)) {
            $fraction = (float) $value;
            $seconds  = (int) round($fraction * 86400);

            return sprintf('%02d:%02d:00', intdiv($seconds, 3600) % 24, intdiv($seconds % 3600, 60));
        }

        return null;
    }

    protected function normalizeStatus(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return 'Terjadwal';
        }

        foreach (self::STATUSES as $status) {
            if (strcasecmp($raw, $status) === 0) {
                return $status;
            }
        }

        return null;
    }

    protected function formatJam($mulai, $selesai): string
    {
        $mulai   = $mulai ? substr((string) $mulai, 0, 5) : null;
        $selesai = $selesai ? substr((string) $selesai, 0, 5) : null;

        if ($mulai && $selesai) {
            return $mulai . ' - ' . $selesai;
        }

        return $mulai ?: ($selesai ?: '-');
    }

    protected function authorizeSimpati()
    {
        $user = Auth::user();
        abort_unless($user && $user->canAccessSimpati(), 403, 'Akses terbatas! Halaman ini hanya untuk Administrator, Ketua Tim/PJ, dan role admin SIMPATI.');

        return $user;
    }

    protected function authorizeGrup(User $user, string $grup): void
    {
        abort_unless(SimpatiRoles::canManageGrup($user, $grup), 403, 'Anda tidak berhak mengelola jadwal ' . SimpatiRoles::grupLabel($grup) . '.');
    }

    protected function backWithSuccess(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    protected function backWithError(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return redirect()->back()->with('error', $message);
    }
}
