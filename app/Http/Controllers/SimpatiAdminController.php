<?php

namespace App\Http\Controllers;

use App\Support\SimpatiRoles;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SimpatiAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = $this->authorizeManageAdmins();

        $holders = [];
        if (Schema::hasTable('simpati_admin_roles')) {
            $rows = \DB::table('simpati_admin_roles')
                ->join('users', 'users.id', '=', 'simpati_admin_roles.user_id')
                ->select('simpati_admin_roles.id as role_row_id', 'simpati_admin_roles.role', 'simpati_admin_roles.user_id', 'users.nama_lengkap', 'users.niplama', 'users.email')
                ->orderBy('users.nama_lengkap')
                ->get();

            foreach ($rows as $row) {
                $holders[$row->role][] = $row;
            }
        }

        $assignedUserIds = [];
        foreach ($holders as $rows) {
            foreach ($rows as $row) {
                $assignedUserIds[$row->user_id] = $row->nama_lengkap;
            }
        }
        asort($assignedUserIds);

        return view('admin.simpati.admins', [
            'roles'          => SimpatiRoles::ALL,
            'roleLabels'     => SimpatiRoles::LABELS,
            'roleDesc'       => SimpatiRoles::DESCRIPTIONS,
            'roleManages'    => SimpatiRoles::MANAGES,
            'grups'          => SimpatiRoles::GRUPS,
            'holders'        => $holders,
            'assignedUsers'  => $assignedUserIds,
            'canManageGrup'  => SimpatiRoles::manageableGrups($user),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManageAdmins();

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role'    => 'required|in:' . implode(',', SimpatiRoles::ALL),
        ]);

        if (!Schema::hasTable('simpati_admin_roles')) {
            return $this->backWithError($request, 'Tabel role SIMPATI belum tersedia. Jalankan migration terlebih dahulu.');
        }

        $userTarget = User::findOrFail($request->input('user_id'));
        $role       = $request->input('role');

        $exists = \DB::table('simpati_admin_roles')
            ->where('user_id', $userTarget->id)
            ->where('role', $role)
            ->exists();

        if ($exists) {
            return $this->backWithError($request, $userTarget->nama_lengkap . ' sudah memegang role ' . SimpatiRoles::label($role) . '.');
        }

        \DB::table('simpati_admin_roles')->insert([
            'user_id'    => $userTarget->id,
            'role'       => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->backWithSuccess($request, $userTarget->nama_lengkap . ' ditetapkan sebagai ' . SimpatiRoles::label($role) . '.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->authorizeManageAdmins();

        $row = \DB::table('simpati_admin_roles')->where('id', $id)->first();
        abort_unless($row, 404);

        if ($row->role === 'super_admin') {
            $jumlahSuper = \DB::table('simpati_admin_roles')->where('role', 'super_admin')->count();
            $userHasSuper = \DB::table('simpati_admin_roles')
                ->where('user_id', $user->id)
                ->where('role', 'super_admin')
                ->exists();

            if ($jumlahSuper <= 1 && !$user->isAdmin() && !$userHasSuper) {
                return $this->backWithError($request, 'Tidak dapat mencabut role Super Admin terakhir.');
            }

            if ($jumlahSuper <= 1 && $user->id === $row->user_id && !$user->isAdmin()) {
                return $this->backWithError($request, 'Anda adalah Super Admin terakhir dan tidak dapat mencabut role sendiri. Minta Administrator sistem mencabutnya.');
            }
        }

        $userTarget = User::find($row->user_id);
        $nama  = $userTarget ? $userTarget->nama_lengkap : 'Pengguna';
        $label = SimpatiRoles::label($row->role);

        \DB::table('simpati_admin_roles')->where('id', $id)->delete();

        return $this->backWithSuccess($request, 'Role ' . $label . ' dicabut dari ' . $nama . '.');
    }

    protected function authorizeManageAdmins()
    {
        $user = Auth::user();
        abort_unless($user && $user->canAccessSimpati(), 403, 'Akses terbatas!');
        abort_unless(SimpatiRoles::canManageAdmins($user), 403, 'Pengelolaan role admin hanya untuk Super Admin / Administrator sistem.');

        return $user;
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
