<?php

namespace App\Http\Controllers\User\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Occupied;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ThirdParty\WAFonnteController;
use App\Http\Controllers\Library\ResponseController;
// use crypt
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Http\Controllers\Security\TokenController;

class AuthenticationController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|alpha_num|unique:users',
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/', //regex for alphabet and space only
            'password' => 'required|string|min:8|alpha_num',
            'phone_number' => 'required|string|numeric|digits_between:9,13|unique:users',
            'address' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'nik' => 'required|string|numeric|digits_between:16,16|unique:users',
            'user_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'nik_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'ktm_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if($validator->fails()){
            return ResponseController::get422($validator->errors());
        }

        $user = new User();
        $user->username = $request->username;
        $user->full_name = $request->full_name;
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number;
        $user->address = $request->address;
        $user->nik = $request->nik;

        if ($request->hasFile('user_image')) {
            $userImage = $request->file('user_image');
            $userImageName = $this->sanitizeFileName(Hash::make($userImage->getClientOriginalName())) . '.' . $userImage->getClientOriginalExtension();
            // move file to public folder
            $userImage->storeAs('private/image/user', $userImageName);
            // set user_image with file name
            $user->user_image = Crypt::encryptString($userImageName);
        }
        
        if ($request->hasFile('nik_image')) {
            $nikImage = $request->file('nik_image');
            $nikImageName = $this->sanitizeFileName(Hash::make($nikImage->getClientOriginalName())) . '.' . $nikImage->getClientOriginalExtension();
            // move file to public folder
            $nikImage->storeAs('private/image/nik', $nikImageName); // store to storage
            // set nik_image with file name
            $user->nik_image = Crypt::encryptString($nikImageName);
        }
        
        if ($request->hasFile('ktm_image')) {
            $ktmImage = $request->file('ktm_image');
            $ktmImageName = $this->sanitizeFileName(Hash::make($ktmImage->getClientOriginalName())) . '.' . $ktmImage->getClientOriginalExtension();
            // move file to public folder
            $ktmImage->storeAs('private/image/ktm', $ktmImageName); // store to storage
            // set ktm_image with file name
            $user->ktm_image = Crypt::encryptString($ktmImageName);
        }
        

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->role = 'user';
        $user->account_verified_at = null;

        
        $wafonnte = new WAFonnteController();
        $body = "Ini adalah kode OTP baru anda untuk verifikasi akun anda di KosKosan. Kode OTP anda adalah " . $otp.". \nJangan dibagikan kepada orang lain demi keamanan akun anda. Jika anda tidak merasa untuk mengirim OTP ini, maka abaikan saja.";
        $wafonnte->sendMessage($user->full_name, $body, $user->phone_number);

        $user->save();
        return ResponseController::post201('User created successfully');
    }

    public function verifyOTP(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|numeric|digits_between:9,13',
            'otp' => 'required|string|numeric|digits_between:6,6',
        ]);

        if($validator->fails()){
            return ResponseController::get422($validator->errors());
        }

        $user = User::where('phone_number', $request->phone_number)->first();
        if($user->otp == $request->otp && $user->account_verified_at == null){
            $user->account_verified_at = now();
            $wafonnte = new WAFonnteController();
            $body = "Akun anda di KosKosan telah berhasil diverifikasi. Sekarang anda dapat menggunakan akun anda untuk login.";
            $wafonnte->sendMessage($user->full_name, $body, $user->phone_number);
            $user->save();
            return ResponseController::post201('Account verified successfully');
        }else{
            return ResponseController::get401('Invalid OTP');
        }
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|alpha_num',
            'password' => 'required|string|min:8|alpha_num',
        ]);

        $response = new ResponseController();

        if($validator->fails()){
            return $response->get422($validator->errors());
        }

        $credentials = $request->only('username', 'password');
        
        $user = User::where('username', $request->username)->first();

        if(!$user){
            return $response->get404('User');
        }

        if ($user->role == 'user') {
            if ($user->otp != null && $user->account_verified_at == null) {
                return $response->get405('Account is not verified');
            } else if ($user->otp == null && $user->account_verified_at == null) {
                return $response->get405('Account is not verified');
            } else {
                if (Auth::attempt($credentials)) {
                    $token = $user->createToken('auth:sanctum', ['user'], )->plainTextToken;

                    $occupy = Occupied::where('id_user', $user->id_user)->first();

                    if($occupy){
                        $is_have_room = 'yes';
                    } else {
                        $is_have_room = 'no';
                    }
                    
                    $user = [
                        'id_user' => $user->id_user,
                        'username' => $user->username,
                        'full_name' => $user->full_name,
                        'phone_number' => $user->phone_number,
                        'address' => $user->address,
                        'nik' => $user->nik,
                        'is_have_room' => $is_have_room,
                    ];
                    return ResponseController::postLogin201($user, $token);
                    } else {
                    return ResponseController::get401('Invalid username or password');
                }
            }
        } else {
            return ResponseController::get401('invalid role');
        }
            
    }

    public function resendOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|alpha_num',
        ]);

        if($validator->fails()){
            return ResponseController::get422($validator->errors());
        }

        $user = User::where('username', $request->username)->first();

        if($user){
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->account_verified_at = null;
            $user->save();
            $wafonnte = new WAFonnteController();
            $body = "Ini adalah kode OTP baru anda untuk verifikasi akun anda di KosKosan. Kode OTP anda adalah " . $otp.". \nJangan dibagikan kepada orang lain demi keamanan akun anda. Jika anda tidak merasa untuk mengirim OTP ini, maka abaikan saja.";
            $wafonnte->sendMessage($user->full_name, $body, $user->phone_number);
            return ResponseController::post201('OTP sent successfully');
        }else{
            return ResponseController::get404('User');
        }
    }

    public function logout(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
        ]);

        if($validator->fails()){
            return ResponseController::get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        $user = User::where('id_user', $id_user)->first();

        if(!$user){
            return ResponseController::get404('User');
        }

        $request->user()->currentAccessToken()->delete();

        return ResponseController::get200('Logout success');
    }

  function sanitizeFileName($fileName) {
    // Remove any characters that are not alphanumeric, underscores, hyphens, or periods
    $sanitizedFileName = preg_replace("/[^\w\-\.]/", '_', $fileName);
    return $sanitizedFileName;
}

}
