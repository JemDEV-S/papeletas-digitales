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
        'api/firma-peru/param',
        'api/firma-peru/upload/*',
        'api/firma-peru/document/*',
        'api/firma-peru/signed-document/*',
    ];
}