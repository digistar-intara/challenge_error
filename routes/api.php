<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Repository\ImageController;

//* USER CONTROLLERS
use App\Http\Controllers\User\Authentication\AuthenticationController;
use App\Http\Controllers\User\Room\RoomController;
use App\Http\Controllers\User\Room\RegisterController;
use App\Http\Controllers\User\Subscription\SubscribeController;
use App\Http\Controllers\User\Subscription\CheckoutController;

//* ADMIN CONTROLLERS
use App\Http\Controllers\Admin\Authentication\AuthenticationController as AdminAuthController;
use App\Http\Controllers\Admin\Room\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\Room\OccupyController as AdminOccupyController;
use App\Http\Controllers\Admin\Subscription\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\User\UserController as AdminUserController;  


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::controller(ImageController::class)->group(function () {
        Route::get('tes', 'imageGetTest');
    });
    //* NON TOKENABLE ROUTES
    Route::prefix('auth')->controller(AuthenticationController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('verify', 'verifyOTP');
        Route::post('resend-otp', 'resendOTP');
    });

    Route::prefix('admin')->group(function () {
        Route::prefix('auth')->controller(AdminAuthController::class)->group(function () {
            Route::post('login', 'login');
        });
        
    });

    //* TOKENABLE ROUTES

    Route::middleware('auth:sanctum', 'ability:user')->group(function () {
        Route::prefix('auth')->controller(AuthenticationController::class)->group(function () {
            Route::post('logout', 'logout');
        });

        Route::prefix('image')->controller(ImageController::class)->group(function () {
            Route::post('get', 'imageGet');
        });

        Route::prefix('room')->group(function(){
            Route::controller(RoomController::class)->group(function (){
                Route::post('list', 'listRoomAll');
                Route::post('information', 'roomInformation');
                Route::post('list-occupy', 'listUserRoom');
            });
            Route::prefix('register')->controller(RegisterController::class)->group(function (){
                Route::post('occupy', 'registerRoom');
                Route::post('resident', 'fillResident');
                Route::post('room-list', 'listRoomSlider');
                // Route::post('resident/as-user', 'fillResidentAsUser');
            });
        });

        Route::prefix('subscription')->group(function () {
            Route::controller(SubscribeController::class)->group(function () {
                Route::post('invoice', 'sendSubscriptionInvoice');
                Route::post('invoice/unpaid', 'sendSubscriptionInvoiceUnpaid');
            });
            Route::controller(CheckoutController::class)->group(function () {
                Route::post('checkout', 'checkout');
            });
        });
    });

    Route::prefix('admin')->middleware('auth:sanctum', 'ability:admin')->group(function () {
        Route::prefix('auth')->controller(AdminAuthController::class)->group(function () {
            Route::post('logout', 'logout');
        });
        Route::prefix('room')->controller(AdminRoomController::class)->group(function () {
            Route::post('list', 'listRoom');
        });
        Route::prefix('occupy')->controller(AdminOccupyController::class)->group(function () {
            Route::post('list', 'occupyList');
            Route::post('acception', 'occupyAcception');
        });
        Route::prefix('subscription')->controller(AdminSubscriptionController::class)->group(function () {
            Route::post('list', 'listSubscription');
            Route::post('acception', 'subscriptionAcception');
        });
        Route::prefix('subscription')->controller(ImageController::class)->group(function () {
            Route::post('invoice-image', 'imageGetSubscription');
        });
        Route::prefix('image')->controller(ImageController::class)->group(function () {
            Route::post('resident', 'imageGetResident');
        });
        Route::prefix('user')->controller(AdminUserController::class)->group(function () {
            Route::post('information', 'getInformationUser');
        });
    });
});
