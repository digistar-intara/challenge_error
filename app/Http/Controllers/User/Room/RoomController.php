<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomPhoto;
use App\Models\Occupied;
use App\Models\Subscription;
use App\Models\Residents;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;


class RoomController extends Controller
{
    public function listRoomAll(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'filter_type' => 'required|string|in:kmDalam,kmLuar,all', 
            'filter_floor' => 'required|string|in:1,2,3,all'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        // Purpose is to show exist rooms that it still available or unoccupied
        // 1. Get all rooms that it still available or unoccupied
        // 2. Get the room type name
        // 3. Get the price according to room type name

        $rooms = Room::join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->select('rooms.id_room', 'rooms.room_number', 'rooms.room_floor', 'room_types.room_type', 'room_types.room_type_price')
            ->orderBy('rooms.room_floor', 'asc')->orderBy('rooms.room_number', 'asc')
            ->get();

        // filter by room type

        if ($request->filter_type != 'all') {
            if ($request->filter_type == 'kmDalam') {
                $rooms = $rooms->filter(function ($room) {
                    return $room->room_type == 'Kamar Mandi Dalam';
                });
            } else if ($request->filter_type == 'kmLuar') {
                $rooms = $rooms->filter(function ($room) {
                    return $room->room_type == 'Kamar Mandi Luar';
                });
            }
        }

        // filter by floor

        if ($request->filter_floor != 'all') {
            $rooms = $rooms->filter(function ($room) use ($request) {
                return $room->room_floor == $request->filter_floor;
            });
        } 

        // check if there's a room that is occupied or not, if it Occupied and still not check out, then not showing it

        $rooms = $rooms->filter(function ($room) {
            return Occupied::where('id_room', $room->id_room)->where('check_out', null)->count() == 0;
        });



        // output is like
        // [
        //     {
        //         "id_room": "uuid",
        //         "room_name": "string",
        //         "room_number": "string",
        //         "room_floor": "string",
        //         "room_type_name": "string"
        //         "price": "integer",
        //         "is_occupied": "boolean"
        //     }
        // ]

        $results = [];

        foreach ($rooms as $room) {
            $results[] = [
                'id_room' => $room->id_room,
                'room_number' => $room->room_number,
                'room_floor' => $room->room_floor,
                'room_type' => $room->room_type,
                'price' => $room->room_type_price,
            ];
        }

        return $response->get200($results);
    }

