<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use env




class WAFonnteController extends Controller
{
    static function sendMessage($nama, $body, $clientTelephone){
        $curl = curl_init();
        $token = 'a7xpfvySV7Pay2yqCo97';
        $url = 'https://api.fonnte.com/send';
        $telephone = $clientTelephone;

        $message = "Halo, $nama.\n\n$body.\n\nWaktu pesan dikirim : ".date('d-m-Y H:i:s')."\n\n Salam hangat, Wiwi Kos.";

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $telephone,
                'message' => $message, 
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $token" //change TOKEN to your actual token
            ),
        ));

        $response = curl_exec($curl);

        // dd($response);

        return $response;
    }
}
