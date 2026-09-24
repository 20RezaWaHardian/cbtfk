@if ($jumlahSoal > 1)
    <nav>
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            <li class="page-item {{ $soal_satuan->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $soal_satuan->previousPageUrl() }}" aria-label="@lang('pagination.previous')">&lsaquo;</a>
            </li>

            {{-- Page Numbers --}}
            @for ($page = 1; $page <= $jumlahSoal; $page++)
                <li class="page-item {{ $page == $soal_satuan->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $soal_satuan->url($page) }}">{{ $page }}</a>
                </li>
            @endfor

            {{-- Next Page Link --}}
            <li class="page-item {{ !$soal_satuan->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $soal_satuan->nextPageUrl() }}" aria-label="@lang('pagination.next')">&rsaquo;</a>
            </li>
        </ul>
    </nav>
@endif
