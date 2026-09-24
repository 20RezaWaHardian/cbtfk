<?php

namespace App\DataTables;

use App\Models\KategoriSoal;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class KategoriSoalDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('kelas', function ($row) {
                if (! $row->id_kelas) {
                    return '-';
                }

                $kelas = DB::table('kelas as a')
                    ->select('a.id_kelas', 'a.kode_kelas', 'b.id_blok', 'b.nama_blok')
                    ->join('blok as b', 'a.id_blok', '=', 'b.id_blok')
                    ->where('a.id_kelas', $row->id_kelas)
                    ->first();

                if (! $kelas) {
                    return '-';
                }

                return $kelas->nama_blok . ' - ' . $kelas->kode_kelas;
            })
            ->editColumn('jumlah', function ($row) {
                return '<a href="' . route('bank-soal.soal.showSoalByKategori', ['id' => $row->id_kategori_soal]) . '" class="btn btn-info btn-sm">Lihat <br>' . $row->jumlah_sub_kategori . ' Sub Kategori <br>' . $row->jumlah . ' Soal</a>';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (Gate::allows('update bank-soal/soal')) {
                    $action .= '<a href="' . route('bank-soal.soal.edit', $row->id_kategori_soal) . '" class="btn btn-warning btn-sm"><i class="fas fa-pencil-alt"></i></a>';
                }

                if (Gate::allows('delete bank-soal/soal')) {
                    $action .= ' <button type="button" data-id=' . $row->id_kategori_soal . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                }

                return $action;
            })
            ->rawColumns(['jumlah', 'action'])
            ->addIndexColumn()
            ->setRowId('id_kategori_soal');
    }

    public function query(KategoriSoal $model): QueryBuilder
    {
        if(auth()->user()->hasRole('Dosen')) {
            return $model->newQuery()
                ->where('id_pelaku', auth()->user()->id)
                ->orderBy('id_kategori_soal', 'desc')
                ->withCount([
                    'soal as jumlah',
                    'sub_kategori_soal as jumlah_sub_kategori',
                ]);
        }else{

            return $model->newQuery()
                ->orderBy('id_kategori_soal', 'desc')
                ->withCount([
                    'soal as jumlah',
                    'sub_kategori_soal as jumlah_sub_kategori',
                ]);
        }
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->parameters(['searchDelay' => 1000])
            ->setTableId('kategorisoal-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->searchable(false)->orderable(false)->title('No')->width(2),
            Column::make('nama_kategori')->title('Nama Kategori'),
            Column::make('kelas')->searchable(false)->orderable(false)->title('Blok/Kelas'),
            Column::computed('jumlah')->searchable(false)->orderable(false)->width(5),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'KategoriSoal_' . date('YmdHis');
    }
}