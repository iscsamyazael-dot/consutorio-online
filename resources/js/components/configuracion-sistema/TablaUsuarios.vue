<template>
    <!-- HEADER -->
    <div class="card-header bg-light border-0 pt-3 px-4 pb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1 d-flex align-items-center">
                    <i class="fas fa-users text-primary me-2"></i>
                    Gestión de Usuarios
                </h3>
                <small class="text-muted d-block ps-1">
                    Administración y control de usuarios registrados
                </small>
            </div>
            <button
                class="btn btn-light border rounded-pill px-4 py-2 shadow-sm text-secondary fw-semibold transition-all"
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

                                <!-- ACCIONES CENTRADAS -->
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <button
                                            class="btn btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarUsuario"
                                            data-toggle="modal"
                                            data-target="#modalEditarUsuario"
                                            @click="abrirModalEditar(usuario)"
                                            title="Editar usuario"
                                        >
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <button
                                            class="btn btn-action-delete"
                                            title="Eliminar usuario"
                                            :disabled="eliminando === usuario.id"
                                            @click="confirmarEliminar(usuario)"
                                        >
                                            <span v-if="eliminando === usuario.id">
                                                <span class="spinner-border spinner-border-sm"></span>
                                            </span>
                                            <i v-else class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR USUARIO -->
    <div
        class="modal fade"
        id="modalEditarUsuario"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>
                        Editar Usuario
                    </h5>
                </div>

                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="avatar-edit">
                            {{ inicialesUsuario }}
                        </div>

                        <h4 class="mt-3 mb-0">
                            {{ formEditar.name }}
                        </h4>
                        <span class="badge bg-info mt-2">
                            <i class="fas fa-user-tag me-1"></i>
                            {{ formEditar.rol }}
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Nombre</label>
                            <input
                                class="form-control"
                                v-model="formEditar.name"
                                readonly
                            >
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label">Rol</label>
                            <input
                                class="form-control"
                                v-model="formEditar.rol"
                                readonly
                            >
                        </div>

                        <div class="col-12 mb-4">
                            <label class="form-label">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input
                                    class="form-control"
                                    type="email"
                                    v-model="formEditar.email"
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nueva contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input
                                    class="form-control"
                                    type="password"
                                    v-model="formEditar.password"
                                    placeholder="Dejar vacío para no cambiar"
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input
                                    class="form-control"
                                    type="password"
                                    v-model="formEditar.password_confirmation"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <!-- Botón oculto para simular el cierre desde JS -->
                    <button
                        type="button"
                        ref="btnCerrarModal"
                        class="d-none"
                        data-bs-dismiss="modal"
                        data-dismiss="modal"
                    ></button>

                    <button
                        type="button"
                        class="btn btn-secondary px-4 rounded-pill"
                        data-bs-dismiss="modal"
                        data-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary px-4 rounded-pill"
                        @click="actualizarUsuario"
                    >
                        <i class="fas fa-save me-2"></i>
                        Guardar Cambios
                    </button>
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
            eliminando: null,

            formEditar: {
                id: null,
                name: '',
                email: '',
                rol: '',
                password: '',
                password_confirmation: ''
            }
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
        },

        inicialesUsuario() {
            if (!this.formEditar.name) return ''

            return this.formEditar.name
                .split(' ')
                .map(p => p[0])
                .join('')
                .substring(0, 2)
                .toUpperCase()
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
                if (Array.isArray(response.data)) {
                    this.usuarios = response.data
                } else if (Array.isArray(response.data.data)) {
                    this.usuarios = response.data.data
                } else {
                    this.usuarios = []
                }
            } catch (error) {
                console.error(error)
                this.usuarios = []
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
                confirmButtonColor: '#ef4444'
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
                farmacia:  { label: 'Farmacia',       icono: 'fas fa-pills',        bg: '#fffbeb', color: '#d97706' }
            }
            return config[rol] ?? { label: rol, icono: 'fas fa-user', bg: '#f1f5f9', color: '#64748b' }
        },

        abrirModalEditar(usuario) {
            this.formEditar = {
                id: usuario.id,
                name: usuario.name,
                email: usuario.email,
                rol: usuario.rol,
                password: '',
                password_confirmation: ''
            }
        },

        cerrarModal() {
            if (this.$refs.btnCerrarModal) {
                this.$refs.btnCerrarModal.click()
            }
        },

        async actualizarUsuario() {
            if (
                this.formEditar.password &&
                this.formEditar.password !== this.formEditar.password_confirmation
            ) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Las contraseñas no coinciden'
                })
                return
            }

            try {
                await ApiService.put(`usuarios/${this.formEditar.id}`, this.formEditar)

                const usuario = this.usuarios.find(u => u.id === this.formEditar.id)
                if (usuario) {
                    usuario.email = this.formEditar.email
                }

                this.cerrarModal()

                Swal.fire({
                    icon: 'success',
                    title: 'Usuario actualizado',
                    timer: 1800,
                    showConfirmButton: false
                })
            } catch (error) {
                console.error(error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el usuario.'
                })
            }
        }
    }
}
</script>

<style scoped>
.avatar-edit {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #eef6ff;
    border: 4px solid #d7eaff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: bold;
    color: #0d6efd;
    margin: auto;
}

/* BOTONES CENTRADOS Y ALINEADOS */
.btn-edit,
.btn-action-delete {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all .25s ease;
}

.btn-edit {
    border: 2px solid #0d6efd;
    color: #0d6efd;
    background: white;
}

.btn-edit:hover {
    background: #0d6efd;
    color: white;
    transform: scale(1.08);
}

.btn-action-delete {
    border: 2px solid #dc3545;
    color: #dc3545;
    background: white;
}

.btn-action-delete:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.08);
}
</style>