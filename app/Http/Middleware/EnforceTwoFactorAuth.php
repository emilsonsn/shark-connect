<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceTwoFactorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if($request->user()->hasPermissionTo('manage-users')) {
            return $next($request);
        }

        if ($request->user() && ! $request->user()->two_factor_secret) {
            //if route isnt profile.show, redirect to profile.show

            if ($request->route()->getName() !== 'profile.show') {
                return redirect()->route('profile.show');
            }
            
        }

        return $next($request);
    }
}
