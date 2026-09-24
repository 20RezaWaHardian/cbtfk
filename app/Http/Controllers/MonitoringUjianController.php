<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Ujian;
use App\Models\LogAktivitas;
use App\Models\PaketHasSoal;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\DataTables\MonitoringUjianDataTable;
use Illuminate\Support\Str;


class MonitoringUjianController extends Controller
{

    public function indexUtama(MonitoringUjianDataTable $monitoringUjianDataTable)
    {

        return view('monitoring.indexUtama', [
            'monitoringUjianDataTable' => $monitoringUjianDataTable->htmlTable(),

        ]);
    }

    public function settingToken($idUjian)
    {
        $ujian = Ujian::findorfail(decrypt($idUjian));

        $token = Str::upper(Str::random(6));

        $ujian->update([
            'token' => $token
        ]);

        return redirect()->back()->with("success","Berhasil Membuat Tokenisasi");
    }

    public function index($ujianId)
    {
        try {
            $ujianId = decrypt($ujianId);
            $ujian = Ujian::findorfail($ujianId);
            $peserta_ujian = PesertaUjian::with('mahasiswa_rombel','peserta_eksternal')->where('ujian_id', $ujian->id_ujian)
                ->select(
                    'id_peserta_ujian',
                    'ip_address',
                    'koreksi',
                    'mulai_ujian',
                    'waktu_berhenti',
                    'sisa_waktu',
                    'id_mhs_pt',
                    'mahasiswa_rombel_id',
                    'id_peserta_eksternal',
                    'ujian_id',
                    'nilai',
                    'nilai_akhir',
                    'status_pengerjaan',
                    'face_register',
                    'created_at',
                    'updated_at',
                    'kehadiran',
                    'kesamaan_foto',
                    'alat_bantu',
                    'menyontek',
                    'berbicara',
                    'latency',
                    'latency_update',
                    'need_kuesioner',
                    'isi_kuesioner',
                    'kuesioner_id'

                )->get();
            $belum_mengerjakan = PesertaUjian::where('ujian_id', $ujian->id_ujian)->where('status_pengerjaan', 0)
                ->select(
                    'id_peserta_ujian',
                    'ip_address',
                    'koreksi',
                    'mulai_ujian',
                    'waktu_berhenti',
                    'sisa_waktu',
                    'id_mhs_pt',
                    'mahasiswa_rombel_id',
                    'ujian_id',
                    'nilai',
                    'nilai_akhir',
                    'status_pengerjaan',
                    'face_register',
                    'created_at',
                    'updated_at',
                    'kehadiran',
                    'kesamaan_foto',
                    'alat_bantu',
                    'menyontek',
                    'berbicara',
                    'latency',
                    'latency_update',
                    'need_kuesioner',
                    'isi_kuesioner',
                    'kuesioner_id'

                )
                ->count();
            $sedang_mengerjakan = PesertaUjian::where('ujian_id', $ujian->id_ujian)->where('status_pengerjaan', 1)
                ->select(
                    'id_peserta_ujian',
                    'ip_address',
                    'koreksi',
                    'mulai_ujian',
                    'waktu_berhenti',
                    'sisa_waktu',
                    'id_mhs_pt',
                    'mahasiswa_rombel_id',
                    'ujian_id',
                    'nilai',
                    'nilai_akhir',
                    'status_pengerjaan',
                    'face_register',
                    'created_at',
                    'updated_at',
                    'kehadiran',
                    'kesamaan_foto',
                    'alat_bantu',
                    'menyontek',
                    'berbicara',
                    'latency',
                    'latency_update',
                    'need_kuesioner',
                    'isi_kuesioner',
                    'kuesioner_id'

                )
                ->count();
            $selesai_mengerjakan = PesertaUjian::where('ujian_id', $ujian->id_ujian)->where('status_pengerjaan', 2)
                ->select(
                    'id_peserta_ujian',
                    'ip_address',
                    'koreksi',
                    'mulai_ujian',
                    'waktu_berhenti',
                    'sisa_waktu',
                    'id_mhs_pt',
                    'mahasiswa_rombel_id',
                    'ujian_id',
                    'nilai',
                    'nilai_akhir',
                    'status_pengerjaan',
                    'face_register',
                    'created_at',
                    'updated_at',
                    'kehadiran',
                    'kesamaan_foto',
                    'alat_bantu',
                    'menyontek',
                    'berbicara',
                    'latency',
                    'latency_update',
                    'need_kuesioner',
                    'isi_kuesioner',
                    'kuesioner_id'

                )
                ->count();
            $jumlah_soal = PaketHasSoal::with('soal')->where('paket_soal_id', $ujian->paket_soal_id)->count();

            $roomName = $ujian->room_name;
            $roomUrl = env('METERED_DOMAIN') . "/{$roomName}";


            return view('monitoring.index', [
                'ujian' => $ujian,
                'peserta_ujian' => $peserta_ujian,
                'jumlah_soal' => $jumlah_soal,
                'belum_mengerjakan' => $belum_mengerjakan,
                'sedang_mengerjakan' => $sedang_mengerjakan,
                'selesai_mengerjakan' => $selesai_mengerjakan,
                'roomUrl' => $roomUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function logAktivitas($id_peserta_ujian)
    {
        $peserta = PesertaUjian::findorfail($id_peserta_ujian);
        $tanggalMulai = Carbon::parse($peserta->ujian->tanggal_ujian)->format('Y-m-d');
        $tanggalSelesai = Carbon::parse($peserta->ujian->selesai_ujian)->format('Y-m-d');

        if ($peserta && $tanggalMulai && $tanggalSelesai) {
            $nim = $peserta->mahasiswa ? strtoupper($peserta->mahasiswa->nim) : $peserta->peserta_eksternal->username;
            $log = LogAktivitas::where('username', $nim)
                ->whereRaw('DATE(tanggal) BETWEEN ? AND ?', [$tanggalMulai, $tanggalSelesai])
                ->get();

            return view('monitoring.log-aktivitas', compact('peserta', 'log'));
        } else {
            return back();
        }
    }
    public function beritaAcara($id_peserta_ujian)
    {

        $peserta = PesertaUjian::findorfail($id_peserta_ujian);
        return view('monitoring.berita-acara', compact('peserta'));
    }
    public function updateBeritaAcara(Request $request, $id_peserta_ujian)
    {

        $peserta = PesertaUjian::findorfail($id_peserta_ujian);

        $peserta->kehadiran = isset($request->kehadiran) ? 1 : 0;
        $peserta->kesamaan_foto = isset($request->kesamaan_foto) ? 1 : 0;
        $peserta->alat_bantu = isset($request->alat_bantu) ? 1 : 0;
        $peserta->menyontek = isset($request->menyontek) ? 1 : 0;
        $peserta->berbicara = isset($request->berbicara) ? 1 : 0;
        $peserta->save();

        $keterangan = 'Berita Acara Tersimpan';
        return redirect()->back()->with('success', $keterangan);
    }
    public function updateStatusPeserta($id_peserta_ujian)
    {

        $peserta = PesertaUjian::findorfail($id_peserta_ujian);
        if ($peserta->status_pengerjaan == 1) {
            $peserta->status_pengerjaan = 3;
            $peserta->save();
            $keterangan = 'Ujian Peserta Telah Dihentikan';
        } elseif ($peserta->status_pengerjaan == 3) {
            // $waktuMulaiSebelumnya = $peserta->mulai_ujian;
            // $selisihWaktu = Carbon::parse($peserta->updated_at)->diffInSeconds($waktuMulaiSebelumnya);
            // // Perbarui mulai_ujian
            // $peserta->mulai_ujian = Carbon::parse($waktuMulaiSebelumnya)->subSeconds($selisihWaktu);
            $peserta->status_pengerjaan = 1;

            $peserta->save();
            $keterangan = 'Ujian Peserta Telah Dimulai';
        }

        return redirect()->back()->with('success', $keterangan);
    }

    public function downloadBeritaAcara($id_ujian)
    {
        $ujian = Ujian::findorfail(decrypt($id_ujian));
        $peserta_ujian = PesertaUjian::with('mahasiswa_rombel')->where('ujian_id', $ujian->id_ujian)->get();
        $peserta_ujian_ba = PesertaUjian::with('mahasiswa_rombel')
            ->where('ujian_id', $ujian->id_ujian)
            ->where(function ($query) {
                $query->where('kesamaan_foto', 1)
                    ->orWhere('alat_bantu', 1)
                    ->orWhere('menyontek', 1)
                    ->orWhere('berbicara', 1);
            })
            ->get();
        $data = [
            'ujian' => $ujian,
            'peserta_ujian' => $peserta_ujian,
            'peserta_ujian_ba' => $peserta_ujian_ba,
        ];

        // Render tampilan untuk PDF
        $pdf = Pdf::loadView('monitoring.download-berita-acara', $data);

        // Unduh PDF
        return $pdf->download('Berita_Acara.pdf');
    }

    public function mulaiPesertAll($id_ujian)
    {
        $idUjian = decrypt($id_ujian);
        $data = PesertaUjian::where('ujian_id', $idUjian)->where('status_pengerjaan', 3)->count();
        if ($data > 0) {

            $data = PesertaUjian::where('ujian_id', $idUjian)->where('status_pengerjaan', 3)->update([
                'status_pengerjaan' => 1
            ]);

            return redirect()->back()->with('success', 'Berhasil Memulai Ujian');
        }
    }
}
