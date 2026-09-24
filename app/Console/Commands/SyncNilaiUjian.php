<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ujian;
use App\Models\PilganJawab;
use App\Models\PaketHasSoal;
use App\Models\Soal;

class SyncNilaiUjian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-nilai-ujian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // public function handle()
    // {
    //     $dt_ujian = Ujian::where('semester_id','20252')->get();
    //     foreach($dt_ujian as $dt)
    //     {
    //         $ujian = Ujian::where('id_ujian', $dt->id_ujian)->first();
    //         $paket = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->select('soal_id', 'poin')->get();
    //         foreach ($paket  as $pk) {
    //             $cekSoalKunci = Soal::where('id_soal', $pk->soal_id)->first();

    //             $cekPilganMhs = PilganJawab::where('soal_id', $pk->soal_id)
    //                 ->whereNull('score')
    //                 ->get();
    //             foreach ($cekPilganMhs as $cmh) {
    //                 if ($cmh->jawab == $cekSoalKunci->kunci) {
    //                     $cmh->update([
    //                         'score' => $pk->poin,
    //                         'status' => 'T'
    //                     ]);
    //                 } else {
    //                     $cmh->update([
    //                         'score' => 0,
    //                         'status' => 'F'
    //                     ]);
    //                 }
    //             }
    //         }
    //     }
        
    // }
    // public function handle()
    // {
    //     $totalBerhasil = 0;
    //     $totalGagal = 0;

    //     $dtUjian = Ujian::where('semester_id', '20252')
    //         ->where('id_ujian','303')
    //         ->get();
    //     foreach ($dtUjian as $ujian) {

    //         try {
    //             $paket = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)->select('soal_id', 'poin')->get();
    //             foreach ($paket  as $pk) {
    //                 $cekSoalKunci = Soal::where('id_soal', $pk->soal_id)->first();

    //                 $cekPilganMhs = PilganJawab::where('soal_id', $pk->soal_id)
    //                     ->where('score',2.5)
    //                     ->where('status','T')
    //                     ->get();
    //                 // dd($cekPilganMhs);
    //                 foreach ($cekPilganMhs as $cmh) {
    //                     if ($cmh->jawab == $cekSoalKunci->kunci) {
    //                         $cmh->update([
    //                             'score' => $pk->poin,
    //                             'status' => 'T'
    //                         ]);
    //                     } else {
    //                         $cmh->update([
    //                             'score' => 0,
    //                             'status' => 'F'
    //                         ]);
    //                     }
    //                 }
    //             }

    //             // tandai ujian sudah selesai sync
    //             $ujian->update([
    //                 'sync' => 1
    //             ]);

    //             $this->info(
    //                 "Ujian {$ujian->id_ujian} berhasil disinkronisasi."
    //             );

    //             $totalBerhasil++;

    //         } catch (\Exception $e) {

    //             $totalGagal++;

    //             \Log::error('Sync Score Error', [
    //                 'id_ujian' => $ujian->id_ujian,
    //                 'error' => $e->getMessage(),
    //                 'line' => $e->getLine()
    //             ]);

    //             $this->error(
    //                 "Ujian {$ujian->id_ujian} gagal diproses. " .
    //                 $e->getMessage()
    //             );
    //         }
    //     }

    //     $this->info('====================================');
    //     $this->info('PROSES SELESAI');
    //     $this->info('Berhasil : ' . $totalBerhasil);
    //     $this->warn('Gagal    : ' . $totalGagal);
    //     $this->info('====================================');
    // }

    public function handle()
    {
        $totalBerhasil = 0;
        $totalGagal = 0;

        $dtUjian = Ujian::with('peserta_ujian')
            ->where('semester_id', '20252')
            ->where('id_ujian', '303')
            ->get();

        $this->info("Jumlah Ujian : " . $dtUjian->count());

        foreach ($dtUjian as $ujian) {

            try {

                $this->newLine();
                $this->info("====================================");
                $this->info("Memproses Ujian : {$ujian->id_ujian}");
                $this->info("Paket Soal      : {$ujian->paket_soal_id}");
                $this->info("Peserta         : " . $ujian->peserta_ujian->count());
                $this->info("====================================");

                $paket = PaketHasSoal::where('paket_soal_id', $ujian->paket_soal_id)
                    ->select('soal_id', 'poin')
                    ->get();

                foreach ($paket as $pk) {

                    $this->newLine();
                    $this->info("Soal ID : {$pk->soal_id}");

                    $cekSoalKunci = Soal::select('id_soal', 'kunci')
                        ->where('id_soal', $pk->soal_id)
                        ->first();

                    if (!$cekSoalKunci) {
                        $this->error("Kunci soal tidak ditemukan.");
                        continue;
                    }

                    $pesertaIds = $ujian->peserta_ujian
                        ->pluck('id_peserta_ujian')
                        ->toArray();

                    $totalJawaban = PilganJawab::where('soal_id', $pk->soal_id)
                        ->whereIn('peserta_ujian_id', $pesertaIds)
                        ->where('score', 1)
                        ->where('status', 'T')
                        ->count();

                    $this->line(
                        "Total jawaban yang akan diproses : {$totalJawaban}"
                    );

                    if ($totalJawaban == 0) {
                        $this->warn("Tidak ada data.");
                        continue;
                    }

                    $bar = $this->output->createProgressBar($totalJawaban);

                    $bar->setFormat(
                        ' %current%/%max% [%bar%] %percent:3s%%'
                    );

                    $bar->start();

                    foreach ($ujian->peserta_ujian as $p) {

                        $cekPilganMhs = PilganJawab::where('soal_id', $pk->soal_id)
                            ->where('peserta_ujian_id', $p->id_peserta_ujian)
                            ->where('score', 1)
                            ->where('status', 'T')
                            ->get();

                        foreach ($cekPilganMhs as $cmh) {

                            try {

                                if ($cmh->jawab == $cekSoalKunci->kunci) {

                                    $cmh->update([
                                        'score' => $pk->poin,
                                        'status' => 'T'
                                    ]);

                                } else {

                                    $cmh->update([
                                        'score' => 0,
                                        'status' => 'F'
                                    ]);
                                }

                            } catch (\Exception $e) {

                                $this->newLine();

                                $this->error(
                                    "Error pada PilganJawab ID {$cmh->id}"
                                );

                                $this->error(
                                    "Peserta ID : {$p->id_peserta_ujian}"
                                );

                                $this->error(
                                    $e->getMessage()
                                );

                                \Log::error('Update PilganJawab Error', [
                                    'pilgan_jawab_id' => $cmh->id,
                                    'peserta_ujian_id' => $p->id_peserta_ujian,
                                    'soal_id' => $pk->soal_id,
                                    'error' => $e->getMessage(),
                                    'line' => $e->getLine(),
                                ]);
                            }

                            $bar->advance();
                        }
                    }

                    $bar->finish();

                    $this->newLine();
                    $this->info("✓ Soal {$pk->soal_id} selesai diproses.");
                }

                $ujian->update([
                    'sync' => 1
                ]);

                $totalBerhasil++;

                $this->newLine();
                $this->info(
                    "✓ Ujian {$ujian->id_ujian} berhasil disinkronisasi."
                );

            } catch (\Exception $e) {

                $totalGagal++;

                \Log::error('Sync Score Error', [
                    'id_ujian' => $ujian->id_ujian,
                    'error' => $e->getMessage(),
                    'line' => $e->getLine()
                ]);

                $this->error(
                    "✗ Ujian {$ujian->id_ujian} gagal diproses."
                );

                $this->error(
                    $e->getMessage()
                );
            }
        }

        $this->newLine();
        $this->info("====================================");
        $this->info("PROSES SELESAI");
        $this->info("Berhasil : {$totalBerhasil}");
        $this->warn("Gagal    : {$totalGagal}");
        $this->info("====================================");
    }
}
