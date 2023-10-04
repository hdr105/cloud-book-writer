<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/api/v1/auth/*',
        '/api/v1/books/*',
        '/api/v1/section/*',
        '/api/v1/subsection/*',

        // '/api/v1/books/add',
        // '/api/v1/books/edit/*',        
        // '/api/v1/books/delete/*',        
    ];
}
