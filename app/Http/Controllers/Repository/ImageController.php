<?php

namespace App\Http\Controllers\Repository;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Residents;
use App\Models\Subscription;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Library\ResponseController;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Http\Controllers\Security\TokenController;

class ImageController extends Controller
{
    public function imageGet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'type_photo' => 'required|string|in:user,nik,ktm',
            'permission' => 'required|string|in:public,private'
        ]);
  
        $response = new ResponseController();
  
        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }
  
        $id_user = $request->id_user;
  
        if (TokenController::tokenChecking($id_user) == false || !TokenController::tokenChecking($id_user)) {
            return $response->get401();
        }
  
        $user = User::where('id_user', $id_user)->first();
  
        if (!$user) {
            return $response->get404('User');
        }
  
        $user_image = $user->user_image;
        $nik_image = $user->nik_image;
        $ktm_image = $user->ktm_image;
  
        $decryptedFoto = Crypt::decryptString(
              match ($request->type_photo) {
                  'user' => $user_image,
                  'nik' => $nik_image,
                  'ktm' => $ktm_image,
              }
        );
  
      //   dd($decryptedFoto);
  
        $imagePath = $request->permission.'/image/'.$request->type_photo. '/' . $decryptedFoto;
  
        if (Storage::exists($imagePath)) {
            $file = Storage::get($imagePath);
  
            // //    Compress the image data using zlib
            // $compressedFile = gzcompress($file, 2);
  
            //    Determine the MIME type of the image (optional)
            $mimeType = Storage::mimeType($imagePath);
  
            //    Create a response with the compressed image bytes
            $response = new Response($file, 200);
            $response->header('Content-Type', $mimeType); //  Optional
            // dd($response);
            return $response;
        } else {
            return response()->json(['error' => 'Image not found'], 404);
        }
    }

    public function imageGetResident(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'id_resident' => 'required|uuid',   
            'type_photo' => 'required|string|in:user,nik,ktm',
            'permission' => 'required|string|in:public,private'
        ]);
  
        $response = new ResponseController();
  
        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }
  
        $id_user = $request->id_user;
  
        if (TokenController::tokenCheckingAdmin($id_user) == false || !TokenController::tokenCheckingAdmin($id_user)) {
            return $response->get401();
        }
  
        $resident = Residents::where('id_resident', $request->id_resident)->first();
  
        if (!$resident) {
            return $response->get200('null');
        }
  
        $user_image = $resident->resident_user_image;
        $nik_image = $resident->resident_nik_image;
        $ktm_image = $resident->resident_ktm_image;
  
        $decryptedFoto = Crypt::decryptString(
              match ($request->type_photo) {
                  'user' => $user_image,
                  'nik' => $nik_image,
                  'ktm' => $ktm_image,
              }
        );
  
      //   dd($decryptedFoto);
  
        $imagePath = $request->permission.'/image/'.$request->type_photo. '/' . $decryptedFoto;
  
        if (Storage::exists($imagePath)) {
            $file = Storage::get($imagePath);
  
            // //    Compress the image data using zlib
            // $compressedFile = gzcompress($file, 2);
  
            //    Determine the MIME type of the image (optional)
            $mimeType = Storage::mimeType($imagePath);
  
            //    Create a response with the compressed image bytes
            $response = new Response($file, 200);
            $response->header('Content-Type', $mimeType); //  Optional
            // dd($response);
            return $response;
        } else {
            return response()->json(['error' => 'Image not found'], 404);
        }

        
    }

    public function imageGetSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|uuid',
            'id_subscription' => 'required|uuid',
        ]);
  
        $response = new ResponseController();
  
        if ($validator->fails()) {
            return $response->get422($validator->errors());
        }
  
        $id_user = $request->id_user;
  
        if (TokenController::tokenCheckingAdmin($id_user) == false || !TokenController::tokenCheckingAdmin($id_user)) {
            return $response->get401();
        }
  
        $subscription = Subscription::where('id_subscription', $request->id_subscription)->first();
  
        if (!$subscription) {
            return $response->get200('null');
        }
  
        
  
      //   dd($decryptedFoto);

        $decryptedFoto = Crypt::decryptString($subscription->payment_receipt);
  
        $imagePath = 'private/image/payment_receipt/' . $decryptedFoto;
  
        if (Storage::exists($imagePath)) {
            $file = Storage::get($imagePath);
  
            // //    Compress the image data using zlib
            // $compressedFile = gzcompress($file, 2);
  
            //    Determine the MIME type of the image (optional)
            $mimeType = Storage::mimeType($imagePath);
  
            //    Create a response with the compressed image bytes
            $response = new Response($file, 200);
            $response->header('Content-Type', $mimeType); //  Optional
            // dd($response);
            return $response;
        } else {
            return response()->json(['error' => 'Image not found'], 404);
        }

        
    }

    // public function imageGetTest(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'id_user' => 'required|uuid',
    //         'type_photo' => 'required|string|in:user,nik,ktm',
    //         'permission' => 'required|string|in:public,private'
    //     ]);
    
    //     $response = new ResponseController();
    
    //     if ($validator->fails()) {
    //         return $response->get422($validator->errors());
    //     }
    
    //     $id_user = $request->id_user;
    
    //     $user = User::where('id_user', $id_user)->first();
    
    //     if (!$user) {
    //         return $response->get404('User');
    //     }
    
    //     $user_image = $user->user_image;
    //     $nik_image = $user->nik_image;
    //     $ktm_image = $user->ktm_image;
    
    //     $decryptedFoto = Crypt::decryptString(
    //         match ($request->type_photo) {
    //             'user' => $user_image,
    //             'nik' => $nik_image,
    //             'ktm' => $ktm_image,
    //         }
    //     );
    
    //     // Debugging
    //     // dd($decryptedFoto);
    
    //     $imagePath = $request->permission.'/image/'.$request->type_photo. '/' . $decryptedFoto;
    
    //     // Debugging
    //     // dd($imagePath);
    
    //     if (Storage::exists($imagePath)) {
    //         $file = Storage::get($imagePath);
    
    //         // // Compress the image data using zlib
    //         // $compressedFile = gzcompress($file, 2);
    
    //         // Determine the MIME type of the image (optional)
    //         $mimeType = Storage::mimeType($imagePath);
    
    //         // Create a response with the compressed image bytes
    //         $response = new Response($file, 200);
    //         $response->header('Content-Type', $mimeType); // Optional
    
    //         // Debugging
    //         // dd($response);
    //         return $response;
    //     } else {
    //         // Debugging
    //         // dd($imagePath);
    //         return response()->json(['error' => 'Image not found'], 404);
    //     }
    // }
}    
