<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\Kuesioner;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use App\Models\JawabanKuesioner;
use App\Models\PertanyaanKuesioner;

class KuesionerPesertaController extends Controller
{
    public function kuesionerSebelum($pesertaUjianId, $ujianId)
    {
        $ujian = Ujian::findOrFail(decrypt($ujianId));
        $kuesioner = Kuesioner::where('terap_kue',2)->where('status',1)->first();
        $peserta = PesertaUjian::findorfail(decrypt($pesertaUjianId));
        $jawaban = JawabanKuesioner::where(
                'peserta_ujian_id',
                $peserta->id_peserta_ujian
            )
            ->get()
            ->keyBy('pertanyaan_kuesioner_id');
            
        $countJwb = JawabanKuesioner::where(
                'peserta_ujian_id',
                $peserta->id_peserta_ujian
            )
            ->whereHas('pertanyaan.kategoriKue',function($q)use($kuesioner){
                $q->where('kuesioner_id',$kuesioner->id_kuesioner);
            })
            ->count();
        
        $countSoal = PertanyaanKuesioner::whereHas('kategoriKue',function($q)use($kuesioner){
                $q->where('kuesioner_id',$kuesioner->id_kuesioner);
            })->count();
        
        $can = false;
        if($countSoal == $countJwb)
        {
            $can = true;
        }

        if ($kuesioner && $ujian && $peserta) {
            LogAktifitas::catat("Masuk Ke Halaman Kuesioner Sebelum Ujian");
            return view('kuesioner.peserta.form-kuesioner-sebelum', compact('kuesioner', 'ujian', 'peserta','jawaban','can'));
        } else {
            LogAktifitas::catat("Gagal Menemukan Kuesioner");
            return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk  Mengisi Kuesioner!');
        }
    }

    public function kuesionerStoreSebelum(Request $request, $pesertaUjianId, $ujianId, $kuesionerId)
    {
        // dd($request);
        if($request->ada_terbuka == 0)
        {
            $request->validate([
                'pertanyaan.*' => 'required|integer|between:1,4',

            ]);
        }

        $allJawaban = [
            'point' => $request->pertanyaan ?? [],
            'teks'  => $request->jawaban_terbuka ?? [],
        ];

        // foreach ($allJawaban['point'] as $pertanyaan_id => $nilai) {
        //     JawabanKuesioner::create([
        //         'pertanyaan_kuesioner_id' => $pertanyaan_id,
        //         'pil_jwb_kue_id' => $nilai,
        //         'jawaban' => $nilai, // khusus angka
        //         'peserta_ujian_id' => $pesertaUjianId,
        //         'jawaban_terbuka' => null,
        //     ]);
        // }

        // foreach ($allJawaban['teks'] as $pertanyaan_id => $nilai) {
        //     JawabanKuesioner::create([
        //         'pertanyaan_kuesioner_id' => $pertanyaan_id,
        //         'pil_jwb_kue_id' => null,
        //         'jawaban' => null,
        //         'peserta_ujian_id' => $pesertaUjianId,
        //         'jawaban_terbuka' => $nilai, // khusus teks
        //     ]);
        // }

        foreach ($allJawaban['point'] as $pertanyaan_id => $nilai) {

            JawabanKuesioner::updateOrCreate(
                [
                    'pertanyaan_kuesioner_id' => $pertanyaan_id,
                    'peserta_ujian_id' => $pesertaUjianId,
                ],
                [
                    'pil_jwb_kue_id' => $nilai,
                    'jawaban' => $nilai,
                    'jawaban_terbuka' => null,
                ]
            );
        }

        foreach ($allJawaban['teks'] as $pertanyaan_id => $nilai) {

            JawabanKuesioner::updateOrCreate(
                [
                    'pertanyaan_kuesioner_id' => $pertanyaan_id,
                    'peserta_ujian_id' => $pesertaUjianId,
                ],
                [
                    'pil_jwb_kue_id' => null,
                    'jawaban' => null,
                    'jawaban_terbuka' => $nilai,
                ]
            );
        }
        
        $peserta = PesertaUjian::findOrFail($pesertaUjianId);
        $peserta->isi_kuesioner = 1;
        $peserta->save();
        LogAktifitas::catat("Telah Mengisi Kuesioner");
        return redirect()->back()
            ->with('success', 'Jawaban berhasil disimpan!');
    }

    public function kuesionerKu($pesertaUjianId, $ujianId, $kuesionerId)
    {

        $kuesioner = Kuesioner::findOrFail($kuesionerId);
        $ujian = Ujian::findOrFail($ujianId);
        $peserta = PesertaUjian::findorfail($pesertaUjianId);


        if ($kuesioner && $ujian && $peserta) {
            LogAktifitas::catat("Masuk Ke Halaman Kuesioner");
            return view('kuesioner.peserta.form-kuesioner', compact('kuesioner', 'ujian', 'peserta'));
        } else {
            LogAktifitas::catat("Gagal Menemukan Kuesioner");
            return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk  Mengisi Kuesioner!');
        }
    }

    public function kuesionerStore(Request $request, $pesertaUjianId, $ujianId, $kuesionerId)
    {
        if($request->ada_terbuka == 0)
        {
            $request->validate([
                'pertanyaan.*' => 'required|integer|between:1,4',

            ]);
        }

        $allJawaban = [
            'point' => $request->pertanyaan ?? [],
            'teks'  => $request->jawaban_terbuka ?? [],
        ];

        foreach ($allJawaban['point'] as $pertanyaan_id => $nilai) {
            JawabanKuesioner::create([
                'pertanyaan_kuesioner_id' => $pertanyaan_id,
                'pil_jwb_kue_id' => $nilai,
                'jawaban' => $nilai, // khusus angka
                'peserta_ujian_id' => $pesertaUjianId,
                'jawaban_terbuka' => null,
            ]);
        }

        foreach ($allJawaban['teks'] as $pertanyaan_id => $nilai) {
            JawabanKuesioner::create([
                'pertanyaan_kuesioner_id' => $pertanyaan_id,
                'pil_jwb_kue_id' => null,
                'jawaban' => null,
                'peserta_ujian_id' => $pesertaUjianId,
                'jawaban_terbuka' => $nilai, // khusus teks
            ]);
        }
        
        $peserta = PesertaUjian::findOrFail($pesertaUjianId);
        $peserta->isi_kuesioner = 1;
        $peserta->save();
        LogAktifitas::catat("Telah Mengisi Kuesioner");
        return redirect()->to('https://siakad-blok.unja.ac.id/riwayatujianmhs')
            ->with('success', 'Jawaban berhasil disimpan!');
    }
}
