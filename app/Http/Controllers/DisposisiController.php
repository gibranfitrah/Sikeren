<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Disposisi;
use App\SuratMasuk;
use App\User;
use App\master_organisasi;
use App\Notification;
use Illuminate\Support\Facades\DB;
class DisposisiController extends Controller
{
    public function create($surat_masuk_id)
    {
        $suratMasuk = SuratMasuk::findOrFail($surat_masuk_id);
        $tim = master_organisasi::all();
        
        return view('disposisi.create', compact('suratMasuk', 'tim'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'surat_masuk_id' => 'required|exists:surat_masuks,id',
        ]);

        $input = $request->all();
        $input['opsi_disposisi'] = isset($input['opsi_disposisi']) ? json_encode($input['opsi_disposisi']) : null;
        $input['diteruskan_kepada_tim'] = isset($input['diteruskan_kepada_tim']) ? json_encode($input['diteruskan_kepada_tim']) : null;
        $input['diketahui'] = isset($input['diketahui']) ? json_encode($input['diketahui']) : null;
        
        // Populate diteruskan_kepada with something so DB doesn't fail the "NOT NULL" constraint (if it exists)
        $input['diteruskan_kepada'] = $input['diteruskan_kepada_lainnya'] ?? 'Tim';

        $disposisi = Disposisi::create($input);

        $suratMasuk = SuratMasuk::find($request->surat_masuk_id);

        // Notification Logic
        $notifiedUsers = []; // To keep track and prevent duplicate notifications

        // Process Diteruskan Kepada
        if ($request->has('diteruskan_kepada_tim')) {
            $timIds = $request->diteruskan_kepada_tim;
            foreach ($timIds as $orgId) {
                $usersInOrg = DB::table('users_jabatan')->where('id_organisasi', $orgId)->pluck('id_users');
                foreach ($usersInOrg as $userId) {
                    if (!in_array($userId, $notifiedUsers)) {
                        Notification::create([
                            'niplama' => $userId,
                            'judul'   => 'Disposisi Diteruskan',
                            'pesan'   => 'Ada surat disposisi yang diteruskan ke divisi Anda. (' . ($suratMasuk->perihal ?? 'Cek Detail') . ')',
                            'link'    => '/disposisi/' . $disposisi->id . '/show',
                            'tipe'    => 'disposisi',
                            'is_read' => 0
                        ]);
                        $notifiedUsers[] = $userId;
                    }
                }
            }
        }

        // Process Diketahui
        if ($request->has('diketahui')) {
            $diketahuiIds = $request->diketahui;
            foreach ($diketahuiIds as $orgId) {
                // Check if it's a valid org ID (not "Lainnya1", "Lainnya2", etc)
                if (is_numeric($orgId)) {
                    $usersInOrg = DB::table('users_jabatan')->where('id_organisasi', $orgId)->pluck('id_users');
                    foreach ($usersInOrg as $userId) {
                        if (!in_array($userId, $notifiedUsers)) {
                            Notification::create([
                                'niplama' => $userId,
                                'judul'   => 'Disposisi (Diketahui)',
                                'pesan'   => 'Ada surat disposisi untuk Anda ketahui. (' . ($suratMasuk->perihal ?? 'Cek Detail') . ')',
                                'link'    => '/disposisi/' . $disposisi->id . '/show',
                                'tipe'    => 'disposisi',
                                'is_read' => 0
                            ]);
                            $notifiedUsers[] = $userId;
                        }
                    }
                }
            }
        }

        return redirect()->route('surat-masuk.index')->with('success', 'Disposisi berhasil dibuat. Tim terkait akan menindaklanjutinya.');
    }

    public function show($id)
    {
        $disposisi = Disposisi::with('suratMasuk')->findOrFail($id);
        $tim = master_organisasi::all();
        return view('disposisi.show', compact('disposisi', 'tim'));
    }
}
