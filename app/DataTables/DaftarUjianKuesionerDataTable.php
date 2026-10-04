<?php

namespace App\DataTables;

use App\Helpers\MyHelpers;
use App\Models\Ujian;
use App\Models\Kuesioner;
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

class DaftarUjianKuesionerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            
            ->editColumn('action', function ($row) {
                $action = '';
                $cekKue = Kuesioner::where('id_kuesioner',decrypt($this->id_kuesioner))->first();
                if($cekKue->terap_kue == 1)
                {
                    $action .= '<a href="'.route('detail-kuesioner',encrypt($row->id_ujian)).'" class="btn btn-sm btn-primary">Detail</a>';

                }else{
                    $action .= '<a href="'.route('detailKueSebelum',encrypt($row->id_ujian)).'" class="btn btn-sm btn-primary">Detail</a>';
                }
                return $action;
            })
            ->rawColumns(['action', 'peserta', 'status'])
            ->addIndexColumn()
            ->setRowId('id_ujian');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Ujian $model): QueryBuilder
    {
        $kuesionerId = decrypt($this->id_kuesioner);
        if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
            return $model->where('kuesioner_id', $kuesionerId)->withCount('peserta_ujian as peserta')->where('status', 2);
        }elseif (auth()->user()->hasAnyRole(['koordinator-blok'])) {
            $idPegawaiLogin = auth()->user()->pegawai->pegawai_siakad_id;
            $semester = DB::table('siakad.semester')->where('periode_aktif', 1)->first();

            $co_blok = DB::table('sistembl_siakad-uin.pengelola_blok as a')
                    ->join('sistembl_siakad-uin.dosen as b','a.dosen_id','b.id_dosen')
                    ->where('a.id_dosen', auth()->user()->dosen->id_dosen)
                    ->pluck('a.id_kelas')->toArray();

            return $model->where('kuesioner_id', $kuesionerId)
            ->whereIn('blok_id',$co_blok)
            ->withCount('peserta_ujian as peserta')->whereIn('status', [2]);
        } else {
            return $model ->where('status', 2)->where('kuesioner_id', $kuesionerId)->withCount('peserta_ujian as peserta')->where('pembuat_ujian_id', auth()->user()->id_asal);
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
