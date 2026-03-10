<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request) {

        return response()->json($request->user());
    }

    public function update(Request $request) {

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'password' => 'sometimes|string|min:8|confirmed'
        ]);

        if(isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
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
