<?php

namespace App\Support;

use Carbon\Carbon;

class DressCodeHelper
{
    /**
     * Jadwal Baku Pakaian Dinas BPS Sepekan
     */
    public const WEEKLY_RULES = [
        1 => [
            'hari'          => 'Senin',
            'atasan'        => 'Kemeja Navy',
            'bawahan'       => 'Celana Cream / Putih',
            'deskripsi'     => 'Atasan kemeja navy dan celana cream/putih',
            'color_theme'   => 'blue',
            'atasan_hex'    => '#1e3a8a',
            'bawahan_hex'   => '#fef08a',
            'badge_bg'      => 'bg-blue-900 text-white',
            'border_color'  => 'border-blue-200',
            'bg_light'      => 'bg-blue-50/70',
            'catatan'       => 'Pakaian dinas hari Senin: Atasan kemeja navy dan celana cream/putih rapi.',
        ],
        2 => [
            'hari'          => 'Selasa',
            'atasan'        => 'Kemeja Putih',
            'bawahan'       => 'Celana Hitam',
            'deskripsi'     => 'Atasan kemeja putih dan celana hitam',
            'color_theme'   => 'slate',
            'atasan_hex'    => '#ffffff',
            'bawahan_hex'   => '#0f172a',
            'badge_bg'      => 'bg-slate-800 text-white',
            'border_color'  => 'border-slate-200',
            'bg_light'      => 'bg-slate-50/80',
            'catatan'       => 'Pakaian dinas hari Selasa: Atasan kemeja putih dan celana hitam formal.',
        ],
        3 => [
            'hari'          => 'Rabu',
            'atasan'        => 'Kemeja Orange',
            'bawahan'       => 'Celana Hitam / Cream',
            'deskripsi'     => 'Atasan kemeja orange dan celana hitam/cream',
            'color_theme'   => 'orange',
            'atasan_hex'    => '#ea580c',
            'bawahan_hex'   => '#1e293b',
            'badge_bg'      => 'bg-orange-600 text-white',
            'border_color'  => 'border-orange-200',
            'bg_light'      => 'bg-orange-50/70',
            'catatan'       => 'Pakaian dinas hari Rabu: Atasan kemeja orange ciri khas BPS dan celana hitam/cream.',
        ],
        4 => [
            'hari'          => 'Kamis',
            'atasan'        => 'Batik',
            'bawahan'       => 'Celana Hitam',
            'deskripsi'     => 'Atasan batik dan celana hitam',
            'color_theme'   => 'amber',
            'atasan_hex'    => '#92400e',
            'bawahan_hex'   => '#0f172a',
            'badge_bg'      => 'bg-amber-800 text-white',
            'border_color'  => 'border-amber-200',
            'bg_light'      => 'bg-amber-50/70',
            'catatan'       => 'Pakaian dinas hari Kamis: Atasan batik nusantara dan celana hitam.',
        ],
        5 => [
            'hari'          => 'Jumat',
            'atasan'        => 'Batik',
            'bawahan'       => 'Celana Hitam',
            'deskripsi'     => 'Atasan batik dan celana hitam (Pagi 07.30 - 10.00 bebas berolahraga)',
            'color_theme'   => 'emerald',
            'atasan_hex'    => '#047857',
            'bawahan_hex'   => '#0f172a',
            'badge_bg'      => 'bg-emerald-700 text-white',
            'border_color'  => 'border-emerald-200',
            'bg_light'      => 'bg-emerald-50/70',
            'catatan'       => 'Pagi pukul 07.30 s.d. 10.00 WITA bebas berolahraga (pakaian olahraga). Setelah pukul 10.00 WITA mengenakan atasan batik dan celana hitam.',
            'jam_olahraga'  => '07.30 - 10.00 WITA',
        ],
        6 => [
            'hari'          => 'Sabtu',
            'atasan'        => 'Bebas Rapi / Casual Dinas',
            'bawahan'       => 'Celana Bebas Rapi',
            'deskripsi'     => 'Hari Libur / Akhir Pekan',
            'color_theme'   => 'gray',
            'atasan_hex'    => '#64748b',
            'bawahan_hex'   => '#334155',
            'badge_bg'      => 'bg-gray-600 text-white',
            'border_color'  => 'border-gray-200',
            'bg_light'      => 'bg-gray-50',
            'catatan'       => 'Hari Libur / Akhir Pekan. Bagi pegawai dengan tugas piket/lembur diperkenankan mengenakan pakaian bebas rapi dan sopan.',
        ],
        0 => [
            'hari'          => 'Minggu',
            'atasan'        => 'Bebas Rapi / Casual Dinas',
            'bawahan'       => 'Celana Bebas Rapi',
            'deskripsi'     => 'Hari Libur / Akhir Pekan',
            'color_theme'   => 'gray',
            'atasan_hex'    => '#64748b',
            'bawahan_hex'   => '#334155',
            'badge_bg'      => 'bg-gray-600 text-white',
            'border_color'  => 'border-gray-200',
            'bg_light'      => 'bg-gray-50',
            'catatan'       => 'Hari Libur / Akhir Pekan.',
        ],
    ];

