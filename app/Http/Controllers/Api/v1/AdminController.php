<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resource;
use App\Models\Folder;
use App\Models\Tag;
use Illuminate\Http\Request;

/**
* @group Admin
*
* Endpoints for platform administration.
* All endpoints require authentication and admin role.
*/
class AdminController extends Controller
{
    /**
    * List all users
    *
    * Returns all registered users. Supports filtering by role.
    *
    * @queryParam role string Filter users by role. Accepted values: user, admin. Example: user
    */
    public function index() {
        $users = User::all();
        return response()->json($users);
    }

    /**
    * Delete a user
    *
    * Permanently deletes a user account and all associated data including folders, resources and tags.
    */
    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
    * Platform stats
    *
    * Returns global platform statistics including total counts of users, resources, folders and tags.
    */
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
