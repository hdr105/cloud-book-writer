<?php

namespace App\Http\Controllers\Api\v1;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\EmailServices;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{   
    // API for LOGIN (OK)
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
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => "User not Found!",
                'data'    => [],
            ]);
        } else {
            $token = $user->createToken('MyAppToken22')->plainTextToken;
    
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
                            "token" => $token,
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

    // API for SIGNUP (OK)
    public function signup(Request $request){
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }

        // Create a new user with the provided data
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign the "Collaborator" role if it exists
        $collaboratorRole = Role::where('name', 'Collaborator')->first();
        if ($collaboratorRole) {
            $user->assignRole($collaboratorRole);
        }

        // Fire the Registered event
        event(new Registered($user));

        // Generate an access token for the user
        $token = $user->createToken('MyAppToken')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Signup Successfully',
            'data'    => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    // PassChange-API-1 to send OTP via email (OK)
    public function sendResetOtp(Request $request)
    {

        $credentials = $request->only(['email']);

        // Validate the request data
        $validator = Validator::make($credentials, [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }

        // Generate a random OTP
        $otp = mt_rand(100000, 999999);

        // Store the OTP in the database
        DB::table('password_reset_tokens')->updateOrInsert(['email' => $request->email], [
            'email' => $request->email,
            'otp' => $otp,
            'created_at' => Carbon::now(),
        ]);

        // Prepare email details
        $emailSubject = 'Your one-time password (OTP)';
        $emailBody = "Your OTP is: $otp";

        $emailDetails = [
            'subject' => $emailSubject,
            'body' => $emailBody,
        ];

        // Send the email
        try {
            Mail::to($request->email)->send(new EmailServices($emailDetails));

            return response()->json([
                'success' => true,
                'message' => "Reset OTP $otp sent to your email '{$request->email}' successfully!",
                'data'    => [],
            ]);
        } catch (\Exception $e) {
            // Handle email sending failure
            return response()->json([
                'success' => false,
                'message' => "Reset OTP not sent to your email!",
                'data'    => [],
            ]);
        }
    }

    // PassChange-API-2 to verify sent otp (OK)
    public function verifyOtp(Request $request) {
        $credentials = $request->only(['email','otp']);

        $validator = Validator::make($credentials,[
            'email' => 'required|email|exists:users',
            'otp'   => 'required|integer|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }

        $passwordReset = PasswordReset::where([
                    'email' => $request->email, 
                    'otp' => $request->otp
                ])->first();
        if ($passwordReset) {

            $otpCreateTime = Carbon::parse($passwordReset->created_at);
            $now = Carbon::now();
            $diffInMinutes = $now->diffInMinutes($otpCreateTime);
            if ( $diffInMinutes < 5) {
                if ( $passwordReset->otp == $request->otp) {
                    return response()->json([
                        "success" => true,
                        "message" => "OTP verified!",
                        "data"    => [],
                    ]);
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => "Invalid OTP!",
                        "data"    => [],
                    ]);
                }
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "OTP Expired!",
                    "data"    => [],
                ]);
            }
        } else {
            return response()->json([
                "success" => false,
                "message" => "No OTP Found!",
                "data"    => [],
            ]);
        }
    }

    // PassChange-API-3 to reset Password (OK)
    public function reset(Request $request) {
        $credentials = $request->only(['email','password']);

        $validator = Validator::make($credentials,[
            'email' => 'required|email|exists:users',
            'password' => 'required| min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        } else {


            $passwordReset = PasswordReset::where([
                'email' => $request->email, 
            ])->first();


            // $passwordReset = Password::sendResetLink([
            //     'email' => $request->email,
            // ])->first();




            if ($passwordReset) {

                $otpCreateTime = Carbon::parse($passwordReset->created_at);
                $now = Carbon::now();
                $diffInMinutes = $now->diffInMinutes($otpCreateTime);

                if ( $diffInMinutes < 5) {
                    User::where('email', $request->email)->update([
                        'password' => Hash::make($request->password)
                    ]);
                    // (new EmailService)->passwordReset([] , $request , "emailTemplates/passwordReset");
                    DB::table('password_reset_tokens')->where([
                        'email' => $request->email
                    ])->delete();
                    
                    return response()->json([
                        'success' => true,
                        'message' => "Password changed successfully!",
                        'data'    => [],
                    ]);
                } else {
                    return response()->json([
                        "success" => false,
                        "message" => "OTP Expired!",
                        "data"    => [],
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "No OTP Found!",
                    'data'    => [],
                ]);
            }
        }
    }

    // API to delete existing table (OK)
    public function accountDelete(Request $request) {
        $credentials = $request->only(['email','password']);

        $validator = Validator::make($credentials,[
            'email' => 'required|email|exists:users',
            'password' => 'required| min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        } else {
            $user = User::where('email' , $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => "Password not correct",
                    'data'    => [],
                ]);
            } else {
                $user->delete();
                return response()->json([
                    'success' => true,
                    'message' => "User Deleted Successfully",
                    'data'    => [],
                ]);
            }
        }
    }

}
