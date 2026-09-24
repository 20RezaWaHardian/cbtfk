<?php

namespace App\DataTables;

use App\Helpers\MyHelpers;
use App\Models\Ujian;
use App\Models\DaftarUjian;
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

class DaftarUjianDataTable extends DataTable
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
                if ($row->pengawas->isNotEmpty()) {
                    $p = '<ol>';
                    foreach ($row->pengawas as $value) {
                        $p .= '<li>' . MyHelpers::nama_gelarById($value->id_dosen) . '</li>';
                    }

                    $p .= '</ol>';
                    return $p;
                } else {
                    return '<span class="badge text-bg-danger">Belum Ada Pengawas</span>';
                }
            })
            // ->addColumn('action', function ($row) {
            //     $action = '';

            //     if (Gate::allows('update ujian/daftar-ujian')) {
            //         $action .= '<a href="' . route('ujian.peserta.index', ['id_ujian' => $row->id_ujian, 'id_kelompok_belajar' => $row->kelompok_belajar_id]) . '"  class="mb-1 btn btn-info btn-sm action">tambah peserta</a>';

            //         if($row->peserta > 0 && $row->status == 0){
            //             // $action .= '<a href="' . route('ujian.daftar-ujian.edit', $row->id_ujian) . '"  class="btn btn-warning btn-sm action">Mulai Ujian</a>';
            //         }
            //         $action .= '<a href="' . route('ujian.daftar-ujian.edit', $row->id_ujian) . '"  class="btn btn-warning btn-sm action"><i class="fas fa-pencil-alt"></i></a>';
            //     }
            //     if (Gate::allows('delete ujian/daftar-ujian')) {
            //         $action .= ' <button type="button" data-id=' . $row->id_ujian . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
            //     }
            //     if (Gate::allows('read ujian/daftar-ujian')) {
            //         $action .= '<a href="' . route('ujian.daftar-ujian.analisisSoal', $row->id_ujian) . '"  class="btn btn-warning btn-sm action">Analisis Soal</a>';
            //     }
            //     return $action;
            // })
            ->editColumn('action', function ($row) {
                $action = '';
                $action .= '<div class="btn-group">
                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static"  aria-expanded="false">
                                    Aksi
                                </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="max-height: none; overflow: visible; z-index: 1055;">';
                // if ($row->status == 0) {
                    // $action .= '<li><a class="dropdown-item" href="' . route('ujian.peserta.index', ['id_ujian' => $row->id_ujian, 'id_kelompok_belajar' => $row->kelompok_belajar_id])  . '">Tambah Peserta</a></li>';
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.peserta.index', ['id_ujian' => $row->id_ujian, 'id_kelompok_belajar' => 0])  . '">Tambah Peserta</a></li>';
                // }

                $action .= '<li><a class="dropdown-item" href="' . route('ujian.daftar-ujian.edit', $row->id_ujian) . '">Edit</a></li>';
                $action .= '<li><button type="button" data-id=' . $row->id_ujian . ' data-jenis="delete" class="dropdown-item action">Hapus</button></li>';

                // $action .= '<li><a class="dropdown-item" href="' . route('ujian.daftar-ujian.analisisSoal', encrypt($row->id_ujian)) . '">Analisis Soal</a></li>';
                $action .= '<li><a class="dropdown-item" href="' . route('bank-soal.paket-soal.analisisSoal', ['id_paket_soal' => encrypt($row->paket_soal_id)]) . '">Analisis Soal</a></li>';
                $action .= '</ul>
                            </div>';

                return $action;
            })
            ->rawColumns(['action', 'peserta', 'status', 'pangawas_ujian_oke'])
            ->addIndexColumn()
            ->setRowId('id_ujian');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Ujian $model): QueryBuilder
    {
        if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
            return $model->withCount('peserta_ujian as peserta')->where('status', '!=', 2);
        }elseif (auth()->user()->hasAnyRole(['koordinator-blok'])) {
            $idPegawaiLogin = auth()->user()->pegawai->pegawai_siakad_id;
            $semester = DB::table('siakad.semester')->where('periode_aktif', 1)->first();

            $co_blok = DB::table('sistembl_siakad-uin.pengelola_blok as a')
                    ->join('sistembl_siakad-uin.dosen as b','a.dosen_id','b.id_dosen')
                    ->where('a.id_dosen', auth()->user()->dosen->id_dosen)
                    ->pluck('a.id_kelas')->toArray();

            return $model
            ->whereIn('blok_id',$co_blok)
            ->withCount('peserta_ujian as peserta')->whereIn('status', [0, 1]);
        } else {
            return $model->withCount('peserta_ujian as peserta')->where('pembuat_ujian_id', auth()->user()->id_asal);
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('daftarujian-table')
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
            Column::make('status')->searchable(false)->orderable(false),
            Column::computed('pangawas_ujian_oke')->searchable(false)->orderable(false)->title('Pengawas'),
            Column::computed('peserta')->searchable(false)->orderable(false)->width(5),
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
        return 'DaftarUjian_' . date('YmdHis');
    }
}
