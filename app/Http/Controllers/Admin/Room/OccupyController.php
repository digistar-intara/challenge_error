<?php

namespace App\Http\Controllers\Admin\Room;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Occupied;
use App\Models\Residents;
use App\Models\Subscription;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class OccupyController extends Controller
{
    public function occupyList(Request $request){
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
    
        $occupied = Occupied::join('rooms', 'occupies.id_room', '=', 'rooms.id_room')
            ->join('residents', 'occupies.id_occupy', '=', 'residents.id_occupy')
            ->select('rooms.room_number', 'rooms.room_floor', 'occupies.status_occupy', 'occupies.is_double_bed', 'occupies.check_in', 'residents.id_resident', 'residents.resident_name', 'residents.resident_phone_number', 'residents.resident_nik', 'residents.resident_address', 'occupies.created_at', 'occupies.id_occupy', 'occupies.check_out')
            ->get();
    
        $occupiedData = [];
    
        foreach ($occupied as $data) {
    
            // date must be like 12 January 2024
            $orderDate = date('d F Y', strtotime($data->created_at));
            $checkInDate = date('d F Y', strtotime($data->check_in));
            $checkOutDate = $data->check_out ? date('d F Y', strtotime($data->check_out)) : "null";
    
            $occupiedData[$data->id_occupy] = [
                'id_occupy' => $data->id_occupy,
                'room_number' => $data->room_number,
                'room_floor' => $data->room_floor,
                'order_date' => $orderDate,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'status_occupy' => $data->status_occupy,
                'residents' => []
            ];
        }
    
        foreach ($occupied as $data) {
            $resident = [
                'id_resident' => $data->id_resident,
                'full_name' => $data->resident_name,
                'phone_number' => $data->resident_phone_number,
                'address' => $data->resident_address,
                'nik' => $data->resident_nik
            ];
    
            $occupiedData[$data->id_occupy]['residents'][] = $resident;
        }
    
        // Remove duplicates from residents list
        foreach ($occupiedData as &$data) {
            $data['residents'] = array_unique($data['residents'], SORT_REGULAR);
        }
    
        // Filter out entries with status not 'pending' or 'occupied'
        $occupiedData = array_filter($occupiedData, function ($value) {
            return $value['status_occupy'] == 'pending' || $value['status_occupy'] == 'occupied';
        });
    
        return $response->get200(array_values($occupiedData)); // Re-index the array
    }
    
    
    public function occupyAcception(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'id_occupy' => 'required|uuid',
            'status_occupy' => 'required|string|in:occupied,checkout,rejected'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if (TokenController::tokenCheckingAdmin($id_user) == false) {
            return $response->get401();
        }

        $occupy;

        $occupyPending = Occupied::where('id_occupy', $request->id_occupy)->where('status_occupy', 'pending')->first();

        $occupyAccept = Occupied::where('id_occupy', $request->id_occupy)->where('status_occupy', 'occupied')->first();

        if ($request->status_occupy == 'occupied' || $request->status_occupy == 'rejected'){
            $occupy = $occupyPending;
        } else if ($request->status_occupy == 'checkout'){
            $occupy = $occupyAccept;
        }

        if(!$occupy){
            return $response->get404('Occupied');
        }

        $occupy->status_occupy = $request->status_occupy;

        if ($request->status_occupy == 'checkout'){
            $occupy->check_out = date('Y-m-d');
        }

        $occupy->save();

        // create unpaid subscription if status is occupied

        // first get month count
        $subscriptionModel = $occupy->subscription_model;
        $dayCount = 0;

        if ($subscriptionModel == '1 month') {
            $dayCount = 30;
        } else if ($subscriptionModel == '3 months') {
            $dayCount = 90;
        } else if ($subscriptionModel == '6 months') {
            $dayCount = 180;
        }

        $subscription = new Subscription();
        $subscription->id_user = $occupy->id_user;
        $subscription->id_occupy = $occupy->id_occupy;
        $subscription->subscription_status = 'unpaid';
        $subscription->subscription_date = date('Y-m-d');
        $subscription->subscription_end_date = Carbon::parse($subscription->subscription_date)->addDays($dayCount);
        $subscription->payment_receipt = null;
        $subscription->save();

        return $response->post201('Occupied status updated');
    }
}
