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
            return $model->withCount('peserta_ujian as peserta')->where('status', 2)->whereNotNull('kuesioner_sebelum_id');
        }elseif (auth()->user()->hasAnyRole(['koordinator-blok'])) {
            $idPegawaiLogin = auth()->user()->pegawai->pegawai_siakad_id;
            $semester = DB::table('siakad.semester')->where('periode_aktif', 1)->first();

            $blokIds = DB::table('siakad_blok.koordinator_blok')
                ->where('id_semester', $semester->id_semester)
                ->where('id_pegawai_koor', $idPegawaiLogin)
                ->orWhere('id_pegawai_ass', $idPegawaiLogin)
                ->pluck('id_kelas');
            // dd($blokIds);
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
            // dd($pegawaiLain)
            $pegawaiLain = $pegawaiLain->reject(function ($id) use ($idPegawaiLogin) {
                return $id == $idPegawaiLogin; // buang yg sama dengan id login
            })->values();
            // dd();
            $pegawai = DB::table('kepeg.pegawai')->where('pegawai_siakad_id', $pegawaiLain->first())->first();
            // dd(auth()->user()->pegawai->id_pegawai);
            $id_blok = DB::table('siakad.kelas as a')
                ->join('siakad.matakuliah as b','b.id_matakuliah','a.id_matakuliah')
                ->join('siakad_blok.koordinator_blok as c','c.id_kelas','a.id_kelas')
                ->where('a.id_semester',$semester->id_semester)
                ->whereIn('a.id_kelas',$blokIds)
                ->groupBy('b.id_blok')
                ->pluck('b.id_blok')->toArray();
            // dd($id_blok);

            return $model
            // ->whereHas('pengawas', function ($q) use ($pegawai) {
            //     $q->whereIn('ujian_has_pengawas.id_pegawai', [$pegawai->id_pegawai, auth()->user()->pegawai->id_pegawai]);
            // })
            ->whereIn('blok_id',$id_blok)
            ->whereNotNull('kuesioner_sebelum_id')
            ->withCount('peserta_ujian as peserta')->whereIn('status', [0, 1]);
        } else {
            return $model->withCount('peserta_ujian as peserta')
                ->whereNotNull('kuesioner_sebelum_id')
                ->where('pembuat_ujian_id', auth()->user()->id_asal);
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
