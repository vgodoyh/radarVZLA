<div>
    <h4 class="mb-3">Dashboard</h4>

    <div class="card">
        <div class="card-body">
            Panel administrativo de Pulso Vzla
        </div>
    </div>

    @if (auth()->user()?->hasAnyRole(['admin', 'super-admin']))
        <section class="card admin-user-activity-card mt-4">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="mb-1">Usuarios conectados</h5>
                        <p class="text-muted text-sm mb-0">Actividad reciente del sistema</p>
                    </div>
                    <div class="admin-user-activity-card__summary">
                        <strong>{{ $onlineUsers }}</strong>
                        <span>usuarios conectados</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-items-center mb-0 admin-user-activity-card__table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Dispositivo</th>
                                <th>Última actividad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $activityUser)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $activityUser->name }}</td>
                                    <td>{{ $activityUser->getRoleNames()->first() ?? 'Sin rol' }}</td>
                                    <td>
                                        <span class="admin-user-activity-status {{ $activityUser->isOnline() ? 'is-online' : 'is-offline' }}">
                                            <span aria-hidden="true"></span>
                                            {{ $activityUser->isOnline() ? 'En línea' : 'Desconectado' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($activityUser->last_device_type)
                                            <span class="admin-user-activity-device">
                                                <i class="fa-solid {{ $activityUser->last_device_type === 'mobile' ? 'fa-mobile-screen' : ($activityUser->last_device_type === 'tablet' ? 'fa-tablet-screen-button' : 'fa-desktop') }}" aria-hidden="true"></i>
                                                <span>{{ ucfirst($activityUser->last_device_type) }}</span>
                                                <small>{{ $activityUser->last_platform }}</small>
                                            </span>
                                        @else
                                            <span class="text-muted">No registrado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($activityUser->last_activity_at)
                                            {{ $activityUser->isOnline() ? $activityUser->last_activity_at->diffForHumans() : $activityUser->last_activity_at->format('d/m/Y H:i') }}
                                        @else
                                            Nunca registrada
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay actividad registrada todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>
