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
            $peserta_ujian = PesertaUjian::with('peserta_eksternal')->where('ujian_id', $ujian->id_ujian)
                ->select(
                    'id_peserta_ujian',
                    'ip_address',
                    'koreksi',
                    'mulai_ujian',
                    'waktu_berhenti',
                    'sisa_waktu',
                    'id_mhs_pt',
                    
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


    /**
     * Mengirim sisa waktu terbaru untuk tabel monitoring tanpa mengubah data DB.
     */
    public function remainingTimes($ujianId)
    {
        $ujian = Ujian::with('paket_soal')->findOrFail(decrypt($ujianId));

        // Durasi paket tersimpan sebagai format waktu (HH:MM:SS).
        $durationParts = array_map('intval', explode(':', (string) optional($ujian->paket_soal)->durasi));
        $durationParts = array_pad($durationParts, 3, 0);
        $durationInSeconds = ($durationParts[0] * 3600) + ($durationParts[1] * 60) + $durationParts[2];
        $serverNow = Carbon::now();

        // Query ini hanya membaca kolom yang dibutuhkan timer monitoring.
        $participants = PesertaUjian::where('ujian_id', $ujian->id_ujian)
            ->get([
                'id_peserta_ujian',
                'status_pengerjaan',
                'mulai_ujian',
                'sisa_waktu',
                'updated_at',
            ])
            ->map(function ($participant) use ($durationInSeconds, $serverNow) {
                $remainingSeconds = null;

                if ($participant->status_pengerjaan === 1) {
                    if (!is_null($participant->sisa_waktu)) {
                        // Kurangi waktu sejak sinkronisasi terakhir agar nilai DB lama tidak menaikkan timer.
                        $elapsedSinceSync = Carbon::parse($participant->updated_at)->diffInSeconds($serverNow);
                        $remainingSeconds = max(0, (int) $participant->sisa_waktu - $elapsedSinceSync);
                    } elseif ($participant->mulai_ujian) {
                        // Peserta baru memakai rumus sama dengan room: waktu mulai ditambah durasi paket.
                        $finishAt = Carbon::parse($participant->mulai_ujian)->addSeconds($durationInSeconds);
                        $remainingSeconds = max(0, $serverNow->diffInSeconds($finishAt, false));
                    }
                } elseif ($participant->status_pengerjaan === 3) {
                    // Saat dihentikan, sisa waktu tidak berjalan sampai peserta dilanjutkan.
                    $remainingSeconds = max(0, (int) $participant->sisa_waktu);
                } elseif ($participant->status_pengerjaan === 2) {
                    $remainingSeconds = 0;
                }

                return [
                    'id_peserta_ujian' => $participant->id_peserta_ujian,
                    'status_pengerjaan' => $participant->status_pengerjaan,
                    'remaining_seconds' => $remainingSeconds,
                ];
            });

        return response()->json([
            'server_time' => $serverNow->timestamp,
            'participants' => $participants,
        ]);
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
        $peserta_ujian = PesertaUjian::where('ujian_id', $ujian->id_ujian)->get();
        $peserta_ujian_ba = PesertaUjian::where('ujian_id', $ujian->id_ujian)
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
