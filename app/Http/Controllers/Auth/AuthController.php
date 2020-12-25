<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    //
    protected $user;

    public function __construct()
    {
        $this->user = new User;
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'password' => 'required|string|min:6',
            ]);
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->messages()->toArray()
            ], 401);
        }

        $check_email = $this->user->where('email', $request->email)->count();
        if ($check_email > 0) {
            return response()->json([
                'success' => false,
                'message' => 'this email already exist !'
            ], 401);
        }

        $registerCom = $this->user::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        if ($registerCom) {
            return $this->login($request);
        }
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->only('email', 'password'),
            [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->messages()->toArray()
            ], 401);
        }

        $input = $request->only('email', 'password');
        $jwt_token = null;


        if (!$jwt_token = auth('users')->attempt($input)) {
            return response()->json([
                'success' => false,
                'message' => 'not register!'
            ], 400);
        }
        return response()->json([
            'success' => true,
            'token' => $jwt_token
        ], 200);

    }
}
