<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use  Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function me(Request $request) {

        return response()->json($request->user());
    }

    public function update(Request $request) {

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'password' => [
                'sometimes',
                'confirmed',
                'not_regex:/^(12345678|123456789|1234567890|password|Password1)$/',
            Password::min(8)->max(15)->mixedCase()->numbers()
            ]
        ]);

        if(isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $request->user()->update($validated);

        return response()->json($request->user()->fresh());
    }

    public function destroy(Request $request) {

        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
