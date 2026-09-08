<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return (bool) session('token');
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];

        if ($this->input('data_from') === 'form') {
            $rules['form'] = 'required';
        }

        return $rules;
    }
}
