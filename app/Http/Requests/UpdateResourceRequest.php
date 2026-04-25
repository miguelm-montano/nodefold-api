<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResourceRequest extends FormRequest
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
            'description' => 'nullable|string|max:400',
            'url' => [
                Rule::requiredIf(fn() => in_array($this->type, ['font', 'web', 'icon', 'color_palette'])),
                'nullable',
                'url',
                'max:2048',
                'starts_with:https://,http://',
            ],
            'tags' => ['nullable', 'string', 'regex:/^(\s*[\w-]{1,30}\s*)(,\s*[\w-]{1,30}\s*)*$/u'],
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
            'url.required' => 'The URL is required for this resource type.',
            'url.url' => 'The URL must be a valid URL.',
            'url.starts_with' => 'The URL must start with http:// or https://.',
            'tags.regex' => 'The tags format is invalid. Use comma-separated tags.',
        ];
    }
}
