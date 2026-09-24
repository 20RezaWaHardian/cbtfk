<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\Ujian;
use App\Models\PesertaUjian;

class ExportController extends Controller
{
    public function exportPdfNilai($id_ujian)
    {
        $ujian = Ujian::where('id_ujian',$id_ujian)->first();

        $peserta = PesertaUjian::with('mhs_pt')->where('ujian_id',$id_ujian)->get();
        $data = [
            'id_ujian' => $ujian->id_ujian,
            'nama_ujian' => $ujian->nama_ujian,
        ]; 

        $pdf = PDF::loadView('export.daftar_nilai',compact('data','peserta'));
       
        // return $pdf->download('nilai.pdf');
        return $pdf->stream();
    }
}
