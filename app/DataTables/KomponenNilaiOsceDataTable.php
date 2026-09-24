<?php

namespace App\DataTables;

use App\Models\KomponenNilaiOsce;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Gate;

class KomponenNilaiOsceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('id_jenis_osce',function($r){
                return $r->jenis ? $r->jenis->nama_jenis_osce : '-';
            })
            ->addColumn('bobot_nilai',function($r){
                return $r->bobot_nilai ?? '<span class="badge text-bg-danger">Belum Diinput</span>';
            })
            ->addColumn('status_instrumen', fn ($row) => $row->instrumen_nilai_count ? 'Sudah ada instrumen' : 'Belum ada instrumen')
            ->addColumn('action', function ($row) {
                $action = '';
                $id_jenis_osce = $this->id_jenis_osce;
                if (Gate::allows('update data-master/komponen-nilai-osce')) {
                    // $action = '<button type="button" data-id=' . encrypt($row->id_komponen_nilai_osce) . ' data-id_jenis_osce="'.$id_jenis_osce.'" data-jenis="edit" class="btn btn-warning btn-sm action"><i class="fas fa-pencil-alt"></i></button>';
                    $action = '<a href=' . route("data-master.komponen-nilai-osce.edit",["id_komponen_nilai_osce"=>encrypt($row->id_komponen_nilai_osce),"id_jenis_osce" =>$id_jenis_osce] ). ' class="btn btn-warning btn-sm action">Edit Komponen</a>';
                }
                if (Gate::allows('create data-master/komponen-nilai-osce') || Gate::allows('update data-master/komponen-nilai-osce')) {
                    $action .= ' <a class="btn btn-primary btn-sm" href="'.route('data-master.komponen-nilai-osce.instrumen', encrypt($row->id_komponen_nilai_osce)).'">Kelola Instrumen</a>';
                }
                if (Gate::allows('delete data-master/komponen-nilai-osce')) {
                    $action .= ' <button type="button" data-id=' . encrypt($row->id_komponen_nilai_osce) . ' data-jenis="delete" class="btn btn-danger btn-sm action">Hapus</button>';
                }
                return $action;
            })
            ->rawColumns(['action','status','bobot_nilai'])
            ->addIndexColumn()
            ->setRowId('id_komponen_nilai_osce');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(KomponenNilaiOsce $model): QueryBuilder
    {
        return $model->with('jenis')->withCount('instrumenNilai')->where('isDeleted',false)->where('id_jenis_osce',decrypt($this->id_jenis_osce));
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('komponennilaiosce-table')
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
            Column::make('nama_komponen')->title('Komponen Nilai'),
            Column::make('id_jenis_osce')->searchable(false)->orderable(false)->title('Jenis OSCE'),
            Column::make('bobot_nilai')->title('Bobot Nilai')->searchable(false),
            Column::make('instrumen_nilai_count')->title('Jumlah Instrumen')->searchable(false),
            Column::computed('status_instrumen')->title('Status Instrumen'),
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
        return 'KomponenNilaiOsce_' . date('YmdHis');
    }
}
