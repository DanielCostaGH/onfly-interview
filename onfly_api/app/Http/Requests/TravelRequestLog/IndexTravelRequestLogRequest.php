<?php

namespace App\Http\Requests\TravelRequestLog;

use Illuminate\Foundation\Http\FormRequest;

class IndexTravelRequestLogRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:requested,approved,canceled'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'code' => ['nullable', 'string'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date'],
        ];
    }
}
