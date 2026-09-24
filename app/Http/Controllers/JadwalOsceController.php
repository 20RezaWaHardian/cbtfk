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

        // $prodi = SiakadProdi::KhususCBT()
        //     ->select('id_prodi', 'nama_prodi', 'id_fakultas')
        //     ->with(['fakultas' => function ($query) {
        //         $query->select('id_fakultas', 'nama_fakultas');
        //     }])->get();

        $semester = SiakadSemester::semesterAktif()->first();
        $blok = DB::table('sistem_blok.blok as a')
            ->where('a.semester_id', $semester->id_semester)
            ->join('sistem_blok.kelompok_blok as b','a.id','b.blok_id')
            ->join('sistem_blok.aturan_kegiatan_blok as c','c.id','b.aturan_kegiatan_blok_id')
            ->where('c.jenis_kegiatan_id',15)
            ->select('a.id', 'a.nama', 'a.kode')
            ->get();

        return view('data-master.jadwal-osce.action', compact(
            'jadwal_osce', 
            'semester',
            'blok'
            ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // "id_prodi" => "required",
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
        Gate::authorize('delete data-master/jadwal-osce');
        $data = DB::table('jadwal_has_stase')->where('id_jadwal_has_stase', $id_jadwal_has_stase)->first();
        if ($data) {
            if (DB::table('peserta_station_osce')->where('id_jadwal_osce', $data->id_jadwal_osce)->where('id_jenis_osce', $data->id_jenis_osce)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Stase sudah memiliki antrean atau riwayat penilaian.'], 422);
            }
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
        Gate::authorize('read data-master/jadwal-osce');
        // $peserta = DB::table('jadwal_has_mhs as a')->where('id_jadwal_osce', decrypt($id_jadwal_osce))
        //     ->join('siakad.mhs_pt as b', 'b.id_mhs_pt', 'a.id_mhs_pt')
        //     ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', 'b.id_mahasiswa')
        //     ->select('a.id_jadwal_has_mhs','a.id_jadwal_osce', 'b.no_mhs', 'c.nama_mahasiswa', 'a.nilai_akhir')
        //     ->get();
        $jadwal_osce = JadwalOsce::where('isDeleted', false)->findOrFail(decrypt($id_jadwal_osce));
        $calon = DB::table('sistem_blok.peserta_blok as a')
                        // ->leftjoin('siakad_blok.kelompok_belajar as b', 'a.id_kelompok_belajar','b.id_kelompok_belajar')
                        // ->leftjoin('siakad.mhs_pt as c','c.id_mhs_pt','a.id_mhs_pt')
                        ->join('sistem_blok.mahasiswa as d','d.id_mahasiswa','a.mahasiswa_id')
                        ->where('a.blok_id',$jadwal_osce->blok_id)
                        ->distinct()
                        ->select('d.id_mahasiswa as id_mhs_pt','d.nim as no_mhs','d.nama as nama_mahasiswa')
                        ->get();
        // dd($peserta);
        $peserta = DB::table('jadwal_has_mhs as a')
            ->join('sistem_blok.mahasiswa as b', 'b.id_mahasiswa', '=', 'a.id_mhs_pt')
            // ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', '=', 'b.id_mahasiswa')
            ->where('a.id_jadwal_osce', $jadwal_osce->id_jadwal_osce)
            ->select('a.*', 'b.nim as no_mhs', 'b.nama as nama_mahasiswa')->orderBy('b.nim')->get();
        $station = DB::table('jadwal_has_stase as a')->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
            ->where('a.id_jadwal_osce', $jadwal_osce->id_jadwal_osce)->select('a.id_jenis_osce', 'b.nama_jenis_osce')->get();
        $riwayat = DB::table('peserta_station_osce')->where('id_jadwal_osce', $jadwal_osce->id_jadwal_osce)
            ->orderBy('id_peserta_station_osce')->get()->groupBy('id_mhs_pt');
        return view('data-master.jadwal-osce.peserta', compact('id_jadwal_osce', 'jadwal_osce', 'peserta', 'calon', 'station', 'riwayat'));
    }

    public function tambahPesertaOsce($id_jadwal_osce)
    {
        Gate::authorize('create data-master/jadwal-osce');
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
        Gate::authorize('create data-master/jadwal-osce');
        $request->validate([
            'id_jadwal_osce' => 'required|string',
            'id_mhs_pt' => 'required|array|min:1',
            'id_mhs_pt.*' => 'required|integer|distinct',
            'id_jenis_osce' => 'nullable|integer|min:1',
            'urutan_antrian' => 'nullable|integer|min:1',
        ]);
        $idJadwal = decrypt($request->id_jadwal_osce);
        app(\App\Services\OsceParticipantService::class)->setParticipants(
            $idJadwal, $request->id_mhs_pt, $request->filled('id_jenis_osce') ? (int) $request->id_jenis_osce : null,
            $request->filled('urutan_antrian') ? (int) $request->urutan_antrian : null
        );
        return redirect()->route('data-master.pesertaOsce', encrypt($idJadwal))->with('success', 'Peserta dan pembagian stase berhasil disimpan.');
    }

    public function hapusPeserta($id_jadwal_has_mhs)
    {
        Gate::authorize('delete data-master/jadwal-osce');
        app(\App\Services\OsceParticipantService::class)->removeParticipant(decrypt($id_jadwal_has_mhs));
        return back()->with('success', 'Peserta dan antrean awal berhasil dihapus.');
    }

}
