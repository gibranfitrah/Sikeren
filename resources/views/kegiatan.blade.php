<!doctype html>
<html lang="en">

<head>
	<title>Sikeren | Kegiatan Saya</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<!-- VENDOR CSS -->
	<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/vendor/linearicons/style.css">
	<!-- MAIN CSS -->
	<link rel="stylesheet" href="assets/css/main.css">
	<!-- FOR DEMO PURPOSES ONLY. You should remove this in your project -->
	<link rel="stylesheet" href="assets/css/demo.css">
	<!-- GOOGLE FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
	<!-- ICONS -->
	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
	<link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.png">

</head>

<body>
	<!-- WRAPPER -->
	<div id="wrapper">
		<!-- NAVBAR -->
		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="brand">
			    <img src="{{asset('assets/img/logo-sikeren.png')}}" alt="Syantik Logo" class="img-responsive logo" width="90" height="90"> 
			</div>
			<div class="container-fluid">
				<div class="navbar-btn">
					<button type="button" class="btn-toggle-fullwidth"><i class="lnr lnr-arrow-left-circle"></i></button>
				</div>
				<form class="navbar-form navbar-left">
					<div class="input-group">
				
        </div>
				</form>
				<div class="navbar-btn navbar-btn-right">

				</div>
				<div id="navbar-menu">
					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle icon-menu" data-toggle="dropdown">
								<i class="lnr lnr-alarm"></i>
								<span class="badge bg-danger">5</span>
							</a>
							<ul class="dropdown-menu notifications">
								<li><a href="#" class="notification-item"><span class="dot bg-warning"></span>System space is almost full</a></li>
								<li><a href="#" class="notification-item"><span class="dot bg-danger"></span>You have 9 unfinished tasks</a></li>
								<li><a href="#" class="notification-item"><span class="dot bg-success"></span>Monthly report is available</a></li>
								<li><a href="#" class="notification-item"><span class="dot bg-warning"></span>Weekly meeting in 1 hour</a></li>
								<li><a href="#" class="notification-item"><span class="dot bg-success"></span>Your request has been approved</a></li>
								<li><a href="#" class="more">See all notifications</a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="lnr lnr-question-circle"></i> <span>Help</span> <i class="icon-submenu lnr lnr-chevron-down"></i></a>
							<ul class="dropdown-menu">
								<li><a href="#">Basic Use</a></li>
								<li><a href="#">Working With Data</a></li>
								<li><a href="#">Security</a></li>
								<li><a href="#">Troubleshooting</a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown"> <span>{{ Auth::user()->nama_lengkap }}</span> <i class="icon-submenu lnr lnr-chevron-down"></i></a>
							<ul class="dropdown-menu">
								<li><a href="#"><i class="lnr lnr-user"></i> <span>My Profile</span></a></li>
								<li><a href="#"><i class="lnr lnr-envelope"></i> <span>Message</span></a></li>
								<li><a href="#"><i class="lnr lnr-cog"></i> <span>Settings</span></a></li>
								<li><a href="#"><i class="lnr lnr-exit"></i> <span>Logout</span></a></li>
							</ul>
						</li>
						<!-- <li>
							<a class="update-pro" href="https://www.themeineed.com/downloads/klorofil-pro-bootstrap-admin-dashboard-template/?utm_source=klorofil&utm_medium=template&utm_campaign=KlorofilPro" title="Upgrade to Pro" target="_blank"><i class="fa fa-rocket"></i> <span>UPGRADE TO PRO</span></a>
						</li> -->
					</ul>
				</div>
			</div>
		</nav>
		<!-- END NAVBAR -->
		<!-- LEFT SIDEBAR -->
		<div id="sidebar-nav" class="sidebar">
			<div class="sidebar-scroll">
				<nav>
					<ul class="nav">
						<li><a href="index.html" class=""><i class="lnr lnr-home"></i> <span>Dashboard</span></a></li>

            <li>
							<a href="#subPages" data-toggle="collapse" class="active"><i class="lnr lnr-file-empty"></i> <span>Buat Kegiatan / Rapat</span> <i class="icon-submenu lnr lnr-chevron-left"></i></a>
							<div id="subPages" class="collapse in">
								<ul class="nav">
								<li><a href="penugasan" class="active"><i class="lnr lnr-code"></i> <span>Buat Kegiatan</span></a></li>
                <li><a href="rapat" class=""><i class="lnr lnr-users"></i> <span>Buat Rapat</span></a></li>
								</ul>
							</div>
						</li>
            

						<li><a href="daftar_kegiatan" class=""><i class="lnr lnr-list"></i> <span>Daftar Kegiatan</span></a></li>
						<li><a href="fullcalender" class=""><i class="lnr lnr-calendar-full"></i> <span>Kegiatan Saya</span></a></li>
            <li><a href="notulis" class=""><i class="lnr lnr-book"></i> <span>Notula</span></a></li>
            <li><a href="bmn" class=""><i class="lnr lnr-laptop-phone"></i> <span>Daftar BMN</span></a></li>
						</ul>
				</nav>
			</div>
		</div>
		<!-- END LEFT SIDEBAR -->
		<div class="main">
			<!-- MAIN CONTENT -->
			<div class="main-content">
				<div class="container-fluid">
					<!-- OVERVIEW -->
					<div class="panel panel-headline">
						<div class="panel-heading">
							<h3 class="panel-title">Buat Kegiatan</h3>
						
						</div>
						<div class="panel-body">


            <input id="search" name="search" type="text" class="form-control" placeholder="Search" />

            <form >
            <div class="form-group">
            <label for ="waktu"><b> Waktu Kegiatan</b> </label>
            
                <input type="date" name="start" class="form-control" value="{{$start}}" > <br>
                <input type="date" name="end" class="form-control" value="{{$end}}" > <br>
               
                <input type="submit" value="Pilih">
        </div>

							
         </form >

         <a href="#undanganModal" data-toggle="modal" >Lihat Kegiatan</a> 


            <form action="{{ route('post.store') }}" method="POST">
    @csrf
    @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            @endif
                        
						
     <div class="row">
    
     <input type="hidden" name="jenis"  value="Kegiatan" >
     <input type="hidden" name="tempat"  value="Vicon" >
     <input type="hidden" name="start" class="form-control" value="{{$start}}" > <br>
      <input type="hidden" name="end" class="form-control" value="{{$end}}" > <br>

     <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
            <label for ="title"><b> Topik Kegiatan</b> </label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" placeholder="Topik Kegiatan">
                @error('topik') <span class="text-danger error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
            <label for ="agenda"><b> Agenda Kegiatan</b> </label>
            
                <input type="text" name="agenda" class="form-control" value="{{ old('agenda') }}" placeholder="Agenda Kegiatan">
           
                @error('agenda') <span class="text-danger error">{{ $message }}</span>@enderror
            </div>
        </div>

        
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
            <label for ="waktu"><b> Waktu Kegiatan</b> </label>
            
                
                
                Jam Mulai  &nbsp<input type="time" name="start_jam"  value="{{ old('start_jam') }}" >  &nbsp  &nbsp
                Jam Akhir  &nbsp<input type="time" name="end_jam"  value="{{ old('end_jam') }}" >
                @error('waktu') <span class="text-danger error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
            <label for ="pemimpin"><b> Pemimpin Kegiatan</b> </label> <br>
            <select class="js-example-basic-single form-control" name="pemimpin">
            @foreach ($peserta as $pesertas)
            
      <option>{{$pesertas->nama_lengkap}}</option>
      @endforeach
            </select>
                @error('pemimpin') <span class="text-danger error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
            <label for ="agenda"><b> Notulis Kegiatan</b> </label> <br>
            <select class="js-example-basic-single form-control" name="notulis" id="ddlModels"> 
            <option>--</option>
            @foreach ($peserta as $pesertas)
            
            <option>{{$pesertas->nama_lengkap}}</option>
            @endforeach
            </select>
                @error('notulis') <span class="text-danger error">{{ $message }}</span>@enderror
            </div>
        </div>

		


        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
            <label for ="agenda"><b> Peserta Kegiatan</b> </label>
                
            <div class="cover">

    <div class="form-group">
      <label for="state">Fungsi:</label>
   <select id="state" name="category_id" class="form-control">
        <option value="" selected disabled>Pilih Fungsi</option>
         @foreach($category as $key => $country)
         <option value="{{$key}}"> {{$country}}</option>
         @endforeach
         </select>
    </div>
    
 
      
