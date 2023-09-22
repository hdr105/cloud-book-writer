<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{   
    public function login(Request $request) {
        $request->merge(['email' => strtolower($request->email)]);
        
        $credentials = $request->only(['email', 'password']);

        $validator = Validator::make($credentials,[
            'email'    => 'required|email|max:255',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }

        $user = User::where('email', $request['email'])->first();
        if ($user) {
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    "success" => false,
                    "message" => "Credentials are not correct!",
                    "data"    => [],
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Login Successfully',
                    'data'    => [
                        "user" => $user,
                        "token" => "token here",
                    ],
                ]);
            } 
        } else {
            return response()->json([
                "success" => false,
                "message" => "User Not Found",
                "data"    => [],
            ]);
        }
    }
}
