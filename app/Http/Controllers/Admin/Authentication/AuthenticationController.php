<?php

namespace App\Http\Controllers\Admin\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Security\TokenController;
use App\Http\Controllers\Library\ResponseController;
// validator
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|alpha_num',
            'password' => 'required|string|alpha_num|min:8'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $credentials = $request->only('username', 'password');

        $user = User::where('username', $request->username)->where('role', 'admin')->first();

        if(!$user){
            return $response->get404('Admin');
        }

        if (!Auth::attempt($credentials)) {
            return $response->get401();
        }

        $token = $user->createToken('auth:sanctum', ['admin'])->plainTextToken;

        $admin = [
            'id_user' => $user->id_user,
            'full_name' => $user->full_name,
        ];

        return $response->postLogin201($admin, $token);
    }

    public function logout(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid'
        ]); 

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if (TokenController::tokenCheckingAdmin($id_user) == false) {
            return $response->get401();
        }

        $user = User::where('id_user', $id_user)->where('role', 'admin')->first();

        if(!$user){
            return ResponseController::get404('Admin');
        }

        $request->user()->currentAccessToken()->delete();

        return ResponseController::get200('Logout success');
    }
}
