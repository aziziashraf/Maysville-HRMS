<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Client
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('authorization');
        //dd(env('APP_API_KEY'));
        if(!empty($authorization) && $authorization == env('APP_API_KEY')){
            return $next($request);
        }else{
            return response()->json('Client Unauthenticated.');
        }
    }
}
