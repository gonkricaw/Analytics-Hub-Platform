<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
            'password_confirmation' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'current_password.required' => 'Current password is required',
            'password.required' => 'New password is required',
            'password.min' => 'New password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
            'password.different' => 'New password must be different from current password',
            'password_confirmation.required' => 'Password confirmation is required',
        ];
    }
}
