<?php
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Task;
use App\User;
use App\penugasan;
use Illuminate\Support\Str;
use App\kegiatan;

class TaskController extends Controller
{
    public function store(Request $request){

        $latestTask = Task::latest()->first();

$id = $latestTask ? $latestTask->id + 1 : 1;

         $a = explode(",",$request->owners);
         
         $b =  str_replace('["','', $a);
         $c = str_replace('"]','', $b);
         $pegawai = str_replace('"','', $c);
         
         
         $pegawai2=  str_replace(array('[', ']', '"','\\'), '', htmlspecialchars(json_encode($request->owners), ENT_NOQUOTES));
        
        $task = new Task();
        
        $task->text = $request->text;
       $task->owners = $pegawai2 ;
        $task->jenis = 'Kegiatan' ;
        $task->start_date = $request->start_date;
        $end_date = \Carbon\Carbon::parse($request->input('end_date'));
        $date_akhir = $end_date->subDays(1);

        $task->date_akhir = $date_akhir;
        $task->duration = $request->duration;
        $task->progress = $request->has("progress") ? $request->progress : 0;
        $task->parent = $request->parent;
        $task->sortorder = Task::max("sortorder") + 1;
        $task->status = 'Belum';
        $task->agenda = $request->agenda;
        $task->tempat = $request->tempat;
        $task->save();

     for ($i = 0; $i < count($pegawai); $i++) {
         
    $tugas = new penugasan();
    $tugas->id_kegiatan = $id;
  
    $tugas->niplama = $pegawai[$i];
   
    $simpan = $tugas->save();

    $user = User::where('niplama', $pegawai[$i])->first();
    $kunci[] = $user ? $user->token_google : null; 

}


     
    $path_to_fcm = "https://fcm.googleapis.com/fcm/send";

    $server_key =
        "AAAAt-obBhI:APA91bG4Dp9xeJoq7HmrZ1aQbhfErrBUdDk-kczCgo9wiFvQubkuYuPpQ5knzqDw4gnPt9bikoQpLyAjvzpoIBH_65mK7gY5EK7J-u9GI4KkOnL4z2x4Haxr0_oRD91yLiRHMCT27KYM";

    $headers = [
        "Authorization:key=" . $server_key,
        "Content-Type:application/json",
    ];
    

    $fields = [
        "registration_ids" => $kunci,
        "priority" => "normal",
        "notification" => [
            "title" => $request->text,
            "body" => $request->agenda,
            "android_channel_id" => "default",
        ],
    ];
    
    $payload = json_encode($fields);

    $curl_session = curl_init();

    curl_setopt($curl_session, CURLOPT_URL, $path_to_fcm);
    curl_setopt($curl_session, CURLOPT_POST, true);
    curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);

    $curl_result = curl_exec($curl_session);

        
 
        return response()->json([
            "action"=> "inserted",
            "tid" => $task->id
        ]);
    }
    

    public function store_surat(Request $request){
        
        if(is_null($request->file('surat'))){
        }
        else{
    
        $file = $request->file('surat');
        $name = time(). '.' . $request->file('surat')->getClientOriginalName(); 
            
        }

        $path = public_path('documents'.DIRECTORY_SEPARATOR);
       
        if(is_null($request->file('surat'))){
        }
        else{
            $file->move($path, $name);
        }
        $id = task::latest()->first()->id;
        $task = Task::find($id);
        
        if(is_null($request->file('surat'))){
        }
        else{
        $task->surat = $name;
        }

        $task->save();
        return back()->with(['success' => 'Upload Berhasil']);
    }


    public function update($id, Request $request){

        $task = Task::find($id);
        

       $a = explode(",",$request->owners);
         
         $b =  str_replace('["','', $a);
         $c = str_replace('"]','', $b);
         $pegawai = str_replace('"','', $c);
         
         
         $pegawai2=  str_replace(array('[', ']', '"','\\'), '', htmlspecialchars(json_encode($request->owners), ENT_NOQUOTES));
        
       
        $task->text = $request->text;
       $task->owners = $pegawai2 ;
       //$task->surat = $surat ;
        $task->start_date = $request->start_date;
        $end_date = \Carbon\Carbon::parse($request->input('end_date'));
        $date_akhir = $end_date->subDays(1);

        $task->date_akhir = $date_akhir;
        $task->duration = $request->duration;
        $task->progress = $request->has("progress") ? $request->progress : 0;
        $task->parent = $request->parent;
        $task->agenda = $request->agenda;
        $task->tempat = $request->tempat;
        $task->status = $request->status;
        
        $task->save();

        for ($i = 0; $i < count($pegawai); $i++) {
    $tugas = new penugasan();
    $tugas->id_kegiatan = $id;
  
    $tugas->niplama = $pegawai[$i];
   
    $simpan = $tugas->save();

    $user = User::where('niplama', $pegawai[$i])->first();
    $kunci[] = $user ? $user->token_google : null; 

}
        

        $path_to_fcm = "https://fcm.googleapis.com/fcm/send";

        $server_key =
            "AAAAt-obBhI:APA91bG4Dp9xeJoq7HmrZ1aQbhfErrBUdDk-kczCgo9wiFvQubkuYuPpQ5knzqDw4gnPt9bikoQpLyAjvzpoIBH_65mK7gY5EK7J-u9GI4KkOnL4z2x4Haxr0_oRD91yLiRHMCT27KYM";
    
        $headers = [
            "Authorization:key=" . $server_key,
            "Content-Type:application/json",
        ];
        
       

       


    
        
        

        $fields = [
            "registration_ids" => $kunci,
            "priority" => "normal",
            "notification" => [
                "title" => $request->text,
                "body" => $request->agenda,
                "android_channel_id" => "default",
            ],
        ];
        
        $payload = json_encode($fields);
    
        $curl_session = curl_init();
    
        curl_setopt($curl_session, CURLOPT_URL, $path_to_fcm);
        curl_setopt($curl_session, CURLOPT_POST, true);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
    
        $curl_result = curl_exec($curl_session);
      

       
        
        
        

        if($request->has("target")){
            $this->updateOrder($id, $request->target);
        }
 
        return response()->json([
            "action"=> "updated"
        ]);

        return redirect()->back()->with(['success' => 'Berhasil ']);
    }


    private function updateOrder($taskId, $target){
        $nextTask = false;
        $targetId = $target;
     
        if(strpos($target, "next:") === 0){
            $targetId = substr($target, strlen("next:"));
            $nextTask = true;
        }
     
        if($targetId == "null")
            return;
     
        $targetOrder = Task::find($targetId)->sortorder;
        if($nextTask)
            $targetOrder++;
     
        Task::where("sortorder", ">=", $targetOrder)->increment("sortorder");
     
        $updatedTask = Task::find($taskId);
        $updatedTask->sortorder = $targetOrder;
        $updatedTask->save();
    }
 
    public function destroy($id){
        $task = Task::find($id);
        $task->delete();
        
        penugasan::where('id_kegiatan', $id)->delete();
       
        
        return response()->json([
            "action"=> "deleted"
        ]);
    }
}