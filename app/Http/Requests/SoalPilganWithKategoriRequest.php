<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SoalPilganWithKategoriRequest extends FormRequest
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
            'pertanyaan' => ['required'],
            'kunci' => ['required'],
            'sub_kategori_soal_id' => [
                'required',
                Rule::exists('sub_kategori_soal', 'id_sub_kategori_soal')->where('is_delete', false),
            ],
        ];
    }
    public function messages()
    {

        return [
            'pertanyaan.required' => 'Pertanyaan wajib diisi.',
            'kunci.required' => 'Pilih salah satu kunci jawaban.',
            'sub_kategori_soal_id.required' => 'Pilih salah satu kategori soal.',
        ];
    }
}
