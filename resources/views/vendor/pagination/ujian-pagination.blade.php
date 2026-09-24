@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">


        @for ($page = 1; $page <= $paginator->lastPage(); $page++)
            @php
                $url = $paginator->url($page); 
            @endphp

            @if ($page == $paginator->currentPage())
                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
            @else
                <li class="page-item">
                    <a class="page-link " href="{{ $url }}">
                        {{ $page }}
                    </a>
                </li>
            @endif
        @endfor



        </ul>
    </nav>
@endif
