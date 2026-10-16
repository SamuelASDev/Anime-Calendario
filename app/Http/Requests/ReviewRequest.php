<?php

namespace App\Http\Requests;

use App\Models\WatchPlan;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && WatchPlan::ownedBy($this->user()->id)->where('anime_id', $this->route('anime')->id)
            ->where('watch_status', 'concluido')->exists();
    }

    public function rules(): array
    {
        return ['rating' => ['nullable', 'integer', 'min:1', 'max:10'], 'comment' => ['nullable', 'string', 'max:2000']];
    }
}
