<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">

        <nav class="admin-topbar__navigation" aria-label="Navegación principal">
            <button type="button" class="admin-sidenav__toggle" id="iconNavbarSidenav" aria-label="Abrir menú" aria-controls="sidenav-main">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            
        </nav>

        <ul class="navbar-nav ms-auto justify-content-end">
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
                <a href="#" class="nav-link text-secondary px-0 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-user me-sm-1"></i>
                    <span class="d-sm-inline d-none">{{ auth()->user()->name }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end px-2 py-3">
                    <li>
                        <a href="{{ route('admin.profile.edit') }}" class="dropdown-item border-radius-md">
                            Perfil
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item border-radius-md">
                                Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>

    </div>
</nav>
