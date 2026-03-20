<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
* @group Tags
*
* Endpoints for managing tags.
* Tags are created automatically when adding or updating resources and deleted when they become orphaned — meaning no resources are associated with them.
* 
* ### Autocomplete
* `GET /api/v1/tags` returns all tags belonging to the authenticated user.
* This endpoint is useful for autocomplete when tagging resources — the user can start typing a tag name and the frontend can suggest existing tags to avoid duplicates.
*
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
