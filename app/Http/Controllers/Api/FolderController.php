<?php

namespace App\Http\Controllers\Api;

use App\Models\Folder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function store(Request $request) {

        $validated = $request->validate([
            'name'      => 'required|string|max:50',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $folder = $request->user()->folders()->create([
            'name'      => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json($folder, 201);
    }

    public function storeSubfolder(Request $request, $id) {

        $parent = Folder::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

        if ($parent->parent_id !== null) {
            abort(403, 'Only one nesting level allowed');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $subfolder = $request->user()->folders()->create([
            'name'      => $validated['name'],
            'parent_id' => $parent->id,
        ]);

        return response()->json($subfolder, 201);
    }
}
