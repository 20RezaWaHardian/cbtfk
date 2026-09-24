<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubKategoriSoalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id_sub_kategori');

        return [
            'id_kategori_soal' => ['required', 'exists:kategori_soal,id_kategori_soal'],
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_kategori_soal', 'nama_kategori')
                    ->where('id_kategori_soal', $this->id_kategori_soal)
                    ->ignore($id, 'id_sub_kategori_soal'),
            ],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
