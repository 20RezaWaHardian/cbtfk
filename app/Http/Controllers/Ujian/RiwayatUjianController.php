<?php

namespace App\Http\Controllers\Ujian;

use App\Models\Ujian;
use App\Models\PesertaUjian;
use App\Models\PilganJawab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\RiwayatUjianDataTable;
use App\DataTables\DaftarPesertaDataTable;
use App\Exports\AnalisisSoalExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use DB;

class RiwayatUjianController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read ujian/riwayat-ujian');
    }

    public function index(RiwayatUjianDataTable $dataTable)
    {
        return $dataTable->render('ujian/riwayat-ujian/index');
    }

    public function resume($id, DaftarPesertaDataTable $dataTable){
        $ujian = Ujian::findorfail(decrypt($id));

        return $dataTable->with(['id_ujian' => $ujian->id_ujian])->render('ujian.riwayat-ujian.resume', compact('ujian'));
    }

    private function getAnalisisSoalData($id_ujian)
    {
        $idUjian = decrypt($id_ujian);
        $jadwal = Ujian::findOrFail($idUjian);

        // 1. Ambil semua mahasiswa yang ikut ujian tersebut
        $peserta = PesertaUjian::where('ujian_id', $idUjian)->get(); 
        $N = $peserta->count();
        $G = max(1, (int) round($N * 0.27)); // Ukuran grup 27% (minimal 1 agar tidak pembagian nol)

        // 2. Urutkan berdasarkan skor total (dari tertinggi ke terendah)
        $sortedPeserta = $peserta->sortByDesc('total_score');

        // 3. Potong menjadi dua grup
        $grupAtas = $sortedPeserta->take($G)->pluck('id_peserta_ujian')->toArray();
        $grupBawah = $sortedPeserta->take(-$G)->pluck('id_peserta_ujian')->toArray();

        $daftarSoal = DB::table('paket_has_soal as a')
                        ->join('soal as b','a.soal_id','b.id_soal')
                        ->where('a.paket_soal_id',$jadwal->paket_soal_id)
                        ->select('a.soal_id','b.pertanyaan')
                        ->get();

        $hasilAnalisis = [];

        if ($N === 0) {
            return [
                'id_ujian' => $id_ujian,
                'jadwal' => $jadwal,
                'hasilAnalisis' => $hasilAnalisis,
            ];
        }

        $pesertaIds = $peserta->pluck('id_peserta_ujian')->toArray();

        foreach ($daftarSoal as $soal) {
            // A. Hitung JUMLAH BENAR di masing-masing grup
            $benarAtas = PilganJawab::where('soal_id', $soal->soal_id)
                        ->whereIn('peserta_ujian_id', $grupAtas)
                        ->where('status', 'T')->count();

            $benarBawah = PilganJawab::where('soal_id', $soal->soal_id)
                        ->whereIn('peserta_ujian_id', $grupBawah)
                        ->where('status', 'T')->count();

            $totalBenarSemua = PilganJawab::where('soal_id', $soal->soal_id)
                        ->whereIn('peserta_ujian_id', $pesertaIds)
                        ->where('status', 'T')->count();

            // B. Hitung PROPORSI (PA dan PB)
            $PA = $benarAtas / $G;
            $PB = $benarBawah / $G;

            // C. Masukkan ke Rumus sesuai Template
            $difficulty = $totalBenarSemua / $N; // Parameter Sulit/Mudah
            $discrimination = $PA - $PB;         // Parameter Daya Beda

            // D. Simpan hasil ke array untuk ditampilkan
            $hasilAnalisis[] = [
                'soal_id' => $soal->pertanyaan,
                'difficulty' => $difficulty,
                'discrimination' => $discrimination,
                'label' => ($difficulty < 0.3) ? 'Sulit' : (($difficulty > 0.7) ? 'Mudah' : 'Sedang')
            ];
        }

        return [
            'id_ujian' => $id_ujian,
            'jadwal' => $jadwal,
            'hasilAnalisis' => collect($hasilAnalisis)->sortBy('difficulty')->values()->all(),
        ];
    }

    public function analisisSoal($id_ujian)
    {
        $data = $this->getAnalisisSoalData($id_ujian);

        return view('ujian/riwayat-ujian/analisis-soal', $data);
    }

    public function exportAnalisisSoalPdf($id_ujian)
    {
        $data = $this->getAnalisisSoalData($id_ujian);
        $logoPath = public_path('assets/images/logos/logo_unja.png');
        $data['logoBase64'] = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $fileName = 'analisis-soal-' . str_replace(' ', '-', strtolower($data['jadwal']->nama_ujian ?? 'ujian')) . '.pdf';

        $pdf = PDF::loadView('export.analisis_soal', $data)->setPaper('a4', 'landscape');

        return $pdf->stream($fileName);
    }

    public function exportAnalisisSoalExcel($id_ujian)
    {
        $data = $this->getAnalisisSoalData($id_ujian);
        $fileName = 'analisis-soal-' . str_replace(' ', '-', strtolower($data['jadwal']->nama_ujian ?? 'ujian')) . '.xlsx';

        return Excel::download(new AnalisisSoalExport($data), $fileName);
    }

}
