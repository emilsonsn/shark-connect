<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, 
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'login' => ['required', 'string', 'max:255', 'unique:users'],
                'group_id' => ['required', 'integer', 'exists:groups,id'],
                'superior_user_id' => ['nullable', 'integer', 'exists:users,id'],
                'password' => $this->passwordRules()
            ],
            [
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
                'login.required' => 'O login é obrigatório.',
                'login.string' => 'O login deve ser um texto.',
                'login.max' => 'O login deve ter no máximo 255 caracteres.',
                'login.unique' => 'Esse login já foi usado.'
            ]
        
        )->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'login' => $input['login'],
            'password' => Hash::make($input['password']),
            'group_id' => $input['group_id'],
            'superior_id' => $input['superior_user_id'],
        ]);
    }
}
