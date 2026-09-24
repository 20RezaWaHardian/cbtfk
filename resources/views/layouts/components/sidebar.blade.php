<div class="sidebar-shell">
    <div class="brand-logo d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="app-brand text-decoration-none">
            <span class="app-brand-logo">
                <img src="{{ asset('assets/images/logos/logouin.png') }}" alt="Logo UIN">
            </span>
            <span class="app-brand-text">
                <strong>CBT FK</strong>
                <small>Computer Based Test</small>
            </span>
        </a>
        <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
        </div>
    </div>

    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
        <ul id="sidebarnav">
            <li class="nav-small-cap">
                <i class="ti ti-layout-dashboard nav-small-cap-icon fs-4"></i>
                <span class="hide-menu">Menu Utama</span>
            </li>

            @foreach (getMenus() as $menu)
                @php
                    $menuUrl = trim($menu->url ?? '', '/');
                    $isMenuActive = $menuUrl !== '' && request()->is($menuUrl . '*');

                    if (count($menu->subMenus) > 0) {
                        foreach ($menu->subMenus as $childMenu) {
                            $childUrl = trim($childMenu->url ?? '', '/');
                            if ($childUrl !== '' && request()->is($childUrl . '*')) {
                                $isMenuActive = true;
                                break;
                            }
                        }
                    }
                @endphp

                @if (count($menu->subMenus) > 0)
                    @can('read ' . $menu->url)
                        <li class="sidebar-item {{ $isMenuActive ? 'selected' : '' }}">
                            <a class="sidebar-link has-arrow {{ $isMenuActive ? 'active' : '' }}" href="javascript:void(0)" aria-expanded="{{ $isMenuActive ? 'true' : 'false' }}">
                                <span class="menu-icon-wrap"><i class="{{ $menu->icon }}"></i></span>
                                <span class="hide-menu">{{ $menu->name }}</span>
                            </a>
                            <ul aria-expanded="{{ $isMenuActive ? 'true' : 'false' }}" class="collapse first-level {{ $isMenuActive ? 'in show' : '' }}">
                                @foreach ($menu->subMenus->whereNull('sub_menu') as $submenu)
                                    @can('read ' . $submenu->url)
                                        @php
                                            $submenuUrl = trim($submenu->url ?? '', '/');
                                            $isSubActive = $submenuUrl !== '' && request()->is($submenuUrl . '*');
                                        @endphp
                                        <li class="sidebar-item {{ $isSubActive ? 'selected' : '' }}">
                                            <a class="sidebar-link {{ $isSubActive ? 'active' : '' }}" href="{{ url($submenu->url) }}">
                                                <span class="submenu-dot"></span>
                                                <span class="hide-menu">{{ $submenu->name }}</span>
                                            </a>
                                        </li>
                                    @endcan
                                @endforeach
                            </ul>
                        </li>
                    @endcan
                @else
                    @can('read ' . $menu->url)
                        <li class="sidebar-item {{ $isMenuActive ? 'selected' : '' }}">
                            <a class="sidebar-link {{ $isMenuActive ? 'active' : '' }}" href="{{ url($menu->url) }}">
                                <span class="menu-icon-wrap"><i class="{{ $menu->icon }}"></i></span>
                                <span class="hide-menu">{{ $menu->name }}</span>
                            </a>
                        </li>
                    @endcan
                @endif
            @endforeach
        </ul>
    </nav>
</div>