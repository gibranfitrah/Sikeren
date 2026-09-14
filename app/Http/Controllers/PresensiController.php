<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\kode_qr;
use App\presensi;
use App\User;
use Auth;  
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use DB;
use App\Imports\Presensi_Import;
use Excel;
use GuzzleHttp\Client;
use Hash;
class PresensiController extends Controller
{
    
    
     public function showForm()
    {
        $user = auth()->user(); 
        return view('absen_kantor', compact('user'));
    }

    public function submitPresensi(Request $request)
{
     $request->validate([
        'status' => 'required',
        'qr_data' => 'required',
        'ket_dinas' => 'required_if:status,5',  
    ], [
        'ket_dinas.required_if' => 'Alasan Dinas is required when Keluar - Dinas is selected.'
    ]);

    $client = new Client();

    try {
        
        $presensiResponse = $client->post('https://webapps.bps.go.id/sultra/sikeren-api/input-presensi.php', [
            'form_params' => [
                'niplama' => auth()->user()->niplama,
                'status' => $request->input('status'),
                'ket_dinas' => $request->input('ket_dinas'),
            ],
        ]);

        $presensiData = json_decode($presensiResponse->getBody()->getContents(), true);

        if (!$presensiData['success']) {
            return back()->withErrors(['error' => 'Gagal Melakukan Presensi, Cek Pilihan Status Kembali']);
        }

        $qrData = $request->input('qr_data');
        $kodeSatker = substr($qrData, -5); 

        $qrResponse = $client->post('https://webapps.bps.go.id/sultra/sikeren-api/input-qr.php', [
            'form_params' => [
                'kode_satker' => $kodeSatker,
                'kode_qr' => $qrData,
            ],
        ]);

        $qrResponseData = json_decode($qrResponse->getBody()->getContents(), true);

        if ($qrResponseData['success']) {

            return redirect('qr')
                ->with('message', 'Berhasil Melakukan Presensi dengan QR: ' . $qrResponseData['qr_code']);
        } else {
            return back()->withErrors(['error' => $qrResponseData['message']]);
        }
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Gagal Melakukan Presensi']);
    }
}

    
    
    

public function checkForNewQRCode()
{
    
    $userId = Auth::id();
    
$lastIdOrganisasi = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_organisasi');  
    
    $lastIdSatker = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_satker');
    if($lastIdSatker == '7400'){
    $latestQrCode = kode_qr::where('kode_satker', $lastIdOrganisasi)->latest()->first();
    }
    else{
        $latestQrCode = kode_qr::where('kode_satker', $lastIdSatker.'0')->latest()->first();
        
    }
    return response()->json(['id' => $latestQrCode->id]);
}

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        Excel::import(new Presensi_Import, $request->file('file'));

