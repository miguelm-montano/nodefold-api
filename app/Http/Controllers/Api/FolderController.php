<?php

namespace App\Http\Controllers\Api;

use App\Models\Folder;
use App\Models\Resource;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index(Request $request) {
        
        $folders = $request->user()->folders()
            ->whereNull('parent_id')
            ->with(['folders.resources', 'resources'])
            ->withCount(['resources'])
            ->get();

        return response()->json($folders);
    }

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