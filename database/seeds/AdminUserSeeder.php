<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('username', 'admin')
            ->orWhere('email', 'admin@bps.go.id')
            ->first();

        if (!$admin) {
            $admin = new User();
            $admin->niplama = '740000000';
            $admin->nipbaru = '198001012000011001';
        }

        $admin->nama_lengkap = 'Administrator';
        $admin->username     = 'admin';
        $admin->email        = 'admin@bps.go.id';
        $admin->password     = Hash::make('admin');
        $admin->save();

        $hasJabatan = DB::table('users_jabatan')->where('id_users', $admin->id)->exists();
        if (!$hasJabatan) {
            DB::table('users_jabatan')->insert([
                'id_users'     => $admin->id,
                'nm_jabatan'   => 'Administrator Sistem / Super Admin',
                'id_satker'    => 7400,
                'id_organisasi'=> 7400,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } else {
            DB::table('users_jabatan')->where('id_users', $admin->id)->update([
                'nm_jabatan' => 'Administrator Sistem / Super Admin'
            ]);
        }
    }
}
