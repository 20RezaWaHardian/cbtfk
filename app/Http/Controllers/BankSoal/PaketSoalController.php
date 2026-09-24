<?php

namespace App\Http\Controllers\BankSoal;

use App\Models\Soal;
use App\Models\PaketSoal;
use App\Imports\SoalImport;
use App\Jobs\ImportSoalJob;
use App\Models\SiakadProdi;
use App\Models\KategoriSoal;
use App\Models\PaketHasSoal;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\PaketSoalDataTable;
use App\Http\Requests\PaketSoalRequest;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Builder;

class PaketSoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read bank-soal/paket-soal');
    }

    public function index(PaketSoalDataTable $dataTable)
    {
        if (Gate::allows('read bank-soal/paket-soal')) {
            return $dataTable->render('bank-soal.paket-soal.index');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    public function create()
    {
        $this->authorize('create bank-soal/paket-soal');

        $jenis_ujian = DB::table('jenis_ujian')
                            ->where('status',1)
                            ->get();

        return view('bank-soal.paket-soal.paket-soal-action', [
            'paket_soal' => new PaketSoal(), 
            'jenis_ujian' => $jenis_ujian
        ]);
    }

    public function store(PaketSoalRequest $request)
    {
        $this->authorize('update bank-soal/paket-soal');

        $data = $request->all();
        $data['created_by'] = auth()->user()->id_asal;
        $paket_soal = PaketSoal::create($data);
        LogAktifitas::catat("Membuat Paket Soal Dengan Judul" . $paket_soal->judul . " Pada Paket Soal.");
        return response()->json([
            'status' => 'success',
            'message' => 'Create Data Success'
        ]);
    }

    public function edit($id)
    {
        $this->authorize('update bank-soal/paket-soal');
        $paket_soal = PaketSoal::Find($id);
        $prodi = SiakadProdi::KhususCBT()
            ->select('id_prodi', 'nama_prodi', 'id_fakultas')
            ->with(['fakultas' => function ($query) {
                $query->select('id_fakultas', 'nama_fakultas');
            }])->get();
        $jenis_ujian = DB::table('jenis_ujian')
                            ->where('status',1)
                            ->get();

        return view('bank-soal.paket-soal.paket-soal-action', compact('paket_soal', 'prodi','jenis_ujian'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update bank-soal/paket-soal');
        $data = PaketSoal::findOrFail($id);
        $judul_lama = $data->judul;
        $data->judul = $request->judul;
        $data->id_jenis_ujian = $request->id_jenis_ujian;
        $data->prodi_id = $request->prodi_id;
        $data->kkm = $request->kkm;
        $data->durasi = $request->durasi;
        $data->ketentuan = $request->ketentuan;
        $data->deskripsi = $request->deskripsi;
        $data->save();
        LogAktifitas::catat("Merubah Paket Soal Dengan Judul " . $judul_lama . " Menjadi  " . $data->judul . " Pada Paket Soal.");
        return response()->json([
            'status' => 'success',
            'message' => 'Update Data Success'
        ]);
    }

    public function destroy($id)
    {
        $this->authorize('delete bank-soal/paket-soal');
        $data = PaketSoal::find($id);
        $judul = $data->judul;
        // $data->delete();
        $data->update([
            'is_delete' => 1
        ]);
        LogAktifitas::catat("Menghapus Paket Soal Dengan Judul " . $judul . " Pada Paket Soal.");

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Data Success'
        ]);
    }
    public function updateStatus($id, $is_active)
    {
        $paketSoal = PaketSoal::find(decrypt($id));
        if ($is_active == 1) {
            $paketSoal->is_active = 0;
            $paketSoal->save();
            return redirect()->back()->with('success', 'Paket Soal Berhasil Di Nonaktifkan');
        } elseif ($is_active == 0) {
            $paketSoal->is_active = 1;
            $paketSoal->save();
            return redirect()->back()->with('success', 'Paket Soal Berhasil Di Aktifkan');
        }
    }
    // Show soal berdasarkan paket soal
    public function showSoalByPaketSoal($id)
    {
        $paket_soal = PaketSoal::findorfail(decrypt($id));

        $kategori_soal = KategoriSoal::get();
        $soal = [];
        if ($paket_soal) {
            $soal = $paket_soal->soal()->with('soal_pilgan')->get();
        }
        // dd($soal);
        return view('bank-soal.paket-soal.show-soal', compact('paket_soal', 'soal', 'kategori_soal'));
    }

    //Validasi Poin

    public function validasiPoin($id)
    {
        $paket_soal = PaketSoal::findorfail($id);
        $paket_has_soal = PaketHasSoal::where('paket_soal_id', $paket_soal->id_paket_soal)->get();
        if (count($paket_has_soal) > 0) {
            $totalSoal = $paket_has_soal->count();

            $totalPoin = 100;
            round($totalPoin / $totalSoal, 2);

            foreach ($paket_has_soal as $item) {
                $item->update(['poin' => $poinPerSoal]);
            }
            return redirect()->back()->with('success', 'Poin berhasil digenerate untuk soal dalam paket ini.');
        } else {
            return redirect()->back()->with('error', 'Tidak ada soal yang ditemukan untuk paket ini.');
        }
    }
    //Store Soal
    public function storeSoal(Request $request)
    {
        $request->validate([
            'id_paket_soal' => 'required|exists:paket_soal,id_paket_soal',
            'id_soal' => is_array($request->id_soal) ? 'required|array|min:1' : 'required|exists:soal,id_soal',
            'id_soal.*' => 'exists:soal,id_soal',
        ]);

        $paketSoal = PaketSoal::findOrFail($request->id_paket_soal);
        $idSoal = is_array($request->id_soal) ? $request->id_soal : [$request->id_soal];

        if ($paketSoal) {
            $paketSoal->soal()->syncWithoutDetaching($idSoal);
            LogAktifitas::catat("Menambah Soal Dengan ID SOAL : " . implode(', ', $idSoal) . " dan ID PAKET SOAL : " . $request->id_paket_soal . " pada paket has soal.");
            return response()->json([
                'status' => 'success',
                'message' => count($idSoal) . ' Soal Berhasil Ditambahkan'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menambahkan Soal, Silahkan Coba Lagi (Paket Soal Tidak Ditemukan)'
            ]);
        }
    }

    public function destroySoal($id_paket_soal, $id_soal)
    {
        $this->authorize('delete bank-soal/paket-soal');


        $paketSoal = PaketSoal::findOrFail($id_paket_soal);

        if ($paketSoal->soal()->where('soal_id', $id_soal)->exists()) {
            $paketSoal->soal()->detach($id_soal);
            LogAktifitas::catat("Menghapus Soal Dengan ID SOAL : " . $id_soal . " dan ID PAKET SOAL : " . $id_paket_soal . " pada paket has soal.");
            return redirect()->back()->with('success', 'Soal Berhasil Dihapus');
        } else {
            return redirect()->back()->with('error', 'Gagal Menghapus Soal');
        }
    }

    // Pilih Soal
    public function pilihSoal($id)
    {
        $paket_soal = PaketSoal::findorfail($id);
        $kategoriQuery = KategoriSoal::with(['sub_kategori_soal' => function ($query) {
            $query->active()->orderBy('nama_kategori');
        }])->whereHas('sub_kategori_soal', function ($query) {
            $query->active();
        });

        if (auth()->user()->hasAnyRole('koordinator-blok', 'kaprodi')) {
            $co_blok = DB::table('siakad_blok.koordinator_blok')
                ->where('id_pegawai_koor', auth()->user()->pegawai->pegawai_siakad_id)
                ->orwhere('id_pegawai_ass', auth()->user()->pegawai->pegawai_siakad_id)
                ->pluck('id_kelas')->toArray();

            $kategori_soal = $kategoriQuery->wherein('id_kelas', $co_blok)->orderBy('nama_kategori')->get();
        } else {
            $kategori_soal = $kategoriQuery->orderBy('nama_kategori')->get();
        }

        return view('bank-soal.paket-soal.kategori-soal', compact('paket_soal', 'kategori_soal'));
    }

    //Pakai Queque
    // public function importSoal(Request $request,$id_paket_soal)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls|max:2048',
    //     ]);

    //     // Ambil file dan dispatch job ke queue
    //     $file = $request->file('file');
    //     $kategori_soal_id = $request->kategori_soal_id;

    //     try {
    //         // Dispatch job untuk menjalankan import di background
    //         ImportSoalJob::dispatch($file->getRealPath(), $id_paket_soal,$kategori_soal_id);

    //         return redirect()->back()->with('success', 'Soal sedang diproses di background, Anda akan diberi tahu saat selesai.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor soal: ' . $e->getMessage());
    //     }
    // }

    // Cara Biasa
    public function importSoal(Request $request, $id_paket_soal)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        // Ambil file dan dispatch job ke queue
        $file = $request->file('file');
        $kategori_soal_id = $request->kategori_soal_id;

        try {

            // Load the file to count the rows
            $collection = Excel::toCollection(null, $file);

            // Assuming the first sheet contains the questions
            $rows = $collection[0]; // Get the first sheet
            $jumlahSoal = $rows->count(); // Count total rows

            $jumlahSoal -= 1; // Skip header row (if applicable)


            $totalPoin = 100;

            $poinPersoal = $totalPoin / $jumlahSoal;
            if ($jumlahSoal <= 0) {
                return redirect()->back()->with('error', 'Tidak ada soal yang ditemukan untuk diimpor.');
            }
            Excel::import(new SoalImport($id_paket_soal,  $kategori_soal_id, $poinPersoal), $file);
            return redirect()->back()->with('success', 'Soal sedang diproses di background, Anda akan diberi tahu saat selesai.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor soal: ' . $e->getMessage());
        }
    }

    public function downloadFormatSoal()
    {
        $filePath = public_path("format_import_soal.xlsx");

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return abort(404, 'File not found');
        }
    }

    public function analisisSoal($id_paket_soal)
    {
        $id = decrypt($id_paket_soal);
        $paket_soal = PaketSoal::with("soal")->find($id);
        // ambil semua soal pada paket soal
        $data = [];
        foreach ($paket_soal->soal as $p) {
            $arr["id_soal"] = $p->id_soal;
            $arr["pertanyaan"] = $p->pertanyaan;
            $arr["jumlah_benar"] = DB::table("pilgan_jawab")
                ->where("soal_id", $p->id_soal)
                ->where("status", "T")
                ->count();
            $arr["jumlah_salah"] = DB::table("pilgan_jawab")
                ->where("soal_id", $p->id_soal)
                ->where("status", "F")
                ->count();
            $data[] = $arr;
        }
        return view("bank-soal.paket-soal.analisis-soal", compact("paket_soal", "data"));
    }
}
