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
* Endpoints for platform administration.
* These endpoints are exclusively available to users with the `admin` role.
* Any authenticated user without the `admin` role will receive a `403 Forbidden` response.
*
* ### Admin endpoints
* - `GET /api/v1/admin/users` — list all registered users, filterable by role.
* - `DELETE /api/v1/admin/users/{id}` — permanently delete a user and all their data.
* - `GET /api/v1/admin/stats` — view global platform statistics including total counts and tag listing.
*
* ### How to get admin access
* Admin users are created via database seeder. Run `php artisan db:seed` to create the default admin account.
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
    *
    * @response 200 [{
    *   "id": 1,
    *   "name": "TestUser",
    *   "email": "usertest@nodefold.com",
    *   "role": "user"
    * }]
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 403 {
    *   "message": "Unauthorized"
    * }
    */
    public function index(Request $request) {
        $query = User::query();
    
        if ($request->query('role')) {
            $query->where('role', $request->query('role'));
        }
    
    return response()->json($query->get());
    }

    /**
    * Delete a user
    *
    * Permanently deletes a user account and all associated data including folders, resources and tags.
    *
    * @urlParam id integer required The ID of the user. Example: 1
    *
    * @response 200 {
    *   "message": "User deleted successfully"
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 403 {
    *   "message": "Unauthorized"
    * }
    * @response 404 {
    *   "message": "Resource not found"
    * }
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
    *
    * @response 200 {
    *   "total_users": 25,
    *   "total_resources": 142,
    *   "total_folders": 38,
    *   "total_tags": 15,
    *   "tags": [{"id": 1, "name": "ocean"}]
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 403 {
    *   "message": "Unauthorized"
    * }
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
