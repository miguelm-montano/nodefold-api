<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Folder;
use App\Models\Resource;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Folders
 * 
 * Endpoints for managing folders and subfolders.
 * Folders can have one level of nesting — a folder can contain subfolders, but subfolders cannot contain further subfolders.
 */
class FolderController extends Controller
{
    /**
    * List all folders
    * 
    * Returns all root folders belonging to the authenticated user, including their subfolders and resources.
    * 
    * @response 200 [{
    *   "id": 1,
    *   "name": "Design",
    *   "parent_id": null,
    *   "total_resources_count": 3,
    *   "folders": [],
    *   "resources": []
    * }]
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    */
    public function index(Request $request) {

        $folders = $request->user()->folders()
            ->whereNull('parent_id')
            ->with(['folders.resources', 'resources'])
            ->get();

        return response()->json($folders);
    }

    /**
    * Create a folder
    * 
    * Creates a new folder. To create a subfolder, include a `parent_id` in the request body.
    * 
    * @bodyParam name string required The name of the folder. Max 50 characters. Example: Design
    * @bodyParam parent_id integer optional The ID of the parent folder. Example: 1
    *
    * @response 201 {
    *   "id": 1,
    *   "name": "Design",
    *   "parent_id": null
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 403 {
    *   "message": "Only one nesting level allowed"
    * }
    * @response 422 {
    *   "message": "The name field is required.",
       "errors": {
    *     "name": ["The name field is required."]
    *   }
    * }
    */
    public function store(Request $request) {

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if ($validated['parent_id'] ?? false) {
            $parent = Folder::where('id', $validated['parent_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            if ($parent->parent_id !== null) {
                abort(403, 'Only one nesting level allowed');
            }
        }

        $folder = $request->user()->folders()->create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json($folder, 201);
    }

    /**
    * Get a folder
    * 
    * Returns a single folder with its subfolders and resources.
    *
    * @response 200 {
    *   "id": 1,
    *   "name": "Design",
    *   "parent_id": null,
    *   "total_resources_count": 3,
    *   "folders": [],
    *   "resources": []
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 404 {
    *   "message": "Resource not found"
    * }
     */
    public function show(Request $request, $id) {

        $folder = Folder::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        
        if ($folder->parent_id === null) {
            $folder->load(['folders.resources', 'resources']);
        } else {
            $folder->load(['resources']);
        }

        return response()->json($folder);

    }

    /**
    * Update a folder
    * 
    * Updates the name of a folder.
    * 
    * @bodyParam name string required The new name of the folder. Max 50 characters. Example: New Design
    *
    * @response 200 {
    *   "id": 1,
    *   "name": "New Design",
    *   "parent_id": null
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 404 {
    *   "message": "Resource not found"
    * }
     */
    public function update(Request $request, $id) {

        $folder = Folder::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $folder->update($validated);

        return response()->json($folder);
    }

    /**
    * Delete a folder
    * 
    * Deletes a folder and all its subfolders, resources and orphan tags.
    *
    * @response 200 {
    *   "message": "Folder deleted"
    * }
    * @response 401 {
    *   "message": "Unauthenticated"
    * }
    * @response 404 {
    *   "message": "Resource not found"
    * }
    */
    public function destroy(Request $request, $id) {

        $folder = Folder::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        if ($folder->parent_id === null) {
            $subfolderIds = $folder->folders()->pluck('id');
            Resource::whereIn('folder_id', $subfolderIds)->delete();
            $folder->folders()->delete();
        }

        $folder->resources()->delete();
        $folder->delete();

        Tag::where('user_id', $request->user()->id)
            ->whereDoesntHave('resources')
            ->delete();

        return response()->json(['message' => 'Folder deleted']);
    }  
}