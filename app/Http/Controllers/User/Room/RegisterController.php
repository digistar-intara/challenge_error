<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Occupied;
use App\Models\Residents;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomPhoto;
use App\Models\User;
use App\Http\Controllers\Library\ResponseContgoller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ThirdParty\WAFonnteController;

class RegisterController extends Controller
{
    public function listRoomSlider(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid|exists:users,id_usr',
            'id_room' => 'required|uuid|exists:rooms,id_rom'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        $room = Room::where('id_oom', $request->id_room)->first();

        if(!$room){
            return $response->get404('Room not found');
        }

        $roomType = RoomType::where('id_room_type', $room->id_room_type)->first();

        $roomPhoto = RoomPhoto::where('id_room_type', $room->id_room_type)->get();

        $decryptRoomPhoto = $roomPhoto->map(function($photo){
            return [
                'room_photo' => 'storage/image/'.Crypt::decryptString($photo->room_photo)
            ];
        });

        $room = [
            'id_room' => $room->id_room,
            'room_number' => $room->room_number,
            'room_floor' => $room->room_floor,
            'room_type' => $roomType->room_type,
            'price' => $roomType->room_type_price,
        ];

        return $response->get200([
            'room' => $room,
            'room_photo' => $decryptRoomPhoto
        ]);
    }

    public function registerRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid|exists:users,id_user',
            'id_room' => 'required|uuid|exists:rooms,id_room',
            'check_in' => 'required|date', // like 15 Januari 2022
            'subscription_model' => 'required|in:1 month,3 months,6 months',
            'is_double_bed' => 'required|in:yes,no'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        // if there's room that has been occupied and still not check out, then it can't be occupied again

        $occupy = Occupied::where('id_room', $request->id_room)->where('check_out', null)->first();

        if($occupy){
            return $response->get409('Room has been occupied');
        }

        $formatedCheckInDateAccordingMySQL = date('Y-m-d', strtotime($request->check_in));

        $occupy = new Occupied();
        $occupy->id_user = $id_user;
        $occupy->id_room = $request->id_room;
        $occupy->check_in = $formatedCheckInDateAccordingMySQL;
        $occupy->subscription_model = $request->subscription_model;
        $occupy->status_occupy = 'pending';
        $occupy->is_double_bed = $request->is_double_bed;

        $occupy->save();

        $id_occupy = $occupy->id_occupy;

        $this->fillResidentAsUser($id_user, $id_occupy);

