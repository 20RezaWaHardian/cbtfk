<?php

use Pusher\Pusher;
use App\Events\StreamOffer;
use App\Events\VideoStream;
use App\Events\IceCandidate;
use App\Events\ReceiveOffer;
use App\Events\StreamAnswer;
use Illuminate\Http\Request;
use App\Events\ReceiveAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UjianController;
use App\Http\Controllers\SoalIBAController;
use App\Http\Controllers\GrupSoalController;
use App\Http\Controllers\LoadDataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KuesionerController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\HomeInertiaController;
use App\Http\Controllers\PrepareExamController;
use App\Http\Controllers\SoalPerblokController;
use App\Http\Controllers\VideoStreamController;
use App\Http\Controllers\UjianInertiaController;
use App\Http\Controllers\BankSoal\SoalController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\MonitoringUjianController;
use App\Http\Controllers\KuesionerPesertaController;
use App\Http\Controllers\SiakadBlok\KelasController;
use App\Http\Controllers\SoalProgressTestController;
use App\Http\Controllers\Ujian\DaftarUjianController;
use App\Http\Controllers\BankSoal\PaketSoalController;
use App\Http\Controllers\InputPertanyaanKueController;
use App\Http\Controllers\Ujian\PesertaUjianController;
use App\Http\Controllers\Ujian\RiwayatUjianController;
use App\Http\Controllers\InputKategoriSoalKueController;
use App\Http\Controllers\BankSoal\AnalisisSoalController;
use App\Http\Controllers\BankSoal\KategoriSoalController;
use App\Http\Controllers\BankSoal\SubKategoriSoalController;
use App\Http\Controllers\DaftarKuesionerController;
use App\Http\Controllers\UjianOsceController;
use App\Http\Controllers\OsceAntrianController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JenisOsceController;
use App\Http\Controllers\KomponenNilaiOsceController;
use App\Http\Controllers\JadwalOsceController;
use App\Http\Controllers\SummernoteController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Models\Ujian;
use App\Models\PaketSoal;
use App\Models\PaketHasSoal;
use App\Models\Soal;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {


    return view('welcome');
});

Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});
Route::controller(HomeInertiaController::class)->group(function () {
    Route::get('/lembar-jwb', 'index')->name('index');

    // Route::get('/', 'index')->name('index');

});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login-eksternal','halamanLoginEksternal')->name('loginEksternal');
    Route::post('/login-post', 'loginPost')->middleware('throttle:5,1')->name('loginPost');
    Route::post('/logout-manual', [AuthController::class, 'logoutManual'])
            ->name('logout.manual');
});

