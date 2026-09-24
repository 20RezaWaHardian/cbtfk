<?php

namespace App\Http\Controllers;

use App\Models\JawabanMhs;
use App\Models\PaketHasSoal;
use Inertia\Inertia;
use App\Models\Ujian;
use App\Models\PesertaUjian;
use App\Models\Soal;
use Illuminate\Http\Request;

class UjianInertiaController extends Controller
{
    public function index($ujianId, $pesertaUjianId)
    {


        try {
            $ujianId = decrypt($ujianId);
            $pesertaId = decrypt($pesertaUjianId);

            $ujian = Ujian::findorfail($ujianId);
            $peserta = PesertaUjian::with('mahasiswa_rombel')->findorfail($pesertaId);
            $peserta->status_pengerjaan = 1;
            $peserta->save();


            $soal = PaketHasSoal::with('soal')->where('paket_soal_id', $ujian->paket_soal_id)->get();
            $jawabanMhs = JawabanMhs::where('id_ujian', $ujianId)
                ->where('id_peserta_ujian', $peserta->id_peserta_ujian)
                ->get()
                ->pluck('id_soal')
                ->toArray();
            // $username = auth()->user()->username;

            return Inertia::render('Beranda/Index', [
                'soal' => $soal,
                'jumlahSoal' => count($soal),
                'id_ujian' => $ujianId,
                'id_peserta_ujian' => encrypt($peserta->id_peserta_ujian),
                'answered_soal_ids' => $jawabanMhs,
                'peserta' => $peserta,
                'waktu_ujian' => $ujian,
            ]);
            // return Inertia::render('Ujian/Index', compact('ujian', 'peserta'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function soalId($id_soal, $id_peserta_ujian, $id_ujian)
    {
        // dd($id_ujian);
        $soal = Soal::where('id_soal', $id_soal)->with('soal_pilgan', 'soal_essay')->first();
        $data = [];
        if ($soal->jenis_soal == 'pilgan') {
            $data['jenis'] = $soal->jenis_soal;
            $data['idSoal'] = $id_soal;
            $data['pertanyaan'] = $soal->pertanyaan;

            $jawabanMhs = JawabanMhs::where('id_soal', $id_soal)
                ->where('id_ujian', $id_ujian)
                ->where('id_peserta_ujian', decrypt($id_peserta_ujian))
                ->first();
            // dd($jawabanMhs);
            foreach ($soal->soal_pilgan as $a) {
                $isSelected = $jawabanMhs && $jawabanMhs->jawaban_mhs == $a->kode;

                $data['pilgan']['kode'][] = $a->kode;
                $data['pilgan']['label'][] = $a->teks;
                if ($isSelected) {
                    $data['jawaban_mhs'] = $a->kode; // Menyimpan jawaban yang terpilih
                }
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
        $paket_soal = Ujian::where('id_ujian', $req->id_ujian)->first();

        $jawabanMhs = JawabanMhs::where('id_ujian', $req->id_ujian)
            ->where('id_peserta_ujian', decrypt($req->id_peserta_ujian))
            ->where('id_soal', $req->id_soal)
            ->first();

        if ($jawabanMhs) {
            $jawabanMhs->update([
                'jawaban_mhs' => $req->jawaban_mhs
            ]);
        } else {
            $jawaban = new JawabanMhs();
            $jawaban->id_ujian = $req->id_ujian;
            $jawaban->id_peserta_ujian = decrypt($req->id_peserta_ujian);
            $jawaban->id_paket_soal = $paket_soal->paket_soal_id;
            $jawaban->id_soal = $req->id_soal;
            $jawaban->jawaban_mhs = $req->jawaban_mhs;
            $jawaban->save();
        }




        return response()->json(['message' => 'oke']);
    }
    public function selesaiUjian(Request $req)
    {
        $paket_soal = Ujian::where('id_ujian', $req->id_ujian)->first();

        $jawabanMhs = JawabanMhs::where('id_ujian', $req->id_ujian)
            ->where('id_peserta_ujian', decrypt($req->id_peserta_ujian))
            ->where('id_soal', $req->id_soal)
            ->first();

        if ($jawabanMhs) {
            $jawabanMhs->update([
                'jawaban_mhs' => $req->jawaban_mhs
            ]);
        } else {
            $jawaban = new JawabanMhs();
            $jawaban->id_ujian = $req->id_ujian;
            $jawaban->id_peserta_ujian = decrypt($req->id_peserta_ujian);
            $jawaban->id_paket_soal = $paket_soal->paket_soal_id;
            $jawaban->id_soal = $req->id_soal;
            $jawaban->jawaban_mhs = $req->jawaban_mhs;
            $jawaban->save();
        }




        return response()->json([
            'code' => '200',
            'message' => 'success',
            "resource" => url("/form-selesai")
        ]);
    }

    public function formSelesai()
    {
        $user = auth()->user()->username;
        return Inertia::render('Beranda/FormSelesai', [
            'user' => $user
        ]);
    }
}
