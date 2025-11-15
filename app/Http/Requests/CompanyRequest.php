<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валідації запиту.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:256',
            'edrpou' => 'required|string|max:10',
            'address' => 'required|string',
        ];
    }

    /**
     * Кастомні повідомлення про помилки валідації.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Назва компанії є обов\'язковою',
            'name.string' => 'Назва компанії має бути рядком',
            'name.max' => 'Назва компанії не може перевищувати 256 символів',
            'edrpou.required' => 'ЄДРПОУ є обов\'язковим',
            'edrpou.string' => 'ЄДРПОУ має бути рядком',
            'edrpou.max' => 'ЄДРПОУ не може перевищувати 10 символів',
            'address.required' => 'Адреса є обов\'язковою',
            'address.string' => 'Адреса має бути рядком',
        ];
    }

    /**
     * Нормалізуємо значення перед валідацією.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'edrpou' => preg_replace('/\D/', '', $this->input('edrpou', '')),
            'name' => preg_replace('/\s+/', ' ', trim($this->input('name', ''))),
            'address' => preg_replace('/\s+/', ' ', trim($this->input('address', ''))),
        ]);
    }
}
