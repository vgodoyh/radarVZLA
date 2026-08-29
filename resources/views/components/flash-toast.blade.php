@php
    $toasts = array_values(array_filter($toasts ?? []));
@endphp

@if (count($toasts))
    <div class="admin-toast-container" aria-live="polite" aria-atomic="true">
        @foreach ($toasts as $toast)
            <div class="admin-toast admin-toast--{{ $toast['type'] ?? 'info' }}" role="status" data-flash-toast>
                <span class="admin-toast__icon" aria-hidden="true">{{ ($toast['type'] ?? 'info') === 'success' ? '✓' : 'i' }}</span>
                <span class="admin-toast__message">{{ $toast['message'] }}</span>
                <button class="admin-toast__close" type="button" aria-label="Cerrar" data-flash-toast-close>&times;</button>
            </div>
        @endforeach
    </div>

    <style>
        .admin-toast-container { position: fixed; top: 20px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; width: min(380px, calc(100vw - 32px)); pointer-events: none; }
        .admin-toast { align-items: center; display: flex; gap: 10px; min-height: 52px; padding: 13px 14px; border: 1px solid; border-radius: 10px; box-shadow: 0 10px 28px rgba(31, 45, 61, .16); font-size: 13px; line-height: 1.35; pointer-events: auto; opacity: 1; transform: translateY(0); animation: admin-toast-in .25s ease both; transition: opacity .25s ease, transform .25s ease; }
        @keyframes admin-toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .admin-toast.is-leaving { opacity: 0; transform: translateY(-8px); }
        .admin-toast--success { background: #edf9f0; border-color: #b9e4c2; color: #24743b; }
        .admin-toast--info { background: #f2f6fa; border-color: #dce5ef; color: #56667b; }
        .admin-toast__icon { align-items: center; border: 1px solid currentColor; border-radius: 50%; display: inline-flex; flex: 0 0 20px; font-size: 13px; font-weight: 700; height: 20px; justify-content: center; width: 20px; }
        .admin-toast__message { flex: 1; }
        .admin-toast__close { background: transparent; border: 0; color: currentColor; cursor: pointer; font-size: 22px; line-height: 1; opacity: .7; padding: 0 2px; }
        .admin-toast__close:hover, .admin-toast__close:focus { opacity: 1; }
        @media (max-width: 767.98px) { .admin-toast-container { top: 12px; left: 16px; right: 16px; width: auto; } }
    </style>
    <script>
        (() => {
            const removeToast = (toast) => {
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 250);
            };
            document.querySelectorAll('[data-flash-toast]').forEach((toast) => {
                toast.querySelector('[data-flash-toast-close]')?.addEventListener('click', () => removeToast(toast));
                window.setTimeout(() => removeToast(toast), 4000);
            });
        })();
    </script>
@endif
