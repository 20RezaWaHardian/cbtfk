<?php

namespace App\DataTables;

use App\Models\PaketSoal;
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

class PaketSoalDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('jumlah', function ($row) {
                return  '<a href="' . route('bank-soal.paket-soal.showSoalByPaketSoal', ['id' => encrypt($row->id_paket_soal)]) . '"  class="btn btn-info btn-sm ">Lihat <br>' . $row->jumlah . ' <br> Soal</a>';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (Gate::allows('update bank-soal/soal')) {
                    if ($row->is_active == 1) {
                        $action .= ' <a href="' . route('bank-soal.paket-soal.updateStatus', ['id' => encrypt($row->id_paket_soal), 'is_active' => 1]) . '"  class="btn btn-success btn-sm action"><i class="fa fa-toggle-off" aria-hidden="true"></i></a>';
                    } elseif ($row->is_active == 0) {
                        $action .= ' <a href="' . route('bank-soal.paket-soal.updateStatus', ['id' => encrypt($row->id_paket_soal), 'is_active' => 0]) . '"  class="btn btn-danger btn-sm action"><i class="fa fa-toggle-on" aria-hidden="true"></i></a>';
                    }
                    $action .= ' <button type="button" data-id=' . $row->id_paket_soal . ' data-jenis="edit" class="btn btn-warning btn-sm action"><i class="fas fa-pencil-alt"></i></button>';
                }
                if (Gate::allows('delete bank-soal/soal')) {
                    $action .= ' <button type="button" data-id=' . $row->id_paket_soal . ' data-jenis="delete" class="btn btn-danger btn-sm action"><i class="fas fa-trash"></i></button>';
                }
                $action .= '<a href="' . route('bank-soal.paket-soal.analisisSoal', ['id_paket_soal' => encrypt($row->id_paket_soal)]) . '"  class="btn btn-secondary btn-sm action"><i class="fa fa-line-chart" aria-hidden="true"></i></a>';
                return $action;
            })
            ->rawColumns(['jumlah', 'action'])
            ->addIndexColumn()
            ->setRowId('id_paket_soal');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PaketSoal $model): QueryBuilder
    {
        if (auth()->user()->hasAnyRole(['developer', 'admin',])) {
            return $model->orderBy('id_paket_soal','desc')
                    ->where('is_delete', 0)
                    ->withCount([
                        'soal as jumlah'
                    ]);
        } elseif (auth()->user()->hasAnyRole(['koordinator-blok'])) {
            $idPegawaiLogin = auth()->user()->pegawai->id_pegawai;
            $semester = DB::table('siakad.semester')->where('periode_aktif', 1)->first();

            $blokIds = DB::table('siakad_blok.koordinator_blok')
                ->where('id_semester', $semester->id_semester)
                ->where('id_pegawai_koor', $idPegawaiLogin)
                ->orWhere('id_pegawai_ass', $idPegawaiLogin)
                ->pluck('id_kelas');

            // ambil pegawai lain baik dari kolom koor atau ass
            $pegawaiLain = DB::table('siakad_blok.koordinator_blok')
                ->whereIn('id_kelas', $blokIds)
                ->where('id_semester', $semester->id_semester)
                ->where(function ($query) use ($idPegawaiLogin) {
                    $query->where('id_pegawai_koor', '!=', $idPegawaiLogin)
                        ->orWhere('id_pegawai_ass', '!=', $idPegawaiLogin);
                })
                ->selectRaw('id_pegawai_koor as id_pegawai')
                ->union(
                    DB::table('siakad_blok.koordinator_blok')
                        ->where('id_semester', $semester->id_semester)
                        ->whereIn('id_kelas', $blokIds)
                        ->where(function ($query) use ($idPegawaiLogin) {
                            $query->where('id_pegawai_koor', '!=', $idPegawaiLogin)
                                ->orWhere('id_pegawai_ass', '!=', $idPegawaiLogin);
                        })
                        ->selectRaw('id_pegawai_ass as id_pegawai')
                )
                ->distinct()
                ->pluck('id_pegawai');
            $pegawaiLain = $pegawaiLain->reject(function ($id) use ($idPegawaiLogin) {
                return $id == $idPegawaiLogin; // buang yg sama dengan id login
            })->values();
            // dd();
            $pegawai = DB::table('kepeg.pegawai')->where('pegawai_siakad_id', $pegawaiLain->first())->first();

            return $model->orderBy('id_paket_soal','desc')
            ->where('is_delete', 0)
            ->whereIn('created_by', [$pegawai->id_pegawai, $idPegawaiLogin])->withCount([
                'soal as jumlah' => function ($query) use ($pegawai) {
                    $query->whereIn('id_pelaku', [auth()->user()->id_asal, $pegawai->id_pegawai]);
                }
            ]);
        } else {
            return $model->orderBy('id_paket_soal','desc')
            ->where('is_delete', 0)
            ->where('created_by', auth()->user()->id_asal)->withCount([
                'soal as jumlah' => function ($query) {
                    $query->where('id_pelaku', auth()->user()->id_asal);
                }
            ]);
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->parameters(['searchDelay => 1000'])
            ->setTableId('paketsoal-table')
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
            Column::make('judul'),
            Column::make('durasi')->title('Durasi (Menit)'),
            Column::computed('jumlah')->searchable(false)->orderable(false)->width(5),
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
        return 'PaketSoal_' . date('YmdHis');
    }
}
