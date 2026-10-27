<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\User;

class PakaianDinasService
{
    /**
     * Mendapatkan informasi ketentuan pakaian dinas berdasarkan tanggal
     */
    public static function getInfoPakaian($date = null): array
    {
        $carbon = $date ? Carbon::parse($date, 'Asia/Makassar') : Carbon::now('Asia/Makassar');
        $dayOfMonth = (int) $carbon->format('d');
        $dayOfWeek = (int) $carbon->format('w'); // 0 = Minggu, 1 = Senin, ..., 5 = Jumat, 6 = Sabtu

        // Cek jika tanggal 17 (Hari Korpri)
        if ($dayOfMonth === 17) {
            return [
                'kode'       => 'korpri',
                'judul'      => 'Seragam KORPRI Lengkap (Tanggal 17)',
                'badge'      => 'Wajib Tanggal 17',
                'deskripsi'  => 'Mengenakan Pakaian Seragam Batik KORPRI lengkap dengan lencana KORPRI, tanda pengenal, peci hitam (pria), atau jilbab biru tua/KORPRI (wanita berhijab), serta bawahan gelap.',
                'warna'      => 'blue',
                'icon'       => 'flag',
                'is_korpri'  => true,
            ];
        }

        // Cek jika besok adalah tanggal 17
        $tomorrow = $carbon->copy()->addDay();
        $isTomorrow17 = ((int) $tomorrow->format('d') === 17);

        // Ketentuan hari kerja reguler BPS Sultra
        switch ($dayOfWeek) {
            case 1: // Senin
            case 2: // Selasa
                return [
                    'kode'         => 'pdh_putih',
                    'judul'        => 'Pakaian Dinas Harian (PDH) Putih',
                    'badge'        => $dayOfWeek === 1 ? 'Senin' : 'Selasa',
                    'deskripsi'    => 'Kemeja putih lengan panjang/pendek berdasi/rapi dengan bawahan celana/rok warna gelap/hitam dan sepatu pantofel.',
                    'warna'        => 'slate',
                    'icon'         => 'user',
                    'is_korpri'    => false,
                    'besok_korpri' => $isTomorrow17,
                ];

            case 3: // Rabu
                return [
                    'kode'         => 'tenun_daerah',
                    'judul'        => 'Pakaian Tenun Motif Khas Sultra',
                    'badge'        => 'Rabu Tenun',
                    'deskripsi'    => 'Mengenakan busana berbahan kain tenun tradisional atau batik khas Sulawesi Tenggara dengan bawahan rapi.',
                    'warna'        => 'indigo',
                    'icon'         => 'sparkles',
                    'is_korpri'    => false,
                    'besok_korpri' => $isTomorrow17,
                ];

            case 4: // Kamis
                return [
                    'kode'         => 'batik_nasional',
                    'judul'        => 'Batik Nasional / Tenun Nusantara',
                    'badge'        => 'Kamis Batik',
                    'deskripsi'    => 'Kemeja/blus batik nasional atau tenun nusantara dengan bawahan sopan dan rapi.',
                    'warna'        => 'amber',
                    'icon'         => 'shirt',
                    'is_korpri'    => false,
                    'besok_korpri' => $isTomorrow17,
                ];

            case 5: // Jumat
                return [
                    'kode'         => 'olahraga_muslim',
                    'judul'        => 'Pakaian Olahraga / Kasual Sopan',
                    'badge'        => 'Jumat Sehat',
                    'deskripsi'    => 'Kaos/pakaian olahraga untuk senam pagi, dilanjutkan busana muslim atau batik kasual yang sopan dan rapi.',
                    'warna'        => 'emerald',
                    'icon'         => 'heart',
                    'is_korpri'    => false,
                    'besok_korpri' => $isTomorrow17,
                ];

            default: // Sabtu & Minggu
                return [
                    'kode'         => 'libur',
                    'judul'        => 'Hari Libur Kedinasan (Weekend)',
                    'badge'        => 'Libur',
                    'deskripsi'    => 'Bebas dan rapi bagi pegawai yang memiliki penugasan lapangan atau lembur kedinasan.',
                    'warna'        => 'slate',
                    'icon'         => 'home',
                    'is_korpri'    => false,
                    'besok_korpri' => $isTomorrow17,
                ];
        }
    }

    /**
     * Broadcast pengumuman pakaian (misal tanggal 17 Korpri) ke seluruh pegawai
     */
    public static function broadcastPakaianKorpri(): int
    {
        $today = Carbon::now('Asia/Makassar');
        $dayOfMonth = (int) $today->format('d');

        $judul = $dayOfMonth === 17 
            ? 'Pengingat Pakaian Dinas: Seragam KORPRI Hari Ini'
            : 'Pengingat Pakaian Dinas: Besok Tanggal 17 Wajib Seragam KORPRI';

        $pesan = $dayOfMonth === 17
            ? 'Hari ini tanggal 17, seluruh pegawai BPS diwajibkan mengenakan Pakaian Seragam Batik KORPRI Lengkap sesuai ketentuan kedinasan.'
            : 'Mengingatkan kepada seluruh pegawai bahwa besok adalah tanggal 17. Mohon mempersiapkan dan mengenakan Seragam KORPRI Lengkap.';

        $users = User::all();
        $count = 0;

        foreach ($users as $u) {
            // Cek apakah sudah pernah menerima notif ini hari ini
            $exists = DB::table('notifications')
                ->where('notifiable_id', $u->id)
                ->where('type', 'App\Notifications\InformasiPakaianNotification')
                ->whereDate('created_at', $today->toDateString())
                ->exists();

            if (!$exists) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\Notifications\InformasiPakaianNotification',
                    'notifiable_type' => 'App\User',
                    'notifiable_id'   => $u->id,
                    'data'            => json_encode([
                        'judul' => $judul,
                        'pesan' => $pesan,
                        'url'   => '/dashboard',
                    ]),
                    'read_at'         => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }
}
