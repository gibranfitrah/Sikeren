<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\bmn;
use App\ruangan;
use App\pemeliharaan;

class BMNController extends Controller
{
    //
    
    public function index(request $request) {
        
        $rooms = ruangan::all();
        $ruang = $request->input('ruang');

        if(empty($ruang)){
             $bmns = bmn::where('nama_ruangan', 'Kepala BPS - Lantai 3')->get();   
        }
        else{
            $bmns = bmn::where('nama_ruangan', $ruang)->get();
        }
      
          
        return view( 'bmn', compact('bmns','rooms','ruang'));
        }

      
        public function index_pemeliharaan(request $request) {
        
          $bmns = pemeliharaan::all();
   
          return view( 'pemeliharaan_bmn', compact('bmns'));
          }

        public function update6($id)
    {
    	$category = bmn::find($id);

	    return response()->json([
	      'data' => $category
	    ]);
    }

    public function edit6(Request $request, $id)
    {

   
      bmn::updateOrCreate(
       [
        'id' => $id
       ],
       [
        'nama_barang' => $request->nama_barang,

       ]
      );

      return response()->json([ 'success' => true ]);

      
    }

    public function destroy5(user $post)
    {

        
        /// melakukan hapus data berdasarkan parameter yang dikirimkan
       $post->delete();
        
        return redirect()->back()
                        ->with('success','User Berhasil Dihapus');
    }


    public function store_perbaikan(Request $request)
    {
        /// membuat validasi untuk title dan content wajib diisi
   
         
        /// insert setiap request dari form ke dalam database via model
        /// jika menggunakan metode ini, maka nama field dan nama form harus sama

    
        $input = $request->all();

        
        pemeliharaan::create($input);
      
         
        /// redirect jika sukses menyimpan data
        return redirect()->back()->with(['success' => 'Berhasil Membuat Penugasan'])->withInput($request->all);
    }

}
