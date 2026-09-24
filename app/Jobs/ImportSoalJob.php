<?php

namespace App\Jobs;

use App\Imports\SoalImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportSoalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $id_paket_soal;
    protected $kategori_soal_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file, $id_paket_soal,$kategori_soal_id)
    {
        $this->file = $file;
        $this->id_paket_soal = $id_paket_soal;
        $this->kategori_soal_id = $kategori_soal_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Excel::import(new SoalImport($this->id_paket_soal, $this->kategori_soal_id), $this->file);
    }
}

