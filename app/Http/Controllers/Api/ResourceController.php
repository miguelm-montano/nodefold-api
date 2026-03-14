<?php

namespace App\Http\Controllers\Api;

use App\Models\Resource;
use App\Models\Folder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{

    public function index(Request $request) {
    
        $resources = $request->user()->resources()
            ->with(['folder'])
            ->get();

        return response()->json($resources);
    }

    public function store(Request $request, $id) {

        $folder    = $this->findUserFolder($id, $request->user()->id);
        $validated = $this->validateResource($request);

        $resource = $request->user()->resources()->create([
            ...$validated,
            'folder_id'  => $folder->id,
            'image_path' => $this->handleImageUpload($request),
            'color_data' => $this->extractColorsFromUrl($validated['type'], $validated['url'] ?? null),
        ]);

        // $resource->syncTagsFromString($validated['tags'] ?? null);

        return response()->json($resource, 201);
    }

    private function findUserFolder(int $id, int $userId): Folder {
        
        return Folder::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    private function handleImageUpload(Request $request): ?string {
        
        if ($request->hasFile('image')) {
            return $request->file('image')->store('resources',  'public');
        }
        return null;
    }

    private function extractColorsFromUrl(?string $type, ?string $url): ?array {
        
        if ($type !== 'color_palette' || empty($url)) return null;

        if (preg_match('/coolors\.co\/(?:palette\/)?([a-f0-9-]+)/i', $url, $matches)) {
            $colors = array_filter(explode('-', $matches[1]), fn($c) => strlen($c) === 6);
            return array_values($colors);
        }

        return null;
    }

    private function validateResource(Request $request): array {
    
        return $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:font,image,color_palette,icon,web',
            'description' => 'nullable|string|max:400',
            'url' => [
                Rule::requiredIf(fn() => in_array($request->type, ['font', 'web', 'icon', 'color_palette'])),
                'nullable',
                'string',
                'max:500',
                ],
            'tags' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240'
        ]);
    }

    public function show(Request $request, $id) {
        
        $resource = Resource::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['folder'])
            ->firstOrFail();

        return response()->json($resource);
    }
}

