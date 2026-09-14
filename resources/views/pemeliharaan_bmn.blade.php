
<!doctype html>
<html lang="en">

<head>
	<title>Sikeren | Lihat Kegiatan</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<!-- VENDOR CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendor/linearicons/style.css') }}">
	<!-- MAIN CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
	<!-- FOR DEMO PURPOSES ONLY. You should remove this in your project -->
	<link rel="stylesheet" href="assets/css/demo.css">
	<!-- GOOGLE FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
	<!-- ICONS -->
	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
	<link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.png">

    <!-- DATATABLES -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    
    <link href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">

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
							<a href="#subPages" data-toggle="collapse" class=""><i class="lnr lnr-file-empty"></i> <span>Buat Kegiatan / Rapat</span> <i class="icon-submenu lnr lnr-chevron-left"></i></a>
							<div id="subPages" class="collapse">
								<ul class="nav">
								<li><a href="penugasan" class=""><i class="lnr lnr-code"></i> <span>Buat Kegiatan</span></a></li>
               					 <li><a href="rapat" class=""><i class="lnr lnr-users"></i> <span>Buat Rapat</span></a></li>
								</ul>
							</div>
						</li>
            

						<li><a href="daftar_kegiatan" class=""><i class="lnr lnr-list"></i> <span>Daftar Kegiatan</span></a></li>
						<li><a href="fullcalender" class=""><i class="lnr lnr-calendar-full"></i> <span>Kegiatan Saya</span></a></li>
            <li><a href="notulis" class=""><i class="lnr lnr-book"></i> <span>Notula</span></a></li>
            <li><a href="bmn" class=""><i class="lnr lnr-laptop-phone"></i> <span>Daftar BMN</span></a></li>
            <li><a href="pemeliharaan_bmn" class="active"><i class="lnr lnr-laptop-phone"></i> <span>Daftar Pemeliharaan BMN</span></a></li>
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
							<h3 class="panel-title">Daftar BMN</h3>
						
						</div>
						<div class="panel-body">


			   
            <div class="table-responsive">
              <!-- Projects table -->
              <table class="table align-items-center table-flush" id="bmn">
                <thead class="thead-light">
                  <tr>
                    
                    
                    
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Status</th>
				
                    
                  </tr>
                </thead>
                <tbody>
                  <tr>
                  @foreach ($bmns as $post)  
              
                    <td>
                    {{ $post->nama_barang }}
                    </td>
                    <td>
                    {{ $post->keterangan }}
                    </td>
                    <td>
                    Pemeriksaan IPDS
                    </td>
					
     
                  </tr>
                  @endforeach
                </tbody>
              </table>

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
	<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
	<script src="{{ asset('assets/scripts/klorofil-common.js') }}"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>	

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script>
    $('#bmn').DataTable({
				
			});

</script>


</body>

</html>
