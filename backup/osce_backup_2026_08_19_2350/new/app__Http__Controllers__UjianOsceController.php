<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalOsce;
use App\Models\KomponenNilaiOsce;
use App\Models\SiakadMhspt;
use DB;
use Yajra\DataTables\Facades\DataTables;

class UjianOsceController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole(['pengawas'])) {
            $data = JadwalOsce::where('jadwal_osce.isDeleted', false)
                ->join('jadwal_has_stase as b', 'b.id_jadwal_osce', 'jadwal_osce.id_jadwal_osce')
                ->join('jenis_osce as c', 'c.id_jenis_osce', 'b.id_jenis_osce')
                ->where('b.id_pegawai', auth()->user()->pegawai->id_pegawai)
                ->where('jadwal_osce.status', 1)
                ->get();
        } elseif (auth()->user()->hasRole(['developer', 'admin'])) {
            $data = JadwalOsce::where('jadwal_osce.isDeleted', false)
                ->join('jadwal_has_stase as b', 'b.id_jadwal_osce', 'jadwal_osce.id_jadwal_osce')
                ->join('jenis_osce as c', 'c.id_jenis_osce', 'b.id_jenis_osce')
                ->where('jadwal_osce.status', 1)
                ->get();
        } else {
            $data = collect();
        }

        return view('data-master.ujian-osce.index', compact('data'));
    }

    public function detailMahasiswa(Request $request, $id_jadwal_osce, $id_jenis_osce)
    {
        $idJadwal = decrypt($id_jadwal_osce);
        $idJenis = decrypt($id_jenis_osce);
        $this->pastikanAksesStation($idJadwal, $idJenis);

        $jadwal = JadwalOsce::where('jadwal_osce.isDeleted', false)
            ->join('jadwal_has_stase as b', 'b.id_jadwal_osce', 'jadwal_osce.id_jadwal_osce')
            ->join('jenis_osce as c', 'c.id_jenis_osce', 'b.id_jenis_osce')
            ->where('jadwal_osce.id_jadwal_osce', $idJadwal)
            ->where('c.id_jenis_osce', $idJenis)
            ->where('jadwal_osce.status', 1)
            ->select('jadwal_osce.*', 'c.nama_jenis_osce', 'c.id_jenis_osce')
            ->firstOrFail();

        if ($request->ajax()) {
            $data = DB::table('peserta_station_osce as ps')
                ->join('siakad.mhs_pt as mp', 'mp.id_mhs_pt', '=', 'ps.id_mhs_pt')
                ->join('siakad.mahasiswa as m', 'm.id_mahasiswa', '=', 'mp.id_mahasiswa')
                ->leftJoin('nilai_peserta_station_osce as n', 'n.id_peserta_station_osce', '=', 'ps.id_peserta_station_osce')
                ->where('ps.id_jadwal_osce', $idJadwal)
                ->where('ps.id_jenis_osce', $idJenis)
                ->whereIn('ps.status', ['menunggu', 'sedang_dinilai', 'selesai_dinilai'])
                ->groupBy('ps.id_peserta_station_osce', 'ps.id_mhs_pt', 'mp.no_mhs', 'm.nama_mahasiswa', 'ps.urutan_antrian', 'ps.status')
                ->select('ps.id_peserta_station_osce', 'ps.id_mhs_pt', 'mp.no_mhs', 'm.nama_mahasiswa', 'ps.urutan_antrian', 'ps.status', DB::raw('COALESCE(SUM(n.nilai),0) as nilai_station'));

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nim', fn ($row) => $row->no_mhs ?? '-')
                ->addColumn('nama_mahasiswa', fn ($row) => $row->nama_mahasiswa ?? '-')
                ->addColumn('nilai_akhir', fn ($row) => $row->nilai_station ?? 0)
                ->addColumn('status_station', fn ($row) => ucfirst(str_replace('_', ' ', $row->status)))
                ->addColumn('aksi', function ($p) use ($jadwal) {
                    if ($p->status === 'selesai_dinilai') {
                        return '<span class="badge bg-success">Selesai Dinilai</span>';
                    }

                    $url = route('data-master.beriNilai', [
                        'id_mhs_pt' => encrypt($p->id_mhs_pt),
                        'id_jadwal_osce' => encrypt($jadwal->id_jadwal_osce),
                        'id_jenis_osce' => encrypt($jadwal->id_jenis_osce),
                    ]);

                    return '<a href="' . $url . '" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> Beri Nilai</a>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return view('data-master.ujian-osce.detail-mahasiswa', compact('jadwal'));
    }

    public function beriNilai($id_mhs_pt, $id_jadwal_osce, $id_jenis_osce)
    {
        $idMhsPt = decrypt($id_mhs_pt);
        $idJadwal = decrypt($id_jadwal_osce);
        $idJenis = decrypt($id_jenis_osce);
        $this->pastikanAksesStation($idJadwal, $idJenis);

        $pesertaStation = DB::table('peserta_station_osce')
            ->where('id_jadwal_osce', $idJadwal)
            ->where('id_jenis_osce', $idJenis)
            ->where('id_mhs_pt', $idMhsPt)
            ->whereIn('status', ['menunggu', 'sedang_dinilai'])
            ->orderByDesc('id_peserta_station_osce')
            ->first();

        if (!$pesertaStation) {
            return redirect()->back()->with('error', 'Peserta tidak ada di antrian station ini atau sudah selesai dinilai.');
        }

        DB::table('peserta_station_osce')
            ->where('id_peserta_station_osce', $pesertaStation->id_peserta_station_osce)
            ->where('status', 'menunggu')
            ->update(['status' => 'sedang_dinilai', 'waktu_mulai' => now(), 'updated_at' => now()]);

        $pesertaStation->status = 'sedang_dinilai';
        $jadwal = JadwalOsce::where('jadwal_osce.isDeleted', false)
            ->join('jadwal_has_stase as b', 'b.id_jadwal_osce', 'jadwal_osce.id_jadwal_osce')
            ->join('jenis_osce as c', 'c.id_jenis_osce', 'b.id_jenis_osce')
            ->where('jadwal_osce.id_jadwal_osce', $idJadwal)
            ->where('c.id_jenis_osce', $idJenis)
            ->select('jadwal_osce.*', 'c.nama_jenis_osce', 'c.id_jenis_osce')
            ->firstOrFail();

        $komponen = KomponenNilaiOsce::where('id_jenis_osce', $idJenis)
            ->where('isDeleted', false)
            ->with(['instrumenNilai'])
            ->get();

        $nilaiTersimpan = DB::table('nilai_peserta_station_osce')
            ->where('id_peserta_station_osce', $pesertaStation->id_peserta_station_osce)
            ->pluck('nilai', 'id_komponen_nilai_osce');

        foreach ($komponen as $k) {
            $k->nilai = $nilaiTersimpan[$k->id_komponen_nilai_osce] ?? null;
        }

        $mhs_pt = SiakadMhspt::with('mahasiswa')->findOrFail($idMhsPt);
        $nilaiStase = $nilaiTersimpan->sum();

        $stationBerikutnya = DB::table('jadwal_has_stase as a')
            ->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
            ->where('a.id_jadwal_osce', $idJadwal)
            ->where('a.id_jenis_osce', '!=', $idJenis)
            ->whereNotIn('a.id_jenis_osce', function ($query) use ($idJadwal, $idMhsPt) {
                $query->select('id_jenis_osce')
                    ->from('peserta_station_osce')
                    ->where('id_jadwal_osce', $idJadwal)
                    ->where('id_mhs_pt', $idMhsPt)
                    ->whereIn('status', ['selesai_dinilai', 'selesai_ujian']);
            })
            ->select('a.id_jenis_osce', 'b.nama_jenis_osce')
            ->orderBy('b.nama_jenis_osce')
            ->get();

        return view('data-master.ujian-osce.beri-nilai', compact('idMhsPt', 'id_mhs_pt', 'jadwal', 'komponen', 'mhs_pt', 'nilaiStase', 'pesertaStation', 'stationBerikutnya'));
    }

    public function simpanNilaiStase(Request $request)
    {
        $rules = [
            'id_peserta_station_osce' => 'required|integer',
            'id_jadwal_osce' => 'required|integer',
            'id_jenis_osce' => 'required|integer',
            'id_mhs_pt' => 'required',
            'tombol' => 'required|in:create,edit,selesai',
            'id_station_berikutnya' => 'nullable',
            'catatan_perpindahan' => 'nullable|string',
        ];

        foreach ($request->input('nilai_mhs', []) as $id => $nilai) {
            $rules["nilai_mhs.$id"] = 'required|in:0,1,2,3';
        }

        $request->validate($rules);
        if ($request->tombol === 'selesai' && !$request->id_station_berikutnya) {
            return redirect()->back()->withInput()->with('error', 'Pilih station berikutnya atau Selesai Ujian.');
        }
        $idMhsPt = decrypt($request->id_mhs_pt);
        $this->pastikanAksesStation($request->id_jadwal_osce, $request->id_jenis_osce);

        DB::transaction(function () use ($request, $idMhsPt) {
            foreach ($request->nilai_mhs as $key => $value) {
                DB::table('nilai_peserta_station_osce')->updateOrInsert(
                    [
                        'id_peserta_station_osce' => $request->id_peserta_station_osce,
                        'id_komponen_nilai_osce' => $key,
                    ],
                    [
                        'id_pegawai' => auth()->user()->pegawai->id_pegawai ?? null,
                        'nilai' => $value,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                DB::table('komponen_has_mhs')->updateOrInsert(
                    [
                        'id_mhs_pt' => $idMhsPt,
                        'id_komponen_osce' => $key,
                        'id_jadwal_osce' => $request->id_jadwal_osce,
                        'id_jenis_osce' => $request->id_jenis_osce,
                    ],
                    ['nilai' => $value]
                );
            }

            $this->hitungNilaiAkhir($request->id_jadwal_osce, $idMhsPt);

            if ($request->tombol === 'selesai') {
                $komponenWajib = KomponenNilaiOsce::where('id_jenis_osce', $request->id_jenis_osce)
                    ->where('isDeleted', false)
                    ->count();
                $nilaiTerisi = DB::table('nilai_peserta_station_osce')
                    ->where('id_peserta_station_osce', $request->id_peserta_station_osce)
                    ->count();

                if ($komponenWajib !== $nilaiTerisi) {
                    throw new \Exception('Semua komponen nilai wajib diisi sebelum selesai dinilai.');
                }

                $update = [
                    'status' => $request->id_station_berikutnya === 'selesai_ujian' ? 'selesai_ujian' : 'selesai_dinilai',
                    'waktu_selesai' => now(),
                    'id_pegawai_penilai' => auth()->user()->pegawai->id_pegawai ?? null,
                    'id_station_berikutnya' => $request->id_station_berikutnya !== 'selesai_ujian' ? $request->id_station_berikutnya : null,
                    'catatan_perpindahan' => $request->catatan_perpindahan,
                    'updated_at' => now(),
                ];

                DB::table('peserta_station_osce')
                    ->where('id_peserta_station_osce', $request->id_peserta_station_osce)
                    ->update($update);

                if ($request->id_station_berikutnya && $request->id_station_berikutnya !== 'selesai_ujian') {
                    $urutan = (int) DB::table('peserta_station_osce')
                        ->where('id_jadwal_osce', $request->id_jadwal_osce)
                        ->where('id_jenis_osce', $request->id_station_berikutnya)
                        ->max('urutan_antrian') + 1;

                    DB::table('peserta_station_osce')->insert([
                        'id_jadwal_osce' => $request->id_jadwal_osce,
                        'id_jenis_osce' => $request->id_station_berikutnya,
                        'id_mhs_pt' => $idMhsPt,
                        'urutan_antrian' => $urutan,
                        'status' => 'menunggu',
                        'waktu_masuk' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('data-master.detailMahasiswa', [
            'id_jadwal_osce' => encrypt($request->id_jadwal_osce),
            'id_jenis_osce' => encrypt($request->id_jenis_osce),
        ])->with('success', $request->tombol === 'selesai' ? 'Peserta selesai dinilai dan antrian diperbarui.' : 'Berhasil Menyimpan Nilai Station.');
    }

    private function pastikanAksesStation($idJadwal, $idJenis): void
    {
        if (auth()->user()->hasRole(['developer', 'admin'])) {
            return;
        }

        $punyaAkses = DB::table('jadwal_has_stase')
            ->where('id_jadwal_osce', $idJadwal)
            ->where('id_jenis_osce', $idJenis)
            ->where('id_pegawai', auth()->user()->pegawai->id_pegawai ?? null)
            ->exists();

        if (!$punyaAkses) {
            abort(403, 'Anda Tidak Memiliki Akses Station Ini');
        }
    }

    private function hitungNilaiAkhir($idJadwal, $idMhsPt): void
    {
        $jumlahStase = DB::table('jadwal_has_stase')->where('id_jadwal_osce', $idJadwal)->count();
        if ($jumlahStase === 0) {
            return;
        }

        $nilaiStaseMhs = DB::table('komponen_has_mhs')
            ->where('id_mhs_pt', $idMhsPt)
            ->where('id_jadwal_osce', $idJadwal)
            ->sum('nilai');

        DB::table('jadwal_has_mhs')
            ->where('id_jadwal_osce', $idJadwal)
            ->where('id_mhs_pt', $idMhsPt)
            ->update(['nilai_akhir' => ($nilaiStaseMhs / $jumlahStase) * 10]);
    }
}