    public function roomInformation(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        // How to do
        // 1. Get the room information
        // 2. Get the latest subscription paid date
        // 3. Get the residents information
        // 4. Check if the subscription is paid or not

        $rooms = Room::join('occupies', 'rooms.id_room', '=', 'occupies.id_room')
            ->join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->select('rooms.id_room', 'rooms.room_number', 'rooms.room_floor', 'room_types.room_type', 'occupies.subscription_model', 'room_types.room_type_price', 'occupies.id_occupy', 'room_types.id_room_type')
            ->where('occupies.id_user', $id_user)->where('occupies.status_occupy', 'occupied')
            ->where('occupies.check_out', null);

        if (!$rooms) {
            return $response->get404('Room');
        }

        $occupy = Occupied::where('id_user', $id_user)->where('check_out', null)->first();

        // dd($occupy->is_double_bed);

        $check_in = $occupy->check_in;
        $check_in = Carbon::parse($check_in)->format('d F Y');

        $rooms = $rooms->first();

        $monthCount;

        if ($rooms->first()->subscription_model == '1 month') {
            $monthCount = 1;
        } else if ($rooms->first()->subscription_model == '3 months') {
            $monthCount = 3;
        } else {
            $monthCount = 6;
        }

        
        // dd($monthCount);


        $additionalPrice;

            // if the room is double bed, then the price is additional 50% from the original price

        if ($occupy->is_double_bed == 'yes') {
            $additionalPrice = $rooms->room_type_price * 0.5;
        } else {
                $additionalPrice = 0;
        }

        $totalPrice = ($rooms->room_type_price + $additionalPrice) *  $monthCount;

        $subscriptionLatestDate = Subscription::where('id_occupy', $rooms->id_occupy)->latest('subscription_end_date')->first()->subscription_end_date;

        // convert to 12 January 2022

        $subscriptionLatestDate = Carbon::parse($subscriptionLatestDate)->format('d F Y');

        $residents = Residents::where('id_occupy', $rooms->id_occupy)->select('id_resident', 'resident_name', 'resident_phone_number', 'resident_nik')->get();

        $subscriptionStatus = Subscription::where('id_occupy', $rooms->id_occupy)->first()->subscription_status;

        $roomImages = RoomPhoto::where('id_room_type', $rooms->id_room_type)->select('room_photo')->get();

        // output is like

        // {
        //     "room_information" : {
        //         "id_room" : "uuid",
        //         "id_occupy" : "uuid",
        //         "room_number" : "string",
        //         "room_floor" : "string",
        //         "room_type" : "string",
        //         "subscription_model" : "string",
        //         "room_type_price" : "integer" // if room_type_price is for monthly, then total it according to subscription_model
        //     },
        //     "next_payment_date" : "subscription_end_date",
        //     "residents" : [
        //         {
        //             "id_resident" : "uuid",
        //             "full_name" : "string",
        //             "phone_number" : "string",
        //             "nik" : "string"
        //         }
        //     ],
        //     "subscription_status" : "subcription_status" // paid or not
        //     "image-room" : [
        //         {
        //             "image" : "km-dalam/km-dalam-a.jpg"
        //         }
        // ]
        // }

        //? Waiting for room_type_price

        $decrypted = [];

        foreach ($roomImages as $image) {
            $decrypted[] = 'storage/image/'.Crypt::decryptString($image->room_photo);
        }

        $results = [
            'room_information' => [
                'id_room' => $rooms->id_room,
                'id_occupy' => $rooms->id_occupy,
                'room_number' => $rooms->room_number,
                'room_floor' => $rooms->room_floor,
                'room_type' => $rooms->room_type,
                'subscription_model' => $rooms->subscription_model,
                'room_type_price' => $totalPrice,
                'double_bed_options' => $occupy->is_double_bed,
                'check_in' => $check_in
            ],
            'next_payment_date' => $subscriptionLatestDate,
            'residents' => $residents,
            'subscription_status' => $subscriptionStatus,
            'image-room' => $decrypted,
            'admin_phone_number' => '082213243739'
        ];

        return $response->get200($results);

    }

    public function listUserRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        // it only show about room that is occupied by the user

        $rooms = Room::join('occupies', 'rooms.id_room', '=', 'occupies.id_room')
            ->join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->select('rooms.id_room', 'rooms.room_number', 'rooms.room_floor', 'room_types.room_type', 'occupies.subscription_model', 'room_types.room_type_price', 'occupies.id_occupy')
            ->where('occupies.id_user', $id_user)->where('occupies.status_occupy', 'occupied')
            ->where('occupies.check_out', null)
            ->get();

        

        $results = [];

        foreach ($rooms as $room) {
            $subscriptionLatestDate = Subscription::where('id_occupy', $room->id_occupy)->latest('subscription_end_date')->first()->subscription_end_date;
            $subscriptionLatestDate = Carbon::parse($subscriptionLatestDate)->format('d F Y');

            // convert subscribe model to days

            $monthCount;
            if ($room->subscription_model == '1 month') {
                $monthCount = 1;
            } else if ($room->subscription_model == '3 months') {
                $monthCount = 3;
            } else if ($room->subscription_model == '6 months') {
                $monthCount = 6;
            } 

            $additionalPrice;

            // if the room is double bed, then the price is additional 50% from the original price

            if (Occupied::where('id_occupy', $room->id_occupy)->first()->is_double_bed == 'yes') {
                $additionalPrice = $room->room_type_price * 0.5;
            } else {
                $additionalPrice = 0;
            }

            $totalPrice = ($room->room_type_price + $additionalPrice) * $monthCount ;

            $results[] = [
                'id_room' => $room->id_room,
                'id_occupy' => $room->id_occupy,
                'room_number' => $room->room_number,
                'room_floor' => $room->room_floor,
                'room_type' => $room->room_type,
                'subscription_model' => $room->subscription_model,
                'room_type_price' => $totalPrice,
                'next_payment_date' => $subscriptionLatestDate
            ];
        }

        if (count($results) == 0) {
            return $response->get404('Room');
        }

        return $response->get200($results);
    }
}
