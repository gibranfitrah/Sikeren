<!doctype html>
<html lang="en">

<head>
	<title>Konsultasi | Syantik - Sistem Pelayanan Pembinaan Statistik Sektoral</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<!-- VENDOR CSS -->
	<link rel="stylesheet" href="{{asset('admin/assets/vendor/bootstrap/css/bootstrap.min.css')}}">
	<link rel="stylesheet" href="{{asset('admin/assets/vendor/font-awesome/css/font-awesome.min.css')}}">
	<link rel="stylesheet" href="{{asset('admin/assets/vendor/linearicons/style.css')}}">

	<!-- MAIN CSS -->
	<link rel="stylesheet" href="{{asset('admin/assets/css/main.css')}}">
	<link rel="stylesheet" href="{{asset('admin/assets/css/paneltab.css')}}">
	<!-- FOR DEMO PURPOSES ONLY. You should remove this in your project -->
	<link rel="stylesheet" href="{{asset('admin/assets/css/demo.css')}}">
	<!-- GOOGLE FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
	<!-- ICONS -->
	<link rel="apple-touch-icon" sizes="76x76" href="{{asset('admin/assets/img/apple-icon.png')}}">
	<link rel="icon" type="image/png" sizes="96x96" href="{{asset('admin/assets/img/favicon.png')}}">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
	



</head>

