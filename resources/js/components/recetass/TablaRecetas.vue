<template>
    <div class="historial-recetas">

        <!-- ENCABEZADO -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fas fa-file-medical text-primary me-2"></i>
                    Historial de Recetas
                </h3>
                <small class="text-muted">
                    Todas las recetas registradas
                </small>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card border-0 shadow rounded-4">

            <div class="card-body">

                <!-- FILTROS -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Buscar receta
                        </label>
                        <input
                            v-model="filtros.busqueda"
                            type="text"
                            class="form-control rounded-pill"
                            placeholder="Buscar por paciente o médico">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Médico
                        </label>
                        <select v-model="filtros.medico" class="form-select rounded-pill">
                            <option value="">Todos</option>
                            <option v-for="medico in medicosDisponibles" :key="medico" :value="medico">
                                {{ medico }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Especialidad
                        </label>
                        <select v-model="filtros.especialidad" class="form-select rounded-pill">
                            <option value="">Todas</option>
                            <option v-for="especialidad in especialidadesDisponibles" :key="especialidad" :value="especialidad">
                                {{ especialidad }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Fecha
                        </label>
                        <input
                            v-model="filtros.fecha"
                            type="date"
                            class="form-control rounded-pill">
                    </div>

                    <div class="col-md-4 d-flex flex-column justify-content-end">
                        <button
                            type="button"
                            class="btn btn-outline-primary rounded-pill w-100 btn-ver-todas"
                            @click="verTodasLasRecetas">
                            <i class="fas fa-list-ul me-2"></i>
                            Ver todas las recetas
                        </button>
                    </div>
                </div>

                <!-- CARGANDO -->
                <div v-if="cargando" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block"></i>
                    Cargando recetas...
                </div>

                <!-- ESTADO VACÍO -->
                <div v-else-if="recetasFiltradas.length === 0" class="alert alert-info text-center">
                    <i class="fas fa-prescription me-2"></i>
                    No se encontraron recetas con los filtros seleccionados.
                </div>

                <!-- TABLA DE DATOS -->
                <div v-else class="table-responsive">
                    <table class="table recetas-table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="receta in recetasFiltradas" :key="receta.id">
                                <td>
                                    <span class="fw-bold text-primary">
                                        #RX-{{ String(receta.id).padStart(3, '0') }}
                                    </span>
                                </td>
                                <td>{{ nombrePaciente(receta) }}</td>
                                <td>{{ nombreMedico(receta) }}</td>
                                <td>{{ formatearFecha(receta.fecha || receta.created_at) }}</td>
                                <td>
                                    <span
                                        class="badge rounded-pill px-3 py-2"
                                        :class="estadoBadgeClass(receta.estado)">
                                        {{ formatearEstadoReceta(receta.estado) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        class="btn btn-outline-primary btn-sm rounded-circle me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalVistaReceta"
                                        @click="verReceta(receta)"
                                        title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button
                                        v-if="receta.consulta_id"
                                        class="btn btn-outline-danger btn-sm rounded-circle"
                                        @click="verPdfReceta(receta)"
                                        title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL DETALLE -->
        <div class="modal fade" id="modalVistaReceta" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg modal-receta" v-if="recetaSeleccionada">

                    <div class="modal-header border-0">
                        <div>
                            <h4 class="fw-bold text-primary mb-1">
                                <i class="fas fa-file-medical me-2"></i>
                                Resumen de Receta
                            </h4>
                            <small class="text-muted">
                                Vista general de la prescripción médica
                            </small>
                        </div>

                        <button
                            type="button"
                            class="btn btn-light rounded-circle btn-close-modern"
                            data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">

                            <div class="col-md-6">
                                <div class="info-card">
                                    <label>Paciente</label>
                                    <h6>{{ nombrePaciente(recetaSeleccionada) }}</h6>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card">
                                    <label>Médico</label>
                                    <h6>{{ nombreMedico(recetaSeleccionada) }}</h6>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="info-card">
                                    <label>Medicamentos</label>
                                    <ul v-if="parseMedicamentos(recetaSeleccionada.medicamentos).length" class="mb-0">
                                        <li v-for="(med, i) in parseMedicamentos(recetaSeleccionada.medicamentos)" :key="i">
                                            {{ med.nombre }}
                                            <span v-if="med.dosis"> - {{ med.dosis }}</span>
                                            <span v-if="med.frecuencia"> - {{ med.frecuencia }}</span>
                                            <span v-if="med.duracion"> - {{ med.duracion }}</span>
                                        </li>
                                    </ul>
                                    <p v-else class="mb-0 text-muted">Sin medicamentos registrados.</p>
                                </div>
                            </div>

                            <div class="col-md-12" v-if="recetaSeleccionada.indicaciones_generales">
                                <div class="info-card">
                                    <label>Indicaciones</label>
                                    <p class="mb-0">{{ recetaSeleccionada.indicaciones_generales }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-card">
                                    <label>Fecha</label>
                                    <h6 class="mb-0">
                                        {{ formatearFecha(recetaSeleccionada.fecha || recetaSeleccionada.created_at) }}
                                    </h6>
                                </div>
                            </div>

                            <div class="col-md-6" v-if="recetaSeleccionada.observaciones_ia">
                                <div class="info-card">
                                    <label>Observaciones IA</label>
                                    <p class="mb-0">{{ recetaSeleccionada.observaciones_ia }}</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer border-0 justify-content-between">
                        <div class="d-flex gap-1">
                            <button
                                v-if="recetaSeleccionada.consulta_id"
                                class="btn btn-danger rounded-pill px-4 btn-pdf"
                                @click="verPdfReceta(recetaSeleccionada)">
                                <i class="fas fa-file-pdf me-2"></i>
                                Ver PDF
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script>
// AJUSTA esta ruta según dónde quede finalmente este archivo dentro de
// resources/js (mismo patrón que usa ExpedienteTabs.vue).
import ApiService from '../../services/ApiService.js'

export default {
    name: 'HistorialRecetas',
    data() {
        return {
            recetas: [],
            cargando: false,
            recetaSeleccionada: null,
            filtros: {
                busqueda: '',
                estado: '',
                fecha: '',
                medico: '',
                especialidad: ''
            }
        }
    },
    mounted() {
        // Solo se necesita cargar las recetas: los selects de Médico y
        // Especialidad se llenan solos vía los computed de abajo
        // (medicosDisponibles / especialidadesDisponibles), que derivan
        // sus valores de this.recetas. No existen (ni se necesitan)
        // endpoints separados /medicos o /especialidades para esta vista.
        this.obtenerRecetas()
    },
    computed: {
        // Lista única de médicos, sacada de las recetas ya cargadas
        // (no depende de un endpoint aparte de /medicos).
        medicosDisponibles() {
            const nombres = this.recetas
                .map(r => this.nombreMedico(r))
                .filter(n => n && n !== 'Sin médico')
            return [...new Set(nombres)].sort()
        },

        // Lista única de especialidades. Ver nota en la plantilla:
        // asumo que la especialidad viene de receta.consulta.especialidad.
        // Ajustar especialidadReceta() si en realidad vive en el médico.
        especialidadesDisponibles() {
            const especialidades = this.recetas
                .map(r => this.especialidadReceta(r))
                .filter(Boolean)
            return [...new Set(especialidades)].sort()
        },

        recetasFiltradas() {
            return this.recetas.filter(r => {
                const texto = this.filtros.busqueda.trim().toLowerCase()
                const coincideTexto =
                    !texto ||
                    this.nombrePaciente(r).toLowerCase().includes(texto) ||
                    this.nombreMedico(r).toLowerCase().includes(texto)

                const coincideEstado =
                    !this.filtros.estado ||
                    (r.estado || '').toLowerCase() === this.filtros.estado

                const coincideMedico =
                    !this.filtros.medico ||
                    this.nombreMedico(r) === this.filtros.medico

                const coincideEspecialidad =
                    !this.filtros.especialidad ||
                    this.especialidadReceta(r) === this.filtros.especialidad

                const fechaReceta = r.fecha || r.created_at
                const coincideFecha =
                    !this.filtros.fecha ||
                    (fechaReceta && fechaReceta.slice(0, 10) === this.filtros.fecha)

                return coincideTexto && coincideEstado && coincideMedico &&
                    coincideEspecialidad && coincideFecha
            })
        }
    },
    methods: {
        async obtenerRecetas() {
            this.cargando = true
            try {
                // GET /recetas -> RecetaController@index
                // (Receta::with(['consulta.paciente','consulta.medico','consulta.especialidad'])->latest('fecha')->get())
                const response = await ApiService.get('/recetas')
                this.recetas = Array.isArray(response.data) ? response.data : []
                console.log('Recetas cargadas:', this.recetas)
            } catch (error) {
                console.error('Error al obtener las recetas:', error)
            } finally {
                this.cargando = false
            }
        },

        // La receta NO tiene paciente_id propio: se llega al paciente vía
        // receta.consulta.paciente (Consulta belongsTo Paciente).
        // Modelo Paciente: nombre, apellido_paterno, apellido_materno.
        nombrePaciente(receta) {
            const p = receta && receta.consulta && receta.consulta.paciente
            if (!p) return 'Sin paciente'
            const partes = [p.nombre, p.apellido_paterno, p.apellido_materno].filter(Boolean)
            return partes.length ? partes.join(' ') : 'Sin paciente'
        },

        // Igual que el paciente: se llega vía receta.consulta.medico
        // (Consulta belongsTo Medico, no User). No confirmé aún los
        // campos exactos del modelo Medico, así que pruebo el mismo
        // patrón que Paciente y caigo a "name" si no aplica.
        nombreMedico(receta) {
            const m = receta && receta.consulta && receta.consulta.medico
            if (!m) return 'Sin médico'
            const partes = [m.nombre, m.apellido_paterno, m.apellido_materno].filter(Boolean)
            return partes.length ? partes.join(' ') : (m.name || 'Sin médico')
        },

        // ⚠️ ASUNCIÓN: la especialidad se toma de receta.consulta.especialidad
        // (relación confirmada en Consulta.php -> especialidad()). Si en tu
        // app la especialidad en realidad depende del médico
        // (receta.consulta.medico.especialidad), cambia esta función para
        // leer de ahí en su lugar.
        especialidadReceta(receta) {
            const e = receta && receta.consulta && receta.consulta.especialidad
            if (!e) return null
            return e.nombre || e.name || null
        },

        parseMedicamentos(meds) {
            if (!meds) return []
            if (Array.isArray(meds)) return meds
            try {
                const parsed = JSON.parse(meds)
                return Array.isArray(parsed) ? parsed : []
            } catch (e) {
                console.error('Error al parsear medicamentos:', e)
                return []
            }
        },

        formatearFecha(fecha) {
            if (!fecha) return ''
            const f = new Date(fecha)
            return f.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
        },

        // ⚠️ Ajusta estos valores si tu columna `estado` en `recetas`
        // usa otros textos (por ejemplo 'vigente' en vez de 'activa').
        // ⚠️ El valor real que guarda la BD para una receta ya generada
        // es 'borrador' (nombre interno/técnico), pero de cara al usuario
        // se muestra como "Finalizada".
        formatearEstadoReceta(estado) {
            switch ((estado || '').toLowerCase()) {
                case 'borrador':
                    return 'Finalizada'
                case 'pendiente':
                    return 'Pendiente'
                case 'activa':
                case 'vigente':
                    return 'Activa'
                case 'completada':
                case 'finalizada':
                case 'validada':
                    return 'Completada'
                case 'cancelada':
                case 'rechazada':
                    return 'Cancelada'
                default:
                    return estado || 'Sin estado'
            }
        },

        estadoBadgeClass(estado) {
            switch ((estado || '').toLowerCase()) {
                case 'borrador':
                    return 'bg-primary'
                case 'pendiente':
                    return 'bg-warning text-dark'
                case 'activa':
                case 'vigente':
                    return 'bg-success'
                case 'completada':
                case 'finalizada':
                case 'validada':
                    return 'bg-primary'
                case 'cancelada':
                case 'rechazada':
                    return 'bg-danger'
                default:
                    return 'bg-secondary'
            }
        },

        verReceta(receta) {
            this.recetaSeleccionada = receta
        },

        // Limpia todos los filtros para mostrar el listado completo de recetas.
        verTodasLasRecetas() {
            this.filtros.busqueda = ''
            this.filtros.estado = ''
            this.filtros.fecha = ''
            this.filtros.medico = ''
            this.filtros.especialidad = ''
        },

        // Reutiliza la misma ruta que ExpedienteTabs.vue:
        // GET consultaIA/{consultaId}/pdf/{tipo}/ver
        verPdfReceta(receta) {
            if (!receta || !receta.consulta_id) {
                console.warn('Esta receta no tiene consulta_id, no se puede generar el PDF.')
                return
            }

            const baseURL = document
                .querySelector('meta[name="base-url"]')
                .getAttribute('content')
            const base = baseURL.replace(/\/$/, '')

            const url = `${base}/consultaIA/${receta.consulta_id}/pdf/receta/ver`
            window.open(url, '_blank')
        }
    }
}
</script>

<style scoped>

.historial-recetas {
    padding: 8px 0;
}

.modal-receta{
    border-radius: 24px;
}

.info-card{
    background: #f8fafc;
    padding: 18px;
    border-radius: 18px;
}

.info-card label{
    font-size: 13px;
    color: #64748b;
    margin-bottom: 6px;
    display: block;
}

/* =========================
   FILTROS
========================= */

.estado-select{
    width: 180px;
}

.btn-ver-todas{
    height: 46px;
    font-weight: 600;
}

.btn-ver-todas:hover{
    background: #0c1fce;
    color: #fff;
}

/* =========================
   INPUTS
========================= */

.form-control,
.form-select{
    height: 46px;
    border: 1px solid #dbe1e8;
    transition: .2s ease;
    background: #fff;
}

.form-control:focus,
.form-select:focus{
    border-color: #0c1fce;
    box-shadow: 0 0 0 3px rgba(37,99,235,.10);
}

.form-label{
    color: #555555;
    margin-bottom: 10px;
    font-size: 14px;
}

/* =========================
   TABLA
========================= */

.recetas-table{
    border-collapse: separate;
    border-spacing: 0 10px;
}

.recetas-table>:not(caption)>*>*{
    border-bottom-width: 0 !important;
    box-shadow: none !important;
}

.recetas-table thead th{
    background: transparent !important;
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 14px 18px;
    border: none !important;
}

.recetas-table tbody tr{
    background: #f0f0f0;
    transition: .25s ease;
    box-shadow: 0 4px 10px rgba(0,0,0,.04);
}

.recetas-table tbody tr:hover{
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(37,99,235,.10);
}

.recetas-table tbody td{
    padding: 18px;
    vertical-align: middle;
    border: none !important;
}

.recetas-table tbody tr td:first-child{
    border-top-left-radius: 16px;
    border-bottom-left-radius: 16px;
}

.recetas-table tbody tr td:last-child{
    border-top-right-radius: 16px;
    border-bottom-right-radius: 16px;
}

/* =========================
   BADGES
========================= */

.badge{
    padding: 8px 14px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
}

/* =========================
   BOTONES
========================= */

.btn{
    transition: .2s ease;
}

.btn:hover{
    transform: scale(1.06);
}

.btn-outline-primary,
.btn-outline-danger{
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-close-modern{
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: .25s ease;
    color: #64748b;
    background: #f8fafc;
}

.btn-close-modern:hover{
    background: #dbeafe;
    color: #2563eb;
    transform: rotate(90deg) scale(1.05);
}

.btn-close-modern i{
    font-size: 14px;
}

.btn-pdf{
    background: linear-gradient(135deg, #dc2626, #ef4444);
    border: none;
    box-shadow: 0 4px 14px rgba(220,38,38,.25);
    transition: .2s ease;
}

.btn-pdf:hover{
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(220,38,38,.35);
}

.card{
    border-radius: 24px;
    overflow: hidden;
}

</style>