<?php

namespace App\Exceptions;

// use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

class ApiExceptionHandler extends Handler
{
    public function render($request, Throwable $exception)
{
    if ($exception instanceof AuthenticationException) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    if ($exception instanceof AuthorizationException) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    return parent::render($request, $exception);
}
}
