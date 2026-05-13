<?php

namespace Esolutions\Datatable\Dialog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id'       => ['required'],
            'password' => ['nullable', 'string', 'required_if:verify_password,true'],
        ];
    }
}
