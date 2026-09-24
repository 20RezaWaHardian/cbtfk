@php
    $authUser = auth()->user();
    $displayName = $authUser->name ?? $authUser->username ?? 'Pengguna';
    $displayUsername = $authUser->username ?? $authUser->email ?? '-';
    $displayRole = $authUser->usertype ?? 'user';
@endphp

<nav class="navbar navbar-expand-lg app-topbar">
    <ul class="navbar-nav align-items-center gap-2">
        <li class="nav-item d-block d-xl-none">
            <a class="nav-link nav-action sidebartoggler" id="headerCollapse" href="javascript:void(0)" aria-label="Buka menu">
                <i class="ti ti-menu-2"></i>
            </a>
        </li>
        <li class="nav-item d-none d-md-block">
            <div class="header-greeting">
                <span>Selamat datang,</span>
                <strong>{{ $displayName }}</strong>
            </div>
        </li>
    </ul>

    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end gap-2">
            <li class="nav-item d-none d-sm-block">
                <span class="system-pill">
                    <i class="ti ti-shield-check"></i>
                    CBT FK Aktif
                </span>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link user-chip" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-avatar">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                    <span class="user-meta d-none d-sm-flex">
                        <strong>{{ $displayName }}</strong>
                        <small>{{ $displayRole }}</small>
                    </span>
                    <i class="ti ti-chevron-down d-none d-sm-inline-flex"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up user-dropdown" aria-labelledby="drop2">
                    <div class="dropdown-user-card">
                        <span class="user-avatar user-avatar-lg">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                        <div>
                            <strong>{{ $displayName }}</strong>
                            <small>{{ $displayUsername }}</small>
                        </div>
                    </div>

                    @if (session()->has('impersonator_id'))
                        <form action="{{ route('logout-as') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-action text-primary border-0 bg-transparent">
                                <i class="ti ti-user-exclamation"></i> Logout As
                            </button>
                        </form>
                    @endif

                        <a href="{{ route('logout.manual') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-action text-danger">
                            <i class="ti ti-logout"></i>
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout.manual') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                </div>
            </li>
        </ul>
    </div>
</nav>