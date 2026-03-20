<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * @group Auth
 * Endpoints for user registration, login and logout.
 * Registration and login do not require authentication.
 */
class AuthController extends Controller
{
    /**
    * Register a new user
    *
    * Creates a new user account and returns an access token.
    *
    * @unauthenticated
    * @bodyParam name string required The user's full name. Example: TestName
    * @bodyParam email string required A valid unique email address. Example: testUser@nodefold.com
    * @bodyParam password string required Min 8, max 15 characters. Must include uppercase and a number. Example: Password123
    * @bodyParam password_confirmation string required Must match the password field. Example: Password123
    *
    * @response 201 {
    *   "user": {
    *     "id": 1,
    *     "name": "TestName",
    *     "email": "testUser@nodefold.com",
    *     "role": "user"
    *   },
    *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
    * }
    * @response 422 {
    *   "message": "The email has already been taken.",
    *   "errors": {
    *     "email": ["The email has already been taken."]
    *   }
    * }
    */
    public function register(Request $request) {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => [
                'required',
                'confirmed',
                'not_regex:/^(12345678|123456789|1234567890|password|Password1)$/',
                Password::min(8)->max(15)->mixedCase()->numbers()
            ]
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
    * Login
    *
    * Authenticates the user and returns an access token.
    *
    * @unauthenticated
    * @bodyParam email string required The registered email address. Example: miguel@nodefold.com
    * @bodyParam password string required The account password. Example: Password123
    *
    * @response 200 {
    *   "user": {
    *     "id": 1,
    *     "name": "TestName",
    *     "email": "testUser@nodefold.com",
    *     "role": "user"
    *   },
    *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
    * }
    * @response 401 {
    *   "message": "Invalid credentials"
    * }
    * @response 422 {
    *   "message": "The email field is required.",
    *   "errors": {
    *     "email": ["The email field is required."]
    *   }
    * }
    */
    public function login(Request $request) {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'user' => Auth::user(),
            'token' => $token,
        ]);
    }

    /**
    * Logout
    * 
    * Invalidates the current access token.
    *
    * @response 200 {
    *   "message": "Logged out successfully"
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    */
    public function logout(Request $request) {

        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
