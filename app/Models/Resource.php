<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model {

    use HasFactory;

    protected $fillable = [
        'user_id',
        'folder_id',
        'title',
        'type',
        'description',
        'url',
        'file_path',
        'thumbnail',
        'image_path',
        'color_data'
    ];

    protected $casts = [
        'color_data' => 'array',
    ];

    public function user() {
        
        return $this->belongsTo(User::class);
    }

    public function folder() {

        return $this->belongsTo(Folder::class);
    }

    public function tags() {

        return $this->belongsToMany(Tag::class, 'resource_tag');
    }

    public function syncTagsFromString(?string $tagsString): void {
        
        $tagNames = collect(explode(',', $tagsString ?? ''))
            ->map(fn ($tag) => trim(strtolower($tag)))
            ->filter()
            ->unique();

        $tagIds = $tagNames->map(fn ($name) =>
            \App\Models\Tag::firstOrCreate(['name' => $name])->id
        );

        $this->tags()->sync($tagIds);
    }
}
