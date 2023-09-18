<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) {
        $userEnv = env('AUTH_BASIC_USER');
        $pwdEnv = env('AUTH_BASIC_PWD');

        $username = $request->input('username');
        $pwd = $request->input('password');

        if ( $username === null && $pwd === null ) return response([ 'status' => 'error', 'message' => 'No se enviaron las credenciales!', ], 500);

        if ( $userEnv !== $username ) return response([ 'status' => 'error', 'message' => 'Username incorrecto!', ], 500);
        
        if ( $pwdEnv !== $pwd) return response([ 'status' => 'error', 'message' => 'Contraseña incorrecta!', ], 500);
        
        return response([
            'status' => 'success',
            'user' => [
                'nickname' => 'Vento Manager',
                'username' => $username
            ],
            'token' => 'Basic '.base64_encode($username . ":" . $pwd),
        ], 200);
    }
}
