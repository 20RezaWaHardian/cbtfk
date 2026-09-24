<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PilganJawabRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'peserta_ujian_id' => 'required',
            'id_soal' => 'required',
            'pilgan_id' => 'required',
            'jawaban_pilgan' => 'required',

        ];
    }
}