        return redirect()->route('report')->with('success', 'Data Imported Successfully');
    }
    
    public function index_report(Request $request) {
        $selectedDate = Carbon::parse($request->input('date', Carbon::now()))
    ->setTimezone('Asia/Singapore')->toDateString();
    
    $userId = Auth::id();
    
    
$lastIdOrganisasi = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_organisasi');  
$lastIdJabatan = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_jabatan');
    
$lastIdSatker = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_satker');
    
    $report = presensi::join('users', 'presensis.niplama', '=', 'users.niplama')
    ->join(DB::raw('(SELECT id_users, id_satker FROM users_jabatan uj1 
                    WHERE uj1.id = (SELECT MAX(id) FROM users_jabatan uj2 WHERE uj1.id_users = uj2.id_users)
                    ) as latest_jabatan'), 'users.id', '=', 'latest_jabatan.id_users') 
    ->select('presensis.*', 'users.nama_lengkap')
    ->whereDate('presensis.created_at', $selectedDate)
    ->where('latest_jabatan.id_satker', $lastIdSatker) 
    ->get();
    
    
  
    return view('report', compact('report', 'selectedDate', 'lastIdJabatan'));
}



public function index_rekap(Request $request)
{
   // $selectedMonth = $request->input('month', Carbon::now()->format('Y-m')); // Example: YYYY-MM
     $selectedMonth = Carbon::parse($request->input('date', Carbon::now()->format('Y-m')))
    ->setTimezone('Asia/Singapore')->toDateString();
    $startOfMonth = Carbon::parse($selectedMonth . '-01')->startOfDay();
    $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
    
    

    // Get all users with their latest id_satker
    $latestSatkerSubquery = DB::table('users_jabatan')
        ->select('id_users', DB::raw('MAX(id) as latest_id'))
        ->groupBy('id_users');

    $usersWithLatestSatker = DB::table('users_jabatan')
        ->joinSub($latestSatkerSubquery, 'latest', function ($join) {
            $join->on('users_jabatan.id', '=', 'latest.latest_id');
        })
        ->join('users', 'users_jabatan.id_users', '=', 'users.id')
        ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
        ->select('users.id', 'users.niplama', 'users.nama_lengkap', 'users_jabatan.id_satker')
        ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
        ->get();

    // Extract id_satker of the authenticated user
    $userId = Auth::id();
    $lastIdSatker = $usersWithLatestSatker->firstWhere('id', $userId)->id_satker;

    // Get all users associated with the same `id_satker`
    $users = $usersWithLatestSatker
        ->where('id_satker', $lastIdSatker)
        ->pluck('nama_lengkap', 'niplama');

    // Get presensi data for the selected month
    $presensis = DB::table('presensis')
        ->join('users', 'users.niplama', '=', 'presensis.niplama')
        ->joinSub($latestSatkerSubquery, 'latest', function ($join) {
            $join->on('users.id', '=', 'latest.id_users');
        })
        ->join('users_jabatan', 'users_jabatan.id', '=', 'latest.latest_id')
        ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
        ->select('users.niplama', 'presensis.status', 'presensis.created_at')
        ->whereBetween('presensis.created_at', [$startOfMonth, $endOfMonth])
        ->where('users_jabatan.id_satker', $lastIdSatker)
        ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
        ->get();

    // Create an array to organize data by `niplama` and date
    $dates = [];
    $data = [];
    
    $statusLabels = [
    1 => 'Masuk',
    2 => 'Keluar (P)',
    3 => 'Kembali',
    4 => 'Pulang',
    5 => 'Keluar (D)',
    6 => 'CUTI',
    7 => 'TL',
];

    foreach ($presensis as $presensi) {
        $date = Carbon::parse($presensi->created_at)->format('Y-m-d');
        $time = Carbon::parse($presensi->created_at)->format('H:i'); // Extract time in H:i format
        $dates[$date] = $date;

        // Collect unique times for each user and date
        if (!isset($data[$presensi->niplama][$date])) {
            $data[$presensi->niplama][$date] = [];
        }
        $statusLabel = $statusLabels[$presensi->status] ?? 'Unknown';
    $data[$presensi->niplama][$date][] = $statusLabel . ' (' . $time . ')';
        
    }

    // Remove duplicate times
    foreach ($data as $niplama => $userDates) {
        foreach ($userDates as $date => $times) {
            $data[$niplama][$date] = array_unique($times);
        }
    }

    // Ensure all dates are represented for all users
    $dates = collect($dates)->sort()->values();
    foreach ($users as $niplama => $name) {
        foreach ($dates as $date) {
            if (!isset($data[$niplama][$date])) {
                $data[$niplama][$date] = [];
            }
        }
    }
    
    $selectedDate = Carbon::parse($request->input('date', Carbon::now()))
    ->setTimezone('Asia/Singapore')->toDateString();
    
    $lastIdOrganisasi = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_organisasi');  
$lastIdJabatan = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_jabatan');
    
$lastIdSatker = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_satker');
    $latestStatuses = DB::table('presensis')
        ->select('niplama', DB::raw('MAX(created_at) as latest_created_at'))
        ->whereDate('created_at', $selectedMonth)
        ->groupBy('niplama');
    
    $statuses = DB::table('users')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_id 
                     FROM users_jabatan 
                     GROUP BY id_users) as latest_users_jabatan"), 
           'users.id', '=', 'latest_users_jabatan.id_users')
    ->join('users_jabatan', 'latest_users_jabatan.latest_id', '=', 'users_jabatan.id')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
    ->leftJoinSub($latestStatuses, 'latest', function ($join) {
        $join->on('users.niplama', '=', 'latest.niplama');
    })
    ->leftJoin('presensis', function ($join) {
        $join->on('users.niplama', '=', 'presensis.niplama')
             ->on('presensis.created_at', '=', 'latest.latest_created_at');
    })
    ->select('users.nama_lengkap as user_name', 'users.niplama', 'presensis.status', 'presensis.created_at', 'users_status.id_status', 'users_jabatan.id_organisasi as organisasi','users_jabatan.id_users as id')
    ->where('users_jabatan.id_satker', $lastIdSatker)
    
    ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
    ->orderBy('created_at', 'desc')
    ->get();
    
    $formattedMonth = substr($selectedMonth, 0, 7);
    $month = substr($selectedMonth, 5, 2);
    
    
    
        
    // Get selected date or default to today
    $selectedDate = $request->input('date', now()->format('Y-m-d'));

    // Extract month and year
    $month = date('m', strtotime($selectedDate));
    $year = date('Y', strtotime($selectedDate));

    // Format for your subquery use
    $formattedMonth = date('Y-m', strtotime($selectedDate));

    // Example: assuming $latestSatkerSubquery and $statuses are defined
    $totalPerMonth = DB::table('users')
        ->leftJoin('presensis', function ($join) use ($month, $year) {
            $join->on('users.niplama', '=', 'presensis.niplama')
                ->whereMonth('presensis.created_at', '=', $month)
                ->whereYear('presensis.created_at', '=', $year)
                ->orWhereNull('presensis.created_at');
        })
        ->joinSub($latestSatkerSubquery, 'latest', function ($join) {
            $join->on('users.id', '=', 'latest.id_users');
        })
        ->join('users_jabatan', 'users_jabatan.id', '=', 'latest.latest_id')
        ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                         FROM users_status 
                         GROUP BY id_users) as latest_users_status"), 
               'users.id', '=', 'latest_users_status.id_users')
        ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
        ->select(
            'users.niplama', 
            'users.nama_lengkap',
            DB::raw("DATE_FORMAT(presensis.created_at, '%Y-%m') as month"),
            DB::raw("
                COUNT(DISTINCT CASE 
                    WHEN presensis.status = 1 AND DAYOFWEEK(presensis.created_at) BETWEEN 2 AND 6 
                    THEN DATE(presensis.created_at) 
                END) as total
            "),
            DB::raw("(
                SELECT COUNT(DISTINCT a.date)
                FROM (
                    SELECT DATE_FORMAT(p.created_at, '%Y-%m-%d') as date
                    FROM presensis p
                    WHERE DATE_FORMAT(p.created_at, '%Y-%m') = '$formattedMonth' 
                    AND WEEKDAY(p.created_at) < 5
                    AND NOT EXISTS (
                        SELECT 1 FROM holidays h WHERE h.holiday_date = DATE_FORMAT(p.created_at, '%Y-%m-%d')
                    )
                ) a
            ) as total_working_days"),
            DB::raw('COUNT(CASE WHEN presensis.status = 6 THEN 1 END) as total_status_6'),
            DB::raw('COUNT(CASE WHEN presensis.status = 7 THEN 1 END) as total_status_7')
        )
        ->whereIn('users.niplama', $statuses->pluck('niplama'))
        ->groupBy('users.niplama', 'month')
        ->orderBy('users.niplama')
        ->orderBy('month')
        ->get();



    return view('rekap', compact('users', 'dates', 'data', 'selectedMonth', 'totalPerMonth'));
}