<body>
	<!-- WRAPPER -->
	<div id="wrapper">
		<!-- NAVBAR -->
		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="brand">
				<a href="/syantik/" style="font-size: 12px;"><img src="{{asset('admin/assets/img/logo-dark.svg')}}" alt="Syantik Logo" class="img-responsive logo"><span>Sistem Informasi Layanan Statistik</span></a>
			</div>
			<div class="container-fluid">
				<div class="navbar-btn">
					<button type="button" class="btn-toggle-fullwidth"><i class="lnr lnr-chevron-left-circle"></i></button>
				</div>
				
					<div class="navbar-btn navbar-btn-right">
					    <a  href="/syantik/beranda" title="Konsultasi" target="_blank"> <span style="font-size:20px;">Beranda</span></a>
				 &nbsp &nbsp &nbsp
				 <a  href="/syantik/data_opd/DINAS%20PENDIDIKAN%20DAN%20KEBUDAYAAN" title="Konsultasi" target="_blank"> <span style="font-size:20px;">Data OPD</span></a>
				 &nbsp &nbsp &nbsp
					     <a  href="/syantik/view_metadata_kegiatan" title="Konsultasi" target="_blank"> <span style="font-size:20px;">Metadata</span></a>
				 &nbsp &nbsp &nbsp
					    <a  href="/syantik/konsultasi" title="Konsultasi" target="_blank"> <span style="font-size:20px;">Konsultasi</span></a>
				 &nbsp &nbsp &nbsp
					    <a  href="https://romantik.bps.go.id/" title="Ajukan Rekomendasi" target="_blank"> <span style="font-size:20px;">Ajukan Rekomendasi</span></a>
				 &nbsp &nbsp &nbsp
					    <a  href="/syantik/modul" title="Modul Pembelajaran" target="_blank"> <spa style="font-size:20px;"n>Modul Pembelajaran</span></a>
				 &nbsp &nbsp &nbsp
						<a  href="/syantik/login" title="Login" target="_blank"> <span style="font-size:20px;">Log In</span> <i class="fa fa-sign-in"></i></a>
				
				</div> 
				
			
			
		
				
					<div id="navbar-menu">
					<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
							<ul class="dropdown-menu">
						    
								<li><a href="{{ route('logout') }}"><i class="lnr lnr-exit"></i> <span>Logout</span></a></li>
							
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
					    
					    
					    
				
					</ul>
				</nav>
			</div>
		</div>
		<!-- END LEFT SIDEBAR -->
		<!-- MAIN -->
		<div class="main">
			<!-- MAIN CONTENT -->
			<div class="main-content">
		


						<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#home">Halaman Awal</a></li>
    <li><a data-toggle="pill" href="#blok1">Blok I</a></li>
    <li><a data-toggle="pill" href="#blok2">Blok II</a></li>
    <li><a data-toggle="pill" href="#blok3">Blok III</a></li>
    <li><a data-toggle="pill" href="#blok4">Blok IV</a></li>
    <li id="Survei" style="display: none;" ><a data-toggle="pill" href="#blok5">Blok V</a></li>
    <li><a data-toggle="pill" href="#blok6">Blok VI</a></li>
    <li><a data-toggle="pill" href="#blok7">Blok VII</a></li>
    <li><a data-toggle="pill" href="#blok8">Blok VIII</a></li>
  

  </ul>
      <form class="was-validated" method="post" action="{{ route('metadata.simpan') }}" enctype="multipart/form-data">
                            @csrf
                            @if (session('success2'))
                            <div class="alert alert-success">
                                {{ session('success2') }}
                            </div>
                            @endif
                            
  	 <div class="tab-content">
  <div id="blok3" class="tab-pane fade">
  <center>   <h3>III. PERENCANAAN DAN PERSIAPAN</h3> </center> 
                            <div class="panel-footer">
                                    
                                <div class="row">
            						    
                                        <div class="col-md-12">
            					            <strong>3.1 Latar Belakang Kegiatan: </strong>
            					            <textarea class="form-control" style="height:100px" name="b3r1" placeholder="Tuliskan Secara Jelas Latar Belakang Kegiatan" required>{{ old('b3r1') }}</textarea>
            					           <div class="valid-feedback">Valid.</div>
      <div class="invalid-feedback">Please fill out this field.</div>
            							   </div>
            							   
            						</div>
            						
            						<br> <br>	    
                                   
                                 <div class="row">
            						    
                                        <div class="col-md-12">
            					            <strong>3.2 Tujuan Kegiatan: </strong>
            					            <textarea class="form-control" style="height:100px" name="b3r2" placeholder="Tuliskan Secara Jelas Tujuan Kegiatan">{{ old('b3r2') }}</textarea>
            					           
            							   </div>
            							   
            						</div>
            						
            						<br> <br>   
            					
            					<div class="row">
            						    
                                        <div class="col-md-12">
            					            <strong>3.3 Rencana Jadwal Kegiatan: </strong>
            					           <table class="table table-striped" width="100%"> 
            					           <tr>
            					               <th colspan="2"> </th>
            					               <th>Tanggal Mulai</th>
            					               <th>Tanggal Selesai</th>
            					           </tr>
            					           <tr>
            					               <td colspan="4"> <strong> A. Perencanaan </strong></td>
            					           </tr>
            					           
            					           <tr>
            					               <td></td>
            					               <td>1. Perencanaan Kegiatan</td>
            					               <td> <input type="date" id="b3r3a1k1" name="b3r3a1k1" class="form-control" value="{{ old('b3r3a1k1') }}" placeholder="Isikan Tanggal Mulai Perencanaan">  </td>
            					               <td> <input type="date" id="b3r3a1k2" name="b3r3a1k2" class="form-control" value="{{ old('b3r3a1k2') }}" placeholder="Isikan Tanggal Selesai Perencanaan">  </td>
            					           </tr>
            					           
            					           <tr>
            					               <td></td>
            					               <td>2. Desainn</td>
            					               <td> <input type="date" id="b3r3a2k1" name="b3r3a2k1" class="form-control" value="{{ old('b3r3a2k1') }}" placeholder="Isikan Tanggal Mulai Desain">  </td>
            					               <td> <input type="date" id="b3r3a2k2" name="b3r3a2k2" class="form-control" value="{{ old('b3r3a2k2') }}" placeholder="Isikan Tanggal Selesai Desain">  </td>
            					           </tr>
            					           
            					           <tr>
            					               <td colspan="4"> <strong> B. Pengumpulan </strong></td>
            					           </tr>
            					           
            					           <tr>
            					               <td></td>
            					               <td>3. Pengumpulan Data</td>
            					               <td> <input type="date" id="b3r3b1k1" name="b3r3b1k1" class="form-control" value="{{ old('b3r3b1k1') }}" placeholder="Isikan Tanggal Mulai Pengumpulan Data">  </td>
            					               <td> <input type="date" id="b3r3b1k2" name="b3r3b1k2" class="form-control" value="{{ old('b3r3b1k2') }}" placeholder="Isikan Tanggal Selesai Pengumpulan Data">  </td>
            					           </tr>
            					           
            					           <tr>
            					               <td colspan="4"> <strong> C. Pemeriksaan </strong></td>
            					           </tr>
            					           
            					           <tr>
            					               <td></td>
            					               <td>4. Pengolahan Data</td>
            					               <td> <input type="date" id="b3r3c1k1" name="b3r3c1k1" class="form-control" value="{{ old('b3r3c1k1') }}" placeholder="Isikan Tanggal Mulai Pengolahan Data">  </td>
            					               <td> <input type="date" id="b3r3c1k2" name="b3r3c1k2" class="form-control" value="{{ old('b3r3c1k2') }}" placeholder="Isikan Tanggal Selesai Pengolahan Data">  </td>
            					           </tr>
            					           
            					           
            					            <tr>
            					               <td colspan="4"> <strong> D. Penyebarluasan </strong></td>
            					           </tr>
            					           
            					           <tr>
            					               <td></td>
            					               <td>5. Analisis</td>
            					               <td> <input type="date" id="b3r3d1k1" name="b3r3d1k1" class="form-control" value="{{ old('b3r3d1k1') }}" placeholder="Isikan Tanggal Mulai Analisis">  </td>
            					               <td> <input type="date" id="b3r3d1k2" name="b3r3d1k2" class="form-control" value="{{ old('b3r3d1k2') }}" placeholder="Isikan Tanggal Selesai Analisis">  </td>
            					           </tr>
            					           
            					            <tr>
            					               <td></td>
            					               <td>6. Diseminasi Hasil</td>
            					               <td> <input type="date" id="b3r3d2k1" name="b3r3d2k1" class="form-control" value="{{ old('b3r3d2k1') }}" placeholder="Isikan Tanggal Mulai Diseminasi">  </td>
            					               <td> <input type="date" id="b3r3d2k2" name="b3r3d2k2" class="form-control" value="{{ old('b3r3d2k2') }}" placeholder="Isikan Tanggal Selesai Diseminasi">  </td>
            					           </tr>
            					           
            					           <tr>
            					               <td></td>
            					               <td>7. Evaluasi</td>
            					               <td> <input type="date" id="b3r3d3k1" name="b3r3d3k1" class="form-control" value="{{ old('b3r3d3k1') }}" placeholder="Isikan Tanggal Mulai Evaluasi">  </td>
            					               <td> <input type="date" id="b3r3d3k2" name="b3r3d3k2" class="form-control" value="{{ old('b3r3d3k2') }}" placeholder="Isikan Tanggal Selesai Evaluasi">  </td>
            					           </tr>
            					           
            					           
            					           </table>
            							   </div>
            							   
            						</div>
            						
            						<br>  <br>
            						
            						
            					
            					            <strong>3.4 Variabel Karateristik Yang Dikumpulkan: </strong>
            					            <table class="table table-bordered" id="dynamicAddRemove2">
                 <tr>
            					               <th>No </th>
            					               <th>Nama Variabel (Karateristik)</th>
            					               <th>Konsep</th>
            					               <th>Definisi</th>
            					               <th>Referensi Waktu (Periode Enumerasi)</th>
            					               <th>Aksi</th>
            					           </tr>


                    
                    <tr>
                        
                    <td>1</td>    
                    <td>
                       <textarea class="form-control" style="height:80px" name="b3r34k1[0]" placeholder="Tuliskan Nama Variabel"></textarea>
                    </td>



                    <td> <textarea class="form-control" style="height:80px" name="b3r34k2[0]" placeholder="Tuliskan Konsep Variabel"></textarea> </td>
            					               <td> <textarea class="form-control" style="height:80px" name="b3r34k3[0]" placeholder="Tuliskan Definisi"></textarea> </td>
            					               <td> <textarea class="form-control" style="height:80px" name="b3r34k4[0]" placeholder="Tuliskan Referensi Waktu"></textarea> </td>
            					               <td> <button type="button" name="add" id="dynamic-ar2" class="btn btn-outline-primary"><i class="fa-solid fa-plus"></i></button></td>
                </tr>
            </table>
            					
            						
            						<br>  <br> 		
            				
            							        
								</div>
								<div class="panel-footer">
									<div class="row">
								
										
									</div>
								</div>
    </div>
    
