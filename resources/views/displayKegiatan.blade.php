<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Notula - Sikeren</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css_mazer/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendors_mazer/iconly/bold.css') }}">

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

                        <li class="sidebar-item  ">
                            <a href="{{ url('dashboard') }}" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>


                        <li class="sidebar-title">Kegiatan &amp; Rapat</li>


                        <li class="sidebar-item  ">
                            <a href="{{ url('penugasan') }}" class='sidebar-link'>
                                <i class="bi bi-calendar-plus-fill"></i>
                                <span>Buat Kegiatan</span>
                            </a>
                        </li>


                        <li class="sidebar-item ">
                            <a href="{{ url('rapat') }}" class='sidebar-link'>
                                <i class="bi bi-people-fill"></i>
                                <span>Buat Rapat</span>
                            </a>
                        </li>

                        <li class="sidebar-item  ">
                            <a href="{{ url('daftar_kegiatan') }}" class='sidebar-link'>
                                <i class="bi bi-list-ul"></i>
                                <span>Daftar Kegiatan</span>
                            </a>
                        </li>

                        <li class="sidebar-item  ">
                            <a href="{{ url('gantt') }}" class='sidebar-link'>
                                <i class="bi bi-kanban-fill"></i>
                                <span>Kegiatan Saya</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Notula & BMN</li>

                        <li class="sidebar-item active">
                            <a href="{{ url('notulis') }}" class='sidebar-link'>
                                <i class="bi bi-journal-text"></i>
                                <span>Notula</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="{{ url('bmn') }}" class='sidebar-link'>
                                <i class="bi bi-box-seam-fill"></i>
                                <span>BMN</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Presensi</li>

                        <li class="sidebar-item">
                            <a href="{{ url('qr') }}" class='sidebar-link'>
                                <i class="bi bi-file-earmark-medical-fill"></i>
                                <span>QR Code</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="{{ url('report') }}" class='sidebar-link'>
                                <i class="bi bi-file-earmark-medical-fill"></i>
                                <span>Report Presensi</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="{{ url('absen-kantor') }}" class='sidebar-link'>
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
                            <h3>Detail Notula</h3>
                            <p class="text-subtitle text-muted">Sistem Kegiatan Terencana</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active" aria-current="page">{{ Auth::user()->nama_lengkap }}</li>
                                    <li class="breadcrumb-item"><a href="dashboard">Log Out</a></li>
                                    
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                
            </div>


            <div class="page-content">
                


                <section class="row">
                    <div class="col-12 col-lg-12">
                        

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>   @foreach ($kegiatans->slice(0, 1) as $result)
                                               <center>  {{ $result->text }} </center>
                                                @endforeach   
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        
                                         @foreach ($kegiatans->slice(0, 1) as $result)
                    						<table class="table table-striped">
                    							<tr>
                                              <th> <h4 style="font-weight:700; ">Agenda</h4> </th>
                                             <td>  <p align="justify">  {{ $result->agenda }} </p> </td>
                    							</tr>
                    
                    						<tr>
                                            <th>    <h4 style="font-weight:700; ">Tempat</h4> </th>
                                            <td>    {{ $result->tempat }} </td>
                    						</tr>
                    
                    						<tr>
                                            <th>    <h4 style="font-weight:700; ">Tanggal</h4> </th>
                                            <td>    {{ $result->start_date }} s.d. {{ $result->date_akhir }} </td>
                    						</tr>
                    
                    						<tr>
                                            <th>    <h4 style="font-weight:700; ">Waktu</h4> </th>
                                            <td>    {{ $result->start_jam }} s.d. {{ $result->end_jam }} </td>
                    						</tr>
                    
                    						<tr>
                                            <th>    <h4 style="font-weight:700; ">Pimpinan</h4>  </th>
                                            <td>    {{ $result->pemimpin }} </td>
                    						</tr>
                    
                    						<tr>
                                            <th>    <h4 style="font-weight:700; ">Notulis</h4> </th>
                                             <td>   {{ $result->notulis }} </td>
                    						</tr>
                    
                    						<tr>
                                            <th>    <h4 style="font-weight:700; ">Peserta</h4> </th>
                                             <td>   
                    						 @foreach ($kegiatans as $result)
                    						
                    						<li>{{$result->abc}}</li>
                    						@endforeach  </td>
                    						</tr>
                    						</table>				
                                            
                    									
                    						
                    						
                    									<hr>
                    
                                               <center> <h4 style="font-weight:700; ">Upload Dokumen</h4> </center>
                    							<form action="{{ url('/store_notulen') }}" method="post" enctype="multipart/form-data">
                    								@csrf
                    
                    								@if (session('success'))
                    									<div class="alert alert-success col-sm">
                    										{{ session('success') }}
                    									</div>
                    								@elseif (session('error'))
                    									<div class="alert alert-danger">
                    										{{ session('error') }}
                    									</div>
                    								@endif
                                       <input type="hidden" name="_token" value="{{csrf_token()}}">
                                                    <input type="hidden" name="id" value="{{$id}}">
                    								<div class="form-group">
                    								<b>	Pilih Notulen </b>
                    									<input type="file" name="notulen" id="notulen" class="form-control">
                    								</div>
                    								@error('notulen') <span class="text-danger error">{{ $message }}</span>@enderror
                    
                    
                    								<div class="form-group">
                    								<b>	Pilih Materi </b>
                    									<input type="file" name="materi" id="materi" class="form-control">
                    								</div>
                    								@error('materi') <span class="text-danger error">{{ $message }}</span>@enderror
                    
                    								<div class="form-group">
                                                    <label for ="agenda"><b> Link Materi</b> </label>
                                                    
                                                        <input type="text" name="materi_link" class="form-control" value="{{$result->materi_link }}" placeholder="Link Materi">
                                                   
                                                        @error('materi_link') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                        
                                        
                                        								<div class="form-group">
                                        								<b>	Pilih Foto </b>
                                        									<input type="file" name="foto" id="foto" class="form-control">
                                        								</div>
                                                                        @error('foto') <span class="text-danger error">{{ $message }}</span>@enderror
                                                                        
                                        								<div class="form-group">
                                                    <label for ="agenda"><b> Link Foto</b> </label>
                                                    
                                                        <input type="text" name="foto_link" class="form-control" value="{{$result->foto_link }}" placeholder="Link Foto">
                                                   
                                                        @error('foto_link') <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                                        
                                        								<button type="submit" class="btn btn-primary">Simpan</button>
                                        							
                                        			</form>
                    						   
                                               
                                                @endforeach
                                        
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                     
                    </div>

                  


                </section>

                
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
