<?php

namespace App\Http\Middleware;

use Closure;
use Log;
use Session;
use Pool;

class AuthenticateGame
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()) {
            $urlArr = explode('/', $request->fullUrl());
            $urlArr[count($urlArr) - 1];

            Session::flash('gameId', $urlArr[count($urlArr) - 1]);
            Session::flash('info','Please create an account to join this pool!');
            
            return redirect('/register');
        }
        return $next($request);
    }
}