Route::middleware(['auth:web', 'verified'])->group(function () {

    Route::controller(KuesionerPesertaController::class)->group(function () {
        Route::get('/kuesioner-sebelum/{id_peserta_ujian}/{id_ujian}', 'kuesionerSebelum')->name('peserta.kuesionerSebelum');
        Route::get('/kuesioner/{id_peserta_ujian}/participant/{id_ujian}/kuesionerku/{id_kuesioner}', 'kuesionerKu')->name('peserta.kuesioner');
        Route::post('/kuesioner/{id_peserta_ujian}/participant/{id_ujian}/kuesionerku/{id_kuesioner}/store', 'kuesionerStore')->name('peserta.kuesionerStore');
        Route::post('/kuesioner/{id_peserta_ujian}/participant/{id_ujian}/{id_kuesioner}/storeKue/sebelum', 'kuesionerStoreSebelum')->name('peserta.kuesionerStoreSebelum');
    });
    Route::controller(PrepareExamController::class)->group(function () {
        Route::get('/pre-exam24/token/{id_peserta_ujian}/{id_ujian}/', 'token')->name('peserta.token');
        Route::post('/pre-exam24/token/cek-token','cekToken')->name('peserta.cekToken');
        Route::get('/pre-exam24/face-register/{id_peserta_ujian}/participant/{id_ujian}/', 'faceRegister')->name('peserta.faceRegister');
        Route::post('/pre-exam24/participant/store-capture/', 'storeGambar')->name('peserta.storeGambar');
        Route::get('/pre-exam24/agreement/{id_peserta_ujian}/participant/{id_ujian}/', 'agreement')->name('peserta.agreement');
        Route::get('/pre-exam24/confirm/{id_ujian}/{id_peserta_ujian}/', 'confirm')->name('peserta.confirm');
        Route::post('/pre-exam24/agreement/participant/', 'agreementPost')->name('peserta.agreementPost');
    });

    Route::controller(UjianController::class)->group(function () {
        Route::get('/exam24/{id_ujian}/participant/{id_peserta_ujian}', 'index')->name('peserta.startUjian');
        Route::get('/soal/{id_soal}/{id_peserta_ujian}/{id_ujian}', 'soalId');
        Route::get('/pagination/fetch_data', 'fetch_data');
        Route::get('/pagination/fetch_data/{id_soal}', 'fetch_data_ajax');
        Route::get('/jawab/soal/pilgan', 'pilganJawab');
        Route::post('/jawab/ragu', 'ragu');

        Route::get('/jawab/soal/essay', 'essayJawab');
        Route::post('/jawab/ragu/essay', 'raguEssay');


        Route::get('/exam24/finish/{id_ujian}/participant/{id_peserta_ujian}', 'akhiriUjian')->name('peserta.finishUjian');
        Route::get('/exam24/finish/{id_ujian}/participant/{id_peserta_ujian}/time-out', 'akhiriUjianTimeOut')->name('peserta.finishUjianTimeOut');
        Route::get('/exam24/finish/{id_ujian}/participant/{id_peserta_ujian}/exit-fullscreen', 'akhiriUjianExitFullScreen')->name('peserta.finishUjianExitFullScreen');

        Route::get('/latency-peserta', function () {
            return response()->json(['status' => 'ok']);
        });

        Route::post('/save-latency-peserta', function (Request $request) {
            $latency = $request->input('latency');
            $id_peserta = Crypt::decrypt($request->input('peserta_id'));

            DB::table('peserta_ujian')
                ->where('id_peserta_ujian', $id_peserta)
                ->update([
                    'latency' => $latency,
                    'latency_updated' => now(),
                ]);

            return response()->json(['message' => 'Latency saved successfully.']);
        });

        Route::post('/store-sisa-waktu', 'storeSisaWaktu')->name('storeSisaWaktu');
    });


    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::post('/refresh-token', 'refreshToken')->name('refreshToken');

        // Route::get('/dashboard-ajax', 'dashboardAjax')->name('dashboard.ajax');
        Route::get('/dashboard/load-datatable-monitoring-ujian', 'getMonitoringUjian')->name('load-datatable-monitoring-ujian');
        Route::get('/dashboard/load-datatable-ujian-saya', 'getUjianSaya')->name('load-datatable-ujian-saya');
    });
    //Konfigurasi
    Route::prefix('konfigurasi')->group(function () {
        Route::controller(RoleController::class)->group(function () {
            Route::get('/roles', 'index');
            Route::get('/roles/create', 'create');
            Route::post('/roles/{id}/update', 'update')->name('roles.update');
            Route::get('/roles/{id}/edit', 'edit');
            Route::post('/roles/store', 'store')->name('roles.store');
            Route::delete('/roles/{id}/delete', 'destroy');

            //Set Permission
            Route::get('/roles/{id}/set-permission', 'setPermission');
            Route::post('/roles/{id}/permission-syc', 'sycPermission')->name('permisssion.sync');
        });

        /*Batasn Route Role dan Permission*/

        Route::controller(PermissionController::class)->group(function () {
            Route::get('/permissions', 'index');
            Route::get('/permissions/create', 'create');
            Route::post('/permissions/{id}/update', 'update')->name('permissions.update');
            Route::get('/permissions/{id}/edit', 'edit');
            Route::post('/permissions/store', 'store')->name('permissions.store');
            Route::delete('/permissions/{id}/delete', 'destroy');
        });

        /*Batasn Route Permission dan User*/

        Route::controller(UserController::class)->group(function () {
            Route::get('/users', 'index');
            Route::get('/users/create', 'create');
            Route::post('/users/{id}/login', 'loginAs')->name('users.login-as');
            Route::post('/users/logout-as', 'logoutAs')->name('logout-as');
            Route::post('/users/{id}/update', 'update')->name('users.update');
            Route::get('/users/{id}/edit', 'edit');
            Route::post('/users/store', 'store')->name('users.store');
            Route::delete('/users/{id}/delete', 'destroy');
        });


        Route::controller(NavigationController::class)->group(function () {
            Route::get('/menus', 'index');
            Route::get('/menus/create', 'create');
            Route::post('/menus/{id}/update', 'update')->name('menus.update');
            Route::get('/menus/{id}/edit', 'edit');
            Route::post('/menus/store', 'store')->name('menus.store');
            Route::delete('/menus/{id}/delete', 'destroy');
        });
    });




    Route::controller(KelasController::class)->prefix('kelas')->name('kelas.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id_kelas}/get-blocks/{id_semester}', 'getBlockByKelasSemester');
        Route::get('/get-blocks/detail/{id_block}', 'getBlockDetail')->name('getBlockDetail');
        Route::get('/get-blocks/mahasiswa/{id_block}', 'getMahasiswaKelompokBelajar')->name('getMahasiswaKelompokBelajar');
        Route::get('/get-blocks/materi/{id_blok}/{id_jenis_blok}', 'getMateriKelompokBelajar')->name('getMateriKelompokBelajar');
    });

    Route::controller(LoadDataController::class)->group(function () {
        Route::get('/get-soal/{id_kategori_soal}/kategori-soal/{id_paket_soal}', 'getSoalByKategoriIdPaketSoalId');
        Route::get('/get-soal/sub-kategori/{id_sub_kategori_soal}/paket-soal/{id_paket_soal}', 'getSoalBySubKategoriIdPaketSoalId');
        Route::get('/get-blok/{id_prodi}/semester/{id_semester}', 'getBlokByIdProdiIdSemester');
        Route::get('/get-kelompok-belajar/{id_blok}', 'getKelompokBelajarByIdBlok');
        // Route::get('/get-paket-soal/{id_prodi}', 'getPaketSoalByIdProdi');
        Route::get('/get-paket-soal', 'getPaketSoal');
        Route::get('/get-pegawai', 'getPegawai');
        Route::get('/get-mahasiswa', 'getMahasiswa');
    });


    Route::prefix('bank-soal')->name('bank-soal.')->group(function () {
        //ANALISIS SOAL
        // Route::prefix('analisis-soal')->name('analisis-soal.')->group(function () {
        //     Route::controller(AnalisisSoalController::class)->group(function () {
        //         Route::get('/', 'index')->name('index');
        //     });
        // });
        //GRUP SOAL
        // Route::prefix('grup-soal')->name('grup-soal.')->group(function () {
        //     Route::controller(GrupSoalController::class)->group(function () {
        //         Route::get('/', 'index')->name('index');
        //     });
        //     //bank-soal/grup-soal/perblok
        //     //bank-soal.grup-soal.perblok
        //     Route::prefix('/perblok')->name('perblok.')->group(function () {
        //         Route::controller(SoalPerblokController::class)->group(function () {
        //             Route::get('/', 'index')->name('index');
        //         });

        //     });
        //      //bank-soal/grup-soal/iba
        //     //bank-soal.grup-soal.iba
        //     Route::prefix('/iba')->name('iba.')->group(function () {
        //         Route::controller(SoalIBAController::class)->group(function () {
        //             Route::get('/', 'index')->name('index');
        //         });

        //     });
        //     //bank-soal/grup-soal/progress-test
        //     //bank-soal.grup-soal.progress-test
        //     Route::prefix('/progress-test')->name('progress-test.')->group(function () {
        //         Route::controller(SoalProgressTestController::class)->group(function () {
        //             Route::get('/', 'index')->name('index');
        //         });

        //     });

        // });

        //SOAL DAN KATEGORI SOAL
        Route::prefix('soal')->name('soal.')->group(function () {
            Route::controller(KategoriSoalController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-blok/kelas', 'getBlok');
                Route::get('/create', 'create')->name('create');
                Route::post('/{id}/update', 'update')->name('update');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::post('/store', 'store')->name('store');
                Route::delete('/{id}/delete', 'destroy')->name('destroy');
            });

            Route::prefix('sub-kategori')->name('sub-kategori.')->controller(SubKategoriSoalController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::post('/{id_sub_kategori}/update', 'update')->name('update');
                Route::delete('/{id_sub_kategori}/delete', 'destroy')->name('destroy');
            });

            //Detail KategoriS Berisi Daftar Soal Pada Kategori Tsb
            Route::controller(SoalController::class)->group(function () {
                Route::get('/{id}/kategori-soal', 'showSoalByKategori')->name('showSoalByKategori');
                Route::get('/{id}/kategori-soal/jenis-soal/create', 'createJenisSoal')->name('createJenisSoal');
                Route::get('/{id}/kategori-soal/jenis-soal/excel-soal', 'excelJenisSoal')->name('excelJenisSoal');
                Route::post('/{id}/kategori-soal/jenis-soal/store', 'storeJenisSoal')->name('storeJenisSoal');
                Route::post('/{id}/kategori-soal/jenis-soal/store-excel', 'storeExcelJenisSoal')->name('storeExcelJenisSoal');
                Route::get('/{id}/kategori-soal/jenis-soal/edit', 'editJenisSoal')->name('editJenisSoal');
                Route::post('/{id}/kategori-soal/jenis-soal/update', 'updateJenisSoal')->name('updateJenisSoal');
                Route::delete('/{id}/kategori-soal/jenis-soal/delete', 'destroyJenisSoal')->name('deleteJenisSoal');

                //Get Soal Berdasarkan ID URL Khusus
                Route::get('/get-soal/{id}/kategori-soal', 'getSoalByKategori')->name('getSoalByKategori');

                //Soal Pilgan
                Route::get('/{id}/pilgan/create', 'createSoalPilgan')->name('createSoalPilgan');
                Route::post('/{id}/pilgan/store', 'storeSoalPilgan')->name('storeSoalPilgan');
                Route::get('/{id}/pilgan/edit', 'editSoalPilgan')->name('editSoalPilgan');
                Route::post('/{id}/pilgan/update', 'updateSoalPilgan')->name('updateSoalPilgan');
                Route::delete('/{id}/pilgan/delete', 'destroySoalPilgan')->name('deleteSoalPilgan');


                //Soal Essay
                Route::get('/{id}/essay/create', 'createSoalEssay')->name('createSoalEssay');
                Route::post('/{id}/essay/store', 'storeSoalEssay')->name('storeSoalEssay');
                Route::get('/{id}/essay/edit', 'editSoalEssay')->name('editSoalEssay');
                Route::post('/{id}/essay/update', 'updateSoalEssay')->name('updateSoalEssay');
                Route::delete('/{id}/essay/delete', 'destroySoalEssay')->name('deleteSoalEssay');


                //Daftar Soal
                Route::get('/daftar-soal', 'daftarSoal')->name('daftarSoal');
                Route::get('/daftar-soal/create', 'createJenisSoalWithoutKategori')->name('createJenisSoalWithoutKategori');
                Route::post('/daftar-soal/store', 'storeJenisSoalWithoutKategori')->name('storeJenisSoalWithoutKategori');

                //Daftar Soal Belum Divalidasi
                Route::get('/daftar-soal/belum-divalidasi', 'daftarSoalBelumDivalidasi')->name('daftarSoalBelumDivalidasi');
                // Route::get('/daftar-soal/validasi-soal/{id_soal}', 'validasiSoal')->name('validasiSoal');
                Route::post('/daftar-soal/validasi-soal', 'validasiSoal')->name('validasiSoal');
                Route::post('/daftar-soal/{id}/ajukan-ulang', 'ajukanUlang')->name('ajukanUlang');

                //Soal PilganWithoutKategori
                Route::get('/daftar-soal/{id}/pilgan/create', 'createSoalPilganWithoutKategori')->name('createSoalPilganWithoutKategori');
                Route::post('/daftar-soal/{id}/pilgan/store', 'storeSoalPilganWithoutKategori')->name('storeSoalPilganWithoutKategori');
                Route::get('/daftar-soal/{id}/pilgan/edit', 'editSoalPilganWithoutKategori')->name('editSoalPilganWithoutKategori');
                Route::post('/daftar-soal/{id}/pilgan/update', 'updateSoalPilganWithoutKategori')->name('updateSoalPilganWithoutKategori');

                //Soal EssayWithoutKategori
                Route::get('/daftar-soal/{id}/essay/create', 'createSoalEssayWithoutKategori')->name('createSoalEssayWithoutKategori');
                Route::post('/daftar-soal/{id}/essay/store', 'storeSoalEssayWithoutKategori')->name('storeSoalEssayWithoutKategori');
                Route::get('/daftar-soal/{id}/essay/edit', 'editSoalEssayWithoutKategori')->name('editSoalEssayWithoutKategori');
                Route::post('/daftar-soal/{id}/essay/update', 'updateSoalEssayWithoutKategori')->name('updateSoalEssayWithoutKategori');
            });
        });

        // PAKET SOAL
        Route::prefix('paket-soal')->name('paket-soal.')->group(function () {
            Route::controller(PaketSoalController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/{id}/update', 'update')->name('update');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::post('/store', 'store')->name('store');
                Route::delete('/{id}/delete', 'destroy')->name('destroy');
                Route::get('/{id}/update-status/{is_active}', 'updateStatus')->name('updateStatus');


                //
                Route::get('/{id}/soal', 'showSoalByPaketSoal')->name('showSoalByPaketSoal');
                Route::get('/{id}/validasi-poin', 'validasiPoin')->name('validasiPoin');
                Route::get('/{id}/piih-soal/form', 'pilihSoal')->name('pilihSoal');
                Route::post('/piih-soal/form/store', 'storeSoal')->name('storeSoal');
                Route::delete('/{id_paket_soal}/soal/{id_soal}/delete', 'destroySoal')->name('deleteSoal');

                //Import Soal
                Route::get('/download/import-soal/format', 'downloadFormatSoal')->name('downloadFormatSoal');
                Route::post('/{id}/import-soal/store', 'importSoal')->name('importSoal');

                //Analisis Soal
                Route::get('/analisis-soal/{id_paket_soal}', 'analisisSoal')->name("analisisSoal");
            });
        });
    });

    Route::prefix('ujian')->name('ujian.')->group(function () {
        Route::controller(DaftarUjianController::class)->prefix('daftar-ujian')->name('daftar-ujian.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/{id}/update', 'update')->name('update');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::post('/store', 'store')->name('store');
            Route::post('/{id}/delete', 'destroy')->name('destroy');

            //Update Status Ujian
            Route::post('/update/status-ujian', 'updateStatusUjian');
            Route::get('/mulai/{id_ujian}', 'mulaiUjian')->name('mulai');
            Route::get('/akhiri/{id_ujian}', 'akhiriUjian')->name('akhiri');


            Route::get('/analisis-soal/{id_ujian}', 'analisisSoal')->name('analisisSoal');
        });
        Route::controller(PesertaUjianController::class)->prefix('peserta')->name('peserta.')->group(function () {
            Route::get('/{id_ujian}/kelompok-belajar/{id_kelompok_belajar}', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/{id}/update', 'update')->name('update');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::post('/store', 'store')->name('store');
            Route::post('/delete', 'destroy')->name('destroy');
            Route::get('/sync/{id_ujian}', 'syncNilai');
            Route::get('/template-import-pmb', 'downloadTemplateImportPmb')->name('templateImportPmb');
            Route::post('/{id_ujian}/import-pmb', 'importPesertaPmb')->name('importPmb');

            //koreksi
            Route::get('/{id_peserta_ujian}/koreksi', 'koreksi')->name('koreksi');
            Route::patch('/essay_jawab/score/update', 'updateScoreEssay')->name('updateScoreEssay');
        });

        Route::controller(RiwayatUjianController::class)->prefix('riwayat-ujian')->name('riwayat-ujian.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/resume/{id_ujian}', 'resume')->name('resume');
            Route::get('/analisis/soal/{id_ujian}', 'analisisSoal')->name('analisisSoal');
            Route::get('/analisis/soal/{id_ujian}/pdf', 'exportAnalisisSoalPdf')->name('analisisSoalPdf');
            Route::get('/analisis/soal/{id_ujian}/excel', 'exportAnalisisSoalExcel')->name('analisisSoalExcel');
        });

        Route::controller(MonitoringUjianController::class)->prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/ujian/{id_ujian}', 'index')->name('index');
            Route::get('/mulai-ujian/{id_ujian}', 'mulaiPesertAll')->name('mulaiPesertAll');
        });
    });
    Route::controller(MonitoringUjianController::class)->prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/ujian', 'indexUtama')->name('indexUtama');
        Route::get('/setToken/{id_ujian}','settingToken')->name('setToken');
        Route::get('/ujian/log-aktivitas/{id_peserta_ujian}', 'logAktivitas')->name('logAktivitas');
        Route::get('/ujian/berita-acara/{id_peserta_ujian}', 'beritaAcara')->name('beritaAcara');
        Route::get('/ujian/berita-acara/update-status-peserta/{id_peserta_ujian}', 'updateStatusPeserta')->name('updateStatusPeserta');
        Route::patch('/ujian/berita-acara/edit/{id_peserta_ujian}', 'updateBeritaAcara')->name('updateBeritaAcara');
        Route::get('/ujian/berita-acara/download/{id_ujian}', 'downloadBeritaAcara')->name('downloadBeritaAcara');
    });

    Route::post('/start-stream', [VideoStreamController::class, 'startStream']);
    Route::post('/broadcasting/auth', [VideoStreamController::class, 'authenticate']);

    //Kuesioner
    Route::prefix('kuesioner')->name('kuesioner.')->group(function () {
        Route::controller(KuesionerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id_kuesioner}/edit', 'edit')->name('edit');
            Route::put('/{id_kuesioner}/update', 'update')->name('update');
            Route::delete('/{id_kuesioner}/destroy', 'destroy')->name('destroy');
        });
        // Route::prefix('/kategori-kue')->name('kategori-kue.')->group(function () {
        Route::controller(InputKategoriSoalKueController::class)->group(function () {
            Route::get('/kategori-kue/{id_kuesioner}', 'index')->name('kategori-kue.index');
            Route::get('/buat-kategori-kue', 'buatKategori')->name('buatKategori');
            Route::post('/store-kategori-kue', 'store')->name('store-kategori-kue');
            Route::get('/{id_kategori_kuesioner}/edit/kategori-kue', 'edit')->name('edit');
            Route::put('/{id_kategori_kuesioner}/update/kategori-kue', 'update')->name('update-kategori-kue');
            Route::delete('/{id_kategori_kuesioner}/destroy-kue', 'destroy');
        });

        Route::controller(InputPertanyaanKueController::class)->group(function () {
            Route::get('/template-pertanyaan-kue', 'templateImport')->name('pertanyaan-kue.template');
            Route::post('/pertanyaan-kue/{id_kategori_kuesioner}/import', 'import')->name('pertanyaan-kue.import');
            Route::get('/pertanyaan-kue/{id_kategori_kuesioner}', 'index')->name('pertanyaan-kue.index');
            Route::get('/buat-pertanyaan-kue', 'buatPertanyaan')->name('buatPertanyaan');
            Route::post('/store-pertanyaan-kue', 'store')->name('store-pertanyaan-kue');
            Route::get('/{id_pertanyaan_kuesioner}/edit/pertanyaan-kue', 'edit')->name('edit-pertanyaan-kue');
            Route::put('/{id_pertanyaan_kuesioner}/update/pertanyaan-kue', 'update')->name('update-pertanyaan-kue');
            Route::delete('/pertanyaan/{id_pertanyaan_kuesioner}/destroy-pertanyaan-kue', 'destroy');
        });

        // });
    });

    Route::prefix('daftar-kuesioner')
        ->controller(DaftarKuesionerController::class)->group(function () {
            Route::get('/', 'index')->name('daftar-kuesioner');
            Route::get('/{id_kuesioner}','daftarUjian')->name('daftar-ujian');
            Route::get('/{id_ujian}/detail', 'detail')->name('detail-kuesioner');
            Route::get('/{id_ujian}/detail/sebelum', 'detailKueSebelum')->name('detailKueSebelum');
            Route::get('/download-kuesioner/{id_kuesioner}', 'downloadKuesioner')->name('downloadKuesioner');
        });

    //Export
    Route::prefix('export')->name('export.')
        ->controller(ExportController::class)->group(function () {
            Route::get('/daftar-ujian/{id_ujian}', 'exportPdfNilai')->name('exportPdfNilai');
        });

    //data Master
    Route::prefix('data-master/')->name('data-master.')->group(function () {
        Route::resource('jenis-osce', JenisOsceController::class);
        // Route::get('/komponen-nilai-osce',[JenisOsceController::class,'komponenNilai'])->name('komponenNilai');
        // Route::post('/komponen-nilai-osce/simpan',[JenisOsceController::class,'simpanKomponenNilai'])->name('simpanKomponenNilai');

        // Route::resource('komponen-nilai-osce',KomponenNilaiOsceController::class);
        Route::controller(KomponenNilaiOsceController::class)->group(function () {
            Route::get('/komponen-nilai-osce/{id_jenis_osce}', 'index')->name('komponen-nilai-osce.index');
            Route::get('/komponen-nilai-osce/create/{id_jenis_osce}', [KomponenNilaiOsceController::class, 'create'])->name('komponen-nilai-osce.create');
            Route::post('/komponen-nilai-osce/store', [KomponenNilaiOsceController::class, 'store'])->name('komponen-nilai-osce.store');
            Route::get('/komponen-nilai-osce/{id_komponen_nilai_osce}/edit/{id_jenis_osce}', [KomponenNilaiOsceController::class, 'editForm'])->name('komponen-nilai-osce.edit');
            Route::put('/komponen-nilai-osce/{id_komponen_nilai_osce}/update', [KomponenNilaiOsceController::class, 'update'])->name('komponen-nilai-osce.update');
            Route::delete('/komponen-nilai-osce/{id_komponen_nilai_osce}', [KomponenNilaiOsceController::class, 'destroy'])->name('komponen-nilai-osce.destroy');

            Route::get('/komponen-nilai-osce/{id}/instrumen', 'instrumen')->name('komponen-nilai-osce.instrumen');
            Route::delete('/komponen-nilai-osce/instrumen/{id}', 'destroyInstrumen')->name('komponen-nilai-osce.destroyInstrumen');

            //InstrumenNilaiOsce
            Route::get('/komponen-nilai-osce/create/{id_komponen_nilai_osce}/instrumen-nilai', [KomponenNilaiOsceController::class, 'formInstrumen'])
                ->name('komponen-nilai-osce.formInstrumen');
            Route::post('/komponen-nilai-osce/simpan/instrumen-nilai', [KomponenNilaiOsceController::class, 'storeInstrumen'])
                ->name('komponen-nilai-osce.storeInstrumen');
            Route::get('/komponen-nilai-osce/edit/instrumen-nilai/{id}', [KomponenNilaiOsceController::class, 'editInstrumen'])
                ->name('komponen-nilai-osce.editInstrumen');
            Route::put('/komponen-nilai-osce/update/{id}/instrumen-nilai', [KomponenNilaiOsceController::class, 'updateInstrumen'])
                ->name('komponen-nilai-osce.updateInstrumen');
        });

        //Jadwal OSCE
        Route::resource('jadwal-osce', JadwalOsceController::class);
        Route::get('/tambah/stase/jadwal-osce/{id_jadwal_osce}', [JadwalOsceController::class, 'tambahStase'])->name('tambahStase');
        Route::post('/simpan-stase/jadwal-osce/{id_jadwal_osce}', [JadwalOsceController::class, 'simpanStase'])->name('simpanStase');
        Route::delete('/hapus/jadwal-stase/{id_jadwal_has_stase}', [JadwalOsceController::class, 'hapusStase'])->name('hapusStase');
        Route::get('/peserta-jadwal-osce/{id_jadwal_osce}', [JadwalOsceController::class, 'pesertaOsce'])->name('pesertaOsce');
        Route::get('/tambah/peserta-jadwal-osce/{id_jadwal_osce}', [JadwalOsceController::class, 'tambahPesertaOsce'])->name('tambahPesertaOsce');
        Route::post('/simpan/peserta-jadwal-osce/', [JadwalOsceController::class, 'storePesertaOsce'])->name('storePesertaOsce');
        Route::delete('/hapus_peserta/{id_jadwal_has_mhs}',[JadwalOsceController::class, 'hapusPeserta'])->name('hapusPeserta');

        //Ujian OSCE
        Route::get('/ujian-osce', [UjianOsceController::class, 'index'])->name('ujian-osce');
        Route::get('/ujian-osce/{id_jadwal_osce}/stase/{id_jenis_osce}', [UjianOsceController::class, 'detailMahasiswa'])->name('detailMahasiswa');
        Route::get('/beri-nilai/{id_mhs_pt}/{id_jadwal_osce}/{id_jenis_osce}', [UjianOsceController::class, 'beriNilai'])->name('beriNilai');
        Route::post('/simpanNilaiStase', [UjianOsceController::class, 'simpanNilaiStase'])->name('simpanNilaiStase');
    });

    Route::prefix('osce-antrian')->name('osce-antrian.')->controller(OsceAntrianController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/jadwal/{id_jadwal_osce}/station', 'station')->name('station');
        Route::get('/jadwal/{id_jadwal_osce}/station/{id_jenis_osce}/peserta', 'peserta')->name('peserta');
        Route::get('/nilai/{id_peserta_station_osce}', 'nilai')->name('nilai');
        Route::post('/nilai/simpan-komponen', 'simpanNilaiKomponen')->name('simpan-nilai-komponen');
        Route::post('/nilai/simpan', 'simpanNilai')->name('simpan-nilai');
    });

    Route::post('/summernote/upload', [SummernoteController::class, 'upload'])
            ->name('summernote.upload');
    Route::post('/summernote/delete', [SummernoteController::class, 'delete'])
            ->name('summernote.delete');
});





require __DIR__ . '/auth.php';
