<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use sanctum personal access token
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Library\ResponseController;

class TokenController extends Controller
{

    //* ACTIVE FUNCTION MODULES

    static function tokenChecking($uuid){
        $request = request();
        $plainTextToken = substr($request->header('Authorisation'), 7); // Ganti dengan nilai plain text dari token yang ingin Anda periksa
        $accessToken = Str::after($plainTextToken, '|');

        $tokenableId = $uuid; // Ganti dengan ID pemilik token yang ingin Anda periksa

        $tokenData = PersonalAccessToken::where('token', hash('sha256', $accessToken))
                                        ->where('tokenable_uuid', $tokenableId)->where('abilities', '["user"]')
                                        ->first();
        // dd($accessToken, $tokenableId, $tokenData);

        if (!$tokenData || $tokenData == null) {
            return false;
        }

        return true;
    }

    static function apiKeysCheckingAdmin(){
        $request = request();
        $apiKey = $request->header('X-API_KEY');
        // $tokenableId = $uuid; // Ganti dengan ID pemilik token yang ingin Anda periksa
        // dd($apiKey, config('app.api_key_admin'));

        if ($apiKey != config('app.api_key_admin')){
            return false;
        }

        return true;
    }


    static function tokenCheckingAdmin($uuid){
        $request = request();
        $plainTextToken = substr($request->header('Authorization'), 7); // Ganti dengan nilai plain text dari token yang ingin Anda periksa
        // $apiKey = $request->header('X-API-KEY');
        $accessToken = Str::after($plainTextToken, '|');

        $tokenableId = $uuid; // Ganti dengan ID pemilik token yang ingin Anda periksa



        // if ($apiKey != config('app.api_key_admin')){
        //     return ResponseController::get401('API Key not valid');
        // }

        // dd(config('app.api_key_admin'), $apiKey, $accessToken, $tokenableId);

        $tokenData = PersonalAccessToken::where('token', hash('sha256', $accessToken))
                                        ->where('tokenable_id', $tokenableId)->where('abilities', '["admin"]')
                                        ->first();
        // dd($accessToken, $tokenableId, $tokenData);

        if (!$tokenData || $tokenData == null) {
            return false;
        }

        return true;
    }

    static function tokenCheckingDeviceActuator($uuid){
        $request = request();
        $plainTextToken = substr($request->header('Authorization'), 7); // Ganti dengan nilai plain text dari token yang ingin Anda periksa
        $accessToken = Str::after($plainTextToken, '|');

        $tokenableId = $uuid; // Ganti dengan ID pemilik token yang ingin Anda periksa

        $tokenData = PersonalAccessToken::where('token', hash('sha256', $accessToken))
                                        ->where('tokenable_id', $tokenableId)->where('abilities', '["actuator"]')
                                        ->first();
        // dd($accessToken, $tokenableId, $tokenData);

        if (!$tokenData || $tokenData == null) {
            return false;
        }

        return true;
    }

    static function tokenCheckingDeviceSensor($uuid){
        $request = request();
        $plainTextToken = substr($request->header('Authorization'), 7); // Ganti dengan nilai plain text dari token yang ingin Anda periksa
        $accessToken = Str::after($plainTextToken, '|');

        $tokenableId = $uuid; // Ganti dengan ID pemilik token yang ingin Anda periksa

        $tokenData = PersonalAccessToken::where('token', hash('sha256', $accessToken))
                                        ->where('tokenable_id', $tokenableId)->where('abilities', '["sensor"]')
                                        ->first();
        // dd($accessToken, $tokenableId, $tokenData);

        if (!$tokenData || $tokenData == null) {
            return false;
        }
        return true;
    }


    function tokenCheckingInterior($uuid){
        $request = request();
        $plainTextToken = substr($request->header('Authorization'), 7); // Ganti dengan nilai plain text dari token yang ingin Anda periksa
        $accessToken = Str::after($plainTextToken, '|');

        $tokenableId = $uuid; // Ganti dengan ID pemilik token yang ingin Anda periksa

        $tokenData = PersonalAccessToken::where('token', hash('sha256', $accessToken))
                                        ->where('tokenable_id', $tokenableId)
                                        ->first();
        // dd($accessToken, $tokenableId, $tokenData);

        if (!$tokenData || $tokenData == null) {
            return false;
        }

        return true;
    }

    static function allTokenDeleteAfterResetPassword($telephone){

        // call user first
        $user = User::where('telephone', $telephone)->first();

        $uuid = $user->id;

        // call all token with tokenable_id is uuid

        $tokens = PersonalAccessToken::where('tokenable_id', $uuid)->get();

        // delete all token

        foreach ($tokens as $token) {
            $token->delete();
        }

        return true;

    }

    static function allTokenDeleteAfterChangePasswordOrTelephone($id_user){

        if (TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)) {
            return ResponseController::get401();
        }
        // call all token with tokenable_id is uuid

        $tokens = PersonalAccessToken::where('tokenable_id', $id_user)->get();

        // delete all token

        foreach ($tokens as $token) {
            $token->delete();
        }

        return true;

    }

}
