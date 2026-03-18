<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
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

    public function logout(Request $request) {

        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
