<?php

namespace App\Http\Requests;

use App\Models\KategoriSoal;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class KategoriSoalRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'nama_kategori' => ['required', Rule::unique(KategoriSoal::class, 'nama_kategori')->ignore($id, 'id_kategori_soal')],
            'id_kelas' => ['nullable', 'exists:kelas,id_kelas'],
        ];
    }
}
