<?php

namespace App\Http\Requests\API;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\HttpFoundation\Response;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name'            => 'required|max:255',
            'last_name'             => 'required|max:255',
            'email'                 => 'required|unique:users,email',
            'password'              => 'min:8|required_with:password_confirmation|same:password_confirmation|required|string|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&.]/',
            'password_confirmation' => 'required|min:8|same:password',
            'username'              => 'required|max:255|unique:users,username'
        ];
    }
}