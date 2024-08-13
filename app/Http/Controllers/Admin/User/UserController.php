<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Occupied;
use App\Models\Room;
use App\Models\Residents;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
// use validator
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getInformationUser(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid', // for admin
            'id_occupy' => 'required|uuid' // for user
        ]);

        $response = new ResponseController();

        if($validator->fails()){
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenCheckingAdmin($id_user) == false || !TokenController::tokenCheckingAdmin($id_user)){
            return ResponseController::get401();
        }

        // Output Step
        // 1. Get User Information like full name, address, phone number, and nik
        // 2. Get User Room Information like room number, room type, and room price
        // 3. Get User Occupy information
        // 4. Get User resident information who sleep in the same room

        // first step call Occupied model

        $occupied = Occupied::where('id_occupy', $request->id_occupy)->first();

        if(!$occupied){
            return $response->get404('Occupied not found');
        }

        $user = User::where('id_user', $occupied->id_user)
            ->select('full_name', 'phone_number', 'address', 'nik', 'username')->first();

        if(!$user){
            return $response->get404('User not found');
        }

        $room = Room::join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->where('rooms.id_room', $occupied->id_room)
            ->select('rooms.room_floor', 'rooms.room_number', 'room_types.room_type', 'room_types.room_type_price')
            ->first();

        if(!$room){
            return $response->get404('Room not found');
        }

        $resident = Residents::where('id_occupy', $request->id_occupy)
            ->select('id_resident','resident_name', 'resident_phone_number', 'resident_address', 'resident_nik')
            ->orderBy('created_at', 'asc')
            ->get();

        $double_bed = $occupied->is_double_bed == 'yes' ? true : false;
        $subscription_model = $occupied->subscription_model;
        $price = $room->room_type_price;

        if($double_bed){
            $price *= 1.5;
        }

        $monthCount = 0;

        if($subscription_model == '1 month'){
            $monthCount = 1;
        } else if($subscription_model == '3 months'){
            $monthCount = 3;
        } else if($subscription_model == '6 months'){
            $monthCount = 6;
        }

        $grandTotal = $price * $monthCount;

        // if is double bed no, then in $resident add one more but value is null

        

        $room->room_type_price = 'Rp '. number_format($room->room_type_price, 0, ',', '.');
        $priceNew = 'Rp '. number_format($price, 0, ',', '.');
        $grandTotalNew = 'Rp '. number_format($grandTotal, 0, ',', '.');
        $newResidentList = $resident;

        $output = [
            'user' => $user,
            'room' => $room,
            'resident' => $newResidentList,
            'occupy' => [
                'id_occupy' => $occupied->id_occupy,
                'subscription_model' => 'Every '.$subscription_model,
                'is_double_bed' => $occupied->is_double_bed,
                'price_each_month' => $priceNew. ' perbulan',
                'grand_total' => $grandTotalNew,
                'check_in' => date('d F Y', strtotime($occupied->check_in)),
            ]
        ];

        return $response->get200($output);
    }
}
