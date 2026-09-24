<?php
namespace App\Imports;

use App\Models\Soal;
use App\Models\Pilgan;
use App\Models\Essay;
use App\Models\PaketHasSoal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SoalImport implements ToCollection, WithHeadingRow
{
    protected $id_paket_soal;
    protected $kategori_soal_id;
    protected $poinPerSoal;

    public function __construct($id_paket_soal, $kategori_soal_id,$poinPerSoal)
    {
        $this->id_paket_soal = $id_paket_soal;
        $this->kategori_soal_id = $kategori_soal_id;
        $this->poinPerSoal = $poinPerSoal;
    }

    public function collection(Collection $rows)
    {
        $poinPerSoal = $this->poinPerSoal;

        foreach ($rows as $row) {
            $soal = Soal::create([
                'kategori_soal_id' => $this->kategori_soal_id,
                'jenis_soal' => $row['jenis_soal'],
                'pertanyaan' => $row['pertanyaan'],
                'kunci' => $row['kunci'],
                'source' => 'import',
                'poin' => $poinPerSoal,
            ]);

           PaketHasSoal::create([
                'paket_soal_id' => $this->id_paket_soal,
                'soal_id' => $soal->id_soal,
                'poin' => $poinPerSoal,
            ]);


            if ($row['jenis_soal'] === 'pilgan') {
                $kodeJawaban = ['A', 'B', 'C', 'D', 'E'];

                for ($i = 0; $i < 5; $i++) {
                    if (!empty($row['pilgan_' . chr(97 + $i)])) { // 'a' = 97 di ASCII
                        Pilgan::create([
                            'soal_id' => $soal->id_soal,
                            'teks' => $row['pilgan_' . chr(97 + $i)],
                            'kode' => $kodeJawaban[$i],
                        ]);
                    }
                }
            }

            if ($row['jenis_soal'] === 'essay') {
                Essay::create([
                    'soal_id' => $soal->id_soal,
                    'pertanyaan' => $row['pertanyaan'],
                    'kunci' => $row['kunci'],
                ]);
            }


        }
    }
}
