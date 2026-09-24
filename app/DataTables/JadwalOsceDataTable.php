<?php

namespace App\DataTables;

use App\Models\JadwalOsce;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Gate;
use App\Helpers\MyHelpers;
use DB;

class JadwalOsceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('status',function($r){
                return $r->status == 1 ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-danger">Tidak Aktif</span>';
            })
            ->addColumn('tanggal_ujian',function($r){
                return $r->tanggal_ujian ? date("d-m-Y", strtotime($r->tanggal_ujian)) : '-';
            })
            ->addColumn('blok_id',function($r){
                return optional($r->blok)->kode ?: '-';
            })
            ->addColumn('waktu_mulai',function($r){

                if($r->waktu_mulai && $r->waktu_selesai)
                {
                    return date("H:m", strtotime($r->waktu_mulai)) ." - ". date("H:m", strtotime($r->waktu_selesai)). " WIB";
                }else{
                    return '-';
                }
            })
            ->addColumn('stase', function($row) {
                    $html = '';
                    $html .= '<button type="button" data-id='.$row->id_jadwal_osce.' data-jenis="tambah-stase" class="btn btn-sm btn-primary action mb-2" style="float:right">Tambah</button>';
                    $html .= '<table class="table table-sm table-bordered">';
                    $html .= '<thead><tr><th>Stase</th><th>Penguji</th><th>#</th></tr></thead>';
                    $html .= '<tbody>';
                    
                    $no = 1;
                    $stase = DB::table('jadwal_has_stase as a')
                                ->join('jenis_osce as b','b.id_jenis_osce','a.id_jenis_osce')
                                ->where('a.id_jadwal_osce',$row->id_jadwal_osce)
                                ->get();
                    if ($stase->count() > 0) {
                        foreach ($stase as $value) {
                            $html .= '<tr>';
                            
                            $html .= '<td>' . $value->nama_jenis_osce . '</td>';
                            $html .= '<td>' . MyHelpers::nama_gelarById($value->id_pegawai) . '</td>'; // Pastikan fieldnya 'bobot'
                            $html .= '<td>';
                            // if (Gate::allows('delete data-master/komponen-nilai-osce')) {
                            $html .= ' <button type="button" data-id=' . $value->id_jadwal_has_stase . ' data-jenis="delete-stase" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                            // }
                            $html .= '</td>';
                            $html .= '</tr>';
                        }
                    } else {
                        $html .= '<tr>';
                            
                            $html .= '<td colspan="3">Belum Ada Stase</td>';
                            
                        $html .= '</tr>';
                    }
                    
                    $html .= '</tbody></table>';
                    return $html;
                // 
            })
            ->addColumn('action', function ($row) {
                $action = '';
                // $id_jenis_osce = $this->id_jenis_osce;
                if (Gate::allows('update data-master/jadwal-osce')) {
                    // $action = '<button type="button" data-id=' . $row->id_jadwal_osce. ' data-jenis="edit" class="btn btn-warning btn-sm action"><i class="fas fa-pencil-alt"></i></button>';
                    $action = '<a href="'.route('data-master.jadwal-osce.edit',$row->id_jadwal_osce) .'" class="btn btn-sm btn-warning"> <i class="fas fa-pencil-alt"></i></a>';
                }
                // if (Gate::allows('delete data-master/komponen-nilai-osce')) {
                    $action .= ' <button type="button" data-id=' . $row->id_jadwal_osce . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                // }
                    $action .= ' <a href="'.route('data-master.pesertaOsce',encrypt($row->id_jadwal_osce)) .'" class="btn btn-sm btn-info m-2">Peserta</a>';
                return $action;
            })
            ->rawColumns(['action','status','stase'])
            ->addIndexColumn()
            ->setRowId('id_jadwal_osce');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(JadwalOsce $model): QueryBuilder
    {
        return $model->where('isDeleted',false)->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('jadwalosce-table')
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
            Column::make('keterangan')->title('Keterangan Jadwal'),
            Column::make('blok_id')->title('Blok'),
            Column::make('tanggal_ujian')->searchable(false)->orderable(false),
            Column::make('waktu_mulai')->searchable(false)->orderable(false)->title('Waktu Ujian'),
            Column::computed('stase')
                ->searchable(false)
                ->orderable(false)
                ->width(400),
            Column::make('status')->searchable(false)->orderable(false),
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
        return 'JadwalOsce_' . date('YmdHis');
    }
}
