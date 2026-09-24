<?php

namespace App\DataTables;

use App\Models\JenisOsce;
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

class JenisOsceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('komponen_nilai', function($row) {
                $komponen = KomponenNilaiOsce::where('id_jenis_osce', $row->id_jenis_osce)->where('isDeleted',false)->get();
                
                if ($komponen->count() > 0) {
                    $html = '<table class="table table-sm table-bordered">';
                    $html .= '<thead><tr><th>Komponen Nilai</th><th>Bobot</th><th>#</th></tr></thead>';
                    $html .= '<tbody>';
                    
                    $no = 1;
                    foreach ($komponen as $value) {
                        $html .= '<tr>';
                        
                        $html .= '<td>' . $value->nama_komponen . '</td>';
                        $html .= '<td>' . $value->bobot_nilai . '</td>'; // Pastikan fieldnya 'bobot'
                        $html .= '<td>';
                        if (Gate::allows('delete data-master/komponen-nilai-osce')) {
                        $html .= ' <button type="button" data-id=' . $value->id_komponen_nilai_osce . ' data-jenis="delete-komponen" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                        }
                        $html .= '</td>';
                        $html .= '</tr>';
                    }
                    
                    $html .= '</tbody></table>';
                    return $html;
                } else {
                    return '<span class="badge text-bg-danger">Belum Ada Komponen Nilai</span>';
                }
            })
            ->addColumn('status',function($r){
                return $r->status == 1 ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-danger">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if (Gate::allows('create data-master/komponen-nilai-osce')) {
                    // $action = '<button type="button" data-id=' . $row->id_jenis_osce . ' data-jenis="create-komponen" class="btn btn-primary btn-sm action m-2"><i class="fa fa-plus"></i> Komponen Nilai</button>';
                    $action = '<a href="'.route("data-master.komponen-nilai-osce.index",encrypt($row->id_jenis_osce)).'" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Komponen Nilai</a>';
                }
                if (Gate::allows('update data-master/jenis-osce')) {
                    $action .= '<button type="button" data-id=' . encrypt($row->id_jenis_osce) . ' data-jenis="edit" class="btn btn-warning btn-sm action"><i class="fas fa-pencil-alt"></i></button>';
                }
                if (Gate::allows('delete data-master/jenis-osce')) {
                    $action .= ' <button type="button" data-id=' . encrypt($row->id_jenis_osce) . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                }
                return $action;
            })
            ->rawColumns(['action','status','komponen_nilai'])
            ->addIndexColumn()
            ->setRowId('id_jenis_soal');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(JenisOsce $model): QueryBuilder
    {
        return $model->where('isDeleted',false)
            ->withCount(['komponen' => fn ($query) => $query->where('isDeleted', false)]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('jenisosce-table')
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
            Column::make('id_jenis_osce') ->width(3)->title('ID Stase OSCE'),
            Column::make('nama_jenis_osce')->title('Stase OSCE'),
            Column::make('komponen_count')->title('Jumlah Komponen Nilai')->searchable(false)->addClass('text-center'),
            Column::make('status')->searchable(false)->orderable(false),
            // Column::computed('komponen_nilai')
            //     ->searchable(false)
            //     ->orderable(false)
            //     ->width(400),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'JenisOsce_' . date('YmdHis');
    }
}
