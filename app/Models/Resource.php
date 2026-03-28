<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model {

    use HasFactory;

    protected $fillable = [
        'user_id',
        'folder_id',
        'title',
        'type',
        'description',
        'url',
        'image_path',
        'color_data'
    ];

    protected $casts = [
        'color_data' => 'array',
    ];

    protected $appends = ['image_url'];

    protected $hidden = ['user_id', 'folder_id'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function user() {
        
        return $this->belongsTo(User::class);
    }

    public function folder() {

        return $this->belongsTo(Folder::class);
    }

    public function tags() {

        return $this->belongsToMany(Tag::class, 'resource_tag');
    }

    public function syncTagsFromString(?string $tagsString, int $userId): void {
        
        $tagNames = collect(explode(',', $tagsString ?? ''))
            ->map(fn ($tag) => trim(strtolower($tag)))
            ->filter()
            ->unique();

        $tagIds = $tagNames->map(function($name) use ($userId) {
            return Tag::firstOrCreate(['name' => $name, 'user_id' => $userId],
            )->id;
    });

        $this->tags()->sync($tagIds);
    }
}
