<template>
  <div class="cc-layout">

    <!-- ═══════════════════════════════════════
         PANEL IZQUIERDO: PACIENTE ACTIVO
    ═══════════════════════════════════════ -->
    <div class="cc-panel">
      <div class="cc-panel__head">
        <span class="cc-dot cc-dot--blue"></span>
        <span class="cc-label">Panel de atención</span>
        <span v-if="editMode" class="cc-edit-flag">
          <i class="ti ti-pencil" aria-hidden="true"></i> Editando
        </span>
      </div>

      <!-- Hero paciente -->
      <div v-if="pacienteActivo" class="cc-hero">
        <div class="cc-avatar" :style="{ background: avatarColor(nombreCompleto(pacienteActivo)) }">
          {{ initials(nombreCompleto(pacienteActivo)) }}
        </div>
        <div class="cc-hero__info">
          <p class="cc-hero__name">{{ nombreCompleto(pacienteActivo) }}</p>
          <span class="cc-mono">{{ pacienteActivo.paciente_id }}</span>
        </div>
      </div>
      <div v-else class="cc-hero">
        <div class="cc-hero__info">
          <p class="cc-hero__name" style="color: #9ca3af;">Sin paciente seleccionado</p>
        </div>
      </div>

      <!-- Detalles -->
      <dl class="cc-details">
        <div class="cc-details__row">
          <dt><i class="ti ti-stethoscope" aria-hidden="true"></i> Motivo de consulta</dt>
          <dd v-if="!editMode">{{ ultimoTriage(pacienteActivo)?.motivo_consulta || '—' }}</dd>
          <dd v-else class="cc-details__edit">
            <input type="text" class="cc-mini-input" v-model="editForm.diagnostico" placeholder="Motivo de consulta" />
          </dd>
        </div>
        <div class="cc-details__row">
          <dt><i class="ti ti-phone" aria-hidden="true"></i> Teléfono</dt>
          <dd>{{ pacienteActivo?.telefono || '—' }}</dd>
        </div>
        <div class="cc-details__row">
          <dt><i class="ti ti-shield-half" aria-hidden="true"></i> Triage</dt>
          <dd v-if="!editMode">
            <span class="cc-chip" :class="triageClass(ultimoTriage(pacienteActivo)?.estado)">
              {{ ultimoTriage(pacienteActivo)?.estado || 'Sin asignar' }}
            </span>
          </dd>
          <dd v-else class="cc-details__edit">
            <select class="cc-mini-select" v-model="editForm.triage">
              <option v-for="t in triageOpciones" :key="t" :value="t">{{ t }}</option>
            </select>
          </dd>
        </div>
        <div class="cc-details__row">
          <dt><i class="ti ti-activity" aria-hidden="true"></i> Estado</dt>
          <dd v-if="!editMode">
            <span class="cc-chip" :class="chipClass(pacienteActivo?.estado)">
              {{ pacienteActivo?.estado || 'Sin asignar' }}
            </span>
          </dd>
          <dd v-else class="cc-details__edit">
            <select class="cc-mini-select" v-model="editForm.estado">
              <option v-for="e in estadoOpciones" :key="e" :value="e">{{ e }}</option>
            </select>
          </dd>
        </div>
      </dl>

      <!-- Síntomas -->
      <div class="cc-symptoms">
        <p class="cc-symptoms__label">
          <i class="ti ti-notes-medical" aria-hidden="true"></i> Sintomatología reportada
        </p>
        <p v-if="!editMode" class="cc-symptoms__text">
          {{ ultimoTriage(pacienteActivo)?.sintomas || 'Sin síntomas registrados.' }}
        </p>
        <textarea
          v-else
          class="cc-symptoms__textarea"
          rows="3"
          v-model="editForm.sintomas"
          placeholder="Síntomas reportados…"
        ></textarea>
      </div>

      <!-- Acciones -->
      <div class="cc-panel__actions" v-if="!editMode">
        <button class="cc-btn cc-btn--ghost" @click="abrirModal('detalle', pacienteActivo)" :disabled="!pacienteActivo">
          <i class="ti ti-eye" aria-hidden="true"></i> Detalle
        </button>
        <button class="cc-btn cc-btn--ghost" @click="iniciarEdicion(pacienteActivo)" :disabled="!pacienteActivo">
          <i class="ti ti-edit" aria-hidden="true"></i> Editar
        </button>
        <button class="cc-btn cc-btn--primary" @click="abrirModal('expediente', pacienteActivo)" :disabled="!pacienteActivo">
          <i class="ti ti-folder-open" aria-hidden="true"></i> Expediente
        </button>
      </div>
      <div class="cc-panel__actions" v-else>
        <button class="cc-btn cc-btn--ghost" @click="cancelarEdicion">
          <i class="ti ti-x" aria-hidden="true"></i> Cancelar
        </button>
        <button class="cc-btn cc-btn--success" @click="guardarEdicion">
          <i class="ti ti-device-floppy" aria-hidden="true"></i> Guardar cambios
        </button>
      </div>
    </div>

    <!-- ═══════════════════════════════════════
         TABLA DE ESPERA
    ═══════════════════════════════════════ -->
    <section class="cc-table-section">
      <div class="cc-table-head">
        <div>
          <span class="cc-dot cc-dot--gray"></span>
          <span class="cc-label">Lista de espera del día</span>
        </div>
        <span class="cc-badge-count">{{ pacientesFiltrados.length }} pacientes</span>
      </div>

      <div class="cc-table-search">
        <i class="ti ti-search" aria-hidden="true"></i>
        <input type="text" v-model="busqueda" placeholder="Buscar por nombre o folio…" />
      </div>

      <div class="cc-table-wrap">
        <table class="cc-table">
          <thead>
            <tr>
              <th>Paciente / Diagnóstico</th>
              <th>Folio</th>
              <th>Estado</th>
              <th>Urgencia</th>
              <th class="cc-th--right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in pacientesFiltrados"
              :key="p.id"
              class="cc-row"
              :class="{ 'cc-row--active': pacienteActivo && p.paciente_id === pacienteActivo.paciente_id }"
            >
              <td class="cc-td--patient">
                <div class="cc-mini-avatar" :style="{ background: avatarColor(nombreCompleto(p)) }">
                  {{ initials(nombreCompleto(p)) }}
                </div>
                <div>
                  <p class="cc-patient-name">{{ nombreCompleto(p) }}</p>
                  <p class="cc-patient-diag">{{ ultimoTriage(p)?.motivo_consulta || '—' }}</p>
                </div>
              </td>
              <td><span class="cc-mono cc-mono--cell">{{ p.paciente_id }}</span></td>
              <td><span class="cc-chip" :class="chipClass(p.estado)">{{ p.estado || 'Sin asignar' }}</span></td>
              <td><span class="cc-chip" :class="triageClass(ultimoTriage(p)?.estado)">{{ ultimoTriage(p)?.estado || 'Sin asignar' }}</span></td>
              <td class="cc-td--actions">
                <button class="cc-icon-btn cc-icon-btn--view" title="Ver / seleccionar" @click="verPaciente(p)">
                  <i class="ti ti-eye" aria-hidden="true"></i>
                </button>
                <button class="cc-icon-btn cc-icon-btn--folder" title="Expediente" @click="abrirExpedienteDesdeFila(p)">
                  <i class="ti ti-folder" aria-hidden="true"></i>
                </button>
              </td>
            </tr>
            <tr v-if="pacientesFiltrados.length === 0">
              <td colspan="5" class="cc-table-empty">No se encontraron pacientes con ese criterio.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ═══════════════════════════════════════
         MODALES
    ═══════════════════════════════════════ -->
    <Teleport to="body">

      <!-- MODAL: FICHA CLÍNICA (detalle) -->
      <transition name="cc-fade">
        <div v-if="modal.tipo === 'detalle'" class="cc-overlay" @click.self="cerrarModal">
          <div class="cc-modal">
            <div class="cc-modal__header cc-modal__header--dark">
              <span><i class="ti ti-shield-check" aria-hidden="true"></i> Ficha clínica digital</span>
              <button class="cc-modal__close" @click="cerrarModal" aria-label="Cerrar">
                <i class="ti ti-x" aria-hidden="true"></i>
              </button>
            </div>
            <div class="cc-modal__body">
              <div class="cc-modal__grid">
                <div>
                  <p class="cc-field-label">Estado actual</p>
                  <span class="cc-chip" :class="chipClass(modal.paciente?.estado)">
                    {{ modal.paciente?.estado }}
                  </span>
                </div>
                <div>
                  <p class="cc-field-label">Triage / prioridad</p>
                  <span class="cc-chip" :class="triageClass(modal.paciente?.triage)">
                    {{ modal.paciente?.triage }}
                  </span>
                </div>
                <div class="cc-modal__grid-full">
                  <p class="cc-field-label">
                    <i class="ti ti-calendar" aria-hidden="true"></i> Fecha de atención
                  </p>
                  <p class="cc-field-value">{{ formatearFecha(modal.paciente?.fecha) }}</p>
                </div>
                <div class="cc-modal__grid-full">
                  <p class="cc-field-label">
                    <i class="ti ti-notes" aria-hidden="true"></i> Síntomas y notas de admisión
                  </p>
                  <p class="cc-field-value cc-field-value--muted">{{ modal.paciente?.sintomas }}</p>
                </div>
              </div>
            </div>
            <div class="cc-modal__footer">
              <button class="cc-btn cc-btn--ghost" @click="cerrarModal">Cerrar</button>
              <button class="cc-btn cc-btn--primary" @click="irAExpediente(modal.paciente)">
                <i class="ti ti-folder-open" aria-hidden="true"></i> Abrir expediente
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- MODAL: EXPEDIENTE CLÍNICO -->
      <transition name="cc-fade">
        <div v-if="modal.tipo === 'expediente'" class="cc-overlay" @click.self="cerrarModal">
          <div class="cc-modal cc-modal--lg">
            <div class="cc-modal__header cc-modal__header--blue">
              <span><i class="ti ti-file-medical" aria-hidden="true"></i> Apertura de expediente clínico</span>
              <button class="cc-modal__close" @click="cerrarModal" aria-label="Cerrar">
                <i class="ti ti-x" aria-hidden="true"></i>
              </button>
            </div>
            <div class="cc-modal__body">

              <!-- Paciente referencia -->
              <div class="cc-exp-ref">
                <div class="cc-mini-avatar cc-mini-avatar--lg" :style="{ background: avatarColor(nombreCompleto(modal.paciente)) }">
                  {{ initials(nombreCompleto(modal.paciente)) }}
                </div>
                <div>
                  <p class="cc-exp-ref__name">{{ nombreCompleto(modal.paciente) }}</p>
                  <p class="cc-exp-ref__folio">
                    Folio: <span class="cc-mono">{{ modal.paciente?.paciente_id }}</span>
                  </p>
                </div>
              </div>

              <!-- Info general del paciente -->
              <p class="cc-section-label">
                <i class="ti ti-user" aria-hidden="true"></i> 1. Datos generales del paciente
              </p>
              <div class="cc-form-grid">
                <div class="cc-field">
                  <label class="cc-field-label">Teléfono</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-phone" aria-hidden="true"></i>
                    <input type="text" v-model="form.telefono" placeholder="Sin teléfono" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Email</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-mail" aria-hidden="true"></i>
                    <input type="text" v-model="form.email" placeholder="Sin email" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Sexo</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-gender-bigender" aria-hidden="true"></i>
                    <input type="text" v-model="form.sexo" placeholder="—" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Tipo de sangre</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-droplet" aria-hidden="true"></i>
                    <input type="text" v-model="form.tipo_sangre" placeholder="—" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Fecha de nacimiento</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-calendar" aria-hidden="true"></i>
                    <input type="text" v-model="form.fecha_nacimiento" placeholder="—" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">CURP</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-id-badge" aria-hidden="true"></i>
                    <input type="text" v-model="form.curp" placeholder="—" />
                  </div>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Dirección</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-map-pin" aria-hidden="true"></i>
                    <input type="text" v-model="form.direccion" placeholder="Sin dirección" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Contacto de emergencia</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-user-exclamation" aria-hidden="true"></i>
                    <input type="text" v-model="form.contacto_emergencia" placeholder="—" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Teléfono emergencia</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-phone-call" aria-hidden="true"></i>
                    <input type="text" v-model="form.telefono_emergencia" placeholder="—" />
                  </div>
                </div>
              </div>

              <!-- Sección 2 -->
              <p class="cc-section-label">
                <i class="ti ti-heartbeat" aria-hidden="true"></i> 2. Signos vitales y somatometría
              </p>
              <div class="cc-form-grid">
                <div class="cc-field">
                  <label class="cc-field-label">Peso (kg)</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-scale" aria-hidden="true"></i>
                    <input type="number" step="0.1" v-model="form.peso" placeholder="0.0" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Talla (cm)</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-ruler" aria-hidden="true"></i>
                    <input type="number" v-model="form.talla" placeholder="170" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Presión arterial</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-heart-rate-monitor" aria-hidden="true"></i>
                    <input type="text" v-model="form.presion" placeholder="120/80 mmHg" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Temperatura (°C)</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-temperature" aria-hidden="true"></i>
                    <input type="number" step="0.1" v-model="form.temperatura" placeholder="36.5" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Saturación (%)</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-lungs" aria-hidden="true"></i>
                    <input type="number" v-model="form.saturacion" placeholder="98" />
                  </div>
                </div>
                <div class="cc-field">
                  <label class="cc-field-label">Frec. cardiaca</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-heart" aria-hidden="true"></i>
                    <input type="number" v-model="form.frecuencia_cardiaca" placeholder="72" />
                  </div>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Motivo de consulta</label>
                  <div class="cc-input-wrap">
                    <i class="ti ti-stethoscope" aria-hidden="true"></i>
                    <input type="text" v-model="form.motivo_consulta" placeholder="—" />
                  </div>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Síntomas</label>
                  <textarea v-model="form.sintomas" rows="2" placeholder="Síntomas reportados…"></textarea>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Diagnóstico / observaciones clínicas iniciales</label>
                  <textarea v-model="form.diagnostico" rows="3" placeholder="Conclusiones o anotaciones físicas del paciente…"></textarea>
                </div>
              </div>

              <!-- Sección 3 antecedentes -->
              <p class="cc-section-label">
                <i class="ti ti-clipboard-list" aria-hidden="true"></i> 3. Antecedentes médicos
              </p>
              <div class="cc-form-grid">
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Alergias</label>
                  <textarea v-model="form.alergias" rows="2" placeholder="Sin alergias registradas…"></textarea>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Enfermedades crónicas</label>
                  <textarea v-model="form.enfermedades_cronicas" rows="2" placeholder="Sin enfermedades crónicas…"></textarea>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Medicamentos actuales</label>
                  <textarea v-model="form.medicamentos_actuales" rows="2" placeholder="Sin medicamentos…"></textarea>
                </div>
                <div class="cc-field cc-field--full">
                  <label class="cc-field-label">Antecedentes quirúrgicos</label>
                  <textarea v-model="form.antecedentes_quirurgicos" rows="2" placeholder="Sin antecedentes quirúrgicos…"></textarea>
                </div>
              </div>

              <!-- Sección 2 -->
              <p class="cc-section-label">
                <i class="ti ti-x-ray" aria-hidden="true"></i> 2. Carga de radiografías / estudios visuales
              </p>
              <div
                class="cc-dropzone"
                @dragover.prevent
                @drop.prevent="onDrop"
                @click="$refs.fileInput.click()"
              >
                <i class="ti ti-cloud-upload" aria-hidden="true"></i>
                <p>Arrastra archivos aquí o haz clic para buscar</p>
                <span>JPG, PNG — varias imágenes a la vez</span>
                <input
                  ref="fileInput"
                  type="file"
                  multiple
                  accept="image/*"
                  @change="onFileChange"
                  style="display:none"
                />
              </div>
              <div v-if="previews.length" class="cc-previews">
                <div v-for="(src, i) in previews" :key="i" class="cc-preview">
                  <img :src="src" alt="preview" />
                  <button @click.stop="previews.splice(i, 1)" aria-label="Eliminar imagen">
                    <i class="ti ti-x" aria-hidden="true"></i>
                  </button>
                </div>
              </div>

            </div>
            <div class="cc-modal__footer">
              <button class="cc-btn cc-btn--ghost" @click="cerrarModal">Cancelar</button>
              <button class="cc-btn cc-btn--success" @click="guardarExpediente">
                <i class="ti ti-device-floppy" aria-hidden="true"></i> Guardar expediente
              </button>
            </div>
          </div>
        </div>
      </transition>

    </Teleport>

  </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
  name: 'ConsultaClinica',

  data() {
    return {
      // Lista principal de pacientes (array)
      pacientes: [],

      // Paciente mostrado en el panel izquierdo
      pacienteActivo: null,

      // Búsqueda en la tabla
      busqueda: '',

      // Modo edición del panel
      editMode: false,

      // Formulario de edición del panel
      editForm: {
        diagnostico: '',
        sintomas: '',
        triage: '',
        estado: ''
      },

      // Opciones para selectores
      triageOpciones: ['Rojo', 'Amarillo', 'Verde'],
      estadoOpciones: ['En espera', 'Atendido', 'Cancelado'],

      // Control del modal activo
      modal: {
        tipo: '',      // 'detalle' | 'expediente' | ''
        paciente: null
      },

      // Previews de imágenes en el modal de expediente
      previews: [],

      // Formulario del expediente clínico
      form: {
        paciente_id:             '',
        codigo_paciente:         '',
        // datos generales
        telefono:                '',
        email:                   '',
        sexo:                    '',
        tipo_sangre:             '',
        fecha_nacimiento:        '',
        curp:                    '',
        direccion:               '',
        contacto_emergencia:     '',
        telefono_emergencia:     '',
        // signos vitales (del triage)
        peso:                    '',
        talla:                   '',
        presion:                 '',
        temperatura:             '',
        saturacion:              '',
        frecuencia_cardiaca:     '',
        motivo_consulta:         '',
        sintomas:                '',
        diagnostico:             '',
        // antecedentes
        alergias:                '',
        enfermedades_cronicas:   '',
        medicamentos_actuales:   '',
        antecedentes_quirurgicos:''
      }
    }
  },

  computed: {
     // Lista de pacientes filtrada según lo que se escriba en el buscador
    pacientesFiltrados() {
      // Si no hay texto de búsqueda, mostrar todos los pacientes
      if (!this.busqueda.trim()) return this.pacientes
      // Convertir la búsqueda a minúsculas para ignorar mayúsculas
      const q = this.busqueda.toLowerCase()
       // Filtrar pacientes por nombre o por folio (paciente_id)
      return this.pacientes.filter(p =>
        (p.nombre || '').toLowerCase().includes(q) ||
        (p.paciente_id || '').toLowerCase().includes(q)
      )
    }
  },

  mounted() {
    this.obtenerPacientes()
  },

  methods: {

    // ──────────────────────────────────────────
    // API
    // ──────────────────────────────────────────

    async obtenerPacientes() {
      try {
        // Obtener la lista de pacientes desde la API
        const response = await ApiService.get('/pacientes')
        // Guardar los pacientes en la variable
        this.pacientes = response.data

        // Si hay pacientes, seleccionar el primero por defecto
        if (this.pacientes.length > 0) {
          this.pacienteActivo = this.pacientes[0]
        }
      } catch (error) {
        // Mostrar el error en la consola si falla la petición
        console.error('Error al obtener pacientes:', error)
      }
    },

    async guardarCambiosPaciente() {
      try {
        // Enviar los cambios del paciente a la API
        await ApiService.put(
          '/pacientes/' + this.pacienteActivo.id,
          this.pacienteActivo
        )
        // Volver a cargar la lista de pacientes
        await this.obtenerPacientes()
      } catch (error) {
        // Mostrar el error en la consola si ocurre alguno
        console.error('Error al guardar paciente:', error)
      }
    },

    // ──────────────────────────────────────────
    // Panel izquierdo
    // ──────────────────────────────────────────

    verPaciente(paciente) {
       // Mostrar la información del paciente seleccionado
      this.pacienteActivo = paciente
      // Desactivar el modo de edición
      this.editMode = false
    },

    iniciarEdicion(paciente) {
      //verifica que exista una paciente seleccionado
      if (!paciente) return
      //Activar que exista un paciente seleccionado
      this.editMode = true
      // carga los datos del paciente en el formulario
      this.editForm = {
        diagnostico: paciente.diagnostico || '',
        sintomas:    paciente.sintomas    || '',
        triage:      paciente.triage      || '',
        estado:      paciente.estado      || ''
      }
    },

    cancelarEdicion() {
      //salir del modo de edicion
      this.editMode = false
    },

    guardarEdicion() {
      // verifica que haya un paciente seleccionado 
      if (!this.pacienteActivo) return

      // Guarda los cambios en el paciente seleccionado
      Object.assign(this.pacienteActivo, this.editForm)
      //Actualizar tambien el paciente en la lista 
      const idx = this.pacientes.findIndex(p => p.id === this.pacienteActivo.id)
      // si el paciente existe en la lista ,actualiza sus datos 
      if (idx !== -1) {
        Object.assign(this.pacientes[idx], this.editForm)
      }

      this.editMode = false

      // Opcional: persistir en la API
      // this.guardarCambiosPaciente()
    },

    // ──────────────────────────────────────────
    // Modales
    // ──────────────────────────────────────────

    abrirModal(tipo, paciente) {
      if (!paciente) return

      this.modal.tipo = tipo
      this.modal.paciente = paciente

      // Inicializar el formulario del expediente con los datos del paciente
      if (tipo === 'expediente') {
        const t = this.ultimoTriage(paciente) || {}
        this.form = {
          paciente_id:              paciente.id            || '',
          codigo_paciente:          paciente.paciente_id   || '',
          // datos generales
          telefono:                 paciente.telefono               || '',
          email:                    paciente.email                  || '',
          sexo:                     paciente.sexo                   || '',
          tipo_sangre:              paciente.tipo_sangre            || '',
          fecha_nacimiento:         paciente.fecha_nacimiento       || '',
          curp:                     paciente.curp                   || '',
          direccion:                paciente.direccion              || '',
          contacto_emergencia:      paciente.contacto_emergencia    || '',
          telefono_emergencia:      paciente.telefono_emergencia    || '',
          // signos vitales del triage
          peso:                     t.peso                          || '',
          talla:                    t.talla                         || '',
          presion:                  t.presion                       || '',
          temperatura:              t.temperatura                   || '',
          saturacion:               t.saturacion                    || '',
          frecuencia_cardiaca:      t.frecuencia_cardiaca           || '',
          motivo_consulta:          t.motivo_consulta               || '',
          sintomas:                 t.sintomas                      || '',
          diagnostico:              '',
          // antecedentes del paciente
          alergias:                 paciente.alergias               || '',
          enfermedades_cronicas:    paciente.enfermedades_cronicas  || '',
          medicamentos_actuales:    paciente.medicamentos_actuales  || '',
          antecedentes_quirurgicos: paciente.antecedentes_quirurgicos || ''
        }
        this.previews = []
      }
    },
    //cerrar el modal y limpia sus datos 
    cerrarModal() {
      this.modal.tipo    = ''
      this.modal.paciente = null
    },

    abrirExpedienteDesdeFila(paciente) {
      this.abrirModal('expediente', paciente)
    },

    irAExpediente(paciente) {
      // Cierra el modal de detalle y abre el de expediente
      this.abrirModal('expediente', paciente)
    },

    guardarExpediente() {
      // Aquí puedes llamar a la API para guardar el expediente
      console.log('Expediente guardado:', {
        paciente: this.modal.paciente,
        form:     this.form,
        imagenes: this.previews.length
      })
      this.cerrarModal()
    },

    // ──────────────────────────────────────────
    // Archivos / imágenes
    // ──────────────────────────────────────────

    onFileChange(event) {
      // obtiner los achivos seleccionados 
      this._leerArchivos(event.target.files)
    },

    onDrop(event) {
      //obtener los archivos que se arrastraron y soltaron
      this._leerArchivos(event.dataTransfer.files)
    },

    _leerArchivos(files) {
      // recorrer los archivos seleccionados
      for (const file of files) {
        // acepta solo imagenes 
        if (!file.type.startsWith('image/')) continue
        // leer la imagen 
        const reader = new FileReader()
        // Guarda  la vista previa de la imagen 
        reader.onload = e => {
          this.previews.push(e.target.result)
        }
        //convertir la imagen para poder mostrarla 
        reader.readAsDataURL(file)
      }
    },

    // ──────────────────────────────────────────
    // Helpers de UI
    // ──────────────────────────────────────────

    formatearFecha(fecha) {
      // verifica que exista una fecha,separar el año,mes y dia 
      if (!fecha) return '—'
      const [anio, mes, dia] = fecha.split('-')
      //lista de los meses
      const meses = [
        'enero','febrero','marzo','abril','mayo','junio',
        'julio','agosto','septiembre','octubre','noviembre','diciembre'
      ]
      //Devolve la fecha con un formato mas facil de leer
      return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`
    },

    // Genera un color consistente a partir del nombre del paciente
    avatarColor(nombre) {
      const colores = [
        '#185FA5', '#059669', '#d97706', '#dc2626',
        '#7c3aed', '#0891b2', '#be185d', '#65a30d'
      ]
      if (!nombre) return colores[0]
      const idx = nombre
        .split('')
        .reduce((acc, c) => acc + c.charCodeAt(0), 0) % colores.length
      return colores[idx]
    },

    // Retorna el triage más reciente del paciente
    ultimoTriage(paciente) {
      if (!paciente?.triages?.length) return null
      return paciente.triages[paciente.triages.length - 1]
    },
    //Verificar que exista un paciente,unir el nombre y los apellidosen un solo texto
    nombreCompleto(p) {
      if (!p) return ''
      return [p.nombre, p.apellido_paterno, p.apellido_materno]
        .filter(Boolean)
        .join(' ')
    },
    //Verifica que exista un nombre ,Obtener las iniciales del nombre 
    initials(nombre) {
      if (!nombre) return ''
      return nombre
        .split(' ')
        .map(n => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },
    //Asigna el color segun el estado del paceinte 
    chipClass(estado) {
      switch (estado) {
        case 'Atendido':  return 'cc-chip--green'
        case 'En espera': return 'cc-chip--amber'
        case 'Cancelado': return 'cc-chip--red'
        default:          return 'cc-chip--gray'
      }
    },
    //Asignar el color segun el nivel de trage 
    triageClass(estado) {
      switch (estado) {
        case 'grave':     return 'cc-chip--red'
        case 'moderado':  return 'cc-chip--amber'
        case 'leve':      return 'cc-chip--green'
        case 'Rojo':      return 'cc-chip--red'
        case 'Amarillo':  return 'cc-chip--amber'
        case 'Verde':     return 'cc-chip--green'
        default:          return 'cc-chip--gray'
      }
    }
  }
}
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css');

/* ─── Reset & base ─── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.cc-layout {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 16px;
  align-items: start;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

@media (max-width: 1024px) {
  .cc-layout { grid-template-columns: 1fr; }
}

/* ─── Shared surface ─── */
.cc-panel,
.cc-table-section {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 16px;
  overflow: hidden;
}

/* ─── Labels & dots ─── */
.cc-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.cc-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 8px;
}
.cc-dot--blue { background: #185FA5; }
.cc-dot--gray { background: #9ca3af; }

/* ─── Panel left ─── */
.cc-panel__head {
  display: flex;
  align-items: center;
  padding: 20px 22px 16px;
  border-bottom: 1px solid #f0f2f5;
  gap: 10px;
}

.cc-edit-flag {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  color: #92400e;
  background: #fef3c7;
  padding: 3px 9px;
  border-radius: 99px;
}

/* Hero */
.cc-hero {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 22px;
  background: #f8faff;
  border-bottom: 1px solid #f0f2f5;
}

.cc-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}

.cc-avatar--lg {
  width: 56px;
  height: 56px;
  font-size: 18px;
  border-radius: 14px;
}

.cc-hero__info { flex: 1; min-width: 0; }
.cc-hero__name {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Details */
.cc-details { padding: 4px 22px; }
.cc-details__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 11px 0;
  border-bottom: 1px dashed #eef0f3;
  gap: 12px;
}
.cc-details__row:last-child { border-bottom: none; }

.cc-details dt {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.cc-details dd {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
  text-align: right;
}

.cc-details__edit {
  display: flex;
  justify-content: flex-end;
}

.cc-mini-input,
.cc-mini-select {
  font-size: 12.5px;
  font-weight: 600;
  font-family: inherit;
  color: #111827;
  border: 1px solid #e5e7eb;
  border-radius: 7px;
  padding: 5px 8px;
  outline: none;
  background: #fff;
  max-width: 160px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cc-mini-input:focus,
.cc-mini-select:focus {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}

/* Symptoms */
.cc-symptoms {
  margin: 4px 22px 18px;
  padding: 14px;
  background: #fff5f5;
  border: 1px solid #fee2e2;
  border-radius: 10px;
}

.cc-symptoms__label {
  font-size: 12px;
  font-weight: 700;
  color: #b91c1c;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.cc-symptoms__text {
  font-size: 12.5px;
  color: #374151;
  line-height: 1.6;
}

.cc-symptoms__textarea {
  width: 100%;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 10px;
  font-size: 12.5px;
  font-family: inherit;
  color: #374151;
  line-height: 1.5;
  resize: vertical;
  outline: none;
  background: #fff;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cc-symptoms__textarea:focus {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}

/* Panel actions */
.cc-panel__actions {
  display: flex;
  gap: 8px;
  padding: 14px 22px;
  background: #f8fafc;
  border-top: 1px solid #f0f2f5;
}

/* ─── Chips ─── */
.cc-chip {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 99px;
  white-space: nowrap;
  letter-spacing: 0.02em;
}
.cc-chip--green { background: #d1fae5; color: #065f46; }
.cc-chip--blue  { background: #dbeafe; color: #1e40af; }
.cc-chip--red   { background: #fee2e2; color: #991b1b; }
.cc-chip--amber { background: #fef3c7; color: #92400e; }
.cc-chip--gray  { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }

/* ─── Mono ─── */
.cc-mono {
  font-family: 'JetBrains Mono', 'Fira Code', monospace;
  font-size: 11px;
  color: #185FA5;
  font-weight: 600;
}
.cc-mono--cell {
  background: #f0f4ff;
  padding: 3px 8px;
  border-radius: 6px;
  display: inline-block;
}

/* ─── Buttons ─── */
.cc-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 14px;
  cursor: pointer;
  border: none;
  text-decoration: none;
  transition: background 0.15s, transform 0.12s;
  white-space: nowrap;
}
.cc-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.cc-btn:active:not(:disabled) { transform: scale(0.97); }

.cc-btn--ghost {
  background: #fff;
  color: #374151;
  border: 1px solid #e5e7eb;
}
.cc-btn--ghost:hover:not(:disabled) { background: #f9fafb; }

.cc-btn--primary {
  background: #185FA5;
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cc-btn--primary:hover:not(:disabled) { background: #0c447c; }

.cc-btn--success {
  background: #059669;
  color: #fff;
  flex: 1;
  justify-content: center;
}
.cc-btn--success:hover:not(:disabled) { background: #047857; }

/* ─── Table section ─── */
.cc-table-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 22px 16px;
  border-bottom: 1px solid #f0f2f5;
}

.cc-badge-count {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  padding: 4px 12px;
  border-radius: 99px;
}

.cc-table-search {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 16px 22px 4px;
  padding: 0 14px;
  height: 42px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #fafafa;
  transition: border-color 0.15s, background 0.15s;
}
.cc-table-search:focus-within {
  border-color: #185FA5;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}
.cc-table-search i { color: #9ca3af; font-size: 16px; flex-shrink: 0; }
.cc-table-search input {
  border: none;
  outline: none;
  background: transparent;
  width: 100%;
  height: 100%;
  font-size: 13px;
  color: #111827;
  font-family: inherit;
}

.cc-table-wrap { overflow-x: auto; }

.cc-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.cc-table thead th {
  padding: 10px 16px;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: #fafafa;
  border-bottom: 1px solid #f0f2f5;
}
.cc-table thead th:first-child { padding-left: 22px; }
.cc-table thead th:last-child  { padding-right: 22px; }
.cc-th--right { text-align: right; }

.cc-row td {
  padding: 14px 16px;
  border-bottom: 1px solid #f7f8fa;
  vertical-align: middle;
  color: #111827;
}
.cc-row td:first-child { padding-left: 22px; }
.cc-row td:last-child  { padding-right: 22px; }
.cc-row:last-child td  { border-bottom: none; }
.cc-row:hover td       { background: #fafbff; }
.cc-row--active td     { background: #f0f6ff; }

.cc-table-empty {
  text-align: center;
  padding: 32px 16px;
  color: #9ca3af;
  font-size: 13px;
}

.cc-td--patient { display: flex; align-items: center; gap: 12px; }
.cc-td--actions { text-align: right; white-space: nowrap; }

.cc-mini-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}
.cc-mini-avatar--lg { width: 44px; height: 44px; border-radius: 12px; font-size: 15px; }

.cc-patient-name { font-weight: 700; color: #111827; font-size: 13px; }
.cc-patient-diag { font-size: 12px; color: #9ca3af; margin-top: 1px; }

/* Botones de acción circulares */
.cc-icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #f3f4f6;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  color: #374151;
  transition: background 0.15s, transform 0.12s;
  margin-left: 4px;
}
.cc-icon-btn:hover  { transform: translateY(-1px); }
.cc-icon-btn:active { transform: scale(0.94); }

.cc-icon-btn--view          { background: #dbeafe; color: #2563eb; }
.cc-icon-btn--view:hover    { background: #bfdbfe; }
.cc-icon-btn--folder        { background: #ccfbf1; color: #0d9488; }
.cc-icon-btn--folder:hover  { background: #99f6e4; }
.cc-icon-btn--edit          { background: #fef3c7; color: #d97706; }
.cc-icon-btn--edit:hover    { background: #fde68a; }
.cc-icon-btn--danger        { background: #fee2e2; color: #ef4444; }
.cc-icon-btn--danger:hover  { background: #fecaca; }

/* ─── Overlay & Modal ─── */
.cc-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  z-index: 9000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.cc-modal {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}
.cc-modal--lg { max-width: 680px; }

.cc-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  gap: 12px;
  flex-shrink: 0;
}
.cc-modal__header--dark { background: #0f172a; }
.cc-modal__header--blue { background: #185FA5; }

.cc-modal__close {
  background: transparent;
  border: none;
  color: rgba(255,255,255,0.7);
  font-size: 18px;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  flex-shrink: 0;
}
.cc-modal__close:hover { background: rgba(255,255,255,0.1); color: #fff; }

.cc-modal__body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.cc-modal__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  background: #f8fafc;
  border: 1px solid #e8eaed;
  border-radius: 12px;
  padding: 16px;
}
.cc-modal__grid-full { grid-column: 1 / -1; }

.cc-modal__footer {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #f0f2f5;
  flex-shrink: 0;
}

/* ─── Fields ─── */
.cc-field-label {
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 5px;
}
.cc-field-value         { font-size: 14px; font-weight: 600; color: #111827; }
.cc-field-value--muted  { font-size: 13px; font-weight: 400; color: #6b7280; line-height: 1.6; }

/* ─── Expediente form ─── */
.cc-exp-ref {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #f8fafc;
  border: 1px solid #e8eaed;
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 22px;
}
.cc-exp-ref__name  { font-size: 15px; font-weight: 700; color: #111827; }
.cc-exp-ref__folio { font-size: 12px; color: #9ca3af; margin-top: 2px; }

.cc-section-label {
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.cc-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 24px;
}

.cc-field       { display: flex; flex-direction: column; }
.cc-field--full { grid-column: 1 / -1; }

.cc-input-wrap {
  display: flex;
  align-items: center;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 0 12px;
  gap: 8px;
  background: #fff;
  transition: border-color 0.15s;
}
.cc-input-wrap:focus-within {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}
.cc-input-wrap i { color: #9ca3af; font-size: 15px; flex-shrink: 0; }
.cc-input-wrap input {
  border: none;
  outline: none;
  height: 42px;
  font-size: 13px;
  width: 100%;
  color: #111827;
  background: transparent;
}

.cc-field textarea {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  color: #111827;
  resize: vertical;
  width: 100%;
  outline: none;
  font-family: inherit;
  transition: border-color 0.15s;
}
.cc-field textarea:focus {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}

/* ─── Dropzone ─── */
.cc-dropzone {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 28px 20px;
  text-align: center;
  cursor: pointer;
  background: #fafafa;
  transition: border-color 0.2s, background 0.2s;
  margin-bottom: 12px;
}
.cc-dropzone:hover {
  border-color: #185FA5;
  background: #f0f6ff;
}
.cc-dropzone i   { font-size: 32px; color: #185FA5; margin-bottom: 8px; display: block; }
.cc-dropzone p   { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.cc-dropzone span { font-size: 12px; color: #9ca3af; }

.cc-previews {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}
.cc-preview {
  position: relative;
  width: 86px;
  height: 86px;
}
.cc-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
}
.cc-preview button {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #ef4444;
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ─── Transitions ─── */
.cc-fade-enter-active, .cc-fade-leave-active { transition: opacity 0.2s; }
.cc-fade-enter-from,   .cc-fade-leave-to     { opacity: 0; }
</style>