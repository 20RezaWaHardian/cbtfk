<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplatePertanyaanKuesionerExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['pertanyaan', 'jenis'];
    }

    public function array(): array
    {
        return [
            ['Dosen menjelaskan tujuan pembelajaran pada awal perkuliahan.', 'point'],
            ['Apa saran Anda untuk perbaikan pembelajaran?', 'terbuka'],
        ];
    }
}