<?php

namespace App\Http\Controllers;


use App\Models\JadwalOsce;
use App\Models\KomponenNilaiOsce;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OsceAntrianController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read osce-antrian');
        $this->middleware('can:update osce-antrian')->only(['nilai', 'simpanNilai', 'simpanNilaiKomponen']);
    }

    public function index()
    {
        $data = JadwalOsce::where('jadwal_osce.isDeleted', false)
            ->when(!auth()->user()->hasRole('developer'), fn ($q) => $q->whereIn('jadwal_osce.id_jadwal_osce', DB::table('jadwal_has_stase')->where('id_pegawai', $this->idPenguji())->select('id_jadwal_osce')))
            ->where('jadwal_osce.status', 1)
            ->orderByDesc('jadwal_osce.tanggal_ujian')
            ->get();

        return view('osce-antrian.index', compact('data'));
    }

    public function station($id_jadwal_osce)
    {
        $idJadwal = decrypt($id_jadwal_osce);
        $this->ensureAssignment($idJadwal);
        $jadwal = JadwalOsce::findOrFail($idJadwal);
        $station = DB::table('jadwal_has_stase as a')
            ->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
            ->leftJoin('peserta_station_osce as ps', function ($join) {
                $join->on('ps.id_jadwal_osce', '=', 'a.id_jadwal_osce')
                    ->on('ps.id_jenis_osce', '=', 'a.id_jenis_osce')
                    ->whereIn('ps.status', ['menunggu', 'sedang_dinilai']);
            })
            ->where('a.id_jadwal_osce', $idJadwal)
            ->when(!auth()->user()->hasRole('developer'), fn ($q) => $q->where('a.id_pegawai', $this->idPenguji()))
            ->groupBy('a.id_jadwal_has_stase', 'a.id_jadwal_osce', 'a.id_jenis_osce', 'a.id_pegawai', 'b.nama_jenis_osce')
            ->select('a.*', 'b.nama_jenis_osce', DB::raw('COUNT(ps.id_peserta_station_osce) as jumlah_antrian'))
            ->orderBy('b.nama_jenis_osce')
            ->get();

        return view('osce-antrian.station', compact('jadwal', 'station'));
    }

    public function peserta($id_jadwal_osce, $id_jenis_osce)
    {
        $idJadwal = decrypt($id_jadwal_osce);
        $idJenis = decrypt($id_jenis_osce);
        $this->ensureAssignment($idJadwal, $idJenis);
        $jadwal = JadwalOsce::findOrFail($idJadwal);
        $station = DB::table('jenis_osce')->where('id_jenis_osce', $idJenis)->first();
        $stations = DB::table('jadwal_has_stase as a')
            ->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
            ->where('a.id_jadwal_osce', $idJadwal)
            ->select('a.id_jenis_osce', 'b.nama_jenis_osce')
            ->orderBy('b.nama_jenis_osce')
            ->get();
        $peserta = $this->queryPesertaStation($idJadwal, $idJenis)->get();

        return view('osce-antrian.peserta', compact('jadwal', 'station', 'stations', 'peserta'));
    }

    public function nilai($id_peserta_station_osce)
    {
        $idPesertaStation = decrypt($id_peserta_station_osce);
        $this->ensureParticipant($idPesertaStation);
        $pesertaStation = DB::table('peserta_station_osce as ps')
            ->join('jadwal_osce as j', 'j.id_jadwal_osce', '=', 'ps.id_jadwal_osce')
            ->join('jenis_osce as s', 's.id_jenis_osce', '=', 'ps.id_jenis_osce')
            ->join('sistem_blok.mahasiswa as m', 'm.id_mahasiswa', '=', 'ps.id_mhs_pt')
            ->where('ps.id_peserta_station_osce', $idPesertaStation)
            ->select('ps.*', 'j.keterangan', 's.nama_jenis_osce', 'm.nim as no_mhs', 'm.nama as nama_mahasiswa')
            ->first();

        abort_if(!$pesertaStation, 404);

        if ($pesertaStation->status === 'menunggu') {
            DB::transaction(function () use ($pesertaStation, $idPesertaStation) {
                JadwalOsce::where('id_jadwal_osce', $pesertaStation->id_jadwal_osce)->lockForUpdate()->firstOrFail();
                $this->ensureParticipant($idPesertaStation);
                DB::table('peserta_station_osce')->where('id_peserta_station_osce', $idPesertaStation)
                ->update(['status' => 'sedang_dinilai', 'waktu_mulai' => now(), 'updated_at' => now()]);
            });
            $pesertaStation->status = 'sedang_dinilai';
        }

        $komponen = KomponenNilaiOsce::where('id_jenis_osce', $pesertaStation->id_jenis_osce)
            ->where('isDeleted', false)
            ->with('instrumenNilai')
            ->get();
        $nilai = DB::table('nilai_peserta_station_osce')
            ->where('id_peserta_station_osce', $idPesertaStation)
            ->pluck('nilai', 'id_komponen_nilai_osce');
        foreach ($komponen as $k) {
            $k->nilai = $nilai[$k->id_komponen_nilai_osce] ?? null;
            if ($k->instrumenNilai->isEmpty()) {
                $k->setRelation('instrumenNilai', $this->instrumenNilaiKomponen($k));
            }
        }

        $stationBerikutnya = DB::table('jadwal_has_stase as a')
            ->join('jenis_osce as b', 'b.id_jenis_osce', '=', 'a.id_jenis_osce')
            ->where('a.id_jadwal_osce', $pesertaStation->id_jadwal_osce)
            ->where('a.id_jenis_osce', '!=', $pesertaStation->id_jenis_osce)
            ->whereNotIn('a.id_jenis_osce', function ($query) use ($pesertaStation) {
                $query->select('id_jenis_osce')->from('peserta_station_osce')
                    ->where('id_jadwal_osce', $pesertaStation->id_jadwal_osce)
                    ->where('id_mhs_pt', $pesertaStation->id_mhs_pt)
                    ;
            })
            ->select('a.id_jenis_osce', 'b.nama_jenis_osce')
            ->orderBy('b.nama_jenis_osce')
            ->get();

        return view('osce-antrian.nilai', compact('pesertaStation', 'komponen', 'stationBerikutnya'));
    }

    public function simpanNilai(Request $request)
    {
        $request->validate([
            'id_peserta_station_osce' => 'required|integer',
            'tombol' => 'required|in:simpan,selesai',
            'id_station_berikutnya' => 'nullable',
        ]);

        $authorized = $this->ensureParticipant($request->id_peserta_station_osce);

        if ($request->tombol === 'selesai' && !$request->id_station_berikutnya) {
            return back()->withInput()->with('error', 'Pilih station berikutnya atau Selesai Ujian.');
        }

        try {
            DB::transaction(function () use ($request) {
            $context = DB::table('peserta_station_osce')->where('id_peserta_station_osce', $request->id_peserta_station_osce)->first();
            abort_if(!$context, 404);
            JadwalOsce::where('id_jadwal_osce', $context->id_jadwal_osce)->lockForUpdate()->firstOrFail();
            $this->ensureParticipant($request->id_peserta_station_osce);
            $peserta = DB::table('peserta_station_osce')->where('id_peserta_station_osce', $request->id_peserta_station_osce)->lockForUpdate()->first();
            if (!$peserta) {
                throw new \Exception('Data peserta station tidak ditemukan.');
            }

            foreach ($request->nilai_mhs ?? [] as $idKomponen => $nilai) {
                $this->validasiNilaiKomponen($peserta, $idKomponen, $nilai);

                DB::table('nilai_peserta_station_osce')->updateOrInsert([
                    'id_peserta_station_osce' => $request->id_peserta_station_osce,
                    'id_komponen_nilai_osce' => $idKomponen,
                ], [
                    'id_pegawai' => $this->idPenguji(),
                    'nilai' => $nilai,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($request->tombol === 'selesai') {
                $this->validateDestination($peserta, $request->id_station_berikutnya);
                $komponenWajib = $this->komponenWajibDenganInstrumen($peserta->id_jenis_osce);
                $wajib = $komponenWajib->count();
                $terisi = DB::table('nilai_peserta_station_osce')
                    ->where('id_peserta_station_osce', $request->id_peserta_station_osce)
                    ->whereIn('id_komponen_nilai_osce', $komponenWajib)
                    ->count();
                if ($wajib === 0 || $wajib !== $terisi) {
                    throw new \Exception('Semua nilai wajib diisi.');
                }

                DB::table('peserta_station_osce')->where('id_peserta_station_osce', $request->id_peserta_station_osce)->update([
                    'status' => $request->id_station_berikutnya === 'selesai_ujian' ? 'selesai_ujian' : 'selesai_dinilai',
                    'waktu_selesai' => now(),
                    'id_pegawai_penilai' => $this->idPenguji(),
                    'id_station_berikutnya' => $request->id_station_berikutnya !== 'selesai_ujian' ? $request->id_station_berikutnya : null,
                    'updated_at' => now(),
                ]);

                if ($request->id_station_berikutnya !== 'selesai_ujian') {
                    DB::table('peserta_station_osce')->insert([
                        'id_jadwal_osce' => $peserta->id_jadwal_osce,
                        'id_jenis_osce' => $request->id_station_berikutnya,
                        'id_mhs_pt' => $peserta->id_mhs_pt,
                        'urutan_antrian' => $this->urutanBerikutnya($peserta->id_jadwal_osce, $request->id_station_berikutnya),
                        'status' => 'menunggu',
                        'waktu_masuk' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('osce-antrian.peserta', [encrypt($authorized->id_jadwal_osce), encrypt($authorized->id_jenis_osce)])->with('success', 'Nilai/antrian OSCE berhasil diproses.');
    }

    public function simpanNilaiKomponen(Request $request)
    {
        $request->validate([
            'id_peserta_station_osce' => 'required|integer',
            'id_komponen_nilai_osce' => 'required|integer',
            'nilai' => 'required|numeric',
        ]);

        $this->ensureParticipant($request->id_peserta_station_osce);

        try {
            $data = DB::transaction(function () use ($request) {
                $context = DB::table('peserta_station_osce')->where('id_peserta_station_osce', $request->id_peserta_station_osce)->first();
                abort_if(!$context, 404);
                JadwalOsce::where('id_jadwal_osce', $context->id_jadwal_osce)->lockForUpdate()->firstOrFail();
                $this->ensureParticipant($request->id_peserta_station_osce);
                $peserta = DB::table('peserta_station_osce')
                    ->where('id_peserta_station_osce', $request->id_peserta_station_osce)
                    ->lockForUpdate()
                    ->first();

                if (!$peserta) {
                    throw new \Exception('Data peserta station tidak ditemukan.');
                }

                $instrumen = $this->validasiNilaiKomponen($peserta, $request->id_komponen_nilai_osce, $request->nilai);

                if ($peserta->status === 'menunggu') {
                    DB::table('peserta_station_osce')
                        ->where('id_peserta_station_osce', $peserta->id_peserta_station_osce)
                        ->update([
                            'status' => 'sedang_dinilai',
                            'waktu_mulai' => now(),
                            'updated_at' => now(),
                        ]);
                }

                DB::table('nilai_peserta_station_osce')->updateOrInsert([
                    'id_peserta_station_osce' => $peserta->id_peserta_station_osce,
                    'id_komponen_nilai_osce' => $request->id_komponen_nilai_osce,
                ], [
                    'id_pegawai' => $this->idPenguji(),
                    'nilai' => $request->nilai,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $komponenWajib = $this->komponenWajibDenganInstrumen($peserta->id_jenis_osce);
                $jumlahKomponen = $komponenWajib->count();
                $jumlahTerisi = DB::table('nilai_peserta_station_osce')
                    ->where('id_peserta_station_osce', $peserta->id_peserta_station_osce)
                    ->whereIn('id_komponen_nilai_osce', $komponenWajib)
                    ->count();

                return compact('instrumen', 'jumlahKomponen', 'jumlahTerisi');
            });

            return response()->json([
                'success' => true,
                'message' => 'Nilai tersimpan.',
                'nilai' => $data['instrumen']->nilai,
                'keterangan' => $data['instrumen']->keterangan,
                'jumlah_komponen' => $data['jumlahKomponen'],
                'jumlah_terisi' => $data['jumlahTerisi'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function validasiNilaiKomponen($peserta, $idKomponen, $nilai)
    {
        $komponen = KomponenNilaiOsce::where('id_komponen_nilai_osce', $idKomponen)
            ->where('id_jenis_osce', $peserta->id_jenis_osce)
            ->where('isDeleted', false)
            ->first();

        if (!$komponen) {
            throw new \Exception('Komponen nilai tidak valid untuk station ini.');
        }

        $instrumen = DB::table('instrumen_nilai_osce')
            ->where('id_komponen_nilai_osce', $idKomponen)
            ->where('nilai', $nilai)
            ->first();

        if (!$instrumen) {
            $idKomponenTemplate = KomponenNilaiOsce::where('nama_komponen', $komponen->nama_komponen)
                ->where('isDeleted', false)
                ->whereHas('instrumenNilai')
                ->where('id_komponen_nilai_osce', '!=', $komponen->id_komponen_nilai_osce)
                ->value('id_komponen_nilai_osce');

            if ($idKomponenTemplate) {
                $instrumen = DB::table('instrumen_nilai_osce')
                    ->where('id_komponen_nilai_osce', $idKomponenTemplate)
                    ->where('nilai', $nilai)
                    ->first();
            }
        }

        if (!$instrumen) {
            throw new \Exception('Nilai tidak sesuai instrumen komponen.');
        }

        return $instrumen;
    }

    private function instrumenNilaiKomponen(KomponenNilaiOsce $komponen)
    {
        $idKomponenTemplate = KomponenNilaiOsce::where('nama_komponen', $komponen->nama_komponen)
            ->where('isDeleted', false)
            ->whereHas('instrumenNilai')
            ->where('id_komponen_nilai_osce', '!=', $komponen->id_komponen_nilai_osce)
            ->value('id_komponen_nilai_osce');

        if (!$idKomponenTemplate) {
            return collect();
        }

        return DB::table('instrumen_nilai_osce')
            ->where('id_komponen_nilai_osce', $idKomponenTemplate)
            ->orderBy('nilai')
            ->get();
    }

    private function komponenWajibDenganInstrumen($idJenisOsce)
    {
        return KomponenNilaiOsce::where('id_jenis_osce', $idJenisOsce)
            ->where('isDeleted', false)
            ->get()
            ->filter(fn ($komponen) => $komponen->instrumenNilai()->exists() || $this->instrumenNilaiKomponen($komponen)->isNotEmpty())
            ->pluck('id_komponen_nilai_osce');
    }

    private function queryPesertaStation($idJadwal, $idJenis)
    {
        return DB::table('peserta_station_osce as ps')
            // ->join('siakad.mhs_pt as mp', 'mp.id_mhs_pt', '=', 'ps.id_mhs_pt')
            ->join('sistem_blok.mahasiswa as m', 'm.id_mahasiswa', '=', 'ps.id_mhs_pt')
            ->whereExists(function ($q) {
                $q->selectRaw('1')->from('jadwal_has_mhs as jm')->whereColumn('jm.id_jadwal_osce', 'ps.id_jadwal_osce')->whereColumn('jm.id_mhs_pt', 'ps.id_mhs_pt');
            })
            ->where('ps.id_jadwal_osce', $idJadwal)
            ->where('ps.id_jenis_osce', $idJenis)
            ->select('ps.*', 'm.nim as no_mhs', 'm.nama as nama_mahasiswa')
            ->selectSub(DB::table('nilai_peserta_station_osce as n')->selectRaw('COALESCE(SUM(n.nilai), 0)')->whereColumn('n.id_peserta_station_osce', 'ps.id_peserta_station_osce'), 'nilai_station')
            ->orderByRaw("CASE ps.status WHEN 'sedang_dinilai' THEN 1 WHEN 'menunggu' THEN 2 WHEN 'selesai_dinilai' THEN 3 ELSE 4 END")
            ->orderBy('ps.urutan_antrian');
    }

    private function urutanBerikutnya($idJadwal, $idJenis): int
    {
        return ((int) DB::table('peserta_station_osce')
            ->where('id_jadwal_osce', $idJadwal)
            ->where('id_jenis_osce', $idJenis)
            ->max('urutan_antrian')) + 1;
    }

    private function idPenguji(): ?int
    {
        if (auth()->user()->hasRole('developer')) {
            return null;
        }
        $id = auth()->user()?->dosen?->id_dosen;
        abort_if(!$id, 403, 'Akun tidak terhubung dengan penguji OSCE.');
        return (int) $id;
    }

    private function ensureAssignment($idJadwal, $idStase = null): void
    {
        abort_unless(JadwalOsce::where('id_jadwal_osce', $idJadwal)->where('isDeleted', false)->where('status', 1)->exists(), 403, 'Jadwal ujian tidak aktif.');
        if (auth()->user()->hasRole('developer')) {
            if ($idStase !== null) {
                abort_unless(DB::table('jadwal_has_stase')->where('id_jadwal_osce', $idJadwal)->where('id_jenis_osce', $idStase)->exists(), 403, 'Stase bukan milik jadwal ini.');
            }
            return;
        }
        abort_unless(DB::table('jadwal_has_stase')->where('id_jadwal_osce', $idJadwal)
            ->where('id_pegawai', $this->idPenguji())->when($idStase !== null, fn ($q) => $q->where('id_jenis_osce', $idStase))->exists(), 403, 'Stase bukan penugasan Anda.');
    }

    private function ensureParticipant($id)
    {
        $peserta = DB::table('peserta_station_osce')->where('id_peserta_station_osce', $id)->first();
        abort_if(!$peserta, 404);
        $this->ensureAssignment($peserta->id_jadwal_osce, $peserta->id_jenis_osce);
        abort_unless(DB::table('jadwal_has_mhs')->where('id_jadwal_osce', $peserta->id_jadwal_osce)->where('id_mhs_pt', $peserta->id_mhs_pt)->exists(), 403, 'Peserta tidak terdaftar pada jadwal.');
        abort_unless(in_array($peserta->status, ['menunggu', 'sedang_dinilai']), 409, 'Penilaian stase sudah selesai.');
        return $peserta;
    }

    private function validateDestination($peserta, $tujuan): void
    {
        $riwayat = DB::table('peserta_station_osce')->where('id_jadwal_osce', $peserta->id_jadwal_osce)->where('id_mhs_pt', $peserta->id_mhs_pt)->get();
        if ($riwayat->contains(fn ($row) => $row->id_peserta_station_osce != $peserta->id_peserta_station_osce && in_array($row->status, ['menunggu', 'sedang_dinilai']))) {
            throw new \Exception('Peserta masih memiliki antrean aktif pada stase lain.');
        }
        $stase = DB::table('jadwal_has_stase')->where('id_jadwal_osce', $peserta->id_jadwal_osce)->pluck('id_jenis_osce');
        if ($tujuan === 'selesai_ujian') {
            $selesai = $riwayat->whereIn('status', ['selesai_dinilai', 'selesai_ujian'])->pluck('id_jenis_osce')->push($peserta->id_jenis_osce);
            if ($stase->diff($selesai)->isNotEmpty()) {
                throw new \Exception('Masih ada stase jadwal yang belum diselesaikan.');
            }
        } elseif (!ctype_digit((string) $tujuan) || !$stase->contains((int) $tujuan) || $riwayat->contains('id_jenis_osce', (int) $tujuan)) {
            throw new \Exception('Stase tujuan harus milik jadwal ini dan belum dikunjungi peserta.');
        }
    }
}
