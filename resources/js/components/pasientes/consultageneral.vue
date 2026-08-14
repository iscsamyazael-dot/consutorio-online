<template>
    <div class="col-12">

        <div class="glass-card horizontal-profile-card">

            <!-- IZQUIERDA: Avatar + Título -->
            <div class="profile-main-section">

                <div class="patient-avatar-horizontal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12z" stroke-linecap="round"/>
                        <path d="M3.6 21.6c0-4.6 3.8-8.4 8.4-8.4s8.4 3.8 8.4 8.4" stroke-linecap="round"/>
                    </svg>
                    <div class="avatar-ring"></div>
                    <div class="avatar-pulse"></div>
                </div>

                <div class="profile-text-horizontal">
                    <div class="badge-chip">
                        <span class="chip-dot"></span>
                        Consulta General
                    </div>
                    <h3 class="profile-title">Registro médico inteligente</h3>
                    <p class="profile-subtitle">Monitoreo clínico en tiempo real</p>
                </div>

            </div>

            <!-- DIVISOR -->
            <div class="vdivider"></div>

            <!-- STATUS -->
            <div class="status-container-horizontal">

                <div class="status-item-horizontal">
                    <div class="status-icon green">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="status-text">
                        <span>Estado</span>
                        <h6>Consulta activa</h6>
                    </div>
                </div>

                <div class="status-item-horizontal">
                    <div class="status-icon orange">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="status-text">
                        <span>Prioridad</span>
                        <h6>Moderada</h6>
                    </div>
                </div>

            </div>

            <!-- DIVISOR -->
            <div class="vdivider"></div>

            <!-- INFO ITEMS -->
            <div class="info-box-horizontal">

                <div class="info-item">
                    <div class="info-icon blue">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="info-text">
                        <small>Médico</small>
                        <!-- FIX: antes "Dr. Martínez" fijo. Ahora se usa el
                             nombre real del usuario logueado, obtenido de
                             GET /perfil-usuario (ProfileController@obtenerPerfil,
                             que devuelve Auth::user()). -->
                        <h6>{{ medicoNombre || 'Cargando...' }}</h6>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon green">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="info-text">
                        <small>Fecha</small>
                        <!-- FIX: antes "22 Mayo 2026" fijo. Ahora se usa la
                             fecha real de hoy (no depende del backend). -->
                        <h6>{{ fechaHoy }}</h6>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon red">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="info-text">
                        <small>Estado clínico</small>
                        <h6>En evaluación</h6>
                    </div>
                </div>

            </div>

        </div>

    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    name: 'HorizontalProfileCard',
    data() {
        return {
            medicoNombre: '',
            fechaHoy: ''
        }
    },
    mounted() {
        this.obtenerMedicoLogueado()
        this.fechaHoy = this.formatearFechaHoy()
    },
    methods: {
        // Trae el usuario autenticado real desde el backend
        // (GET /perfil-usuario -> ProfileController@obtenerPerfil).
        async obtenerMedicoLogueado() {
            try {
                const response = await ApiService.get('/perfil-usuario')
                // obtenerPerfil() devuelve el modelo User completo
                // (Auth::user()); solo usamos el nombre aquí.
                this.medicoNombre = response.data.name || 'Sin nombre registrado'
            } catch (error) {
                console.error('Error al obtener el usuario logueado:', error)
                this.medicoNombre = 'No disponible'
            }
        },
        // Fecha real de hoy, formateada igual que el resto del sistema
        // (ej. "22 de mayo de 2026" -> se ajusta a "22 Mayo 2026" para
        // mantener el mismo formato visual que tenía el dato estático).
        formatearFechaHoy() {
            const hoy = new Date()
            const dia = hoy.getDate()
            const mes = hoy.toLocaleDateString('es-MX', { month: 'long' })
            const anio = hoy.getFullYear()
            const mesCapitalizado = mes.charAt(0).toUpperCase() + mes.slice(1)
            return `${dia} ${mesCapitalizado} ${anio}`
        }
    }
}
</script>

<style scoped>

/* ══════════════════════════════
   CARD BASE
══════════════════════════════ */
.horizontal-profile-card {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 22px 28px;
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
    border: 1px solid #f0f4ff;
    position: relative;
    overflow: hidden;
    animation: cardIn .55s cubic-bezier(.22,1,.36,1) both;
}

/* Decorative top-left gradient splash */
.horizontal-profile-card::before {
    content: '';
    position: absolute;
    top: -40px;
    left: -40px;
    width: 160px;
    height: 160px;
    background: radial-gradient(circle, rgba(37,99,235,.08) 0%, transparent 70%);
    pointer-events: none;
}

