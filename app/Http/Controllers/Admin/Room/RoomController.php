<?php

namespace App\Http\Controllers\Admin\Room;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Occupied;
use App\Models\Residents;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    public function listRoom(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'room_floor' => 'required|in:1,2,3,all',
        ]);
    
        $response = new ResponseController();
    
        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }
    
        $id_user = $request->id_user;
    
        if (TokenController::tokenCheckingAdmin($id_user) == false) {
            return $response->get401();
        }
    
        $roomsQuery = Room::leftJoin('occupies', 'rooms.id_room', '=', 'occupies.id_room')
            ->join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->leftJoin('residents', 'occupies.id_occupy', '=', 'residents.id_occupy')
            ->orderBy('rooms.room_number', 'asc')
            ->select(
                'rooms.room_number',
                'rooms.room_floor',
                'occupies.status_occupy',
                'occupies.is_double_bed',
                'occupies.check_in',
                'residents.id_resident',
                'residents.resident_name',
                'residents.resident_phone_number',
                'residents.resident_address',
                'residents.resident_nik',
                'room_types.room_type'
            );
    
        if ($request->room_floor != 'all') {
            $roomsQuery->where('rooms.room_floor', $request->room_floor);
        }
    
        $rooms = $roomsQuery->get();
    
        $roomData = [];
    
        foreach ($rooms as $room) {
            $roomNumber = $room->room_number;
            $roomFloor = $room->room_floor;
            $roomStatus = $room->status_occupy ?: 'empty';
            $isDoubleBed = $room->is_double_bed ?: 'null';
            $roomType = $room->room_type ?: 'null';
            $checkIn = $room->check_in ? date('d F Y', strtotime($room->check_in)) : 'null';
    
            // Initialize residents data
            $residentsData = [];
    
            // Check if room is not empty and resident is not null
            if ($roomStatus != 'empty' && $room->id_resident !== null) {
                // Check if room already exists in $roomData array
                $roomExists = false;
                foreach ($roomData as &$roomItem) {
                    if ($roomItem['room_number'] == $roomNumber && $roomItem['room_floor'] == $roomFloor) {
                        $roomExists = true;
                        // Add resident to existing room
                        $roomItem['residents'][] = [
                            'id_resident' => $room->id_resident,
                            'full_name' => $room->resident_name,
                            'phone_number' => $room->resident_phone_number,
                            'address' => $room->resident_address,
                            'nik' => $room->resident_nik
                        ];
                        break;
                    }
                }
    
                // If room doesn't exist in $roomData array, create new entry
                if (!$roomExists) {
                    $residentsData[] = [
                        'id_resident' => $room->id_resident,
                        'full_name' => $room->resident_name,
                        'phone_number' => $room->resident_phone_number,
                        'address' => $room->resident_address,
                        'nik' => $room->resident_nik
                    ];
    
                    $roomData[] = [
                        'room_number' => $roomNumber,
                        'room_floor' => $roomFloor,
                        'room_status' => $roomStatus,
                        'is_double_bed' => $isDoubleBed,
                        'room_type' => $roomType,
                        'check_in' => $checkIn,
                        'residents' => $residentsData
                    ];
                }
            } else {
                // For empty rooms or rooms without residents, set residents data to null
                $residentsData[] = [
                    'id_resident' => 'null',
                    'full_name' => 'null',
                    'phone_number' => 'null',
                    'address' => 'null',
                    'nik' => 'null'
                ];
    
                $roomData[] = [
                    'room_number' => $roomNumber,
                    'room_floor' => $roomFloor,
                    'room_status' => $roomStatus,
                    'is_double_bed' => $isDoubleBed,
                    'room_type' => $roomType,
                    'check_in' => $checkIn,
                    'residents' => $residentsData
                ];
            }
        }
    
        return $response->get200($roomData);
    }
    
    
}
