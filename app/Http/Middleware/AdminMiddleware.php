<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect('login');
        }

        $userRole = $request->user()->role->name ?? null;

        if (!in_array($userRole, ['admin', 'jefe_rrhh'])) {
            abort(403, 'No tiene permisos de administración para acceder a esta sección.');
        }

        return $next($request);
    }
}