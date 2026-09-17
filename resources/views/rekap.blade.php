<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi - Sikeren</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css_mazer/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors_mazer/iconly/bold.css') }}">
    
    <!-- Include DataTables CSS -->
<link href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" rel="stylesheet" />


    <link rel="stylesheet" href="{{ asset('assets/vendors_mazer/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors_mazer/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css_mazer/app.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images_mazer/favicon.svg') }}" type="image/x-icon">
</head>

<body>
    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                
                  
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
               
             
                <br>
               <center> <a href="dashboard"><img src="{{asset('assets/img/logo-sikeren.png')}}" width="100" height="50px" Alt="Sikeren" ></a> </center> 
             
                      
                   
              
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Menu</li>

                        <li class="sidebar-item">
                            <a href="dashboard" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Kegiatan &amp; Rapat</li>

                        <li class="sidebar-item">
                            <a href="{{ url('penugasan') }}" class='sidebar-link''>
                                <i class="bi bi-calendar-plus-fill"></i>
                                <span>Buat Kegiatan</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="rapat" class='sidebar-link'>
                                <i class="bi bi-people-fill"></i>
                                <span>Buat Rapat</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="daftar_kegiatan" class='sidebar-link'>
                                <i class="bi bi-list-ul"></i>
                                <span>Daftar Kegiatan</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="gantt" class='sidebar-link'>
                                <i class="bi bi-kanban-fill"></i>
                                <span>Tugas Saya</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Notula & BMN</li>

                        <li class="sidebar-item">
                            <a href="notulis" class='sidebar-link'>
                                <i class="bi bi-journal-text"></i>
                                <span>Notula</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="bmn" class='sidebar-link'>
                                <i class="bi bi-box-seam-fill"></i>
                                <span>BMN</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Presensi</li>

                        <li class="sidebar-item">
                            <a href="qr" class='sidebar-link'>
                                <i class="bi bi-file-earmark-medical-fill"></i>
                                <span>QR Code</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="report" class='sidebar-link'>
                                <i class="bi bi-file-earmark-medical-fill"></i>
                                <span>Report Presensi</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="absen-kantor" class='sidebar-link'>
                                <i class="bi bi-file-earmark-medical-fill"></i>
                                <span>Presensi</span>
                            </a>
                        </li>

                    </ul>
                </div>
                <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
            </div>
        </div>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>Rekap Bulanan</h3>
                            <p class="text-subtitle text-muted">Sistem Kegiatan Terencana</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active" aria-current="page">{{ Auth::user()->nama_lengkap }}</li>
                                    <li class="breadcrumb-item"><a href="{{ route('actionlogout') }}">Log Out</a></li>
                                    
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                
            </div>
            @if (session('success'))
                                            <div class="alert alert-success">
                                            {{ session('success') }}
                                            </div>
                                            @endif
        
        @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                
                <form method="GET" action="{{ route('rekap') }}">
    <div class="form-group">
        <label for="date">Pilih Tanggal</label>
        <input type="date" id="date" name="date" class="form-control" value="{{ $selectedMonth }}">
    </div>


    <button type="submit" class="btn btn-primary">Filter</button>
</form>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="tab281-tab" data-bs-toggle="tab" href="#tab281" role="tab" aria-controls="tab281" aria-selected="true">Rekap Per Bulan</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab282-tab" data-bs-toggle="tab" href="#tab282" role="tab" aria-controls="tab282" aria-selected="false">Rekap Per Status Kehadiran</a>
                            </li>
                        </ul>
                        
                        <br>
                        <div class="tab-content" id="myTabContent">
                        <!-- Tab 1 Content -->
                        <div class="tab-pane fade show active" id="tab281" role="tabpanel" aria-labelledby="tab281-tab">
                            <div class="card-body"> 
                            <table id="table_id2" class="table table-striped">
    <thead>
        <tr>
            <th>Pegawai</th>
            @foreach ($dates as $date)
                <th>{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $niplama => $name)
            <tr>
                <td>
                    {{ $name }}<br>({{ $niplama }})
                </td>
                @foreach ($dates as $date)
                    <td>
                        @if (!empty($data[$niplama][$date]))
                            @foreach ($data[$niplama][$date] as $entry)
                                {{ $entry }}<br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
</div>
                            </div>
                            
                            <div class="tab-pane fade show" id="tab282" role="tabpanel" aria-labelledby="tab282-tab">
                           <table id="table_id" class="table table-striped">
    <thead>
        <tr>
            <th>Pegawai</th>
            <th>Bulan</th>
            <th>Total Hari Kerja</th>
            <th>Total Presensi</th>
            <th>Tugas Luar/Cuti</th>
            <th>Tidak Presensi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($totalPerMonth as $row)
            <tr>
                <td>{{ $row->nama_lengkap }}</td>
                <td>{{ $row->month }}</td>
                <td>{{ $row->total_working_days }}</td>
                <td>{{ $row->total }}</td>
                
                <td>{{ $row->total_status_6 + $row->total_status_7 }}</td>
                <td> {{ $row->total_working_days - $row->total - $row->total_status_6  }} </td>
            </tr>
        @endforeach
    </tbody>
