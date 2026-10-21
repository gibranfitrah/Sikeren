<?php

namespace App\Http\Controllers;

use App\JadwalLayanan;
use App\PetugasLayanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimpatiDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->canAccessSimpati(), 403, 'Akses terbatas! Halaman ini hanya untuk Administrator, Ketua Tim/PJ, dan role admin SIMPATI.');

        $counts = [];
        foreach (array_keys(\App\Support\SimpatiRoles::GRUPS) as $grup) {
            $counts[$grup] = $this->countPetugas($grup);
        }

        $bertugasHariIni = $this->petugasHariIni();

        return view('admin.simpati.dashboard', [
            'counts'          => $counts,
            'totalPetugas'    => array_sum($counts),
            'bertugasHariIni' => $bertugasHariIni,
            'jumlahBertugas'  => count($bertugasHariIni),
            'grups'           => \App\Support\SimpatiRoles::GRUPS,
        ]);
    }

    /**
     * Jumlah petugas aktif suatu grup: anggota terdaftar digabung
     * dengan petugas PST yang diturunkan dari penugasan STARLA/SIKEREN.
     */
    protected function countPetugas(string $grup): int
    {
        $terdaftar = Schema::hasTable('petugas_layanans')
            ? PetugasLayanan::grup($grup)->aktif()->get()
            : collect();

        if ($grup !== 'pst') {
            return $terdaftar->count();
        }

        $assignedNips = $terdaftar->pluck('niplama')->filter()->all();
        $derived = $this->derivedPstNiplamas();

        $extra = $derived->reject(fn ($nip) => in_array($nip, $assignedNips))->count();

        return $terdaftar->count() + $extra;
    }

    /** NIP unik petugas PST hasil penugasan (alur STARLA). */
    protected function derivedPstNiplamas()
    {
        if (!Schema::hasTable('penugasans') || !Schema::hasTable('tasks')) {
            return collect();
        }

        return DB::table('penugasans')
            ->join('tasks', 'tasks.id', '=', 'penugasans.id_kegiatan')
            ->whereNotNull('penugasans.niplama')
            ->where('penugasans.niplama', '!=', '')
            ->where(function ($q) {
                $q->where('penugasans.keterangan', 'like', '%Petugas PST%')
                  ->orWhere('penugasans.keterangan', 'like', '%PST Sesi%')
                  ->orWhere('tasks.jenis_kegiatan', 'like', '%Pelayanan Statistik Terpadu%')
                  ->orWhere('tasks.jenis_kegiatan', 'like', '%PST%')
                  ->orWhere('tasks.text', 'like', '%PST%');
            })
            ->distinct()
            ->pluck('penugasans.niplama');
    }

    /**
     * Daftar petugas yang bertugas hari ini (seluruh grup),
     * digabung antara jadwal layanan dan penugasan PST STARLA.
     */
    protected function petugasHariIni(): array
    {
        $items = [];

        if (Schema::hasTable('jadwal_layanans')) {
            foreach (JadwalLayanan::where('tanggal', now()->toDateString())->orderBy('grup')->orderBy('nama')->get() as $row) {
                $items[] = [
                    'grup'    => $row->grup,
                    'grupLabel' => \App\Support\SimpatiRoles::grupLabel($row->grup),
                    'nama'    => $row->nama,
                    'niplama' => $row->niplama ?: '-',
                    'sesi'    => $row->sesi ? 'Sesi ' . $row->sesi : null,
                    'jam'     => $this->formatJam($row->jam_mulai, $row->jam_selesai),
                    'tempat'  => $row->keterangan ?: '-',
                    'status'  => $row->status,
                    'sumber'  => 'SIMPATI',
                ];
            }
        }

        if (Schema::hasTable('penugasans') && Schema::hasTable('tasks')) {
            $today = now()->toDateString();
            $starlaRows = DB::table('penugasans')
                ->join('tasks', 'tasks.id', '=', 'penugasans.id_kegiatan')
                ->where(function ($q) use ($today) {
                    $q->where('tasks.start_date', $today)
                      ->orWhere(function ($qq) use ($today) {
                          $qq->where('tasks.start_date', '<=', $today)
                             ->where(function ($q3) use ($today) {
                                 $q3->whereNull('tasks.date_akhir')
                                    ->orWhere('tasks.date_akhir', '>=', $today);
                             });
                      });
                })
                ->where(function ($q) {
                    $q->where('penugasans.keterangan', 'like', '%Petugas PST%')
                      ->orWhere('penugasans.keterangan', 'like', '%PST Sesi%')
                      ->orWhere('tasks.jenis_kegiatan', 'like', '%Pelayanan Statistik Terpadu%')
                      ->orWhere('tasks.jenis_kegiatan', 'like', '%PST%')
                      ->orWhere('tasks.text', 'like', '%PST%');
                })
                ->select(
                    'penugasans.niplama', 'penugasans.peserta', 'penugasans.status_kehadiran',
                    'penugasans.keterangan', 'tasks.tempat', 'tasks.start_jam', 'tasks.end_jam'
                )
                ->get();

            foreach ($starlaRows as $row) {
                $sesi = null;
                if (preg_match('/Sesi\s*(\d+)/i', $row->keterangan ?? '', $m)) {
                    $sesi = (int) $m[1];
                }

                $items[] = [
                    'grup'      => 'pst',
                    'grupLabel' => 'Petugas PST',
                    'nama'      => $row->peserta ?: ($row->niplama ?: '-'),
                    'niplama'   => $row->niplama ?: '-',
                    'sesi'      => $sesi ? 'Sesi ' . $sesi : null,
                    'jam'       => $this->formatJam($row->start_jam, $row->end_jam),
                    'tempat'    => $row->tempat ?: '-',
                    'status'    => $row->status_kehadiran ?: 'Belum Konfirmasi',
                    'sumber'    => 'STARLA',
                ];
            }
        }

        usort($items, function ($a, $b) {
            return [$a['grup'], $a['nama']] <=> [$b['grup'], $b['nama']];
        });

        return $items;
    }

    protected function formatJam($mulai, $selesai): string
    {
        $mulai    = $mulai ? substr((string) $mulai, 0, 5) : null;
        $selesai  = $selesai ? substr((string) $selesai, 0, 5) : null;

        if ($mulai && $selesai) {
            return $mulai . ' - ' . $selesai;
        }

        return $mulai ?: ($selesai ?: '-');
    }
}