    /**
     * Dapatkan informasi lengkap pakaian dinas untuk tanggal tertentu (default: hari ini)
     */
    public static function getTodayDressCode($dateInput = null): array
    {
        $timezone = config('app.timezone', 'Asia/Makassar');
        $date = $dateInput ? Carbon::parse($dateInput, $timezone) : Carbon::now($timezone);

        $dayOfWeek = (int) $date->dayOfWeek; // 0 = Minggu, 1 = Senin, ..., 5 = Jumat, 6 = Sabtu
        $dayOfMonth = (int) $date->format('j');
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$dayOfWeek];
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][(int)$date->format('n')];

        $tanggalFormatted = $namaHari . ', ' . $date->format('j') . ' ' . $namaBulan . ' ' . $date->format('Y');

        // Cek jam saat ini (WITA)
        $currentHour = (int) $date->format('H');
        $currentMinute = (int) $date->format('i');
        $currentMinutesFromMidnight = ($currentHour * 60) + $currentMinute;

        $isTanggal17 = ($dayOfMonth === 17);
        $isJumat = ($dayOfWeek === 5);
        $isJamOlahragaJumat = ($isJumat && $currentMinutesFromMidnight >= (7 * 60 + 30) && $currentMinutesFromMidnight < (10 * 60));

        if ($isTanggal17) {
            $today = [
                'is_korpri'           => true,
                'hari'                => $namaHari,
                'tanggal_formatted'   => $tanggalFormatted,
                'atasan'              => 'Baju Batik KORPRI',
                'bawahan'             => 'Celana / Rok Biru Tua (Navy) / Hitam',
                'deskripsi'           => 'Pakaian Seragam Batik KORPRI Lengkap',
                'badge_label'         => 'Peringatan Hari KORPRI (Tanggal 17)',
                'badge_bg'            => 'bg-blue-900 text-yellow-300 border border-yellow-400/40',
                'theme'               => 'korpri',
                'catatan'             => 'Setiap tanggal 17, seluruh pegawai diwajibkan mengenakan Pakaian Seragam Batik KORPRI lengkap dengan peci hitam (pria) dan lencana Korpri.',
                'is_jumat'            => $isJumat,
                'is_olahraga_now'     => false,
            ];
        } else {
            $rule = self::WEEKLY_RULES[$dayOfWeek] ?? self::WEEKLY_RULES[1];
            $today = [
                'is_korpri'           => false,
                'hari'                => $namaHari,
                'tanggal_formatted'   => $tanggalFormatted,
                'atasan'              => $rule['atasan'],
                'bawahan'             => $rule['bawahan'],
                'deskripsi'           => $rule['deskripsi'],
                'badge_label'         => 'Pakaian Dinas Hari ' . $namaHari,
                'badge_bg'            => $rule['badge_bg'],
                'theme'               => $rule['color_theme'],
                'catatan'             => $rule['catatan'],
                'is_jumat'            => $isJumat,
                'is_olahraga_now'     => $isJamOlahragaJumat,
                'jam_olahraga'        => $rule['jam_olahraga'] ?? null,
            ];
        }

        return [
            'today'               => $today,
            'is_tanggal_17'       => $isTanggal17,
            'is_jumat'            => $isJumat,
            'is_olahraga_now'     => $isJamOlahragaJumat,
            'weekly_rules'        => self::getWeeklySummary(),
            'info_korpri'         => 'Setiap tanggal 17 menggunakan seragam Batik KORPRI.',
            'info_jumat_olahraga' => 'Khusus hari Jumat, pukul 07.30 s.d. 10.00 WITA bebas berolahraga (pakaian olahraga).'
        ];
    }

    /**
     * Ringkasan seluruh jadwal Senin - Jumat + KORPRI
     */
    public static function getWeeklySummary(): array
    {
        return [
            [
                'hari'        => 'Senin',
                'hari_kode'   => 1,
                'atasan'      => 'Kemeja Navy',
                'bawahan'     => 'Celana Cream / Putih',
                'badge_class' => 'bg-blue-900 text-white',
                'dot_color'   => 'bg-blue-900',
                'keterangan'  => 'Atasan kemeja navy dan celana cream/putih',
            ],
            [
                'hari'        => 'Selasa',
                'hari_kode'   => 2,
                'atasan'      => 'Kemeja Putih',
                'bawahan'     => 'Celana Hitam',
                'badge_class' => 'bg-slate-800 text-white',
                'dot_color'   => 'bg-slate-700',
                'keterangan'  => 'Atasan kemeja putih dan celana hitam',
            ],
            [
                'hari'        => 'Rabu',
                'hari_kode'   => 3,
                'atasan'      => 'Kemeja Orange',
                'bawahan'     => 'Celana Hitam / Cream',
                'badge_class' => 'bg-orange-600 text-white',
                'dot_color'   => 'bg-orange-500',
                'keterangan'  => 'Atasan kemeja orange dan celana hitam/cream',
            ],
            [
                'hari'        => 'Kamis',
                'hari_kode'   => 4,
                'atasan'      => 'Batik',
                'bawahan'     => 'Celana Hitam',
                'badge_class' => 'bg-amber-800 text-white',
                'dot_color'   => 'bg-amber-700',
                'keterangan'  => 'Atasan batik dan celana hitam',
            ],
            [
                'hari'        => 'Jumat',
                'hari_kode'   => 5,
                'atasan'      => 'Batik',
                'bawahan'     => 'Celana Hitam',
                'badge_class' => 'bg-emerald-700 text-white',
                'dot_color'   => 'bg-emerald-600',
                'keterangan'  => 'Pagi 07.30 - 10.00 WITA bebas berolahraga; dilanjutkan atasan batik dan celana hitam',
            ],
            [
                'hari'        => 'Tanggal 17',
                'hari_kode'   => 17,
                'atasan'      => 'Baju Batik KORPRI',
                'bawahan'     => 'Celana / Rok Biru Tua / Hitam',
                'badge_class' => 'bg-blue-900 text-yellow-300 border border-yellow-400/30',
                'dot_color'   => 'bg-yellow-400',
                'keterangan'  => 'Peringatan Hari KORPRI: Seragam Batik KORPRI lengkap',
            ],
        ];
    }
}
