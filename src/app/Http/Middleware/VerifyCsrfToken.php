<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        if(($request->getPathInfo() == '/login' || $request->getPathInfo() == '/register') && $request->method() == "POST"){
            return $next($request);
        }
        return parent::handle($request, $next);
    }
}
