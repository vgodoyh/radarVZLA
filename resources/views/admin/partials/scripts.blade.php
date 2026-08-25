@livewireScripts

<script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/argon-dashboard.js') }}"></script>

<script>
    (() => {
        const breakpoint = 1200;
        const body = document.body;
        const sidebar = document.getElementById('sidenav-main');
        const openButton = document.getElementById('iconNavbarSidenav');
        const closeButton = document.getElementById('iconSidenav');
        const overlay = document.getElementById('admin-sidenav-overlay');

        if (!sidebar || !openButton || !closeButton || !overlay) {
            return;
        }

        const syncOverlay = () => {
            const isOpen = window.innerWidth < breakpoint && body.classList.contains('g-sidenav-pinned');

            overlay.classList.toggle('is-visible', isOpen);
            overlay.setAttribute('aria-hidden', String(!isOpen));
        };

        openButton.addEventListener('click', syncOverlay);
        closeButton.addEventListener('click', syncOverlay);
        overlay.addEventListener('click', () => {
            body.classList.remove('g-sidenav-pinned');
            syncOverlay();
        });

        window.addEventListener('resize', () => {
            body.classList.remove('g-sidenav-pinned', 'g-sidenav-hidden');
            syncOverlay();
        });

        syncOverlay();
    })();
</script>

<script src="https://kit.fontawesome.com/46cce8fd62.js" crossorigin="anonymous"></script>