public function updateJam(Request $request, $id)
{
    
    $request->validate([
        'jam' => 'required|date_format:H:i',
    ]);

    $status = presensi::find($id);

    if ($status) {
        $originalDate = Carbon::parse($status->created_at)->format('Y-m-d');
        
        $newTime = Carbon::parse($originalDate . ' ' . $request->jam);
        $status->created_at = $newTime;

        $status->save();

        return redirect()->back()->with('success', 'Jam updated successfully!');
    }

    return redirect()->back()->with('error', 'Record not found!');
}


public function index(Request $request)
{
    $kode_satker = 7400;
    $selectedDate = Carbon::parse($request->input('date', Carbon::now()))
    ->setTimezone('Asia/Singapore')->toDateString();
    $userId = Auth::id();
    
    $satker_id = $request->satker_id;
    
    $organisasi_id = $request->organisasi_id;
    
$lastIdOrganisasi = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_organisasi');  
$lastIdJabatan = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_jabatan');
    
$lastIdSatker = DB::table('users_jabatan')
    ->where('id_users', $userId)
    ->orderBy('id', 'desc') 
    ->value('id_satker');
   

    
    if($lastIdSatker == '7400'){
        $data = kode_qr::where('kode_satker', $lastIdOrganisasi)->latest()->first();
        $qrcode = $data ? QrCode::size(300)->generate($data->kode_qr) : null;
    }

    else{
        $data = kode_qr::where('kode_satker', $lastIdSatker.'0')->latest()->first();
        $qrcode = $data ? QrCode::size(300)->generate($data->kode_qr) : null;
    }

    $users = DB::table('users')
        ->join('users_jabatan', 'users.id', '=', 'users_jabatan.id_users')
        ->where('users_jabatan.id_satker', $kode_satker)
        ->select('users.id', 'users.nama_lengkap', 'users.niplama')
        ->get();

    $latestStatuses = DB::table('presensis')
        ->select('niplama', DB::raw('MAX(created_at) as latest_created_at'))
        ->whereDate('created_at', $selectedDate)
        ->groupBy('niplama');
        
    
    

  

    
    if($userId === 45){
        $satker_id = $satker_id ?? 7400;
        $statuses = DB::table('users')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_id 
                     FROM users_jabatan 
                     GROUP BY id_users) as latest_users_jabatan"), 
           'users.id', '=', 'latest_users_jabatan.id_users')
    ->join('users_jabatan', 'latest_users_jabatan.latest_id', '=', 'users_jabatan.id')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
    ->leftJoinSub($latestStatuses, 'latest', function ($join) {
        $join->on('users.niplama', '=', 'latest.niplama');
    })
    ->leftJoin('presensis', function ($join) {
        $join->on('users.niplama', '=', 'presensis.niplama')
             ->on('presensis.created_at', '=', 'latest.latest_created_at');
    })
    ->select('users.nama_lengkap as user_name', 'users.niplama', 'presensis.status', 'presensis.created_at', 'users_status.id_status', 'users_jabatan.id_organisasi as organisasi','users_jabatan.id_users as id')
    ->where('users_jabatan.id_satker', $satker_id)
    
    ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
    ->orderBy('created_at', 'desc')
    ->get();
    
   $presensis = DB::table('presensis')
    ->whereIn('niplama', $statuses->pluck('niplama'))
    ->whereDate('created_at', $selectedDate)
    ->get()
    ->groupBy('niplama');
    
    }
    
    elseif($userId === 66){ 
$statuses = DB::table('users')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_id 
                     FROM users_jabatan 
                     GROUP BY id_users) as latest_users_jabatan"), 
           'users.id', '=', 'latest_users_jabatan.id_users')
    ->join('users_jabatan', 'latest_users_jabatan.latest_id', '=', 'users_jabatan.id')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
    ->leftJoinSub($latestStatuses, 'latest', function ($join) {
        $join->on('users.niplama', '=', 'latest.niplama');
    })
    ->leftJoin('presensis', function ($join) {
        $join->on('users.niplama', '=', 'presensis.niplama')
             ->on('presensis.created_at', '=', 'latest.latest_created_at');
    })
    ->select('users.nama_lengkap as user_name', 'users.niplama', 'presensis.status', 'presensis.created_at', 'users_status.id_status', 'users_jabatan.id_organisasi as organisasi','users_jabatan.id_users as id')
    ->where('users_jabatan.id_satker', $lastIdSatker)
    ->where('users_jabatan.id_organisasi', $organisasi_id)
    ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
    ->orderBy('created_at', 'desc')
    ->get();
    
   $presensis = DB::table('presensis')
    ->whereIn('niplama', $statuses->pluck('niplama'))
    ->whereDate('created_at', $selectedDate)
    ->get()
    ->groupBy('niplama');
 }
 elseif($lastIdSatker == '7400'){   
$statuses = DB::table('users')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_id 
                     FROM users_jabatan 
                     GROUP BY id_users) as latest_users_jabatan"), 
           'users.id', '=', 'latest_users_jabatan.id_users')
    ->join('users_jabatan', 'latest_users_jabatan.latest_id', '=', 'users_jabatan.id')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
    ->leftJoinSub($latestStatuses, 'latest', function ($join) {
        $join->on('users.niplama', '=', 'latest.niplama');
    })
    ->leftJoin('presensis', function ($join) {
        $join->on('users.niplama', '=', 'presensis.niplama')
             ->on('presensis.created_at', '=', 'latest.latest_created_at');
    })
    ->select('users.nama_lengkap as user_name', 'users.niplama', 'presensis.status', 'presensis.created_at', 'users_status.id_status', 'users_jabatan.id_organisasi as organisasi','users_jabatan.id_users as id')
    ->where('users_jabatan.id_satker', $lastIdSatker)
    ->where('users_jabatan.id_organisasi', $lastIdOrganisasi)
    ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
    ->orderBy('created_at', 'desc')
    ->get();
    
   $presensis = DB::table('presensis')
    ->whereIn('niplama', $statuses->pluck('niplama'))
    ->whereDate('created_at', $selectedDate)
    ->get()
    ->groupBy('niplama');
 }
    
     
    
    else{
        $statuses = DB::table('users')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_id 
                     FROM users_jabatan 
                     GROUP BY id_users) as latest_users_jabatan"), 
           'users.id', '=', 'latest_users_jabatan.id_users')
    ->join('users_jabatan', 'latest_users_jabatan.latest_id', '=', 'users_jabatan.id')
    ->join(DB::raw("(SELECT id_users, MAX(id) as latest_status_id 
                     FROM users_status 
                     GROUP BY id_users) as latest_users_status"), 
           'users.id', '=', 'latest_users_status.id_users')
    ->join('users_status', 'latest_users_status.latest_status_id', '=', 'users_status.id')
    ->leftJoinSub($latestStatuses, 'latest', function ($join) {
        $join->on('users.niplama', '=', 'latest.niplama');
    })
    ->leftJoin('presensis', function ($join) {
        $join->on('users.niplama', '=', 'presensis.niplama')
             ->on('presensis.created_at', '=', 'latest.latest_created_at');
    })
    ->select('users.nama_lengkap as user_name', 'users.niplama', 'presensis.status', 'presensis.created_at', 'users_status.id_status', 'users_jabatan.id_organisasi as organisasi','users_jabatan.id_users as id')
    ->where('users_jabatan.id_satker', $lastIdSatker)
    
    ->whereIn('users_status.id_status', [1, 2, 3,4, 31, 32]) 
    ->orderBy('created_at', 'desc')
    ->get();
    
   $presensis = DB::table('presensis')
    ->whereIn('niplama', $statuses->pluck('niplama'))
    ->whereDate('created_at', $selectedDate)
    ->get()
    ->groupBy('niplama');
    
    }

    
