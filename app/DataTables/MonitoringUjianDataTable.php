<?php

namespace App\DataTables;

use App\Helpers\MyHelpers;
use App\Models\Ujian;
use App\Models\MonitoringUjian;
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

class MonitoringUjianDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            // ->editColumn('prodi_id', function ($row) {
            //     return  $row->prodi->nama_prodi ?? '';
            // })
            ->editColumn('nama_ujian', function ($row) {
                // return  '<a href="' . route('ujian.peserta.index', ['id_ujian' => $row->id_ujian, 'id_kelompok_belajar' => $row->kelompok_belajar_id]) . '" >' . $row->nama_ujian . '</a>';
                return $row->nama_ujian;
            })
            ->editColumn('durasi_ujian', function ($row) {
                return  $row->paket_soal->durasi ?? '';
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
            ->editColumn('status', function ($row) {
                $status = '';
                switch ($row->status) {
                    case 1:
                        $status = '<span class="badge text-bg-info">Sedang Berlangsung</span>';
                        break;

                    case 2:
                        $status = '<span class="badge text-bg-danger">Berakhir</span>';
                        break;

                    default:
                        $status = '<span class="badge text-bg-secondary">Belum Dimulai</span>';
                        break;
                }

                if($row->id_jenis_ujian == 2)
                {
                    $status .= "<hr>";
                    $status .= "Token :" .$row->token ?? "Belum di Set Token";
                }
                return $status;
            })
            ->editColumn('aksi', function ($row) {
                $action = '';
                $action .= '<div class="btn-group">
                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu" style="max-height: none; overflow: visible;">'; // Ensures no scroll and auto-expands

                if ($row->status == 0) {
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.daftar-ujian.mulai', ['id_ujian' => encrypt($row->id_ujian)]) . '">Mulai Ujian</a></li>';
                } elseif ($row->status == 1) {
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.daftar-ujian.akhiri', ['id_ujian' => encrypt($row->id_ujian)]) . '">Akhiri Ujian</a></li>';
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.monitoring.index', ['id_ujian' => encrypt($row->id_ujian)]) . '">Monitor Ujian</a></li>';
                } elseif ($row->status == 2) {
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.riwayat-ujian.resume', ['id_ujian' => encrypt($row->id_ujian)]) . '">Resume Ujian</a></li>';
                    $action .= '<li><a class="dropdown-item" href="' . route('ujian.monitoring.index', ['id_ujian' => encrypt($row->id_ujian)]) . '">Monitor Ujian</a></li>';
                }
                
                if($row->id_jenis_ujian == 2)
                {
                    $action .= '<li><a class="dropdown-item" href="' . route('monitoring.setToken', ['id_ujian' => encrypt($row->id_ujian)]) . '">Set Token</a></li>';
                }

                $action .= '</ul>
                            </div>';

                return $action;
            })




            ->rawColumns(['aksi', 'status', 'nama_ujian', 'pangawas_ujian_oke'])
            ->addIndexColumn()
            ->setRowId('id_ujian');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Ujian $model): QueryBuilder
    {
        if (auth()->user()->hasAnyRole(['pengawas'])) {
            return $model->whereHas('pengawas', function ($q) {
                $q->where('id_pegawai', auth()->user()->dosen->id_dosen);
            })
            ->withCount('peserta_ujian as peserta')
            ->whereIn('status', [0, 1]);
        }  else {
            return $model->withCount('peserta_ujian as peserta')->whereIn('status', [0, 1]);
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function htmlTable(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('monitoringujian-table')
            ->columns($this->getColumns())
            // ->minifiedAjax()
            //->dom('Bfrtip')
            ->ajax([
                'url' => route('load-datatable-monitoring-ujian'),
                'type' => 'GET',
                // 'data' => ['identitas_usulan_id' => $identitasUsulanId], // Kirim identitas_usulan_id
            ])
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
            // Column::make('prodi_id')->title('Program Studi')->searchable(true)->orderable(true),
            Column::make('nama_ujian')->searchable(true)->orderable(true),
            Column::computed('durasi_ujian')->title('Durasi (Menit)')->searchable(true)->orderable(true),
            Column::make('tanggal_ujian')->title('Mulai Ujian')->searchable(true)->orderable(true),
            Column::make('selesai_ujian')->title('Selesai Ujian')->searchable(true)->orderable(true),
            Column::computed('pangawas_ujian_oke')->searchable(false)->orderable(false)->width(5),
            Column::make('status')->searchable(false)->orderable(false)->width(5),
            Column::computed('aksi')->searchable(false)->orderable(false)->width(5),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'MonitoringUjian_' . date('YmdHis');
    }
}
