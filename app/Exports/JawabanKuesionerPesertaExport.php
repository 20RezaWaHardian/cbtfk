<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JawabanKuesionerPesertaExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function __construct(
        private readonly Collection $kategori,
        private readonly Collection $pertanyaan,
        private readonly Collection $peserta,
        private readonly Collection $jawaban
    ) {
    }

    public function headings(): array
    {
        return [
            [
                'Identitas Peserta',
                '',
                '',
                ...$this->kategori->flatMap(function ($kategori) {
                    if ($kategori->pertanyaan->isEmpty()) {
                        return [];
                    }

                    return collect([$kategori->nama_kategori])
                        ->concat(array_fill(0, max($kategori->pertanyaan->count() - 1, 0), ''));
                })->all(),
            ],
            [
                'No',
                'NIM/Username',
                'Nama Peserta',
                ...$this->pertanyaan->pluck('pertanyaan')->map(
                    fn ($pertanyaan) => trim(strip_tags((string) $pertanyaan))
                )->all(),
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $kolomTerakhir = Coordinate::stringFromColumnIndex(3 + $this->pertanyaan->count());

                $sheet->mergeCells('A1:C1');

                // Setiap kategori digabung selebar jumlah pertanyaan di bawahnya.
                $kolomAwal = 4;
                foreach ($this->kategori as $kategori) {
                    $jumlahPertanyaan = $kategori->pertanyaan->count();
                    if ($jumlahPertanyaan === 0) {
                        continue;
                    }

                    $awal = Coordinate::stringFromColumnIndex($kolomAwal);
                    $akhir = Coordinate::stringFromColumnIndex($kolomAwal + $jumlahPertanyaan - 1);
                    $sheet->mergeCells("{$awal}1:{$akhir}1");
                    $kolomAwal += $jumlahPertanyaan;
                }

                $sheet->getStyle("A1:{$kolomTerakhir}2")->getFont()->setBold(true);
                $sheet->getStyle("A1:{$kolomTerakhir}2")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
            },
        ];
    }

    public function array(): array
    {
        return $this->peserta->values()->map(function ($peserta, $index) {
            $mahasiswa = $peserta->mahasiswa;
            $pesertaEksternal = $peserta->peserta_eksternal;
            $jawabanPeserta = $this->jawaban->get($peserta->id_peserta_ujian, collect());

            $baris = [
                $index + 1,
                $mahasiswa?->nim ?? $pesertaEksternal?->username ?? '-',
                $mahasiswa?->nama ?? $pesertaEksternal?->nama_peserta ?? '-',
            ];

            foreach ($this->pertanyaan as $pertanyaan) {
                $jawaban = $jawabanPeserta->get($pertanyaan->id_pertanyaan_kuesioner);
                $baris[] = $pertanyaan->jenis_pertanyaan === 'point'
                    ? ($jawaban?->jawaban ?? '-')
                    : ($jawaban?->jawaban_terbuka ?? '-');
            }

            return $baris;
        })->all();
    }
}