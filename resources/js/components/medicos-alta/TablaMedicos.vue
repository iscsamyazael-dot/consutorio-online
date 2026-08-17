<template>
    <div class="container-fluid">

     <!--Tarjetas Resumen-->
    <div class="row mb-4">

        <div class="col-md-4">

            <div class="small-box bg-info">

                <div class="inner">
                    <h3>{{ cards.total }}</h3>
                    <p>Doctores Registrados</p>
                </div>

                <div class="icon">
                    <i class="fas fa-user-md"></i>
                </div>

            </div>

        </div>

        <!-- Tarjeta Activos -->
        <div class="col-md-4">
            <div class="small-box bg-success" @click="filtrarPorEstado('activo')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ cards.activos }}</h3>
                    <p>Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Tarjeta Inactivos -->
        <div class="col-md-4">
            <div class="small-box bg-warning" @click="filtrarPorEstado('inactivo')" style="cursor: pointer;">
                <div class="inner">
                    <h3>{{ cards.inactivos }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>

    </div>

        <!-- Tabla -->
    <div class="card shadow">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="card-title m-0">
                        Lista de Doctores
                    </h3>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input 
                            v-model="buscar"
                            type="text" 
                            class="form-control" 
                            placeholder="Buscar doctor..." 
                        />
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive card-body p-0">
            <table class="table table-hover text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>Folio</th> 
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th>Horario</th>
                        <th>Días Laborales</th>
                        <th>Estado</th>
                        <th>Dur. consulta</th>
                        <th>Sucursal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <tr v-for="medico in medicosFiltrados" :key="medico.id">
                    
                        <td>{{ medico.folio }}</td>
                        
                        <td>{{ medico.nombre }}</td>

                        <td>{{ medico.especialidad ? medico.especialidad.nombre : 'Sin especialidad' }}</td>

                        <!-- Horario Concatenado (Tomando la hora del primer y último horario como referencia) -->
                        <td>
                        {{ medico.horarios && medico.horarios.length 
                            ? `${medico.horarios[0].hora_inicio.substring(0, 5)} - ${medico.horarios[medico.horarios.length - 1].hora_fin.substring(0, 5)}` 
                            : 'Horario no definido' 
                        }}
                        </td>

                             <!-- Días Laborales (Mapeando el arreglo de horarios) -->
                            <td class="text-wrap" style="max-width: 220px; font-size: 13px;">
                                <!-- El secreto: un contenedor con ancho máximo que obliga al contenido a saltar de línea -->
                                <div style="max-width: 180px; width: 100%;">
                                    <div class="d-flex flex-wrap gap-1">
                                        <!-- Convertimos el string largo en elementos individuales usando .split() -->
                                        <span 
                                            v-for="(dia, index) in medico.dias_laborales?.split(', ')" 
                                            :key="index" 
                                            class="badge bg-light text-dark border fw-normal"
                                            style="font-size: 0.85rem;"
                                            >
                                            {{ dia }}
                                        </span>
                                    </div>
                                </div>
                                {{ medico.horarios && medico.horarios.length 
                                    ? medico.horarios.map(h => {
                                        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                        return dias[h.dia_semana] || 'Desconocido';
                                    }).join(', ')
                                    : 'Sin días de atención' 
                                }}
                            </td>

                        <td>
                            <span 
                                class="badge"
                                :class="medico.activo == 1 ? 'badge-success' : 'badge-secondary'"
                            >
                                {{ medico.activo == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td>
                            <span v-if="medico.horarios && medico.horarios.length > 0" class="badge bg-light text-dark border fw-semibold">
                                <i class="fas fa-clock text-primary me-1" style="font-size: 12px;"></i>
                                {{ medico.horarios[0].duracion_consulta }} min
                            </span>
                            <span v-else class="text-muted" style="font-size: 13px;">
                                --
                            </span>
                        </td>

                        <td class="text-wrap" style="max-width: 220px; font-size: 13px;">
                            <span v-if="medico.configuraciones && medico.configuraciones.length > 0 && medico.configuraciones[0].ubicacion">
                                <i class="fas fa-map-marker-alt text-muted me-1" style="font-size: 12px;"></i>
                                {{ medico.configuraciones[0].ubicacion.nombre }}
                            </span>
                            <span v-else class="text-muted text-uppercase fw-medium" style="font-size: 11px;">
                                Sin Sucursal
                            </span>
                        </td>

                        <td>
                            
                            <button 
                                class="btn btn-sm btn-warning text-white" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEditarMedico"
                                @click="editarMedico(medico.id)"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--MODAL EDITAR MEDICO-->
<div class="modal fade" id="modalEditarMedico" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">

      <!-- HEADER -->
      <div class="modal-header border-0 p-0" style="background:#1a56db">
        <div class="d-flex align-items-center justify-content-between w-100 px-4 py-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                 style="width:44px;height:44px;background:rgba(255,255,255,0.18);font-size:16px;letter-spacing:-0.5px">
              {{ formMedico.nombre ? formMedico.nombre.split(' ').map(w=>w[0]).slice(0,2).join('') : 'MD' }}
            </div>
            <div>
              <h5 class="modal-title fw-semibold text-white m-0" style="font-size:15px">Editar Perfil Médico</h5>
              <small class="text-white-50" style="font-size:12px">Actualiza los datos del médico registrado</small>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="font-monospace fw-bold text-white px-3 py-1 rounded-pill"
                  style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);font-size:16px;letter-spacing:0.8px">
              {{ formMedico.folio }}
            </span>
          </div>
        </div>
      </div>

      <form @submit.prevent="guardarCambiosMedico">
        <!-- BODY -->
        <div class="modal-body p-4" style="background:#f8fafc">
            <div class="row g-3">

                <!-- Nombre -->
                <div class="col-12">
                <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color: #000;">Nombre del médico</label>
                <div class="input-group rounded-3 overflow-hidden border" style="border-color:#e2e8f0!important">
                    <span class="input-group-text bg-white border-0" style="color:#94a3b8"><i class="fas fa-user" style="font-size:14px"></i></span>
                    <input v-model="formMedico.nombre" type="text" class="form-control bg-white border-0 py-2" style="font-size:14px" required />
                </div>
                </div>

                <!-- Especialidad + Estado -->
                <div class="col-md-7">
                <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color:#000;">Especialidad médica</label>
                <div class="input-group rounded-3 overflow-hidden border" style="border-color:#e2e8f0!important">
                    <span class="input-group-text bg-white border-0" style="color:#94a3b8"><i class="fas fa-stethoscope" style="font-size:14px"></i></span>
                    <select v-model="formMedico.especialidad_id" class="form-select bg-white border-0 py-2" style="font-size:14px">
                    <option v-for="e in especialidades" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                    </select>
                </div>
                </div>

                <div class="col-md-5">
                <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color:#000;">Estado</label>
                <div class="d-flex align-items-center rounded-3 border bg-white px-3" style="height:42px;border-color:#e2e8f0!important;gap:10px">
                    <span class="rounded-circle flex-shrink-0" :style="{ width:'8px', height:'8px', background: formMedico.estado === 'Activo' ? '#10b981' : '#94a3b8' }"></span>
                    <select v-model="formMedico.estado" class="border-0 bg-transparent w-100 py-0 fw-medium" style="font-size:14px;outline:none"
                            :style="{ color: formMedico.estado === 'Activo' ? '#10b981' : '#64748b' }" required>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                </div>

                <!--COSTO SUCURSAL Y DURACION DE CONSULTA-->

                <div class="col-12 mt-2">
                    <hr class="my-0" style="border-color:#e8edf2">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color:#000;">Sucursal / Ubicación</label>
                    <div class="input-group rounded-3 overflow-hidden border" style="border-color:#e2e8f0!important">
                        <span class="input-group-text bg-white border-0" style="color:#94a3b8"><i class="fas fa-map-marker-alt" style="font-size:14px"></i></span>
                        <select v-model="formMedico.ubicacion_id" class="form-select bg-white border-0 py-2" style="font-size:14px" required>
                            <option value="" disabled>Selecciona una sucursal</option>
                            <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color:#000;">Costo de Consulta</label>
                    <div class="input-group rounded-3 overflow-hidden border" style="border-color:#e2e8f0!important">
                        <span class="input-group-text bg-white border-0" style="color:#10b981"><i class="fas fa-dollar-sign" style="font-size:14px"></i></span>
                        <input v-model="formMedico.costo_consulta" type="number" step="0.01" min="0" class="form-control bg-white border-0 py-2" style="font-size:14px" placeholder="0.00" required />
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color:#000;">Duración (Minutos)</label>
                    <div class="input-group rounded-3 overflow-hidden border" style="border-color:#e2e8f0!important">
                        <span class="input-group-text bg-white border-0" style="color:#3b82f6"><i class="fas fa-clock" style="font-size:14px"></i></span>
                        <input v-model="formMedico.duracion_consulta" type="number" min="5" step="5" class="form-control bg-white border-0 py-2" style="font-size:14px" placeholder="Ej. 30" required />
                    </div>
                </div>

                <!-- Horario -->
                <div class="col-12 mt-2">
                <hr class="my-0" style="border-color:#e8edf2">
                </div>
                <div class="col-12">
                <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color: #000;">HORARIO DE ATENCION</label>
                <p class="mb-2" style="font-size:12px;color:#94a3b8">Rango de horas disponibles para consultas</p>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group rounded-3 overflow-hidden border flex-grow-1" style="border-color:#e2e8f0!important">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-clock" style="color:#10b981;font-size:14px"></i></span>
                    <input type="time" v-model="formMedico.hora_inicio" class="form-control bg-white border-0 py-2 font-monospace" style="font-size:14px" />
                    </div>
                    <span class="text-muted fw-bold flex-shrink-0" style="font-size:18px">→</span>
                    <div class="input-group rounded-3 overflow-hidden border flex-grow-1" style="border-color:#e2e8f0!important">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-clock" style="color:#ef4444;font-size:14px"></i></span>
                    <input type="time" v-model="formMedico.hora_fin" class="form-control bg-white border-0 py-2 font-monospace" style="font-size:14px" />
                    </div>
                </div>
                </div>

                <!-- Días laborales -->
                <div class="col-12 mt-2">
                <hr class="my-0" style="border-color:#e8edf2">
                </div>
                <div class="col-12">
                    <label class="form-label text-uppercase fw-bold mb-1" style="font-size:11px;letter-spacing:0.8px;color: #000;">DÍAS LABORALES AUTORIZADOS</label>
                    <p class="mb-3" style="font-size:12px;color:#94a3b8">Selecciona los días habilitados para consultas</p>
                    <div class="d-flex flex-wrap gap-2">
                        <label
                            v-for="dia in listaDiasDisponibles"
                            :key="dia"
                            class="user-select-none"
                            style="cursor:pointer">
                            <input type="checkbox" :value="dia" v-model="diasSeleccionados" class="d-none" />
                            <span
                                class="d-inline-block px-3 py-2 rounded-2 fw-semibold transition-all"
                                style="font-size:12px;letter-spacing:0.2px;border:1.5px solid;transition:all 0.15s"
                                :style="diasSeleccionados.includes(dia)
                                ? 'background:#1a56db;border-color:#1a56db;color:#fff'
                                : 'background:#fff;border-color:#e2e8f0;color:#64748b'"
                            >
                                {{ dia }}
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer border-0 bg-white px-4 py-3 d-flex align-items-center justify-content-between">
          <small class="text-muted">Los cambios se guardarán de inmediato</small>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-link text-secondary text-decoration-none fw-medium px-3" data-bs-dismiss="modal">
              Cancelar
            </button>
            <button type="submit" class="btn fw-semibold px-4 d-flex align-items-center gap-2"
                    style="background:#1a56db;color:#fff;border-radius:8px;font-size:13px">
              <i class="fas fa-save "></i> Guardar cambios
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>




<!-- CONTENEDOR FLOTANTE PARA TOASTS -->

<div style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
  
  <!-- Toast de Éxito -->
  <div 
    v-if="toastSuccess" 
    class="toast show align-items-center text-white bg-success border-0 shadow-lg" 
    role="alert"
  >
    <div class="d-flex p-2">
      <div class="toast-body d-flex align-items-center w-100">
        <i class="fas fa-check-circle me-2 fs-5"></i>
        <div class="flex-grow-1">{{ mensajeSuccess }}</div>
      </div>
      <button 
        @click="toastSuccess = false" 
        type="button" 
        class="btn-close btn-close-white m-auto me-2"
      ></button>
    </div>
  </div>

  <!-- Toast de Error -->
  <div 
    v-if="toastError" 
    class="toast show align-items-center text-white bg-danger border-0 shadow-lg mt-2" 
    role="alert"
  >
    <div class="d-flex p-2">
      <div class="toast-body d-flex align-items-center w-100">
        <i class="fas fa-exclamation-circle me-2 fs-5"></i>
        <div class="flex-grow-1">{{ mensajeError }}</div>
      </div>
      <button 
        @click="toastError = false" 
        type="button" 
        class="btn-close btn-close-white m-auto me-2"
      ></button>
    </div>
  </div>

</div>

</template>

<script>

import ApiService from '../../services/ApiService.js'

export default {

    data() {

        return {

            toastSuccess: false,
            mensajeSuccess: '',
            toastError: false,
            mensajeError: '',

            idMedicoEliminar: null,

            formMedico: {
            id: null,   
            folio: '',
            nombre: '',
            especialidad_id: '',
            estado: 'Activo', 
            hora_inicio: '',       
            hora_fin: '',
            ubicacion_id: '',
            costo_consulta: '',
            duracion_consulta: ''     
            },

            cards: {
                total: 0,
                activos: 0,
                inactivos: 0
            },

            // Control de los Días Laborales de forma visual e independiente
            listaDiasDisponibles: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            diasSeleccionados: [], 
        
       
            filtroEstado: 'activo', // 'activo' o 'inactivo'
            buscar: '',    // Vinculado al input de texto
            medicos: [],
            especialidades: [],
            sucursales:[],
            costo_consulta:[],

            dias: [

                { nombre: 'Lunes', inicial: 'L' },
                { nombre: 'Martes', inicial: 'MA' },
                { nombre: 'Miércoles', inicial: 'MI' },
                { nombre: 'Jueves', inicial: 'J' },
                { nombre: 'Viernes', inicial: 'V' },
                { nombre: 'S' , inicial: 'S' },
                { nombre: 'Domingo', inicial: 'D' }

            ],

           

        }

    },


    computed: {
        // Esta función filtra localmente los médicos de la tabla en tiempo real
        medicosFiltrados() {
            return this.medicos.filter(medico => {
                // 1. Validar Filtro de Estado (Activo vs Inactivo)
                // Asumiendo que medico.activo viene como 1 (Activo) y 0 (Inactivo) o booleano
                const esActivo = medico.activo == 1 || medico.activo === true;
                
                if (this.filtroEstado === 'activo' && !esActivo) return false;
                if (this.filtroEstado === 'inactivo' && esActivo) return false;

                // 2. Validar Búsqueda por Texto
                if (!this.buscar.trim()) return true;

                const query = this.buscar.toLowerCase();
                return medico.nombre && medico.nombre.toLowerCase().includes(query);
            });
        }
    },
                


    mounted() {

        this.obtenerEspecialidades();
        this.obtenerMedicos();
        this.obtenerSucursales();
        this.obtenerEstadisticasMedicos();
        

    },

    methods: {

        filtrarPorEstado(estado) {
            if (estado === 'inactivo') {
                // Si le vuelve a dar clic a inactivo estando ya en inactivo, regresa a activo
                this.filtroEstado = this.filtroEstado === 'inactivo' ? 'activo' : 'inactivo';
            } else {
                // Si da clic a Activos
                this.filtroEstado = 'activo';
            }
        },

        // Trae los datos desde la BD al modal de Editar usando la KEY (id)
        async editarMedico(id) {
            try {
                const response = await ApiService.get(`/medicos/${id}`)
                const medico = response.data

                this.formMedico.id                 = medico.id
                this.formMedico.folio              = medico.folio
                this.formMedico.nombre             = medico.nombre
                this.formMedico.cedula_profesional = medico.cedula_profesional
                this.formMedico.especialidad_id    = medico.especialidad_id
                this.formMedico.estado             = medico.activo == 1 ? 'Activo' : 'Inactivo'
                
                // Lugar y costo (Desde configuraciones)
                if (medico.configuraciones && medico.configuraciones.length > 0) {
                    this.formMedico.ubicacion_id   = medico.configuraciones[0].ubicacion_id
                    this.formMedico.costo_consulta = medico.configuraciones[0].costo_consulta
                } else {
                    this.formMedico.ubicacion_id   = ''
                    this.formMedico.costo_consulta = ''
                }

                // Horarios Y Duración (Desde la tabla horarios)
                if (medico.horarios && medico.horarios.length > 0) {
                    this.formMedico.hora_inicio       = medico.horarios[0].hora_inicio.substring(0, 5)
                    this.formMedico.hora_fin          = medico.horarios[0].hora_fin.substring(0, 5)
                    this.formMedico.duracion_consulta = medico.horarios[0].duracion_consulta // ← Extraído de horarios
                } else {
                    this.formMedico.hora_inicio       = ''
                    this.formMedico.hora_fin          = ''
                    this.formMedico.duracion_consulta = '' // ← Limpio si no hay horarios
                }
                
                // Días seleccionados 
                const diasMap = { 1:'Lunes', 2:'Martes', 3:'Miércoles', 4:'Jueves', 5:'Viernes', 6:'Sábado', 7:'Domingo' }
                this.diasSeleccionados = medico.horarios.map(h => diasMap[h.dia_semana])

            } catch (error) {
                console.error('No se pudieron obtener los datos', error)
            }
        },

        async guardarCambiosMedico() {
            try {
                const payload = {
                    nombre:            this.formMedico.nombre,
                    especialidad_id:   this.formMedico.especialidad_id,
                    estado:            this.formMedico.estado,
                    hora_inicio:       this.formMedico.hora_inicio,
                    hora_fin:          this.formMedico.hora_fin,
                    dias:              this.diasSeleccionados,
                    ubicacion_id:      this.formMedico.ubicacion_id,
                    costo_consulta:    this.formMedico.costo_consulta,
                    duracion_consulta: this.formMedico.duracion_consulta // En el payload se queda igual porque el Controlador lo mapea a los horarios
                }

                await ApiService.put(`/medicos/${this.formMedico.id}`, payload)

                if (this.formMedico.estado === 'Inactivo') {
                    this.medicos = this.medicos.filter(m => m.id !== this.formMedico.id)
                } else {
                    await this.obtenerMedicos()
                }

                // Actualiza los contadores de las cartas de inmediato
                await this.obtenerEstadisticasMedicos();

                document.getElementById('modalEditarMedico')
                    .querySelector('[data-bs-dismiss="modal"]')
                    .click()

                this.mensajeSuccess = 'Médico actualizado correctamente'
                this.toastSuccess = true
                setTimeout(() => { this.toastSuccess = false }, 4000)

            } catch (error) {
                console.error('Error al actualizar:', error)
                this.mensajeError = error.response?.data?.message || 'Error al guardar los cambios'
                this.toastError = true
                setTimeout(() => { this.toastError = false }, 4000)
            }
        },

        async obtenerMedicos() {

            try {

                const response = await ApiService.get('/medicos')
                this.medicos = response.data
                console.log('medico cargado:',this.medicos)
            } 
            catch (error) {

                console.error(
                    'Error al obtener medicos:',
                    error
                )
            }
        },


        async obtenerEspecialidades() {

            try {

                const response = await ApiService.get('especialidades')

                this.especialidades = response.data

                console.log(
                    'Especialidades cargadas:',
                    this.especialidades
                )

            } catch (error) {

                console.error(
                    'Error al obtener especialidades:',
                    error
                )

            }

        },

        async obtenerSucursales() {
            try {
                // Hacemos la petición a la ruta de ubicaciones/sucursales
                const response = await ApiService.get('listaUbicaciones')
                // Guardamos los datos en tu array del data()
                this.sucursales = response.data
                
                console.log(
                    'Sucursales cargadas:',
                    this.sucursales
                )
            } catch (error) {
                console.error(
                    'Error al obtener sucursales:',
                    error
                )
            }
        },


        async obtenerEstadisticasMedicos() {
            try {
                const response = await ApiService.get('medicoEstadistica');
                this.cards = response.data; // Asigna { total, activos, inactivos }
            } catch (error) {
                console.error('Error al obtener las estadísticas:', error);
            }
        },

        async buscarMedico() {
            if (!this.buscar.trim()) {
                return;
            }
            try {
                const response = await ApiService.get('/buscarMedico?buscar=' + this.buscar);
                this.medicos = response.data;
            } catch (error) {
                console.error(
                    'No se encuentran resultados',
                    error
                );
            }
        },

       

    }

}


</script>

