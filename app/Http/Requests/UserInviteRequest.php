<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserInviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasPermission('user-create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
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
            'name.required' => 'Name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already in use',
            'roles.*.exists' => 'One or more selected roles do not exist',
        ];
    }
}
