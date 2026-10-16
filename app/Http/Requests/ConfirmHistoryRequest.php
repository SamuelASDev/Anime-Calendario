<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:watch_plans,id'],
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'episodes' => ['required_if:action,confirm', 'nullable', 'integer', 'min:0', 'max:100000'],
            'action' => ['required', 'in:confirm,missed'],
        ];
    }
}
