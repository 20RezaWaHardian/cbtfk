<?php

namespace App\DataTables;

use App\Models\Soal;
use App\Models\DaftarSoal;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class DaftarSoalDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))

            ->editColumn('pertanyaan', function ($row) {
                if (!$row->pertanyaan) {
                    return '<span class="badge text-bg-warning">Pertanyaan Belum Dibuat</span>';
                }
                $truncatedText = strlen(strip_tags($row->pertanyaan)) > 50 ? substr(strip_tags($row->pertanyaan), 0, 50) . '...' : strip_tags($row->pertanyaan);
                return '<div>' . $truncatedText . '</div>';
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
                    // $action .= ' <button type="button" data-id=' . $row->id_soal. ' class="btn btn-success btn-sm action"> Ubah Poin </button>';
                    if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
                        if ($row->jenis_soal == 'essay') {
                            $action .= ' <a href="' . route('bank-soal.soal.editSoalEssayWithoutKategori', ['id' => $row->id_soal]) . '" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                        } elseif ($row->jenis_soal == 'pilgan') {
                            $action .= ' <a href="' . route('bank-soal.soal.editSoalPilganWithoutKategori', ['id' => $row->id_soal]) . '" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                        }
                        if (Gate::allows('delete bank-soal/soal/daftar-soal')) {
                                $action .= ' <button type="button" data-id=' . $row->id_soal . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                            }
                    }else{
                        if(in_array($row->status_validasi, [0, 2], true)){
                            if ($row->jenis_soal == 'essay') {
                                $action .= ' <a href="' . route('bank-soal.soal.editSoalEssayWithoutKategori', ['id' => $row->id_soal]) . '" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                            } elseif ($row->jenis_soal == 'pilgan') {
                                $action .= ' <a href="' . route('bank-soal.soal.editSoalPilganWithoutKategori', ['id' => $row->id_soal]) . '" class="btn btn-info btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                            }


                        }

                        

                    }
                }

                

                return $action;
            })
            ->rawColumns(['pertanyaan', 'action', 'kategori_soal','status_validasi'])
            ->addIndexColumn()
            ->setRowId('id_soal');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Soal $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->when(in_array(request('status_validasi'), ['0', '1', '2'], true), function ($query) {
                $query->where('status_validasi', request('status_validasi'));
            });
        if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
            return $query
                ->when(request('kategori_soal'), function ($query) {
                    $query->whereHas('sub_kategori_soal', function ($q) {
                        $q->where('id_kategori_soal', request('kategori_soal'));
                    });
                })
                ->when(request('sub_kategori_soal'), function ($query) {
                    $query->where('sub_kategori_soal_id', request('sub_kategori_soal'));
                });
        }else{
            return $query->where(function ($query) {
                    if (auth()->user()->id_asal !== null) {
                        $query->where('created_by', auth()->user()->id_asal)
                            ->orWhere(function ($fallback) {
                                $fallback->whereNull('created_by')->where('id_pelaku', auth()->user()->id);
                            });
                    } else {
                        $query->whereNull('created_by')->where('id_pelaku', auth()->user()->id);
                    }
                })
                ->when(request('kategori_soal'), function ($query) {
                    $query->whereHas('sub_kategori_soal', function ($q) {
                        $q->where('id_kategori_soal', request('kategori_soal'));
                    });
                })
                ->when(request('sub_kategori_soal'), function ($query) {
                    $query->where('sub_kategori_soal_id', request('sub_kategori_soal'));
                });
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('daftarsoal-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', null, [
                'status_validasi' => "function() { return $('#status_validasi').val(); }",
                'kategori_soal' => "function() { return $('#kategori_soal').val(); }",
                'sub_kategori_soal' => "function() { return $('#sub_kategori_soal').val(); }",
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
            Column::make('pertanyaan')->width(100)->searchable(true)->orderable(true)->width(200),
            Column::computed('kategori_soal')->width(100)->searchable(false)->orderable(false)->width(200),
            Column::make('status_validasi')->addClass('text-center'),
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
        return 'DaftarSoal_' . date('YmdHis');
    }
}
