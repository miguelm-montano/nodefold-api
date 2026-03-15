<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        
        return [
            'user_id'   => \App\Models\User::factory(),
            'folder_id' => \App\Models\Folder::factory(),
            'title'     => fake()->words(3, true),
            'type'      => fake()->randomElement(['font', 'image',  'color_palette', 'icon', 'web']),
            'description' => fake()->sentence(),
            'url'       => fake()->url(),
            'image_path' => null,
            'color_data' => null,
        ];
    }
}
