<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;

class UpdatePegawaiJabatan2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $raw = <<<'TXT'
Hadi Susanto, M.A.	Kepala BPS Provinsi
Andi Kurniawan, S.ST., M.Si.	Kepala Bagian Umum
Nike Roso Wulandari, S.ST., M.E.	Statistisi Ahli Madya
Muh. Mulyadi, S.ST., M.E.	Statistisi Ahli Madya
Erra Septy Vibriane, S.Si., M.E.	Statistisi Ahli Madya
Harningsih, S.ST.	Statistisi Ahli Madya
Rizkiani, S.ST.	Statistisi Ahli Madya
Burit Retnowati, S.ST.	Statistisi Ahli Madya
Fatchur Rochman, S.ST., M.E.	Pranata Komputer Ahli Madya
St. Rasnani Manafi, S.E., M.Si.	Analis Pengelolaan Keuangan APBN Ahli Madya
Fatimatuz Zahro, S.ST.	Statistisi Ahli Muda
Sahunan Qola Jayati, S.ST., M.M.	Statistisi Ahli Muda
Miftahtul Khair Anwar, S.ST., M.E.	Statistisi Ahli Muda
Zaima Nurrusydah, S.ST., M.Si.	Statistisi Ahli Muda
Ridwan Kun Satria, S.Si., M.Si.	Statistisi Ahli Muda
Adiman Suriawan, S.E., M.E.	Statistisi Ahli Muda
Wa Ode Rahmina Sari, S.ST.	Statistisi Ahli Muda
Fani Dewi Astuti, S.ST.	Statistisi Ahli Muda
Rachmatiah Rachman, S.E., M.Si.	Statistisi Ahli Muda
Maulida, S.P.	Statistisi Ahli Muda
Nurlyah, S.ST.	Statistisi Ahli Muda
Titin Yuniarty, S.ST., M.Si.	Statistisi Ahli Muda
Arizka Selviana, S.ST.	Statistisi Ahli Muda
Amrin Barata, S.ST.	Statistisi Ahli Muda
Dyah Ayu Ratna Nurmalinda, S.ST.	Statistisi Ahli Muda
Parlindungan Siregar, S.Stat.	Statistisi Ahli Muda
Muhammad Haris La Ode, S.ST., M.E.K.K.	Statistisi Ahli Muda
Junedi, S.ST., M.Si.	Statistisi Ahli Muda
Diana Pratiwi Moningka, S.E.	Statistisi Ahli Muda
Jaka Pratama, S.ST., M.Stat.	Statistisi Ahli Muda
Wa Ode Vitria Astika Sari, S.Si., M.Ec.Dev.	Statistisi Ahli Muda
Muhammad Ahnan Prastito, S.ST.	Statistisi Ahli Muda
Azwar Surahman, S.ST., M.A.	Statistisi Ahli Muda
Farha Imamiah Gaffar, S.E.	Statistisi Ahli Muda
Wulan Isfah Jamil, S.ST., M.S.E.	Statistisi Ahli Muda
Ristama Ika Pretty Manurung, S.ST.	Statistisi Ahli Muda
Suci Safitriani, S.ST.	Pranata Komputer Ahli Muda
Vianey Weda Rahesti, S.ST., M.E.K.K.	Pranata Komputer Ahli Muda
Hendry Pramudia Putra, S.Stat.	Pranata Komputer Ahli Muda
Muhammad Rizal Karim, S.ST.	Pranata Komputer Ahli Muda
Muhammad Arifiansyah Ayub, S.ST., M.Sc.	Pranata Komputer Ahli Muda
Muliani Kadir, S.Si., M.M.	Analis Pengelolaan Keuangan APBN Ahli Muda
Raimon Mahmudin Darma Sakti, S.E.	Pengelola Pengadaan Barang dan Jasa Ahli Muda
Ardiman Adami, S.Psi.	Analis SDM Aparatur Ahli Muda
La Ode Haerul Saleh Wahid, S.H.	Analis SDM Aparatur Ahli Muda
Irma Suryani, S.Si.	Arsiparis Ahli Muda
Hermawan, S.ST.	Analis Anggaran Ahli Muda
Wa Ode Hasmayuli, S.ST., M.Si.	Analis Anggaran Ahli Muda
Syifa Reihana, S.ST.	Statistisi Ahli Pertama
Denny Rizky Firmansyah, S.Tr.Stat.	Statistisi Ahli Pertama
Irna Octaviana Latif, S.E.	Statistisi Ahli Pertama
Mulawarman, S.Tr.Stat.	Statistisi Ahli Pertama
Tino Aprilian, S.Tr.Stat.	Statistisi Ahli Pertama
Mochamad Wildan Maulana, S.Tr.Stat.	Statistisi Ahli Pertama
La Emi, S.Pd., M.Si.	Statistisi Ahli Pertama
Erni Octaviani, S.Tr.Stat.	Statistisi Ahli Pertama
Muhammad Syadrie, S.Tr.Stat.	Statistisi Ahli Pertama
Firda Agil Al Rasyid, S.Tr.Stat.	Statistisi Ahli Pertama
Maudy Fitri Liani, S.Tr.Stat.	Statistisi Ahli Pertama
Agriyandi Rizaldi, S.Tr.Stat.	Statistisi Ahli Pertama
Rahmadan Salehani, S.Ak.	Statistisi Ahli Pertama
Moh. Hardiansyah Mashar, S.ST.	Statistisi Ahli Pertama
Astutyningsih, S.P.	Pranata Komputer Ahli Pertama
Rian Alfa Nurfalah, S.Tr.Stat.	Pranata Komputer Ahli Pertama
Muhammad Haidar Fikri Januar, S.Tr.Stat.	Pranata Komputer Ahli Pertama
Rezky Susanty Nurdin, S.Stat., M.E.	Analis Pengelolaan Keuangan APBN Ahli Pertama
Asrafiah, S.E.	Analis SDM Aparatur Ahli Pertama
Uyun Racmawati, S.Psi.	Analis SDM Aparatur Ahli Pertama
La Riko, S.Ak.	Pengelola Pengadaan Barang dan Jasa Ahli Pertama
Fitharia Susiyanti, S.E.	Arsiparis Ahli Pertama
Masdiana, S.Pd.	Arsiparis Ahli Pertama
Harlianto Tumanggor, S.S.	Pranata Hubungan Masyarakat Ahli Pertama
Diase, S.H.	Penyuluh Hukum Ahli Pertama
Mani Daud, S.E., M.Si.	Statistisi Penyelia
Eka Baktiar, M.M.	Pranata Keuangan APBN Penyelia
Herawati	Statistisi Mahir
Komang Damike, A.Md.	Statistisi Mahir
Slamet Riyadi, A.Md.	Penata Laksana Barang Mahir
Siti Rohima, A.Md.Stat.	Statistisi Terampil
Ziko Mildulandy Rahim, A.Md.Stat.	Statistisi Terampil
Andi Jumaena, A.Md.Stat.	Statistisi Terampil
Emmanuella Caesarah Agatha Sumenge, A.Md.	Arsiparis Terampil
Lu`Luin Saabiqah, A.Md.Kb.N.	Pranata Keuangan APBN Terampil
Nur Syamsidar, A.Md.Kom.	Pranata SDM Aparatur Terampil
Darul, S.I.Kom.	Pengawas Pendataan Statistik
Manggoa Joni	Pengolah Data
Khaidir	Pengolah Data
Predi Siampa	Teknisi Pemeliharaan Sarana dan Prasarana
Yunus Samuel Tandi Bua	Pengelola Surat
Sri Nurmala Ningsih, S.Tr.Stat.	CPNS
Siti Nurrahmawati A., S.E.	Penata Layanan Operasional
Ardin Jani, S.Si.	Penata Layanan Operasional
Arby, S.T.	Penata Layanan Operasional
Ifan Anshari, A.Md.	Pengelola Layanan Operasional
Abdul Djalil	Operator Layanan Operasional
Muhammad Yusril	Operator Layanan Operasional
Iksan Djuku	Operator Layanan Operasional
Sirnan Setiawan	Operator Layanan Operasional
Tamzir	Operator Layanan Operasional
Mat Asdi	Pengelola Umum Operasional
Putu Suweda	Pengelola Umum Operasional
TXT;

        $newUsersMeta = [
            'Hadi Susanto, M.A.' => ['niplama' => '340013332', 'nm_tim' => 'Pimpinan', 'group' => 'Kepala BPS', 'username' => 'hadisusanto', 'email' => 'hadisusanto@bps.go.id', 'jk' => '1'],
            'Uyun Racmawati, S.Psi.' => ['niplama' => '340055407', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'uyun.racmawati', 'email' => 'uyun.racmawati@bps.go.id', 'jk' => '2'],
            'Masdiana, S.Pd.' => ['niplama' => '340065085', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'masdiana', 'email' => 'masdiana@bps.go.id', 'jk' => '2'],
            'Sri Nurmala Ningsih, S.Tr.Stat.' => ['niplama' => '340066522', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'srinurmala', 'email' => 'srinurmala@bps.go.id', 'jk' => '2'],
            'Siti Nurrahmawati A., S.E.' => ['niplama' => '340065663', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'sitinurrahmawati', 'email' => 'sitinurrahmawati@bps.go.id', 'jk' => '2'],
            'Ardin Jani, S.Si.' => ['niplama' => '340064240', 'nm_tim' => 'Statistik Pertanian', 'group' => 'Statistik Pertanian', 'username' => 'ardinjani', 'email' => 'ardinjani@bps.go.id', 'jk' => '1'],
            'Arby, S.T.' => ['niplama' => '340064231', 'nm_tim' => 'Statistik Sumber Daya Hayati', 'group' => 'Statistik Sumber Daya Hayati', 'username' => 'arby', 'email' => 'arby@bps.go.id', 'jk' => '1'],
            'Ifan Anshari, A.Md.' => ['niplama' => '340064799', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'ifan.anshari', 'email' => 'ifan.anshari@bps.go.id', 'jk' => '1'],
            'Abdul Djalil' => ['niplama' => '340064008', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'abdul.djalil', 'email' => 'abdul.djalil@bps.go.id', 'jk' => '1'],
            'Muhammad Yusril' => ['niplama' => '340065223', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'muhammad.yusril', 'email' => 'muhammad.yusril@bps.go.id', 'jk' => '1'],
            'Iksan Djuku' => ['niplama' => '340064806', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'iksan.djuku', 'email' => 'iksan.djuku@bps.go.id', 'jk' => '1'],
            'Sirnan Setiawan' => ['niplama' => '340065657', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'sirnan.setiawan', 'email' => 'sirnan.setiawan@bps.go.id', 'jk' => '1'],
            'Tamzir' => ['niplama' => '340065822', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'tamzir', 'email' => 'tamzir@bps.go.id', 'jk' => '1'],
            'Mat Asdi' => ['niplama' => '340065093', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'mat.asdi', 'email' => 'mat.asdi@bps.go.id', 'jk' => '1'],
            'Putu Suweda' => ['niplama' => '340065395', 'nm_tim' => 'Bagian Umum', 'group' => 'Bagian Umum', 'username' => 'putu.suweda', 'email' => 'putu.suweda@bps.go.id', 'jk' => '1'],
        ];

        // 1. Nonaktifkan jabatan lama Agnes Widiastuti dan hapus grup Kepala BPS
        DB::table('groups')->where('niplama', '340012043')->where('grup', 'like', '%Kepala BPS%')->delete();
        DB::table('users_jabatan')->where('id_users', 1)->update([
            'is_active' => 0,
            'nm_jabatan' => 'Purnatugas (Mantan Kepala BPS)',
            'updated_at' => now()
        ]);

        // 2. Nonaktifkan Plt Kepala BPS dan Kepala Bagian Umum lama
        DB::table('users_jabatan')
            ->where('id_users', 45)
            ->where('nm_jabatan', 'like', '%Kepala BPS%')
            ->update(['is_active' => 0, 'updated_at' => now()]);

        DB::table('users_jabatan')
            ->where('nm_jabatan', 'like', '%Kepala Bagian Umum%')
            ->update(['is_active' => 0, 'updated_at' => now()]);

        // 3. Proses 101 pegawai dan jabatan baru
        $lines = explode("\n", trim($raw));
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            $parts = preg_split('/\t+/', $line);
            if (count($parts) < 2) $parts = preg_split('/\s{2,}/', $line);
            $namaGelar = trim($parts[0]);
            $jabatanBaru = trim($parts[1] ?? '');

            $baseName = trim(explode(',', $namaGelar)[0]);
            $gelarBelakang = count(explode(',', $namaGelar)) > 1 ? trim(substr($namaGelar, strpos($namaGelar, ',') + 1)) : null;

            $user = User::where('nama_lengkap', $namaGelar)
                ->orWhere('nama_lengkap', $baseName)
                ->orWhere('nama_lengkap', 'like', '%' . $baseName . '%')
                ->first();

            if (!$user) {
                $meta = $newUsersMeta[$namaGelar] ?? ($newUsersMeta[$baseName] ?? null);
                $nip = $meta['niplama'] ?? 'NIP' . rand(100000, 999999);
                $username = $meta['username'] ?? strtolower(str_replace(' ', '', $baseName));
                $email = $meta['email'] ?? $username . '@bps.go.id';
                $jk = $meta['jk'] ?? '1';

                $user = new User();
                $user->token_id = md5(uniqid($nip, true));
                $user->niplama = $nip;
                $user->nipbaru = $nip;
                $user->nama_lengkap = $namaGelar;
                $user->gelar_belakang = $gelarBelakang;
                $user->jk = $jk;
                $user->tanggal_lahir = '1980-01-01';
                $user->kabkot_asal = '7400';
                $user->foto = 'user.png';
                $user->username = $username;
                $user->email = $email;
                $user->password = bcrypt('bps7400');
                $user->save();

                if (!empty($meta['group'])) {
                    DB::table('groups')->updateOrInsert(
                        ['niplama' => $nip],
                        ['grup' => $meta['group']]
                    );
                }
            } else {
                $user->nama_lengkap = $namaGelar;
                if ($gelarBelakang) {
                    $user->gelar_belakang = $gelarBelakang;
                }
                $user->save();
            }

            // Set jabatan lama nonaktif
            DB::table('users_jabatan')
                ->where('id_users', $user->id)
                ->update(['is_active' => 0]);

            // Insert jabatan aktif baru
            DB::table('users_jabatan')->insert([
                'id_users' => $user->id,
                'id_jabatan' => 0,
                'id_fungsional' => null,
                'id_fungsional_jabatan' => null,
                'id_organisasi' => '',
                'id_satker' => '7400',
                'tmt' => '2024-01-01',
                'no_sk' => null,
                'tgl_sk' => null,
                'nm_jabatan' => $jabatanBaru,
                'is_pindahsatker' => 0,
                'is_active' => 1,
                'is_validated' => 1,
                'catatan' => 'Revisi SIKEREN 2026',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($namaGelar === 'Hadi Susanto, M.A.') {
                DB::table('groups')->updateOrInsert(
                    ['niplama' => $user->niplama],
                    ['grup' => 'Kepala BPS']
                );
            }

            if (str_contains($namaGelar, 'Andi Kurniawan')) {
                DB::table('groups')->updateOrInsert(
                    ['niplama' => $user->niplama],
                    ['grup' => 'Bagian Umum']
                );
            }
        }
    }
}
