<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SuratMasuk;

class SuratMasukController extends Controller
{
    public function index()
    {
        $suratMasuks = SuratMasuk::orderBy('created_at', 'desc')->get();
        return view('surat_masuk.index', compact('suratMasuks'));
    }

    public function create()
    {
        return view('surat_masuk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat' => 'required',
            'asal_surat' => 'required',
            'perihal' => 'required',
        ]);

        $input = $request->all();
        SuratMasuk::create($input);

        return redirect()->route('surat-masuk.index')->with('success', 'Surat Masuk berhasil ditambahkan');
    }
}
