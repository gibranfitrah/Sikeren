<?php

namespace Tests\Feature;

use App\JadwalLayanan;
use App\PetugasLayanan;
use App\Support\SimpatiRoles;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SimpatiFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('petugas_layanans') || !Schema::hasTable('simpati_admin_roles')) {
            $this->markTestSkipped('Tabel SIMPATI belum ada. Jalankan migration terlebih dahulu.');
        }
    }

    /** Buat user uji tanpa menyentuh akun asli (username unik per test). */
    protected function makeUser(array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        $suffix = str_pad((string) $seq, 5, '0', STR_PAD_LEFT);

        return User::create(array_merge([
            'nama_lengkap' => 'Uji SIMPATI ' . $seq,
            'username'     => 'uji_simpati_' . $seq . '_' . uniqid(),
            'email'        => 'uji_simpati_' . $seq . '_' . uniqid() . '@example.test',
            'password'     => bcrypt('secret'),
            'niplama'      => 'UJI' . $suffix,
            'nipbaru'      => '9' . $suffix . substr(uniqid(), -4),
        ], $overrides));
    }

    protected function makeAdmin(): User
    {
        return $this->makeUser([
            'username'     => 'admin',
            'email'        => 'admin.uji@example.test',
            'nama_lengkap' => 'Administrator Uji',
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

    protected function makePetugas(string $grup, array $overrides = []): PetugasLayanan
    {
        return PetugasLayanan::create(array_merge([
            'grup'     => $grup,
            'nama'     => 'Petugas ' . strtoupper($grup) . ' ' . uniqid(),
            'niplama'  => 'PTG' . substr(uniqid(), -5),
            'is_aktif' => true,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // Dashboard
    // ---------------------------------------------------------------------

    public function test_dashboard_dapat_diakses_admin()
    {
        $response = $this->actingAs($this->makeAdmin())->get(route('simpati.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard', false);
    }

    public function test_dashboard_403_untuk_pengguna_tanpa_akses()
    {
        $response = $this->actingAs($this->makeUser())->get(route('simpati.dashboard'));

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------------
    // Kelola Petugas
    // ---------------------------------------------------------------------

    public function test_petugas_index_tab_semua_dan_grup_dapat_diakses()
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('simpati.petugas.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('simpati.petugas.index', ['tab' => 'pst']))->assertStatus(200);
        $this->actingAs($admin)->get(route('simpati.petugas.index', ['tab' => 'satpam']))->assertStatus(200);
    }

    public function test_admin_dapat_menambah_petugas_manual()
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('simpati.petugas.store'), [
            'grup'    => 'cs',
            'nama'    => 'CS Uji Manual',
            'niplama' => 'CS001',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('petugas_layanans', [
            'grup' => 'cs',
            'nama' => 'CS Uji Manual',
        ]);
    }

    public function test_admin_pst_tidak_boleh_menambah_grup_satpam_403()
    {
        $user = $this->makeUser();
        $this->assignRole($user, 'admin_pst');

        $response = $this->actingAs($user)->post(route('simpati.petugas.store'), [
            'grup' => 'satpam',
            'nama' => 'Satpam Uji',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('petugas_layanans', ['nama' => 'Satpam Uji']);
    }

    public function test_admin_pst_dapat_menambah_grup_pst()
    {
        $user = $this->makeUser();
        $this->assignRole($user, 'admin_pst');

        $response = $this->actingAs($user)->post(route('simpati.petugas.store'), [
            'grup' => 'pst',
            'nama' => 'PST Uji Admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('petugas_layanans', ['grup' => 'pst', 'nama' => 'PST Uji Admin']);
    }

    public function test_update_dan_destroy_petugas()
    {
        $admin    = $this->makeAdmin();
        $petugas  = $this->makePetugas('ppid', ['nama' => 'PPID Uji']);

        $this->actingAs($admin)->post(route('simpati.petugas.update', $petugas->id), [
            'is_aktif' => '0',
            'catatan'  => 'Sedang cuti panjang',
        ])->assertRedirect();

        $this->assertDatabaseHas('petugas_layanans', [
            'id'       => $petugas->id,
            'is_aktif' => 0,
            'catatan'  => 'Sedang cuti panjang',
        ]);

        $this->actingAs($admin)->delete(route('simpati.petugas.destroy', $petugas->id))->assertRedirect();

        $this->assertDatabaseMissing('petugas_layanans', ['id' => $petugas->id]);
    }

    public function test_cari_pegawai_mengembalikan_json()
    {
        $admin = $this->makeAdmin();
        $this->makeUser(['nama_lengkap' => 'Pencarian Khusus SIMPATI']);

        $response = $this->actingAs($admin)->getJson(route('simpati.petugas.cari', ['q' => 'Pencarian Khusus']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    // ---------------------------------------------------------------------
    // Kelola Jadwal
    // ---------------------------------------------------------------------

    public function test_jadwal_index_dapat_diakses()
    {
        $response = $this->actingAs($this->makeAdmin())->get(route('simpati.jadwal.index', ['grup' => 'pst']));

        $response->assertStatus(200);
    }

    public function test_jadwal_store_dan_update_status()
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('simpati.jadwal.store'), [
            'grup'   => 'pst',
            'tanggal'=> now()->toDateString(),
            'nama'   => 'Petugas Jadwal Uji',
            'status' => 'Terjadwal',
        ])->assertRedirect();

        $jadwal = JadwalLayanan::where('nama', 'Petugas Jadwal Uji')->first();
        $this->assertNotNull($jadwal);

        $this->actingAs($admin)->post(route('simpati.jadwal.status', $jadwal->id), [
            'status' => 'Hadir',
        ])->assertRedirect();

        $this->assertDatabaseHas('jadwal_layanans', [
            'id'     => $jadwal->id,
            'status' => 'Hadir',
        ]);
    }

    public function test_jadwal_template_mengembalikan_file_xlsx()
    {
        $response = $this->actingAs($this->makeAdmin())->get(route('simpati.jadwal.template', ['grup' => 'pst']));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', strtolower((string) $response->headers->get('content-type')));
    }

    public function test_admin_ppid_tidak_boleh_mengubah_jadwal_pst_403()
    {
        $user = $this->makeUser();
        $this->assignRole($user, 'admin_ppid');

        $jadwal = JadwalLayanan::create([
            'grup'    => 'pst',
            'tanggal' => now()->toDateString(),
            'nama'    => 'PST Dilindungi',
            'status'  => 'Terjadwal',
            'sumber'  => 'manual',
        ]);

        $this->actingAs($user)->post(route('simpati.jadwal.status', $jadwal->id), [
            'status' => 'Hadir',
        ])->assertStatus(403);

        $this->assertDatabaseHas('jadwal_layanans', ['id' => $jadwal->id, 'status' => 'Terjadwal']);
    }

    // ---------------------------------------------------------------------
    // Kelola Admin
    // ---------------------------------------------------------------------

    public function test_kelola_admin_hanya_untuk_super_admin_atau_administrator()
    {
        $biasa = $this->makeUser();
        $this->assignRole($biasa, 'admin_pst');

        $this->actingAs($biasa)->get(route('simpati.admins.index'))->assertStatus(403);

        $super = $this->makeUser();
        $this->assignRole($super, 'super_admin');
        $this->actingAs($super)->get(route('simpati.admins.index'))->assertStatus(200);

        $this->actingAs($this->makeAdmin())->get(route('simpati.admins.index'))->assertStatus(200);
    }

    public function test_tetapkan_dan_cabut_role_admin()
    {
        $super  = $this->makeUser();
        $this->assignRole($super, 'super_admin');
        $target = $this->makeUser();

        $this->actingAs($super)->post(route('simpati.admins.store'), [
            'user_id' => $target->id,
            'role'    => 'admin_satpam_cs',
        ])->assertRedirect();

        $this->assertDatabaseHas('simpati_admin_roles', [
            'user_id' => $target->id,
            'role'    => 'admin_satpam_cs',
        ]);

        $rowId = DB::table('simpati_admin_roles')
            ->where('user_id', $target->id)
            ->where('role', 'admin_satpam_cs')
            ->value('id');

        $this->actingAs($super)->delete(route('simpati.admins.destroy', $rowId))->assertRedirect();

        $this->assertDatabaseMissing('simpati_admin_roles', ['id' => $rowId]);
    }

    public function test_role_simpati_memberi_akses_halaman_simpati()
    {
        $user = $this->makeUser();
        $this->assignRole($user, 'admin_ruangan');

        $this->assertTrue(SimpatiRoles::hasAnyRole($user->fresh()));
        $this->actingAs($user)->get(route('simpati.dashboard'))->assertStatus(200);
    }

    // ---------------------------------------------------------------------
    // Regresi: fitur lama SIMPATI & API STARLA tetap berjalan
    // ---------------------------------------------------------------------

    public function test_halaman_integrasi_simpati_lama_tetap_dapat_diakses()
    {
        $this->actingAs($this->makeAdmin())->get(route('simpati.index'))->assertStatus(200);
    }

    public function test_api_starla_pst_jadwal_tetap_mengembalikan_json()
    {
        $response = $this->getJson(route('api.starla.pst.index'));

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }
}
