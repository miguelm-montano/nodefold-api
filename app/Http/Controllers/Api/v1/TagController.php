<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Tags
 *
 * Endpoints for managing tags.
 * Tags are created when adding resources and deleted by
 * editing the resource when they become orphaned
 * All endpoints require authentication.
 */
class TagController extends Controller
{
    /**
    * List all tags
    *
    * Returns all tags belonging to the authenticated user.
    * Useful for autocomplete when tagging resources.
    *
    * @response 200 [{
    *   "id": 1,
    *   "name": "ocean"
    * }]
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    */
    public function index(Request $request) {
        
        $tags = $request->user()->tags()->get();

        return response()->json($tags);
    }
}
