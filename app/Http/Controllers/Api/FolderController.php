<?php

namespace App\Http\Controllers\Api;

use App\Models\Folder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index(Request $request) {
        
        $folders = $request->user()->folders()
            ->whereNull('parent_id')
            ->with(['folders'])
            ->get();

        return response()->json($folders);
    }

    public function store(Request $request) {

        $validated = $request->validate([
            'name'      => 'required|string|max:50',
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
            'name'      => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json($folder, 201);
    }

    public function show(Request $request, $id) {

        $folder = Folder::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        
        if ($folder->parent_id === null) {
            $folder->load('folders');
        }

        return response()->json($folder);

    }
}
