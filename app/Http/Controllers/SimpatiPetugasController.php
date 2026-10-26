<?php

namespace App\Http\Controllers;

use App\PetugasLayanan;
use App\Support\SimpatiRoles;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimpatiPetugasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $this->authorizeSimpati();

        $tab    = $request->query('tab', 'semua');
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $tim    = $request->query('tim', '');

        $tabs = array_merge(['semua'], array_keys(SimpatiRoles::GRUPS));
        if (!in_array($tab, $tabs, true)) {
            $tab = 'semua';
        }

        $grups       = SimpatiRoles::GRUPS;
        $manageable  = SimpatiRoles::manageableGrups($user);

        // Auto-seed Petugas PST & Layanan jika tabel masih kosong (memastikan siapa pun yang clone/pull repo langsung terisi data)
        if (Schema::hasTable('petugas_layanans') && PetugasLayanan::where('grup', 'pst')->count() === 0) {
            $seederFile = database_path('seeds/SimpatiPetugasLayananSeeder.php');
            if (file_exists($seederFile)) {
                require_once $seederFile;
                $seeder = new \SimpatiPetugasLayananSeeder();
                $seeder->run();
            }
        }

        $tabCounts = $this->tabCounts();
        $timList   = Schema::hasTable('master_groups')
            ? DB::table('master_groups')->orderBy('grup')->pluck('grup')->all()
            : [];

        if ($tab === 'semua') {
            $totalStaff   = User::bpsStaff()->count();
            $assignedIds  = Schema::hasTable('petugas_layanans')
                ? PetugasLayanan::whereNotNull('user_id')->distinct()->pluck('user_id')->all()
                : [];
            $jumlahBelum  = User::bpsStaff()->whereNotIn('id', $assignedIds)->count();

            $query = User::bpsStaff()
                ->leftJoin(DB::raw('(SELECT uj1.* FROM users_jabatan uj1 INNER JOIN (SELECT id_users, MAX(id) as max_id FROM users_jabatan GROUP BY id_users) uj2 ON uj1.id = uj2.max_id) as latest_jabatan'), 'users.id', '=', 'latest_jabatan.id_users')
                ->select(
                    'users.id', 'users.nama_lengkap', 'users.niplama', 'users.nipbaru',
                    'users.email', 'users.username', 'latest_jabatan.nm_jabatan'
                );

            if ($q !== '') {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('users.nama_lengkap', 'like', $like)
                       ->orWhere('users.niplama', 'like', $like)
                       ->orWhere('users.nipbaru', 'like', $like)
                       ->orWhere('users.email', 'like', $like)
                       ->orWhere('latest_jabatan.nm_jabatan', 'like', $like);
                });
            }

            if ($tim !== '') {
                $query->whereIn('users.niplama', function ($sub) use ($tim) {
                    $sub->select('niplama')->from('groups')->where('grup', $tim);
                });
            }

            if ($status === 'belum') {
                $query->whereNotIn('users.id', $assignedIds);
            } elseif ($status === 'sudah') {
                $query->whereIn('users.id', $assignedIds);
            }

            $pegawais = $query->orderBy('users.nama_lengkap', 'asc')->paginate(15)->withQueryString();

            $petugasPerUser = Schema::hasTable('petugas_layanans')
                ? PetugasLayanan::whereIn('user_id', $pegawais->pluck('id'))->get()->groupBy('user_id')
                : collect();

            $rows = $pegawais->getCollection()->map(function ($p) use ($petugasPerUser) {
                return [
                    'user'    => $p,
                    'jabatan' => $p->nm_jabatan ?: 'Pegawai BPS',
                    'tims'    => DB::table('groups')->where('niplama', $p->niplama)->pluck('grup')->all(),
                    'grups'   => $petugasPerUser->get($p->id, collect()),
                    'sudah'   => $petugasPerUser->has($p->id),
                ];
            });

            $pagination = $pegawais;
            $petugasList = collect();
        } else {
            $query = PetugasLayanan::grup($tab)->with('user');

            if ($q !== '') {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('nama', 'like', $like)
                       ->orWhere('niplama', 'like', $like)
                       ->orWhere('nipbaru', 'like', $like)
                       ->orWhere('jabatan', 'like', $like);
                });
            }

            $petugasList = $query->orderBy('is_aktif', 'desc')->orderBy('nama')->paginate(15)->withQueryString();
            $pagination  = $petugasList;
            $rows        = collect();
        }

        return view('admin.simpati.petugas', [
            'tab'           => $tab,
            'tabs'          => $tabs,
            'q'             => $q,
            'status'        => $status,
            'tim'           => $tim,
            'grups'         => $grups,
            'manageable'    => $manageable,
            'tabCounts'     => $tabCounts,
            'timList'       => $timList,
            'rows'          => $rows,
            'petugasList'   => $petugasList,
            'pagination'    => $pagination,
            'totalStaff'    => $tab === 'semua' ? User::bpsStaff()->count() : 0,
            'jumlahBelum'   => $tab === 'semua' ? $jumlahBelum : 0,
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->authorizeSimpati();

        $grups = array_keys(SimpatiRoles::GRUPS);
        $request->validate([
            'grup'    => 'required|in:' . implode(',', $grups),
            'user_id' => 'required_without:nama|nullable|integer|exists:users,id',
            'nama'    => 'required_without:user_id|nullable|string|max:200',
            'niplama' => 'nullable|string|max:15',
            'catatan' => 'nullable|string|max:500',
        ]);

        $grup = $request->input('grup');
        $this->authorizeGrup($user, $grup);

        $model = new PetugasLayanan();
        $model->grup = $grup;

        if ($request->filled('user_id')) {
            $target = User::findOrFail($request->input('user_id'));

            if (PetugasLayanan::where('user_id', $target->id)->where('grup', $grup)->exists()) {
                return $this->backWithError($request, $target->nama_lengkap . ' sudah terdaftar sebagai ' . SimpatiRoles::grupLabel($grup) . '.');
            }

            $model->user_id  = $target->id;
            $model->nama     = $target->nama_lengkap;
            $model->niplama  = $target->niplama;
            $model->nipbaru  = $target->nipbaru;
            $model->jabatan  = DB::table('users_jabatan')->where('id_users', $target->id)->orderByDesc('id')->value('nm_jabatan');
        } else {
            $namaManual = trim($request->input('nama'));
            if (PetugasLayanan::where('grup', $grup)->where('nama', $namaManual)->exists()) {
                return $this->backWithError($request, $namaManual . ' sudah terdaftar sebagai ' . SimpatiRoles::grupLabel($grup) . '.');
            }

            $model->nama    = $namaManual;
            $model->niplama = $request->input('niplama');
        }

        $model->is_aktif = true;
        $model->catatan  = $request->input('catatan');
        $model->save();

        return $this->backWithSuccess($request, 'Petugas berhasil ditambahkan ke ' . SimpatiRoles::grupLabel($grup) . '.');
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorizeSimpati();

        $model = PetugasLayanan::findOrFail($id);
        $this->authorizeGrup($user, $model->grup);

        $request->validate([
            'grup'     => 'sometimes|in:' . implode(',', array_keys(SimpatiRoles::GRUPS)),
            'is_aktif' => 'nullable|in:0,1',
            'catatan'  => 'nullable|string|max:500',
        ]);

        if ($request->filled('grup') && $request->input('grup') !== $model->grup) {
            $this->authorizeGrup($user, $request->input('grup'));
            if (PetugasLayanan::where('grup', $request->input('grup'))->where('user_id', $model->user_id)->exists()) {
                return $this->backWithError($request, $model->nama . ' sudah terdaftar di grup tersebut.');
            }
            $model->grup = $request->input('grup');
        }

        if ($request->has('is_aktif')) {
            $model->is_aktif = $request->input('is_aktif') == '1';
        }

        if ($request->filled('catatan') || $request->has('catatan')) {
            $model->catatan = $request->input('catatan');
        }

        $model->save();

        return $this->backWithSuccess($request, 'Data petugas ' . $model->nama . ' berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->authorizeSimpati();

        $model = PetugasLayanan::findOrFail($id);
        $this->authorizeGrup($user, $model->grup);

        $nama  = $model->nama;
        $grup  = $model->grup;
        $model->delete();

        return $this->backWithSuccess($request, $nama . ' dihapus dari ' . SimpatiRoles::grupLabel($grup) . '. (Akun pegawai tidak ikut terhapus)');
    }

    /** AJAX: pencarian pegawai untuk modal penugasan. */
    public function cariPegawai(Request $request)
    {
        $this->authorizeSimpati();

        $q     = trim((string) $request->query('q', ''));
        $limit = 15;

        $query = User::bpsStaff()
            ->leftJoin(DB::raw('(SELECT uj1.* FROM users_jabatan uj1 INNER JOIN (SELECT id_users, MAX(id) as max_id FROM users_jabatan GROUP BY id_users) uj2 ON uj1.id = uj2.max_id) as latest_jabatan'), 'users.id', '=', 'latest_jabatan.id_users')
            ->select('users.id', 'users.nama_lengkap', 'users.niplama', 'users.nipbaru', 'users.email', 'latest_jabatan.nm_jabatan');

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($qq) use ($like) {
                $qq->where('users.nama_lengkap', 'like', $like)
                   ->orWhere('users.niplama', 'like', $like)
                   ->orWhere('users.nipbaru', 'like', $like);
            });
        }

        $results = $query->orderBy('users.nama_lengkap')->limit($limit)->get();

        $grupsPerUser = Schema::hasTable('petugas_layanans')
            ? PetugasLayanan::whereIn('user_id', $results->pluck('id'))->get()->groupBy('user_id')
            : collect();

        return response()->json([
            'success' => true,
            'data'    => $results->map(fn ($p) => [
                'id'           => $p->id,
                'nama_lengkap' => $p->nama_lengkap,
                'niplama'      => $p->niplama,
                'nipbaru'      => $p->nipbaru,
                'nm_jabatan'   => $p->nm_jabatan ?: 'Pegawai BPS',
                'grups'        => $grupsPerUser->get($p->id, collect())->pluck('grup')->all(),
            ])->values(),
        ]);
    }

    protected function tabCounts(): array
    {
        $counts = ['semua' => User::bpsStaff()->count()];

        if (!Schema::hasTable('petugas_layanans')) {
            foreach (array_keys(SimpatiRoles::GRUPS) as $grup) {
                $counts[$grup] = 0;
            }

            return $counts;
        }

        foreach (array_keys(SimpatiRoles::GRUPS) as $grup) {
            $counts[$grup] = PetugasLayanan::grup($grup)->aktif()->count();
        }

        return $counts;
    }

    protected function authorizeSimpati()
    {
        $user = Auth::user();
        abort_unless($user && $user->canAccessSimpati(), 403, 'Akses terbatas! Halaman ini hanya untuk Administrator, Ketua Tim/PJ, dan role admin SIMPATI.');

        return $user;
    }

    protected function authorizeGrup(User $user, string $grup): void
    {
        abort_unless(SimpatiRoles::canManageGrup($user, $grup), 403, 'Anda tidak berhak mengelola grup ' . SimpatiRoles::grupLabel($grup) . '.');
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