$allPresensis = DB::table('presensis')
    ->whereIn('status', [1, 2, 3, 4])
    ->whereDate('created_at', $selectedDate)
    ->orderBy('niplama')
    ->orderBy('created_at')
    ->get();

$istirahatSums = [];
$masukKantorSums = [];
$keluarTimes = [];
$now = Carbon::now();

$masukTimes = [];
$pulangTimes = [];

foreach ($allPresensis as $presensi) {
    $niplama = $presensi->niplama;

    if ($presensi->status == 1) {
        if (!isset($masukTimes[$niplama])) {
            $masukTimes[$niplama] = Carbon::parse($presensi->created_at);
        }
    }

    if ($presensi->status == 2) {
        $keluarTimes[$niplama] = Carbon::parse($presensi->created_at);
    } elseif ($presensi->status == 3 && isset($keluarTimes[$niplama])) {
        $kembaliTime = Carbon::parse($presensi->created_at);
        $keluarTime = $keluarTimes[$niplama];

        $timeDifference = $kembaliTime->diffInSeconds($keluarTime);


        if (!isset($istirahatSums[$niplama])) {
            $istirahatSums[$niplama] = 0;
        }

        
        $istirahatSums[$niplama] += $timeDifference;

    
        unset($keluarTimes[$niplama]);
    } elseif ($presensi->status == 4) {
        $pulangTimes[$niplama] = Carbon::parse($presensi->created_at);
    }
}

