<?php

namespace App\Imports;

use App\Models\PertanyaanKuesioner;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PertanyaanKuesionerImport implements ToCollection, WithMultipleSheets
{
    public int $count = 0;

    public function __construct(protected $kategoriId)
    {
    }

    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $rows)
    {
        $headers = collect($rows->first() ?? [])->map(fn ($value) => strtolower(trim((string) $value)));
        if ($headers->filter(fn ($value) => $value === 'pertanyaan')->count() !== 1 ||
            $headers->filter(fn ($value) => $value === 'jenis')->count() !== 1) {
            throw ValidationException::withMessages(['file_excel' => 'Baris pertama wajib memuat header pertanyaan dan jenis, masing-masing satu kali.']);
        }

        $pertanyaanIndex = $headers->search('pertanyaan');
        $jenisIndex = $headers->search('jenis');
        $data = [];
        $errors = [];
        foreach ($rows->slice(1) as $index => $row) {
            if (collect($row)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }
            $pertanyaan = trim((string) ($row[$pertanyaanIndex] ?? ''));
            $jenis = strtolower(trim((string) ($row[$jenisIndex] ?? '')));
            $line = $index + 1;
            if ($pertanyaan === '') {
                $errors[] = "Baris {$line}: pertanyaan wajib diisi.";
            }
            if (!in_array($jenis, ['point', 'terbuka'], true)) {
                $errors[] = "Baris {$line}: jenis wajib point atau terbuka.";
            }
            $data[] = [
                'kategori_kuesioner_id' => $this->kategoriId,
                'pertanyaan' => $pertanyaan,
                'jenis_pertanyaan' => $jenis,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages(['file_excel' => $errors]);
        }
        if (!$data) {
            throw ValidationException::withMessages(['file_excel' => 'File tidak berisi pertanyaan.']);
        }
        foreach ($data as $item) {
            PertanyaanKuesioner::create($item);
            $this->count++;
        }
    }
}