<!-- AKHIR BLOK III -->

									
									
    <div id="home" class="tab-pane fade in active">
      <h3>Halaman Awal</h3>
                            <div class="panel-footer">
                                
                            
                            
                           
                            
                                    
                                    <div class="row">
                                        <div class="col-md-10">
            					            <strong>Judul Kegiatan: </strong>
            					            <input type="text" id="b0r1" name="b0r1" class="form-control" value="{{ old('b0r1') }}" placeholder="Isikan judul kegiatan statistik yang dilakukan">
            							   </div>
            							   
            							    <div class="col-md-2">
            							   <strong>Tahun:</strong>
            					            <input type="text" id="b0r2" name="b0r2" class="form-control" value="{{ old('b0r2') }}" placeholder="Isikan tahun kegiatan">
            							   </div>
            						</div>
            						<br> <br>
            						
            						<div class="row">
                                        <div class="col-md-12">
            					            <strong>Cara Pengumpulan Data:</strong>
            					            <select class="select2 form-control" name="b0r3" id="b0r3" value="{{ old('b0r3') }}" >
            					              
            					                <option value="Pencacahan Lengkap">Pencacahan Lengkap</option>
            					                <option value="Survei">Survei</option>
            					                <option value="Kompilasi Produk Administrasi">Kompilasi Produk Administrasi</option>
            					                <option value="Cara Lain Sesuai Dengan Perkembangan TI">Cara Lain Sesuai Dengan Perkembangan TI</option>
            					           </select>
            							   </div>
            							   
            							   
            						</div>
            						
            								<br> <br>
            						
            						<div class="row">
                                        <div class="col-md-12">
            					            <strong>Sektor Kegiatan:</strong>
            					            <select class="form-control select21" name="b0r4" value="{{ old('b0r4') }}" >
            					              
            					                <option value="Pertanian dan Perikanan">Pertanian dan Perikanan</option>
            					                <option value="Demografi dan Kependudukan">Demografi dan Kependudukan</option>
            					                <option value="Pembangunan">Pembangunan</option>
            					                <option value="Proyeksi Ekonomi">Proyeksi Ekonomi</option>
            					                <option value="Pendidikan dan Pelatihan">Pendidikan dan Pelatihan</option>
            					                <option value="Lingkungan">Lingkungan</option>
            					                <option value="Keuangan">Keuangan</option>
            					                <option value="Globalisasi">Globalisasi</option>
            					                <option value="Kesehatan">Kesehatan</option>
            					                <option value="Industri dan Jasa">Industri dan Jasa</option>
            					                <option value="Teknologi Informasi dan Komunikasi">Teknologi Informasi dan Komunikasi</option>
            					                <option value="Perdagangan Internasional dan Neraca Perdagangan">Perdagangan Internasional dan Neraca Perdagangan</option>
            					                <option value="Ketenagakerjaan">Ketenagakerjaan</option>
            					                <option value="Neraca Nasional">Neraca Nasional</option>
            					                <option value="Indikator Ekonomi Bulanan">Indikator Ekonomi Bulanan</option>
            					                <option value="Produktivitas">Produktivitas</option>
            					                <option value="Harga dan Paritas Daya Beli">Harga dan Paritas Daya Beli</option>
            					                <option value="Sektor Publik Perpajakan dan Regulasi Pasar">Sektor Publik Perpajakan dan Regulasi Pasar</option>
            					                <option value="Perwilayahan dan Perkotaan">Perwilayahan dan Perkotaan</option>
            					                <option value="Ilmu Pengetahuan dan Hak Paten">Ilmu Pengetahuan dan Hak Paten</option>
            					                <option value="Perlindungan Sosial dan Kesejahteraan">Perlindungan Sosial dan Kesejahteraan</option>
            					                <option value="Transportasi">Transportasi</option>
            					           </select>
            							   </div>
            							   
            							   
            						</div>
            						<br> <br>
            						
            						
            							        
								</div>
								
								
								<div class="panel-footer">
									<div class="row">
							
								
									</div>
								</div>
    </div>
    
    
    
    <div id="blok1" class="tab-pane fade">
  <center>   <h3>I. PENYELENGGARA</h3> </center> 
                            <div class="panel-footer">
                                    
                                    
                                     <div class="row">
                                        <div class="col-md-12">
            					            <strong>1.1 Instansi Penyelenggara: </strong>
            					            <input type="text" id="b1r1" name="b1r1" class="form-control" value="{{ old('b1r1') }}" placeholder="Nama Instansi Penyelenggara">
            							   </div>
            						</div>
            						
            						<br> <br>
            						
            						  <div class="row">
                                        <div class="col-md-12">
            					            <strong>1.2 Alamat Instansi Penyelenggara: </strong>
            					            <input type="text" id="b1r2" name="b1r2" class="form-control" value="{{ old('b1r2') }}" placeholder="Alamat Instansi Penyelenggara">
            							   </div>
            							   
            							 
            						</div>
            						<br> <br>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
            					            <strong>Telepon: </strong>
            					            <input type="text" id="b1r3" name="b1r3" class="form-control" value="{{ old('b1r3') }}" placeholder="Telepon Instansi Penyelenggara">
            							   </div>
            							   
            							    <div class="col-md-4">
            							   <strong>Faksmile:</strong>
            					            <input type="text" id="b1r4" name="b1r4" class="form-control" value="{{ old('b1r4') }}" placeholder="Faksmile Instansi Penyelenggara">
            							   </div>
            							   
            							   <div class="col-md-4">
            							   <strong>Email:</strong>
            					            <input type="text" id="b1r5" name="b1r5" class="form-control" value="{{ old('b1r5') }}" placeholder="Email Instansi Penyelenggara">
            							   </div>
            						</div>
            						<br> <br>
            						
            				
            							        
								</div>
								<div class="panel-footer">
									<div class="row">
							
										
									</div>
								</div>
    </div>
    <div id="blok2" class="tab-pane fade">
      <center>   <h3>II. PENANGGUNG JAWAB</h3> </center> 
      <div class="panel-footer">
								    
							 <div class="row">
                                        <div class="col-md-12">
            					            <strong>2.1 Unit Eselon Penanggung Jawab: </strong>
            					            
            							   </div>
            						</div>
            						<br>
            						<div class="row">
            						    
                                        <div class="col-md-6">
            					            <strong>Eselon 1: </strong>
            					            <input type="text" id="b2r1" name="b2r1" class="form-control" value="{{ old('b2r1') }}" placeholder="Tuliskan Nama Penanggung Jawab Setingkat Eselon 1">
            							   </div>
            							   <div class="col-md-6">
            					            <strong>Eselon 2: </strong>
            					            <input type="text" id="b2r2" name="b2r2" class="form-control" value="{{ old('b2r2') }}" placeholder="Tuliskan Nama Penanggung Jawab Setingkat Eselon 2">
            							   </div>
            							   
            						</div>
            						
            						<br> <br>
            						
            						
						  <div class="row">
                                        <div class="col-md-12">
            					            <strong>2.2 Penanggung Jawab Teknis (Setingkat Eselon 3): </strong>
            					            
            							   </div>
            						</div>
            						<br>
            						
            				<div class="row">
            						    
                                        <div class="col-md-12">
            					            <strong>Nama: </strong>
            					            <input type="text" id="b2r3" name="b2r3" class="form-control" value="{{ old('b2r3') }}" placeholder="Tuliskan Nama Penanggung Jawab Teknis">
            							   </div>
            							   
            						</div>
            						
            						<br> 	
            					<div class="row">
            						    
                                        <div class="col-md-12">
            					            <strong>Jabatan: </strong>
            					            <input type="text" id="b2r4" name="b2r4" class="form-control" value="{{ old('b2r4') }}" placeholder="Tuliskan Jabatan Penanggung Jawab Teknis">
            							   </div>
            							   
            						</div>
            						
            						<br> 
            						
            				<div class="row">
            						    
                                        <div class="col-md-12">
            					            <strong>Alamat: </strong>
            					            <input type="text" id="b2r5" name="b2r5" class="form-control" value="{{ old('b2r5') }}" placeholder="Tuliskan Alamat Penanggung Jawab Teknis">
            							   </div>
            							   
            						</div>
            						
            						<br> 					
            				 <div class="row">
                                        <div class="col-md-4">
            					            <strong>Telepon: </strong>
            					            <input type="text" id="b2r6" name="b2r6" class="form-control" value="{{ old('b2r6') }}" placeholder="Telepon Penanggung Jawab Teknis">
            							   </div>
            							   
            							    <div class="col-md-4">
            							   <strong>Faksmile:</strong>
            					            <input type="text" id="b2r7" name="b2r7" class="form-control" value="{{ old('b2r7') }}" placeholder="Faksmile Penanggung Jawab Teknis">
            							   </div>
            							   
            							   <div class="col-md-4">
            							   <strong>Email:</strong>
            					            <input type="text" id="b2r8" name="b2r8" class="form-control" value="{{ old('b2r8') }}" placeholder="Email Penanggung Jawab Teknis">
            							   </div>
            						</div>
            						<br> <br>
            						
            						
            									
								</div>
								<div class="panel-footer">
									<div class="row">
								
				
									</div>
								</div>
    </div>


        

 <div id="blok4" class="tab-pane fade">
  <center>   <h3>IV. DESAIN KEGIATAN</h3> </center> 
                            <div class="panel-footer">
                                    
                                    
                
            						
            					 <div class="row">
                                        <div class="col-md-12">
                                            <strong>4.1 Kegiatan Ini Dilakukan: </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="sekali" name="b4r1" value="Hanya Sekali">
                                                <label for="sekali">Hanya Sekali</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="berulang" name="b4r1" value="Berulang">
                                                <label for="berulang">Berulang</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br> 
            			
            						
            					 <div class="row">
            						     
                                        <div id="Berulang" style="display: none;" class="col-md-12 desc">
                                            <strong>4.2 Frekuensi Penyelenggaraan: </strong>
            					            <table>
            					               <tr> 
            					               <td> <input type="radio" id="harian" name="b4r2" value="Harian">
                                            <label for="harian">Harian</label> &nbsp &nbsp </td>
            					            
            					            <td> 
                                            <input type="radio" id="mingguan" name="b4r2" value="Mingguan">
                                            <label for="mingguan">Mingguan</label> &nbsp &nbsp
                                            </td>
            					            
            					            <td> 
                                            <input type="radio" id="bulanan" name="b4r2" value="Bulanan">
                                            <label for="bulanan">Bulanan</label> &nbsp &nbsp
                                            </td>
                                            <td> 
                                            <input type="radio" id="triwulanan" name="b4r2" value="Triwulanan">
                                            <label for="triwulanan">Triwulanan</label> &nbsp &nbsp
                                            </td>
                                            <td> 
                                            <input type="radio" id="empatbulanan" name="b4r2" value="Empat Bulanan">
                                            <label for="empatbulanan">Empat Bulanan</label> &nbsp &nbsp
                                            </td>
                                            <td> 
                                            <input type="radio" id="semesteran" name="b4r2" value="Semesteran">
                                            <label for="semesteran">Semesteran</label> &nbsp &nbsp
                                            </td>
                                            <td> 
                                            <input type="radio" id="tahunan" name="b4r2" value="Tahunan">
                                            <label for="tahunan">Tahunan</label> &nbsp &nbsp
                                            </td>
                                            <td> 
                                            <input type="radio" id="lebihduatahun" name="b4r2" value="Lebih Dari Dua Tahunan">
                                            <label for="lebihduatahun">Lebih Dari Dua Tahunan</label> &nbsp &nbsp
                                            </td>
            					               </tr>  
            					        
                                            
                                            </table>
                                            <br>
            							   </div>
            							   
            							
            							   
            						</div>
            				
            					
            						
            						<div class="row">
                                        <div class="col-md-12">
                                            <strong>4.3 Tipe Pengumpulan Data: </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="panel" name="b4r3" value="Longitudinal Panel">
                                                <label for="panel">Longitudinal Panel</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="cross" name="b4r3" value="Longitudinal Cross Sectional">
                                                <label for="cross">Longitudinal Cross Sectional</label> &nbsp &nbsp
                                                </td>
                                                <td> 
                                                <input type="radio" id="cross-sectional" name="b4r3" value="Cross Sectional">
                                                <label for="cross-sectional">Cross Sectional</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br> 
            				
            				        <div class="row">
                                        <div class="col-md-12">
                                            <strong>4.4 Cakupan Wilayah Pengumpulan Data: </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="seluruh-indo" name="b4r4" value="Seluruh">
                                                <label for="seluruh-indo">Seluruh Wilayah Indonesia</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="sebagian-indo" name="b4r4" value="Sebagian">
                                                <label for="sebagian-indo">Sebagian Wilayah Indonesia</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br>
            						
            						 <div class="row">
            						     
                                        <div id="Sebagian" style="display: none;" class="col-md-12 desc2">
                                            <strong>4.5 Wilayah Kegiatan: </strong> <br>
            					                 <select class="form-control select22" style="width:100%" name="b4r5[]" value="{{ old('b0r5') }}" multiple >
            					              
            					                <option value="Buton">Buton</option>
            					                <option value="Muna">Muna</option>
            					                <option value="Konawe">Konawe</option>
            					                <option value="Kolaka">Kolaka</option>
            					                <option value="Konawe Selatan">Konawe Selatan</option>
            					                <option value="Bombana">Bombana</option>
            					                <option value="Wakatobi">Wakatobi</option>
            					                <option value="Kolaka Utara">Kolaka Utara</option>
            					                <option value="Buton Utara">Buton Utara</option>
            					                <option value="Konawe Utara">Konawe Utara</option>
            					                <option value="Kolaka Timur">Kolaka Timur</option>
            					                <option value="Konawe Kepulauan">Konawe Kepulauan</option>
            					                <option value="Muna Barat">Muna Barat</option>
            					                <option value="Buton Tengah">Buton Tengah</option>
            					                <option value="Buton Selatan">Buton Selatan</option>
            					                <option value="Kota Kendari">Kota Kendari</option>
            					                <option value="Kota Baubau">Kota Baubau</option>
            					   
            					           </select>
                                            <br>
                                            <br>
            							   </div>
            						   
            						</div>
            						 
            						
            							 <div class="row">
            						     
                                        <div class="col-md-12">
                                            <strong>4.6 Metode Pengumpulan Data: </strong> <br>
            					                <label><input  type="checkbox" name="b4r6[]" value="Wawancara"> Wawancara</label><br>
                                                <label><input type="checkbox" name="b4r6[]" value="Mengisi Kuesioner Sendiri"> Mengisi Kuesioner Sendiri</label><br>
                                                <label><input type="checkbox" name="b4r6[]" value="Pengamatan"> Pengamatan</label><br>
                                                <label><input type="checkbox" name="b4r6[]" value="Pengumpulan Data Sekunder"> Pengumpulan Data Sekunder</label><br>
                                                <label><input type="checkbox" id="check" onclick="myFunction()"> Lainnya</label><br>
                                               
                                                <input type = "text" name="b4r6[]" id="lainnya" style="display: none;" class="form-control" placeholder ="Tuliskan Metode Pengumpulan Data Lainnya">
                                                
                                            <br>
            							   </div>
            						   
            						</div>
            						
            						
            							 <div class="row">
            						     
                                        <div class="col-md-12">
                                            <strong>4.7 Sarana Pengumpulan Data: </strong> <br>
            					                <label><input  type="checkbox" name="b4r7[]" value="Paper-assisted Personal Interviewing (PAPI)"> Paper-assisted Personal Interviewing (PAPI)</label><br>
                                                <label><input type="checkbox" name="b4r7[]" value="Computer-assisted Personal Interviewing (CAPI)"> Computer-assisted Personal Interviewing (CAPI)</label><br>
                                                <label><input type="checkbox" name="b4r7[]" value="Computer-assisted Telephones Interviewing (CATI)"> Computer-assisted Telephones Interviewing (CATI)</label><br>
                                                <label><input type="checkbox" name="b4r7[]" value="Computer Aided Web Interviewing (CAWI)"> Computer Aided Web Interviewing (CAWI)</label><br>
                                                <label><input type="checkbox" name="b4r7[]" value="Mail"> Mail</label><br>
                                                <label><input type="checkbox" id="check-sarana" onclick="cekSarana()"> Lainnya</label><br>
                                               
                                                <input type = "text" name="b4r7[]" id="lainnya-sarana" style="display: none;" class="form-control" placeholder ="Tuliskan Sarana Pengumpulan Data Lainnya">
                                                
                                            <br>
            							   </div>
            						   
            						</div>
            						
            						<div class="row">
            						     
                                        <div class="col-md-12">
                                            <strong>4.8 Unit Pengumpulan Data: </strong> <br>
            					                <label><input  type="checkbox" name="b4r8[]" value="Individu">Individu</label><br>
                                                <label><input type="checkbox" name="b4r8[]" value="Rumah Tangga">Rumah Tangga</label><br>
                                                <label><input type="checkbox" name="b4r8[]" value="Usaha/Perusahaan">Usaha/Perusahaan</label><br>
                                                <label><input type="checkbox" id="check-unit" onclick="cekUnit()"> Lainnya</label><br>
                                               
                                                <input type = "text" name="b4r8[]" id="lainnya-unit" style="display: none;" class="form-control" placeholder ="Tuliskan Unit Pengumpulan Data Lainnya">
                                                
                                            <br>
            							   </div>
            						   
            						</div>
            						
            							        
								</div>
								<div class="panel-footer">
									<div class="row">
									
										
									</div>
								</div>
    </div>
    
    <!-- AKHIR BLOK IV -->
    
    <div id="blok5" class="tab-pane fade">
  <center>   <h3>V. DESAIN SAMPEL</h3> </center>
  <center>   Diisi Jika Cara Pengumpulan Data adalah Survei </center> 
                            <div class="panel-footer">
                                    
                                 <div class="row">
                                        <div class="col-md-12">
                                            <strong>5.1 Jenis Rancangan Sampel: </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="single-stage" name="b5r1" value="Single Stage Atau Phase">
                                                <label for="single-stage">Single Stage Atau Phase</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="multi-stage" name="b5r1" value="Multi Stage Atau Phase">
                                                <label for="multi-stage">Multi Stage Atau Phase</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br> 
            						
            						<div class="row">
                                        <div class="col-md-12">
                                            <strong>5.2 Metode Pemilihan Sampel Tahap Terakhir: </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="probabilitas" name="b5r2" value="Probabilitas">
                                                <label for="probabilitas">Sampel Probabilitas</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="nonprobabilitas" name="b5r2" value="NonProbabilitas">
                                                <label for="nonprobabilitas">Sampel Nonprobabilitas</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br> 
            						
            						 <div class="row">
            						     
                                        <div id="NonProbabilitas" style="display: none;" class="col-md-12 desc3">
                                            <strong>5.3 Metode Yang Digunakan: </strong><br>
            					                <input type="radio" id="quota" name="b5r3" value="Quota Sampling">
                                                <label for="quota">Quota Sampling</label><br>
                                                <input type="radio" id="accidental" name="b5r3" value="Accidental Sampling">
                                                <label for="accidental">Accidental Sampling</label> <br>
                                                <input type="radio" id="purposive" name="b5r3" value="Purposive Sampling">
                                                <label for="purposive">Purposive Sampling</label> <br>
                                                <input type="radio" id="snowball" name="b5r3" value="Snowball Sampling">
                                                <label for="snowball">Snowball Sampling</label> <br>
                                                <input type="radio" id="saturation" name="b5r3" value="Saturation Sampling">
                                                <label for="saturation">Saturation Sampling</label> <br>
                                            <br>
            							   </div>
            						</div>
            						
            						<div class="row">
            						     
                                        <div id="Probabilitas" style="display: none;" class="col-md-12 desc3">
                                            <strong>5.3 Metode Yang Digunakan: </strong><br>
            					                <input type="radio" id="simple" name="b5r3" value="Simple Random Sampling">
                                                <label for="simple">Simple Random Sampling</label><br>
                                                <input type="radio" id="systematic" name="b5r3" value="Systematic Random Sampling">
                                                <label for="systematic">Systematic Random Sampling</label> <br>
                                                <input type="radio" id="stratified" name="b5r3" value="Stratified Random Sampling">
                                                <label for="stratified">Stratified Random Sampling</label> <br>
                                                <input type="radio" id="cluster" name="b5r3" value="Cluster Sampling">
                                                <label for="cluster">Cluster Sampling</label> <br>
                                                <input type="radio" id="probability" name="b5r3" value="Probabilty Proportional to Size Sampling">
                                                <label for="probability">Probabilty Proportional to Size Sampling</label> <br>
                                            <br>
                                            
                                             <strong>5.4 Kerangka Sampel Terakhir: </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="list" name="b5r4" value="List Frame">
                                                <label for="list">List Frame</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="area" name="b5r4" value="Area Frame">
                                                <label for="area">Area Frame</label>
                                                </td>
            					               </tr>
                                            </table>
                                            <br>
                                            
                                            <strong>5.5 Fraksi Sampel Keseluruhan: </strong>
                                            <textarea class="form-control" style="height:100px" name="b5r5" placeholder="Tuliskan Fraksi Sampel Keseluruhan">{{ old('b5r5') }}</textarea>
                                            <br>
                                            
                                            <strong>5.6 Nilai Perkiraan Sampling Error Variabel Utama: </strong>
                                            <input type="text" id="b5r6" name="b5r6" class="form-control" value="{{ old('b5r6') }}" placeholder="Tuliskan Nilai Perkiraan Sampling Error">
                                            <br>
                                            
                                            
            							   </div>
            						</div>
                                     
                                    <div class="row">
                                        <div class="col-md-12">
                                            <strong>5.7 Unit Sampel: </strong>
            					            <input type="text" id="b5r7" name="b5r7" class="form-control" value="{{ old('b5r7') }}" placeholder="Tuliskan Unit Sampel">
            							   </div>
            						</div>
            						<br>
            						<div class="row">
                                        <div class="col-md-12">
                                            <strong>5.8 Unit Observasi: </strong>
            					            <input type="text" id="b5r8" name="b5r8" class="form-control" value="{{ old('b5r8') }}" placeholder="Tuliskan Unit Observasi">
            							   </div>
            						</div>
            						<br>  
            				
            							        
								</div>
								<div class="panel-footer">
									<div class="row">
							
										
									</div>
								</div>
    </div>
    
    
    <!-- AKHIR BLOK V -->
    
    <div id="blok6" class="tab-pane fade">
  <center>   <h3>VI. PENGUMPULAN DATA</h3> </center> 
                            <div class="panel-footer">
                                    
                                <div class="row">
                                        <div class="col-md-12">
                                            <strong>6.1 Apakah Melakukan Uji Coba (Pilot Survey)? </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="ya" name="b6r1" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b6r1" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br>     
                                 
            					<div class="row">
            						     
                                        <div class="col-md-12">
                                            <strong>6.2 Metode Pemeriksaan Kualitas Pengumpulan Data: </strong> <br>
            					                <label><input  type="checkbox" name="b6r2[]" value="Kunjungan Kembali">Kunjungan Kembali</label><br>
                                                <label><input type="checkbox" name="b6r2[]" value="Supervisi">Supervisi</label><br>
                                                <label><input type="checkbox" name="b6r2[]" value="Task Force">Task Force</label><br>
                                                <label><input type="checkbox" id="check-kualitas" onclick="cekKualitas()"> Lainnya</label><br>
                                               
                                                <input type = "text" name="b6r2[]" id="lainnya-kualitas" style="display: none;" class="form-control" placeholder ="Tuliskan Metode Pemeriksaan Kualitas Pengumpulan Data Lainnya">
                                                
                                            <br>
            							   </div>
            						   
            						</div>
            						
            					<div class="row">
                                        <div class="col-md-12">
                                            <strong>6.3 Apakah Melakukan Penyesuaian Nonrespon? </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="ya" name="b6r3" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b6r3" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            						<br>     	
            				
            					<div class="row">
                                        <div class="col-md-12">
                                            <strong>6.4 Petugas Pengumpulan Data: </strong><br>
            					          
                					            <input type="radio" id="staf" name="b6r4" value="Staf Instansi Penyelenggara">
                                                <label for="staf">Staf Instansi Penyelenggara</label>
            					               <br>
            					                
                                                <input type="radio" id="mitra" name="b6r4" value="Mitra Atau Tenaga Kontrak">
                                                <label for="mitra">Mitra Atau Tenaga Kontrak</label>
                                              
            					               <br>
            					              
                                                <input type="radio" id="staf-mitra" name="b6r4" value="Staf Instansi Penyelenggara Dan Mitra Atau Tenaga Kontrak">
                                                <label for="staf-mitra">Staf Instansi Penyelenggara Dan Mitra Atau Tenaga Kontrak</label>
                                              
            					         
            							   </div>
            						</div>
            						<br>     	
            					
            						<div class="row">
                                        <div class="col-md-12">
                                            <strong>6.5 Persyaratan Pendidikan Terendah Petugas Pengumpulan Data: </strong><br>
            					          
                					            <input type="radio" id="smp" name="b6r5" value="Kurang Dari Atau Sama Dengan SMP">
                                                <label for="smp">Kurang Dari Atau Sama Dengan SMP</label>
            					               <br>
            					                
                                                <input type="radio" id="sma" name="b6r5" value="SMA atau SMK">
                                                <label for="sma">SMA atau SMK</label>
                                              
            					               <br>
            					              
                                                <input type="radio" id="diploma" name="b6r5" value="Diploma I Atau II Atau III">
                                                <label for="diploma">Diploma I Atau II Atau III</label>
                                                <br>
            					              
                                                <input type="radio" id="sarjana" name="b6r5" value="Diploma IV Atau S1 Atau S2 Atau S3">
                                                <label for="sarjana">Diploma IV Atau S1 Atau S2 Atau S3</label>
                                              
            					               
                                          
            							   </div>
            						</div>
            						<br>
            					
            					<div class="row">
                                        <div class="col-md-12">
                                            <strong>6.6 Jumlah Petugas: </strong><br>
            					          
                					       <input style="width:17%" type="text" name="b6r6a" value="Supervisor/Penyelia/Pengawas" disabled> <input style="width:5%" type="text" name="b6r6a" value="0"> Orang <br> <br>
                					            <input style="width:17%" type="text" name="b6r6b" value="Pengumpul Data/Enumerator" disabled> <input style="width:5%" type="text" name="b6r6b" value="0"> Orang
                                           
            							   </div>
            						</div>
            						<br>
            					
            					<div class="row">
                                        <div class="col-md-12">
                                            <strong>6.7 Apakah Melakukan Pelatihan Petugas? </strong>
            					            <table>
            					               <tr> 
                					            <td> <input type="radio" id="ya" name="b6r7" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b6r7" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
                                            </table>
            							   </div>
            						</div>
            					  		
            					
            							        
								</div>
								<div class="panel-footer">
									<div class="row">
								
										
									</div>
								</div>
    </div>
    
    <!-- AKHIR BLOK VI -->
    
    
    <div id="blok7" class="tab-pane fade">
  <center>   <h3>VII. PENGOLAHAN DAN ANALISIS</h3> </center> 
                            <div class="panel-footer">
                                    
                                    
                                <div class="row">
                                        <div class="col-md-12">
                                            <strong>7.1 Tahapan Pengolahan Data: </strong>
            					            <table >
            					               <tr>
            					                <td>Penyuntingan (Editing) &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b7r1a" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b7r1a" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
            					                <tr>
            					                <td>Penyandian (Coding) &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b7r1b" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b7r1b" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
            					               <tr>
            					                <td>Data Entry &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b7r1c" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b7r1c" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
            					               <tr>
            					                <td>Penyahihan (Validasi) &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b7r1d" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b7r1d" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
                                            </table>
                                            
                                            
            							   </div>
            						</div>
            						<br>
            						
            						
            						<div class="row">
                                        <div class="col-md-12">
                                            <strong>7.2 Metode Analisis: </strong>
            					            <table >
            					               <tr>
            					                
                					            <td> <input type="radio" id="deskriptif" name="b7r2" value="Deskriptif">
                                                <label for="deskriptif">Deskriptif</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="inferensia" name="b7r2" value="Inferensia">
                                                <label for="inferensia">Inferensia</label> &nbsp &nbsp
                                                </td>
                                                <td> 
                                                <input type="radio" id="deskriptif-inferensia" name="b7r2" value="Deskriptif dan Inferensia">
                                                <label for="deskriptif-inferensia">Deskriptif dan Inferensia</label> &nbsp &nbsp
                                                </td>
            					               </tr>
            					               
            					              
            					               
                                            </table>
                                            
                                            
            							   </div>
            						</div>
            						<br>
            						
            						
            							<div class="row">
            						     
                                        <div class="col-md-12">
                                            <strong>7.3 Unit Analisis: </strong> <br>
            					                <label><input  type="checkbox" name="b7r3[]" value="Individu">Individu</label><br>
                                                <label><input type="checkbox" name="b7r3[]" value="Rumah Tangga">Rumah Tangga</label><br>
                                                <label><input type="checkbox" name="b7r3[]" value="Usaha/Perusahaan">Usaha/Perusahaan</label><br>
                                                <label><input type="checkbox" id="check-analisis" onclick="cekAnalisis()"> Lainnya</label><br>
                                               
                                                <input type = "text" name="b7r3[]" id="lainnya-analisis" style="display: none;" class="form-control" placeholder ="Tuliskan Unit Analisis Lainnya">
                                                
                                            <br>
            							   </div>
            						   
            						</div>
            						
            						<div class="row">
            						     
                                        <div class="col-md-12">
                                            <strong>7.4 Tingkat Penyajian Hasil Analisis: </strong> <br>
            					                <label><input  type="checkbox" name="b7r4[]" value="Nasional">Nasional</label><br>
                                                <label><input type="checkbox" name="b7r4[]" value="Provinsi">Provinsi</label><br>
                                                <label><input type="checkbox" name="b7r4[]" value="Kabupaten/Kota">Kabupaten/Kota</label><br>
                                                <label><input type="checkbox" id="check-penyajian" onclick="cekPenyajian()"> Lainnya</label><br>
                                               
                                                <input type = "text" name="b7r4[]" id="lainnya-penyajian" style="display: none;" class="form-control" placeholder ="Tuliskan Tingkat Penyajian Hasil Analisis Lainnya">
                                                
                                            <br>
            							   </div>
            						   
            						</div>
                                   
            						
            				
            							        
								</div>
								<div class="panel-footer">
									<div class="row">
						
										
									</div>
								</div>
    </div>
    