        return $response->post201([
            'id_occupy' => $id_occupy,
            'message' => 'Room has been registered successfully.'
        ]);

    }

    public function fillResident(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid|exists:users,id_user',
            'id_occupy' => 'required|uuid|exists:occupies,id_occupy',
            'full_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'nik' => 'required|string|numeric|digits:16',
            'phone_number' => 'required|string|numeric|digits_between:9,13|unique:residents,resident_phone_number',
            'address' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'nik_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'ktm_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'user_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        // first, check if the user has been registered as resident
        // second, check room capacity
        // third, check if another resident has been registered in the same room and it's full or not according room capacity
        // forth, check if another resident has been registered but different id_user and id_occupy
        // fourth, fill the resident data

        $resident = Residents::where('id_user', $id_user)->where('id_occupy', $request->id_occupy)->get();

        $roomCapacity = Occupied::where('id_occupy', $request->id_occupy)->first()->is_double_bed;

        $residentMaxCount;

        if($roomCapacity == 'yes'){
            $residentMaxCount = 2;
        } else {
            $residentMaxCount = 1;
        }

        $occupy = Occupied::where('id_occupy', $request->id_occupy)->where('check_out', null)->where('status_occupy', 'pending')->first();

        if(!$occupy){
            $this->discardOccupy($request->id_occupy);
            return $response->get404('Room has been checked out');
        }

        $resident = Residents::where('id_occupy', $request->id_occupy)->get();

        if($resident->count() >= $residentMaxCount){
            $this->discardOccupy($request->id_occupy);
            return $response->get409('Room is full');
        }

        // if there's a different id_user in same id_occupy, then it can't be registered

        foreach($resident as $r){
            if($r->id_user != $id_user){
                $this->discardOccupy($request->id_occupy);
                return $response->get409('Another user has been registered in the same room');
            }
        }

        // if user in account will register as resident, can fill up too but not uploading file again

        // if ($resident->resident_nik == $request->nik) {
        //     return $response->get409('NIK has been registered');
        // }

        $resident = new Residents();
        $resident->id_user = $id_user;
        $resident->id_occupy = $request->id_occupy;
        $resident->resident_name = $request->full_name;
        $resident->resident_nik = $request->nik;
        $resident->resident_phone_number = $request->phone_number;
        $resident->resident_address = $request->address;


            if ($request->hasFile('user_image')) {
                $userImage = $request->file('user_image');
                $userImageName = $this->sanitizeFileName(Hash::make($userImage->getClientOriginalName())) . '.' . $userImage->getClientOriginalExtension();
                // move file to public folder
                $userImage->storeAs('private/image/user', $userImageName);
                // set user_image with file name
                $resident->resident_user_image = Crypt::encryptString($userImageName);
            }

            if ($request->hasFile('nik_image')) {
                $nikImage = $request->file('nik_image');
                $nikImageName = $this->sanitizeFileName(Hash::make($nikImage->getClientOriginalName())) . '.' . $nikImage->getClientOriginalExtension();
                // move file to public folder
                $nikImage->storeAs('private/image/nik', $nikImageName); // store to storage
                // set nik_image with file name
                $resident->resident_nik_image = Crypt::encryptString($nikImageName);
            }

            if ($request->hasFile('ktm_image')) {
                $ktmImage = $request->file('ktm_image');
                $ktmImageName = $this->sanitizeFileName(Hash::make($ktmImage->getClientOriginalName())) . '.' . $ktmImage->getClientOriginalExtension();
                // move file to public folder
                $ktmImage->storeAs('private/image/ktm', $ktmImageName); // store to storage
                // set ktm_image with file name
                $resident->resident_ktm_image = Crypt::encryptString($ktmImageName);
            }


        $resident->save();

        $wafonnte = new WAFonnteController();
        $room = Room::where('id_room', $occupy->id_room)->first();
        $roomNumber = Room::where('id_room', $occupy->id_room)->first()->room_number;
        $roomFloor = Room::where('id_room', $occupy->id_room)->first()->room_floor;
        $roomType = RoomType::where('id_room_type', $room->id_room_type)->first()->room_type;
        $roomCapacity = $residentMaxCount;
        $checkIn = $occupy->check_in;
        $body = "Anda telah terdaftar sebagai penghuni tempat kost kami dengan detail sebagai berikut:\n\nNama: ".$request->full_name."\nNomor Kamar: ".$roomNumber."\nLantai: ".$roomFloor."\nTipe Kamar: ".$roomType."\nKapasitas Kamar: ".$roomCapacity." orang\nTanggal Check In: ".$checkIn."\n\nTerima kasih telah mempercayakan tempat kost kami sebagai tempat tinggal Anda. Semoga Anda betah dan nyaman tinggal di tempat kost kami. Jika ada keluhan atau pertanyaan, jangan ragu untuk menghubungi kami. Terima kasih.";
        dd($body);
        $wafonnte->sendMessage($request->full_name, $body, $request->phone_number);

        return $response->post201(
            'Resident has been registered successfully.'
        );
    }

    private function fillResidentAsUser($id_user, $id_occupy){

        $resident = Residents::where('id_user', $id_user)->where('id_occupy', $id_occupy)->get();

        $roomCapacity = Occupied::where('id_occupy', $id_occupy)->first()->is_double_bed;

        $residentMaxCount;

        if($roomCapacity == 'yes'){
            $residentMaxCount = 2;
        } else {
            $residentMaxCount = 1;
        }

        // check room capacity from room type and compare it with the number of residents in the same room

        $occupy = Occupied::where('id_occupy', $id_occupy)->where('check_out', null)->first();
        $room = Room::where('id_room', $occupy->id_room)->first();
        // dd($room);
        $room_type = RoomType::where('id_room_type', $room->id_room_type)->first();

        if(!$occupy){
            return $response->get404('Room has been checked out');
        }

        $resident = Residents::where('id_occupy', $id_occupy)->get();


        if($resident->count() >= $residentMaxCount){
            return $response->get409('Room is full');
        }

        // if there's a different id_user in same id_occupy, then it can't be registered

        foreach($resident as $r){
            if($r->id_user != $id_user){
                return $response->get409('Another user has been registered in the same room');
            }
        }

        // if ($resident->resident_nik == $nik) {
        //     return $response->get409('NIK has been registered');
        // }

        // if user in account will register as resident, can fill up too but not uploading file again

        $existedUser = User::where('id_user', $id_user)->first();

        // dd($existedUser);

        $resident = new Residents();
        $resident->id_user = $existedUser->id_user;
        $resident->id_occupy = $id_occupy;
        $resident->resident_name = $existedUser->full_name;
        $resident->resident_nik = $existedUser->nik;
        $resident->resident_phone_number = $existedUser->phone_number;
        $resident->resident_address = $existedUser->address;
        $resident->resident_user_image = $existedUser->user_image;
        $resident->resident_nik_image = $existedUser->nik_image;
        $resident->resident_ktm_image = $existedUser->ktm_image;


        return true;
    }

    private function discardOccupy($id_occupy){
        $resident = Residents::where('id_ocupy', $id_occupy)->first();

        $occupy = Occupied::where('id_occupy', $id_occupy)->first();

    }

    private function sanitizeFileName($fileName){
        return preg_replace('/[^a-zA-Z0-9.]/', '_', $fileName);
    }

}
