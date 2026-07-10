<template>
  <div class="cc-layout">

    <!-- ═══════════════════════════════════════
         PANEL IZQUIERDO: PACIENTE ACTIVO
    ═══════════════════════════════════════ -->
    <div class="cc-panel">
      <div class="cc-panel__head">
        <span class="cc-dot cc-dot--blue"></span>
        <span class="cc-label">Panel de atención</span>
        <!-- Indicador de modo edición activo -->
        <span v-if="editMode" class="cc-edit-flag">
          <i class="ti ti-pencil" aria-hidden="true"></i> Editando
        </span>
      </div>

      <!-- Hero: avatar + nombre + folio del paciente activo -->
      <div v-if="pacienteActivo" class="cc-hero">
        <div class="cc-avatar" :style="{ background: avatarColor(nombreCompleto(pacienteActivo)) }">
          {{ initials(nombreCompleto(pacienteActivo)) }}
        </div>
        <div class="cc-hero__info">
          <p class="cc-hero__name">{{ nombreCompleto(pacienteActivo) }}</p>
          <span class="cc-mono">{{ pacienteActivo.paciente_id }}</span>
        </div>
      </div>
      <!-- Estado vacío cuando no hay paciente seleccionado -->
      <div v-else class="cc-hero">
        <div class="cc-hero__info">
          <p class="cc-hero__name" style="color: #9ca3af;">Sin paciente seleccionado</p>
        </div>
      </div>

      <!-- Detalles del paciente activo: motivo, teléfono, triage y estado -->
      <dl class="cc-details">
        <!-- Motivo de consulta: texto en modo lectura, input en modo edición -->
        <div class="cc-details__row">
          <dt><i class="ti ti-stethoscope" aria-hidden="true"></i> Motivo de consulta</dt>
          <dd v-if="!editMode">{{ ultimoTriage(pacienteActivo)?.motivo_consulta || '—' }}</dd>
          <dd v-else class="cc-details__edit">
            <input type="text" class="cc-mini-input" v-model="editForm.diagnostico" placeholder="Motivo de consulta" />
          </dd>
        </div>
        <!-- Teléfono: solo lectura -->
        <div class="cc-details__row">
          <dt><i class="ti ti-phone" aria-hidden="true"></i> Teléfono</dt>
          <dd>{{ pacienteActivo?.telefono || '—' }}</dd>
        </div>
        <!-- Triage: chip en lectura, select en edición -->
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
        <!-- Estado: chip en lectura, select en edición -->
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

      <!-- Síntomas: texto en lectura, textarea en edición -->
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

      <!-- Acciones del panel: ver, editar, expediente (modo lectura) -->
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
      <!-- Acciones del panel: cancelar y guardar (modo edición) -->
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
      <!-- Encabezado: título + contador de pacientes -->
      <div class="cc-table-head">
        <div>
          <span class="cc-dot cc-dot--gray"></span>
          <span class="cc-label">Lista de espera del día</span>
        </div>
        <span class="cc-badge-count">{{ pacientesFiltrados.length }} pacientes</span>
      </div>

      <!-- Buscador por nombre o folio -->
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
            <!-- Una fila por cada paciente filtrado -->
            <!-- cc-row--active resalta la fila del paciente seleccionado en el panel -->
            <tr
              v-for="p in pacientesFiltrados"
              :key="p.id"
              class="cc-row"
              :class="{ 'cc-row--active': pacienteActivo && p.paciente_id === pacienteActivo.paciente_id }"
            >
              <!-- Avatar circular + nombre en negritas + motivo de consulta debajo -->
              <td class="cc-td--patient">
                <div class="cc-mini-avatar" :style="{ background: avatarColor(nombreCompleto(p)) }">
                  {{ initials(nombreCompleto(p)) }}
                </div>
                <div>
                  <p class="cc-patient-name">{{ nombreCompleto(p) }}</p>
                  <p class="cc-patient-diag">{{ ultimoTriage(p)?.motivo_consulta || '—' }}</p>
                </div>
              </td>
              <!-- Folio en azul -->
              <td><span class="cc-mono cc-mono--cell">{{ p.paciente_id }}</span></td>
              <!-- Chip de estado (Activo, En espera, etc.) -->
              <td><span class="cc-chip" :class="chipClass(p.estado)">{{ p.estado || 'Sin asignar' }}</span></td>
              <!-- Chip de urgencia/triage -->
              <td><span class="cc-chip" :class="triageClass(ultimoTriage(p)?.estado)">{{ ultimoTriage(p)?.estado || 'Sin asignar' }}</span></td>
              <!-- Botones de acción: ojo azul y carpeta cyan sobre fondo gris neutro -->
              <td class="cc-td--actions">
                <button class="cc-icon-btn cc-icon-btn--view" title="Ver / seleccionar" @click="verPaciente(p)">
                  <i class="ti ti-eye" aria-hidden="true"></i>
                </button>
                <button class="cc-icon-btn cc-icon-btn--folder" title="Expediente" @click="abrirExpedienteDesdeFila(p)">
                  <i class="ti ti-folder" aria-hidden="true"></i>
                </button>
              </td>
            </tr>
            <!-- Estado vacío cuando no hay resultados -->
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

      <!-- MODAL: FICHA CLÍNICA (detalle de solo lectura) -->
      <!-- @click.self cierra el modal al hacer clic fuera del cuadro -->
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
                <!-- Estado actual del paciente -->
                <div>
                  <p class="cc-field-label">Estado actual</p>
                  <span class="cc-chip" :class="chipClass(modal.paciente?.estado)">
                    {{ modal.paciente?.estado }}
                  </span>
                </div>
                <!-- Nivel de triage -->
                <div>
                  <p class="cc-field-label">Triage / prioridad</p>
                  <span class="cc-chip" :class="triageClass(modal.paciente?.triage)">
                    {{ modal.paciente?.triage }}
                  </span>
                </div>
                <!-- Fecha de atención formateada -->
                <div class="cc-modal__grid-full">
                  <p class="cc-field-label">
                    <i class="ti ti-calendar" aria-hidden="true"></i> Fecha de atención
                  </p>
                  <p class="cc-field-value">{{ formatearFecha(modal.paciente?.fecha) }}</p>
                </div>
                <!-- Síntomas y notas de admisión -->
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
              <!-- Abre el expediente completo del paciente -->
              <button class="cc-btn cc-btn--primary" @click="irAExpediente(modal.paciente)">
                <i class="ti ti-folder-open" aria-hidden="true"></i> Abrir expediente
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- MODAL: EXPEDIENTE CLÍNICO (formulario completo) -->
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

              <!-- Referencia visual del paciente al que pertenece el expediente -->
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

              <!-- ── Sección 1: Datos generales ── -->
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

              <!-- ── Sección 2: Signos vitales ── -->
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

              <!-- ── Sección 3: Antecedentes médicos ── -->
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

              <!-- ── Sección 4: Radiografías / estudios visuales ── -->
              <p class="cc-section-label">
                <i class="ti ti-x-ray" aria-hidden="true"></i> 4. Carga de radiografías / estudios visuales
              </p>
              <!-- Zona de arrastre: clic o drag & drop para subir imágenes -->
              <div
                class="cc-dropzone"
                @dragover.prevent
                @drop.prevent="onDrop"
                @click="$refs.fileInput.click()"
              >
                <i class="ti ti-cloud-upload" aria-hidden="true"></i>
                <p>Arrastra archivos aquí o haz clic para buscar</p>
                <span>JPG, PNG — varias imágenes a la vez</span>
                <!-- Input oculto activado por el clic en la dropzone -->
                <input
                  ref="fileInput"
                  type="file"
                  multiple
                  accept="image/*"
                  @change="onFileChange"
                  style="display:none"
                />
              </div>
              <!-- Previews de imágenes cargadas, con botón para eliminar cada una -->
              <div v-if="previews.length" class="cc-previews">
                <div v-for="(src, i) in previews" :key="i" class="cc-preview">
                  <img :src="src" alt="preview" />
                  <button @click.stop="previews.splice(i, 1)" aria-label="Eliminar imagen">
                    <i class="ti ti-x" aria-hidden="true"></i>
                  </button>
                </div>
              </div>

            </div>
            <!-- Footer del modal: cancelar o guardar expediente -->
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
      // Lista principal de pacientes traída de la API
      pacientes: [],

      // Paciente mostrado en el panel izquierdo
      pacienteActivo: null,

      // Texto escrito en el buscador (filtra por nombre o folio)
      busqueda: '',

      // Controla si el panel izquierdo está en modo edición
      editMode: false,

      // Datos temporales del formulario de edición del panel
      editForm: {
        diagnostico: '',
        sintomas: '',
        triage: '',
        estado: ''
      },

      // Opciones disponibles en los selectores del modo edición
      triageOpciones: ['Rojo', 'Amarillo', 'Verde'],
      estadoOpciones: ['En espera', 'Atendido', 'Cancelado'],

      // Controla qué modal está abierto y a qué paciente pertenece
      modal: {
        tipo: '',       // 'detalle' | 'expediente' | '' (vacío = cerrado)
        paciente: null
      },

      // Imágenes cargadas en el dropzone del expediente (base64)
      previews: [],

      // Formulario completo del expediente clínico
      form: {
        paciente_id:              '',
        codigo_paciente:          '',
        // ── Datos generales
        telefono:                 '',
        email:                    '',
        sexo:                     '',
        tipo_sangre:              '',
        fecha_nacimiento:         '',
        curp:                     '',
        direccion:                '',
        contacto_emergencia:      '',
        telefono_emergencia:      '',
        // ── Signos vitales (vienen del triage)
        peso:                     '',
        talla:                    '',
        presion:                  '',
        temperatura:              '',
        saturacion:               '',
        frecuencia_cardiaca:      '',
        motivo_consulta:          '',
        sintomas:                 '',
        diagnostico:              '',
        // ── Antecedentes médicos
        alergias:                 '',
        enfermedades_cronicas:    '',
        medicamentos_actuales:    '',
        antecedentes_quirurgicos: ''
      }
    }
  },

  computed: {
    // Filtra la lista de pacientes según el texto del buscador
    // Compara contra nombre completo y folio (paciente_id)
    pacientesFiltrados() {
      if (!this.busqueda.trim()) return this.pacientes
      const q = this.busqueda.toLowerCase()
      return this.pacientes.filter(p =>
        (p.nombre || '').toLowerCase().includes(q) ||
        (p.paciente_id || '').toLowerCase().includes(q)
      )
    }
  },

  // Al montar el componente, carga la lista de pacientes
  mounted() {
    this.obtenerPacientes()
  },

  methods: {

    // ──────────────────────────────────────────
    // API
    // ──────────────────────────────────────────

    async obtenerPacientes() {
      try {
        // Solicita la lista de pacientes al backend
        const response = await ApiService.get('/pacientes')
        this.pacientes = response.data
        // Selecciona el primer paciente por defecto si existe
        if (this.pacientes.length > 0) {
          this.pacienteActivo = this.pacientes[0]
        }
      } catch (error) {
        console.error('Error al obtener pacientes:', error)
      }
    },

    async guardarCambiosPaciente() {
      try {
        // Envía los datos actualizados del paciente activo al backend
        await ApiService.put('/pacientes/' + this.pacienteActivo.id, this.pacienteActivo)
        await this.obtenerPacientes()
      } catch (error) {
        console.error('Error al guardar paciente:', error)
      }
    },

    // ──────────────────────────────────────────
    // Panel izquierdo
    // ──────────────────────────────────────────

    verPaciente(paciente) {
      // Carga el paciente seleccionado en el panel y sale del modo edición
      this.pacienteActivo = paciente
      this.editMode = false
    },

    iniciarEdicion(paciente) {
      if (!paciente) return
      // Activa el modo edición y precarga el formulario con los datos actuales
      this.editMode = true
      this.editForm = {
        diagnostico: paciente.diagnostico || '',
        sintomas:    paciente.sintomas    || '',
        triage:      paciente.triage      || '',
        estado:      paciente.estado      || ''
      }
    },

    cancelarEdicion() {
      // Sale del modo edición sin guardar cambios
      this.editMode = false
    },

    guardarEdicion() {
      if (!this.pacienteActivo) return
      // Aplica los cambios del formulario al paciente activo
      Object.assign(this.pacienteActivo, this.editForm)
      // Sincroniza también el objeto dentro del array de pacientes
      const idx = this.pacientes.findIndex(p => p.id === this.pacienteActivo.id)
      if (idx !== -1) {
        Object.assign(this.pacientes[idx], this.editForm)
      }
      this.editMode = false
      // Descomenta para persistir en la API:
      // this.guardarCambiosPaciente()
    },

    // ──────────────────────────────────────────
    // Modales
    // ──────────────────────────────────────────

    abrirModal(tipo, paciente) {
      if (!paciente) return
      this.modal.tipo    = tipo
      this.modal.paciente = paciente

      // Si se abre el expediente, precarga el formulario con los datos del paciente
      if (tipo === 'expediente') {
        const t = this.ultimoTriage(paciente) || {}
        this.form = {
          paciente_id:              paciente.id                         || '',
          codigo_paciente:          paciente.paciente_id                || '',
          // datos generales
          telefono:                 paciente.telefono                   || '',
          email:                    paciente.email                      || '',
          sexo:                     paciente.sexo                       || '',
          tipo_sangre:              paciente.tipo_sangre                || '',
          fecha_nacimiento:         paciente.fecha_nacimiento           || '',
          curp:                     paciente.curp                       || '',
          direccion:                paciente.direccion                  || '',
          contacto_emergencia:      paciente.contacto_emergencia        || '',
          telefono_emergencia:      paciente.telefono_emergencia        || '',
          // signos vitales del último triage
          peso:                     t.peso                              || '',
          talla:                    t.talla                             || '',
          presion:                  t.presion                           || '',
          temperatura:              t.temperatura                       || '',
          saturacion:               t.saturacion                        || '',
          frecuencia_cardiaca:      t.frecuencia_cardiaca               || '',
          motivo_consulta:          t.motivo_consulta                   || '',
          sintomas:                 t.sintomas                          || '',
          diagnostico:              '',
          // antecedentes
          alergias:                 paciente.alergias                   || '',
          enfermedades_cronicas:    paciente.enfermedades_cronicas      || '',
          medicamentos_actuales:    paciente.medicamentos_actuales      || '',
          antecedentes_quirurgicos: paciente.antecedentes_quirurgicos   || ''
        }
        this.previews = []
      }
    },

    cerrarModal() {
      // Cierra el modal activo y limpia la referencia al paciente
      this.modal.tipo     = ''
      this.modal.paciente = null
    },

    abrirExpedienteDesdeFila(paciente) {
      // Atajo para abrir el expediente directamente desde la tabla
      this.abrirModal('expediente', paciente)
    },

    irAExpediente(paciente) {
      // Desde el modal de detalle, abre el expediente del mismo paciente
      this.abrirModal('expediente', paciente)
    },

    guardarExpediente() {
      // Aquí conectar con la API para persistir el expediente
      console.log('Expediente guardado:', {
        paciente: this.modal.paciente,
        form:     this.form,
        imagenes: this.previews.length
      })
      this.cerrarModal()
    },

    // ──────────────────────────────────────────
    // Archivos / imágenes (dropzone)
    // ──────────────────────────────────────────

    onFileChange(event) {
      // Dispara la lectura de archivos seleccionados con el input
      this._leerArchivos(event.target.files)
    },

    onDrop(event) {
      // Dispara la lectura de archivos soltados en la dropzone
      this._leerArchivos(event.dataTransfer.files)
    },

    _leerArchivos(files) {
      for (const file of files) {
        // Solo acepta imágenes (jpg, png, etc.)
        if (!file.type.startsWith('image/')) continue
        const reader = new FileReader()
        // Agrega la imagen como base64 al array de previews
        reader.onload = e => { this.previews.push(e.target.result) }
        reader.readAsDataURL(file)
      }
    },

    // ──────────────────────────────────────────
    // Helpers de UI
    // ──────────────────────────────────────────

    formatearFecha(fecha) {
      // Convierte "2024-03-15" → "15 de marzo de 2024"
      if (!fecha) return '—'
      const [anio, mes, dia] = fecha.split('-')
      const meses = [
        'enero','febrero','marzo','abril','mayo','junio',
        'julio','agosto','septiembre','octubre','noviembre','diciembre'
      ]
      return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`
    },

    avatarColor(nombre) {
      // Genera un color consistente para cada nombre (mismo nombre = mismo color siempre)
      const colores = [
        '#185FA5','#059669','#d97706','#dc2626',
        '#7c3aed','#0891b2','#be185d','#65a30d'
      ]
      if (!nombre) return colores[0]
      const idx = nombre
        .split('')
        .reduce((acc, c) => acc + c.charCodeAt(0), 0) % colores.length
      return colores[idx]
    },

    ultimoTriage(paciente) {
      // Retorna el triage más reciente del paciente (último del array)
      if (!paciente?.triages?.length) return null
      return paciente.triages[paciente.triages.length - 1]
    },

    nombreCompleto(p) {
      // Une nombre + apellido paterno + apellido materno, omitiendo los vacíos
      if (!p) return ''
      return [p.nombre, p.apellido_paterno, p.apellido_materno]
        .filter(Boolean)
        .join(' ')
    },

    initials(nombre) {
      // Genera las iniciales del nombre (máx. 2 letras) para el avatar
      if (!nombre) return ''
      return nombre
        .split(' ')
        .map(n => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },

    chipClass(estado) {
      // Devuelve la clase CSS del chip según el estado del paciente
      switch (estado) {
        case 'Atendido':  return 'cc-chip--green'
        case 'En espera': return 'cc-chip--amber'
        case 'Cancelado': return 'cc-chip--red'
        default:          return 'cc-chip--gray'
      }
    },

    triageClass(estado) {
      // Devuelve la clase CSS del chip según el nivel de triage
      switch (estado) {
        case 'grave':    case 'Rojo':     return 'cc-chip--red'
        case 'moderado': case 'Amarillo': return 'cc-chip--amber'
        case 'leve':     case 'Verde':    return 'cc-chip--green'
        default:                          return 'cc-chip--gray'
      }
    }
  }
}
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css');
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* ─── Reset & base ─── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* Layout principal: panel izquierdo fijo + tabla a la derecha */
.cc-layout {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 20px;
  align-items: start;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  background: #f4f6f9;
  padding: 20px;
  min-height: 100vh;
}

/* En pantallas chicas, apila panel y tabla en una sola columna */
@media (max-width: 1024px) {
  .cc-layout { grid-template-columns: 1fr; }
}

/* ─── Superficies compartidas (panel y tabla) ─── */
.cc-panel,
.cc-table-section {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}

/* ─── Labels & dots de sección ─── */
.cc-label { font-size: 13px; font-weight: 600; color: #374151; }

.cc-dot {
  display: inline-block;
  width: 8px; height: 8px;
  border-radius: 50%;
  margin-right: 8px;
}
.cc-dot--blue { background: #185FA5; }
.cc-dot--gray { background: #9ca3af; }

/* ─── Cabecera del panel izquierdo ─── */
.cc-panel__head {
  display: flex;
  align-items: center;
  padding: 20px 22px 16px;
  border-bottom: 1px solid #f0f2f5;
  gap: 10px;
}

/* Indicador "Editando" que aparece en modo edición */
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

/* ─── Hero: avatar + nombre del paciente activo ─── */
.cc-hero {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 22px;
  background: #f8faff;
  border-bottom: 1px solid #f0f2f5;
}

/* Avatar circular del panel izquierdo */
.cc-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; font-weight: 700;
  color: #fff;
  flex-shrink: 0;
  letter-spacing: 0.03em;
}

.cc-hero__info { flex: 1; min-width: 0; }
.cc-hero__name {
  font-size: 14px; font-weight: 700; color: #111827;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ─── Detalles del paciente (dl/dt/dd) ─── */
.cc-details { padding: 4px 22px; }
.cc-details__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 11px 0;
  border-bottom: 1px solid #f3f4f6;
  gap: 12px;
}
.cc-details__row:last-child { border-bottom: none; }

.cc-details dt {
  font-size: 12px; color: #6b7280; font-weight: 500;
  display: flex; align-items: center; gap: 6px;
  white-space: nowrap;
}
.cc-details dd {
  font-size: 13px; font-weight: 600; color: #111827;
  text-align: right;
}
.cc-details__edit { display: flex; justify-content: flex-end; }

/* Inputs pequeños del modo edición del panel */
.cc-mini-input,
.cc-mini-select {
  font-size: 12.5px; font-weight: 600; font-family: inherit;
  color: #111827;
  border: 1px solid #e5e7eb; border-radius: 7px;
  padding: 5px 8px; outline: none;
  background: #fff; max-width: 160px;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cc-mini-input:focus,
.cc-mini-select:focus {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}

/* ─── Caja de síntomas ─── */
.cc-symptoms {
  margin: 4px 22px 18px;
  padding: 14px;
  background: #fff5f5;
  border: 1px solid #fee2e2;
  border-radius: 10px;
}
.cc-symptoms__label {
  font-size: 12px; font-weight: 700; color: #b91c1c;
  margin-bottom: 6px;
  display: flex; align-items: center; gap: 6px;
}
.cc-symptoms__text { font-size: 12.5px; color: #374151; line-height: 1.6; }
.cc-symptoms__textarea {
  width: 100%;
  border: 1px solid #fecaca; border-radius: 8px;
  padding: 10px;
  font-size: 12.5px; font-family: inherit; color: #374151;
  line-height: 1.5; resize: vertical; outline: none; background: #fff;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cc-symptoms__textarea:focus {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
}

/* ─── Barra de acciones del panel ─── */
.cc-panel__actions {
  display: flex; gap: 8px;
  padding: 14px 22px;
  background: #f8fafc;
  border-top: 1px solid #f0f2f5;
}

/* ─── Chips de estado y triage ─── */
.cc-chip {
  display: inline-block;
  font-size: 11px; font-weight: 700;
  padding: 4px 12px;
  border-radius: 99px;
  white-space: nowrap;
  letter-spacing: 0.02em;
}
.cc-chip--green { background: #d1fae5; color: #065f46; }
.cc-chip--blue  { background: #dbeafe; color: #1e40af; }
.cc-chip--red   { background: #fee2e2; color: #991b1b; }
.cc-chip--amber { background: #fef3c7; color: #92400e; }
/* Gris-verde suave para "Activo" (como en la foto de referencia) */
.cc-chip--gray  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

/* ─── Texto monoespacio (folio) ─── */
.cc-mono {
  font-family: 'Inter', monospace;
  font-size: 11px; color: #185FA5; font-weight: 600;
}
.cc-mono--cell { color: #185FA5; font-size: 11px; font-weight: 600; }

/* ─── Botones generales ─── */
.cc-btn {
  display: inline-flex; align-items: center; gap: 6px;
  border-radius: 9px;
  font-size: 13px; font-weight: 600;
  padding: 8px 14px;
  cursor: pointer; border: none;
  transition: background 0.15s, transform 0.12s;
  white-space: nowrap;
}
.cc-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.cc-btn:active:not(:disabled) { transform: scale(0.97); }

.cc-btn--ghost {
  background: #fff; color: #374151;
  border: 1px solid #e5e7eb;
}
.cc-btn--ghost:hover:not(:disabled) { background: #f9fafb; }

.cc-btn--primary {
  background: #185FA5; color: #fff;
  flex: 1; justify-content: center;
}
.cc-btn--primary:hover:not(:disabled) { background: #0c447c; }

.cc-btn--success {
  background: #059669; color: #fff;
  flex: 1; justify-content: center;
}
.cc-btn--success:hover:not(:disabled) { background: #047857; }

/* ════════════════════════════════════════
   TABLA DE ESPERA
   ════════════════════════════════════════ */

/* Cabecera de la sección tabla: título + contador */
.cc-table-head {
  display: flex;
  justify-content: space-between; align-items: center;
  padding: 20px 24px 16px;
  border-bottom: 1px solid #f0f2f5;
}

.cc-badge-count {
  font-size: 12px; font-weight: 600; color: #6b7280;
  background: #f3f4f6; border: 1px solid #e5e7eb;
  padding: 4px 12px; border-radius: 99px;
}

/* Buscador integrado como línea con separador inferior */
.cc-table-search {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 24px;
  border-bottom: 1px solid #f0f2f5;
  background: #fff;
}
.cc-table-search i { color: #9ca3af; font-size: 16px; flex-shrink: 0; }
.cc-table-search input {
  border: none; outline: none; background: transparent;
  width: 100%; font-size: 13px; color: #111827; font-family: inherit;
}
.cc-table-search input::placeholder { color: #9ca3af; }

.cc-table-wrap { overflow-x: auto; }

/* Tabla sin bordes externos, solo separadores entre filas */
.cc-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

/* Cabecera de columnas */
.cc-table thead th {
  padding: 10px 24px;
  text-align: left;
  font-size: 12px; font-weight: 600; color: #6b7280;
  background: #fafafa;
  border-bottom: 1px solid #f0f2f5;
  white-space: nowrap;
}
.cc-th--right { text-align: right; }

/* Filas de datos */
.cc-row td {
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
  color: #111827;
  background: #fff;
}
.cc-row:last-child td { border-bottom: none; }
.cc-row:hover td      { background: #fafbff; }      /* hover sutil */
.cc-row--active td    { background: #eff6ff; }      /* fila del paciente activo */

/* Estado vacío cuando el buscador no encuentra resultados */
.cc-table-empty {
  text-align: center; padding: 40px 16px;
  color: #9ca3af; font-size: 13px;
}

/* Celda paciente: avatar circular + nombre + motivo alineados */
.cc-td--patient { display: flex; align-items: center; gap: 14px; }
.cc-td--actions { text-align: right; white-space: nowrap; }

/* Avatar circular en la tabla (igual que en la foto de referencia) */
.cc-mini-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;                     /* ← círculo */
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  color: #fff; flex-shrink: 0;
  letter-spacing: 0.03em;
}
.cc-mini-avatar--lg {
  width: 48px; height: 48px;
  border-radius: 50%; font-size: 16px;
}

/* Nombre del paciente en negritas */
.cc-patient-name {
  font-weight: 700; color: #111827;
  font-size: 13.5px; line-height: 1.3;
}

/* Texto secundario debajo del nombre (motivo de consulta) en azul */
.cc-patient-diag {
  font-size: 11.5px; color: #185FA5;
  font-weight: 600; margin-top: 2px;
}

/* ════════════════════════════════════════
   BOTONES DE ACCIÓN CIRCULARES
   Fondo gris neutro en reposo,
   color solo en el ícono (como en la foto)
   ════════════════════════════════════════ */
.cc-icon-btn {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: #f0f2f5;              /* gris neutro — igual en todos */
  border: none; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 17px;
  transition: background 0.15s, transform 0.12s;
  margin-left: 6px;
}
.cc-icon-btn:hover  { transform: translateY(-1px); }
.cc-icon-btn:active { transform: scale(0.94); }

/* Ojo — azul eléctrico como en la foto */
.cc-icon-btn--view       { background: #f0f2f5; color: #2563eb; }
.cc-icon-btn--view:hover { background: #dbeafe; }

/* Carpeta — cyan/turquesa como en la foto */
.cc-icon-btn--folder       { background: #f0f2f5; color: #06b6d4; }
.cc-icon-btn--folder:hover { background: #cffafe; }

/* Editar — ámbar (por si se usa en otro lugar) */
.cc-icon-btn--edit       { background: #f0f2f5; color: #d97706; }
.cc-icon-btn--edit:hover { background: #fef3c7; }

/* Eliminar — rojo (por si se usa en otro lugar) */
.cc-icon-btn--danger       { background: #f0f2f5; color: #ef4444; }
.cc-icon-btn--danger:hover { background: #fee2e2; }

/* ─── Overlay y modales ─── */
/* Fondo semitransparente que cubre toda la pantalla */
.cc-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.5);
  z-index: 9000;
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}

/* Cuadro blanco del modal */
.cc-modal {
  background: #fff;
  border-radius: 20px;
  width: 100%; max-width: 480px;
  overflow: hidden;
  display: flex; flex-direction: column;
  max-height: 90vh;
  box-shadow: 0 20px 60px rgba(0,0,0,0.18);
}
/* Versión grande para el expediente */
.cc-modal--lg { max-width: 680px; }

/* Cabecera del modal con fondo de color */
.cc-modal__header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 18px 24px;
  font-size: 14px; font-weight: 700; color: #fff;
  gap: 12px; flex-shrink: 0;
}
.cc-modal__header--dark { background: #0f172a; }  /* ficha clínica */
.cc-modal__header--blue { background: #185FA5; }  /* expediente */

/* Botón circular "×" para cerrar el modal */
.cc-modal__close {
  background: transparent; border: none;
  color: rgba(255,255,255,0.7);
  font-size: 18px; cursor: pointer;
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 8px; flex-shrink: 0;
}
.cc-modal__close:hover { background: rgba(255,255,255,0.1); color: #fff; }

/* Cuerpo scrollable del modal */
.cc-modal__body { padding: 24px; overflow-y: auto; flex: 1; }

/* Grid 2 columnas para la ficha clínica */
.cc-modal__grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 14px;
  background: #f8fafc; border: 1px solid #e8eaed;
  border-radius: 12px; padding: 16px;
}
.cc-modal__grid-full { grid-column: 1 / -1; }

/* Footer del modal: botones Cancelar/Guardar */
.cc-modal__footer {
  display: flex; justify-content: space-between; gap: 10px;
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #f0f2f5;
  flex-shrink: 0;
}

/* ─── Campos del expediente ─── */
.cc-field-label {
  font-size: 11px; font-weight: 600; color: #9ca3af;
  text-transform: uppercase; letter-spacing: 0.06em;
  margin-bottom: 6px;
  display: flex; align-items: center; gap: 5px;
}
.cc-field-value        { font-size: 14px; font-weight: 600; color: #111827; }
.cc-field-value--muted { font-size: 13px; font-weight: 400; color: #6b7280; line-height: 1.6; }

/* Referencia visual del paciente en el encabezado del expediente */
.cc-exp-ref {
  display: flex; align-items: center; gap: 14px;
  background: #f8fafc; border: 1px solid #e8eaed;
  border-radius: 12px; padding: 14px 16px;
  margin-bottom: 22px;
}
.cc-exp-ref__name  { font-size: 15px; font-weight: 700; color: #111827; }
.cc-exp-ref__folio { font-size: 12px; color: #9ca3af; margin-top: 2px; }

/* Etiqueta de sección dentro del expediente */
.cc-section-label {
  font-size: 13px; font-weight: 700; color: #374151;
  margin-bottom: 14px;
  display: flex; align-items: center; gap: 8px;
}

/* Grid de 3 columnas para los campos del formulario */
.cc-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 24px;
}

.cc-field       { display: flex; flex-direction: column; }
.cc-field--full { grid-column: 1 / -1; }  /* campo que ocupa todo el ancho */

/* Input con ícono a la izquierda */
.cc-input-wrap {
  display: flex; align-items: center;
  border: 1px solid #e5e7eb; border-radius: 10px;
  padding: 0 12px; gap: 8px;
  background: #fff;
  transition: border-color 0.15s;
}
.cc-input-wrap:focus-within {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}
.cc-input-wrap i { color: #9ca3af; font-size: 15px; flex-shrink: 0; }
.cc-input-wrap input {
  border: none; outline: none;
  height: 42px; font-size: 13px;
  width: 100%; color: #111827; background: transparent;
}

/* Textarea del expediente */
.cc-field textarea {
  border: 1px solid #e5e7eb; border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px; color: #111827;
  resize: vertical; width: 100%;
  outline: none; font-family: inherit;
  transition: border-color 0.15s;
}
.cc-field textarea:focus {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}

/* ─── Dropzone de imágenes ─── */
.cc-dropzone {
  border: 2px dashed #d1d5db; border-radius: 12px;
  padding: 28px 20px; text-align: center;
  cursor: pointer; background: #fafafa;
  transition: border-color 0.2s, background 0.2s;
  margin-bottom: 12px;
}
.cc-dropzone:hover { border-color: #185FA5; background: #f0f6ff; }
.cc-dropzone i    { font-size: 32px; color: #185FA5; margin-bottom: 8px; display: block; }
.cc-dropzone p    { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.cc-dropzone span { font-size: 12px; color: #9ca3af; }

/* Galería de previews de imágenes cargadas */
.cc-previews { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
.cc-preview  { position: relative; width: 86px; height: 86px; }
.cc-preview img {
  width: 100%; height: 100%;
  object-fit: cover; border-radius: 10px;
  border: 1px solid #e5e7eb;
}
/* Botón rojo para eliminar una imagen del preview */
.cc-preview button {
  position: absolute; top: -6px; right: -6px;
  width: 22px; height: 22px; border-radius: 50%;
  background: #ef4444; color: #fff;
  border: none; cursor: pointer; font-size: 12px;
  display: flex; align-items: center; justify-content: center;
}

/* ─── Transición fade para los modales ─── */
.cc-fade-enter-active, .cc-fade-leave-active { transition: opacity 0.2s; }
.cc-fade-enter-from,   .cc-fade-leave-to     { opacity: 0; }
</style>