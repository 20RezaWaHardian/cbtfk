<?php

namespace App\Http\Requests;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class JenisSoalRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'jenis_soal' => ['required'],
            'sub_kategori_soal_id' => [
                'nullable',
                Rule::exists('sub_kategori_soal', 'id_sub_kategori_soal')->where('is_delete', false),
            ],
            // 'poin' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
