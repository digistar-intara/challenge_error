<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
// use App\Exceptions\ApiExceptionHandler;
use Throwable;
use App\Http\Controllers\Library\ResponseController;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
       // if token unavailable in header or invalid

       // if user access at web, sent to login page, but if user access at api, sent to json response

        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {

                // if route with prefix api
                if ($request->is('api/*')) {
                    return response()->json([
                        'status' => 'Error',
                        'message' => 'Unauthorized',
                        'status_code' => 401
                    ], 401);
                } 
                    return ResponseController::get401(); // BAGIAN YANG DIUBAH
                

                // if ($request->expectsJson()) {
                //     return response()->json([
                //         'status' => 'Error',
                //         'message' => 'Unauthorized',
                //         'status_code' => 401
                //     ], 401);
                // }
                // return redirect()->guest(route('login'));
            }
        });






        //  $this->renderable(function (Throwable $e, $request) {
        //       if ($e instanceof \Illuminate\Auth\AuthenticationException) {
        //         return response()->json([
        //             'status' => 'Error',
        //             'message' => 'Unauthorized',
        //             'status_code' => 401
        //         ], 401);
        //       }
        //  });
        
    }
    
}
