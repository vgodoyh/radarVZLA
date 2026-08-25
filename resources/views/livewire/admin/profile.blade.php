<div class="admin-profile">
    <header class="admin-profile__header">
        <div>
            <h1>Mi perfil</h1>
            <p>Actualizá tus datos personales y credenciales de acceso.</p>
        </div>
    </header>

    @if (session('status'))
        <div class="alert alert-success admin-profile__alert" role="status">
            {{ session('status') }}
        </div>
    @endif

    <div class="admin-profile__cards">

        <section class="card admin-profile__card mb-4">
            <div class="card-body">
                <h3>Información personal</h3>

                <form wire:submit="updateProfile">
                    <div class="mb-3">
                        <label for="profile-name" class="form-label">Nombre</label>
                        <input
                            id="profile-name"
                            type="text"
                            wire:model="name"
                            class="form-control @error('name') is-invalid @enderror"
                            required
                            maxlength="255"
                            autocomplete="name"
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="profile-email" class="form-label">Correo electrónico</label>
                        <input
                            id="profile-email"
                            type="email"
                            wire:model="email"
                            class="form-control @error('email') is-invalid @enderror"
                            required
                            maxlength="255"
                            autocomplete="email"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="updateProfile">Guardar cambios</span>
                        <span wire:loading wire:target="updateProfile">Guardando...</span>
                    </button>
                </form>
            </div>
        </section>

        <section class="card admin-profile__card">
            <div class="card-body">
                <h3>Cambiar contraseña</h3>

                <form wire:submit="updatePassword">
                    <div class="mb-3">
                        <label for="current-password" class="form-label">Contraseña actual</label>
                        <input
                            id="current-password"
                            type="password"
                            wire:model="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            required
                            autocomplete="current-password"
                        >
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new-password" class="form-label">Nueva contraseña</label>
                        <input
                            id="new-password"
                            type="password"
                            wire:model="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                            autocomplete="new-password"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirmation" class="form-label">Confirmar nueva contraseña</label>
                        <input
                            id="password-confirmation"
                            type="password"
                            wire:model="password_confirmation"
                            class="form-control"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="updatePassword">Actualizar contraseña</span>
                        <span wire:loading wire:target="updatePassword">Actualizando...</span>
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
