<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKritikRequest extends FormRequest
{
    protected $errorBag = 'comment';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'Komentar tidak boleh kosong.',
            'comment.string' => 'Komentar harus berupa teks.',
            'comment.max' => 'Komentar maksimal 500 karakter.',
        ];
    }
}
