<?php

namespace App\Http\Controllers\Api;

use App\Models\Resource;
use App\Models\Folder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function store(Request $request, $id) {

        $folder = Folder::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:font,image,color_palette,icon,web',
            'description' => 'nullable|string|max:400',
            'url' => 'nullable|string|max:500',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240'
        ]);

        $imageFile = $validated['image'] ?? null;
        unset($validated['image']);
        unset($validated['tags']);

        $resource = $request->user()->resources()->create([
            ...$validated,
            'folder_id'  => $folder->id,
            'image_path' => $this->handleImageUpload($request),
        ]);

        // $resource->syncTagsFromString($validated['tags'] ?? null);

        return response()->json($resource, 201);
    }

    private function handleImageUpload(Request $request): ?string {
        
        if ($request->hasFile('image')) {
            return $request->file('image')->store('resources',  'public');
        }
        return null;
    }
}
