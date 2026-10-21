<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Task;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Mobile login endpoint
     * Accepts: username/email/nama_lengkap/niplama/nipbaru and password
     */
    public function login(Request $request)
    {
        $loginInput = trim($request->input('username') ?: $request->input('email') ?: $request->input('nip') ?: '');
        $password   = trim($request->input('password') ?: '');

        if (empty($loginInput) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email dan Password tidak boleh kosong.'
            ], 422);
        }

        // 1. Search user in users table
        $user = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->orWhere('nama_lengkap', $loginInput)
            ->orWhere('niplama', $loginInput)
            ->orWhere('nipbaru', $loginInput)
            ->first();

        // 2. Fuzzy search if not found
        if (!$user) {
            $cleanInput = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $loginInput)[0]));
            if (!empty($cleanInput)) {
                $allUsers = User::all();
                foreach ($allUsers as $u) {
                    $uSlug1 = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $u->username ?? ''));
                    $uSlug2 = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $u->nama_lengkap ?? ''));
                    if (($uSlug1 && str_contains($uSlug1, $cleanInput)) || ($uSlug2 && str_contains($uSlug2, $cleanInput)) ||
                        ($cleanInput && (str_contains($cleanInput, $uSlug1) || str_contains($cleanInput, $uSlug2)))) {
                        $user = $u;
                        break;
                    }
                }
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan dengan akun tersebut.'
            ], 404);
        }

        // 3. Verify password (matching LoginController logic)
        $userSlug = method_exists($user, 'getDefaultPasswordSlug') 
            ? $user->getDefaultPasswordSlug() 
            : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(',', $user->nama_lengkap ?? '')[0]));
        $cleanInputPwd = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $password));
        $expectedWith123 = $userSlug . '123';
        $expectedPlain   = $userSlug;

        $isValid = false;

        // A. Pattern: Name lowercase + 123 (e.g., yunussamuel123 or hadisusanto123)
        if ($cleanInputPwd === $expectedWith123 || $cleanInputPwd === $expectedPlain) {
            $isValid = true;
        }
        // B. Hash check from DB
        elseif (Hash::check($password, $user->password)) {
            $isValid = true;
        }
        // C. Username lowercase + 123
        elseif (!empty($user->username) && ($cleanInputPwd === strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->username)) . '123' || $cleanInputPwd === strtolower($user->username))) {
            $isValid = true;
        }
        // D. Development / standard passwords
        elseif (in_array($password, ['password', 'bps123', 'admin', 'admin123', '12345678', '123456'])) {
            $isValid = true;
        }
        // E. Legacy MD5
        elseif (isset($user->password) && (md5($password) === $user->password || $password === $user->password)) {
            $isValid = true;
        }

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi salah. Silakan coba lagi.'
            ], 401);
        }

        // Ensure token_id exists or generate new token
        $token = $user->token_id ?: Str::random(32);
        if (!$user->token_id) {
            $user->token_id = $token;
            $user->save();
        }

        // Fetch user jabatan and organisasi details
        $jabatan = DB::table('users_jabatan')
            ->leftJoin('master_organisasi', 'users_jabatan.id_organisasi', '=', 'master_organisasi.id')
            ->where('users_jabatan.id_users', $user->id)
            ->orderBy('users_jabatan.id', 'desc')
            ->select(
                'users_jabatan.nm_jabatan',
                'users_jabatan.id_organisasi',
                'users_jabatan.id_satker',
                'master_organisasi.nm_organisasi'
            )
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'token'   => $token,
            'user'    => [
                'id'           => $user->id,
                'niplama'      => $user->niplama,
                'nipbaru'      => $user->nipbaru,
                'nama_lengkap' => $user->nama_lengkap,
                'username'     => $user->username,
                'email'        => $user->email,
                'foto'         => $user->foto ? url('storage/foto/' . $user->foto) : null,
                'jabatan'      => $jabatan->nm_jabatan ?? 'Pegawai BPS',
                'organisasi'   => $jabatan->nm_organisasi ?? 'BPS Sulawesi Tenggara',
                'id_satker'    => $jabatan->id_satker ?? '7400'
            ]
        ]);
    }

    /**
     * Get authenticated user profile via token
     */
    public function me(Request $request)
    {
        $token = $request->bearerToken() ?: $request->header('X-Token') ?: $request->input('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak disertakan.'
            ], 401);
        }

        $user = User::where('token_id', $token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.'
            ], 401);
        }

        $jabatan = DB::table('users_jabatan')
            ->leftJoin('master_organisasi', 'users_jabatan.id_organisasi', '=', 'master_organisasi.id')
            ->where('users_jabatan.id_users', $user->id)
            ->orderBy('users_jabatan.id', 'desc')
            ->select(
                'users_jabatan.nm_jabatan',
                'users_jabatan.id_organisasi',
                'users_jabatan.id_satker',
                'master_organisasi.nm_organisasi'
            )
            ->first();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'           => $user->id,
                'niplama'      => $user->niplama,
                'nipbaru'      => $user->nipbaru,
                'nama_lengkap' => $user->nama_lengkap,
                'username'     => $user->username,
                'email'        => $user->email,
                'foto'         => $user->foto ? url('storage/foto/' . $user->foto) : null,
                'jabatan'      => $jabatan->nm_jabatan ?? 'Pegawai BPS',
                'organisasi'   => $jabatan->nm_organisasi ?? 'BPS Sulawesi Tenggara',
                'id_satker'    => $jabatan->id_satker ?? '7400'
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout'
        ]);
    }
}
