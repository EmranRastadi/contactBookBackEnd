<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:users', ['except' => ['login']]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['seccess' => false, 'message' => 'invalid input!'], 401);
        }
        return $this->responceWithToken($token);
    }

    private function responceWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'message' => 'login',
        ]);
    }
}