<!-- AKHIR BLOK VII -->

    <div id="blok8" class="tab-pane fade">
  <center>   <h3>VIII. DISEMINASI HASIL</h3> </center> 
                            <div class="panel-footer">
                                    
                                <div class="row">
                                        <div class="col-md-12">
                                            <strong>8.1 Produk Kegiatan yang Tersedia untuk Umum: </strong>
            					            <table >
            					               <tr>
            					                <td>Tercetak (Hardcopy) &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b8r1a" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b8r1a" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
            					                <tr>
            					                <td>Digital (Softcopy) &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b8r1b" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b8r1b" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
            					               <tr>
            					                <td>Data Mikro &nbsp &nbsp</td> <td>:  &nbsp</td>
                					            <td> <input type="radio" id="ya" name="b8r1c" value="Ya">
                                                <label for="ya">Ya</label> &nbsp &nbsp </td>
                					            <td> 
                                                <input type="radio" id="tidak" name="b8r1c" value="Tidak">
                                                <label for="tidak">Tidak</label>
                                                </td>
            					               </tr>
            					               
                                            </table>
                                            
                                            
            							   </div>
            						</div>
            						<br>
            						
            						 
            						<div class="row">
                                        <div name="YaYaYa" style="display: none;" class="col-md-12">
                                            <strong>8.2 Rencana Rilis Produk Kegiatan: </strong>
            					            <table class="table table-striped">
            					            <tr>
            					                <th></th> <th>Tanggal Rilis</th>
            					            </tr>    
                                            <tr> 
            					            <td>Tercetak (Hardcopy)</td> <td> <div name="b8r2a" style="display: none;"> <input type="date" id="b8r2a" name="b8r2a" value="{{ old('b8r2a') }}" class="form-control"> </div> </td>
            					            </tr> 
            					            <tr>
            					            <td>Digital (Softcopy)</td> <td> <div name="b8r2b" style="display: none;"> <input type="date" id="b8r2b" name="b8r2b" value="{{ old('b8r2b') }}" class="form-control"> </div> </td>    
            					            </tr>
            					            <tr>
            					             <td>Data Mikro</td> <td> <div name="b8r2c" style="display: none;"> <input type="date" id="b8r2c" name="b8r2c" value="{{ old('b8r2c') }}" class="form-control"> </div> </td>   
            					            </tr>
                                            </table>
                                            <br>
            							   </div>
            						</div>
            						
            					
            						
            						
            						
            						
                 				        
								</div>
								<div class="panel-footer">
									<div class="row">
								
										
									</div>
								</div>
    </div>

