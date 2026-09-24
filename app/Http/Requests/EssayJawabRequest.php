<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EssayJawabRequest extends FormRequest
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
    public function rules(): array
    {
        return [

            'peserta_ujian_id' => 'required',
            'soal_id' => 'required',
            'essay_id' => 'required',
            'jawaban_essay' => 'required',
        ];
    }
}
