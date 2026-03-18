<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resource;
use App\Models\Folder;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index() {
        $users = User::all();
        return response()->json($users);
    }

    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function stats() {
        return response()->json([
            'total_users' => User::count(),
            'total_resources' => Resource::count(),
            'total_folders' => Folder::count(),
            'total_tags' => Tag::count(),
            'tags' => Tag::all()
        ]);
    }
}