<button type="submit" class="btn btn-primary">Kirim</button>
<!-- AKHIR BLOK VIII -->
    </form>

  </div>
  
  
				</div>
			</div>
			<!-- END MAIN CONTENT -->
		</div>
		<!-- END MAIN -->
		<div class="clearfix"></div>
		<footer>
			<div class="container-fluid">
				<p class="copyright">2021 © <a href="https://sultra.bps.go.id">BPS Provinsi Sulawesi Tenggara</a></p>
			</div>
		</footer>
	</div>
	<!-- END WRAPPER -->
	<!-- Javascript -->
	<script src="{{asset('admin/assets/vendor/jquery/jquery.min.js')}}"></script>
	<script src="{{asset('admin/assets/vendor/bootstrap/js/bootstrap.min.js')}}"></script>
	<script src="{{asset('admin/assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
	<script src="{{asset('admin/assets/scripts/klorofil-common.js')}}"></script>
	  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
        function myFunction() {
          var checkBox = document.getElementById("check");
          var text = document.getElementById("lainnya");
          if (checkBox.checked == true){
            text.style.display = "block";
          } else {
             text.style.display = "none";
          }
        }
        
         function cekSarana() {
          var checkBox = document.getElementById("check-sarana");
          var text = document.getElementById("lainnya-sarana");
          if (checkBox.checked == true){
            text.style.display = "block";
          } else {
             text.style.display = "none";
          }
        }
        
        function cekUnit() {
          var checkBox = document.getElementById("check-unit");
          var text = document.getElementById("lainnya-unit");
          if (checkBox.checked == true){
            text.style.display = "block";
          } else {
             text.style.display = "none";
          }
        }
        
        function cekKualitas() {
          var checkBox = document.getElementById("check-kualitas");
          var text = document.getElementById("lainnya-kualitas");
          if (checkBox.checked == true){
            text.style.display = "block";
          } else {
             text.style.display = "none";
          }
        }
        
         function cekAnalisis() {
          var checkBox = document.getElementById("check-analisis");
          var text = document.getElementById("lainnya-analisis");
          if (checkBox.checked == true){
            text.style.display = "block";
          } else {
             text.style.display = "none";
          }
        }
        
        function cekPenyajian() {
          var checkBox = document.getElementById("check-penyajian");
          var text = document.getElementById("lainnya-penyajian");
          if (checkBox.checked == true){
            text.style.display = "block";
          } else {
             text.style.display = "none";
          }
        }
