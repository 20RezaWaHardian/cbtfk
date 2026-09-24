<nav class="navbar navbar-expand-lg navbar-light exam-navbar" aria-label="Identitas ujian">
    <ul class="navbar-nav">
        <li class="nav-item nav-item-cbt">
            <div class="exam-brand">
                <img src="{{ asset('assets/images/logos/logouin.png') }}" width="100" height="50" alt="Logo UIN" class="exam-logo">
                <small class="exam-brand-label">CBT FK</small>
            </div>
        </li>
    </ul>
    <div class="exam-participant" id="navbarNav">
        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end exam-participant">
            <li class="exam-participant-info">
                <span class="exam-participant-name" title="{{ auth()->user()->userSistemBlok->mahasiswa->nama ?? '-' }}">{{ auth()->user()->userSistemBlok->mahasiswa->nama ?? '-' }}</span>
                <small class="exam-participant-username" title="{{ auth()->user()->username }}">{{ auth()->user()->username }}</small>
            </li>

            <li class="exam-avatar-wrapper">
                    <img src="{{ asset('assets/images/profile/user-1.jpg') }}" alt="" width="35"
                        height="35" class="exam-avatar">

            </li>
            {{-- <li class="nav-item dropdown">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="{{ asset('assets/images/profile/user-1.jpg') }}" alt="" width="35"
                        height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                    <div class="message-body">
                        <a href="{{ route('logout.manual') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="d  btn btn-outline-primary mx-3 mt-2 d-block">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout.manual') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </li> --}}
        </ul>
    </div>
</nav>
