<?php

namespace App\DataTables;

use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Gate;
use App\Models\DaftarSoalButuhValidasi;
use App\Models\Soal;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class DaftarSoalButuhValidasiDataTable extends DataTable
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
                return $row->status_validasi === 0 ? '<input type="checkbox" class="row-checkbox" value="' . $row->id_soal . '">' : '';
            })
            // ->editColumn('pertanyaan', function ($row) {
            //     if (!$row->pertanyaan) {
            //         return '<span class="badge text-bg-warning">Pertanyaan Belum Dibuat</span>';
            //     }
            //     $truncatedText = strlen(strip_tags($row->pertanyaan)) > 50 ? substr(strip_tags($row->pertanyaan), 0, 50) . '...' : strip_tags($row->pertanyaan);
            //     return '<div>' . $truncatedText . '</div>';
            // })
            ->editColumn('pertanyaan', function ($row) {
                if (!$row->pertanyaan) {
                    return '<span class="badge text-bg-warning">Pertanyaan Belum Dibuat</span>';
                }

                // Bersihkan tag HTML dan pastikan encoding UTF-8
                $cleanText = strip_tags($row->pertanyaan);
                $cleanText = mb_convert_encoding($cleanText, 'UTF-8', 'UTF-8');

                $truncatedText = strlen($cleanText) > 50 
                    ? substr($cleanText, 0, 50) . '...' 
                    : $cleanText;

                return '<div>' . e($truncatedText) . '</div>'; // gunakan e() biar aman XSS
            })
            ->editColumn('kategori_soal', function ($row) {
                return $row->kategori_soal ? $row->kategori_soal->nama_kategori : '<span class="badge text-bg-warning">Kategori Soal Belum Dibuat</span>';
            })
            ->editColumn('status_validasi', function ($row) {
                return view('bank-soal.soal.status-validasi', ['soal' => $row])->render();
            })
            ->addColumn('action', function ($row) {
                $action = '';
                if (Gate::allows('update bank-soal/soal/daftar-soal')) {
                    // $action .= ' <a href="#" onclick="confirmValidasi(\'' . route('bank-soal.soal.validasiSoal', $row->id_soal) . '\')" class="btn btn-info btn-sm mb-2">Validasi</a> <br>';

                    if ($row->jenis_soal == 'essay') {
                        $action .= ' <a href="' . route('bank-soal.soal.editSoalEssayWithoutKategori', ['id' => $row->id_soal]) . '" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                    } elseif ($row->jenis_soal == 'pilgan') {
                        $action .= ' <a href="' . route('bank-soal.soal.editSoalPilganWithoutKategori', ['id' => $row->id_soal]) . '" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                    }
                }

                if (Gate::allows('delete bank-soal/soal/daftar-soal')) {
                    $action .= ' <button type="button" data-id=' . $row->id_soal . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                }

                return $action;
            })
            ->rawColumns(['checkbox', 'pertanyaan', 'action', 'kategori_soal', 'status_validasi'])
            ->addIndexColumn()
            ->setRowId('id_soal');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Soal $model): QueryBuilder
    {
        if (auth()->user()->hasAnyRole(['koordinator-blok'])) {
            $co_blok = DB::table('sistembl_siakad-uin.pengelola_blok as a')
                    ->join('sistembl_siakad-uin.dosen as b','a.dosen_id','b.id_dosen')
                    ->where('a.id_dosen', auth()->user()->dosen->id_dosen)
                    ->pluck('a.id_kelas')->toArray();
            return $model->newQuery()
                ->with('kategori_soal')
                ->where('status_validasi', in_array(request('status_validasi'), ['0', '1', '2'], true) ? request('status_validasi') : 0)
                ->whereNotNull('pertanyaan')
                ->whereHas('kategori_soal', function ($q) use ($co_blok) {
                    $q->whereIn('id_kelas', $co_blok);
                })
                ->when(request('kategori_soal'), function ($query) {
                    $query->whereHas('sub_kategori_soal', function ($q) {
                        $q->where('id_kategori_soal', request('kategori_soal'));
                    });
                })
                ->when(request('sub_kategori_soal'), function ($query) {
                    $query->where('sub_kategori_soal_id', request('sub_kategori_soal'));
                });
        } else {
            return $model->newQuery()
                    ->where('status_validasi', in_array(request('status_validasi'), ['0', '1', '2'], true) ? request('status_validasi') : 0)
                    ->when(request('kategori_soal'), function ($query) {
                        $query->whereHas('sub_kategori_soal', function ($q) {
                            $q->where('id_kategori_soal', request('kategori_soal'));
                        });
                    })
                    ->when(request('sub_kategori_soal'), function ($query) {
                        $query->where('sub_kategori_soal_id', request('sub_kategori_soal'));
                    })
                    ->whereNotNull('pertanyaan');
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('daftarsoalbutuhvalidasi-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', null, [
                'status_validasi' => "function() { return $('#status_validasi').val(); }",
                'kategori_soal' => "function() { return $('#kategori_soal').val(); }",
                'sub_kategori_soal' => "function() { return $('#sub_kategori_soal').val(); }",
            ])
            ->dom("<'row'<'col-md-6'l><'col-md-6'f>>" .
                "<'row'<'col-md-12'tr>>" .
                "<'row'<'col-md-5'i><'col-md-7'p>>")
            ->initComplete("function() {
                $('#select-all').on('click', function() {
                    var rows = $('#daftarsoalbutuhvalidasi-table tbody tr').find('td:nth-child(2) input[type=checkbox]');
                    rows.prop('checked', $(this).prop('checked'));
                });
                }")
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
            Column::make('checkbox')
                ->title('<input type="checkbox" id="select-all">')
                ->searchable(false)
                ->orderable(false)
                ->printable(false)
                ->exportable(false)
                ->width(2)
                ->view('partials.checkbox'),
            // Column::make('id_soal')->width(100)->searchable(true)->orderable(true)->width(200),
            Column::make('pertanyaan')->width(100)->searchable(true)->orderable(true)->width(500),
            Column::computed('kategori_soal')->width(100)->searchable(false)->orderable(false)->width(200),
            Column::make('status_validasi')->addClass('text-center')->width(100),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'DaftarSoalButuhValidasi_' . date('YmdHis');
    }
}
