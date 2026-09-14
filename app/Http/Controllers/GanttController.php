<?php
namespace App\Http\Controllers;
use App\Task;
use App\Link;
use App\penugasan;
 
class GanttController extends Controller
{
    public function get(){
        $tasks = new Task();
        $links = new Link();
        $penugasans = new penugasan();
        return response()->json([
            "data" => $tasks
            ->orderBy('sortorder')->get(),

            "links" => $links->all(),
           
            
        ]);
    }
}