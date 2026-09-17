<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kegiatan - Sikeren</title>

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

                        <li class="sidebar-item active ">
                            <a href="{{ url('daftar_kegiatan') }}" class='sidebar-link'>
                                <i class="bi bi-list-ul"></i>
                                <span>Daftar Kegiatan</span>
                            </a>
                        </li>

                        <li class="sidebar-item  ">
                            <a href="{{ url('gantt') }}" class='sidebar-link'>
                                <i class="bi bi-kanban-fill"></i>
                                <span>Tugas Saya</span>
                            </a>
                        </li>

                        <li class="sidebar-title">Notula & BMN</li>

                        <li class="sidebar-item">
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
                            <h3>Detail Kegiatan</h3>
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
                                    <h3 class="text-center">
                                        {{ $kegiatan->text }}
                                    </h3>
                                </div>
                                    <div class="card-body">

                                        <table class="table table-bordered">

                                            <tr>
                                                <th width="25%">Nama Kegiatan</th>
                                                <td>{{ $kegiatan->text }}</td>
                                            </tr>

                                            <tr>
                                                <th>Agenda</th>
                                                <td>{{ $kegiatan->agenda }}</td>
                                            </tr>

                                            <tr>
                                                <th>Tempat</th>
                                                <td>{{ $kegiatan->tempat }}</td>
                                            </tr>

                                            <tr>
                                                <th>Tanggal</th>
                                                <td>
                                                    {{ $kegiatan->start_date }}
                                                    s/d
                                                    {{ $kegiatan->date_akhir }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Dasar Surat</th>
                                                <td>
                                                   @if($kegiatan->surat)
                                                    <a href="{{ $kegiatan->surat }}" target="_blank" class="btn btn-primary btn-sm">
                                                        Lihat Surat
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                                </td>
                                            </tr>

                                        </table>

                                        </table>

                                        <div class="mt-4 d-flex gap-2">
                                            <a href="{{ url('daftar_kegiatan') }}" class="btn btn-secondary">
                                                ← Kembali
                                            </a>
                                            
                                            <!-- Tombol Buat Sub-Kegiatan -->
                                            <a href="{{ url('penugasan?parent_id='.$kegiatan->id) }}" class="btn btn-info text-white">
                                                <i class="bi bi-plus-circle"></i> Buat Sub-Kegiatan
                                            </a>

                                            <!-- Tombol Cetak Undangan -->
                                            <a href="{{ url('/employee/pdf_kegiatan/'.$kegiatan->id) }}" target="_blank" class="btn btn-success">
                                                <i class="bi bi-printer"></i> Cetak Undangan
                                            </a>

                                            <!-- Tombol Approve/Reject untuk Pemimpin Rapat -->
                                            @if(Auth::user()->nama_lengkap == $kegiatan->pemimpin && $kegiatan->status_pemimpin == 'Menunggu')
                                                <form action="{{ route('kegiatan.approve', $kegiatan->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Setujui kegiatan ini?')">
                                                        <i class="bi bi-check-circle"></i> Setujui Rapat
                                                    </button>
                                                </form>
                                                <form action="{{ route('kegiatan.reject', $kegiatan->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="button" class="btn btn-danger" onclick="let alasan = prompt('Alasan penolakan:'); if(alasan) { this.form.insertAdjacentHTML('beforeend', '<input type=\'hidden\' name=\'alasan\' value=\'' + alasan + '\'>'); this.form.submit(); }">
                                                        <i class="bi bi-x-circle"></i> Tolak Rapat
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($kegiatan->status_pemimpin != 'Menunggu' && $kegiatan->status_pemimpin != null)
                                                <span class="badge bg-{{ $kegiatan->status_pemimpin == 'Disetujui' ? 'success' : 'danger' }} p-2 ms-auto">
                                                    Status: {{ $kegiatan->status_pemimpin }}
                                                </span>
                                            @endif
                                        </div>

                                    </div>

                                
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
