<?php

namespace App\Http\Controllers\User\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Room;
use App\Models\Occupied;
use App\Http\Controllers\Library\ResponseController;
use App\Http\Controllers\Security\TokenController;
// use Hash and Crypt
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
Use Carbon\Carbon;

class SubscribeController extends Controller
{
    public function sendSubscriptionInvoice(Request $request){
        $validator = Validator::make($request->all(), [
            'id_uer' => 'required|uuid',
            'id_ocupy' => 'required|uuid',
            'payment_receipt' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        $subscription = Subscription::where('id_user', $id_user)->where('id_occupy', $request->id_occupy)->where('subscription_status', 'unpaid')->orderBy('created_at', 'desc')->first();

        if($subscription == null){
            return $response->get422('You don\'t have any unpaid subscription');
        }

        $subscription->subscription_status = 'pending';

        if($request->hasFile('payment_receipt')){
            $paymentInvoice = $request->file('payment_receipt');
            $paymentInvoiceName = $this->sanitizeFileName(Hash::make($paymentInvoice->getClientOriginalName())) . '.' . $paymentInvoice->getClientOriginalExtension();
            // move file to public folder
            $paymentInvoice->storeAs('private/image/payment_receipt', $paymentInvoiceName); // store to storage
            // set ktm_image with file name
            $subscription->payment_receipt = Crypt::encryptString($paymentInvoiceName);
        }

        $subscription->save();

        return $response->post201('Tagihan telah dilunasi');
    }

    public function sendSubscriptionInvoiceUnpaid(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'id_ocupy' => 'required|uuid',
        ]);

        $response = new ResponseController();

        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }

        $id_user = $request->id_user;

        if(TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)){
            return ResponseController::get401();
        }

        // check if user has been checked out or not
        $occupied = Occupied::where('id_user', $id_user)->where('id_occupy', $request->id_occupy)->where('check_out', null)->where('occupies.status_occupy', 'occupied')->first();

        // for cases that user has been checked out or user subscribe it for the first time
        if($occupied == null){
            return $response->get422('You can\'t subscribe because you have been checked out');
        }

        $subscriptiModel = $occupied->subscription_model;

        $aMonth = 30;
        $threeMonths = 90;
        $sixMonths = 180;

        $monthsCount;

        if($subscriptiModel == '1 month'){
            $monthsCount = $aMonth;
        } else if($subscriptiModel == '3 months'){
            $monthsCount = $threeMonths;
        } else if($subscriptiModel == '6 months'){
            $monthsCount = $sixMonths;
        }

        $subscriptionLatestData = Subscription::where('id_user', $id_user)->where('id_occupy', $request->id_occupy)->orderBy('created_at', 'desc')->where('subscription_status', 'paid')->first();

        if($subscriptionLatestData == null){
            $dateStartSubscription = $occupied->check_in; // if user hasn't been subscribed before then the subscription start date is the check in date
         } else {
            $dateStartSubscription = $subscriptionLatestData->subscription_end_date; // if user has been subscribed before then the subscription start date is the last subscription end date
        }

        $nextPaymentDate = Carbon::parse($dateStartSubscription)->addDays($monthsCount);

        if(Carbon::now() < $nextPaymentDate && Carbon::now() > $dateStartSubscription && $dateStartSubscription != $occupied->check_in){
            return $response->get422('You still in the subscription period');
        }

        // format date to 'Y-m-d'
        $endDate = Carbon::parse($dateStartSubscription)->addDays($monthsCount)->format('Y-m-d');

        $subscription = new Subscription();
        $subscription->id_user = $id_user;
        $subscription->id_occupy = $request->id_occupy;
        $subscription->subscription_status = 'unpaid';
        $subscription->subscription_date = $dateStartSubscription;
        $subscription->subscription_end_date = $endDate; // date format is 'Y-m-d'
        $subscription->payment_receipt = null;
        $subscription->save();

        return $response->post201('Tagihan baru telah dikirim');
    }

    public function sanitizeFileName($fileName){
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $fileName);
        return $fileName;
    }
}
