<?php

namespace App\DataTables;

use App\Models\KepegUser;
use App\Models\User;
use App\Models\SiakadUser;
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

class UserSiabDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {

        return (new EloquentDataTable($query))
            ->addColumn('role', function ($row) {
                if ($row) {
                    $awal = $row->roles;

                    if ($awal) {
                        foreach ($awal as $key => $value) {
                            $awal[$key] = $value->name;
                        }
                        $awal = json_decode($awal, true);

                        if (is_array($awal)) {
                            return implode(" || ", $awal);
                        }
                    }
                }else{
                    return 'Belum Diset';
                }

            })

            ->addColumn('action', function ($row) {
                $action = '';
                if (Gate::allows('update konfigurasi/users')) {
                    $action .= '<button type="button" data-id=' . $row->username . ' data-jenis="edit" class="btn btn-warning btn-sm action">Set Role</button>';
                    $action .= ' <button type="button" data-id=' . $row->username . ' data-jenis="login-as" class="btn btn-success btn-sm action">Login As</button>';
                }

                return $action;
            })
            ->addIndexColumn()
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    // public function query(SiakadUser $model): QueryBuilder
    public function query(User $model): QueryBuilder
    {
        return $model->query();

    }
    // public function query(SiakadUser $model): QueryBuilder
    // {
        
    //     return $model->where('username', 'NOT LIKE', '%x%');

    // }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->parameters(['searchDelay => 1000'])
            ->setTableId('usersiab-table')
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
            Column::make('username')->title('NIP/NIDK')->searchable(true),
            Column::make('name')->name('kepeg_pegawai.nama_pegawai')->title('Nama Pegawai')->searchable(true)->defaultContent('-'),
            // Column::make('name')->title('Nama')->searchable(true),
            Column::computed('role'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(200)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'UserSiab_' . date('YmdHis');
    }
}
