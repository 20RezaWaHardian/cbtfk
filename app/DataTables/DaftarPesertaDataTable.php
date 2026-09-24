<?php

namespace App\DataTables;

use App\Models\DaftarPesertum;
use App\Models\PesertaUjian;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DaftarPesertaDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
                ->editColumn('nim', function ($row) {
                    return $row->mhs_pt->no_mhs;
                })
                ->editColumn('nama_peserta', function ($row) {
                    return '<a href="' . route('ujian.peserta.koreksi', ['id_peserta_ujian' => $row->id_peserta_ujian]) . '">' .  $row->mhs_pt->mahasiswa->nama_mahasiswa . '</a>';
                })
                ->editColumn('nilai', function ($row) {
                    return $row->total_nilai() ?? 0;
                })

                ->editColumn('aksi', function ($row) {
                    return '<a class="btn btn-sm btn-secondary" href="' . route('ujian.peserta.koreksi', ['id_peserta_ujian' => $row->id_peserta_ujian]) . '"> Detail  </a>';
                })
                ->rawColumns(['nama_peserta','aksi'])
                ->addIndexColumn()
                ->setRowId('id_peserta_ujian');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PesertaUjian $model): QueryBuilder
    {
        $ujianId = $this->id_ujian;
        return $model->newQuery()->where('ujian_id', $ujianId);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('daftarpeserta-table')
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
            Column::make('DT_RowIndex')->searchable(false)->orderable(false)->title('No')->width(2)->addClass('text-center'),
            Column::computed('nim')->width(100)->searchable(false)->orderable(false),
            Column::computed('nama_peserta')->searchable(false)->orderable(false),
            Column::make('nilai')->searchable(false)->orderable(false)->addClass('text-center'),
            Column::computed('aksi')->searchable(false)->orderable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'DaftarPeserta_' . date('YmdHis');
    }
}
