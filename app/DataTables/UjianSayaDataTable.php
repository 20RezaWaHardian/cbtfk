<?php

namespace App\DataTables;

use App\Models\PesertaUjian;
use App\Models\SBMahasiswaRombel;
use App\Models\UjianSaya;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UjianSayaDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('nilai', function ($row) {
                $output = '';

                if (!empty($row->nilai_akhir)) {
                    $output .= '<span class="badge text-bg-success">' . $row->nilai_akhir . '</span><br>';
                } elseif ($row->status_pengerjaan == 2) {
                    $output .= $row->nilai_akhir ?? $row->nilai;
                } else {
                    $output .= '-<br>';
                }

                if ($row->need_kuesioner == 1) {
                    if ($row->isi_kuesioner == 0) {
                        $output .= 'Klik untuk <a href="' . url('kuesioner/' . $row->id_peserta_ujian . '/participant/' . $row->ujian_id . '/kuesionerku/' . $row->kuesioner_id) . '">mengisi Kuesioner</a><br>';
                    } else {
                        $output .= 'Anda telah mengisi kuesioner<br>';
                    }
                }

                return $output;
            })

            ->editColumn('ujian.status', function ($row) {
                $status = '';
                if ($row->ujian) {
                    if (($row->status_pengerjaan == 0) && $row->ujian->status == 1) {
                        if($row->ujian->id_jenis_ujian == 1)
                        {
                            if($row->ujian->is_kuesioner_sebelum == 0)
                            {
                                $status = '<a href="' . route('peserta.faceRegister', [
                                    'id_ujian' => encrypt($row->ujian_id), 
                                    'id_peserta_ujian' => encrypt($row->id_peserta_ujian)
                                    ]) . '" class="btn btn-info btn-sm masukRoom mb-2">Mulai Ujian</a> <br/>';

                            }else{
                                $status = '<a href="' . route('peserta.kuesionerSebelum', [
                                'id_peserta_ujian' => encrypt($row->id_peserta_ujian),
                                'id_ujian' => encrypt($row->ujian_id), 
                                ]) . '" class="btn btn-info btn-sm mb-2">Masuk</a> <br/>';
                            }
                        }else{
                            $status = '<a href="' . route('peserta.token', ['id_ujian' => encrypt($row->ujian_id), 'id_peserta_ujian' => encrypt($row->id_peserta_ujian)]) . '" class="btn btn-info btn-sm masukRoom mb-2">Mulai Ujian</a> <br/>';
                            // $status = '<a href="' . route('peserta.faceRegister', ['id_ujian' => encrypt($row->ujian_id), 'id_peserta_ujian' => encrypt($row->id_peserta_ujian)]) . '" class="btn btn-info btn-sm masukRoom mb-2">Mulai Ujian</a> <br/>';
                        }
                    }elseif($row->status_pengerjaan == 1 && $row->ujian->status == 1){
                        $status = '<a href="' . route('peserta.faceRegister', ['id_ujian' => encrypt($row->ujian_id), 'id_peserta_ujian' => encrypt($row->id_peserta_ujian)]) . '" class="btn btn-info btn-sm masukRoom mb-2">Lanjutkan Ujian</a> <br/>';

                    }elseif($row->status_pengerjaan == 2){
                        $status = '<span class="badge text-bg-success mb-2">Anda sudah mengerjakan</span> <br/>';
                    }

                    switch ($row->status_pengerjaan) {
                        case 0:
                            $status .= '<span class="badge text-bg-secondary">Belum Dimulai</span>';
                            break;
                        case 1:
                            $status .= '<span class="badge text-bg-warning">Sedang Berlangsung</span>';
                            break;
                        case 2:
                            $status .= '<span class="badge text-bg-danger">Berakhir</span>';
                            break;
                        case 3:
                            $status .= '<span class="badge text-bg-danger">Dihentikan</span>';
                            break;
                        default:
                            $status .= '<span class="badge text-bg-secondary">-</span>';
                            break;
                    }
                }
                return $status;
            })

            ->rawColumns(['ujian.status','nilai'])
            ->addIndexColumn()
            ->setRowId('id_peserta_ujian');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PesertaUjian $model): QueryBuilder
    {
        $mhs_pt_id = auth()->user()->userSistemBlok->mahasiswa->id_mahasiswa ?? null;
        return $model->where('id_mhs_pt', $mhs_pt_id)
                ->with('ujian', 'mahasiswa_rombel');
        
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function htmlTable(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('ujiansaya-table')
            ->columns($this->getColumns())
            // ->minifiedAjax()
            //->dom('Bfrtip')
            ->ajax([
                'url' => route('load-datatable-ujian-saya'),
                'type' => 'GET',
                // 'data' => ['identitas_usulan_id' => $identitasUsulanId], // Kirim identitas_usulan_id
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
            Column::make('ujian.tanggal_ujian')->title('Tanggal Ujian')->width(100)->searchable(true)->orderable(true),
            Column::make('ujian.selesai_ujian')->title('Selesai Ujian')->width(100)->searchable(true)->orderable(true),
            Column::make('ujian.nama_ujian')->title('Nama Ujian')->searchable(true)->orderable(true),
            Column::make('nilai')->width(50)->searchable(true)->orderable(true),
            Column::make('ujian.status')->title('Status')->searchable(false)->orderable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'UjianSaya_' . date('YmdHis');
    }
}
