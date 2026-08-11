<template>
    <header class="topbar">
        <!-- ==========================================
             IDENTIDAD / RUTA
        =========================================== -->
        <div class="brand">

            <div class="mark">
                MO
            </div>
            <div class="brand-info">
                <span class="brand-title">
                    Panel Super-admin
                </span>
                <span class="brand-separator">
                    /
                </span>
                <span class="brand-section">
                    Gestión de clientes
                </span>
            </div>
        </div>


        <!-- ==========================================
             ACCIONES DEL ADMINISTRADOR
        =========================================== -->
        <div class="topbar-actions">
            <!-- BÚSQUEDA -->
            <button 
                type="button"
                class="icon-btn"
                aria-label="Buscar"
                title="Buscar">
                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"/>
                    <path
                        d="M21 21l-4.3-4.3"/>
                </svg>
            </button>

            <!-- NOTIFICACIONES -->
            <button
                type="button"
                class="icon-btn notification-btn"
                aria-label="Notificaciones"
                title="Notificaciones">
                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2">
                    <path
                        d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/>
                    <path
                        d="M10 20a2 2 0 0 0 4 0"/>
                </svg>
                <span class="notification-dot"></span>
            </button>

            <!-- SEPARADOR -->
            <span class="topbar-divider"></span>

            <!-- PERFIL -->
            <div class="user-menu">
                <button
                    type="button"
                    class="avatar-chip"
                    aria-label="Perfil del administrador"
                    @click="menuAbierto = !menuAbierto">
                    <div class="dot">
                        AT
                    </div>
                    <div class="avatar-info">
                        <span class="avatar-name">
                            Admin Test
                        </span>
                        <span class="avatar-role">
                            Super administrador
                        </span>
                    </div>
                    <svg
                        class="avatar-arrow"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>

                <div v-if="menuAbierto" class="user-dropdown">
                    <button type="button" class="dropdown-item" @click="cerrarSesion">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <path d="M16 17l5-5-5-5M21 12H9" />
                        </svg>
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>


<script>
export default {

    name: 'HeaderBar',

    data() {
        return {
            menuAbierto: false
        }
    },

    methods: {
        cerrarSesion() {
            const form = document.createElement('form')
            form.method = 'POST'
            form.action = '/super-admin/logout'

            const token = document.createElement('input')
            token.type = 'hidden'
            token.name = '_token'
            token.value = document.querySelector('meta[name="csrf-token"]').content
            form.appendChild(token)

            document.body.appendChild(form)
            form.submit()
        }
    }

}
</script>