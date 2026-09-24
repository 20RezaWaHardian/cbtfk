<?php

namespace App\Http\Controllers\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\EssayJawab;
use App\Models\PilganJawab;
use App\Models\PaketHasSoal;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use App\Models\SBKelompokBelajar;
use App\Models\SBMahasiswaRombel;
use App\Models\PesertaUjianEksternal;
use App\Imports\ImportPesertaPmbUjian;
use App\Exports\TemplateImportPesertaPmbExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\DataTables\PesertaUjianDataTable;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class PesertaUjianController extends Controller
{
    public function index($id_ujian, $id_kelompok_belajar, PesertaUjianDataTable $dataTable)
    {
        if (Gate::allows('read ujian/daftar-ujian')) {
            $ujian = Ujian::where('id_ujian', $id_ujian)->first();
            $daftar_mahasiswa = [];
            // ambil id kelas induk dan semua anak

            if($ujian->id_jenis_ujian == 1)
            {
                $daftar_mahasiswa = DB::table("sistembl_siakad-uin .peserta_blok as a")
                                ->leftJoin("sistembl_siakad-uin .mahasiswa as b", 'a.mahasiswa_id', '=', 'b.id_mahasiswa')
                                ->leftJoin('peserta_ujian as f', function ($q) use ($ujian) {
                                    $q->on('f.id_mhs_pt', '=', 'b.id_mahasiswa')
                                        ->where('f.ujian_id', '=', $ujian->id_ujian);
                                })
                                ->where('a.blok_id', $ujian->blok_id)
                                ->whereNull('f.id_mhs_pt')
                                ->select(
                                    'b.id_mahasiswa as id_mhs_pt',
                                    'b.nama as nama_mahasiswa',
                                    'b.nim as no_mhs'
                                )
                                ->get();
            }else if($ujian->id_jenis_ujian == 2){
                $daftar_mahasiswa = collect();
            }

            $ujian = Ujian::where('id_ujian', $id_ujian)->first();

            return $dataTable->with(['id_ujian' => $ujian->id_ujian])->render('ujian.peserta.index',
                 compact('daftar_mahasiswa',
                //   'peserta_ujian', 
                  'ujian'));
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    public function store(Request $request)
    {
        // dd($request);
        $this->authorize('create ujian/daftar-ujian');

        $ujian = Ujian::where('id_ujian', $request->ujian_id)->first();
        if ($ujian) {

            if ($ujian->is_kuesioner == 1) {
                $wajib = 1; //ya
                $kuesioner_id = $ujian->kuesioner_id;
            } else {
                $wajib = 0; //tidak
                $kuesioner_id = null;
            }
            DB::beginTransaction();
            try {
                foreach ($request->selected_mahasiswa as $mahasiswa_rombel_id) {
                    if($ujian->id_jenis_ujian == 1)
                    {
                        PesertaUjian::create([
                            // 'mahasiswa_rombel_id' => $mahasiswa_rombel_id,
                            'id_mhs_pt' => $mahasiswa_rombel_id,
                            'ujian_id' => $request->ujian_id,
                            'need_kuesioner' => $wajib,
                            'kuesioner_id' => $kuesioner_id,
                        ]);
                    }elseif($ujian->id_jenis_ujian == 2)
                    {
                        $pesertaEksternal = PesertaUjianEksternal::where('id_peserta_eksternal', $mahasiswa_rombel_id)
                            ->orWhere('id_pmb', $mahasiswa_rombel_id)
                            ->first();

                        if (!$pesertaEksternal) {
                            throw new \Exception(
                                "Peserta PMB lokal dengan ID {$mahasiswa_rombel_id} tidak terdaftar"
                            );
                        }

                        PesertaUjian::updateOrCreate(
                            [
                                'id_peserta_eksternal' => $pesertaEksternal->id_peserta_eksternal,
                                'ujian_id'             => $ujian->id_ujian,
                            ],
                            [
                                'need_kuesioner' => $wajib,
                                'kuesioner_id'   => $kuesioner_id,
                            ]
                        );
                    }
                }

                LogAktifitas::catat(
                    "Memvalidasi Peserta Ujian {$ujian->nama_ujian} (ID {$ujian->id_ujian})"
                );

                DB::commit();

                return redirect()
                    ->route('ujian.peserta.index', ['id_ujian'=>$ujian->id_ujian,'id_kelompok_belajar'=>0])
                    // ->route('ujian.daftar-ujian.index')
                    ->with('success', 'Berhasil memvalidasi peserta ujian');
                    
            } catch (\Exception $e) {
                DB::rollBack();

                return redirect()
                    ->route('ujian.peserta.index', ['id_ujian'=>$ujian->id_ujian,'id_kelompok_belajar'=>0])
                    // ->route('ujian.daftar-ujian.index')
                    ->with('error', $e->getMessage());
            }
            
        } else {
            return redirect()
                    // ->back()
                    ->route('ujian.daftar_ujian.index')
                    ->with('error', 'Opps Ujian Tidak Ditemukan');
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return response()->json(['error' => 'No items selected'], 400);
        }
        try {
            $data = PesertaUjian::whereIn('id_peserta_ujian', $id)->first();
            if(!$data->id_peserta_eksternal)
            {
                PesertaUjian::whereIn('id_peserta_ujian', $id)->delete();
            }else{
                PesertaUjianEksternal::where('id_peserta_eksternal',$data->id_peserta_eksternal)->delete();
                PesertaUjian::whereIn('id_peserta_ujian', $id)->delete();
            }
            // if($data)
            return response()->json(['success' => 'Items deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete items'], 500);
        }
    }

    public function downloadTemplateImportPmb()
    {
        return Excel::download(new TemplateImportPesertaPmbExport, 'template_import_peserta_pmb.xlsx');
    }

    public function importPesertaPmb(Request $request, $id_ujian)
    {
        $this->authorize('create ujian/daftar-ujian');

        $request->validate([
            'file_import_pmb' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $ujian = Ujian::where('id_ujian', $id_ujian)->firstOrFail();

        if ($ujian->id_jenis_ujian != 2) {
            return redirect()->back()->with('error', 'Import peserta PMB hanya untuk jenis ujian PMB.');
        }

        DB::beginTransaction();
        try {
            $import = new ImportPesertaPmbUjian($ujian);
            Excel::import($import, $request->file('file_import_pmb'));

            LogAktifitas::catat("Import peserta PMB ujian {$ujian->nama_ujian} (ID {$ujian->id_ujian})");
            DB::commit();

            return redirect()
                ->route('ujian.peserta.index', ['id_ujian' => $ujian->id_ujian, 'id_kelompok_belajar' => 0])
                ->with('success', "Import peserta PMB berhasil. Baru: {$import->getImported()}, sudah ada/update: {$import->getSkipped()}.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('ujian.peserta.index', ['id_ujian' => $ujian->id_ujian, 'id_kelompok_belajar' => 0])
                ->with('error', $e->getMessage());
        }
    }


    //Koreksi Ujian
    public function koreksi($id_peserta_ujian)
    {
        $peserta_ujian = PesertaUjian::find($id_peserta_ujian);
        $paket_has_soal = PaketHasSoal::where('paket_soal_id', $peserta_ujian->ujian->paket_soal_id)->get();

        $total_poin = 0;
        // Jika ada soal yang ditemukan
        if ($paket_has_soal->isNotEmpty()) {
            $total_poin = $paket_has_soal->sum('poin');
        }



        $essay_jawab = EssayJawab::where('peserta_ujian_id', $peserta_ujian->id_peserta_ujian)->where('score', '!=', null)->get();
        $pilgan_jawab = PilganJawab::where('peserta_ujian_id', $peserta_ujian->id_peserta_ujian)->get();

        //soal yang belum di koreksi
        $koreksi_jawaban = EssayJawab::where('peserta_ujian_id', $peserta_ujian->id_peserta_ujian)->whereNull('score')->get();

        $score_pilgan = PilganJawab::where('peserta_ujian_id', $peserta_ujian->id_peserta_ujian)->sum('score');


        if ($koreksi_jawaban->count() == 0) {
            $score_essay = EssayJawab::where('peserta_ujian_id', $peserta_ujian->id_peserta_ujian)->sum('score');
            $total_score = $score_essay + $score_pilgan;
            $nilai_akhir = $total_score / $total_poin * 100;

            PesertaUjian::where('id_peserta_ujian', $peserta_ujian->id_peserta_ujian)->update([
                'nilai' => $total_score,
                'nilai_akhir' => $nilai_akhir
            ]);



            return view('ujian.peserta.koreksi', compact(['peserta_ujian', 'essay_jawab', 'pilgan_jawab', 'koreksi_jawaban', 'total_poin']));
        }

        return view('ujian.peserta.koreksi', compact(['peserta_ujian', 'essay_jawab', 'pilgan_jawab', 'koreksi_jawaban', 'total_poin']));
    }

    public function updateScoreEssay(Request $request)
    {
        $essay_jawab = EssayJawab::findOrFail($request->id);
        $update_essay_jawab = [
            'score' => $request->score
        ];
        EssayJawab::where('id_essay_jawab', $request->id)->update($update_essay_jawab);
        return redirect()->back();
    }

    public function syncNilai($id_ujian)
    {
        $idUjian = decrypt($id_ujian);
        $ujian = Ujian::where('id_ujian', $idUjian)->first();
        $paket = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->select('soal_id', 'poin')->get();
        foreach ($paket  as $pk) {
            $cekSoalKunci = Soal::where('id_soal', $pk->soal_id)->first();

            $cekPilganMhs = PilganJawab::where('soal_id', $pk->soal_id)
                ->whereNull('score')
                // ->where('peserta_ujian_id', 4828)
                ->get();
            foreach ($cekPilganMhs as $cmh) {
                if ($cmh->jawab == $cekSoalKunci->kunci) {
                    $cmh->update([
                        'score' => $pk->poin,
                        'status' => 'T'
                    ]);
                } else {
                    $cmh->update([
                        'score' => 0,
                        'status' => 'F'
                    ]);
                }
            }
        }
        $ujian->update([
            'sync' => 1
        ]);
        LogAktifitas::catat("Menyingkron nilai mahasiswa pada ujian" . $ujian->nama_ujian);
        return response()->json([
            'pesan' => "Berhasil Syncron Nilai Mahasiswa"
        ]);
    }
}
