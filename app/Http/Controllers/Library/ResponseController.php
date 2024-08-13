<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    // Status Code 200 (GET)

    static function get200($data = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Data is retrieved successfully!',
            'data' => $data,
            'status_code' => '200'
        ], 200);
    }

    // Status Code 200 (DELETE)

    static function delete200()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Data is deleted successfully!',
            'status_code' => '200'
        ], 200);
    }

    // Status Code 201 (POST)

    static function post201($data = null)
    {
        if ($data == null) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data is created successfully!',
                'status_code' => '201'
            ], 201);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data is created successfully!',
            'data' => $data,
            'status_code' => '201'
        ], 201);
    }

    static function postLogin201($user = null, $token = null)
    {

        return response()->json([
            'status' => 'success',
            'message' => 'Data is created successfully!',
            'user' => $user,
            'access_token' => $token,
            'status_code' => '201'
        ], 201);
    }

    // Status Code 201 (PUT)

    static function put201($data = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Data is updated successfully!',
            'data' => $data,
            'status_code' => '201'
        ], 201);
    }

    // Status Code 400 (GET, POST, PUT, DELETE)

    static function get400()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Bad Request!',
            'status_code' => '400'
        ], 400);
    }

    static function getUser400(){
        return response()->json([
            'status' => 'error',
            'message' => 'User already existed!',
            'status_code' => '400'
        ], 400);
    }

    // Status Code 401 (GET, POST, PUT, DELETE)

    static function get401($message = null)
    {

        if ($message != null) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'status_code' => '401'
            ], 401);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized!',
            'status_code' => '401'
        ], 401);
    }

    // Status Code 403 (GET, POST, PUT, DELETE)

    static function get403()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Forbidden!',
            'status_code' => '403'
        ], 403);
    }

    // Status Code 404 (GET, POST, PUT, DELETE)

    static function get404($things)
    {
        return response()->json([
            'status' => 'error',
            'message' => $things.' Not Found!',
            'status_code' => '404'
        ], 404);
    }

    static function getList404($things, $data)
    {
        return response()->json([
            'status' => 'error',
            'message' => $things.' Not Found!',
            'data' => $data,
            'status_code' => '404'
        ], 404);
    }
    

    // Status Code 405 (GET, POST, PUT, DELETE)

    static function get405($data)
    {
        return response()->json([
            'status' => 'error',
            'message' => $data,
            'status_code' => '405'
        ], 405);
    }

    static function get409($data)
    {
        return response()->json([
            'status' => 'error',
            'message' => $data,
            'status_code' => '409'
        ], 409);
    }

    // Status Code 422 (GET, POST, PUT, DELETE)

    static function get422($details = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unprocessable Entity!',
            'details' => $details,
            'status_code' => '422'
        ], 422);
    }

    // Status Code 500 (GET, POST, PUT, DELETE)

    static function get500()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Internal Server Error!',
            'status_code' => '500'
        ], 500);
    }

    // Status Code 503 (GET, POST, PUT, DELETE)

    static function get503()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Service Unavailable!',
            'status_code' => '503'
        ], 503);
    }

    // Status Code 504 (GET, POST, PUT, DELETE)

    static function get504()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Gateway Timeout!',
            'status_code' => '504'
        ], 504);
    }

    // Status Code 505 (GET, POST, PUT, DELETE)

    static function get505()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'HTTP Version Not Supported!',
            'status_code' => '505'
        ], 505);
    }

    // Status Code 507 (GET, POST, PUT, DELETE)

    static function get507()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Insufficient Storage!',
            'status_code' => '507'
        ], 507);
    }

    // Status Code 511 (GET, POST, PUT, DELETE)

    static function get511()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Network Authentication Required!',
            'status_code' => '511'
        ], 511);
    }

    // Status Code 520 (GET, POST, PUT, DELETE)

    static function get520()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unknown Error!',
            'status_code' => '520'
        ], 520);
    }
}
