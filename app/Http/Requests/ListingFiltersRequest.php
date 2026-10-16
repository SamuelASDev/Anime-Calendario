<?php

namespace App\Http\Requests;

use App\Enums\WatchStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $catalog = $this->routeIs('anime.index.all');
        $statuses = array_column(WatchStatus::cases(), 'value');
        if ($catalog) {
            $statuses[] = 'unlisted';
        }

        return [
            'q' => ['nullable', 'string', 'max:200'],
            'genre' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in($statuses)],
            'sort' => ['nullable', Rule::in($catalog ? ['recent', 'title', 'rating', 'episodes'] : ['recent', 'title', 'start_date'])],
            'favorite' => ['nullable', 'boolean'],
            'airing' => ['nullable', 'string', 'max:100'],
            'length' => ['nullable', Rule::in(['short', 'medium', 'long', 'unknown'])],
        ];
    }
}
