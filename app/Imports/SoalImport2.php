<?php
namespace App\Imports;

use App\Models\Soal;
use App\Models\Pilgan;
use App\Models\Essay;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SoalImport2 implements ToCollection, WithHeadingRow
{

    protected $sub_kategori_soal_id;
    protected $poinPerSoal;

    public function __construct($sub_kategori_soal_id, $poinPerSoal)
    {
        $this->sub_kategori_soal_id = $sub_kategori_soal_id;
        $this->poinPerSoal = $poinPerSoal;
    }

    public function collection(Collection $rows)
    {
        $poinPerSoal = $this->poinPerSoal;


        foreach ($rows as $row) {
            $jenisSoal = strtolower(trim($row['jenis_soal'] ?? ''));
            $pertanyaan = $row['pertanyaan'] ?? null;
            $kunci = $row['kunci'] ?? null;
            $pilihanJawaban = $row['pilihan_jawaban'] ?? null;

            if (blank($jenisSoal) || blank($pertanyaan) || blank($kunci)) {
                continue;
            }

            if ($jenisSoal === 'pilgan' && blank($pilihanJawaban)) {
                continue;
            }

            if (! in_array($jenisSoal, ['pilgan', 'essay'])) {
                continue;
            }

            DB::transaction(function () use ($jenisSoal, $pertanyaan, $kunci, $pilihanJawaban, $poinPerSoal) {
                $soal = Soal::create([
                    'sub_kategori_soal_id' => $this->sub_kategori_soal_id,
                    'jenis_soal' => $jenisSoal,
                    'pertanyaan' => $pertanyaan,
                    'kunci' => $kunci,
                    'source' => 'import',
                    'poin' => $poinPerSoal,
                    'id_pelaku' => auth()->user()->id_user ?? null,
                    'created_by' => auth()->user()->id_asal ?? null,
                ]);


                if ($jenisSoal === 'pilgan') {

                    $kodeJawaban = ['A', 'B', 'C', 'D', 'E'];

                    $array = array_filter(array_map('trim', explode(';', $pilihanJawaban)));

                // for ($i = 0; $i < 5; $i++) {
                //     if (!empty($row['pilgan_' . chr(97 + $i)])) { // 'a' = 97 di ASCII
                //         Pilgan::create([
                //             'soal_id' => $soal->id_soal,
                //             'teks' => $row['pilgan_' . chr(97 + $i)],
                //             'kode' => $kodeJawaban[$i],
                //         ]);
                //     }
                // }

                    foreach ($array as $i => $item) {
                        // pastikan tidak lebih dari 5 opsi
                        if ($i < count($kodeJawaban)) {
                            Pilgan::create([
                                'soal_id' => $soal->id_soal,
                                'teks'    => $item,
                                'kode'    => $kodeJawaban[$i],
                            ]);
                        }
                    }
                }

                if ($jenisSoal === 'essay') {
                    Essay::create([
                        'soal_id' => $soal->id_soal,
                        'pertanyaan' => $pertanyaan,
                        'kunci' => $kunci,
                    ]);
                }
            });


        }
    }
}
