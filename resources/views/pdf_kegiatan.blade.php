<img src="{{ storage_path('app/foto/bps.jpeg') }}" alt="" height="114" width="563">
<img src="data:image/svg+xml;base64, {!! $qrcode !!}"> 
<div style="margin-right:0px;margin-left:500px" >
<table>
    <tr>
<td style="text-align:right; font-size:18px">Kendari, {{$employee->start}} </td>
</tr>
</table>   
</div>

<table>
    <tr>
        <td style="font-size:18px">Nomor</td> <td style="font-size:18px">:</td> <td style="font-size:18px"> {{$employee->nomor}} </td>
    </tr>

    <tr>
        <td style="font-size:18px">Lampiran</td> <td style="font-size:18px">:</td> <td style="font-size:18px"> - </td>
    </tr>

    <tr>
        <td style="font-size:18px">Perihal</td> <td style="font-size:18px">:</td> <td style="font-size:18px"> {{$employee->title}} </td>
    </tr>
    <tr>
        <th height="20"> </th>	
	</tr>

</table>


<table>
    <tr>
        <td style="font-size:18px"> Kepada Yang Terhormat : </td>
    </tr>

    <tr>
        <td style="font-size:18px"> Bapak/Ibu Peserta Rapat </td>
    </tr>

    <tr>
        <td style="font-size:18px"> di- </td>
    </tr>

    <tr>
        <td style="font-size:18px"> &nbsp; &nbsp; &nbsp; &nbsp;  Tempat </td>
    </tr>

    <tr>
        <td style="text-align:justify; line-height:150%; font-size:18px"> &nbsp; &nbsp; &nbsp; &nbsp; Dalam rangka {{$employee->agenda}}, bersama ini kami mengundang Bapak/Ibu untuk mengikuti rapat yang akan dilaksanakan pada: </td>
    </tr>

   

</table>

<table>
    <tr>
        <td style="font-size:18px"> &nbsp; &nbsp; &nbsp; &nbsp; Hari, tanggal</td> <td style="font-size:18px"> &nbsp; &nbsp;:</td> <td style="font-size:18px">  {!! htmlspecialchars_decode(date('l', strtotime($employee->start))) !!}, {{$employee->start}}  </td>
    </tr>
    <tr>
        <td style="font-size:18px"> &nbsp; &nbsp; &nbsp; &nbsp; Pukul</td> <td style="font-size:18px"> &nbsp; &nbsp;:</td> <td style="font-size:18px">  {{substr($employee->start_jam,0,-3) }} s.d. {{substr($employee->end_jam,0,-3) }}   </td>
    </tr>
    <tr>
        <td style="font-size:18px"> &nbsp; &nbsp; &nbsp; &nbsp; Tempat</td> <td style="font-size:18px"> &nbsp; &nbsp;:</td> <td style="font-size:18px">  {{$employee->tempat}} </td>
    </tr>

</table>

<table>
    <tr>
        <td style="text-align:justify; line-height:150%; font-size:18px"> &nbsp; &nbsp; &nbsp; &nbsp; Mengingat pentingnya acara tersebut diharapkan kehadiran Bapak/Ibu tepat waktu. Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih </td>
    </tr>
    <tr>
           <th height="20"> </th>	
	</tr>
</table>


<div style="margin-right:0px;margin-left:400px" >
<table>

    @if($employee->pemimpin == 'Agnes Widiastuti')
    <tr>
        <td style='text-align:center; font-size:18px'> Kepala Badan Pusat Statistik </td>
    </tr>
    @else
    <tr>
        <td style='text-align:center; font-size:18px'>a.n. Kepala Badan Pusat Statistik </td>
    </tr>
    @endif
    <tr>
        <td style='text-align:center; font-size:18px'> Provinsi Sulawesi Tenggara</td>
    </tr>

    @if($employee->status_pemimpin == 'Disetujui' || $employee->setuju_rapat == 1)
    <tr>
            <th height="50"> <img src="data:image/svg+xml;base64, {!! $qrcode !!}">  </th>	
           <th height="50" width="300">   </th>	
           <th height="50"> <img src="data:image/svg+xml;base64, {!! $qrcode2 !!}">  </th>
    </tr>
	@else
	<tr>
           <th height="50">   </th>	
	</tr>
	@endif
	
    <tr>
        <td style='text-align:center; font-size:18px'> {{$employee->pemimpin}}</td>
    </tr>


</table>
</div>