foreach ($keluarTimes as $niplama => $keluarTime) {
    
    $kembaliTime = $now->copy()->addHours(8);

    $timeDifference = $kembaliTime->diffInSeconds($keluarTime);

    if (!isset($istirahatSums[$niplama])) {
        $istirahatSums[$niplama] = 0;
    }

    $istirahatSums[$niplama] += $timeDifference;
}

foreach ($masukTimes as $niplama => $masukTime) {

    if (isset($pulangTimes[$niplama])) {
        $pulangTime = $pulangTimes[$niplama];
    } else {
        
        $pulangTime = $now->copy()->addHours(8);
    }

    
    $istirahatSumInSeconds = $istirahatSums[$niplama] ?? 0;

    $totalMasukKantorSeconds = $pulangTime->diffInSeconds($masukTime) - $istirahatSumInSeconds;

    $masukKantorSums[$niplama] = gmdate('H:i', $totalMasukKantorSeconds);
}

$estimatedJamPulang = []; 

foreach ($masukTimes as $niplama => $masukTime) {
    
    $estimatedPulangTime = $masukTime->copy()->addHours(7)->addMinutes(30);

    if (isset($istirahatSums[$niplama])) {
        $estimatedPulangTime->addSeconds($istirahatSums[$niplama]);
    }

    $estimatedJamPulang[$niplama] = $estimatedPulangTime->format('H:i');
}

