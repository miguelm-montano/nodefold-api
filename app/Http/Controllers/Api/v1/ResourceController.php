<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Resource;
use App\Models\Folder;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Resources
 *
 * Endpoints for managing user resources.
 * Resources can be images, fonts, color palettes, icons or web links.
 * All endpoints require authentication.
 */
class ResourceController extends Controller
{
    /**
     * List all resources
     *
     * Returns all resources belonging to the authenticated user.
     * Supports filtering via query parameters.
     *
     * @queryParam search string Filter resources by title. Example: Chair
     * @queryParam Filter by tag status. Accepted values: true, false. Example: true
     * @queryParam tag string Filter by tag name. Example: modern
     */
    public function index(Request $request) {
    
        $query = $request->user()->resources()->with(['folder']);

        $this->applyFilters($query, $request);
        
        return response()->json($query->get());

    }

    /**
     * Get a resource
     *
     * Returns a single resource with its folder and tags.
     */
    public function show(Request $request, $id) {
        
        $resource = Resource::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['folder', 'tags'])
            ->firstOrFail();

        return response()->json($resource);
    }

    /**
    * Create a resource
    *
    * Creates a new resource inside the specified folder.
    *
    * @bodyParam title string required The title of the resource. Example: Green Tones
    * @bodyParam type string required The type of resource.<br> Allowed: font, image, color_palette, icon, web. Example: color_palette
    * @bodyParam description string optional A short description. Max 400 characters. Example: Green tones for the home page
    * @bodyParam url string optional URL required for font, web, icon and color_palette types. Example: https://coolors.co/palette/dad7cd-a3b18a-588157-3a5a40-344e41
    * @bodyParam tags string optional Comma separated list of tags. Example: greens, forest
    * @bodyParam image file optional Image file. Accepted: jpg, jpeg, png, webp, gif. Max 10MB.
     */
    public function store(Request $request, $id) {

        $folder = $this->findUserFolder($id, $request->user()->id);
        $validated = $this->validateResource($request);

        $resource = $request->user()->resources()->create([
            ...$validated,
            'folder_id' => $folder->id,
            'image_path' => $this->handleImageUpload($request),
            'color_data' => $this->extractColorsFromUrl($validated['type'], $validated['url'] ?? null),
        ]);

        $resource->syncTagsFromString($validated['tags'] ?? null, $request->user()->id);

        return response()->json($resource->load('tags', 'folder'), 201);
    }

    /**
     * Update a resource
     *
     * Updates the information of an existing resource.
     *
     * @bodyParam title string required The updated title. Example: Updated Green Tones
     * @bodyParam type string required The resource type. Example: color_palette
     * @bodyParam description string optional The updated description. Example: Updated description
     * @bodyParam url string optional The updated URL. Example: https://coolors.co/palette/dad7cd-a3b18a-588157-3a5a40-344e41
     * @bodyParam tags string optional Updated comma separated tags. Example: greens, updated
     */
    public function update(Request $request, $id) {

        $resource = Resource::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $this->validateResource($request);

        $tags = $validated['tags'] ?? null;
        unset($validated['image'], $validated['tags']);

        $resource->syncTagsFromString($tags, $request->user()->id);

        Tag::where('user_id', $request->user()->id)
            ->whereDoesntHave('resources')
            ->delete();
        
        $resource->update($validated);

        return response()->json($resource->fresh()->load('tags', 'folder'));
    }

    /**
     * Delete a resource
     *
     * Deletes the resource and cleans up any orphan tags.
     */
    public function destroy(Request $request, $id) {

        $resource = Resource::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $userId = $resource->user_id;

        $resource->delete();

        Tag::where('user_id', $userId)
            ->whereDoesntHave('resources')
            ->delete();

        return response()->json(['message' => 'Resource deleted']);
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
                Rule::when(
                    $request->type === 'image' && $request->url,
                    ['regex:/\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i']
                ),
            ],
            'tags' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240'
        ]);
    }

    private function applyFilters($query, Request $request): void {
    
        if ($request->query('tagged') === 'true') {
            $query->has('tags');
        }

        if ($request->query('tagged') === 'false') {
            $query->doesntHave('tags');
        }

        if ($request->query('search')) {
            $query->where('title', 'like', '%' . $request->query('search') . '%');
        }

        if ($request->query('tag')) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('name', $request->query('tag'));
            });
        }
    }
}

