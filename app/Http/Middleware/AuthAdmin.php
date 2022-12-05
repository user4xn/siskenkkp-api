<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthAdmin extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if($user->role_id !=  1){
            return response()->json([
                'status' => 'failed', 
                'code' => 401,
                'message' => 'Unauthorized.' 
            ], 401);
        }
        
        $request->user = $user;

        return $next($request);       
    }
}
