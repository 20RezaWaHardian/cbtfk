<?php

namespace App\Http\Controllers\BankSoal;

use App\DataTables\KategoriSoalDataTable;
use App\Helpers\LogAktifitas;
use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriSoalRequest;
use App\Models\KategoriSoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class KategoriSoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read bank-soal/soal');
    }

    public function index(KategoriSoalDataTable $dataTable)
    {
        if (Gate::allows('read bank-soal/soal')) {
            return $dataTable->render('bank-soal.soal.index');
        }

        abort(403, 'Anda Tidak Memiliki Akses');
    }

    public function create()
    {
        $this->authorize('create bank-soal/soal');

        $semester = $this->getSemesterOptions();

        return view('bank-soal.soal.kategori-action', [
            'kategori_soal' => new KategoriSoal(),
            'semester' => $semester,
        ]);
    }

    public function getBlok(Request $request)
    {
    
        $kelas = DB::table('sistembl_siakad-uin .blok as a')
                    ->select(
                        'a.id',
                        'a.kode',
                        'a.nama',
                        'a.semester_id',
                        'a.prodi_id'
                    )
                    ->where('a.prodi_id',1)
                    ->when($request->id_semester, function ($query) use ($request) {
                        $query->where('a.semester_id', $request->id_semester);
                    })
                    ->orderBy('a.id')
                    ->get();

        return response()->json([
            'kelas' => $kelas,
        ]);
    }

    public function store(KategoriSoalRequest $request)
    {
        $this->authorize('create bank-soal/soal');

        $data = KategoriSoal::create([
            'nama_kategori' => $request->nama_kategori,
            'id_kelas' => $request->id_kelas,
            'id_pelaku' => auth()->user()->id
        ]);

        LogAktifitas::catat("Membuat Kategori " . $data->nama_kategori . " Pada Kategori Soal.");

        return redirect()->route('bank-soal.soal.index')->with('success', 'Berhasil Membuat Kategori Soal');
    }

    public function edit($id)
    {
        $this->authorize('update bank-soal/soal');

        $kategori_soal = KategoriSoal::findOrFail($id);
        $semester = $this->getSemesterOptions();

        return view('bank-soal.soal.kategori-action', compact('kategori_soal', 'semester'));
    }

    public function update(KategoriSoalRequest $request, $id)
    {
        $this->authorize('update bank-soal/soal');

        $data = KategoriSoal::findOrFail($id);
        $kategori_lama = $data->nama_kategori;
        $data->nama_kategori = $request->nama_kategori;
        $data->id_kelas = $request->id_kelas;
        $data->id_pelaku = auth()->user()->id;

        $data->save();

        LogAktifitas::catat("Merubah Kategori " . $kategori_lama . " Menjadi Kategori " . $data->nama_kategori . " Pada Kategori Soal.");

        return redirect()->route('bank-soal.soal.index')->with('success', 'Berhasil Mengupdate Kategori Soal');
    }

    public function destroy($id)
    {
        $this->authorize('delete bank-soal/soal');

        $data = KategoriSoal::findOrFail($id);
        $nama_kategori = $data->nama_kategori;
        $data->delete();

        LogAktifitas::catat("Menghapus Kategori " . $nama_kategori . " Pada Kategori Soal.");

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Data Success',
        ]);
    }

    private function getSemesterOptions()
    {
        // return DB::table('semester')
        //     ->select('id_semester', 'tgl_mulai', 'tgl_selesai', 'periode_aktif')
        //     ->orderByDesc('periode_aktif')
        //     ->orderByDesc('id_semester')
        //     ->get()
        //     ->map(function ($semester) {
        //         return [
        //             'id_semester' => $semester->id_semester,
        //             'nama_semester' => $this->semesterName($semester->id_semester),
        //             'aktif' => (bool) $semester->periode_aktif,
        //         ];
        //     });
        return DB::table('sistembl_siakad-uin .semester')
                    ->where('is_aktif',1)
                    ->get()
                    ->map(function ($semester) {
                        return [
                            'id_semester' => $semester->id_semester,
                            'nama_semester' => $this->semesterName($semester->kode),
                            'aktif' => (bool) $semester->is_aktif,
                        ];
                    });
    }

    private function semesterName(string $idSemester): string
    {
        $tahun = substr($idSemester, 0, 4);
        $jenis = substr($idSemester, -1);
        $tahunAkhir = (int) $tahun + 1;

        return match ($jenis) {
            '1' => "Ganjil {$tahun} / {$tahunAkhir}",
            '2' => "Genap {$tahun} / {$tahunAkhir}",
            '3' => "Pendek {$tahun}",
            default => $idSemester,
        };
    }
}