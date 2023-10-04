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
     *     path="/api/v1/auth/registerAuthor",
     *     operationId="registerAuthor",
     *     tags={"Authentication"},
     *     summary="Register a new Author",
     *     description="Registers a new Author and returns an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
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
    public function registerAuthor(Request $request)
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


        // Assign the "Author" role if it exists
        $collaboratorRole = Role::where('name', 'Author')->first();


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
     *     path="/api/v1/auth/registerCollaborator",
     *     operationId="registerCollaborator",
     *     tags={"Authentication"},
     *     summary="Register a new Collaborator",
     *     description="Registers a new Collaborator and returns an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
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
    public function registerCollaborator(Request $request)
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


        // Assign the "Collaborator" role if it exists
        $collaboratorRole = Role::where('name', 'Collaborator')->first();


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
}
