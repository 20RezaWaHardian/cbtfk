<?php

namespace App\Http\Requests;

use App\Models\PaketSoal;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PaketSoalRequest extends FormRequest
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
            'judul' => ['required', Rule::unique(PaketSoal::class)->ignore($this->paket_soal)],
            'durasi' => ['required'],
            // 'kkm' => ['required'],
        ];
    }
}