</script>
	<script>

$(document).ready(function() {
  $(".select2").select2({
    placeholder: "Pilih cara pengumpulan data",
    allowClear: true
});

  $(".select21").select2({
    placeholder: "Pilih sektor kegiatan",
    allowClear: true
});

$(".select22").select2({
    placeholder: "Pilih Wilayah",
    allowClear: true
});



});

$(document).ready(function() {
    
    
    $("input[name$='b4r1']").click(function() {
        var test = $(this).val();

        $("div.desc").hide();
        $("#" + test).show();
    });
    
    
    $("input[name$='b4r4']").click(function() {
        var test = $(this).val();

        $("div.desc2").hide();
        $("#" + test).show();
    });
    
     $("input[name$='b5r2']").click(function() {
        var test = $(this).val();

        $("div.desc3").hide();
        $("#" + test).show();
    });
    
   
    $('input').change(() => {
  const first = $('input[name=b8r1a]:checked').val();
  const second = $('input[name=b8r1b]:checked').val();
  const third = $('input[name=b8r1c]:checked').val();
  

  
  $("div[name=YaYaYa]").toggle(first === "Ya" || second === "Ya" || third === "Ya");
  $("div[name=b8r2a]").toggle(first === "Ya"); 
  $("div[name=b8r2b]").toggle(second === "Ya");
  $("div[name=b8r2c]").toggle(third === "Ya"); 

});

    

    
    
    $("#b0r3").change(function(){
     var status = this.value;
     
   if(status=="Survei")
    $("#" + status).show();
    if(status=="Pencacahan Lengkap")
    $("#Survei").hide();
    if(status=="Kompilasi Produk Administrasi")
    $("#Survei").hide();
    if(status=="Cara Lain Sesuai Dengan Perkembangan TI")
    $("#Survei").hide();

  
   
  });
    
    
    
});

  </script>
  
  <script type="text/javascript">
    function validate() {
        if (document.getElementById('lainnya').checked) {
            alert("checked");
        } else {
            alert("You didn't check it! Let me check it for you.");
        }
    }
