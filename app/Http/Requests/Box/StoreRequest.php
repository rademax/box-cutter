<?php

namespace App\Http\Requests\Box;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'sheetSize' => [
                'array',
                'required',
            ],
            'sheetSize.w' => [
                'required',
                'integer',
                'min:1',
            ],
            'sheetSize.l' => [
                'required',
                'integer',
                'min:1',
            ],
            'boxSize' => [
                'array',
                'required',
            ],
            'boxSize.w' => [
                'required',
                'integer',
                'min:1',
            ],
            'boxSize.d' => [
                'required',
                'integer',
                'min:1',
            ],
            'boxSize.h' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
