<?php

namespace App\Http\Requests;

use App\Enums\WatchStatus;
use App\Services\Planning\PlanManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWatchPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:1970-01-01', 'before_or_equal:2100-12-31'],
            'episodes_watched' => ['required', 'integer', 'min:0', 'max:100000'],
            'watch_status' => ['sometimes', 'required', Rule::enum(WatchStatus::class)],
            'is_completed' => ['sometimes', 'boolean'],
        ];
        foreach (PlanManager::WEEK as $day) {
            $rules[$day] = ['required', 'integer', 'min:0', 'max:100'];
            $rules[$day.'_variable'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }
}
