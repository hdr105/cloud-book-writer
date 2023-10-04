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
use OpenApi\Annotations as OA;

// /**
//  * @OA\Info(
//  *     title="BookStore Authentication Services",
//  *     version="1.0",
//  *     description="This is a simple API documentation for demonstration of Authentication Services for cloud-based Bookstore.",
//  *     @OA\Contact(
//  *         name="Muhammad Haider",
//  *         email="haadi.javaid@gmail.com"
//  *     )
//  * )
//  */

class AuthController extends Controller
{


    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Registers a new user and returns an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Collaborator John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     * )
     */
    public function register(Request $request)
    {
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

        // Determine the user's role based on their name
        $check_role = strtok($request->name, " ");
        if ($check_role == "Author") {
            // Assign the "Author" role if it exists
            $collaboratorRole = Role::where('name', 'Author')->first();
        } else {
            // Assign the "Collaborator" role if not "Author"
            $collaboratorRole = Role::where('name', 'Collaborator')->first();
        }

        if ($collaboratorRole) {
            $user->assignRole($collaboratorRole);
        }

        // Fire the Registered event
        event(new Registered($user));

        // Generate an access token for the user
        $token = $user->createToken('MyAppToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Signup Successfully',
            'data'    => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     summary="Login as a user",
     *     description="Logs in a user and returns an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="author@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     * )
     */
    public function login(Request $request)
    {
        // Ensure email is in lowercase for consistency
        $request->merge(['email' => strtolower($request->email)]);

        $credentials = $request->only(['email', 'password']);

        // Validate the login credentials
        $validator = Validator::make($credentials, [
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

        // Retrieve the user by email
        $user = User::where('email', $request['email'])->first();

        if (!$user) {
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


    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot/send-reset-otp",
     *     operationId="sendResetOTP",
     *     tags={"Authentication"},
     *     summary="Send a reset OTP",
     *     description="Sends a one-time password (OTP) to the user's email for password reset.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User email",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *     ),
     * )
     */
    public function send_reset_otp(Request $request)
    {
        // Extract the email from the request
        $credentials = $request->only(['email']);

        // Validate the request data
        $validator = Validator::make($credentials, [
            'email' => 'required|email|exists:users',
        ]);

        // Check for validation failure
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }

        // Generate a random OTP (One-Time Password)
        $otp = mt_rand(100000, 999999);

        // Store the OTP in the database for later verification
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

        // Attempt to send the email
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot/verify-otp",
     *     operationId="verifyOTP",
     *     tags={"Authentication"},
     *     summary="Verify OTP",
     *     description="Verifies the one-time password (OTP) sent to the user's email.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User email and OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="otp", type="integer", example=123456),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or OTP expired",
     *     ),
     * )
     */
    public function verify_otp(Request $request)
    {
        // Extract email and OTP (One-Time Password) from the request
        $credentials = $request->only(['email', 'otp']);

        // Validate the request data
        $validator = Validator::make($credentials, [
            'email' => 'required|email|exists:users',
            'otp'   => 'required|integer|digits:6',
        ]);

        // Check for validation failure
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }

        // Find the OTP record in the database
        $passwordReset = PasswordReset::where([
            'email' => $request->email,
            'otp' => $request->otp
        ])->first();

        // Check if OTP record exists
        if ($passwordReset) {
            $otpCreateTime = Carbon::parse($passwordReset->created_at);
            $now = Carbon::now();
            $diffInMinutes = $now->diffInMinutes($otpCreateTime);

            // Check if OTP is still valid (within 5 minutes)
            if ($diffInMinutes < 5) {
                if ($passwordReset->otp == $request->otp) {
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

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot/reset",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     summary="Reset password",
     *     description="Resets the user's password using the provided OTP.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User email and new password",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or OTP expired",
     *     ),
     * )
     */
    public function reset(Request $request)
    {
        // Extract email and new password from the request
        $credentials = $request->only(['email', 'password']);

        // Validate the request data
        $validator = Validator::make($credentials, [
            'email' => 'required|email|exists:users',
            'password' => 'required| min:8',
        ]);

        // Check for validation failure
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

            if ($passwordReset) {
                $otpCreateTime = Carbon::parse($passwordReset->created_at);
                $now = Carbon::now();
                $diffInMinutes = $now->diffInMinutes($otpCreateTime);

                // Check if OTP is still valid (within 5 minutes)
                if ($diffInMinutes < 5) {
                    // Update the user's password
                    User::where('email', $request->email)->update([
                        'password' => Hash::make($request->password)
                    ]);

                    // Delete the OTP record
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


    /**
     * @OA\Post(
     *     path="/api/v1/auth/account-delete",
     *     operationId="deleteUserAccount",
     *     tags={"Authentication"},
     *     summary="Delete user account",
     *     description="Deletes the user account.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User account deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     * )
     */
    public function accountDelete(Request $request)
    {
        // Extract email and password from the request
        $credentials = $request->only(['email', 'password']);

        // Validate the request data
        $validator = Validator::make($credentials, [
            'email' => 'required|email|exists:users',
            'password' => 'required|min:8',
        ]);

        // Check for validation failure
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        } else {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            // Check if the provided password matches the user's password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => "Password not correct",
                    'data'    => [],
                ]);
            } else {
                // Delete the user account
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
