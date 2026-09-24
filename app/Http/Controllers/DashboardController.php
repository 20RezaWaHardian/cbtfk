<?php

namespace App\Http\Controllers;

use App\Models\Soal;
use App\Models\Ujian;
use App\Models\PaketSoal;
use App\Models\SiakadProdi;
use App\Models\LogAktivitas;
use App\Models\PaketHasSoal;
use App\Models\PesertaUjian;
use Illuminate\Http\Request;
use App\Models\SBMahasiswaRombel;
use Illuminate\Support\Facades\Gate;
use App\DataTables\UjianSayaDataTable;
use Stevebauman\Location\Facades\Location;
use App\DataTables\MonitoringUjianDataTable;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index(UjianSayaDataTable $ujianSayaDataTable, MonitoringUjianDataTable $monitoringUjianDataTable, Request $request)
    {
        // dd(auth()->user()->roles);
        $user = auth()->user();

        if ($user && $user->can('read dashboard')) {
            // if (auth()->user()->hasRole('mahasiswa')) {
            //     return redirect()->route('keycloak.logout')->with('error', 'Silahkan akses aplikasi CBT melalui <a href="https://siakad-blok.unja.ac.id/">Siakad Blok</a>.');
            // }
            $ujian = Ujian::count();
            $soal = Soal::count();
            $paket_soal = PaketSoal::where('is_delete',0)->count();
            // $prodi = SiakadProdi::KhususCBT()->count();
            $ip = $request->ip();
            $currentUserInfo = Location::get($ip);
            $aktivitas = LogAktivitas::orderBy('created_at', 'desc')->take(5)->get();

            return view('dashboard', [
                'paket_soal' => $paket_soal,
                'soal' => $soal,
                // 'prodi' => $prodi,
                'aktivitas' => $aktivitas,
                'ujianSayaDataTable' => $ujianSayaDataTable->htmlTable(),
                'monitoringUjianDataTable' => $monitoringUjianDataTable->htmlTable(),

            ]);
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    //Gets Monitoring Ujian
    public function getMonitoringUjian(MonitoringUjianDataTable $monitoringUjianDataTable)
    {
        return $monitoringUjianDataTable->render('dashboard');
    }

    //Gets Ujian MHS

    public function getUjianSaya(UjianSayaDataTable $ujianSayaDataTable)
    {
        return $ujianSayaDataTable->render('dashboard');
    }

    public function refreshToken()
    {
        $session = session('_keycloak_token');

        if (!$session || !isset($session['refresh_token'])) {
            return false;
        }

        $client_id = env('KEYCLOAK_CLIENT_ID');
        $client_secret = env('KEYCLOAK_CLIENT_SECRET');
        $refresh_token = $session['refresh_token'];

        try {
            $response = Http::asForm()
                ->timeout(5) // biar gak ngegantung lama
                ->withoutVerifying() // setara dengan verify => false di Guzzle
                ->post(env('KEYCLOAK_BASE_URL') . '/realms/' . env('KEYCLOAK_REALM') . '/protocol/openid-connect/token', [
                    'grant_type' => 'refresh_token',
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'refresh_token' => $refresh_token,
                ]);

            if ($response->failed()) {
                \Log::error('Keycloak refresh token failed: ' . $response->body());
                return false;
            }

            $newToken = $response->json();

            // Simpan token baru ke session Laravel
            session(['_keycloak_token' => $newToken]);

            return $newToken;

        } catch (\Exception $e) {
            \Log::error('Keycloak refresh token exception: ' . $e->getMessage());
            return false;
        }
    }
}
