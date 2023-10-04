<?php

namespace App\Exceptions;

use Exception;

class AuthorizationException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'You do not have the necessary permissions to perform this action.',
        ], 403);
    }
}
