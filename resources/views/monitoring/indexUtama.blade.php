@extends('layouts.app')
@section('title', 'Monitoring Ujian')
@push('style')
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
@endpush
@section('contents')
    {{-- <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-1">{{ session('userlogin')['nama_pelaku'] }}</h5>
            <p class="mb-0">{{ session('userlogin')['username'] }} </p>
        </div>
    </div> --}}

        <div class="row">

            {{-- MONITORING --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-9 fw-semibold">Monitoring Ujian</h5>
                        <div class="table-responsive">
                            {!! $monitoringUjianDataTable->table(['id' => 'monitoringujian-table']) !!}
                        </div>
                    </div>

                </div>
            </div>

        </div>

@endsection

@push('scripts')
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{ $monitoringUjianDataTable->scripts() }}

    </script>
@endpush
