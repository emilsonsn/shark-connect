<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
            'client_id'             => ['nullable','exists:clients,id'],
            'proposal_number'       => ['required','string','max:100'],
            'amount'                => ['required','numeric','min:0'],
            'product'               => ['required','string','max:255'],
            'bank'                  => ['required','string','max:255'],
            'commission_percentage' => ['required','numeric','min:0','max:100'],
            'commission_value'      => ['nullable','numeric','min:0'], // será recalculado no controller
            'payment_status'        => ['required','in:pending,paid,canceled'],
            'sale_date'             => ['required','date'],
            'paid_at'               => ['nullable','date'],
        ];
    }
}
