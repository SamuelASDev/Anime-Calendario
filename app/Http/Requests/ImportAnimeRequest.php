<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportAnimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->input('target') !== 'global' || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'mal_id' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'title' => ['required', 'string', 'max:255'],
            'episodes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'synopsis' => ['nullable', 'string', 'max:20000'],
            'status' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'url:http,https', 'max:255'],
            'genres' => ['nullable', 'string', 'max:1000'],
            'target' => ['required', 'in:global,personal,later'],
        ];
    }
}
