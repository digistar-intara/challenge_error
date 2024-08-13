<?php

namespace App\Http\Controllers\Admin\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function listSubscription(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'id_occupy' => 'required|uuid'
        ]);
    
        $response = new ResponseController();
    
        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }
    
        $id_user = $request->id_user;
    
        if (TokenController::tokenCheckingAdmin($id_user) == false) {
            return $response->get401();
        }
    
        $id_occupy = $request->id_occupy;
    
        $subscription = Subscription::join('occupies', 'subscriptions.id_occupy', '=', 'occupies.id_occupy')
            ->join('rooms', 'occupies.id_room', '=', 'rooms.id_room')
            ->join('room_types', 'rooms.id_room_type', '=', 'room_types.id_room_type')
            ->where('subscriptions.id_occupy', $id_occupy)
            ->select(
                'subscriptions.id_subscription',
                'subscriptions.subscription_status',
                'subscriptions.subscription_date',
                'subscriptions.subscription_end_date',
                'occupies.subscription_model',
                'occupies.is_double_bed',
                'room_types.room_type_price'
            )
            ->get();
    
        $grandTotal = 0;

        
    
        foreach ($subscription as $sub) {
            // Calculate price for each subscription
            $price = $sub->room_type_price;
            if ($sub->is_double_bed == 'yes') {
                $price *= 1.5; // Add 50% if double bed
            }

            // dd($price, $sub->subscription_model);

            $monthCount = 0;
    
            // Calculate month count
            if ($sub->subscription_model == '1 month') {
                $monthCount = 1;
            } else if ($sub->subscription_model == '3 months') {
                $monthCount = 3;
            } else if ($sub->subscription_model == '6 months') {
                $monthCount = 6;
            }
    
            // Update grand total
            $grandTotal += $price * $monthCount;
        }
    
        // Prepare subscription list with grand total
        $subscriptionList = [];
        foreach ($subscription as $sub) {
            $subscriptionDate = date('d F Y', strtotime($sub->subscription_date));
            $subscriptionEndDate = date('d F Y', strtotime($sub->subscription_end_date));
            // grand total is separated every 3 digits from back
            $grandTotal = number_format($grandTotal, 0, ',', '.');
            $subscriptionList[] = [
                'id_subscription' => $sub->id_subscription,
                'subscription_status' => $sub->subscription_status,
                'subscription_start_date' => $subscriptionDate,
                'subscription_end_date' => $subscriptionEndDate,
                'grand_total' => 'Rp '.$grandTotal // Include the calculated grand total
            ];
        }
    
        return $response->get200($subscriptionList);
    }
    

    public function subscriptionAcception(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'id_subscription' => 'required|uuid',
            'subscription_status' => 'required|in:paid,rejected'
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if (TokenController::tokenCheckingAdmin($id_user) == false) {
            return $response->get401();
        }

        $id_subscription = $request->id_subscription;

        $subscription = Subscription::where('id_subscription', $id_subscription)->where('subscription_status', 'pending')->first();

        if(!$subscription){
            return $response->get404('Subscription');
        }

        $subscription->subscription_status = $request->subscription_status;

        $subscription->save();

        return $response->post201('Subscription status updated');
    }
}
