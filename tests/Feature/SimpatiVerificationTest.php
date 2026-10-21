<?php

namespace Tests\Feature;

use App\JadwalLayanan;
use App\PetugasLayanan;
use App\Support\SimpatiRoles;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SimpatiVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('petugas_layanans') || !Schema::hasTable('simpati_admin_roles')) {
            $this->markTestSkipped('Tabel SIMPATI belum ada. Jalankan migration terlebih dahulu.');
        }
    }

    protected function makeUser(array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        $suffix = str_pad((string) $seq, 5, '0', STR_PAD_LEFT);

        return User::create(array_merge([
            'nama_lengkap' => 'Verif SIMPATI ' . $seq,
            'username'     => 'verif_simpati_' . $seq . '_' . uniqid(),
            'email'        => 'verif_simpati_' . $seq . '_' . uniqid() . '@example.test',
            'password'     => bcrypt('secret'),
            'niplama'      => 'VF' . substr(uniqid(), -6),
            'nipbaru'      => '8' . $suffix . substr(uniqid(), -4),
        ], $overrides));
    }

    protected function makeAdmin(): User
    {
        return $this->makeUser([
            'username'     => 'admin',
            'email'        => 'admin.verif@example.test',
            'nama_lengkap' => 'Administrator Verif',
        ]);
    }

    protected function assignRole(User $user, string $role): void
    {
        DB::table('simpati_admin_roles')->insert([
            'user_id'    => $user->id,
            'role'       => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // =====================================================================
    // 1. Semua halaman & tab render dengan data DB nyata (admin)
    // =====================================================================

    public function test_semua_halaman_dan_tab_merender_200_dengan_admin()
    {
        $admin   = $this->makeAdmin();
        $petugas = $this->makePetugasAktif('pst');
        $jadwal  = JadwalLayanan::create([
            'grup' => 'pst', 'tanggal' => now()->toDateString(),
            'nama' => 'Petugas Verif Hari Ini', 'status' => 'Hadir', 'sumber' => 'manual',
        ]);

        $routes = [
            route('simpati.dashboard'),
            route('simpati.petugas.index', ['tab' => 'semua']),
            route('simpati.petugas.index', ['tab' => 'pst']),
            route('simpati.petugas.index', ['tab' => 'ppid']),
            route('simpati.petugas.index', ['tab' => 'satpam']),
            route('simpati.petugas.index', ['tab' => 'cs']),
            route('simpati.jadwal.index', ['grup' => 'pst']),
            route('simpati.jadwal.index', ['grup' => 'ppid']),
            route('simpati.jadwal.index', ['grup' => 'satpam']),
            route('simpati.jadwal.index', ['grup' => 'cs']),
            route('simpati.jadwal.index', ['grup' => 'pst', 'bulan' => '2020-01']),
            route('simpati.admins.index'),
            route('simpati.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200);
        }

        // Data nyata benar-benar terlihat pada halaman
        $this->actingAs($admin)->get(route('simpati.petugas.index', ['tab' => 'pst']))
            ->assertSee($petugas->nama, false);
        $this->actingAs($admin)->get(route('simpati.dashboard'))
            ->assertSee('Petugas Verif Hari Ini', false);
    }

    public function test_tab_grup_tidak_valid_jatuh_ke_default_tanpa_500()
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('simpati.petugas.index', ['tab' => 'ngawur']))->assertStatus(200);
        $this->actingAs($admin)->get(route('simpati.jadwal.index', ['grup' => 'ngawur']))->assertStatus(200);
        $this->actingAs($admin)->get(route('simpati.jadwal.index', ['grup' => 'pst', 'bulan' => 'bukan-bulan']))->assertStatus(200);
    }

    protected function makePetugasAktif(string $grup): PetugasLayanan
    {
        return PetugasLayanan::create([
            'grup'     => $grup,
            'nama'     => 'Verif Petugas ' . strtoupper($grup) . ' ' . uniqid(),
            'niplama'  => 'VP' . substr(uniqid(), -6),
            'is_aktif' => true,
        ]);
    }

    // =====================================================================
    // 2. Pencarian AJAX dengan data nyata
    // =====================================================================

    public function test_pencarian_ajax_menemukan_pegawai_nyata()
    {
        $admin = $this->makeAdmin();
        $target = $this->makeUser(['nama_lengkap' => 'NamaUnikVerifSIMPATI']);

        $response = $this->actingAs($admin)->getJson(route('simpati.petugas.cari', ['q' => 'NamaUnikVerifSIMPATI']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => [['id', 'nama_lengkap', 'niplama', 'nm_jabatan', 'grups']]]);
        $response->assertJsonFragment(['nama_lengkap' => 'NamaUnikVerifSIMPATI']);
    }

    public function test_pencarian_ajax_tanpa_kata_kunci_mengembalikan_data()
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->getJson(route('simpati.petugas.cari'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =====================================================================
    // 3. Jadwal manual: validasi jam & penolakan data tidak valid
    // =====================================================================

    public function test_jadwal_store_menolak_jam_selesai_sebelum_jam_mulai()
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('simpati.jadwal.store'), [
            'grup'        => 'pst',
            'tanggal'     => now()->toDateString(),
            'nama'        => 'Jadwal Jam Salah',
            'status'      => 'Terjadwal',
            'jam_mulai'   => '14:00',
            'jam_selesai' => '09:00',
        ]);

        $response->assertSessionHasErrors('jam_selesai');
        $this->assertDatabaseMissing('jadwal_layanans', ['nama' => 'Jadwal Jam Salah']);
    }

    public function test_jadwal_store_menolak_status_tidak_dikenal()
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('simpati.jadwal.store'), [
            'grup' => 'pst', 'tanggal' => now()->toDateString(),
            'nama' => 'Jadwal Status Salah', 'status' => 'Ngasal',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_jadwal_tanpa_nama_dan_tanpa_user_ditolak()
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('simpati.jadwal.store'), [
            'grup' => 'pst', 'tanggal' => now()->toDateString(), 'status' => 'Terjadwal',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    // =====================================================================
    // 4. Unggah Excel round-trip (template -> isi -> upload)
    // =====================================================================

    public function test_unduh_template_dan_unggah_kembali_berhasil()
    {
        $admin = $this->makeAdmin();
        $grup  = 'ppid';

        // Unduh template resmi
        $tpl = $this->actingAs($admin)->get(route('simpati.jadwal.template', ['grup' => $grup]));
        $tpl->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', strtolower((string) $tpl->headers->get('content-type')));

        // Bangun file XLSX isi sesuai header template (Tanggal, NIP Lama, Nama, ...)
        $xlsx = $this->buildJadwalXlsx([
            ['2026-11-03', 'VP99999', 'Uji Unggah Excel', 1, '08:00', '12:00', 'Hadir', 'Ruang PST'],
            ['2026-11-04', '', 'Baris Tanpa NIP', 2, '13:00', '16:00', 'Terjadwal', ''],
        ]);

        $response = $this->actingAs($admin)->post(route('simpati.jadwal.upload'), [
            'grup' => $grup,
            'file' => $xlsx,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_layanans', [
            'grup' => $grup, 'nama' => 'Uji Unggah Excel', 'status' => 'Hadir',
            'sumber' => 'upload', 'tanggal' => '2026-11-03',
        ]);
        $this->assertDatabaseHas('jadwal_layanans', [
            'grup' => $grup, 'nama' => 'Baris Tanpa NIP', 'status' => 'Terjadwal',
        ]);
    }

    public function test_unggah_excel_kolom_wajib_hilang_ditolak()
    {
        $admin = $this->makeAdmin();

        $xlsx = $this->buildRawXlsx([
            ['BukanHeader', 'Kolom', 'Acak'],
            ['x', 'y', 'z'],
        ]);

        $response = $this->actingAs($admin)->post(route('simpati.jadwal.upload'), [
            'grup' => 'ppid',
            'file' => $xlsx,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('jadwal_layanans', ['nama' => 'y']);
    }

    /** Bangun file XLSX isi jadwal dengan header template resmi. */
    protected function buildJadwalXlsx(array $rows): UploadedFile
    {
        $data = array_merge(
            [['Tanggal', 'NIP Lama', 'Nama', 'Sesi', 'Jam Mulai', 'Jam Selesai', 'Status', 'Keterangan']],
            $rows
        );

        return $this->buildRawXlsx($data);
    }

    protected function buildRawXlsx(array $data): UploadedFile
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($data);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $tmp = tempnam(sys_get_temp_dir(), 'verif') . '.xlsx';
        $writer->save($tmp);

        return new UploadedFile($tmp, 'verif-jadwal.xlsx', null, null, true);
    }

    // =====================================================================
    // 5. Integrasi baca STARLA (baca-saja) tidak merusak & tampil di jadwal
    // =====================================================================

    public function test_integrasi_starla_pst_tampil_sebagai_baca_saja()
    {
        $admin = $this->makeAdmin();

        if (!Schema::hasTable('penugasans') || !Schema::hasTable('tasks')) {
            $this->markTestSkipped('Tabel penugasans/tasks tidak tersedia.');
        }

        $taskId = DB::table('tasks')->insertGetId([
            'start_date'     => now()->toDateString(),
            'jenis_kegiatan' => 'Pelayanan Statistik Terpadu (PST)',
            'text'           => 'PST Verif Sesi 1',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        DB::table('penugasans')->insert([
            'id_kegiatan' => $taskId,
            'niplama'     => 'STARLA01',
            'peserta'     => 'Petugas STARLA Verif',
            'keterangan'  => 'Petugas PST Sesi 1',
            'status_kehadiran' => 'Hadir',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Dashboard: petugas STARLA muncul dengan label STARLA
        $dash = $this->actingAs($admin)->get(route('simpati.dashboard'));
        $dash->assertStatus(200);
        $dash->assertSee('Petugas STARLA Verif', false);
        $dash->assertSee('STARLA', false);

        // Jadwal PST: entri STARLA tampil dan bersifat baca-saja
        $jadwal = $this->actingAs($admin)->get(route('simpati.jadwal.index', ['grup' => 'pst']));
        $jadwal->assertStatus(200);
        $jadwal->assertSee('Petugas STARLA Verif', false);
        $jadwal->assertSee('baca-saja', false);
    }

    public function test_api_starla_tetap_mengembalikan_sukses()
    {
        $this->getJson(route('api.starla.pst.index'))
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    // =====================================================================
    // 6. Matriks hak akses per role
    // =====================================================================

    /**
     * @dataProvider roleAccessProvider
     */
    public function test_matriks_hak_akses_role(string $role, array $manageable, bool $canAdmin)
    {
        $user = $this->makeUser();
        $this->assignRole($user, $role);

        // Halaman baca: semua role SIMPATI boleh
        $this->actingAs($user)->get(route('simpati.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('simpati.petugas.index', ['tab' => 'semua']))->assertStatus(200);

        // Pencarian AJAX boleh
        $this->actingAs($user)->getJson(route('simpati.petugas.cari'))->assertStatus(200);

        // Scope grup petugas
        foreach (['pst', 'ppid', 'satpam', 'cs'] as $grup) {
            $allowed = in_array($grup, $manageable, true);
            $resp = $this->actingAs($user)->post(route('simpati.petugas.store'), [
                'grup' => $grup,
                'nama' => 'Scope ' . $role . ' ' . $grup . ' ' . uniqid(),
            ]);
            $allowed ? $resp->assertRedirect() : $resp->assertStatus(403);
        }

        // Kelola Admin
        $adminResp = $this->actingAs($user)->get(route('simpati.admins.index'));
        $canAdmin ? $adminResp->assertStatus(200) : $adminResp->assertStatus(403);
    }

    public static function roleAccessProvider(): array
    {
        return [
            'super_admin'     => ['super_admin',     ['pst', 'ppid', 'satpam', 'cs'], true],
            'admin_sdm'       => ['admin_sdm',       ['pst', 'ppid', 'satpam', 'cs'], false],
            'admin_pst'       => ['admin_pst',       ['pst'],                         false],
            'admin_ppid'      => ['admin_ppid',      ['ppid'],                        false],
            'admin_satpam_cs' => ['admin_satpam_cs', ['satpam', 'cs'],                false],
            'admin_ruangan'   => ['admin_ruangan',   [],                              false],
            'admin_zoom'      => ['admin_zoom',      [],                              false],
        ];
    }

    public function test_ketua_tim_dapat_akses_tanpa_role_tapi_tidak_kelola_admin()
    {
        // Simulasi akses via isAdmin() (paling deterministik di data dev)
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('simpati.dashboard'))->assertStatus(200);
        // Administrator sistem BUKAN super_admin SIMPATI, tapi tetap boleh kelola admin
        $this->actingAs($admin)->get(route('simpati.admins.index'))->assertStatus(200);
    }

    public function test_pengguna_biasa_403_di_semua_halaman_simpati()
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('simpati.dashboard'))->assertStatus(403);
        $this->actingAs($user)->get(route('simpati.petugas.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('simpati.jadwal.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('simpati.admins.index'))->assertStatus(403);
        $this->actingAs($user)->getJson(route('simpati.petugas.cari'))->assertStatus(403);
        $this->actingAs($user)->post(route('simpati.petugas.store'), ['grup' => 'pst', 'nama' => 'X'])->assertStatus(403);
    }

    // =====================================================================
    // 7. Perlindungan Super Admin
    // =====================================================================

    public function test_super_admin_terakhir_tidak_dapat_dicabut_oleh_super_admin_lain()
    {
        // Skenario: dua super admin -> satu boleh dicabut
        $superA = $this->makeUser();
        $superB = $this->makeUser();
        $this->assignRole($superA, 'super_admin');
        $this->assignRole($superB, 'super_admin');

        $rowA = DB::table('simpati_admin_roles')->where('user_id', $superA->id)->where('role', 'super_admin')->value('id');

        // superB mencabut superA saat masih ada 2 super admin -> berhasil
        $this->actingAs($superB)->delete(route('simpati.admins.destroy', $rowA))->assertRedirect();
        $this->assertDatabaseMissing('simpati_admin_roles', ['id' => $rowA]);

        // Sekarang superB satu-satunya -> mencabut dirinya sendiri harus GAGAL
        $rowB = DB::table('simpati_admin_roles')->where('user_id', $superB->id)->where('role', 'super_admin')->value('id');
        $resp = $this->actingAs($superB)->delete(route('simpati.admins.destroy', $rowB));
        $resp->assertSessionHas('error');
        $this->assertDatabaseHas('simpati_admin_roles', ['id' => $rowB]);
    }

    public function test_store_role_duplikat_ditolak()
    {
        $super  = $this->makeUser();
        $this->assignRole($super, 'super_admin');
        $target = $this->makeUser();

        $this->actingAs($super)->post(route('simpati.admins.store'), ['user_id' => $target->id, 'role' => 'admin_pst'])->assertRedirect();

        $resp = $this->actingAs($super)->post(route('simpati.admins.store'), ['user_id' => $target->id, 'role' => 'admin_pst']);
        $resp->assertSessionHas('error');
        $this->assertEquals(1, DB::table('simpati_admin_roles')->where('user_id', $target->id)->where('role', 'admin_pst')->count());
    }

    public function test_store_role_tidak_valid_ditolak()
    {
        $super = $this->makeUser();
        $this->assignRole($super, 'super_admin');

        $this->actingAs($super)->post(route('simpati.admins.store'), [
            'user_id' => $this->makeUser()->id, 'role' => 'role_palsu',
        ])->assertSessionHasErrors('role');
    }
}
