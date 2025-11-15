<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $bankAccountId = $this->route('id') ?? $this->route('bank_account');

        return [
            'bank_code' => [
                'required',
                'string',
                'max:50',
            ],
            'account_number' => [
                'required',
                'string',
                'regex:/^[0-9]{9,19}$/',
                // ✅ Kiểm tra UNIQUE trừ chính tài khoản hiện tại
                Rule::unique('bank_accounts', 'account_number')
                    ->ignore($bankAccountId)
                    ->whereNull('deleted_at'),
            ],
            'account_name' => [
                'required',
                'string',
                'max:255',
            ],
            'note' => 'nullable|string|max:500',
            'is_primary' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'account_number.unique' => 'Số tài khoản này đã được đăng ký bởi user khác.',
            'bank_code.required' => 'Vui lòng chọn ngân hàng.',
            'account_number.required' => 'Vui lòng nhập số tài khoản.',
            'account_number.regex' => 'Số tài khoản phải có 9-19 chữ số.',
            'account_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ];
    }
}