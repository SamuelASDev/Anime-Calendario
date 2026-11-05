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
            'studio' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:60'],
            'season' => ['nullable', 'regex:/^(winter|spring|summer|fall)-\d{4}$/'],
            'aired_from' => ['nullable', 'date_format:Y-m-d'],
            'aired_until' => ['nullable', 'date_format:Y-m-d', Rule::when($this->filled('aired_from'), 'after_or_equal:aired_from')],
        ];
    }

    public function messages(): array
    {
        return [
            'season.regex' => 'Escolha uma temporada válida.',
            'aired_from.date_format' => 'Informe uma data inicial de lançamento válida.',
            'aired_until.date_format' => 'Informe uma data final de lançamento válida.',
            'aired_until.after_or_equal' => 'A data final do lançamento deve ser igual ou posterior à inicial.',
        ];
    }
}
