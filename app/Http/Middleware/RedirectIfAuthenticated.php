<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        if (!Auth::guard('buyerApi')->check()) {
            Session::flash('flash_message', 'You need to log in to access this page.');
            return Redirect::guest(route('buyerApi.login'));
        }
    
        if (!Auth::guard('merchantApi')->check()) {
            Session::flash('flash_message', 'You need to log in to access this page.');
            return Redirect::guest(route('merchantApi.login'));
        }

        // foreach ($guards as $guard) {
        //     if (Auth::guard($guard)->check()) {
        //         return redirect(RouteServiceProvider::HOME);
        //     }
        // }

        return $next($request);
    }
}
