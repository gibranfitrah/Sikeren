<?php

require '../sikeren-dev/vendor/autoload.php';

session_start();

$provider = new JKD\SSO\Client\Provider\Keycloak([
    'authServerUrl'         => 'https://sso.bps.go.id',
    'realm'                 => 'pegawai-bps',
    'clientId'              => '17600-sikeren-my0',
    'clientSecret'          => '6488c064-66dc-4a95-b4a8-db720911266b',
    'redirectUri'           => 'https://webapps.bps.go.id/sultra/sikeren/sso.php'
]);

if (!isset($_GET['code'])) {

    // Untuk mendapatkan authorization code
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

    // Mengecek state yang disimpan saat ini untuk memitigasi serangan CSRF
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {

    try {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
    } catch (Exception $e) {
        exit('Gagal mendapatkan akses token : ' . $e->getMessage());
    }

    // Opsional: Setelah mendapatkan token, anda dapat melihat data profil pengguna
    try {

        $user = $provider->getResourceOwner($token);
        
    } catch (Exception $e) {
        exit('Gagal Mendapatkan Data Pengguna: ' . $e->getMessage());
    }

    // Gunakan token ini untuk berinteraksi dengan API di sisi pengguna
    // echo $token->getToken();
    
    $data = [
		    "token" => $token->getToken(),
            "nip" => $user->getNip(),		
            "nama" => $user->getName(),					
            "email" => $user->getEmail(), 
            "username" => $user->getUsername(), 
            "nipbaru" => $user->getNipbaru(),
            "kodeorganisasi" => $user->getKodeOrganisasi(),
            "kodeprovinsi" => $user->getKodeProvinsi(), 
            "kodekabupaten" => $user->getKodeKabupaten(), 
            "alamatkantor" => $user->getAlamatKantor(),
            "provinsi" => $user->getProvinsi(),
            "kabupaten" => $user->getKabupaten(), 
            "golongan" => $user->getGolongan(),
            "jabatan" => $user->getJabatan(),
            "foto" => $user->getUrlFoto(),
            "eselon" => $user->getEselon(),
        ];
        
    
    echo json_encode($data);
    
}
