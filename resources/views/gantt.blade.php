@extends('layouts.app')

@section('title', 'Kegiatan Saya - Sikeren')
@section('header_title', 'Kegiatan Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/codebase/dhtmlxgantt.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css?v=5.2.4">
    <style>
    .gantt_tree_content {
    overflow:hidden;
    text-overflow: ellipsis;
}
    .weekend{ background: rgba(183,142,185,0.3) !important;}
		.gantt_cal_chosen,
		.gantt_cal_chosen select{
			width: 530px;
		}
  		.owner-label{
			width: 20px;
			height: 20px;
			line-height: 20px;
			font-size: 12px;
			display: inline-block;
			border: 1px solid #cccccc;
			border-radius: 25px;
			background: #e6e6e6;
			color: #6f6f6f;
			font-weight: bold;
            margin-top: 5px;
		}
        .fa {
    cursor: pointer;
    font-size: 14px;
    text-align: center;
    opacity: 0.2;
    padding: 5px;
}

.fa:hover {
    opacity: 1;
}

.fa-pencil {
    color: #ffa011;
    border-radius: 50%;
}

.lightbox1{
    background: #77D760;
}

.almost_complete{
  background: orange;
}
  
.gantt_task_progress{
  background-color:rgba(3,201,169,1);
}

.gantt_cal_larea{
			overflow:visible;
		}
		.gantt_cal_chosen,
		.gantt_cal_chosen select{
			width: 530px;
      
		}

    .select2-container{ z-index:10002; }

</style>
@endpush

@section('content')
<div class="space-y-6">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Kegiatan Saya (Gantt Chart)</h2>
        <p class="text-gray-500 text-sm mt-1">Pantau dan kelola jadwal kegiatan Anda.</p>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 min-h-[600px] flex flex-col">
        <label> Tanggal Mulai &nbsp <input id='search_start' type='date' style='width:150px' value="2024-01-01"/> </label>
<label> Tanggal Akhir &nbsp <input id='search_end' type='date' style='width:150px' value="2024-12-31"/> </label>
<button onClick="change_detector()">Filter</button>

<div style="float:right" >
    <br>
<form class="gantt_control">
	

	

	<input type="radio" id="scale1" class="gantt_radio" name="scale" value="day"  checked>
	<label for="scale1">Day scale</label>

	<input type="radio" id="scale2" class="gantt_radio" name="scale" value="week">
	<label for="scale2">Week scale</label>

	<input type="radio" id="scale3" class="gantt_radio" name="scale" value="month">
	<label for="scale3">Month scale</label>

	<input type="radio" id="scale4" class="gantt_radio" name="scale" value="quarter">
	<label for="scale4">Quarter scale</label>

	<input type="radio" id="scale5" class="gantt_radio" name="scale" value="year">
	<label for="scale5">Year scale</label>

</form>
</div>
<br>
<br>
<select onchange=show_owners(this.value) class="chosen-select" >


@if( (Auth::user()->nama_lengkap == 'Agnes Widiastuti') )
<option value="All">All</option>
    @foreach ($peserta as $pesertas)
    <option value="{{$pesertas->niplama}}">{{$pesertas->nama_lengkap}}</option>
    @endforeach
@else
<option value="All">All</option>
<option value="{{Auth::user()->niplama}}">{{Auth::user()->nama_lengkap}}</option>
 
 
@endif

</select>


<input type=checkbox value="Belum" onclick="change_filter()"> Belum
<input type=checkbox value="Selesai" onclick="change_filter()"> Selesai


<div id="gantt_here" style='width:100%; height:100%;'> 

</div>
  </div>                                      
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                     
                    </div>

                  


                
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="{{ asset('assets/codebase/dhtmlxgantt.js') }}"></script>
    <script src="https://rawgit.com/bnjmnhndrsn/select2-optgroup-select/master/example/vendor/select2.js"></script>
    <script src="https://rawgit.com/bnjmnhndrsn/select2-optgroup-select/master/dist/select2.optgroupSelect.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.js?v=5.2.4"></script>
    <script type="text/javascript">



$(document).ready(function() {
    $(".chosen-select").chosen();

    $.ajaxSetup({
			headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
        
});

    gantt.config.date_format = "%Y-%m-%d %H:%i:%s";
    

    let show_owner = 'All';

    
function show_owners(selected_owner) {
    show_owner = selected_owner;
    gantt.render();
}


gantt.ownerss =[ @foreach ($peserta as $pesertas) {
    
    key: {{$pesertas->niplama}},
   
    label: "{{$pesertas->nama_lengkap}}"
},
@endforeach


];

function find_by_id(owners) {
    for (let i = 0; i < gantt.ownerss.length; i++) {
        if (owners.includes(gantt.ownerss[i].key)) return gantt.ownerss[i];
    }
    return gantt.ownerss[0]

};

var filter = null;
function change_filter(){
  filter = {};
  var filters = document.querySelectorAll("input");
  var turn_off_filter = true;
  for (var i = 0; i < filters.length; i++) {
    if (filters[i].checked) {
      filter[filters[i].value] = filters[i].value;
      turn_off_filter = false
    }
    else {
      filter[filters[i].value] = false;
    }
  }
  if (turn_off_filter) filter = null;

  gantt.render();
}


gantt.attachEvent("onBeforeTaskDisplay", function(id, task){
  if (filter && task.status != filter[task.status]){
    return false;
  }
  return true;
});


gantt.attachEvent("onBeforeTaskDisplay", function (id, task) {
    
    if (show_owner == "All") return true;
    
    if (task.owners.includes(show_owner)) return true;
    return false;
});

    gantt.attachEvent("onLightboxSave", function(id, new_task, is_new){
    
  var task = gantt.getTask(id);
  var par=gantt.getParent(id);
    
  if(par!=0){  
  if (new_task.duration > gantt.getTask(par).duration || new_task.end_date > gantt.getTask(par).end_date ) {gantt.message("Melebihi Durasi Project!")  
  return false;}
  else{

   // gantt.refreshData();
    gantt.message("Berhasil Simpan")
     
    return true;
    
  }
}
  else{
    return true;
  }
        

  if (is_new) {
        delete task.$index;
    }
    return true;


});


gantt.attachEvent("onBeforeTaskChanged", function(id, mode, new_task){
  var task = gantt.getTask(id);

 

});

var sizes = {};
    var taskToDrag = null;
    var taskStartDate = null;
    var taskEndDate = null;
    var toggleTaskToDrag = false;

    function toggleCreateTaskToDrag (value) {
        if (value) {
            toggleTaskToDrag = value
        } else return false
    };


 

    gantt.attachEvent("onBeforeTaskChanged", function(id, mode, task){
        var taskToReturnDate = gantt.getTask(id);

        var par=gantt.getParent(id);  


    if(par!=0){ 
        if (mode === "move" || mode === "resize") {

            taskStartDate = taskToReturnDate.start_date;
            taskEndDate = taskToReturnDate.end_date;
            taskEndDuration = taskToReturnDate.duration;
            taskToDrag = taskToReturnDate;

            toggleTaskToDrag = true;
            sizes = gantt.getTaskPosition(taskToReturnDate);
            

            gantt.confirm({
                text: "Are you sure?",
                ok:"Yes",
                cancel:"No",
                callback: function(result){
                    if (result === true) {
                        gantt.getTask(id).start_date = taskStartDate;
                        gantt.getTask(id).end_date = taskEndDate;
                        if(taskEndDate > gantt.getTask(par).end_date ){      
                        gantt.getTask(par).end_date = taskEndDate;
                        gantt.getTask(par).duration= taskEndDuration }

                        gantt.updateTask(id);
                        gantt.updateTask(par);
                        
                       
                   
                    }
                    
                    gantt.render();
                }
            });

            return false;
        }
        return true
    }
    else{
        if (mode === "move" || mode === "resize") {

taskStartDate = taskToReturnDate.start_date;
taskEndDate = taskToReturnDate.end_date;
taskEndDuration = taskToReturnDate.duration;
taskToDrag = taskToReturnDate;

toggleTaskToDrag = true;
sizes = gantt.getTaskPosition(taskToReturnDate);


gantt.confirm({
    text: "Are you sure?",
    ok:"Yes",
    cancel:"No",
    callback: function(result){
        if (result === true) {
            gantt.getTask(id).start_date = taskStartDate;
            gantt.getTask(id).end_date = taskEndDate;
           

            gantt.updateTask(id);
           
            
           
       
        }
        
        gantt.render();
    }
});

return false;
}
return true
    }




    });

    //ubah warna
    gantt.templates.task_class = function(start, end, task){
  if (task.progress > 0.8) return "almost_complete";
};


  gantt.form_blocks["multiselect"] = {
		render: function (sns) {
			var height = (sns.height || "23") + "px";
			var html = "<div class='gantt_cal_ltext gantt_cal_chosen gantt_cal_multiselect' style='height:" + height + ";'><select data-placeholder='...' id='target' multiple> @foreach ($master_groups as $category) <optgroup label='{{ $category->grup }}'> @foreach ($groups as $participant) @if ($participant->grup === $category->grup) <option value='{{ $participant->niplama }}'>{{ $participant->nama_lengkap }}</option> @endif @endforeach </optgroup>  @endforeach   ";
		
			html += "</select></div>";
			return html;
		},

    set_value: function (node, value, ev, sns) {
			node.style.overflow = "visible";
			node.parentNode.style.overflow = "visible";
			node.style.display = "inline-block";
			var select = $(node.firstChild);
      
			if (value) {
				value = (value + "").split(",");
				select.val(value);
			}
			else {
				select.val([]);
			}

			select.select2();

      
      select.select2.amd.require(["optgroup-data", "optgroup-results"], 
        function (OptgroupData, OptgroupResults) {
        select.select2({
            dataAdapter: OptgroupData,
            resultsAdapter: OptgroupResults,
            closeOnSelect: false,
      
              }); 
          });
      
      
			if(sns.onchange){
				select.change(function(){
					sns.onchange.call(this);
				})
			}
			select.trigger('chosen:updated');
			select.trigger("change");
		},

		get_value: function (node, ev) {
			var value = $(node.firstChild).val();
			return value;
		},

		focus: function (node) {
			$(node.firstChild).focus();
		}
	};

gantt.serverList("people", [ @foreach ($peserta as $pesertas) {
    
        key: {{$pesertas->niplama}},
       
        label: "{{$pesertas->nama_lengkap}}"
    },
    @endforeach
   

]);

function findUser(id) {
    var list = gantt.serverList("people");
    for (var i = 0; i < list.length; i++) {
        if (list[i].key == id) {
            return list[i];
        }
    }
    return null;
}


var progressEditor = {type: "custom_editor", map_to: "progress", min:0.1, max: 1};


function clone_task(id) {
    const task = gantt.getTask(id);
    const clone = gantt.copy(task);
    clone.id = +(new Date());
    gantt.addTask(clone, clone.parent)
}

gantt.templates.task_text=function(start,end,task){
    return "<span style='text-align:left;'>" + Math.round(task.progress * 100) + "% </span> <a target=_blank href='https://www.google.com'> "+task.text+"</a> ";
};




var admin = ['Agnes Widiastuti','Muhammad Rizal Karim'];
var user = '{{ Auth::user()->nama_lengkap }}'
if (admin.includes(user)){
   gantt.config.columns = [{
        name: "text",
        align: "left",
        label: "Task Name <input placeholder='Search tasks...' id='search' type='field' oninput=changeDetector() style='width:150px' />",
        tree: true,
        width: 300,
        template:function(obj){
                                return "<a href='https://www.google.com'> "+obj.text+" </a>"},
        resize: true
    },
    
    {
        name: "start_date",
        align: "center",
        width: 80,
        resize: true
    },
   
    {
        name: "duration",
        width: 50,
        align: "center"
    },
    
    {
        name: "clone", label: "Duplikat", width: 64, template: function (task) {
            return "<input type=button value='Copy' onclick=clone_task(" + task.id + ")>"
        }
    },

    {
        name: "add",
        width: 30
    }
];
}
else { 
    gantt.config.columns = [{
        name: "text",
        align: "left",
        label: "Task Name <input placeholder='Search tasks...' id='search' type='field' oninput=changeDetector() style='width:150px' />",
        tree: true,
        width: 300,
        template:function(obj){
                                return "<a href='https://www.google.com'> "+obj.text+" </a>"},
        resize: true
    },
    
    {
        name: "start_date",
        align: "center",
        width: 80,
        resize: true
    },
   
    {
        name: "duration",
        width: 50,
        align: "center"
    }
];}













 //end date + 1 dan tool tip
gantt.templates.task_end_date = function(date){
   return gantt.templates.task_date(new Date(date.valueOf() - 1)); 
};

var strToDate= gantt.date.str_to_date("%Y-%m-%d");
var gridDateToStr = gantt.date.date_to_str("%Y-%m-%d");
gantt.templates.grid_date_format = function(date, column){
   if(column === "end_date"){
     return gridDateToStr(new Date(date.valueOf() - 1)); 
   }else{
     return gridDateToStr(date); 
   }
   
}
 
//end date + 1 dan tool tip



var dateToStr = gantt.date.date_to_str("%Y-%m-%d");
var strToDate= gantt.date.str_to_date("%Y-%m-%d");
var start_filter_data, end_filter_data;
var start_search_box = document.getElementById("search_start");
var end_search_box = document.getElementById("search_end");

gantt.attachEvent("onDataRender", function(){
  start_search_box = document.getElementById("search_start");
  end_search_box = document.getElementById("search_end");
});   

function change_detector(){
  
    start_filter_data = start_search_box.value;

    end_filter_data = end_search_box.value;
 
  gantt.refreshData();
} 

function compare_input(id) {
  var match = false; 
    // check task's text
    if (gantt.getTask(id).start_date >= strToDate(start_search_box.value) && gantt.getTask(id).end_date <= strToDate(end_search_box.value))
      match = true;
return match;
}

gantt.attachEvent("onBeforeTaskDisplay", function (id, task) {
	if (compare_input(id)) {
		return true;
	}
	return false;
});



gantt.locale.labels.section_owner = "Penugasan";
gantt.locale.labels.section_description = "Topik";
gantt.locale.labels.section_parent = "Kegiatan Utama";
gantt.locale.labels.section_agenda = "Agenda";
gantt.locale.labels.section_tempat = "Tempat";

gantt.config.lightbox.sections = [{
        name: "description",
        height: 38,
        map_to: "text",
        type: "textarea",
        focus: true
    },
    
     {
        name: "agenda",
        height: 38,
        map_to: "agenda",
        type: "textarea",
      
    },
    
    {
        name: "tempat",
        height: 38,
        map_to: "tempat",
        type: "textarea",
      
    },

    {name:"parent", type:"parent", allow_root:"true", root_label:"No parent"}, 

  
    
    {
        name: "time",
        type: "duration",
        map_to: "auto"
    },
    
      {
        name: "owner",
        height: 150,
        type: "multiselect",
        options: gantt.serverList("people"),
        map_to: "owners", onchange: function (e) {
     
                gantt.resizeLightbox();
                adjustLightboxHeight();
               
            
        }
    },
    
    {
        name: "time",
       
       
    },

];


function adjustLightboxHeight() {
    var multiselect = $("#target"); // Assuming "target" is the ID of your multiselect dropdown
    var selectedOptionsCount = multiselect.val() ? multiselect.val().length : 0;
    var calculatedHeight = 23 + selectedOptionsCount * 10; // Adjust the height calculation as needed

    // Set the height of the multiselect container
    $(".gantt_cal_multiselect").css("height", calculatedHeight + "px");
    
    // Optionally, you may need to trigger a resize event on the Gantt chart if necessary
    // gantt.resize();
}


function myFunction() {
  var x = document.getElementById("myFile").value;
  
}






gantt.attachEvent("onGanttReady", function(){
    
var admin = ['Agnes Widiastuti','Muhammad Rizal Karim'];
var user = '{{ Auth::user()->nama_lengkap }}'
if (admin.includes(user)){
   gantt.config.buttons_left = ["gantt_save_btn","gantt_cancel_btn","complete_button","complete_button2"];   
   gantt.config.buttons_right = ["gantt_delete_btn"];
}
else { 
    gantt.config.buttons_left = ["gantt_cancel_btn"];   
    gantt.config.buttons_right = [""]; }
    
   
});

gantt.locale.labels["complete_button"] = "Complete";
gantt.locale.labels["complete_button2"] = "UnComplete";

gantt.attachEvent("onLightboxButton", function(button_id, node, e){
    if(button_id == "complete_button"){
        var id = gantt.getState().lightbox;
    
        gantt.getChildren(id).progress = 1;
        gantt.getTask(id).progress = 1;
        gantt.getTask(id).status = "Selesai";
        gantt.updateTask(id);
        gantt.hideLightbox();
    }

    if(button_id == "complete_button2"){
        var id = gantt.getState().lightbox;
        gantt.getTask(id).progress = 0;
        gantt.getTask(id).status = "Belum";
        gantt.updateTask(id);
        gantt.hideLightbox();
    }
});


// scale

var zoomConfig = {
  levels: [
   
    {
      name: "day",
      scale_height: 27,
      min_column_width: 80,
      scales: [
        { unit: "day", step: 1, format: "%d %M" }
      ]
    },
    {
      name: "week",
      scale_height: 50,
      min_column_width: 50,
      scales: [
        {
          unit: "week", step: 1, format: function (date) {
            var dateToStr = gantt.date.date_to_str("%d %M");
            var endDate = gantt.date.add(date, -7, "day");
            var weekNum = gantt.date.date_to_str("%W")(date);
           return "#" + weekNum + ", " + dateToStr(date) + " - " + dateToStr(endDate);
          //  return "Minggu-" + weekNum + " "  ;
          }
        },
        { unit: "day", step: 1, format: "%j %D" }
      ]
    },
    {
      name: "month",
      scale_height: 50,
      min_column_width: 120,
      scales: [
        { unit: "month", format: "%F, %Y" },
        { unit: "week", format: "Week #%W" }
      ]
    },
    {
      name: "quarter",
      height: 50,
      min_column_width: 90,
      scales: [
        { unit: "month", step: 1, format: "%M" },
        {
          unit: "quarter", step: 1, format: function (date) {
            var dateToStr = gantt.date.date_to_str("%M");
            var endDate = gantt.date.add(gantt.date.add(date, 3, "month"), -1, "day");
            return dateToStr(date) + " - " + dateToStr(endDate);
          }
        }
      ]
    },
    {
      name: "year",
      scale_height: 50,
      min_column_width: 30,
      scales: [
        { unit: "year", step: 1, format: "%Y" }
      ]
    }
  ]
};

gantt.ext.zoom.init(zoomConfig);
gantt.ext.zoom.setLevel("day");
gantt.ext.zoom.attachEvent("onAfterZoom", function (level, config) {
  document.querySelector(".gantt_radio[value='" + config.name + "']").checked = true;
})


//wekeend
gantt.templates.timeline_cell_class = function(task,date){
  if(date.getDay()==0||date.getDay()==6){ 
    return "weekend" ;
  }
};

var holidays = ["23-01-2023"];
var format_date = gantt.date.str_to_date("%d-%m-%Y");
for (var i = 0; i < holidays.length; i++) {
    var converted_date = format_date(holidays[i])
    gantt.setWorkTime({date:converted_date, hours:false})
}


gantt.templates.scale_cell_class = function(date){
    if(!gantt.isWorkTime(date)) return "weekend";
};

gantt.templates.timeline_cell_class = function(item,date){
    if(!gantt.isWorkTime(date)) return "weekend" ;
};


gantt.config.work_time = true; 

gantt.config.autosize = "y";
//gantt.config.auto_types = true;

gantt.config.open_tree_initially = true;
gantt.init("gantt_here");


// Fungsi Pencarian
let filterData;
let searchBox = document.getElementById("search");
gantt.attachEvent("onDataRender", () => {
    searchBox = document.getElementById("search");
});
gantt.attachEvent("onGanttRender", () => {
    searchBox = document.getElementById("search");
});
function changeDetector() {
    filterData = searchBox.value;
    gantt.refreshData();
}
function compareInput(id) {
    let match = false;
    // check children's text
    if (gantt.hasChild(id)) {
        gantt.eachTask(childObject => {
            if (compareInput(childObject.id, filterData)) match = true;
        }, id);
    }
    // check task's text
    if (gantt.getTask(id).text.toLowerCase().indexOf(filterData.toLowerCase()) >= 0 || gantt.getTask(id).owners.toLowerCase().indexOf(filterData.toLowerCase()) >= 0)
        match = true;
    return match;
}
gantt.attachEvent("onBeforeTaskDisplay", (id, task) => {
    if (compareInput(id)) {
        return true;
    }
    return false;
});
changeDetector();

// Akhir Fungsi Pencarian

    gantt.load("api/data");


    var dp = new gantt.dataProcessor("api");
    dp.init(gantt);
    dp.setTransactionMode("REST");
    
    sessionStorage.setItem("lastname", "Smith");
    let personName = sessionStorage.getItem("lastname");


//otomatis parent progress


gantt.attachEvent("onAfterTaskUpdate", function(id,item){
		if (item.parent == 0) {
			return;
		}
		
		var parentTask = gantt.getTask(item.parent); //console.log(parent.id);return;
		
		var childs = gantt.getChildren(parentTask.id);
		var totProgress = 0;
		
		var tempTask;
		for (i = 0; i < childs.length; i++) { //console.log(childs[i]);
			tempTask = gantt.getTask(childs[i]);
			totProgress += parseFloat(tempTask.progress);
		}
		//console.log(totProgress);
		
		parentTask.progress = (totProgress / childs.length).toFixed(2);
		gantt.updateTask(parentTask.id);
	});


// ubah scale

function zoomIn() {
  gantt.ext.zoom.zoomIn();
}
function zoomOut() {
  gantt.ext.zoom.zoomOut()
}

var radios = document.getElementsByName("scale");
for (var i = 0; i < radios.length; i++) {
  radios[i].onclick = function (event) {
    gantt.ext.zoom.setLevel(event.target.value);
  };
}



gantt.ext.zoom.attachEvent("onAfterZoom", function (level, config) {
  var dates = gantt.getSubtaskDates();
  gantt.date.month_start(dates.start_date);
  gantt.config.start_date = dates.start_date;
  gantt.config.end_date = gantt.config.end_date || gantt.getState().max_date;
  gantt.render();
  gantt.scrollTo(0, null)
});






</script>
@endpush
