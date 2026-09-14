<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PresensiApiController extends Controller
{
    public function today()
    {
        // Set timezone Asia/Singapore
        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');

        // Fetch rows where date(created_at) = today
        $rows = DB::table('presensis')
            ->whereDate('created_at', $today)
            ->get();

        // Return JSON
        return response()->json($rows, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
    public function fullToday()
    {
        // Asia/Singapore timezone (sama seperti PHP Anda)
        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');

        // Query sama seperti PHP Anda
        $sql = "
            SELECT 
                users.nama_lengkap AS user_name, 
                users.niplama, 
                presensis.status, 
                presensis.created_at, 
                users_status.id_status, 
                users_jabatan.id_organisasi AS organisasi, 
                users_jabatan.id_users AS id,
                users_jabatan.id_satker AS id_satker 
            FROM users
            JOIN (
                SELECT id_users, MAX(id) AS latest_id 
                FROM users_jabatan 
                GROUP BY id_users
            ) AS latest_users_jabatan 
                ON users.id = latest_users_jabatan.id_users
            JOIN users_jabatan 
                ON latest_users_jabatan.latest_id = users_jabatan.id
            JOIN (
                SELECT id_users, MAX(id) AS latest_status_id 
                FROM users_status 
                GROUP BY id_users
            ) AS latest_users_status 
                ON users.id = latest_users_status.id_users
            JOIN users_status 
                ON latest_users_status.latest_status_id = users_status.id
            LEFT JOIN presensis 
                ON users.niplama = presensis.niplama 
                AND DATE(presensis.created_at) = ?
            WHERE users_status.id_status IN (1, 2, 3, 4, 31, 32)
            ORDER BY presensis.created_at ASC
        ";

        // Jalankan query dengan binding (aman dari SQL injection)
        $rows = DB::select($sql, [$today]);

        return response()->json($rows);
    }
    
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'niplama' => 'required',
            'status'  => 'required|integer',
            'ket_dinas' => 'nullable|string'
        ]);

        $niplama = $request->niplama;
        $status  = (int) $request->status;
        $ket_dinas = $request->ket_dinas;

        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');

        // Check if status 1 exists today
        $status1Exists = DB::table('presensis')
            ->where('niplama', $niplama)
            ->where('status', 1)
            ->whereDate('created_at', $today)
            ->exists();

        if ($status1Exists && $status == 1) {
            return response()->json([
                "success" => false,
                "message" => "Cannot insert: niplama already has status 1 for today."
            ]);
        }

        // Check if any entry exists today
        $hasTodayEntry = DB::table('presensis')
            ->where('niplama', $niplama)
            ->whereDate('created_at', $today)
            ->exists();

        if (!$hasTodayEntry && $status != 1) {
            return response()->json([
                "success" => false,
                "message" => "The first status of the day must be 1."
            ]);
        }

        // Get latest status for today
        $latest = DB::table('presensis')
            ->where('niplama', $niplama)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->first();

        if ($latest) {
            $latest_status = (int) $latest->status;

            // Prevent consecutive status rules
            if ($latest_status == 2 && $status == 2) {
                return response()->json(["success" => false, "message" => "Cannot insert: status 2 cannot be inserted consecutively."]);
            }

            if ($latest_status == 3 && $status == 3) {
                return response()->json(["success" => false, "message" => "Cannot insert: status 3 cannot be inserted consecutively."]);
            }

            if ($latest_status == 5 && $status == 5) {
                return response()->json(["success" => false, "message" => "Cannot insert: status 5 cannot be inserted consecutively."]);
            }

            // Additional logic identical to your PHP checks
            if (in_array($latest_status, [2, 5]) && $status != 3) {
                return response()->json(["success" => false, "message" => "New status must be 3."]);
            }

            if ($latest_status == 3 && !in_array($status, [2, 5])) {
                return response()->json(["success" => false, "message" => "Status after 3 must be 2 or 5."]);
            }

            if ($latest_status == 1 && !in_array($status, [2, 5])) {
                return response()->json(["success" => false, "message" => "Status after 1 must be 2 or 5."]);
            }
        }

        // Insert new record
        $created_at = Carbon::now('Asia/Singapore');

        DB::table('presensis')->insert([
            'niplama'   => $niplama,
            'status'    => $status,
            'ket_dinas' => $ket_dinas,
            'created_at'=> $created_at,
        ]);

        return response()->json([
            "success" => true,
            "message" => "Data saved successfully"
        ]);
    }
    
     public function generate(Request $request)
    {
        // Validate request
        $request->validate([
            'kode_satker' => 'required',
            'kode_qr'     => 'required'
        ]);

        $kodeSatker = $request->kode_satker;
        $kodeQrInput = $request->kode_qr;

        // Use today's date as Ymd format
        $currentDate = Carbon::now('Asia/Singapore')->format('Ymd');

        // Hash the kode_qr
        $hashed = hash('sha256', $kodeQrInput);

        // Build final QR code
        $newKodeQr = $hashed . '-' . $currentDate . '-' . $kodeSatker;

        // Check if this QR already exists
        $exists = DB::table('kode_qrs')
            ->where('kode_qr', $newKodeQr)
            ->exists();

        if ($exists) {
            return response()->json([
                "success" => false,
                "message" => "QR Code sudah digunakan"
            ]);
        }

        // Insert new QR code
        DB::table('kode_qrs')->insert([
            'kode_satker' => $kodeSatker,
            'kode_qr'     => $newKodeQr
        ]);

        return response()->json([
            "success" => true,
            "message" => "QR Code Generated Successfully",
            "qr_code" => $newKodeQr
        ]);
    }
    
    public function index_running()
    {
        // Fetch all running text
        $data = DB::table('running_text')->select('text')->get();

        return response()->json($data);
    }
}
