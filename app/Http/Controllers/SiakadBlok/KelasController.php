<?php

namespace App\Http\Controllers\SiakadBlok;

use App\Helpers\SiakadBlok;
use Carbon\Carbon;

use App\Models\SiakadProdi;
use Illuminate\Http\Request;
use App\Models\SiakadSemester;
use App\Models\SBKelompokBelajar;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SBRiwayatMappingBlog;
use App\Models\SBBlok;
use App\Models\SBDosenPengampu;
use App\Models\SBJenisBlok;
use App\Models\SBMahasiswaRombel;
use App\Models\SBRiwayatMappingBlog as ModelsSBRiwayatMappingBlog;
use App\Models\SiakadBlokKelompokBelajar;
use Illuminate\Support\Facades\Gate;

class KelasController extends Controller
{
    // LogAktifitas::catat("Login ke CBT-FKIK"); ->tambahkan ini utk setiap kondisi create,edit, dan delete
    public function __construct()
    {
        $this->middleware('can:read kelas');
    }
    public function index()
    {
        if (Gate::allows('read kelas')) {
            $prodi = SiakadProdi::KhususCBT()
                ->select('id_prodi', 'nama_prodi', 'id_fakultas')
                ->with(['fakultas' => function ($query) {
                    $query->select('id_fakultas', 'nama_fakultas');
                }])->get();

            $data_semester = SiakadSemester::select('id_semester', 'tgl_mulai', 'tgl_selesai')->orderBy('id_semester', 'desc')->where('periode_aktif',1)
                ->get();
            $semester = $data_semester->map(function ($semester) {
                $lastDigit = substr($semester->id_semester, -1);
                if ($lastDigit == 1) {
                    $nama_semester = 'Ganjil';
                } elseif ($lastDigit == 2) {
                    $nama_semester = 'Genap';
                } else {
                    $nama_semester = 'Pendek';
                }

                $yearStart = date('Y', strtotime($semester->tgl_mulai));
                $yearEnd = date('Y', strtotime($semester->tgl_selesai));

                $tahun = $yearStart == $yearEnd ? $yearStart : "$yearStart/$yearEnd";

                return [
                    'id_semester' => $semester->id_semester,
                    'nama_semester' => $nama_semester,
                    'tahun' => $tahun,
                ];
            });


            return view('kelas.index', compact('prodi', 'semester'));
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    public function getBlockByKelasSemester($id_kelas, $id_semester)
    {
        $kelas = DB::table("siakad.kelas as a")
            ->select("a.id_kelas", 'b.nama_matakuliah', 'c.nama_kurikulum', 'a.kode_kelas', 'a.jumlah_peserta', 'b.kode_matakuliah', 'd.nama_blok', 'd.tahun as tahun_blok', 'b.sks_total', 'd.id_blok')
            ->leftjoin("siakad.matakuliah as b", 'a.id_matakuliah', '=', 'b.id_matakuliah')
            ->leftjoin("siakad.kurikulum as c", 'a.id_kurikulum', '=', 'c.id_kurikulum')
            ->leftjoin("siakad_blok.blok as d", 'b.id_blok', '=', 'd.id_blok')
            ->where("sistem_blok", '1')
            ->where("a.id_prodi", $id_kelas)
            ->where("a.id_semester", $id_semester)
            ->wherenull("a.id_kelas_parent")
            ->orderby("b.nama_blok")
            ->orderby("a.id_kelas")
            ->paginate(4000);
        $kelasmerge = DB::table("siakad.kelas as a")
            ->wherenotnull("id_kelas_parent")
            ->where("a.id_prodi", $id_kelas)
            ->where("a.id_semester", $id_semester)
            ->get();

        $kelasmerg = array();

        foreach ($kelasmerge as $km) {
            $idparent = $km->id_kelas_parent;
            $id_kelas = $km->id_kelas;
            $kelasmerg[$idparent][] = $km->jumlah_peserta;
        }

        $data = array();

        $data['kelasmerge'] = $kelasmerg;
        $data['kelas'] = $kelas;

        // dd($data);
        return view('kelas.partials.bloks-list', compact('data'));
    }
    public function getBlockDetail($id_kelas)
    {
        $id_kelas = decrypt($id_kelas);
        $kelas = DB::table("siakad.kelas as a")
            ->select("a.id_kelas", 'b.nama_matakuliah', 'c.nama_kurikulum', 'a.kode_kelas', 'a.jumlah_peserta', 'b.kode_matakuliah',  'd.nama_blok', 'd.tahun as tahun_blok', 'b.sks_total', 'd.id_blok')
            ->leftjoin("siakad.matakuliah as b", 'a.id_matakuliah', '=', 'b.id_matakuliah')
            ->leftjoin("siakad.kurikulum as c", 'a.id_kurikulum', '=', 'c.id_kurikulum')
            ->leftjoin("siakad_blok.blok as d", 'b.id_blok', '=', 'd.id_blok')
            ->where('a.id_kelas', $id_kelas)
            ->wherenull("a.id_kelas_parent")
            ->first();

        $blok = SBBlok::where('id_blok', $kelas->id_blok)->first();
        $id_block = $blok->id_blok;
        if ($blok->id_prodi == 14201) //ilmu keperawatan
        {

            $jenisBlok = SBJenisBlok::whereIn('id_jenisblok', [25, 26, 27])->get();
            // $data = [];
            // foreach ($jenisBlok as $key => $value) {
            //     $data['jenis_blok'][] = $value->jenis_blok;
            //     foreach(SiakadBlok::kelompok_belajar($id_block,$item->id_jenisblok) as $item)
            //     {
            //         $data['']
            //     }
            // }
        } else {
            $jenisBlok = SBJenisBlok::whereIn('id_jenisblok', [21, 22, 23, 24])->get();
        }
        return view('kelas.detail-block_list', compact(
            'jenisBlok',
            'id_block',
            'id_kelas',
            'kelas'
        ));
    }

    public function getMahasiswaKelompokBelajar($id_kelompok, Request $request)
    {
        $mahasiswa = SBMahasiswaRombel::where('id_kelompok_belajar', $id_kelompok)
            ->where('id_kelas', $request->kelas)
            ->get();
        return view('kelas.partials.mahasiswa', compact('mahasiswa'));
    }

    public function getMateriKelompokBelajar($id_blok, $id_jenis_blok, Request $r)
    {
        $pokokmateri = DB::table('siakad_blok.materi_perkuliahan_blok as a')
            ->select('a.*', 'b.id_blok', 'b.id_jenis_blok')
            ->leftJoin('siakad_blok.mapping_blok_jenisblok as b', 'a.id_mapping_blok', 'b.id')
            ->where('b.id_blok', $id_blok)
            ->where('b.id_jenis_blok', $id_jenis_blok)
            ->orderBy('a.nomor_urut')
            ->get();

        $i = 0;
        $data = [];
        foreach ($pokokmateri as $p) {
            $data[$i] = $p;

            $mat = DB::table('siakad_blok.materi_perkuliahan_rincian as a')
                ->select("a.*", 'b.id_pegawai', 'b.id_dosen', 'b.nama', 'b.gelar_depan', 'b.gelar_belakang', 'b.nip', 'b.id_jabfung', 'b.sks', 'b.id_dosen_pengampu')
                ->leftjoin('siakad_blok.dosen_pengampu as b', function ($join) use ($r) {
                    $join->on("a.id_materi_perkuliahan_rincian", '=', 'b.id_materi_perkuliahan_rincian')
                        ->where("b.id_kelompok_belajar", $r->kelompok);
                })
                ->where('id_materi_perkuliahan_blok', $p->id_materi_perkuliahan_blok)
                ->orderby("a.nomor_urut")
                ->orderby("b.id_dosen_pengampu")
                ->get();

            if (count($mat) > 0) {
                $data[$i]->rincian = $mat;
            } else {
                $data[$i]->rincian = array();
            }
            $i++;
        }
        // dd($data);
        return view('kelas.partials.materi', compact('data'));
    }
}
