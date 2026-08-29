<div class="admin-sidenav-overlay" id="admin-sidenav-overlay" aria-hidden="true"></div>

<aside class="sidenav admin-sidenav bg-dark navbar navbar-vertical navbar-expand-xs border-0 fixed-start" id="sidenav-main">
    <button type="button" class="admin-sidenav__close" id="iconSidenav" aria-label="Cerrar menú">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
    @php($isGeneralAdmin = auth()->user()?->hasAnyRole(['admin', 'super-admin']))

    @if ($isGeneralAdmin)
        <div class="sidenav-header">
            <a class="navbar-brand m-0 text-light d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-house text-teal"></i>
                <span class="text-teal">Inicio</span>
            </a>
        </div>

        <hr class="horizontal light mt-0">
    @endif

    <div class="navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">

            @if ($isGeneralAdmin || auth()->user()?->can('publish content'))
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-file-circle-plus text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">Publicar</span>
                    </a>
                </li>
            @endif

            @if ($isGeneralAdmin || auth()->user()?->can('view reports'))
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-chart-line text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">Informes</span>
                    </a>
                </li>
            @endif

            {{--<li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white opacity-6">
                    Gestión del Sistema
                </h6>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white {{ request()->routeIs('emisor.*') || request()->routeIs('tipo_emisor.*') || request()->routeIs('tipo_red_social.*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse"
                href="#submenu-emisor"
                aria-expanded="{{ request()->routeIs('emisor.*') || request()->routeIs('tipo_emisor.*') || request()->routeIs('tipo_red_social.*') ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-bullhorn text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Emisor</span>
                </a>

                <div class="collapse {{ request()->routeIs('emisor.*') || request()->routeIs('tipo_emisor.*') || request()->routeIs('tipo_red_social.*') ? 'show' : '' }}"
                    id="submenu-emisor">
                    <ul class="nav ms-4">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('emisor.*') ? 'active' : '' }}" href="{{ route('emisor.index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-circle" style="font-size:5px;"></i></span>
                                <span class="sidenav-normal">Emisores</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tipo_emisor.*') ? 'active' : '' }}" href="{{ route('tipo_emisor.index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-circle" style="font-size:5px;"></i></span>
                                <span class="sidenav-normal">Tipos de emisor</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tipo_red_social.*') ? 'active' : '' }}" href="{{ route('tipo_red_social.index') }}">
                                <span class="sidenav-mini-icon"><i class="fas fa-circle" style="font-size:5px;"></i></span>
                                <span class="sidenav-normal">Tipos de red social</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li> --}}

            @if ($isGeneralAdmin || auth()->user()?->canAny(['view jep dashboard', 'view acceso justicia dashboard', 'view ovfn dashboard', 'view obu dashboard']))
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white opacity-6">
                        Organizaciones
                    </h6>
                </li>
            @endif

            @can('view jep dashboard')
                <li class="nav-item">
                    <div class="nav-link text-white organization-link organization-link--jep {{ request()->routeIs('admin.jep.*') ? 'active' : '' }}">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-scale-balanced text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">JEP</span>
                    </div>
                </li>
            @endcan

            @can('view acceso justicia dashboard')
                <li class="nav-item">
                    <a class="nav-link text-white organization-link organization-link--access {{ request()->routeIs('admin.acceso-justicia.*') ? 'active' : '' }}" href="{{ route('admin.acceso-justicia.index') }}" @if(request()->routeIs('admin.acceso-justicia.*')) aria-current="page" @endif>
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-gavel text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">Acceso a la Justicia</span>
                    </a>
                </li>
            @endcan

            @can('view ovfn dashboard')
                <li class="nav-item">
                    <a class="nav-link text-white organization-link organization-link--ovfn {{ request()->routeIs('admin.ovfn.*') ? 'active' : '' }}" href="{{ route('admin.ovfn.index') }}" @if(request()->routeIs('admin.ovfn.*')) aria-current="page" @endif>
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-circle-check text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">OVFN</span>
                    </a>
                </li>
            @endcan

            @can('view obu dashboard')
                <li class="nav-item">
                    <div class="nav-link text-white organization-link organization-link--obu {{ request()->routeIs('admin.obu.*') ? 'active' : '' }}">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-graduation-cap text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">OBU</span>
                    </div>
                </li>
            @endcan

            @if ($isGeneralAdmin || auth()->user()?->canAny(['manage permissions', 'manage roles', 'manage users']))
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder text-white opacity-6">
                        Seguridad del Sistema
                    </h6>
                </li>
            @endif

            @if ($isGeneralAdmin || auth()->user()?->can('manage permissions'))
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{route('permission.index')}}">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-unlock text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">Permisos</span>
                    </a>
                </li>
            @endif

            @if ($isGeneralAdmin || auth()->user()?->can('manage roles'))
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{route('role.index')}}">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-gear text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">Roles</span>
                    </a>
                </li>
            @endif

            @if ($isGeneralAdmin || auth()->user()?->can('manage users'))
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{route('user.index')}}">
                        <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-users text-sm text-gray-600"></i>
                        </div>
                        <span class="nav-link-text ms-1">Usuarios</span>
                    </a>
                </li>
            @endif

        </ul>
    </div>

    <div class="sidenav-footer mx-2">
        <div class="card card-plain shadow-none">
            <img class="w-50 mx-auto" src="{{ asset('assets/img/pulso-venezuela-color.png') }}" alt="Pulso Venezuela">

            <div class="card-body text-center p-3 w-100 pt-0">
                <p class="mt-2 mb-0 text-xs text-white">
                    © {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</aside>
