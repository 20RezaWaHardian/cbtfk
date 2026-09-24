<?php

namespace App\DataTables;

use App\Models\Ujian;
use App\Models\RiwayatUjian;
use App\Helpers\MyHelpers;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use DB;

class RiwayatUjianDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('status', function ($row) {
                switch ($row->status) {
                    case 1:
                        return '<span class="badge text-bg-info">Sedang Berlangsung</span>';
                    case 2:
                        return '<span class="badge text-bg-danger">Berakhir</span>';
                    default:
                        return '<span class="badge text-bg-secondary">Belum Dimulai</span>';
                }
            })
            ->editColumn('peserta', function ($row) {


                    return  $row->peserta;

            })
            ->addColumn('pangawas_ujian_oke', function ($row) {
                if($row->pengawas->isNotEmpty())
                {
                    $p = '<ol>';
                        foreach ($row->pengawas as $value) {
                            $p .= '<li>'.MyHelpers::nama_gelarById($value->id_pegawai).'</li>';
                        }
                        
                    $p .= '</ol>';
                    return $p;

                }else{
                    return '<span class="badge text-bg-danger">Belum Ada Pengawas</span>';
                }
                
            })
            ->editColumn('action', function ($row) {
                $action = '';
                $action .= '<div class="btn-group">
                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static"  aria-expanded="false">
                                    Aksi
                                </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="max-height: none; overflow: visible; z-index: 1055;">';
                    if(Gate::allows('export ujian/riwayat-ujian'))
                    {
                        $action .= '<li><a class="dropdown-item" href="' . route('export.exportPdfNilai',  ['id_ujian' => $row->id_ujian]) . '">Export PDF</a></li>';
                    }
                    
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.peserta.index',  ['id_ujian' => $row->id_ujian, 'id_kelompok_belajar' => 0]) . '">Lihat Peserta</a></li>';
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.daftar-ujian.edit', $row->id_ujian) . '">Edit</a></li>';
                    $action .= '<li><button type="button" data-id=' . $row->id_ujian . ' data-jenis="delete" class="dropdown-item action">Hapus</button></li>';

                    // $action .= '<li><a class="dropdown-item" href="' . route('ujian.daftar-ujian.analisisSoal', encrypt($row->id_ujian)) . '">Analisis Soal</a></li>';
                    // $action .= '<li><a class="dropdown-item" href="' . route('bank-soal.paket-soal.analisisSoal', ['id_paket_soal' => encrypt($row->paket_soal_id)]) . '">Analisis Soal</a></li>';
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.riwayat-ujian.analisisSoal', ['id_ujian' => encrypt($row->id_ujian)]) . '">Analisis Soal</a></li>';
                    // $action .= '<a href="' . route('bank-soal.paket-soal.analisisSoal', ['id_paket_soal' => encrypt($row->id_paket_soal)]) . '"  class="btn btn-secondary btn-sm action"><i class="fa fa-line-chart" aria-hidden="true"></i></a>';
                $action .= '</ul>
                            </div>';

                return $action;
            })

            ->rawColumns(['peserta', 'status','action','pangawas_ujian_oke'])
            ->addIndexColumn()
            ->setRowId('id_ujian');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Ujian $model): QueryBuilder
    {
        
        if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
            $data =  $model->withCount('peserta_ujian as peserta')
                        ->where('status', 2);
                        // ->where('tanggal_ujian','like','%2026-02-08%')
                        // ->get();
            return $data;
            // dd($data);
        }else if(auth()->user()->hasAnyRole(['koordinator-blok']))
        {
            $idPegawaiLogin = auth()->user()->pegawai->pegawai_siakad_id;
            $semester = DB::table('siakad.semester')->where('periode_aktif',1)->first();

            $blokIds = DB::table('siakad_blok.koordinator_blok')
                ->where('id_semester',$semester->id_semester)
                ->where('id_pegawai_koor', $idPegawaiLogin)
                ->orWhere('id_pegawai_ass', $idPegawaiLogin)
                ->pluck('id_kelas');

            // ambil pegawai lain baik dari kolom koor atau ass
            $pegawaiLain = DB::table('siakad_blok.koordinator_blok')
                ->whereIn('id_kelas', $blokIds)
                ->where('id_semester',$semester->id_semester)
                ->where(function ($query) use ($idPegawaiLogin) {
                    $query->where('id_pegawai_koor', '!=', $idPegawaiLogin)
                          ->orWhere('id_pegawai_ass', '!=', $idPegawaiLogin);
                })
                ->selectRaw('id_pegawai_koor as id_pegawai')
                ->union(
                    DB::table('siakad_blok.koordinator_blok')
                        ->where('id_semester',$semester->id_semester)
                        ->whereIn('id_kelas', $blokIds)
                        ->where(function ($query) use ($idPegawaiLogin) {
                            $query->where('id_pegawai_koor', '!=', $idPegawaiLogin)
                                  ->orWhere('id_pegawai_ass', '!=', $idPegawaiLogin);
                        })
                        ->selectRaw('id_pegawai_ass as id_pegawai')
                )
                ->distinct()
                ->pluck('id_pegawai');
            $pegawaiLain = $pegawaiLain->reject(function ($id) use ($idPegawaiLogin) {
                return $id == $idPegawaiLogin; // buang yg sama dengan id login
            })->values();
            // dd();
            $pegawai = DB::table('kepeg.pegawai')->where('pegawai_siakad_id',$pegawaiLain->first())->first();

            return $model->whereHas('pengawas',function($q)use($pegawai){
                $q->whereIn('ujian_has_pengawas.id_pegawai',[$pegawai->id_pegawai,auth()->user()->pegawai->id_pegawai]);
            })->withCount('peserta_ujian as peserta')->where('status',2);


            
        }
        else{
            return $model->withCount('peserta_ujian as peserta')->where('pembuat_ujian_id',auth()->user()->id_asal)->where('status',2);
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('riwayatujian-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->searchable(false)->orderable(false)->title('No')->width(2),
            Column::make('tanggal_ujian')->width(100)->searchable(true)->orderable(true),
            Column::make('selesai_ujian')->width(100)->searchable(true)->orderable(true),
            Column::make('nama_ujian')->searchable(true)->orderable(true),
            Column::make('pangawas_ujian_oke')->searchable(false)->orderable(false)->title('Pengawas'),
            Column::make('status')->searchable(false)->orderable(false),
            Column::computed('peserta')->title('Jumlah Peserta')->searchable(false)->orderable(false)->addClass('text-center')->width(5),
            Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(60)
            ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'RiwayatUjian_' . date('YmdHis');
    }
}
