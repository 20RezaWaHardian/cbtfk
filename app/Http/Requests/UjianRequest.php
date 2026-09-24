<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UjianRequest extends FormRequest
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
            'nama_ujian' => ['required'],
            // 'kelompok_belajar_id' => ['required'],
            'paket_soal_id' => ['required'],
            'tanggal_ujian' => ['required'],
            'tampil_nilai' => ['required'],
            'pengawas_ujian' => ['required'],

        ];
    }
    public function messages()
    {

        return [
            'nama_ujian.required' => 'Nama Ujian wajib diisi.',
            // 'kelompok_belajar_id.required' => 'Kelompok Belajar wajib diisi.',
            'paket_soal_id.required' => 'Paket Soal wajib diisi.',
            'tanggal_ujian.required' => 'Tanggal Ujian wajib diisi.',
            'tampil_nilai.required' => 'Tampilkan Ujian wajib diisi.',
            'pengawas_ujian.required' => 'Silahkan Pilih Pengawas Ujian.',
        ];
    }
}
