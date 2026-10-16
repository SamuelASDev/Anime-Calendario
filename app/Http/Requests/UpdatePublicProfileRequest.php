<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'username' => ['required', 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('users')->ignore($this->user()->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'profile_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
        foreach (['is_public', 'show_watching_public', 'show_completed_public', 'show_favorites_public', 'show_top10_public', 'show_reviews_public'] as $flag) {
            $rules[$flag] = ['sometimes', 'boolean'];
        }

        return $rules;
    }
}
