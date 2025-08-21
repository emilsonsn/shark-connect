<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:191'],
            'username' => ['required','string','max:191'],
        ];
    }
}