/* ══════════════════════════════
   AVATAR
══════════════════════════════ */
.profile-main-section {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-shrink: 0;
    animation: fadeRight .5s .1s cubic-bezier(.22,1,.36,1) both;
}

.patient-avatar-horizontal {
    position: relative;
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.patient-avatar-horizontal svg {
    width: 30px;
    height: 30px;
    color: #2563eb;
    position: relative;
    z-index: 2;
}

.avatar-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
    border: 2px solid #bfdbfe;
    z-index: 1;
}

.avatar-pulse {
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    border: 2px solid rgba(37,99,235,.2);
    animation: avatarPulse 2.2s ease-out infinite;
}

@keyframes avatarPulse {
    0%   { transform: scale(1);   opacity: .6; }
    100% { transform: scale(1.35); opacity: 0; }
}

/* ══════════════════════════════
   PROFILE TEXT
══════════════════════════════ */
.badge-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #2563eb;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 6px;
}

.chip-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #2563eb;
    animation: chipBlink 1.8s ease-in-out infinite;
}

@keyframes chipBlink {
    0%, 100% { opacity: 1; }
    50%       { opacity: .3; }
}

.profile-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
    letter-spacing: -.2px;
    line-height: 1.2;
}

.profile-subtitle {
    font-size: .8rem;
    color: #6b7280;
    margin: 3px 0 0;
}

/* ══════════════════════════════
   VERTICAL DIVIDER
══════════════════════════════ */
.vdivider {
    width: 1px;
    height: 60px;
    background: linear-gradient(to bottom, transparent, #e5e7eb, transparent);
    margin: 0 24px;
    flex-shrink: 0;
    animation: dividerGrow .6s .2s cubic-bezier(.22,1,.36,1) both;
}

@keyframes dividerGrow {
    from { transform: scaleY(0); opacity: 0; }
    to   { transform: scaleY(1); opacity: 1; }
}

/* ══════════════════════════════
   STATUS ITEMS
══════════════════════════════ */
.status-container-horizontal {
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex-shrink: 0;
    animation: fadeRight .5s .25s cubic-bezier(.22,1,.36,1) both;
}

.status-item-horizontal {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-text span {
    font-size: .7rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    display: block;
}

.status-text h6 {
    font-size: .85rem;
    font-weight: 700;
    color: #111827;
    margin: 1px 0 0;
}

/* ══════════════════════════════
   INFO ITEMS
══════════════════════════════ */
.info-box-horizontal {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    animation: fadeRight .5s .35s cubic-bezier(.22,1,.36,1) both;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    padding: 10px 14px;
    border-radius: 14px;
    background: #f9fafb;
    border: 1px solid #f3f4f6;
    transition: background .2s, transform .2s, box-shadow .2s;
    cursor: default;
}

.info-item:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.07);
}

.info-text small {
    font-size: .68rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    display: block;
}

.info-text h6 {
    font-size: .85rem;
    font-weight: 700;
    color: #111827;
    margin: 2px 0 0;
}

/* ══════════════════════════════
   SHARED ICON STYLES
══════════════════════════════ */
.status-icon,
.info-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform .2s;
}

.status-icon svg,
.info-icon svg {
    width: 17px;
    height: 17px;
}

.info-item:hover .info-icon {
    transform: scale(1.12) rotate(-4deg);
}

/* Colors */
.status-icon.green, .info-icon.green {
    background: #dcfce7;
    color: #16a34a;
}
.status-icon.orange, .info-icon.orange {
    background: #fff7ed;
    color: #ea580c;
}
.info-icon.blue {
    background: #dbeafe;
    color: #2563eb;
}
.info-icon.red {
    background: #fee2e2;
    color: #dc2626;
    animation: heartbeat 1.6s ease-in-out infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    14%       { transform: scale(1.15); }
    28%       { transform: scale(1); }
    42%       { transform: scale(1.1); }
    70%       { transform: scale(1); }
}

/* ══════════════════════════════
   ENTRANCE ANIMATIONS
══════════════════════════════ */
@keyframes cardIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes fadeRight {
    from { opacity: 0; transform: translateX(-10px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ══════════════════════════════
   RESPONSIVE
══════════════════════════════ */
@media (max-width: 768px) {
    .horizontal-profile-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .vdivider {
        width: 100%;
        height: 1px;
        margin: 0;
        background: linear-gradient(to right, transparent, #e5e7eb, transparent);
    }

    .info-box-horizontal {
        flex-direction: column;
        width: 100%;
    }

    .info-item {
        width: 100%;
    }
}
</style>