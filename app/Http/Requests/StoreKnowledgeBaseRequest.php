<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnowledgeBaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in([
                'hours', 'tuition', 'enrollment', 'health', 'meals',
                'schedule', 'pickup', 'safety', 'classrooms', 'policies', 'general',
            ])],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string'],
            'is_active' => ['boolean'],
        ];
    }
}
