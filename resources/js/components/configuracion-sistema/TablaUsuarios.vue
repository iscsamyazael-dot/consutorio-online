<template>
    <!-- HEADER -->
    <div class="card-header bg-transparent border-0 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="fas fa-users text-primary me-2"></i>
                    Gestión de Usuarios
                </h3>
                <small class="text-muted">
                    Administración y control de usuarios registrados
                </small>
            </div>
            <button
                class="btn btn-light border rounded-pill px-4 py-2 shadow-sm text-secondary fw-semibold"
                @click="$emit('volver')"
            >
                <i class="fas fa-arrow-left me-2"></i>
                Volver al registro
            </button>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body px-4">

                <!-- BARRA DE HERRAMIENTAS -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Usuarios registrados</h5>
                        <small class="text-muted">
                            {{ usuariosFiltrados.length }} usuario{{ usuariosFiltrados.length !== 1 ? 's' : '' }} encontrado{{ usuariosFiltrados.length !== 1 ? 's' : '' }}
                        </small>
                    </div>

                    <!-- BUSCADOR -->
                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="width:280px;">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="fas fa-search text-primary"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control border-0 ps-2"
                            placeholder="Buscar usuario..."
                            style="background:#f8fafc;"
                            v-model="busqueda"
                        >
                    </div>
                </div>

                <!-- ESTADO: CARGANDO -->
                <div v-if="cargando" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted small">Cargando usuarios...</p>
                </div>

                <!-- ESTADO: SIN RESULTADOS -->
                <div v-else-if="usuariosFiltrados.length === 0" class="text-center py-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                        style="width:64px;height:64px;background:#f1f5f9;">
                        <i class="fas fa-users-slash text-muted fs-4"></i>
                    </div>
                    <p class="fw-semibold text-dark mb-1">
                        {{ busqueda ? 'Sin coincidencias' : 'Aún no hay usuarios' }}
                    </p>
                    <small class="text-muted">
                        {{ busqueda ? `No se encontraron usuarios con "${busqueda}"` : 'Registra el primer usuario para comenzar.' }}
                    </small>
                </div>

                <!-- TABLA -->
                <div v-else class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small">
                                <th>Usuario</th>
                                <th>Correo electrónico</th>
                                <th>Rol</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="usuario in usuariosFiltrados" :key="usuario.id">

                                <!-- NOMBRE + AVATAR -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                            :style="`width:42px;height:42px;background:${rolConfig(usuario.rol).bg};color:${rolConfig(usuario.rol).color};font-size:0.8rem;`"
                                        >
                                            {{ iniciales(usuario.name) }}
                                        </div>
                                        <div>
                                            <strong>{{ usuario.name }}</strong>
                                            <small class="d-block text-muted">
                                                {{ usuario.activo ? 'Usuario activo' : 'Usuario inactivo' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- EMAIL -->
                                <td class="text-muted" style="font-size:0.875rem;">
                                    {{ usuario.email }}
                                </td>

                                <!-- ROL BADGE -->
                                <td>
                                    <span
                                        class="badge rounded-pill px-3 py-2"
                                        :style="`background:${rolConfig(usuario.rol).bg};color:${rolConfig(usuario.rol).color};font-size:0.75rem;`"
                                    >
                                        <i :class="`${rolConfig(usuario.rol).icono} me-1`"></i>
                                        {{ rolConfig(usuario.rol).label }}
                                    </span>
                                </td>

                                <!-- ACCIONES -->
                                <td class="text-center">
                                    <button
                                        class="btn btn-outline-danger btn-sm rounded-circle"
                                        title="Eliminar usuario"
                                        :disabled="eliminando === usuario.id"
                                        @click="confirmarEliminar(usuario)"
                                    >
                                        <span v-if="eliminando === usuario.id">
                                            <span class="spinner-border spinner-border-sm"></span>
                                        </span>
                                        <i v-else class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    emits: ['volver'],

    data() {
        return {
            usuarios: [],
            busqueda: '',
            cargando: false,
            eliminando: null, // id del usuario que se está eliminando
        }
    },

    computed: {
        usuariosFiltrados() {
            if (!this.busqueda.trim()) return this.usuarios

            const q = this.busqueda.toLowerCase()
            return this.usuarios.filter(u =>
                u.name.toLowerCase().includes(q) ||
                u.email.toLowerCase().includes(q) ||
                this.rolConfig(u.rol).label.toLowerCase().includes(q)
            )
        }
    },

    mounted() {
        this.cargarUsuarios()
    },

    methods: {
        async cargarUsuarios() {
            this.cargando = true
            try {
                const response = await ApiService.get('/usuarios')
                
                // Verificar qué está devolviendo exactamente
                console.log('Respuesta API:', response.data)

                // Manejar ambos casos: array directo o envuelto en objeto
                if (Array.isArray(response.data)) {
                    this.usuarios = response.data
                } else if (Array.isArray(response.data.data)) {
                    this.usuarios = response.data.data  // si viene paginado
                } else {
                    this.usuarios = []
                }

            } catch (error) {
                console.error(error)
                this.usuarios = [] // garantizar que siempre sea array
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los usuarios.',
                    confirmButtonText: 'Aceptar'
                })
            } finally {
                this.cargando = false
            }
        },

        async confirmarEliminar(usuario) {
            const resultado = await Swal.fire({
                icon: 'warning',
                title: '¿Eliminar usuario?',
                html: `Esta acción eliminará a <strong>${usuario.name}</strong> permanentemente.`,
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
            })

            if (!resultado.isConfirmed) return

            this.eliminando = usuario.id
            try {
                await ApiService.delete(`usuarios/${usuario.id}`)
                this.usuarios = this.usuarios.filter(u => u.id !== usuario.id)

                Swal.fire({
                    icon: 'success',
                    title: 'Usuario eliminado',
                    text: `${usuario.name} fue eliminado correctamente.`,
                    timer: 2000,
                    showConfirmButton: false
                })
            } catch (error) {
                console.error(error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo eliminar el usuario.',
                    confirmButtonText: 'Aceptar'
                })
            } finally {
                this.eliminando = null
            }
        },

        iniciales(nombre) {
            if (!nombre) return '?'
            return nombre
                .split(' ')
                .slice(0, 2)
                .map(p => p[0])
                .join('')
                .toUpperCase()
        },

        rolConfig(rol) {
            const config = {
                admin:     { label: 'Administrador', icono: 'fas fa-crown',        bg: '#eef2ff', color: '#4f46e5' },
                medico:    { label: 'Médico',         icono: 'fas fa-user-md',      bg: '#ecfeff', color: '#0891b2' },
                asistente: { label: 'Asistente',      icono: 'fas fa-calendar-alt', bg: '#ecfdf5', color: '#10b981' },
                farmacia:  { label: 'Farmacia',       icono: 'fas fa-pills',        bg: '#fffbeb', color: '#d97706' },
            }
            return config[rol] ?? { label: rol, icono: 'fas fa-user', bg: '#f1f5f9', color: '#64748b' }
        }
    }
}
</script>