</script>
  
	<script type="text/javascript">
    $(function () {
        $("#ddlModels").change(function () {
            if ($(this).val() == 'others') {
                $("#instansi_pembinaan").removeAttr("disabled");
                $("#instansi_pembinaan").focus();
            } else {
                $("#instansi_pembinaan").attr("disabled", "disabled");
            }
        });
    });
</script>


<script type="text/javascript">
        var i = 0;
        $("#dynamic-ar").click(function() {
 
            ++i;
            $("#dynamicAddRemove").append(
                '<tr> <td> '+i+' </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k1['+
                i +
                ']" placeholder="Tuliskan Nama Variabel"></textarea> </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k2['+
                i+
                ']" placeholder="Tuliskan Konsep Variabel"></textarea> </td>   <td> <textarea class="form-control" style="height:80px" name="b3r34k3['+
                i+
                ']" placeholder="Tuliskan Definisi"></textarea> </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k4['+
                i+
                ']" placeholder="Tuliskan Referensi Waktu"></textarea> </td>    <td><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fa-solid fa-trash"></i></button></td></tr>'
            );





        });

        $(document).on('click', '.remove-input-field', function() {
            $(this).parents('tr').remove();
        });
    </script>
    
     <script type="text/javascript">
        var i = 0;
        $("#dynamic-ar2").click(function() {
    


            ++i;
            $("#dynamicAddRemove2").append(
                '<tr> <td> '+i+' </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k1['+
                i +
                ']" placeholder="Tuliskan Nama Variabel"></textarea> </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k2['+
                i+
                ']" placeholder="Tuliskan Konsep Variabel"></textarea> </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k3['+
                i+
                ']" placeholder="Tuliskan Definisi"></textarea> </td> <td> <textarea class="form-control" style="height:80px" name="b3r34k4['+
                i+
                ']" placeholder="Tuliskan Referensi Waktu"></textarea> </td>  <td><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fa-solid fa-trash"></i></button></td></tr>'
            );





        });

        $(document).on('click', '.remove-input-field', function() {
            $(this).parents('tr').remove();
        });
    </script>
</body>

</html>