</div>
<label for="city">Calon Peserta:</label><br>
      <select name="city" id="city" multiple="multiple"></select>
    
    
     <input id="moveright"  type="button" value="  >  "/>
     <input id="moveleft"  type="button" value="  <  "/>
        

   
    
    <select id="peserta" multiple="multiple" name="peserta[]">
     
    </select>
  </div>



                @error('peserta') <span class="text-danger error">{{ $message }}</span>@enderror
            </div>
        </div>
        
        <div class="col-xs-12 col-sm-12 col-md-12 text-right">
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </div>
 
</form>



<div class="modal fade" id="undanganModal" >
                        <div class="modal-dialog" role="document">
                           
                                <div class="modal-content">
								<div class="modal-header"  style="background-color: #006fcc; color:#FFF;">
														  <h3 class="modal-title" id="tambahlabel">Kegiatan {{$start}} s.d. {{$end}} </h3>  
														</div>
                            
                                <div class="modal-body">
							

								
								</div>
								
                                 <div class="modal-footer">
                                     <a class="btn btn-secondary" data-dismiss="modal"> Kembali</a>
                              
                                     <input type="submit" class="btn btn-primary" style="border-radius: 5px;"></button>
                                
                                 </div>
								  
                            </div>
                           </form>
                        </div>
                    </div>  

							
							
						</div>
					</div>
					<!-- END OVERVIEW -->
					
						
						
					</div>
				</div>
			</div>
			<!-- END MAIN CONTENT -->
		</div>
		<!-- END MAIN -->
		<div class="clearfix"></div>
		<footer>
			<div class="container-fluid">
				<p class="copyright">&copy; 2017 <a href="https://www.themeineed.com" target="_blank">Theme I Need</a>. All Rights Reserved.</p>
			</div>
		</footer>
	</div>
	<!-- END WRAPPER -->
	<!-- Javascript -->
	<script src="assets/vendor/jquery/jquery.min.js"></script>
	<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js"></script>
	<script src="assets/scripts/klorofil-common.js"></script>

	
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  
  <script src="{{asset('assets/ckeditor/ckeditor.js')}}"></script>
<script>
   var topik = document.getElementById("topik_ck");
   CKEDITOR.replace( 'topik_ck' );
   CKEDITOR.config.allowedContent = true;
</script>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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


</body>

</html>
