<?php

namespace App\DataTables;

use App\Models\PesertaUjian;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use DB;

class PesertaUjianDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="' . $row->id_peserta_ujian . '">';
            })
            ->editColumn('nim', function ($row) {
                if($row->ujian->id_jenis_ujian == 1)
                {
                    return $row->mahasiswa->nim;
                }else{
                    return $row->peserta_eksternal->username ?? '-';
                }
            })
            ->addColumn('password_pmb', function ($row) {
                if ($row->ujian->id_jenis_ujian == 2) {
                    return $row->peserta_eksternal->password_plain ?? '-';
                }

                return '-';
            })
            ->editColumn('nama', function ($row) {

                if($row->ujian->id_jenis_ujian == 1)
                {
                    return '<a href="' . route('ujian.peserta.koreksi', ['id_peserta_ujian' => $row->id_peserta_ujian]) . '">' .  $row->mahasiswa->nama . '</a>';
                }else{
                    return $row->peserta_eksternal->nama_peserta;
                }
                // $mhs = DB::table('siakad.mhs_pt as a')->join('siakad.mahasiswa as b','b.id_mahasiswa','a.id_mahasiswa')->Where('a.id_mhs_pt',$row->id_mhs_pt)->select('b.nama_mahasiswa')->first();
                // return '<a href="' . route('ujian.peserta.koreksi', ['id_peserta_ujian' => $row->id_peserta_ujian]) . '">' .  $mhs->nama_mahasiswa . '</a>';
            })
            ->editColumn('nilai', function ($row) {
                if($row->total_nilai() != false)
                {
                    return $row->total_nilai();
                }else{
                    return 0;
                }
            })

            ->editColumn('aksi', function ($row) {
                return '<a class="btn btn-sm btn-secondary" href="' . route('ujian.peserta.koreksi', ['id_peserta_ujian' => $row->id_peserta_ujian]) . '"> Koreksi  </a>';
            })
            ->rawColumns(['checkbox', 'nama', 'aksi'])
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
            ->setTableId('pesertaujian-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row'<'col-md-6'l><'col-md-6'f>>" .
                "<'row'<'col-md-12'tr>>" .
                "<'row'<'col-md-5'i><'col-md-7'p>>")
            ->initComplete("function() {
                $('#select-all').on('click', function() {
                    var rows = $('#pesertaujian-table tbody tr').find('td:nth-child(2) input[type=checkbox]');
                    rows.prop('checked', $(this).prop('checked'));
                });
                }")
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
            Column::make('checkbox')
                ->title('<input type="checkbox" id="select-all">')
                ->searchable(false)
                ->orderable(false)
                ->printable(false)
                ->exportable(false)
                ->width(2)
                ->view('partials.checkbox'),
            Column::computed('nim')->width(100)->searchable(false)->orderable(false)->title('Username'),
            Column::computed('nama')->width(100)->searchable(false)->orderable(false)->width(200),
            // Column::computed('password_pmb')->width(100)->searchable(false)->orderable(false)->title('Password'),
            Column::make('nilai')->searchable(false)->orderable(false),
            Column::computed('aksi')->searchable(false)->orderable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'PesertaUjian_' . date('YmdHis');
    }
}
