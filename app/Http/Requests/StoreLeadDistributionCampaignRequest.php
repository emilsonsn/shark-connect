<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadDistributionCampaignRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:255'],
            'groups' => ['required', 'array'],
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'max_per_user' => ['required', 'integer', 'min:1'],
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
            'name.required' => 'O nome é obrigatório',
            'description.required' => 'A descrição é obrigatória',
            'name.max' => 'O nome deve ter no máximo 255 caracteres',
            'description.max' => 'A descrição deve ter no máximo 255 caracteres',
            'name.string' => 'O nome deve ser uma string',
            'description.string' => 'A descrição deve ser uma string',
            'groups.required' => 'É necessário selecionar pelo menos um grupo',
        ];
    }
}
