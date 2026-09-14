<?php

namespace App\Http\Controllers;

use App\Task;
use App\penugasan;
use Illuminate\Http\Request;

class DetailKegiatanController extends Controller
{
    public function show($id)
    {
        return redirect('/daftarkegiatan/' . $id);
    }
}