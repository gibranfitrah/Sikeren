<?php
/*
------------------------------------------------------------------------------------------------
Parameter yang digunakan, khusus untuk $client_id & $client_secret 
di ganti sesuai dengan yang didapatkan dari subdit JKD
------------------------------------------------------------------------------------------------
*/
$url_base       = 'https://sso.bps.go.id/auth/';
$url_token      = $url_base.'realms/pegawai-bps/protocol/openid-connect/token';
$url_api        = $url_base.'realms/pegawai-bps/api-pegawai';
$client_id      = '17600-sikeren-my0'; 
$client_secret  = '6488c064-66dc-4a95-b4a8-db720911266b';

$query_search   = '/unit/740000092000';

/*
------------------------------------------------------------------------------------------------
Tahap 1 :
Mendapatkan akses token
------------------------------------------------------------------------------------------------
*/
$ch = curl_init($url_token);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($ch, CURLOPT_POSTFIELDS,"grant_type=client_credentials");
curl_setopt($ch, CURLOPT_USERPWD, $client_id . ":" . $client_secret);  
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response_token = curl_exec($ch);
if(curl_errno($ch)){
    throw new Exception(curl_error($ch));
}
curl_close ($ch);
$json_token = json_decode($response_token, true);
$access_token = $json_token['access_token'];


/*
------------------------------------------------------------------------------------------------
Tahap 2 :
Mendapatkan data pegawai dengan username tertentu
------------------------------------------------------------------------------------------------
*/
$ch = curl_init($url_api.$query_search);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , 'Authorization: Bearer '.$access_token ));  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if(curl_errno($ch)){
    throw new Exception(curl_error($ch));
}
curl_close ($ch);
$json = json_decode($response, true);

// echo "Hasil Pencarian <b>$query_search </b><hr>";
// $i=1;
// foreach ($json as $result){
//     echo "<br>$i : Username : ".$result['username']."<ul>";
//     foreach ($result['attributes'] as $key => $value){
//         echo "<li><i>".$key."</i>: <br>". $value[0]."</li>";
//     }
//     echo "</ul>";
//     $i++;
   
// }

print_r(json_encode($json));
// print_r($json_token);


?>
