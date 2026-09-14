<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiseraBeritaController extends Controller
{
    public function index(Request $request)
{
    $page = (int) $request->query('page', 1);
    if ($page < 1) $page = 1;

    $limit = 10;
    $offset = ($page - 1) * $limit;

    $rows = DB::table('sisera_berita')
        ->orderBy('tanggal', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get()
        ->map(function ($item) {
            // Samakan tipe data dengan API lama
            return [
                'id'        => (string) $item->id,     // <- ubah ke string
                'judul'     => $item->judul,
                'keterangan'=> $item->keterangan,
                'tanggal'   => $item->tanggal,
                'photo'     => $item->photo,
            ];
        });

    return response()->json($rows, 200, [], 
        JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
    );
}

    
    public function show($id)
{
    // Ambil data berdasarkan ID
    $row = DB::table('sisera_berita')->where('id', $id)->first();

    if (!$row) {
        return response()->json([
            "error" => "Data not found"
        ], 404);
    }

    // Samakan tipe data id seperti API lama
    $row->id = (string) $row->id;

    // Tambahkan photo_url
    $row->photo_url = $row->photo 
        ? "https://webapps.bps.go.id/sultra/" . $row->photo
        : null;

    return response()->json($row, 200, [], JSON_UNESCAPED_UNICODE);
}

}
