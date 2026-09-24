<?php

namespace App\Http\Controllers;

use App\Models\JawabanMhs;
use App\Models\PaketHasSoal;
use App\Models\PaketSoal;
use App\Models\Soal;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeInertiaController extends Controller
{
    public function index()
    {
        $soal = PaketHasSoal::with('soal')->where('paket_soal_id', 2)->get();
        return Inertia::render('Beranda/Index', [
            'soal' => $soal,
            'jumlahSoal' => count($soal),
            'id_ujian' => 6
        ]);
    }

    public function soalId($id_soal)
    {
        $soal = Soal::where('id_soal', $id_soal)->with('soal_pilgan', 'soal_essay')->first();
        $data = [];
        if ($soal->jenis_soal == 'pilgan') {
            $data['jenis'] = 'pilgan';
            $data['idSoal'] = $id_soal;
            $data['pertanyaan'] = $soal->pertanyaan;
            foreach ($soal->soal_pilgan as $a) {
                $data['pilgan']['kode'][] = $a->kode;
                $data['pilgan']['label'][] = $a->teks;
            }
            // $data['pilgan'] = $soal->soal_pilgan;
        } elseif ($soal->jenis_soal == 'essay') {
            $data['jenis'] = 'essay';
            $data['idSoal'] = $id_soal;
            $data['pertanyaan'] = $soal->pertanyaan;
        }
        // dd($data);
        return $data;
    }

    public function jawabanUser(Request $req)
    {
        // dd($req);
        $paket_soal = Ujian::where('id_ujian', $req->id_ujian)->first();

        $jawaban = new JawabanMhs();
        $jawaban->id_ujian = $req->id_ujian;
        $jawaban->id_paket_soal = $paket_soal->paket_soal_id;
        $jawaban->id_soal = $req->id_soal;
        $jawaban->jawaban_mhs = $req->jawaban_mhs;
        $jawaban->save();

        return response()->json(['message' => 'oke']);
    }

    public function tataCara()
    {
        return Inertia::render('Beranda/Tatacara');
    }
}
