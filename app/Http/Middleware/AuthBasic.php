<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthBasic
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    
    public function handle(Request $request, Closure $next)
    {
        $userEnv = env('AUTH_BASIC_USER');
        $pwdEnv = env('AUTH_BASIC_PWD');

        $data = array(
            'message' => 'Auth failed',
            'code' => 401,
        );

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];

            if ($userEnv == $user && $pwdEnv == $password) {
                return $next($request);
            }else {
                return response()->json($data, $data['code']);
            }
        } else {
            return response()->json($data, $data['code']);
        }
    }
}
