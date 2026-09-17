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
                            <h3>Presensi</h3>
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
            <!-- Display status message -->
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <!-- Display validation errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
            
       

<div class="row">


                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Ganti Password</h4>
                                    </div>
                                    <div class="card-body">
                                        
<form action="{{ route('password.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
            <input type="checkbox" id="show_current_password" onclick="togglePassword('current_password')"> Show Password
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
            <input type="checkbox" id="show_new_password" onclick="togglePassword('new_password')"> Show Password
        </div>

        <div class="mb-3">
            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
            <input type="checkbox" id="show_new_password_confirmation" onclick="togglePassword('new_password_confirmation')"> Show Password
        </div>

        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
        

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

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pageLength": 25, // Show 100 rows by default
            "paging": true, // Disable pagination controls
            "info": true, // Disable the table information display
            "order": [[3, 'desc']], // Change 0 to the column index of your date/time column, and 'desc' for descending order
            "lengthChange": true // Disable the option to change the number of rows displayed
        });
    });
</script>


<script>
    function togglePassword(fieldId) {
        var passwordField = document.getElementById(fieldId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
        } else {
            passwordField.type = "password";
        }
    }
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
