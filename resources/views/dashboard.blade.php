@extends('layouts.app')
@section('title', 'Dashboard')
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

    @if (auth()->user()->hasRole(['mahasiswa','peserta-eksternal']))
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-9 fw-semibold">Ujian Saya</h5>
                        <div class="table-responsive">
                            {!! $ujianSayaDataTable->table(['id' => 'ujiansaya-table']) !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @else

        <div class="row">
            
            <div class="col-sm-6 col-xl-4">
                <div class="card bg-danger-subtle shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Jumlah Paket Soal</h6>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7">{{ $paket_soal }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card bg-success-subtle shadow-none">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Jumlah Soal</h6>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4">
                            <h3 class="mb-0 fw-semibold fs-7">{{ $soal }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <div class="row">

                {{-- MONITORING --}}
                @if(auth()->user()->can('read monitoring/ujian'))
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
                @endif


                {{-- SLIDER DAN LOG AKTIVITAS --}}
                @if(auth()->user()->can('read logaktifitas'))
                <div class="col-lg-7">
                    {{-- <div class="card bg-dark text-white">
                        <img src="{{ asset('assets/images/logos/logo-cbt.png') }}" class="card-img" alt="...">
                        <div class="card-img-overlay">
                        <h5 class="card-title">Card title</h5>
                        <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                        <p class="card-text">Last updated 3 mins ago</p>
                        </div>
                    </div> --}}

                    {{-- <div class="card mb-3" >
                        <div class="row g-0">
                        <div class="col-md-4">
                            <img src="{{ asset('assets/images/profile/dekan-fkik.jpg') }}" class="img-fluid rounded-start" alt="{{ asset('assets/images/logos/logo-cbt.png') }}">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                            <h5 class="card-title">Card title</h5>
                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                            <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
                            </div>
                        </div>
                        </div>
                    </div> --}}
                </div>
                <div class="col-lg-5">

                    <div class="card">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h5 class="card-title fw-semibold">Aktivitas Terkini</h5>
                            </div>

                            <ul class="timeline-widget mb-0 position-relative mb-n5">
                                @forelse ($aktivitas as $data)
                                    @if ($loop->last)
                                        <li class="timeline-item d-flex position-relative overflow-hidden">
                                            <div class="timeline-time text-dark flex-shrink-0 text-end">
                                                {{ Carbon\Carbon::parse($data->tanggal)->format('d M') }} -
                                                {{ Carbon\Carbon::parse($data->tanggal)->format('H:i') }}
                                            </div>
                                            <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                                <span
                                                    class="timeline-badge border-2 border border-success flex-shrink-0 my-8"></span>
                                            </div>
                                            <div class="timeline-desc fs-3 text-dark mt-n1 fw-semibold">
                                                {{ $data->nama_pelaku }}
                                                <a href="javascript:void(0)"
                                                    class="text-primary d-block fw-normal">{{ $data->aktivitas }}</a>
                                            </div>
                                        </li>
                                    @else
                                        <li class="timeline-item d-flex position-relative overflow-hidden">
                                            <div class="timeline-time text-dark flex-shrink-0 text-end">
                                                {{ Carbon\Carbon::parse($data->tanggal)->format('d M') }} -
                                                {{ Carbon\Carbon::parse($data->tanggal)->format('H:i') }}
                                            </div>
                                            <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                                <span
                                                    class="timeline-badge border-2 border border-primary flex-shrink-0 my-8"></span>
                                                <span class="timeline-badge-border d-block flex-shrink-0"></span>
                                            </div>
                                            <div class="timeline-desc fs-3 text-dark mt-n1 fw-semibold">
                                                {{ $data->nama_pelaku }}
                                                <a href="javascript:void(0)"
                                                    class="text-primary d-block fw-normal">{{ $data->aktivitas }}</a>
                                            </div>
                                        </li>
                                    @endif
                                @empty
                                <li class="timeline-item d-flex position-relative overflow-hidden">
                                <p> Belum Ada Log Aktifitas Yang Tercatat </p>
                                </li>
                                @endforelse



                            </ul>
                        </div>
                    </div>

                </div>
                @endif

            </div>

    @endif

@endsection

@push('scripts')
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    {{ $monitoringUjianDataTable->scripts() }}

    {{ $ujianSayaDataTable->scripts() }}

    <script>
        $('#ujiansaya-table').on('click', '.masukRoom', function() {

            event.preventDefault(); // Menghentikan perilaku default dari link

            Swal.fire({
                title: 'Apakah anda yakin akan memasuki room ujian?',
                text: "Jika ya, maka aksi ini tidak bisa dibatalkan lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Masuk ke room ujian'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the exam start page
                    window.location.href = $(this).attr('href');
                }
            });


        });
    </script>
@endpush
