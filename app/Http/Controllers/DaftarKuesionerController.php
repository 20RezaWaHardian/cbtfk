<?php

namespace App\Http\Controllers;

use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Spatie\LaravelPdf\Facades\Pdf;
use App\DataTables\DaftarUjianKuesionerDataTable;

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
        $pil_jwb = DB::table('pilihan_jwb_kue')
            ->select('id_pilihan_jwb_kue', 'nama_pilihan')
            ->get();


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

        return view('kuesioner.daftar-kue.detail', compact(
            'kue',
            'total_responden',
            'pil_jwb',
            'rekapMap',
            'totalPerPertanyaan'
        ));
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
        $pil_jwb = DB::table('pilihan_jwb_kue')
            ->select('id_pilihan_jwb_kue', 'nama_pilihan')
            ->get();


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

        return view('kuesioner.daftar-kue.detail', compact(
            'kue',
            'total_responden',
            'pil_jwb',
            'rekapMap',
            'totalPerPertanyaan'
        ));
    }


    public function downloadKuesioner($id_kuesioner)
    {
        $kue = Kuesioner::with('kategori_kuesioner')
            ->where('id_kuesioner', decrypt($id_kuesioner))
            ->first();

        // total responden unik yang isi kuesioner ini
        $total_responden = JawabanKuesioner::with('pertanyaan.kategoriKue')
            ->whereHas('pertanyaan.kategoriKue', function ($q) use ($id_kuesioner) {
                $q->where('kuesioner_id', decrypt($id_kuesioner));
            })
            ->distinct('peserta_ujian_id')
            ->count('peserta_ujian_id');

        $pil_jwb = DB::table('pilihan_jwb_kue as a')->get();

        $jwb_kue = JawabanKuesioner::select(
            'id_jawaban_kuesioner',
            'peserta_ujian_id',
            'pertanyaan_kuesioner_id',
            'pil_jwb_kue_id',
            'jawaban'
        )->get();
        
        $html = view('kuesioner.daftar-kue.download', compact(
            'kue',
            'total_responden',
            'jwb_kue',
            'pil_jwb'
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
