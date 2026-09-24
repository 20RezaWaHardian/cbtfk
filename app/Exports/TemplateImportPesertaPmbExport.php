<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateImportPesertaPmbExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'id_pmb',
            'username',
            'nama_peserta',
        ];
    }

    public function array(): array
    {
        return [
            ['10001', '2300012345', 'Nama Peserta Contoh'],
        ];
    }
}