foreach ($istirahatSums as $niplama => $seconds) {
    $istirahatSums[$niplama] = gmdate('H:i', $seconds);
}



    
    return view('qr', compact('qrcode', 'kode_satker', 'statuses', 'istirahatSums', 'masukKantorSums', 'selectedDate', 'data', 'lastIdJabatan', 'presensis', 'estimatedJamPulang', 'userId'));
}





    

public function submitScan(Request $request)
{
    $kodeSatker = $request->input('kode_satker');
    $currentDate = now()->format('Ymd');

    $latestKodeQr = DB::table('kode_qrs')
        ->where('kode_satker', $kodeSatker)
        ->whereDate('created_at', now()->toDateString())
        ->orderBy('id', 'desc')
        ->first();

    if ($latestKodeQr) {

        $lastHyphenPosition = strrpos($latestKodeQr->kode_qr, '-');

        $increment = intval(substr($latestKodeQr->kode_qr, $lastHyphenPosition + 1)) + 1;
    } else {
        $increment = 1;
    }

    $newKode = $kodeSatker . '-' . $currentDate . '-' . $increment;
    
    $hashedKodeQr = hash('sha256', $newKode);
    
    $newKodeQr = $hashedKodeQr . '-' . $currentDate . '-' . $increment;
    

    $exists = DB::table('kode_qrs')->where('kode_qr', $newKodeQr)->exists();

    if ($exists) {
        return redirect()->back()->with('error', 'QR Code Sudah Digunakan.');
    }

    $newItem = kode_qr::create([
        'kode_satker' => $kodeSatker,
        'kode_qr' => $newKodeQr,
    ]);

    return redirect()->route('qr', ['id' => $newItem->id]);
}

public function showPasswordForm()
    {
        return view('password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        // Check if the current password matches
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->route('password')->withErrors(['current_password' => 'Current password does not match']);
        }

        // Update password
        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        return redirect()->route('password')->with('status', 'Password updated successfully');

    }


}