<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WatchLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'watched_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'episodes_watched_today' => ['required', 'integer', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
