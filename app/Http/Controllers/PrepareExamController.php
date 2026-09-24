<?php

namespace App\Http\Controllers;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\PaketHasSoal;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use App\Models\SBMahasiswaRombel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class PrepareExamController extends Controller
{
    public function token($id_peserta_ujian, $id_ujian)
    {
        $pesertaId = decrypt($id_peserta_ujian);
        $ujianId = decrypt($id_ujian);

        $peserta = PesertaUjian::findorfail($pesertaId);
        $ujian = Ujian::findorfail($ujianId);
        return view('exam.token', compact('peserta', 'ujian'));
    }

    public function cekToken(Request $request)
    {
        // dd($request);
        $ujian = Ujian::findorfail(decrypt($request->id_ujian));
        // dd($ujian);
        if($ujian->token == $request->token)
        {
            return redirect()
                    ->route('peserta.faceRegister', ['id_ujian' => $request->id_ujian, 'id_peserta_ujian' => $request->id_peserta_ujian]);
        }else{
            return redirect()->back()->with("error","Token Yang Di Inputkan Tidak Terdaftar");
        }
    }
    public function faceRegister($id_peserta_ujian, $id_ujian)
    {

        $pesertaId = decrypt($id_peserta_ujian);
        $ujianId = decrypt($id_ujian);

        $peserta = PesertaUjian::findorfail($pesertaId);
        $ujian = Ujian::findorfail($ujianId);



        #cek gambarnya apakah sudah face register atau blm
        if ($peserta->face_register) {

            return $this->agreement($id_peserta_ujian, $id_ujian);
        }

        return view('exam.face-register', compact('peserta', 'ujian'));
    }

    public function storeGambar(Request $request)
    {
        try {
            $ujianId = decrypt($request->ujianId);
            $pesertaId = decrypt($request->pesertaId);

            $ujian = Ujian::findorfail($ujianId);
            $peserta = PesertaUjian::findorfail($pesertaId);

            $img = $request->image;

            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);

            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);



            $path = "foto_peserta/" . date('Y') . '/' . date('m') . '/' . date('d') . '/';
            if (!File::isDirectory('regis_peserta_ujian/' . $path)) {
                File::makeDirectory('regis_peserta_ujian/' . $path, 0777, true, true);
            }

            if($peserta->id_mhs_pt)
            {
                $file = $peserta->mahasiswa->nim . '-' . date('YmdHis') . '.jpeg';
            }else{
                $file = ($peserta->peserta_eksternal->username ?? $peserta->id_peserta_ujian) . '-' . date('YmdHis') . '.jpeg';
            }

            $simpan = Storage::disk('simpan_foto_register')->put($path . $file, $image_base64);
            if ($simpan) {
                if ($peserta->face_register) {
                    Storage::disk('simpan_foto_register')->delete($peserta->face_register);
                }
                $peserta->update([
                    'face_register' => $path . $file
                ]);
                LogAktifitas::catat("Berhasil Melakukan Face Register");
                return response()->json([
                    'message' => 'Foto Berhasil Disimpan!',
                    'redirect' => route('peserta.agreement', [
                        'id_peserta_ujian' => $request->pesertaId,
                        'id_ujian' => $request->ujianId
                    ]),
                ], 200);
            } else {
                LogAktifitas::catat("Gagal Melakukan Face Register");
                return response()->json(['message' => 'Silahkan Coba Lagi!'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function agreement($id_peserta_ujian, $id_ujian)
    {

        $pesertaId = decrypt($id_peserta_ujian);
        $ujianId = decrypt($id_ujian);

        $peserta = PesertaUjian::findorfail($pesertaId);
        $ujian = Ujian::findorfail($ujianId);
        return view('exam.agreement', ['ujian' => $ujian, 'peserta' => $peserta]);
    }

    public function confirm($id_ujian, $id_peserta_ujian)
    {
        $ujian = Ujian::with('paket_soal')->where('id_ujian', decrypt($id_ujian))->first();
        $durasi_jam   =  date('H', strtotime($ujian->paket_soal->durasi));
        $durasi_menit =  date('i', strtotime($ujian->paket_soal->durasi));
        $durasi_detik =  date('s', strtotime($ujian->paket_soal->durasi));

        $total_duration_in_seconds = ($durasi_jam * 3600) + ($durasi_menit * 60) + $durasi_detik;
        $peserta = PesertaUjian::where('id_peserta_ujian', decrypt($id_peserta_ujian))->first();
        if ($peserta->sisa_waktu == null) {
            $peserta->sisa_waktu = $total_duration_in_seconds;
            $peserta->save();
        }
        try {
            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dimulai.',
                'redirect' => url('/exam24/' . $id_ujian . '/participant/' . $id_peserta_ujian)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }
}
