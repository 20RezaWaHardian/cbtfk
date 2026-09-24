<?php

namespace App\Http\Controllers;

use App\Models\JadwalOsce;
use App\Models\JenisOsce;
use App\Models\SiakadProdi;
use App\Models\SiakadSemester;
use Illuminate\Http\Request;
use App\Helpers\MyHelpers;
use App\DataTables\JadwalOsceDataTable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogAktifitas;
use App\Imports\ImportPesertaOsce;
use Maatwebsite\Excel\Facades\Excel;

class JadwalOsceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(JadwalOsceDataTable $dataTable)
    {
        if (Gate::allows('read data-master/jadwal-osce')) {
            return $dataTable->render('data-master.jadwal-osce.index');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jadwal_osce = new JadwalOsce();

        $prodi = SiakadProdi::KhususCBT()
            ->select('id_prodi', 'nama_prodi', 'id_fakultas')
            ->with(['fakultas' => function ($query) {
                $query->select('id_fakultas', 'nama_fakultas');
            }])->get();

        $semester = SiakadSemester::where('periode_aktif', 1)
            ->get();

        return view('data-master.jadwal-osce.action', compact('jadwal_osce', 'prodi', 'semester'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "id_prodi" => "required",
            "id_semester" => "required",
            "blok_id" => "required",
            "keterangan" => "required",
            "tanggal_ujian" => "required",
            "waktu_mulai" => "required",
            "waktu_selesai" => "required",
        ]);

        $data = JadwalOsce::create($validated);

        LogAktifitas::catat("Menambah Jadwal OSCE dengan id " . $data->id_jadwal_osce);

        return redirect()->route('data-master.jadwal-osce.index')->with('success', 'Berhasil Menambah Jadwal OSCE');
    }

    public function tambahStase($id_jadwal_osce)
    {
        $stase = JenisOsce::where('isDeleted', false)->get();
        return view('data-master.jadwal-osce.form-stase', compact('stase', 'id_jadwal_osce'));
    }

    public function simpanStase(Request $request, $id_jadwal_osce)
    {
        $request->validate([
            'stase' => 'required',
            'id_pegawai' => 'required'
        ]);

        $cek = DB::table('jadwal_has_stase')->where('id_jadwal_osce', $id_jadwal_osce)
            ->where('id_jenis_osce', $request->stase)
            ->where('id_pegawai', $request->id_pegawai)
            ->first();

        $cek2 = DB::table('jadwal_has_stase')->where('id_jadwal_osce', $id_jadwal_osce)
            ->where('id_jenis_osce', $request->stase)
            ->first();

        $cek3 = DB::table('jadwal_has_stase')->where('id_jadwal_osce', $id_jadwal_osce)
            ->where('id_pegawai', $request->id_pegawai)
            ->first();


        if (!$cek && !$cek2 && !$cek3) {
            $simpan = DB::table('jadwal_has_stase')->insertGetId([
                'id_jadwal_osce' => $id_jadwal_osce,
                'id_jenis_osce' => $request->stase,
                'id_pegawai' => $request->id_pegawai
            ]);

            LogAktifitas::catat("Menambah Stase dengan id " . $simpan);

            return redirect()->back()->with('success', 'Berhasil Menambahkan Stase');
        } elseif ($cek) {
            return redirect()->back()->with('error', '❌ Kombinasi stase dan penguji sudah ada pada jadwal ini.');
        } elseif ($cek2) {
            return redirect()->back()->with('error', '⚠️ Stase ini sudah terdaftar pada jadwal tersebut.');
        } elseif ($cek3) {
            return redirect()->back()->with('error', '⚠️ Penguji ini sudah terdaftar pada jadwal tersebut.');
        } else {
            return redirect()->back()->with('error', '❌ Data sudah ada sebelumnya.');
        }
    }

    public function hapusStase($id_jadwal_has_stase)
    {
        $data = DB::table('jadwal_has_stase')->where('id_jadwal_has_stase', $id_jadwal_has_stase)->first();
        if ($data) {
            DB::table('jadwal_has_stase')->where('id_jadwal_has_stase', $id_jadwal_has_stase)->delete();
            LogAktifitas::catat("Menghapus Stase dengan id " . $id_jadwal_has_stase);

            return response()->json([
                'status' => 'success',
                'message' => 'Delete Data Success'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Delete Data Failed'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JadwalOsce $jadwalOsce)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jadwal_osce = JadwalOsce::find($id);

        $prodi = SiakadProdi::KhususCBT()
            ->select('id_prodi', 'nama_prodi', 'id_fakultas')
            ->with(['fakultas' => function ($query) {
                $query->select('id_fakultas', 'nama_fakultas');
            }])->get();

        $semester = SiakadSemester::where('periode_aktif', 1)
            ->get();

        return view('data-master.jadwal-osce.action', compact('jadwal_osce', 'prodi', 'semester'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            "id_prodi" => "required",
            "id_semester" => "required",
            "keterangan" => "required",
            "tanggal_ujian" => "required",
            "waktu_mulai" => "required",
            "waktu_selesai" => "required",
            "status" => 'required'
        ]);

        $data = JadwalOsce::where('id_jadwal_osce', $id)->update($validated);

        LogAktifitas::catat("Mengubah Jadwal OSCE dengan id " . $id);

        return redirect()->route('data-master.jadwal-osce.index')->with('success', 'Berhasil Mengubah Jadwal OSCE');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = JadwalOsce::find($id);
        $keterangan = $data->keterangan;
        $data->update([
            'isDeleted' => 1
        ]);
        LogAktifitas::catat("Menghapus Jadwal OSCE dengan keterangan " . $keterangan . " Pada Jadwal OSCE.");

        return response()->json([
            'status' => '200',
            'message' => 'Delete Data Success'
        ]);
    }

    public function pesertaOsce($id_jadwal_osce)
    {
        // $peserta = DB::table('jadwal_has_mhs as a')->where('id_jadwal_osce', decrypt($id_jadwal_osce))
        //     ->join('siakad.mhs_pt as b', 'b.id_mhs_pt', 'a.id_mhs_pt')
        //     ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', 'b.id_mahasiswa')
        //     ->select('a.id_jadwal_has_mhs','a.id_jadwal_osce', 'b.no_mhs', 'c.nama_mahasiswa', 'a.nilai_akhir')
        //     ->get();
        $jadwal_osce = JadwalOsce::where('id_jadwal_osce',decrypt($id_jadwal_osce))->first();
        $peserta = DB::table('siakad_blok.mahasiswa_rombel as a')
                        ->leftjoin('siakad_blok.kelompok_belajar as b', 'a.id_kelompok_belajar','b.id_kelompok_belajar')
                        ->leftjoin('siakad.mhs_pt as c','c.id_mhs_pt','a.id_mhs_pt')
                        ->join('siakad.mahasiswa as d','d.id_mahasiswa','c.id_mahasiswa')
                        ->where('b.id_blok',$jadwal_osce->blok_id)
                        ->groupBy('a.id_mhs_pt')
                        ->select('a.id_mhs_pt','c.no_mhs','d.nama_mahasiswa')
                        ->get();
        // dd($peserta);
        return view('data-master.jadwal-osce.peserta', compact('id_jadwal_osce', 'peserta'));
    }

    public function tambahPesertaOsce($id_jadwal_osce)
    {
        $peserta = null;
        $station = DB::table('jadwal_has_stase as a')
            ->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
            ->where('a.id_jadwal_osce', decrypt($id_jadwal_osce))
            ->select('a.id_jenis_osce', 'b.nama_jenis_osce')
            ->orderBy('b.nama_jenis_osce')
            ->get();

        return view('data-master.jadwal-osce.tambahPeserta', compact('peserta', 'id_jadwal_osce', 'station'));
    }

    public function storePesertaOsce(Request $request)
    {
        $request->validate([
            'id_mhs_pt' => 'required',
            'id_jenis_osce' => 'required',
            'urutan_antrian' => 'nullable|integer|min:1',
        ]);

        $simpan = DB::transaction(function () use ($request) {
            $jadwalPeserta = DB::table('jadwal_has_mhs')
                ->where('id_jadwal_osce', $request->id_jadwal_osce)
                ->where('id_mhs_pt', $request->id_mhs_pt)
                ->first();

            if (!$jadwalPeserta) {
                $idJadwalHasMhs = DB::table('jadwal_has_mhs')->insertGetId([
                    'id_jadwal_osce' => $request->id_jadwal_osce,
                    'id_mhs_pt' => $request->id_mhs_pt,
                ]);
            } else {
                $idJadwalHasMhs = $jadwalPeserta->id_jadwal_has_mhs;
            }

            $sudahAdaStation = DB::table('peserta_station_osce')
                ->where('id_jadwal_osce', $request->id_jadwal_osce)
                ->where('id_jenis_osce', $request->id_jenis_osce)
                ->where('id_mhs_pt', $request->id_mhs_pt)
                ->exists();

            if (!$sudahAdaStation) {
                $urutan = $request->urutan_antrian ?: ((int) DB::table('peserta_station_osce')
                    ->where('id_jadwal_osce', $request->id_jadwal_osce)
                    ->where('id_jenis_osce', $request->id_jenis_osce)
                    ->max('urutan_antrian') + 1);

                DB::table('peserta_station_osce')->insert([
                    'id_jadwal_osce' => $request->id_jadwal_osce,
                    'id_jenis_osce' => $request->id_jenis_osce,
                    'id_mhs_pt' => $request->id_mhs_pt,
                    'urutan_antrian' => $urutan,
                    'status' => 'menunggu',
                    'waktu_masuk' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $idJadwalHasMhs;
        });

        LogAktifitas::catat("Menambah Peserta OSCE dengan id " . $simpan);

        return redirect()->back()->with('success', 'Berhasil Menambahkan Peserta OSCE');
    }

    public function importPeserta($id_jadwal_osce)
    {
        return view('data-master.jadwal-osce.importPeserta', compact('id_jadwal_osce'));
    }

    public function simpanImportPeserta(Request $request)
    {
        $request->validate([
            'file_import_peserta' => 'required|mimes:xlsx,xls|max:2048',
        ]);


        $file = $request->file('file_import_peserta');

        try {
            $id_jadwal_osce = $request->id_jadwal_osce;
            Excel::import(new ImportPesertaOsce($id_jadwal_osce), $file);
            return redirect()->back()->with('success', 'Import Peserta Berhasil.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor soal: ' . $e->getMessage());
        }
    }

    public function downloadFormatPeserta()
    {
        $filePath = public_path("templateImportPesertaOsce.xlsx");

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return abort(404, 'File not found');
        }
    }

    public function hapusPeserta($id_jadwal_has_mhs)
    {
        $peserta = DB::table('jadwal_has_mhs as a')->where('id_jadwal_has_mhs',decrypt($id_jadwal_has_mhs))->first();

        if($peserta)
        {
            DB::table('jadwal_has_mhs as a')->where('id_jadwal_has_mhs',decrypt($id_jadwal_has_mhs))->delete();
        }

        return redirect()->back()->with('success','Berhasil Menghapus Peserta OSCE');
    }
}
