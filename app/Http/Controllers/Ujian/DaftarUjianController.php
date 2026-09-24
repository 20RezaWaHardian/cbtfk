<?php

namespace App\Http\Controllers\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\PaketSoal;
use App\Models\PilganJawab;
use App\Models\SiakadProdi;
use Illuminate\Support\Str;
use App\Models\PaketHasSoal;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use App\Models\SiakadSemester;
use App\Models\SBKelompokBelajar;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UjianRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use App\DataTables\DaftarUjianDataTable;
use App\DataTables\PesertaUjianDataTable;
use App\Models\Kuesioner;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DaftarUjianController extends Controller
{

    public function index(DaftarUjianDataTable $dataTable)
    {

        if (Gate::allows('read ujian/daftar-ujian')) {
            return $dataTable->render('ujian.daftar-ujian.index');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    public function create()
    {
        $this->authorize('create ujian/daftar-ujian');
        // $prodi = SiakadProdi::KhususCBT()
        //     ->select('id_prodi', 'nama_prodi', 'id_fakultas')
        //     ->with(['fakultas' => function ($query) {
        //         $query->select('id_fakultas', 'nama_fakultas');
        //     }])->get();

        $semester = SiakadSemester::where('is_aktif', 1)
            ->first();
        $kuesioner = Kuesioner::where('status', 1)->where('terap_kue',1)->get();


        $jenis_ujian = DB::table('jenis_ujian')
                            ->where('status',1)
                            ->get();

        $kategori_ujian = DB::table('kategori_ujian')
                            ->get();
        
        $blok = DB::table('sistembl_siakad-uin.blok')
            ->where('semester_id', $semester->id_semester)
            ->select('id', 'nama', 'kode')
            ->get();
            
        return view('ujian.daftar-ujian.daftar-ujian-form', [
            'ujian' => new Ujian(),
            // 'prodi' => $prodi,
            'semester' => $semester,
            'kuesioner' => $kuesioner,
            'jenis_ujian' => $jenis_ujian,
            'kategori_ujian' => $kategori_ujian,
            'blok' => $blok,
        ]);
    }
    public function store(UjianRequest $request)
    {
        // dd($request);
        $this->authorize('create ujian/daftar-ujian');
        $semester = SiakadSemester::semesterAktif()->select('id_semester')
            ->first();
        if (!$semester) {
            return redirect()->back()->with('error', 'Data Semester Aktif Tidak Ditemukan!');
        }


        $data = $request->except(['files', 'pengawas_ujian', 'kelompok_belajar_id']);
        $data['pembuat_ujian_id'] = auth()->user()->id;
        $data['semester_id'] = $semester->id_semester;

        // dd($data);

        $ujian = Ujian::create($data);
        if ($ujian && $request->has('pengawas_ujian')) {
            $ujian->pengawas()->sync($request->pengawas_ujian);
        }
        LogAktifitas::catat("Membuat Ujian Dengan Nama " . $ujian->nama_ujian . " (id)" . $ujian->id_ujian . ".");



        // // Contain the logic to create a new meeting
        $meteredDomain = env('METERED_DOMAIN');
        $meteredSecretKey = env('METERED_SECRET_KEY');

        $randomText = Str::random(7);
        $roomName = "exam_{$ujian->id_ujian}_{$randomText}";

        $response = Http::post("https://{$meteredDomain}/api/v1/room?secretKey={$meteredSecretKey}", [
            'roomName' => $roomName,
            'autoJoin' => true,
            // 'expireUnixSec' => $ujian->selesai_ujian,
            // 'notBeforeUnixSec' => $ujian->tanggal_ujian,
        ]);

        $ujian->id_room =  $response->json("_id");
        $ujian->room_name =  $response->json("roomName");
        $ujian->save();
        return redirect()->route('ujian.daftar-ujian.index')->with('success', 'Berhasil Membuat Ujian');
    }

    public function edit($id)
    {
        $this->authorize('update ujian/daftar-ujian');
        $ujian = Ujian::findorfail($id);
        // $prodi = SiakadProdi::KhususCBT()
        //     ->select('id_prodi', 'nama_prodi', 'id_fakultas')
        //     ->with(['fakultas' => function ($query) {
        //         $query->select('id_fakultas', 'nama_fakultas');
        //     }])->get();

        $semester = SiakadSemester::where('is_aktif', 1)
            ->first();
        $kuesioner = Kuesioner::where('status', 1)->where('terap_kue',1)->get();

        $paket_soal = PaketSoal::get();
        $jenis_ujian = DB::table('jenis_ujian')
                            ->where('status',1)
                            ->get();
        $kategori_ujian = DB::table('kategori_ujian')
                            ->get();
        $blok = DB::table('sistembl_siakad-uin.blok')
            ->where('semester_id', $semester->id_semester)
            ->select('id', 'nama', 'kode')
            ->get();
        return view('ujian.daftar-ujian.daftar-ujian-form', 
            compact(
                'jenis_ujian',
                'ujian',
                'paket_soal', 
                'semester', 
                'kuesioner',
                'kategori_ujian',
                'blok'
            ));
    }

    public function update($id, UjianRequest $request)
    {
        $this->authorize('update ujian/daftar-ujian');

        try {
            $data = [
                'id_jenis_ujian' => $request->input('id_jenis_ujian'),
                'nama_ujian' => $request->input('nama_ujian'),
                'prodi_id' => $request->input('prodi_id'),
                'blok_id' => $request->input('blok_id'),
                'kelompok_belajar_id' => $request->input('kelompok_belajar_id'),
                'kategori_ujian_id' => $request->input('kategori_ujian_id'),
                'paket_soal_id' => $request->input('paket_soal_id'),
                'tanggal_ujian' => $request->input('tanggal_ujian'),
                'selesai_ujian' => $request->input('selesai_ujian'),
                'ketentuan_ujian' => $request->input('ketentuan_ujian'),
                'tampil_nilai' => $request->input('tampil_nilai'),
                'status' => $request->input('status'),
                'is_kuesioner' => $request->input('is_kuesioner'),
                'kuesioner_id' => $request->input('kuesioner_id'),
            ];

            $ujian = Ujian::findOrFail($id);
            $data['id_room'] = $ujian->id_room;
            $data['room_name'] = $ujian->room_name;
            $data['semester_id'] = $ujian->semester_id;
            $data['pembuat_ujian_id'] = $ujian->pembuat_ujian_id;
            if ($ujian) {

                $ujian->update($data);
                if ($ujian && $request->has('pengawas_ujian')) {
                    $ujian->pengawas()->sync($request->pengawas_ujian);
                }
                LogAktifitas::catat("Memperbaruhi Ujian Dengan Nama " . $ujian->nama_ujian . " (id)" . $ujian->id_ujian . ".");
            } else {
                return redirect()->back()->with('error', 'Id Ujian tidak ditemukan.');
            }

            return redirect()->route('ujian.daftar-ujian.index')
                ->with('success', 'Ujian berhasil diperbarui.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'ID Ujian Tidak Ditemukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui ujian: ' . $e->getMessage());
        }
    }



    public function destroy($id)
    {

        $this->authorize('delete ujian/daftar-ujian');
        $ujian = Ujian::findOrFail($id);

        $meteredDomain = env('METERED_DOMAIN');
        $meteredSecretKey = env('METERED_SECRET_KEY');

        $roomName = $ujian->room_name;
        if ($roomName) {
            Http::delete("https://{$meteredDomain}/api/v1/room/{$roomName}?secretKey={$meteredSecretKey}");
        }


        try {
            DB::beginTransaction();
            PesertaUjian::where('ujian_id', $ujian->id)->delete();
            $ujian->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Ujian Berhasil Dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus ujian');
        }
    }

    public function updateStatusUjian(Request $request)
    {
        $id_update1 = $request->input('id_update1');
        $id_update2 = $request->input('id_update2');

        Ujian::whereIn('id_ujian', $id_update1)->where('status', 0)->update(['status' => 1]);

        $ujianList = Ujian::whereIn('id_ujian', $id_update2)->whereIn('status', [1, 0])->get();
        $meteredDomain = env('METERED_DOMAIN');
        $meteredSecretKey = env('METERED_SECRET_KEY');
        foreach ($ujianList as $ujian) {
            $roomName = $ujian->room_name;

            if ($roomName) {
                Http::delete("https://{$meteredDomain}/api/v1/room/{$roomName}?secretKey={$meteredSecretKey}");
            }
            $ujian->update(['status' => 2]);
        }

        return response()->json(['message' => 'Status ujian berhasil diperbarui.']);
    }

    //mulai
    public function mulaiUjian($id)
    {

        $ujian = Ujian::find(decrypt($id));
        $cek_peserta = count($ujian->peserta_ujian);
        if ($cek_peserta > 0) {
            $ujian->status =  1;
            $ujian->save();
            return redirect()->back()->with('success', 'Ujian Berhasil dimulai');
        } else {
            return redirect()->back()->with('error', 'Ujian Tidak Dapat Dimulai, Silahkan tambahkan minimal 1 peserta ujian.');
        }
    }
    //akhiri
    public function akhiriUjian($id)
    {


        $ujian = Ujian::where('id_ujian', decrypt($id))->first();
        $meteredDomain = env('METERED_DOMAIN');
        $meteredSecretKey = env('METERED_SECRET_KEY');

        $roomName = $ujian->room_name;
        if ($roomName) {
            Http::delete("https://{$meteredDomain}/api/v1/room/{$roomName}?secretKey={$meteredSecretKey}");
        }
        $ujian->update(['status' => 2]);

        $pesertaUjian = PesertaUjian::where('ujian_id', decrypt($id))
            ->whereNotNull('mulai_ujian')
            ->where('status_pengerjaan', '!=', 2)
            ->update([
                'status_pengerjaan' => 2
            ]);


        return redirect()->back()->with('success', 'Ujian Berhasil diakhiri');
    }

    public function analisisSoal($id)
    {
        if (Gate::allows('read bank-soal/soal')) {
            // $ujian = Ujian::FindOrFail(decrypt($id));
            // $jumlahSoal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->count();
            // $kunciJawaban = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)
            //     ->with('soal')
            //     ->get()
            //     ->pluck('soal.kunci');

            // $pesertaUjian = PesertaUjian::where('ujian_id', $ujian->id_ujian)
            //     ->select(
            //         'id_peserta_ujian',
            //         'ip_address',
            //         'koreksi',
            //         'mulai_ujian',
            //         'waktu_berhenti',
            //         'sisa_waktu',
            //         'id_mhs_pt',
            //         'mahasiswa_rombel_id',
            //         'ujian_id',
            //         'nilai',
            //         'nilai_akhir',
            //         'status_pengerjaan',
            //         'face_register',
            //         'created_at',
            //         'updated_at',
            //         'kehadiran',
            //         'kesamaan_foto',
            //         'alat_bantu',
            //         'menyontek',
            //         'berbicara',
            //         'latency',
            //         'latency_update',
            //         'need_kuesioner',
            //         'isi_kuesioner',
            //         'kuesioner_id'

            //     )
            //     ->get();
            // $jawabanPeserta = PilganJawab::whereIn('peserta_ujian_id', $pesertaUjian->pluck('id_peserta_ujian'))
            //     ->with('soal')
            //     ->get();


            // // Calculate peserta scores and ranks
            // $pesertaSkor = [];
            // foreach ($pesertaUjian as $peserta) {
            //     $skor = 0;
            //     foreach ($jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian) as $jawaban) {
            //         $soal = $jawaban->soal;
            //         if ($jawaban->jawab == $soal->kunci) {
            //             $skor++;
            //         }
            //     }
            //     $pesertaSkor[$peserta->id_peserta_ujian] = $skor;
            // }

            // // Sort peserta by score
            // arsort($pesertaSkor);

            // // Assign ranks
            // $rankedPeserta = [];
            // foreach (array_keys($pesertaSkor) as $index => $pesertaId) {
            //     $rankedPeserta[$pesertaId] = [
            //         'rank' => $index + 1,
            //         'skor' => $pesertaSkor[$pesertaId]
            //     ];
            // }

            // // Calculate midpoint
            // $totalPeserta = count($pesertaUjian);
            // $midpoint = (int) floor($totalPeserta / 2); // Calculate midpoint

            // // Split into rank atas and rank bawah
            // $pesertaRankAtas = $pesertaUjian->sortBy(function ($peserta) use ($rankedPeserta) {
            //     return $rankedPeserta[$peserta->id_peserta_ujian]['rank'];
            // })->take($midpoint);
            // $jumlahPesertaRankAtas = count($pesertaRankAtas);

            // $pesertaRankBawah = $pesertaUjian->sortBy(function ($peserta) use ($rankedPeserta) {
            //     return $rankedPeserta[$peserta->id_peserta_ujian]['rank'];
            // })->skip($midpoint);
            // $jumlahPesertaRankBawah = count($pesertaRankBawah);

            // $correctAnswersPerQuestionAtas = [];
            // $correctAnswersPerQuestionBawah = [];

            // // Create an array where the keys are soal_id values
            // foreach ($pesertaRankAtas as $peserta) {
            //     // Track the answers for each soal_id across participants
            //     foreach ($kunciJawaban as $index => $kunci) {
            //         $soalId = $index + 1; // Assuming soal_id starts from 1

            //         // Initialize the array if the soal_id is not yet in the array
            //         if (!isset($correctAnswersPerQuestionAtas[$soalId])) {
            //             $correctAnswersPerQuestionAtas[$soalId] = 0;
            //         }

            //         // Find the participant's answer for this soal_id (if any)
            //         $jawabanPesertaForSoal = $jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian)->where('soal_id', $soalId)->first();

            //         // If the participant has answered the question and the answer is correct, increment the correct answer count
            //         if ($jawabanPesertaForSoal && $jawabanPesertaForSoal->jawab == $jawabanPesertaForSoal->soal->kunci) {
            //             $correctAnswersPerQuestionAtas[$soalId]++;
            //         }
            //     }
            // }


            // foreach ($pesertaRankBawah as $peserta) {
            //     // Initialize the answers count for each question to 0
            //     foreach ($kunciJawaban as $index => $kunci) {
            //         $soalId = $index + 1;  // Assuming soal_id is 1-based

            //         // Initialize the count if not already set
            //         if (!isset($correctAnswersPerQuestionBawah[$soalId])) {
            //             $correctAnswersPerQuestionBawah[$soalId] = 0;
            //         }

            //         // Find the participant's answer for this question (if any)
            //         $jawabanPesertaForSoal = $jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian)->where('soal_id', $soalId)->first();

            //         // If the answer exists and it's correct, increment the correct count
            //         if ($jawabanPesertaForSoal && $jawabanPesertaForSoal->jawab == $jawabanPesertaForSoal->soal->kunci) {
            //             $correctAnswersPerQuestionBawah[$soalId]++;
            //         }
            //     }
            // }

            // Ambil data ujian berdasarkan id yang sudah didekripsi
            $ujian = Ujian::FindOrFail(decrypt($id));

            // Ambil jumlah soal dari paket soal
            $jumlahSoal = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->count();

            // Ambil kunci jawaban untuk setiap soal dengan join
            $kunciJawaban = PaketHasSoal::join('soal', 'paket_has_soal.soal_id', '=', 'soal.id_soal')
                ->where('paket_soal_id', $ujian->paket_soal_id)
                ->select('paket_has_soal.soal_id', 'soal.kunci')
                ->get()
                ->keyBy('soal_id');  // Key by soal_id

            // Ambil peserta ujian
            $pesertaUjian = PesertaUjian::where('ujian_id', $ujian->id_ujian)
                ->select('id_peserta_ujian', 'nilai', 'status_pengerjaan', 'kehadiran') // Pilih kolom yang dibutuhkan
                ->get();

            // Ambil jawaban peserta ujian dan soal yang terlibat
            $jawabanPeserta = PilganJawab::with('soal:id_soal,kunci') // Eager load soal
                ->whereIn('peserta_ujian_id', $pesertaUjian->pluck('id_peserta_ujian'))
                ->select('peserta_ujian_id', 'soal_id', 'jawab')
                ->get();

            // Hitung skor untuk setiap peserta dengan query langsung di database
            $jawabanPesertaSkor = PilganJawab::whereIn('peserta_ujian_id', $pesertaUjian->pluck('id_peserta_ujian'))
                ->join('soal', 'pilgan_jawab.soal_id', '=', 'soal.id_soal')
                ->whereColumn('pilgan_jawab.jawab', 'soal.kunci')  // Cocokkan jawaban dengan kunci soal
                ->groupBy('peserta_ujian_id')
                ->select('peserta_ujian_id', DB::raw('COUNT(*) as skor'))
                ->get()
                ->keyBy('peserta_ujian_id');  // Key by peserta_ujian_id

            // Map skor ke peserta ujian
            $pesertaSkor = [];
            foreach ($pesertaUjian as $peserta) {
                $pesertaSkor[$peserta->id_peserta_ujian] = $jawabanPesertaSkor->get($peserta->id_peserta_ujian, ['skor' => 0])['skor'];
            }

            // Urutkan peserta berdasarkan skor tertinggi
            arsort($pesertaSkor);

            // Hitung peringkat
            $rankedPeserta = [];
            foreach (array_keys($pesertaSkor) as $index => $pesertaId) {
                $rankedPeserta[$pesertaId] = [
                    'rank' => $index + 1,
                    'skor' => $pesertaSkor[$pesertaId]
                ];
            }

            // Tentukan midpoint untuk membagi peserta
            $totalPeserta = count($pesertaUjian);
            $midpoint = (int) floor($totalPeserta / 2); // Hitung midpoint

            // Pisahkan peserta menjadi rank atas dan rank bawah
            $pesertaRankAtas = $pesertaUjian->sortBy(function ($peserta) use ($rankedPeserta) {
                return $rankedPeserta[$peserta->id_peserta_ujian]['rank'];
            })->take($midpoint);

            $pesertaRankBawah = $pesertaUjian->sortBy(function ($peserta) use ($rankedPeserta) {
                return $rankedPeserta[$peserta->id_peserta_ujian]['rank'];
            })->skip($midpoint);

            // Hitung jawaban benar per soal untuk peserta di rank atas
            $correctAnswersPerQuestionAtas = [];
            foreach ($pesertaRankAtas as $peserta) {
                foreach ($kunciJawaban as $soalId => $kunci) {
                    if (!isset($correctAnswersPerQuestionAtas[$soalId])) {
                        $correctAnswersPerQuestionAtas[$soalId] = 0;
                    }

                    // Cari jawaban peserta untuk soal ini
                    $jawabanPesertaForSoal = $jawabanPeserta->firstWhere('peserta_ujian_id', $peserta->id_peserta_ujian)
                        ->firstWhere('soal_id', $soalId);

                    if ($jawabanPesertaForSoal && $jawabanPesertaForSoal->jawab == $jawabanPesertaForSoal->soal->kunci) {
                        $correctAnswersPerQuestionAtas[$soalId]++;
                    }
                }
            }

            // Hitung jawaban benar per soal untuk peserta di rank bawah
            $correctAnswersPerQuestionBawah = [];
            foreach ($pesertaRankBawah as $peserta) {
                foreach ($kunciJawaban as $soalId => $kunci) {
                    if (!isset($correctAnswersPerQuestionBawah[$soalId])) {
                        $correctAnswersPerQuestionBawah[$soalId] = 0;
                    }

                    // Cari jawaban peserta untuk soal ini
                    $jawabanPesertaForSoal = $jawabanPeserta->firstWhere('peserta_ujian_id', $peserta->id_peserta_ujian)
                        ->firstWhere('soal_id', $soalId);

                    if ($jawabanPesertaForSoal && $jawabanPesertaForSoal->jawab == $jawabanPesertaForSoal->soal->kunci) {
                        $correctAnswersPerQuestionBawah[$soalId]++;
                    }
                }
            }

            // dd("dsada");


            return view('ujian.daftar-ujian.analisis-soal', compact(
                'ujian',
                'jumlahSoal',
                'kunciJawaban',
                'pesertaUjian',
                'jawabanPeserta',
                'pesertaRankAtas',
                'pesertaRankBawah',
                'correctAnswersPerQuestionAtas',
                'correctAnswersPerQuestionBawah',
                'jumlahPesertaRankAtas',
                'jumlahPesertaRankBawah'
            ));
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }
}
