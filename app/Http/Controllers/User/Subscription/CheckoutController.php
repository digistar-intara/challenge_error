<?php

namespace App\Http\Controllers\User\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
use App\Models\Occupied;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



class CheckoutController extends Controller
{
    public function checkout(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid|exists:user,id_user',
            'id_occupy' => 'required|uuid|exists:occupie,id_occupy',
        ]);

        $response = new ResponseController();

        if($validator->fails()){
            return $response->get422($validator->errors());
        }

        $user = $request->id_user;



        if (TokenController::tokenChecking($user) == false) {
            return $response->get401();
        }

        $check = Occupied::where('id_occupy', $request->id_occupy)->where('id_user', $user)->where('occupies.status_occupy', 'occupied')->first();
        if (!$check) {
            return $response->get422('User is not occupy this room');
        }

        // use format Y-m-d date now
        $check->check_out = date('Y-m-d');
        $check->save();

        return $response->post201('Checkout success');

    }
}