</table>
                            </div>
                            
                            
                            </div>

 


           



            
           

<!-- Include Bootstrap's JS to enable modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>




                             
                                    </div>
                                </div>
                            </div>



                           


                        </div>


            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>{{ date("Y") }} &copy; BPS Provinsi Sulawesi Tenggara</p>
                    </div>
                    <div class="float-end">
                        
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="{{ asset('assets/vendors_mazer/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js_mazer/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('assets/vendors_mazer/apexcharts/apexcharts.js') }}"></script>
    

    <script src="{{ asset('assets/js_mazer/main.js') }}"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
  <script src="https://rawgit.com/bnjmnhndrsn/select2-optgroup-select/master/example/vendor/select2.js"></script>
<script src="https://rawgit.com/bnjmnhndrsn/select2-optgroup-select/master/dist/select2.optgroupSelect.js"></script>

<!-- Include DataTables JS -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
        src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.4/kt-2.7.0/r-2.3.0/rg-1.2.0/rr-1.2.8/sc-2.0.7/sb-1.3.4/sp-2.0.2/sl-1.4.0/sr-1.1.1/datatables.min.js">
    </script>


<script>
        $(document).ready(function() {
            $('#table_id').DataTable({
                "ordering": true,
                 "paging": false,
                 "searching": false,
                 "info": false,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                

            });
        });

</script>

<script>
        $(document).ready(function() {
            $('#table_id2').DataTable({
                "ordering": true,
                 "paging": false,
                 "searching": false,
                 "info": false,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                

            });
        });

</script>


<script>
    
</script>

  
  <script src="{{asset('assets/ckeditor/ckeditor.js')}}"></script>
<script>
   var topik = document.getElementById("topik_ck");
   CKEDITOR.replace( 'topik_ck' );
   CKEDITOR.config.allowedContent = true;
</script>

<script>
 $(document).ready(function() {
    $("#search").autocomplete({
 
        source: function(request, response) {
            $.ajax({
            url: "{{url('autocomplete')}}",
            data: {
                    term : request.term
             },
            dataType: "json",
            success: function(data){
               var resp = $.map(data,function(obj){
                    return obj.nama_lengkap;
               }); 
 
               response(resp);
            }
        });
    },
    minLength: 1
 });
});
 
</script>   


<script>

$(document).ready(function() {
    $('.js-example-basic-single').select2();

    $('#target').select2();

	$(function(){
	    $.fn.select2.amd.require(["optgroup-data", "optgroup-results"], 
	        function (OptgroupData, OptgroupResults) {
	        $('#target').select2({
	            dataAdapter: OptgroupData,
	            resultsAdapter: OptgroupResults,
	            closeOnSelect: false,
				
	        }); 
	    });
	});
});

  </script>

  <script>

$("#moveright").on('click' , function(){
   
    var obj = ($("#city option:selected"));
    $.each(obj, function(index , item){ 
         $("#peserta").append($(this));
    });
   
});

$("#moveleft").on('click' , function(){
    
    var obj = ($("#peserta option:selected"));    
     $.each(obj, function(index , item){ 
         $("#city").append($(this));
    });
});

</script>

<script type=text/javascript>
  $('#country').change(function(){
  var countryID = $(this).val();  
  if(countryID){
    $.ajax({
      type:"GET",
      url:"{{url('getState')}}?country_id="+countryID,
      success:function(res){        
      if(res){
        $("#state").empty();
        $("#state").append('<option>Select State</option>');
        $.each(res,function(key,value){
          $("#state").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#state").empty();
      }
      }
    });
  }else{
    $("#state").empty();
    $("#city").empty();
  }   
  });
  $('#state').on('change',function(){
  var stateID = $(this).val();  
  if(stateID){
    $.ajax({
      type:"GET",
      url:"{{url('getPegawai')}}?id_organisasi="+stateID,
      success:function(res){        
      if(res){
        $("#city").empty();
 $("#city");
        $.each(res,function(key,value){
          $("#city").append('<option value="'+key+'">'+value+'</option>');
        });
      
      }else{
        $("#city").empty();
      }
      }
    });
  }else{
    $("#city").empty();
  }
    
  });
</script>

<script>
$('#target').select2();

$(function(){
    $.fn.select2.amd.require(["optgroup-data", "optgroup-results"], 
        function (OptgroupData, OptgroupResults) {
        $('#target').select2({
            dataAdapter: OptgroupData,
            resultsAdapter: OptgroupResults,
            closeOnSelect: false,
      
        }); 
    });
});
</script>
</body>

</html>
