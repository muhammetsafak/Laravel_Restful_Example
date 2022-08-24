<?php

namespace App\Http\Middleware;

use App\Classes\JWT;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class JWTAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->headers->get('Authorization');
        if(empty($auth)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        if(!\str_starts_with($auth, 'Bearer ')){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        if(JWT::decode(\substr($auth, 7)) === FALSE){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        $data = JWT::get();
        $user = User::where('username', $data['username'])->first();
        if(empty($user)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }

        return $next($request);
    }
}
