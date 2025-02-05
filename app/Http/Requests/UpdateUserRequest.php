<?php

namespace App\Http\Requests;

use App\Actions\Fortify\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id . ',id'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'superior_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'password' => ['nullable','string', new Password, 'confirmed'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome deve ter no máximo 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.string' => 'O email deve ser um texto.',
            'email.max' => 'O email deve ter no máximo 255 caracteres.',
            'email.unique' => 'Esse email já foi usado.',
            'email.email' => 'O email informado não é válido.',
            'group_id.exists' => 'O grupo selecionado não existe.',
            'superior_user_id.exists' => 'O usuário superior selecionado não existe.',
            'confirmed' => 'As senhas não são iguais.',
        ];
    }
}
