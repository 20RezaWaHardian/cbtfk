<?php

namespace App\Http\Controllers;

use App\Models\Soal;
use App\Models\PaketSoal;
use Illuminate\Http\Request;
use App\Models\SBKelompokBelajar;
use Illuminate\Support\Facades\DB;

class LoadDataController extends Controller
{
    public function getSoalByKategoriIdPaketSoalid($id_kategori_soal, $id_paket_soal)
    {
        $soal = Soal::whereHas('sub_kategori_soal', function ($query) use ($id_kategori_soal) {
                $query->where('id_kategori_soal', $id_kategori_soal)->active();
            })
            ->where('status_validasi', 1)
            ->whereDoesntHave('paket_soal', function ($query) use ($id_paket_soal) {
                $query->where('paket_soal_id', $id_paket_soal);
            })
            ->with(['soal_pilgan', 'soal_essay', 'sub_kategori_soal'])
            ->get();

        $paket_soal = PaketSoal::findorfail($id_paket_soal);
        return view('bank-soal.paket-soal.partials.soal-list', compact('soal', 'paket_soal'));
    }

    public function getSoalBySubKategoriIdPaketSoalId($id_sub_kategori_soal, $id_paket_soal)
    {
        $soal = Soal::where('sub_kategori_soal_id', $id_sub_kategori_soal)
            ->where('status_validasi', 1)
            ->whereDoesntHave('paket_soal', function ($query) use ($id_paket_soal) {
                $query->where('paket_soal_id', $id_paket_soal);
            })
            ->whereHas('sub_kategori_soal', function ($query) {
                $query->active();
            })
            ->with(['soal_pilgan', 'soal_essay', 'sub_kategori_soal'])
            ->get();

        $paket_soal = PaketSoal::findorfail($id_paket_soal);
        return view('bank-soal.paket-soal.partials.soal-list', compact('soal', 'paket_soal'));
    }

    public function getBlokByIdProdiIdSemester($prodiId, $semesterId)
    {
        // $data = DB::table("siakad.kelas as a")
        //     ->select("a.id_kelas", 'b.nama_matakuliah', 'c.nama_kurikulum', 'a.kode_kelas', 'a.jumlah_peserta', 'b.kode_matakuliah', 'd.nama_blok', 'd.tahun as tahun_blok', 'b.sks_total', 'd.id_blok')
        //     ->leftjoin("siakad.matakuliah as b", 'a.id_matakuliah', '=', 'b.id_matakuliah')
        //     ->leftjoin("siakad.kurikulum as c", 'a.id_kurikulum', '=', 'c.id_kurikulum')
        //     ->leftjoin("siakad_blok.blok as d", 'b.id_blok', '=', 'd.id_blok')
        //     ->leftjoin('siakad_blok.mapping_blok_jenisblok as e', 'e.id_blok', 'd.id_blok')
        //     ->whereIn('e.id_jenis_blok',[22,25])
        //     ->where("sistembl_siakad-uin ", '1')
        //     ->where("a.id_prodi", $prodiId)
        //     ->where("a.id_semester", $semesterId)
        //     ->wherenull("a.id_kelas_parent")
        //     ->orderby("b.nama_blok")
        //     ->orderby("a.id_kelas")
        //     ->groupBy('b.id_blok')
        //     ->get();

        $data = DB::table("siakad.kelas as a")
            ->select('d.nama_blok', 'd.tahun as tahun_blok', 'd.id_blok')
            ->leftJoin("siakad.matakuliah as b", 'a.id_matakuliah', '=', 'b.id_matakuliah')
            ->leftJoin("siakad.kurikulum as c", 'a.id_kurikulum', '=', 'c.id_kurikulum')
            ->leftJoin("siakad_blok.blok as d", 'b.id_blok', '=', 'd.id_blok')
            ->leftJoin('siakad_blok.mapping_blok_jenisblok as e', 'e.id_blok', '=', 'd.id_blok')
            // ->whereIn('e.id_jenis_blok',[22,25])
            ->where("sistembl_siakad-uin ", '1')
            ->where("a.id_prodi", $prodiId)
            ->where("a.id_semester", $semesterId)
            ->whereNull("a.id_kelas_parent")
            ->groupBy('d.nama_blok', 'd.tahun', 'd.id_blok') 
            ->get();


        return response()->json($data);
    }
    public function getKelompokBelajarByIdBlok($idBlok)
    {
        // $data = SBKelompokBelajar::with('jenis_blok')->where('id_blok', $idBlok)->whereIN('id_jenis_blok', [21, 22, 23, 24, 25, 26, 27, 28])->get();
        $data = SBKelompokBelajar::with('jenis_blok')->where('id_blok', $idBlok)->whereIN('id_jenis_blok', [22])->get();
        return response()->json($data);
    }
    public function getPaketSoalByIdProdi($idProdi)
    {
        // $data = SBKelompokBelajar::with('jenis_blok')->where('id_blok', $idBlok)->whereIN('id_jenis_blok', [21, 22, 23, 24, 25, 26, 27, 28])->get();
        if($idProdi != "eksternal")
        {
            $data = PaketSoal::where('prodi_id', $idProdi)->where('is_active', 1)->get();
        }else{
            $data = PaketSoal::where('id_jenis_ujian','!=',1)->where('is_active', 1)->get();
        }
        return response()->json($data);
    }

    public function getPaketSoal()
    {
        $data = PaketSoal::where('id_jenis_ujian',1)
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->get();
        return response()->json($data);
    }

    public function getPegawai(Request $request)
    {
        $datas = DB::table('sistembl_siakad-uin .dosen as a')
            ->where(function ($q) use ($request) {
                $q->orWhere('a.nip', 'like', '%' . $request->search . '%')
                    ->where('a.nidn', 'like', '%' . $request->search . '%')
                    ->orWhere('a.nama', 'like', '%' . $request->search . '%');
            })
            ->take(50)->get();
        $json = [];
        if ($datas) {

            foreach ($datas as $data) {

                $json[] = ['id' => $data->id_dosen, 'text' => $data->nip . " - " . $data->gelar_depan . " " . ucwords(strtolower($data->nama)) . ", " . $data->gelar_belakang];
            }
        }
        return response()->json($json);
    }

    public function getMahasiswa(Request $request)
    {
        $semester = DB::table('siakad.semester')->where('periode_aktif', 1)->first();
        $datas = DB::table('siakad.mhs_pt as a')
            ->join('siakad.reg_mhs as c', 'c.id_mhs_pt', 'a.id_mhs_pt')
            ->whereIn('c.id_status_mahasiswa', ['A', 'C'])
            ->where('c.id_semester', $semester->id_semester)
            ->join('siakad.mahasiswa as b', 'a.id_mahasiswa', '=', 'b.id_mahasiswa')
            ->join('siakad.prodi as d','d.id_prodi','a.id_prodi')
            ->where('id_fakultas',5)
            ->where(function ($q) use ($request) {
                $q->where('a.no_mhs', 'like', $request->search . '%')
                    ->orWhere('b.nama_mahasiswa', 'like', $request->search . '%');
            })
            ->take(50)->get();
        $json = [];
        if ($datas) {

            foreach ($datas as $data) {

                $json[] = ['id' => $data->id_mhs_pt, 'text' => $data->no_mhs . ' - ' . $data->nama_mahasiswa];
            }
        }


        return response()->json($json);
    }
}
