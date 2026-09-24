<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\SoalSatuan;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\PaketSoal;
use App\Models\EssayJawab;
use App\Models\JawabanMhs;
use App\Models\PilganJawab;
use App\Models\PaketHasSoal;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\EssayJawabRequest;
use App\Http\Requests\PilganJawabRequest;
use Illuminate\Support\Facades\Storage;


class UjianController extends Controller
{


    public function index($ujianId, $pesertaUjianId)
    {
        date_default_timezone_set("Asia/Jakarta"); // mengatur time zone untuk WIB.
        try {
            $ujianId = decrypt($ujianId);
            $pesertaId = decrypt($pesertaUjianId);

            $ujian = Ujian::findorfail($ujianId);
            if($ujian->id_jenis_ujian == 1)
            {
                $peserta = PesertaUjian::join('sistembl_siakad-uin .mahasiswa as b','b.id_mahasiswa','peserta_ujian.id_mhs_pt')
                                        ->findorfail($pesertaId);
            }else{
                $peserta = PesertaUjian::join('peserta_eksternal as b','b.id_peserta_eksternal','peserta_ujian.id_peserta_eksternal')
                                    ->findorfail($pesertaId);

            }
            // dd($peserta);
            if ($peserta->status_pengerjaan == 0) {
                $peserta->update([
                    'mulai_ujian' => Carbon::now(),
                    'status_pengerjaan' => 1,
                    'ip_address' => request()->ip(),
                ]);
            }


            if ($peserta->status_pengerjaan != 1) {
                if($ujian->id_jenis_ujian == 1)
                {

                    // return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
                    return redirect()->route('dashboard')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
                }else{
                    return redirect()->route('dashboard')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
                }
            }

            $ujian = Ujian::findorfail($ujianId);
            $paket_soal = PaketSoal::where('id_paket_soal', $ujian->paket_soal_id)->first();
            // dd($peserta->json_soal_mhs);
            // $paket_has_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->with('soal')->first();
            if($peserta->json_soal_mhs == null)
            {
                $paket_has_soal = Soal::join('paket_has_soal as b','b.soal_id','soal.id_soal')
                    ->where('b.paket_soal_id',$ujian->paket_soal_id)
                    ->inRandomOrder()
                    ->select('soal.*','b.paket_soal_id')
                    ->get();
                
                $json_sistem = json_encode($paket_has_soal, JSON_PRETTY_PRINT);
                // dd($json_sistem);
                $namaFile = 'soal_'.$peserta->no_mhs.'_' . time() . '.json';

                $cekFileJson = PesertaUjian::select('json_soal_mhs')
                    ->findOrFail($pesertaId);
                
                Storage::disk('public')->put('json/' . $namaFile, $json_sistem);

                $json = PesertaUjian::where('id_peserta_ujian',$pesertaId)->update([
                    'json_soal_mhs' => 'json/' . $namaFile
                ]);

            }


            // $decode = json_decode($peserta_json->json_soal_mhs);
            if (!session()->has("soal_peserta_$pesertaId")) {
                $peserta_json = PesertaUjian::select('json_soal_mhs')
                    ->findOrFail($pesertaId);

                $file = Storage::disk('public')->get($peserta_json->json_soal_mhs);
                $decoded = json_decode($file);

                session([
                    "soal_peserta_$pesertaId" => $decoded
                ]);
            }

            $decode = session("soal_peserta_$pesertaId");

            $paket_has_soal = $decode[0];

            $active_soalId = $paket_has_soal->id_soal;
            // $active_soalId = $paket_has_soal->soal_id;
            // Ambil nama room yang sudah dibuat oleh admin
            $roomName = $ujian->room_name;
            // Buat URL join khusus peserta ini
            // $idPesertaUjian = $peserta->id_peserta_ujian;
            if($ujian->id_jenis_ujian == 1)
            {
                $participantName = $peserta->nama;
            }else{
                $participantName = $peserta->nama_peserta;
            }
            // $participantUrl = "https://".env('METERED_DOMAIN')."/{$roomName}?name={$participantName}&role=participant&autoJoin=true&id={$peserta->id_peserta_ujian}";
            $participantUrl = env('METERED_DOMAIN') . "/{$roomName}";

            
            $id_soals = [];
            foreach ($decode as $item) {
                $id_soals[] = $item->id_soal;
            }

            // $pagination_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)
            //     ->wherein('soal_id',$id_soals)
            //     ->select(['soal_id'])
            //     ->get();
            $pagination_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)
                ->whereIn('soal_id', $id_soals)
                ->orderByRaw("FIELD(soal_id, ".implode(',', $id_soals).")") // Mengurutkan sesuai urutan di $id_soals
                ->select(['soal_id'])
                ->get();

            $totalSoal =  $pagination_soal->count();
            $soalIds = $pagination_soal->pluck('soal_id')->toArray();


            $totalJawaban = DB::table('paket_has_soal as s')
                ->leftJoin('pilgan_jawab as pj', function ($join) use ($soalIds, $pesertaId) {
                    $join->on('s.soal_id', '=', 'pj.soal_id')
                        ->whereIn('s.soal_id', $soalIds)
                        ->where('pj.peserta_ujian_id', $pesertaId);
                })
                ->leftJoin('essay_jawab as ej', function ($join) use ($soalIds, $pesertaId) {
                    $join->on('s.soal_id', '=', 'ej.soal_id')
                        ->whereIn('s.soal_id', $soalIds)
                        ->where('ej.peserta_ujian_id', $pesertaId);
                })
                ->where('s.paket_soal_id', $ujian->paket_soal_id)
                ->where(function ($query) {
                    $query->whereNotNull('pj.soal_id')
                        ->orWhereNotNull('ej.soal_id');
                })
                ->select('s.soal_id')
                ->count();

            $waktu_mulai = strtotime($peserta->mulai_ujian); // UNIX timestamp (detik sejak 1970)
            $durasi_jam   = date('H', strtotime($paket_soal->durasi));
            $durasi_menit = date('i', strtotime($paket_soal->durasi));
            $durasi_detik = date('s', strtotime($paket_soal->durasi));

            $total_duration_in_seconds = ($durasi_jam * 3600) + ($durasi_menit * 60) + $durasi_detik;

            $waktu_selesai = $waktu_mulai + $total_duration_in_seconds;

            if (!is_null($peserta->sisa_waktu)) {
                // Pakai sisa_waktu yang sudah tersimpan di DB
                $sisa_waktu = $peserta->sisa_waktu;
            } else {
                // Ujian baru, hitung dari awal
                $now = time();
                $sisa_waktu = max(0, $waktu_selesai - $now);
            }


            LogAktifitas::catat("Berhasil Memulai Ujian");

            return view(
                'exam.room.halaman-ujian',
                compact(
                    'ujian',
                    'peserta',
                    'waktu_mulai',
                    'waktu_selesai',
                    'durasi_jam',
                    'durasi_menit',
                    'durasi_detik',
                    'total_duration_in_seconds',
                    'participantUrl',
                    'participantName',
                    'totalSoal',
                    'totalJawaban',
                    'active_soalId',
                    'paket_has_soal',
                    'pagination_soal',
                    'sisa_waktu'
                )
            );
        } catch (\Exception $e) {
            // dd($e);
            LogAktifitas::catat("Gagal Memulai Ujian");
            return redirect()->back()->with('error', 'Ops, Ada Kesalahan ... Silahkan Coba Lagi!');
        }
    }


    public function fetch_data_ajax(Request $request, $id_soal)
    {


        $peserta = PesertaUjian::find($request->peserta_ujian_id);
        // if ($peserta->status_pengerjaan != 1) {
        //     return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
        // }
        
        $ujian = Ujian::where('id_ujian', $peserta->ujian_id)->first();
        if ($peserta->status_pengerjaan != 1) {
            if($ujian->id_jenis_ujian == 1)
            {

                return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
            }else{
                return redirect()->route('dashboard')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
            }
        }
        

        $active_soalId = $id_soal;



        $paket_has_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->where('soal_id', $id_soal)->with('soal')->first();

        // $pagination_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)
        //     ->select(['soal_id'])
        //     ->get();

        // $totalSoal =  $pagination_soal->count();
        // $soalIds = $pagination_soal->pluck('soal_id')->toArray();
        // $peserta_json = PesertaUjian::join('siakad.mhs_pt as b','b.id_mhs_pt','peserta_ujian.id_mhs_pt')
        //                             ->join('siakad.mahasiswa as c','c.id_mahasiswa','b.id_mahasiswa')
        //                             ->findorfail($peserta->id_peserta_ujian);

        // $decode = json_decode($peserta_json->json_soal_mhs);

        // $id_soals = [];
        // foreach ($decode as $item) {
        //     $id_soals[] = $item->id_soal;
        // }
        $sessionKey = "soal_peserta_{$peserta->id_peserta_ujian}";

        // Cek apakah sudah ada soal di session
        if (!session()->has($sessionKey)) {
            // Ambil dari database hanya sekali
            
            // $peserta_json = PesertaUjian::join('siakad.mhs_pt as b', 'b.id_mhs_pt', '=', 'peserta_ujian.id_mhs_pt')
            //     ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', '=', 'b.id_mahasiswa')
            //     ->where('peserta_ujian.id_peserta_ujian', $peserta->id_peserta_ujian)
            //     ->firstOrFail();

            if($ujian->id_jenis_ujian == 1)
            {
                $peserta_json = PesertaUjian::join('siakad.mhs_pt as b', 'b.id_mhs_pt', '=', 'peserta_ujian.id_mhs_pt')
                            ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', '=', 'b.id_mahasiswa')
                            ->where('peserta_ujian.id_peserta_ujian', $peserta->id_peserta_ujian)
                            ->firstOrFail();
            }else{
                $peserta_json = PesertaUjian::join('peserta_eksternal as b','b.id_peserta_eksternal','peserta_ujian.id_peserta_eksternal')
                                            ->where('peserta_ujian.id_peserta_ujian', $peserta->id_peserta_ujian)
                                            ->findorfail();
                    

            }

            $file = Storage::disk('public')->get($peserta_json->json_soal_mhs);
            $decoded = json_decode($file);
            // $decode = json_decode($peserta_json->json_soal_mhs, true);

            // Simpan ke session
            // KODE LAMA:
            // session()->put($sessionKey, $decode);
            session()->put($sessionKey, $decoded);
        }

        // Ambil soal dari session
        $decode = session($sessionKey);

        // Ambil id_soal dari session decode
        $id_soals = array_column($decode, 'id_soal');

        $pagination_soal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)
            ->whereIn('soal_id', $id_soals)
            ->orderByRaw("FIELD(soal_id, ".implode(',', $id_soals).")") // Mengurutkan sesuai urutan di $id_soals
            ->select(['soal_id'])
            ->get();

        $totalSoal =  $pagination_soal->count();
        $soalIds = $pagination_soal->pluck('soal_id')->toArray();

        $pagination_jawaban = DB::table('paket_has_soal as s')
            ->leftJoin('pilgan_jawab as pj', function ($join) use ($soalIds, $request) {
                $join->on('s.soal_id', '=', 'pj.soal_id')
                    ->whereIn('s.soal_id', $soalIds)
                    ->where('pj.peserta_ujian_id', $request->peserta_ujian_id);
            })
            ->leftJoin('essay_jawab as ej', function ($join) use ($soalIds, $request) {
                $join->on('s.soal_id', '=', 'ej.soal_id')
                    ->whereIn('s.soal_id', $soalIds)
                    ->where('ej.peserta_ujian_id', $request->peserta_ujian_id);
            })
            ->where('s.paket_soal_id', $ujian->paket_soal_id)
            ->where(function ($query) {
                $query->whereNotNull('pj.soal_id')
                    ->orWhereNotNull('ej.soal_id');
            })
            ->select(
                's.soal_id',
                DB::raw('COALESCE(pj.jenis_jwb, ej.jenis_jwb) as jenis_jwb')
            )
            ->get();


        // Menghitung total jawaban keseluruhan
        $totalJawaban = $pagination_jawaban->count();


        $jawaban =  null;
        if ($paket_has_soal->soal->jenis_soal == 'pilgan') {
            $jawaban = PilganJawab::where('soal_id', $id_soal)->where('peserta_ujian_id', $request->peserta_ujian_id)->first();
        } elseif ($paket_has_soal->soal->jenis_soal == 'essay') {
            $jawaban = EssayJawab::where('soal_id', $id_soal)->where('peserta_ujian_id', $request->peserta_ujian_id)->first();
        }

        if ($request->ajax()) {
            return view('exam.room.pagination_data_ajax', compact('active_soalId', 'paket_has_soal', 'totalSoal', 'totalJawaban', 'jawaban', 'peserta', 'ujian', 'pagination_soal', 'pagination_jawaban'))->render();
        }
        
        


    }

    public function pilganJawab(PilganJawabRequest $request)
    {

        // KODE LAMA:
        // $peserta = PesertaUjian::find(decrypt($request->peserta_ujian_id));
        $peserta = PesertaUjian::with('ujian')->findOrFail(decrypt($request->peserta_ujian_id));
        if ($peserta->status_pengerjaan != 1) {
            return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
        }
        // try {

        if (!$peserta->ujian) {
            throw new \Exception("Data ujian peserta tidak ditemukan.");
        }

        $pesertaId = $peserta->id_peserta_ujian;
        $paketSoalId = $peserta->ujian->paket_soal_id;

        // KODE LAMA:
        // $score = PaketHasSoal::where('paket_soal_id', decrypt($request->paket_soal_id))
        //     ->where('soal_id', $request->id_soal)
        //     ->first();
        $score = PaketHasSoal::where('paket_soal_id', $paketSoalId)
            ->where('soal_id', $request->id_soal)
            ->first();
        if (!$score) {
            throw new \Exception("Data poin untuk soal tidak ditemukan.");
        }
        $jawaban = $request->jawaban_pilgan;
        $soal = Soal::where('id_soal', $score->soal_id)->first();
        if (!$soal) {
            throw new \Exception("Data soal tidak ditemukan.");
        }
        $poinDidapat = 0;
        $status = 'F';
        if ($jawaban == $soal->kunci) {
            $poinDidapat = $score->poin;
            $status = 'T';
        }


        # Create or update PilganJawab
        $posts = PilganJawab::updateOrCreate(
            [
                // KODE LAMA:
                // 'peserta_ujian_id' => decrypt($request->peserta_ujian_id),
                'peserta_ujian_id' => $pesertaId,
                'soal_id' => $request->id_soal,
            ],
            [
                'pilgan_id' => decrypt($request->pilgan_id),
                'jawab' => $jawaban,
                'score' => $poinDidapat,
                'status' => $status,
                'jenis_jwb' => 1
            ]
        );

        # Log activity
        LogAktifitas::catat($posts->wasRecentlyCreated ? "Soal Pilihan Ganda Dijawab" : "Jawaban Soal Pilihan Ganda Diubah");

        // KODE LAMA:
        // $paket_has_soal = PaketHasSoal::where('paket_soal_id', decrypt($request->paket_soal_id))
        //     ->select(['soal_id'])
        //     ->get();
        $paket_has_soal = PaketHasSoal::where('paket_soal_id', $paketSoalId)
            ->select(['soal_id'])
            ->get();

        $totalSoal =  $paket_has_soal->count();
        $soalIds = $paket_has_soal->pluck('soal_id')->toArray();

        $totalJawaban = DB::table('paket_has_soal as s')
            ->leftJoin('pilgan_jawab as pj', function ($join) use ($soalIds, $pesertaId) {
                $join->on('s.soal_id', '=', 'pj.soal_id')
                    ->whereIn('s.soal_id', $soalIds)
                    // KODE LAMA:
                    // ->where('pj.peserta_ujian_id', decrypt($request->peserta_ujian_id));
                    ->where('pj.peserta_ujian_id', $pesertaId);
            })
            ->leftJoin('essay_jawab as ej', function ($join) use ($soalIds, $pesertaId) {
                $join->on('s.soal_id', '=', 'ej.soal_id')
                    ->whereIn('s.soal_id', $soalIds)
                    // KODE LAMA:
                    // ->where('ej.peserta_ujian_id', decrypt($request->peserta_ujian_id));
                    ->where('ej.peserta_ujian_id', $pesertaId);
            })
            // KODE LAMA:
            // ->where('s.paket_soal_id', decrypt($request->paket_soal_id))
            ->where('s.paket_soal_id', $paketSoalId)
            ->where(function ($query) {
                $query->whereNotNull('pj.soal_id')
                    ->orWhereNotNull('ej.soal_id');
            })
            ->select('s.soal_id')
            ->count();



        # Mengembalikan response JSON
        return response()->json([
            'success' => true,
            'message' => $posts->wasRecentlyCreated
                ? "Jawaban berhasil disimpan."
                : "Jawaban berhasil diperbarui.",
            'totalSoal' => $totalSoal,
            'totalJawaban' => $totalJawaban,
        ]);
        // } catch (\Exception $e) {
        //     # Mengembalikan response error
        //     return response()->json([
        //         'success' => false,
        //         'message' => "Terjadi kesalahan: " . $e->getMessage(),
        //     ], 500);
        // }
    }

    public function ragu(Request $request)
    {
        // return decrypt($request->peserta_ujian_id) . '  ' . $request->id_soal;
        $posts = PilganJawab::updateOrCreate(
            [
                'peserta_ujian_id' => decrypt($request->peserta_ujian_id),
                'soal_id' => $request->id_soal,
            ],
            [
                'jenis_jwb' => $request->value_jwb_ragu
            ]
        );


        return response()->json([
            'success' => true,
            'message' => "Berhasil Ditandai Ragu",
        ]);
    }

    public function essayJawab(EssayJawabRequest $request)
    {

        $peserta = PesertaUjian::find(decrypt($request->peserta_ujian_id));
        if ($peserta->status_pengerjaan != 1) {
            return redirect()->to('https://siakad-blok.unja.ac.id/home')->with('error', 'Ups Silahkan Hubungi Admin Untuk Membuka Akses Anda!');
        }
        try {
            # Create or update EssayJawab
            $posts = EssayJawab::updateOrCreate(
                [
                    'peserta_ujian_id' => decrypt($request->peserta_ujian_id),
                    'soal_id' => decrypt($request->soal_id),
                ],
                [
                    'essay_id' => decrypt($request->essay_id),
                    'jawab' => $request->jawaban_essay,
                    'jenis_jwb' => 1
                ]
            );

            # Log aktivitas berdasarkan aksi yang dilakukan
            LogAktifitas::catat($posts->wasRecentlyCreated ? "Soal Essay Dijawab" : "Jawaban Soal Essay Diubah");
            $paket_has_soal = PaketHasSoal::where('paket_soal_id', decrypt($request->paket_soal_id))
                ->select(['soal_id'])
                ->get();
            $totalSoal =  $paket_has_soal->count();
            $soalIds = $paket_has_soal->pluck('soal_id')->toArray();
            $totalJawaban = DB::table('paket_has_soal as s')
                ->leftJoin('pilgan_jawab as pj', function ($join) use ($soalIds, $request) {
                    $join->on('s.soal_id', '=', 'pj.soal_id')
                        ->whereIn('s.soal_id', $soalIds)
                        ->where('pj.peserta_ujian_id', decrypt($request->peserta_ujian_id));
                })
                ->leftJoin('essay_jawab as ej', function ($join) use ($soalIds, $request) {
                    $join->on('s.soal_id', '=', 'ej.soal_id')
                        ->whereIn('s.soal_id', $soalIds)
                        ->where('ej.peserta_ujian_id', decrypt($request->peserta_ujian_id));
                })
                ->where('s.paket_soal_id', decrypt($request->paket_soal_id))
                ->where(function ($query) {
                    $query->whereNotNull('pj.soal_id')
                        ->orWhereNotNull('ej.soal_id');
                })
                ->select('s.soal_id')
                ->count();

            return response()->json([
                'success' => true,
                'message' => $posts->wasRecentlyCreated
                    ? "Jawaban berhasil disimpan."
                    : "Jawaban berhasil diperbarui.",
                'totalSoal' => $totalSoal,
                'totalJawaban' => $totalJawaban,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }

    public function raguEssay(Request $request)
    {
        // return decrypt($request->peserta_ujian_id) . '  ' . $request->id_soal;
        $posts = EssayJawab::updateOrCreate(
            [
                'peserta_ujian_id' => decrypt($request->peserta_ujian_id),
                'soal_id' => $request->id_soal,
            ],
            [
                'jenis_jwb' => $request->value_jwb_ragu
            ]
        );


        return response()->json([
            'success' => true,
            'message' => "Berhasil Ditandai Ragu",
        ]);
    }

    public function akhiriUjian($id_ujian, $id_peserta_ujian)
    {
        $ujianId = decrypt($id_ujian);
        $pesertaId = decrypt($id_peserta_ujian);
        $ujian = Ujian::findorfail($ujianId);

        $pilganJawab = PilganJawab::where('peserta_ujian_id', $pesertaId)->sum('score');
        $essayJawab = EssayJawab::where('peserta_ujian_id', $pesertaId)->sum('score');
        $total_nilai = $pilganJawab + $essayJawab;
        $update_finish_peserta = [
            'waktu_berhenti' => date('Y-m-d H:i:s'),
            'status_pengerjaan' => 2,
            'nilai' => $total_nilai,
        ];
        PesertaUjian::where('ujian_id', $ujianId)->where('id_peserta_ujian', $pesertaId)->update($update_finish_peserta);
        //CEK APAKAH WAJIB ISI KUESIONER?
        $data = PesertaUjian::where('ujian_id', $ujianId)->where('id_peserta_ujian', $pesertaId)->first();


        if (auth()->user()->usertype == 'mahasiswa') {
            LogAktifitas::catat("Ujian Diakhiri Peserta");
            if ($data->need_kuesioner == 1) {
                return response()->json([
                    'message' => 'Silahkan Isi Kuesioner Terlebih Dahulu.',
                    'redirect' => url('https://cbt-fkik.unja.ac.id/kuesioner/' . $pesertaId . '/participant/' . $ujianId . '/kuesionerku/' . $ujian->kuesioner_id)
                ]);
            } else {
                if($ujian->id_jenis_ujian == 1)
                {
                    return response()->json([
                        'message' => 'Anda Telah Menyelesaikan Ujian.',
                        'redirect' => url('https://siakad-blok.unja.ac.id/riwayatujianmhs')
                    ]);
                    // return redirect()->route('dashboard');
                }else{
                    return response()->json([
                        'message' => 'Anda Telah Menyelesaikan Ujian.',
                        'redirect' => route('dashboard')
                    ]);
                    // return redirect()->route('dashboard');
                }
                
            }
        } else {
            LogAktifitas::catat("Ujian Diakhiri Admin/Pengawas");
            return redirect()->back()->with('success', 'Ujian Peserta Berhasil Diakhiri');
        }
    }

    public function akhiriUjianTimeOut($id_ujian, $id_peserta_ujian)
    {

        $ujianId = decrypt($id_ujian);
        $pesertaId = decrypt($id_peserta_ujian);
        $ujian = Ujian::findorfail($ujianId);
        $pilganJawab = PilganJawab::where('peserta_ujian_id', $pesertaId)->sum('score');
        $essayJawab = EssayJawab::where('peserta_ujian_id', $pesertaId)->sum('score');
        $total_nilai = $pilganJawab + $essayJawab;
        $update_finish_peserta = [
            'waktu_berhenti' => date('Y-m-d H:i:s'),
            'status_pengerjaan' => 2,
            'nilai' => $total_nilai,
        ];
        PesertaUjian::where('ujian_id', $ujianId)->where('id_peserta_ujian', $pesertaId)->update($update_finish_peserta);
        LogAktifitas::catat("Ujian Berakhir Karena Waktu Habis");

        //CEK APAKAH WAJIB ISI KUESIONER?
        $data = PesertaUjian::where('ujian_id', $ujianId)->where('id_peserta_ujian', $pesertaId)->first();


        if ($data->need_kuesioner == 1) {
            return redirect()->to('https://cbt-fkik.unja.ac.id/kuesioner/' . $pesertaId . '/participant/' . $ujianId . '/kuesionerku/' . $ujian->kuesioner_id);
        } else {
            if($ujian->id_jenis_ujian == 1)
            {
                return redirect()->route('dashboard');
            }else{
                return redirect()->route('dashboard');
            }
            // return redirect()->route('dashboard');
        }
    }
    public function akhiriUjianExitFullScreen($id_ujian, $id_peserta_ujian)
    {


        $ujianId = decrypt($id_ujian);
        $pesertaId = decrypt($id_peserta_ujian);
        $ujian = Ujian::where('id_ujian',$ujianId)->first();
        $pilganJawab = PilganJawab::where('peserta_ujian_id', $pesertaId)->sum('score');
        $essayJawab = EssayJawab::where('peserta_ujian_id', $pesertaId)->sum('score');
        $total_nilai = $pilganJawab + $essayJawab;
        $update_finish_peserta = [
            'status_pengerjaan' => 3,
            'nilai' => $total_nilai,
        ];
        PesertaUjian::where('ujian_id', $ujianId)->where('id_peserta_ujian', $pesertaId)->update($update_finish_peserta);
        LogAktifitas::catat("Ujian Dihentikan Karena Keluar Dari Mode Fullscreen");

        if($ujian->id_jenis_ujian == 1)
        {
            return redirect()->route('dashboard');
        }else{
            return redirect()->route('dashboard');
        }
    }

    public function runPing(Request $request)
    {
        $id_peserta = $request->input('peserta_id');
        $pesertaId = decrypt($id_peserta);
        $host = 'https://cbt-fkik.unja.ac.id/';


        if (stripos(PHP_OS, 'WIN') === 0) {
            // Untuk Windows
            $pingResult = exec("/usr/bin/ping -n 1 $host", $output, $status);
            preg_match('/time[=<]([0-9\.]+)ms/', $pingResult, $matches);
        } else {
            // Untuk Linux/MacOS
            $pingResult = exec("/usr/bin/ping -c 1 $host", $output, $status);
            preg_match('/time=([0-9\.]+) ms/', $pingResult, $matches);
        }

        // Ekstrak latency dari hasil ping
        $latency = isset($matches[1]) ? floatval($matches[1]) : null;



        return response()->json(['latency' => $latency]);
    }

    public function storeSisaWaktu(Request $req)
    {
        $peserta = PesertaUjian::where('id_peserta_ujian', decrypt($req->id_peserta_ujian))->first();
        $peserta->sisa_waktu = $req->sisa_waktu;
        $peserta->save();

        return response()->json([
            'status' => 200
        ]);
    }
}
