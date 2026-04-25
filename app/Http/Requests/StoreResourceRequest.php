<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:font,image,color_palette,icon,web',
            'folder_id' => 'required|integer|exists:folders,id',
            'description' => 'nullable|string|max:400',
            'url' => [
                Rule::requiredIf(fn() => in_array($this->type, ['font', 'web', 'icon', 'color_palette'])),
                'nullable',
                'url',
                'max:2048',
                'starts_with:https://,http://',
            ],
            'tags' => ['nullable', 'string', 'regex:/^(\s*[\w-]{1,30}\s*)(,\s*[\w-]{1,30}\s*)*$/u'],
            'image' => [
                'nullable',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:10240',
                'dimensions:max_width=8000,max_height=8000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be one of: font, image, color_palette, icon, web.',
            'folder_id.required' => 'The folder ID field is required.',
            'folder_id.exists' => 'The selected folder does not exist.',
            'url.required' => 'The URL is required for this resource type.',
            'url.url' => 'The URL must be a valid URL.',
            'url.starts_with' => 'The URL must start with http:// or https://.',
            'image.mimetypes' => 'The image must be a file of type: jpeg, png, webp, gif.',
            'image.max' => 'The image may not be greater than 10MB.',
            'image.dimensions' => 'The image dimensions must not exceed 8000x8000 pixels.',
            'tags.regex' => 'The tags format is invalid. Use comma-separated tags.',
        ];
    }
}
