<?php

namespace App\Http\Controllers;

use App\Exports\JawabanKuesionerPesertaExport;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\PesertaUjian;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Spatie\LaravelPdf\Facades\Pdf;
use App\DataTables\DaftarUjianKuesionerDataTable;
use Maatwebsite\Excel\Facades\Excel;

class DaftarKuesionerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read daftar-kuesioner');
    }

    public function index()
    {
        $kuesioner = Kuesioner::all();
        return view('kuesioner.daftar-kue.index', compact('kuesioner'));
    }

    public function daftarUjian(DaftarUjianKuesionerDataTable $dataTable,$id_kuesioner)
    {
        return $dataTable->with(['id_kuesioner' => $id_kuesioner])->render('kuesioner.daftar-kue.daftar-ujian');
    }

    public function detail($id_ujian)
    {
        $ujianId = decrypt($id_ujian);
        $ujian = DB::table('ujian')->where('id_ujian',$ujianId)->first();
        $kue = Kuesioner::with([
            'kategori_kuesioner.pertanyaan'
        ])->where('id_kuesioner', $ujian->kuesioner_id)
        ->firstOrFail();


        // total responden unik
        $total_responden = JawabanKuesioner::with('pesertaUjian.ujian','pertanyaan.kategoriKue')
            ->whereHas('pesertaUjian.ujian', function($q)use ($ujian){
                $q->where('id_ujian',$ujian->id_ujian);
                // $q->where('kuesioner_id',$ujian->kuesioner_id);
            })
            // ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
            //     $q->where('kuesioner_id', $ujian->kuesioner_id);
            // })
            ->distinct('peserta_ujian_id')
            ->count('peserta_ujian_id');


        // semua pilihan jawaban
        // $pil_jwb = DB::table('pilihan_jwb_kue')
        //     ->select('id_pilihan_jwb_kue', 'nama_pilihan')
        //     ->get();
        $pil_jwb = DB::table('pilihan_jwb_kue')
                    ->select('id_pilihan_jwb_kue', 'nama_pilihan')
                    ->orderBy('id_pilihan_jwb_kue')
                    ->get();
        if ($pil_jwb->isEmpty()) {
            $pil_jwb = collect([
                (object) [
                    'id_pilihan_jwb_kue' => 1,
                    'nama_pilihan' => '1',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 2,
                    'nama_pilihan' => '2',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 3,
                    'nama_pilihan' => '3',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 4,
                    'nama_pilihan' => '4',
                ],
            ]);
        }


        // ambil rekap langsung dari SQL
        $rekapJawaban = JawabanKuesioner::select(
                'pertanyaan_kuesioner_id',
                'pil_jwb_kue_id',
                DB::raw('COUNT(*) as total')
            )
            ->with('pesertaUjian.ujian','pertanyaan.kategoriKue')
            ->whereHas('pesertaUjian.ujian', function($q)use ($ujian){
                $q->where('id_ujian',$ujian->id_ujian);
                $q->where('kuesioner_id',$ujian->kuesioner_id);
            })
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
                $q->where('kuesioner_id', $ujian->kuesioner_id);
            })
            ->groupBy(
                'pertanyaan_kuesioner_id',
                'pil_jwb_kue_id'
            )
            ->get();

        // total jawaban per pertanyaan
        $totalPerPertanyaan = JawabanKuesioner::select(
                'pertanyaan_kuesioner_id',
                DB::raw('COUNT(*) as total')
            )
            ->with('pesertaUjian.ujian','pertanyaan.kategoriKue')
            ->whereHas('pesertaUjian.ujian', function($q)use ($ujian){
                $q->where('id_ujian',$ujian->id_ujian);
                $q->where('kuesioner_id',$ujian->kuesioner_id);
            })
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
                $q->where('kuesioner_id', $ujian->kuesioner_id);
            })
            ->groupBy('pertanyaan_kuesioner_id')
            ->pluck('total', 'pertanyaan_kuesioner_id');
        


        // mapping biar cepat akses
        $rekapMap = [];

        foreach ($rekapJawaban as $r) {
            $rekapMap[$r->pertanyaan_kuesioner_id][$r->pil_jwb_kue_id] = $r->total;
        }

        $jawabanTerbuka = JawabanKuesioner::query()
            ->select('pertanyaan_kuesioner_id', 'jawaban_terbuka')
            ->whereHas('pesertaUjian.ujian', function ($q) use ($ujian) {
                $q->where('id_ujian', $ujian->id_ujian)
                    ->where('kuesioner_id', $ujian->kuesioner_id);
            })
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
                $q->where('kuesioner_id', $ujian->kuesioner_id);
            })
            ->whereNotNull('jawaban_terbuka')
            ->where('jawaban_terbuka', '<>', '')
            ->orderBy('id_jawaban_kuesioner')
            ->get()
            ->groupBy('pertanyaan_kuesioner_id');
        return view('kuesioner.daftar-kue.detail', compact(
            'kue',
            'id_ujian',
            'total_responden',
            'pil_jwb',
            'rekapMap',
            'totalPerPertanyaan',
            'jawabanTerbuka'
        ));
    }

    public function exportJawabanPeserta($id_ujian)
    {
        $ujianId = decrypt($id_ujian);
        $ujian = DB::table('ujian')->where('id_ujian', $ujianId)->first();
        $kue = Kuesioner::with([
            'kategori_kuesioner' => fn ($query) => $query->orderBy('id_kategori_kuesioner'),
            'kategori_kuesioner.pertanyaan' => fn ($query) => $query->orderBy('id_pertanyaan_kuesioner'),
        ])->where('id_kuesioner', $ujian->kuesioner_id)->firstOrFail();

        // Kolom pertanyaan mengikuti urutan kategori dan pertanyaan pada kuesioner.
        $pertanyaan = $kue->kategori_kuesioner->flatMap->pertanyaan->values();
        $pertanyaanIds = $pertanyaan->pluck('id_pertanyaan_kuesioner');

        // Hanya peserta ujian ini yang telah memiliki jawaban kuesioner.
        $pesertaIds = JawabanKuesioner::query()
            ->whereIn('pertanyaan_kuesioner_id', $pertanyaanIds)
            ->whereHas('pesertaUjian', fn ($query) => $query->where('ujian_id', $ujianId))
            ->distinct()
            ->pluck('peserta_ujian_id');

        $peserta = PesertaUjian::with(['mahasiswa', 'peserta_eksternal'])
            ->where('ujian_id', $ujianId)
            ->whereIn('id_peserta_ujian', $pesertaIds)
            ->orderBy('id_peserta_ujian')
            ->get();

        // Satu query jawaban, lalu kelompokkan untuk mencegah query per peserta/kolom.
        $jawaban = JawabanKuesioner::query()
            ->whereIn('peserta_ujian_id', $peserta->pluck('id_peserta_ujian'))
            ->whereIn('pertanyaan_kuesioner_id', $pertanyaanIds)
            ->get()
            ->groupBy('peserta_ujian_id')
            ->map(fn ($items) => $items->keyBy('pertanyaan_kuesioner_id'));

        $namaFile = 'jawaban-kuesioner-' . str($kue->judul_kuesioner)->slug() . '.xlsx';

        return Excel::download(
            new JawabanKuesionerPesertaExport($kue->kategori_kuesioner, $pertanyaan, $peserta, $jawaban),
            $namaFile
        );
    }

    public function detailKueSebelum($id_ujian)
    {
        $ujianId = decrypt($id_ujian);
        $ujian = DB::table('ujian')->where('id_ujian',$ujianId)->first();
        $kue = Kuesioner::with([
            'kategori_kuesioner.pertanyaan'
        ])->where('id_kuesioner', $ujian->kuesioner_sebelum_id)
        ->firstOrFail();




        // total responden unik
        $total_responden = JawabanKuesioner::with('pesertaUjian.ujian','pertanyaan.kategoriKue')
            ->whereHas('pesertaUjian.ujian', function($q)use ($ujian){
                $q->where('id_ujian',$ujian->id_ujian);
                // $q->where('kuesioner_id',$ujian->kuesioner_id);
            })
            ->distinct('peserta_ujian_id')
            ->count('peserta_ujian_id');


        // semua pilihan jawaban
        // $pil_jwb = DB::table('pilihan_jwb_kue')
        //     ->select('id_pilihan_jwb_kue', 'nama_pilihan')
        //     ->get();
        $pil_jwb = DB::table('pilihan_jwb_kue')
                    ->select('id_pilihan_jwb_kue', 'nama_pilihan')
                    ->orderBy('id_pilihan_jwb_kue')
                    ->get();
        if ($pil_jwb->isEmpty()) {
            $pil_jwb = collect([
                (object) [
                    'id_pilihan_jwb_kue' => 1,
                    'nama_pilihan' => '1',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 2,
                    'nama_pilihan' => '2',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 3,
                    'nama_pilihan' => '3',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 4,
                    'nama_pilihan' => '4',
                ],
            ]);
        }


        // ambil rekap langsung dari SQL
        $rekapJawaban = JawabanKuesioner::select(
                'pertanyaan_kuesioner_id',
                'pil_jwb_kue_id',
                DB::raw('COUNT(*) as total')
            )
            ->with('pesertaUjian.ujian','pertanyaan.kategoriKue')
            ->whereHas('pesertaUjian.ujian', function($q)use ($ujian){
                $q->where('id_ujian',$ujian->id_ujian);
                $q->where('kuesioner_sebelum_id',$ujian->kuesioner_sebelum_id);
            })
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
                $q->where('kuesioner_id', $ujian->kuesioner_sebelum_id);
            })
            ->groupBy(
                'pertanyaan_kuesioner_id',
                'pil_jwb_kue_id'
            )
            ->get();

        // total jawaban per pertanyaan
        $totalPerPertanyaan = JawabanKuesioner::select(
                'pertanyaan_kuesioner_id',
                DB::raw('COUNT(*) as total')
            )
            ->with('pesertaUjian.ujian','pertanyaan.kategoriKue')
            ->whereHas('pesertaUjian.ujian', function($q)use ($ujian){
                $q->where('id_ujian',$ujian->id_ujian);
                $q->where('kuesioner_sebelum_id',$ujian->kuesioner_sebelum_id);
            })
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
                $q->where('kuesioner_id', $ujian->kuesioner_sebelum_id);
            })
            ->groupBy('pertanyaan_kuesioner_id')
            ->pluck('total', 'pertanyaan_kuesioner_id');
        


        // mapping biar cepat akses
        $rekapMap = [];

        foreach ($rekapJawaban as $r) {
            $rekapMap[$r->pertanyaan_kuesioner_id][$r->pil_jwb_kue_id] = $r->total;
        }

        $jawabanTerbuka = JawabanKuesioner::query()
            ->select('pertanyaan_kuesioner_id', 'jawaban_terbuka')
            ->whereHas('pesertaUjian.ujian', function ($q) use ($ujian) {
                $q->where('id_ujian', $ujian->id_ujian)
                    ->where('kuesioner_sebelum_id', $ujian->kuesioner_sebelum_id);
            })
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($ujian) {
                $q->where('kuesioner_id', $ujian->kuesioner_sebelum_id);
            })
            ->whereNotNull('jawaban_terbuka')
            ->where('jawaban_terbuka', '<>', '')
            ->orderBy('id_jawaban_kuesioner')
            ->get()
            ->groupBy('pertanyaan_kuesioner_id');
        return view('kuesioner.daftar-kue.detail', compact(
            'kue',
            'total_responden',
            'pil_jwb',
            'rekapMap',
            'totalPerPertanyaan',
            'jawabanTerbuka'
        ));
    }


    public function downloadKuesioner($id_kuesioner)
    {
        $kuesionerId = decrypt($id_kuesioner);
        $kue = Kuesioner::with([
            'kategori_kuesioner' => fn ($query) => $query->orderBy('id_kategori_kuesioner'),
            'kategori_kuesioner.pertanyaan' => fn ($query) => $query->orderBy('id_pertanyaan_kuesioner'),
        ])->where('id_kuesioner', $kuesionerId)->firstOrFail();

        $pertanyaanIds = $kue->kategori_kuesioner
            ->flatMap->pertanyaan
            ->pluck('id_pertanyaan_kuesioner');

        // total responden unik yang isi kuesioner ini
        $total_responden = JawabanKuesioner::query()
            ->whereIn('pertanyaan_kuesioner_id', $pertanyaanIds)
            ->distinct('peserta_ujian_id')
            ->count('peserta_ujian_id');

        // $pil_jwb = DB::table('pilihan_jwb_kue as a')->get();
        $pil_jwb = DB::table('pilihan_jwb_kue')
                    ->select('id_pilihan_jwb_kue', 'nama_pilihan')
                    ->orderBy('id_pilihan_jwb_kue')
                    ->get();
        if ($pil_jwb->isEmpty()) {
            $pil_jwb = collect([
                (object) [
                    'id_pilihan_jwb_kue' => 1,
                    'nama_pilihan' => '1',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 2,
                    'nama_pilihan' => '2',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 3,
                    'nama_pilihan' => '3',
                ],
                (object) [
                    'id_pilihan_jwb_kue' => 4,
                    'nama_pilihan' => '4',
                ],
            ]);
        }

        // Agregasi di DB agar seluruh baris jawaban tidak dimuat ke memori PHP.
        $rekapJawaban = JawabanKuesioner::query()
            ->select(
                'pertanyaan_kuesioner_id',
                'pil_jwb_kue_id',
                DB::raw('COUNT(*) as total')
            )
            ->whereIn('pertanyaan_kuesioner_id', $pertanyaanIds)
            ->groupBy('pertanyaan_kuesioner_id', 'pil_jwb_kue_id')
            ->get();

        $rekapMap = [];
        foreach ($rekapJawaban as $rekap) {
            $rekapMap[$rekap->pertanyaan_kuesioner_id][$rekap->pil_jwb_kue_id] = $rekap->total;
        }

        $totalPerPertanyaan = JawabanKuesioner::query()
            ->select('pertanyaan_kuesioner_id', DB::raw('COUNT(*) as total'))
            ->whereIn('pertanyaan_kuesioner_id', $pertanyaanIds)
            ->groupBy('pertanyaan_kuesioner_id')
            ->pluck('total', 'pertanyaan_kuesioner_id');
        
        $html = view('kuesioner.daftar-kue.download', compact(
            'kue',
            'total_responden',
            'pil_jwb',
            'rekapMap',
            'totalPerPertanyaan'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->simpleTables = true;
        $mpdf->SetDisplayMode('fullwidth');

        $mpdf->SetHTMLHeader('<div style="text-align: right; font-size: 10px;">Laporan Kuesioner</div>');
        $mpdf->SetHTMLFooter('<div style="text-align: center; font-size: 10px;">Halaman {PAGENO}</div>');

        $mpdf->WriteHTML($html);
        $mpdf->Output('laporan.pdf', 'I');
        // return view('kuesioner.daftar-kue.detail', compact('kue', 'totaljwb_kue', 'jwb_kue', 'pil_jwb'));
    }
}
