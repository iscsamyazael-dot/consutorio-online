<template>
    <div class="card border-0 shadow-sm rounded-4 mt-4 bg-white">
        <div class="card-body p-4">

            <!-- BUSCADOR -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar paciente..."
                        v-model="buscar"
                    >
                </div>
            </div>

            <!-- TABLA -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Teléfono</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <tr v-for="paciente in pacientesFiltrados" :key="paciente.id">

                            <!-- PACIENTE -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div
                                        class="avatar-circle"
                                        :style="{ background: obtenerColor(paciente.nombre) }"
                                    >
                                        {{ (paciente.nombre || 'P').substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">
                                            {{ paciente.nombre }} {{ paciente.apellido_paterno }} {{ paciente.apellido_materno }}
                                        </h6>
                                        <small class="text-muted">
                                            FOLIO: {{ paciente.paciente_id }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- TELÉFONO -->
                            <td>{{ paciente.telefono }}</td>

                            <!-- EDAD -->
                            <td>{{ paciente.edad }} años</td>

                            <!-- ESTADO -->
                            <td>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                    {{ paciente.estado }}
                                </span>
                            </td>

                            <!-- ACCIONES -->
                            <td class="text-end">

                                <!-- ✅ ALERTA DATOS FALTANTES — ya NO abre modal, redirige al registro -->
                                <span
                                    v-if="tieneDatosFaltantes(paciente)"
                                    class="alert-indicator me-2"
                                    :title="'Faltan: ' + datosFaltantes(paciente).join(', ')"
                                    @click="irARegistroConDatos(paciente)"
                                    style="cursor:pointer"
                                >
                                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                                </span>

                                <!-- VER -->
                                <button
                                    class="btn btn-light btn-sm action-btn me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#verpacienteModal"
                                    @click="obtenerDetallePaciente(paciente.id)"
                                >
                                    <i class="fas fa-eye text-primary"></i>
                                </button>

                                <!-- EXPEDIENTE -->
                                <a
                                    class="btn btn-light btn-sm action-btn me-2"
                                    :href="'ExpedientePacientes/' + paciente.id"
                                >
                                    <i class="fas fa-folder-open text-info"></i>
                                </a>

                                <!-- EDITAR -->
                                <button
                                    class="btn btn-light btn-sm action-btn me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editarpacienteModal"
                                    @click="verModificarPacientes(paciente.id)"
                                >
                                    <i class="fas fa-edit text-warning"></i>
                                </button>

                                <!-- ELIMINAR -->
                                <button
                                    class="btn btn-light btn-sm action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#eliminarpacienteModal"
                                    @click="pacienteSeleccionado = paciente.id"
                                >
                                    <i class="fas fa-trash text-danger"></i>
                                </button>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- ===== MODAL VER PACIENTE ===== -->
    <div class="modal fade" id="verpacienteModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-circle me-2"></i>
                        Información del Paciente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="patient-profile mb-4">
                        <div class="avatar-large">
                            {{ detallePaciente.nombre?.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="fw-bold mb-1">{{ detallePaciente.nombre }}</h3>
                            <span class="badge bg-success">Consulta activa</span>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <label>Teléfono</label>
                                <h6>{{ detallePaciente.telefono }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <label>Sexo</label>
                                <h6>{{ detallePaciente.sexo }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <label>Tipo de Sangre</label>
                                <h6>{{ detallePaciente.tipo_sangre }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <label>Alergias</label>
                                <h6 v-if="detallePaciente.alergias && detallePaciente.alergias.trim()">
                                    {{ detallePaciente.alergias }}
                                </h6>
                                <h6 v-else>Sin Alergias</h6>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-card">
                                <label>Dirección</label>
                                <h6>{{ detallePaciente.direccion }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL EDITAR PACIENTE ===== -->
    <div class="modal fade" id="editarpacienteModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-warning border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-edit me-2"></i>
                        Editar Paciente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user text-warning me-1"></i>Nombre
                                </label>
                                <input type="text" class="form-control" v-model="editarPaciente.nombre">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone text-warning me-1"></i>Teléfono
                                </label>
                                <input type="text" class="form-control" v-model="editarPaciente.telefono">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-birthday-cake text-warning me-1"></i>Edad
                                </label>
                                <input type="number" class="form-control" v-model="editarPaciente.edad">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-venus-mars text-warning me-1"></i>Sexo
                                </label>
                                <select class="form-control" v-model="editarPaciente.sexo">
                                    <option value="">-- Seleccionar --</option>
                                    <option>Masculino</option>
                                    <option>Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope text-warning me-1"></i>Email
                                </label>
                                <input type="email" class="form-control" v-model="editarPaciente.email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-id-card text-warning me-1"></i>CURP
                                </label>
                                <input type="text" class="form-control" v-model="editarPaciente.curp">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-tint text-warning me-1"></i>Tipo de Sangre
                                </label>
                                <select class="form-control" v-model="editarPaciente.tipo_sangre">
                                    <option value="">-- Seleccionar --</option>
                                    <option>A+</option>
                                    <option>A-</option>
                                    <option>B+</option>
                                    <option>B-</option>
                                    <option>AB+</option>
                                    <option>AB-</option>
                                    <option>O+</option>
                                    <option>O-</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-heart-pulse text-warning me-1"></i>Estado
                                </label>
                                <select class="form-control" v-model="editarPaciente.estado">
                                    <option>Consulta activa</option>
                                    <option>Paciente activo</option>
                                    <option>Pendiente</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-warning" @click="guardarCambiosPaciente()">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL ELIMINAR PACIENTE ===== -->
    <div class="modal fade" id="eliminarpacienteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-trash me-2"></i>
                        Eliminar Paciente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="delete-icon mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="fw-bold mb-2">¿Estás seguro de eliminar este paciente?</h5>
                    <p class="text-muted mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" @click="eliminarPaciente()">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    data() {
        return {
            pacientes: [],
            detallePaciente: [],
            editarPaciente: {},
            buscar: '',
            pacienteSeleccionado: '',
            form: {
                paciente_id: '',
                codigo_paciente: ''
            }
        }
    },

    computed: {
        pacientesFiltrados() {
            if (!this.buscar.trim()) return this.pacientes;
            const q = this.buscar.toLowerCase();
            return this.pacientes.filter(p =>
                (p.nombre           || '').toLowerCase().includes(q) ||
                (p.apellido_paterno || '').toLowerCase().includes(q) ||
                (p.apellido_materno || '').toLowerCase().includes(q) ||
                (p.telefono         || '').toLowerCase().includes(q) ||
                String(p.paciente_id || '').toLowerCase().includes(q)
            );
        }
    },

    mounted() {
        this.obtenerPacientes();
    },

    methods: {

        // ─── DATOS FALTANTES ──────────────────────────────────────────────────────

        datosFaltantes(paciente) {
            if (!paciente || !paciente.nombre) return [];
            const campos = [
                { campo: 'email',       etiqueta: 'Email'          },
                { campo: 'sexo',        etiqueta: 'Sexo'           },
                { campo: 'tipo_sangre', etiqueta: 'Tipo de sangre' },
                { campo: 'curp',        etiqueta: 'CURP'           },
                { campo: 'edad',        etiqueta: 'Edad'           },
            ];
            return campos
                .filter(c => !paciente[c.campo] || String(paciente[c.campo]).trim() === '')
                .map(c => c.etiqueta);
        },

        tieneDatosFaltantes(paciente) {
            return this.datosFaltantes(paciente).length > 0;
        },

        // ─── ✅ NUEVO: Redirige al registro precargando datos del paciente ─────────

        irARegistroConDatos(paciente) {
                localStorage.setItem('pacientePrecargar', JSON.stringify(paciente));
                window.location.href = '/PacienteNuevo';
            },

        // ─── COLORES AVATAR ───────────────────────────────────────────────────────

       obtenerColor(nombre) {
    const colores = [
        "#1976d2",
        "#43a047",
        "#7b1fa2",
        "#ef6c00",
        "#00897b",
        "#c2185b",
        "#5d4037",
        "#546e7a"
    ];

    // Evitar error si nombre es null, undefined o vacío
    if (!nombre) {
        return "#6c757d";
    }
    nombre = String(nombre);
    let suma = 0;
    for (let i = 0; i < nombre.length; i++) {
        suma += nombre.charCodeAt(i);
    }
    return colores[suma % colores.length];
},
        // ─── FORMULARIO ───────────────────────────────────────────────────────────

        limpiarFormulario() {
            this.form = {
                nombre: '',
                apellido_paterno: '',
                apellido_materno: '',
                telefono: '',
                email: '',
                edad_anios: '',
                sexo: '',
                direccion: '',
                tipo_sangre: '',
                contacto_emergencia: '',
                telefono_emergencia: '',
                curp: '',
                notas_generales: '',
                fecha_nacimiento: '',
                presion_arterial: '',
                saturacion: '',
                temperatura: '',
                frecuencia_cardiaca: '',
                frecuencia_respiratoria: '',
                peso: '',
                talla: '',
                sintomas: '',
                motivo_consulta: ''
            };
        },

        // ─── CRUD PACIENTES ───────────────────────────────────────────────────────

        async guardarPaciente() {
            try {
                const response = await ApiService.post('/pacientes', this.form);
                console.log('Guardado:', response.data);
                Swal.fire({
                    icon: 'success',
                    title: 'Paciente registrado',
                    text: 'El paciente fue guardado exitosamente.',
                    confirmButtonText: 'Aceptar'
                });
                this.limpiarFormulario();
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al guardar el paciente.',
                    confirmButtonText: 'Aceptar'
                });
            }
        },

        async obtenerPacientes() {
            try {
                const response = await ApiService.get('/pacientes');
                // Asignas directamente la lista que te da el servidor
                this.pacientes = response.data;
                console.log('Pacientes cargados:', this.pacientes);
            } catch (error) {
                console.error('Error al obtener pacientes:', error);
            }
        },

        async obtenerDetallePaciente(id) {
            try {
                const response = await ApiService.get('/pacientes/' + id);
                this.detallePaciente = response.data;
            } catch (error) {
                console.error('Error al obtener detalle del paciente:', error);
            }
        },

        async verModificarPacientes(id) {
            try {
                const response = await ApiService.get('/pacientes/' + id);
                this.editarPaciente = {
                    nombre: '',
                    apellido_paterno: '',
                    apellido_materno: '',
                    telefono: '',
                    email: '',
                    edad: '',
                    sexo: '',
                    direccion: '',
                    tipo_sangre: '',
                    curp: '',
                    contacto_emergencia: '',
                    telefono_emergencia: '',
                    notas_generales: '',
                    estado: '',
                    ...response.data
                };
            } catch (error) {
                console.error('Error al editar paciente:', error);
            }
        },

        async guardarCambiosPaciente() {
            try {
                const response = await ApiService.put('/pacientes/' + this.editarPaciente.id, this.editarPaciente);
                console.log('Actualizado:', response.data);

                const index = this.pacientes.findIndex(p => p.id === this.editarPaciente.id);
                if (index !== -1) {
                    this.pacientes[index] = { ...this.pacientes[index], ...this.editarPaciente };
                    this.pacientes = [...this.pacientes];
                }

                document.getElementById('editarpacienteModal')
                    .querySelector('[data-bs-dismiss="modal"]').click();

                await Swal.fire({
                    icon: 'success',
                    title: 'Paciente actualizado',
                    text: 'Los cambios fueron guardados exitosamente.',
                    confirmButtonText: 'Aceptar'
                });

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al actualizar el paciente.',
                    confirmButtonText: 'Aceptar'
                });
            }
        },

        async eliminarPaciente() {
            try {
                await ApiService.delete('/pacientes/' + this.pacienteSeleccionado);

                this.pacientes = this.pacientes.filter(p => p.id !== this.pacienteSeleccionado);

                document.getElementById('eliminarpacienteModal')
                    .querySelector('[data-bs-dismiss="modal"]').click();

                await Swal.fire({
                    icon: 'success',
                    title: 'Paciente eliminado',
                    text: 'El paciente fue eliminado correctamente.',
                    confirmButtonText: 'Aceptar'
                });

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al eliminar el paciente.',
                    confirmButtonText: 'Aceptar'
                });
            }
        }
    }
}
</script>

<style>

.card {
    border-radius: 24px !important;
    overflow: hidden;
    background: white !important;
}

/* TABLA */
.table {
    margin-bottom: 0 !important;
}

.table thead th {
    border: none !important;
    padding: 18px !important;
    font-weight: 700 !important;
    color: #495057 !important;
    background: #eef0f3 !important;
    font-size: 15px !important;
}

.table tbody td {
    padding: 18px !important;
    vertical-align: middle !important;
    border-top: 1px solid #1d5994 !important;
}

.table-hover tbody tr:hover {
    background: #f8fbff !important;
    transition: .3s;
}

/* AVATAR */
.avatar-circle {
    width: 50px !important;
    height: 50px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: white !important;
    font-weight: bold !important;
    font-size: 20px !important;
    box-shadow: 0 5px 15px rgba(0,0,0,.15) !important;
    flex-shrink: 0;
}

/* BOTONES */
.action-btn {
    border-radius: 12px !important;
    transition: .3s !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.08) !important;
    width: 38px !important;
    height: 38px !important;
    border: none !important;
}

.action-btn:hover {
    transform: translateY(-3px);
}

.alert-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* ICONO LLAMATIVO */
.alert-icon {
    color: #dc3545;
    font-size: 18px;
    animation: pulseAlert 1.2s infinite;
    filter: drop-shadow(0 0 6px rgba(220, 53, 69, 0.6));
}

/* PULSO ROJO */
@keyframes pulseAlert {
    0%   { transform: scale(1);    opacity: 1;   }
    50%  { transform: scale(1.25); opacity: 0.6; }
    100% { transform: scale(1);    opacity: 1;   }
}

/* BUSCADOR */
.search-box {
    position: relative;
    width: 320px;
}

.search-box i {
    position: absolute;
    top: 14px;
    left: 15px;
    color: #999;
    z-index: 10;
}

.search-box input {
    padding-left: 42px !important;
    border-radius: 14px !important;
    border: 1px solid #e5e7eb !important;
    height: 48px !important;
    box-shadow: none !important;
}

.search-box input:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 .2rem rgba(13,110,253,.15) !important;
}

/* BADGE */
.bg-success-subtle {
    background: #d1fae5 !important;
}

/* RESPONSIVE */
.table-responsive {
    overflow-x: auto;
}

/* PATIENT PROFILE (modal ver) */
.patient-profile {
    display: flex;
    align-items: center;
    gap: 16px;
}

.avatar-large {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #1976d2;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
    font-weight: bold;
    flex-shrink: 0;
}

/* INFO CARD (modal ver) */
.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 14px 16px;
}

.info-card label {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 4px;
    display: block;
}

.info-card h6 {
    margin: 0;
    font-weight: 600;
}

/* DELETE ICON (modal eliminar) */
.delete-icon {
    font-size: 48px;
    color: #dc3545;
}

</style>