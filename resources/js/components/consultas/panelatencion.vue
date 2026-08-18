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
      <!-- Estado vacío cuando no hay paciente seleccionado / no hay citas hoy -->
      <div v-else class="cc-hero">
        <div class="cc-hero__info">
          <p class="cc-hero__name" style="color: #9ca3af;">{{ mensajeSinPaciente }}</p>
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
        <button class="cc-btn cc-btn--ghost" @click="cancelarEdicion" :disabled="guardandoEdicion">
          <i class="ti ti-x" aria-hidden="true"></i> Cancelar
        </button>
        <button class="cc-btn cc-btn--success" @click="guardarEdicion" :disabled="guardandoEdicion">
          <i class="ti ti-device-floppy" aria-hidden="true"></i> {{ guardandoEdicion ? 'Guardando...' : 'Guardar cambios' }}
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
        <input type="text" v-model="busqueda" placeholder="Buscar por nombre o folio… (busca en todos los pacientes)" />
      </div>

      <!-- Filtros: especialidad, médico y fecha con calendario (cruzando con /api/citas) -->
      <!-- Estos filtros se ignoran automáticamente mientras haya texto en el buscador de arriba -->
      <div class="cc-table-filtros" :class="{ 'cc-table-filtros--inactivos': busquedaActiva }">
        <select v-model="especialidadSeleccionada" class="cc-filtro-select" @change="onEspecialidadChange" :disabled="busquedaActiva">
          <option value="">Todas las especialidades</option>
          <option v-for="esp in especialidadesDisponibles" :key="esp.id" :value="esp.id">
            {{ esp.nombre }}
          </option>
        </select>

        <select v-model="medicoSeleccionado" class="cc-filtro-select" :disabled="busquedaActiva">
          <option value="">Todos los médicos</option>
          <option v-for="med in medicosFiltrados" :key="med.id" :value="med.id">
            {{ med.nombre }}
          </option>
        </select>

        <!-- Selector de fecha con calendario nativo del navegador -->
        <label class="cc-filtro-fecha">
          <i class="ti ti-calendar" aria-hidden="true"></i>
          <input
            type="date"
            v-model="fechaFiltro"
            class="cc-filtro-fecha-input"
            :disabled="busquedaActiva"
          />
        </label>

        <!-- Solo aparece cuando hay una fecha activa; la quita para ver todas -->
        <button
          v-if="fechaFiltro"
          type="button"
          class="cc-filtro-toggle"
          :disabled="busquedaActiva"
          @click="fechaFiltro = ''"
        >
          <i class="ti ti-x" aria-hidden="true"></i> Ver todas las fechas
        </button>
      </div>

      <!-- Aviso de cómo seleccionar un paciente para nueva consulta / estado del filtro actual -->
      <div class="cc-hint">
        <i class="ti ti-info-circle" aria-hidden="true"></i>
        <span v-if="busquedaActiva">
          Buscando "<strong>{{ busqueda }}</strong>" en todos los pacientes, sin importar fecha, médico o especialidad.
        </span>
        <span v-else>
          Doble clic sobre un paciente para seleccionarlo y usarlo en <strong>Nueva consulta</strong>.
          <template v-if="fechaFiltro"> · Mostrando citas del <strong>{{ formatearFecha(fechaFiltro) }}</strong></template>
        </span>
      </div>

      <div class="cc-table-wrap">
        <table class="cc-table">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Turno</th>
              <th>Paciente / Diagnóstico</th>
              <th>Folio</th>
              <th>Médico / Especialidad</th>
              <th>Estado</th>
              <th>Urgencia</th>
              <th class="cc-th--right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(listaPacientes, index) in pacientesFiltrados"
              :key="listaPacientes.id || index"
              class="cc-row"
              :class="{
                'cc-row--active': pacienteActivo && listaPacientes.paciente_id === pacienteActivo.paciente_id,
                'cc-row--selected': pacienteSeleccionadoId === listaPacientes.id
              }"
              title="Doble clic para seleccionar este paciente"
              @dblclick="seleccionarParaConsulta(listaPacientes)"
            > 
              <!-- Número consecutivo -->
              <td class="font-semibold text-gray-500">
                {{ index + 1 }}
              </td>
              
              <!-- Turno -->
              <td>
                <span class="px-2.5 py-1 text-xs font-bold bg-blue-100 text-blue-800 rounded-full">
                  #{{ listaPacientes.turno_numero ?? '—' }}
                </span>
              </td>
              
              <!-- Paciente / Diagnóstico -->
              <td class="cc-td--patient">
                <div class="cc-mini-avatar" :style="{ background: avatarColor(nombreCompleto(listaPacientes)) }">
                  {{ initials(nombreCompleto(listaPacientes)) }}
                </div>
                <div>
                  <p class="cc-patient-name">
                    {{ nombreCompleto(listaPacientes) }}
                    <i v-if="pacienteSeleccionadoId === listaPacientes.id" class="ti ti-circle-check cc-selected-icon" aria-hidden="true"></i>
                  </p>
                  <p class="cc-patient-diag">{{ ultimoTriage(listaPacientes)?.motivo_consulta || '—' }}</p>
                </div>
              </td>
              
              <!-- Folio -->
              <td><span class="cc-mono cc-mono--cell">{{ listaPacientes.turno_folio || '—' }}</span></td>
              
              <!-- Médico / Especialidad -->
             <td>
              <div class="text-xs font-medium text-gray-900">{{ listaPacientes.turno_medico ? listaPacientes.turno_medico.nombre : 'Sin médico' }}</div>
              <div class="text-xs text-gray-500">{{ listaPacientes.turno_especialidad ? listaPacientes.turno_especialidad.nombre : 'Sin especialidad' }}</div>
            </td>
              
              <!-- Estado -->
             <td><span class="cc-chip" :class="chipClassTurno(listaPacientes.turno_estado)">{{ listaPacientes.turno_estado || 'Sin asignar' }}</span></td>
              
              <!-- Urgencia -->
              <td><span class="cc-chip" :class="triageClass(ultimoTriage(listaPacientes)?.estado)">{{ ultimoTriage(listaPacientes)?.estado || 'Sin asignar' }}</span></td>
              
              <!-- Acciones -->
              <td class="cc-td--actions">
                <button class="cc-icon-btn cc-icon-btn--view" title="Ver / seleccionar" @click="verPaciente(listaPacientes)">
                  <i class="ti ti-eye" aria-hidden="true"></i>
                </button>
                <button
                  v-if="!tieneCitaHoy(listaPacientes)"
                  class="cc-icon-btn cc-icon-btn--add"
                  title="Agregar a la lista de espera"
                  @click="abrirModal('triage', listaPacientes)"
                >
                  <i class="ti ti-plus" aria-hidden="true"></i>
                </button>
                <button
                  v-else
                  class="cc-icon-btn cc-icon-btn--triage"
                  title="Editar signos vitales"
                  @click="abrirModal('triage', listaPacientes)"
                >
                  <i class="ti ti-shield-half" aria-hidden="true"></i>
                </button>
                <button class="cc-icon-btn cc-icon-btn--folder" title="Expediente" @click="abrirExpedienteDesdeFila(listaPacientes)">
                  <i class="ti ti-folder" aria-hidden="true"></i>
                </button>
                <button
                  class="cc-icon-btn cc-icon-btn--danger"
                  title="Finalizar y quitar de la lista de espera"
                  :disabled="finalizandoId === listaPacientes.id"
                  @click="finalizarPaciente(listaPacientes)"
                >
                  <i class="ti ti-trash" aria-hidden="true"></i>
                </button>
              </td>
            </tr>
            
            <tr v-if="pacientesFiltrados.length === 0">
              <td colspan="8" class="cc-table-empty">No se encontraron pacientes con ese criterio.</td>
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

      <!-- MODAL: EDITAR SIGNOS VITALES (edición rápida desde la tabla) -->
      <transition name="cc-fade">
        <div v-if="modal.tipo === 'triage'" class="cc-overlay" @click.self="cerrarModal">
          <div class="cc-modal cc-modal--md">
            <div class="cc-modal__header cc-modal__header--blue">
              <span><i class="ti ti-shield-half" aria-hidden="true"></i> {{ tieneCitaHoy(modal.paciente) ? 'Editar signos vitales' : 'Agregar paciente a la lista de espera' }}</span>
              <button class="cc-modal__close" @click="cerrarModal" aria-label="Cerrar">
                <i class="ti ti-x" aria-hidden="true"></i>
              </button>
            </div>
            <div class="cc-modal__body">
              <!-- Aviso: este paciente no tiene cita hoy, y al guardar se agregará a la lista -->
              <div
                v-if="modal.paciente && !tieneCitaHoy(modal.paciente)"
                class="cc-triage-aviso"
              >
                <i class="ti ti-info-circle" aria-hidden="true">
                </i>
                <p> Este paciente no tiene cita para hoy, al guardar 
                 se agregará al final de la
                <strong>lista de espera de hoy.</strong></p>
              </div>

              <!-- Referencia visual del paciente + badge de estado global del triaje -->
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
                <span v-if="overallTriageStatus" class="cc-overall-badge" :class="'cc-badge-' + overallTriageStatus">
                  <span class="cc-overall-dot"></span>
                  {{ overallTriageLabel }}
                </span>
              </div>

              <!-- Médico / especialidad: solo se pide cuando el paciente aún no tiene
                   cita hoy (walk-in). Usa el catálogo COMPLETO (catalogoMedicos /
                   catalogoEspecialidades), no los derivados de la lista de espera de
                   hoy, para poder asignar a un médico que hoy todavía no tiene a
                   nadie en la fila. -->
                <div v-if="modal.paciente && !tieneCitaHoy(modal.paciente)" class="cc-motivo-panel">
                  <div class="cc-motivo-panel-head">
                    <span><i class="ti ti-user-plus" aria-hidden="true"></i> Asignación</span>
                  </div>
                  <div class="cc-asignacion-grid">
                    <div class="cc-field">
                      <label class="cc-field-label">Especialidad</label>
                      <select class="cc-select-full" v-model="triageForm.especialidad_id" @change="onEspecialidadTriageChange">
                        <option value="">Sin especialidad asignada</option>
                        <option v-for="esp in catalogoEspecialidades" :key="esp.id" :value="esp.id">
                          {{ esp.nombre }}
                        </option>
                      </select>
                    </div>
                    <div class="cc-field">
                      <label class="cc-field-label">Médico</label>
                      <select class="cc-select-full" v-model="triageForm.medico_id">
                        <option value="">Sin médico asignado</option>
                        <option v-for="med in medicosCatalogoFiltrados" :key="med.id" :value="med.id">
                          {{ med.nombre }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

              <!-- Motivo de consulta / síntomas: la IA usa este texto + los
                   signos vitales para determinar automáticamente el nivel
                   de urgencia (Rojo/Amarillo/Verde) al guardar. -->
              <div class="cc-motivo-panel">
                <div class="cc-motivo-panel-head">
                  <span><i class="ti ti-notes-medical" aria-hidden="true"></i> Motivo de consulta / síntomas</span>
                  <span class="cc-motivo-panel-sub">La IA calculará la urgencia con base en esto</span>
                </div>
                <textarea
                  class="cc-motivo-textarea"
                  rows="3"
                  v-model="triageForm.motivo_consulta"
                  placeholder="Describe lo que el paciente reporta: dolor, mareo, dificultad para respirar, etc."
                ></textarea>
              </div>

              <!-- Panel de signos vitales, igual que el paso "Triaje" del alta de paciente -->
              <div class="cc-vitals-panel">
                <div class="cc-vitals-panel-head">
                  <span>Signos vitales</span>
                  <span class="cc-vitals-panel-sub">Rangos evaluados para adulto</span>
                </div>

                <div class="cc-vitals-grid">

                  <div class="cc-vital-card" :class="'cc-v-' + presionStatus">
                    <div class="cc-vital-label">Presión arterial</div>
                    <div class="cc-vital-readout">
                      <input type="text" v-model="triageForm.presion" class="cc-vital-input" placeholder="120/80" maxlength="7">
                      <span class="cc-vital-unit">mmHg</span>
                    </div>
                    <span class="cc-vital-status-tag" v-if="presionMensaje">{{ presionMensaje }}</span>
                  </div>

                  <div class="cc-vital-card" :class="'cc-v-' + saturacionStatus">
                    <div class="cc-vital-label">Saturación O₂</div>
                    <div class="cc-vital-readout">
                      <input type="number" v-model.number="triageForm.saturacion" class="cc-vital-input" placeholder="98" min="0" max="100">
                      <span class="cc-vital-unit">%</span>
                    </div>
                    <span class="cc-vital-status-tag" v-if="saturacionMensaje">{{ saturacionMensaje }}</span>
                  </div>

                  <div class="cc-vital-card" :class="'cc-v-' + temperaturaStatus">
                    <div class="cc-vital-label">Temperatura</div>
                    <div class="cc-vital-readout">
                      <input type="number" v-model.number="triageForm.temperatura" class="cc-vital-input" placeholder="36.5" min="30" max="45" step="0.1">
                      <span class="cc-vital-unit">°C</span>
                    </div>
                    <span class="cc-vital-status-tag" v-if="temperaturaMensaje">{{ temperaturaMensaje }}</span>
                  </div>

                  <div class="cc-vital-card" :class="'cc-v-' + frecuenciaCardiacaStatus">
                    <div class="cc-vital-label">Frec. cardíaca</div>
                    <div class="cc-vital-readout">
                      <input type="number" v-model.number="triageForm.frecuencia_cardiaca" class="cc-vital-input" placeholder="72" min="0" max="300">
                      <span class="cc-vital-unit">lpm</span>
                    </div>
                    <span class="cc-vital-status-tag" v-if="frecuenciaCardiacaMensaje">{{ frecuenciaCardiacaMensaje }}</span>
                  </div>

                  <div class="cc-vital-card" :class="'cc-v-' + frecuenciaRespiratoriaStatus">
                    <div class="cc-vital-label">Frec. respiratoria</div>
                    <div class="cc-vital-readout">
                      <input type="number" v-model.number="triageForm.frecuencia_respiratoria" class="cc-vital-input" placeholder="16" min="0" max="60">
                      <span class="cc-vital-unit">rpm</span>
                    </div>
                    <span class="cc-vital-status-tag" v-if="frecuenciaRespiratoriaMensaje">{{ frecuenciaRespiratoriaMensaje }}</span>
                  </div>

                  <div class="cc-vital-card">
                    <div class="cc-vital-label">Peso</div>
                    <div class="cc-vital-readout">
                      <input type="number" v-model.number="triageForm.peso" class="cc-vital-input" placeholder="70.0" min="0" max="300" step="0.1">
                      <span class="cc-vital-unit">kg</span>
                    </div>
                  </div>

                  <div class="cc-vital-card">
                    <div class="cc-vital-label">Talla</div>
                    <div class="cc-vital-readout">
                      <input type="number" v-model.number="triageForm.talla" class="cc-vital-input" placeholder="170" min="0" max="250">
                      <span class="cc-vital-unit">cm</span>
                    </div>
                  </div>

                  <!-- ═══ NUEVO: Tarjeta de IMC calculado en vivo ═══ -->
                  <div class="cc-vital-card cc-vital-card-imc" v-if="imcTriage">
                    <div class="cc-vital-label">
                      IMC
                      <span v-if="imcTriage.tipo === 'pediatrico'" class="cc-vital-imc-percentil">
                        Percentil {{ imcTriage.percentil }}
                      </span>
                    </div>
                    <div class="cc-vital-readout">
                      <strong class="cc-vital-value-imc">{{ imcTriage.bmi }}</strong>
                      <span class="cc-vital-imc-badge" :class="claseImc(imcTriage)">
                        {{ imcTriage.clasificacion }}
                      </span>
                    </div>
                  </div>
                  <div class="cc-vital-card cc-vital-card-imc-vacio" v-else-if="triageForm.peso || triageForm.talla">
                    <div class="cc-vital-label">IMC</div>
                    <small class="cc-vital-imc-hint">Captura peso y talla para calcularlo</small>
                  </div>

                </div>
              </div>
            </div>
            <div class="cc-modal__footer">
              <button class="cc-btn cc-btn--ghost" @click="cerrarModal" :disabled="guardandoTriage">Cancelar</button>
              <button class="cc-btn cc-btn--success" :disabled="guardandoTriage" @click="guardarTriageRapido">
                <i class="ti ti-device-floppy" aria-hidden="true"></i> {{ guardandoTriage ? 'Guardando...' : 'Guardar signos vitales' }}
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
              <div class="cc-field">
                <label class="cc-field-label">Peso (kg)</label>
                <div class="cc-input-wrap">
                  <i class="ti ti-scale" aria-hidden="true"></i>
                  <input
                    type="number"
                    step="0.1"
                    v-model.number="form.peso"
                    placeholder="0.0"
                  />
                </div>
              </div>

              <div class="cc-field">
                <label class="cc-field-label">Talla (cm)</label>
                <div class="cc-input-wrap">
                  <i class="ti ti-ruler" aria-hidden="true"></i>
                  <input
                    type="number"
                    step="0.1"
                    v-model.number="form.talla"
                    placeholder="170"
                  />
                </div>
              </div>

              <!-- IMC calculado automáticamente -->
              <div v-if="imcCalculado !== null" class="cc-field cc-field--full">
                <div class="cc-imc-result">
                  <div class="cc-imc-result__icon">
                    <i class="ti ti-scale" aria-hidden="true"></i>
                  </div>

                  <div class="cc-imc-result__info">
                    <span class="cc-field-label">Índice de Masa Corporal (IMC)</span>

                    <strong class="cc-imc-value">
                      {{ imcCalculado }}
                      <small>kg/m²</small>
                    </strong>

                    <span
                      class="cc-imc-status"
                      :class="'cc-imc-status--' + categoriaImc"
                    >
                      {{ categoriaImcTexto }}
                    </span>
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
import axios from 'axios'
// Bus de eventos compartido para avisar a otras vistas (ej. Home.vue,
// el dashboard) cuando una consulta se finaliza, sin esperar el
// setInterval de refresco automático de esa vista.
import eventBus from '../../utils/eventBus.js'
import { evaluarIMC } from '@/utils/bmiPercentile.js'
import lmsTable from '@/data/bmi-lms-cdc.json'

// Clave usada en localStorage para el paciente seleccionado para "Nueva consulta"
const CLAVE_PACIENTE_SELECCIONADO = 'pacienteSeleccionado'

// Fecha de hoy en formato YYYY-MM-DD (mismo formato que cita.fecha y que
// el input type="date"), calculada en hora local para no desfasarse por UTC.
// Se define fuera del componente porque se usa como valor por defecto en
// data(), antes de que el resto del componente esté disponible.
function obtenerFechaHoyISO() {
  const hoy = new Date()
  const y = hoy.getFullYear()
  const m = String(hoy.getMonth() + 1).padStart(2, '0')
  const d = String(hoy.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export default {
  name: 'ConsultaClinica',

  data() {
    return {
      // Lista principal de pacientes traída de la API
      pacientes: [],

      // Citas traídas de /api/citas — es la única fuente que trae médico,
      // especialidad y fecha; se cruza con "pacientes" por id para poder filtrar.
      listaEspera: [],

      loading:{
        lista:false,
      },

      // Paciente mostrado en el panel izquierdo.
      pacienteActivo: null,

      // Texto escrito en el buscador
      busqueda: '',

      // Filtros de la tabla de espera.
      especialidadSeleccionada: '',
      medicoSeleccionado: '',
      fechaFiltro: obtenerFechaHoyISO(),

      // Catálogo completo (médicos/especialidades) para el modal de triage
      catalogoMedicos: [],
      catalogoEspecialidades: [],

      // Controla si el panel izquierdo está en modo edición
      editMode: false,
      guardandoEdicion: false,

      editForm: {
        diagnostico: '',
        sintomas: '',
        triage: '',
        estado: ''
      },

      triageOpciones: ['Rojo', 'Amarillo', 'Verde'],
      estadoOpciones: ['En espera', 'Atendido', 'Cancelado'],

      modal: {
        tipo: '',
        paciente: null
      },

      // Formulario del modal de edición rápida de signos vitales (desde la tabla)
      triageForm: {
      presion: '',
      saturacion: null,
      temperatura: null,
      frecuencia_cardiaca: null,
      frecuencia_respiratoria: null,
      peso: null,
      talla: null,

      // IMC calculado automáticamente
      imc: null,
      imc_percentil: null,
      imc_clasificacion: '',

      motivo_consulta: '',
      medico_id: '',
      especialidad_id: ''
    },
      guardandoTriage: false,

      // Imágenes cargadas en el dropzone del expediente (base64)
      previews: [],

      pacienteSeleccionadoId: null,
      finalizandoId: null,

      // Formulario completo del expediente clínico
      form: {
        paciente_id:              '',
        codigo_paciente:          '',
        telefono:                 '',
        email:                    '',
        sexo:                     '',
        tipo_sangre:              '',
        fecha_nacimiento:         '',
        curp:                     '',
        direccion:                '',
        contacto_emergencia:      '',
        telefono_emergencia:      '',
        peso:                     '',
        talla:                    '',
        imc:                      '',
        imc_percentil:            '',
        imc_clasificacion:        '',
        presion:                  '',
        temperatura:              '',
        saturacion:               '',
        frecuencia_cardiaca:      '',
        motivo_consulta:          '',
        sintomas:                 '',
        diagnostico:              '',
        alergias:                 '',
        enfermedades_cronicas:    '',
        medicamentos_actuales:    '',
        antecedentes_quirurgicos: ''
      }
    }
  },

  computed: {
    especialidadesDisponibles() {
      const mapa = new Map()
      const lista = Array.isArray(this.listaEspera) ? this.listaEspera : []
      lista.forEach(item => {
        if (!item.especialidad) return
        const id = item.especialidad.id ?? item.especialidad.nombre
        if (!mapa.has(id)) mapa.set(id, { id, nombre: item.especialidad.nombre })
      })
      return Array.from(mapa.values())
    },

    medicosDisponibles() {
      const mapa = new Map()
      const lista = Array.isArray(this.listaEspera) ? this.listaEspera : []
      lista.forEach(item => {
        if (!item.medico) return
        const id = item.medico.id ?? item.medico.nombre
        const espId = item.especialidad ? (item.especialidad.id ?? item.especialidad.nombre) : ''
        if (!mapa.has(id)) {
          mapa.set(id, { id, nombre: item.medico.nombre, especialidadIds: new Set(espId ? [espId] : []) })
        } else if (espId) {
          mapa.get(id).especialidadIds.add(espId)
        }
      })
      return Array.from(mapa.values()).map(m => ({ ...m, especialidadIds: Array.from(m.especialidadIds) }))
    },

    medicosFiltrados() {
      if (!this.especialidadSeleccionada) return this.medicosDisponibles
      return this.medicosDisponibles.filter(m =>
        Array.isArray(m.especialidadIds) && m.especialidadIds.includes(this.especialidadSeleccionada)
      )
    },

    medicosCatalogoFiltrados() {
      if (!this.triageForm.especialidad_id) return this.catalogoMedicos
      return this.catalogoMedicos.filter(m => m.especialidad_id == this.triageForm.especialidad_id)
    },

    citasPorPacienteId() {
      const mapa = new Map()
      const lista = Array.isArray(this.listaEspera) ? this.listaEspera : []
      lista.forEach(item => {
        const pid = item.paciente?.id || item.paciente_id
        if (pid == null) return
        if (!mapa.has(pid)) mapa.set(pid, [])
        mapa.get(pid).push(item)
      })
      return mapa
    },

    busquedaActiva() {
      return this.busqueda.trim().length > 0
    },

    pacientesFiltrados() {
      const q = this.busqueda.trim().toLowerCase()

      const fusionar = (p) => {
        const registros = this.citasPorPacienteId.get(p.id) || []
        const registroActivo = registros.find(r => !['Cancelada', 'Finalizada'].includes(r.estado)) || registros[0] || null

        return {
          ...p,
          _registroLE:         registroActivo,
          turno_id:            registroActivo?.id ?? null,
          turno_numero:        registroActivo?.numero_turno ?? null,
          turno_folio:         registroActivo?.folio ?? null,
          turno_estado:        registroActivo?.estado ?? null,
          turno_medico:        registroActivo?.medico ?? null,
          turno_especialidad:  registroActivo?.especialidad ?? null,
        }
      }

      if (q) {
        return this.pacientes
          .filter(p =>
            (p.nombre || '').toLowerCase().includes(q) ||
            (p.paciente_id || '').toLowerCase().includes(q)
          )
          .map(fusionar)
      }

      const hayFiltro = !!(this.especialidadSeleccionada || this.medicoSeleccionado)
      let lista = this.pacientes

      if (hayFiltro) {
        lista = this.pacientes.filter(p => {
          const registrosPaciente = this.citasPorPacienteId.get(p.id) || []
          return registrosPaciente.some(item => {
            const medicoOk = !this.medicoSeleccionado ||
              (item.medico && (item.medico.id ?? item.medico.nombre) === this.medicoSeleccionado)
            const especialidadOk = !this.especialidadSeleccionada ||
              (item.especialidad && (item.especialidad.id ?? item.especialidad.nombre) === this.especialidadSeleccionada)
            const estadoOk = !['Cancelada', 'Finalizada'].includes(item.estado)
            return medicoOk && especialidadOk && estadoOk
          })
        })
      } else {
        lista = this.pacientes.filter(p => {
          const registrosPaciente = this.citasPorPacienteId.get(p.id) || []
          return registrosPaciente.some(item => !['Cancelada', 'Finalizada'].includes(item.estado))
        })
      }

      lista = lista.map(fusionar).sort((a, b) => {
        const turnoA = a.turno_numero ?? 999999
        const turnoB = b.turno_numero ?? 999999
        return turnoA - turnoB
      })

      return lista
    },

    mensajeSinPaciente() {
      if (this.pacientesFiltrados.length === 0) {
        return 'No hay pacientes en la lista de espera para hoy'
      }
      return 'Sin paciente seleccionado'
    },

    presionStatus() {
      const raw = this.triageForm.presion
      if (!raw || !raw.includes('/')) return ''
      const [sysStr, diaStr] = raw.split('/')
      const sys = parseInt(sysStr, 10)
      const dia = parseInt(diaStr, 10)
      if (isNaN(sys) || isNaN(dia)) return ''
      if (sys >= 180 || dia >= 120 || sys < 90) return 'critical'
      if (sys >= 140 || dia >= 90) return 'warning'
      return 'normal'
    },
    saturacionStatus() {
      const v = this.triageForm.saturacion
      if (v === null || v === '' || v === undefined) return ''
      if (v < 90) return 'critical'
      if (v < 95) return 'warning'
      return 'normal'
    },
    temperaturaStatus() {
      const v = this.triageForm.temperatura
      if (v === null || v === '' || v === undefined) return ''
      if (v >= 38.5 || v < 35.5) return 'critical'
      if (v >= 37.6) return 'warning'
      return 'normal'
    },
    frecuenciaCardiacaStatus() {
      const v = this.triageForm.frecuencia_cardiaca
      if (v === null || v === '' || v === undefined) return ''
      if (v < 50 || v > 120) return 'critical'
      if (v < 60 || v > 100) return 'warning'
      return 'normal'
    },
    frecuenciaRespiratoriaStatus() {
      const v = this.triageForm.frecuencia_respiratoria
      if (v === null || v === '' || v === undefined) return ''
      if (v < 8 || v > 24) return 'critical'
      if (v < 12 || v > 20) return 'warning'
      return 'normal'
    },
    overallTriageStatus() {
      const statuses = [
        this.presionStatus, this.saturacionStatus, this.temperaturaStatus,
        this.frecuenciaCardiacaStatus, this.frecuenciaRespiratoriaStatus
      ]
      if (statuses.includes('critical')) return 'critical'
      if (statuses.includes('warning')) return 'warning'
      if (statuses.includes('normal')) return 'normal'
      return ''
    },
    overallTriageLabel() {
      return this.statusLabel(this.overallTriageStatus)
    },

    presionMensaje() {
      const raw = this.triageForm.presion
      if (!raw || !raw.includes('/')) return ''
      const [sysStr, diaStr] = raw.split('/')
      const sys = parseInt(sysStr, 10)
      const dia = parseInt(diaStr, 10)
      if (isNaN(sys) || isNaN(dia)) return ''
      if (sys >= 180 || dia >= 120) return 'Crisis hipertensiva'
      if (sys < 90) return 'Hipotensión'
      if (sys >= 140 || dia >= 90) return 'Hipertensión leve'
      return 'Presión normal'
    },
    saturacionMensaje() {
      const v = this.triageForm.saturacion
      if (v === null || v === '' || v === undefined) return ''
      if (v < 90) return 'Hipoxemia grave'
      if (v < 95) return 'Hipoxemia leve'
      return 'Saturación normal'
    },
    temperaturaMensaje() {
      const v = this.triageForm.temperatura
      if (v === null || v === '' || v === undefined) return ''
      if (v >= 38.5) return 'Fiebre alta'
      if (v < 35.5) return 'Hipotermia'
      if (v >= 37.6) return 'Fiebre leve'
      return 'Temperatura normal'
    },
    frecuenciaCardiacaMensaje() {
      const v = this.triageForm.frecuencia_cardiaca
      if (v === null || v === '' || v === undefined) return ''
      if (v < 50) return 'Bradicardia'
      if (v > 120) return 'Taquicardia'
      if (v < 60) return 'Bradicardia leve'
      if (v > 100) return 'Taquicardia leve'
      return 'Frecuencia normal'
    },
    frecuenciaRespiratoriaMensaje() {
      const v = this.triageForm.frecuencia_respiratoria
      if (v === null || v === '' || v === undefined) return ''
      if (v < 8) return 'Bradipnea'
      if (v > 24) return 'Taquipnea'
      if (v < 12) return 'Bradipnea leve'
      if (v > 20) return 'Taquipnea leve'
      return 'Respiración normal'
    },

    // ── IMC calculado en vivo para el modal de triage ──
    // Usa la misma lógica que SignosVitales.vue: si hay edad y sexo
    // confiables, aplica la tabla pediátrica CDC (percentil); si no,
    // devuelve el IMC crudo sin clasificar. El watch de peso/talla
    // llama a calcularIMCAutomatico() para reflejar esto en triageForm.
    imcTriage() {
      const peso = Number(this.triageForm.peso)
      const talla = Number(this.triageForm.talla)

      if (!peso || !talla || peso <= 0 || talla <= 0) {
        return null
      }

      const paciente = this.modal.paciente

      if (!paciente) {
        return null
      }

      let edadAnios = Number(paciente.edad) || 0
      let edadMeses = 0

      // Si tenemos fecha de nacimiento, calculamos la edad con mayor precisión
      if (paciente.fecha_nacimiento) {
        const nacimiento = new Date(paciente.fecha_nacimiento)
        const hoy = new Date()

        if (!isNaN(nacimiento.getTime())) {
          let meses =
            (hoy.getFullYear() - nacimiento.getFullYear()) * 12 +
            (hoy.getMonth() - nacimiento.getMonth())

          if (hoy.getDate() < nacimiento.getDate()) {
            meses--
          }

          meses = Math.max(meses, 0)

          edadAnios = Math.floor(meses / 12)
          edadMeses = meses % 12
        }
      }

      const sexo = (paciente.sexo || '').toString().trim().toUpperCase()

      if (!sexo) {
        return {
          bmi: Number(
            (peso / Math.pow(talla / 100, 2)).toFixed(2)
          ),
          tipo: 'sin_clasificar',
          clasificacion: 'Falta sexo del paciente'
        }
      }

      return evaluarIMC(
        {
          pesoKg: peso,
          tallaCm: talla,
          edadAnios,
          edadMeses,
          sexo
        },
        lmsTable
      )
    }
  },

  async mounted() {
    await Promise.all([this.obtenerPacientes(), this.obtenerListaEspera(), this.cargarCatalogoAgregar()])
    this.cargarSeleccionPrevia()
    this.inicializarPacienteActivo()
  },
  watch: {
    'triageForm.peso': {
      handler() {
        this.calcularIMCAutomatico()
      }
    },
    'triageForm.talla': {
      handler() {
        this.calcularIMCAutomatico()
      }
    }
  },

  methods: {
    chipClassTurno(estado) {
      switch (estado) {
        case 'En proceso': return 'cc-chip--blue'
        case 'Llamando':   return 'cc-chip--amber'
        case 'Finalizada': return 'cc-chip--green'
        case 'Cancelada':  return 'cc-chip--red'
        case 'En espera':  return 'cc-chip--gray'
        default:           return 'cc-chip--gray'
      }
    },
    nombreCompleto(p) {
      const paciente = p.paciente || p;
      return paciente.nombre || 'Sin nombre';
    },

    getRegistroLE(paciente) {
      const registros = this.citasPorPacienteId.get(paciente.id) || []
      return registros[0] || {}
    },

    getTurno(paciente) {
      const reg = this.getRegistroLE(paciente)
      return reg.numero_turno ?? '-'
    },

    getEstado(paciente) {
      const reg = this.getRegistroLE(paciente)
      return reg.estado ?? 'En espera'
    },

    getFolio(paciente) {
      const reg = this.getRegistroLE(paciente)
      return reg.folio || paciente.paciente_id || 'S/F'
    },

    calcularIMCAutomatico() {
      const resultado = this.imcTriage

      if (!resultado) {
        this.triageForm.imc = null
        this.triageForm.imc_percentil = null
        this.triageForm.imc_clasificacion = ''
        return
      }

      this.triageForm.imc = resultado.bmi ?? null

      this.triageForm.imc_percentil =
        resultado.percentil ??
        resultado.percentile ??
        null

      this.triageForm.imc_clasificacion =
        resultado.clasificacion ??
        resultado.tipo ??
        ''
    },

    // ── NUEVO: color del badge de IMC según severidad (mismo criterio
    // que usa SignosVitales.vue en su método claseImc) ──
    claseImc(info) {
      if (!info) return ''
      if (info.tipo === 'sin_clasificar') return 'cc-imc-badge-neutro'
      const c = (info.clasificacion || '').toLowerCase()
      if (c.includes('normal')) return 'cc-imc-badge-normal'
      if (c.includes('bajo')) return 'cc-imc-badge-warning'
      if (c.includes('sobrepeso')) return 'cc-imc-badge-warning'
      if (c.includes('obesidad')) return 'cc-imc-badge-critical'
      return 'cc-imc-badge-neutro'
    },

    // ──────────────────────────────────────────
    // API
    // ──────────────────────────────────────────

    async obtenerPacientes() {
      try {
        const response = await ApiService.get('/pacientes')
        this.pacientes = response.data

        if (this.pacienteActivo) {
          const actualizado = this.pacientes.find(p => p.id === this.pacienteActivo.id)
          this.pacienteActivo = actualizado || null
        }
      } catch (error) {
        console.error('Error al obtener pacientes:', error)
      }
    },

    async obtenerListaEspera() {
      this.loading.lista = true
      this.loadError = null

      const params = {}
      if (this.medicoSeleccionado) params.medico_id = this.medicoSeleccionado
      if (this.especialidadSeleccionada) params.especialidad_id = this.especialidadSeleccionada

      try {
        const res = await ApiService.get('/lista-espera', { params })
        this.listaEspera = res.data.lista || res.data
        console.log("📌 Datos de la Lista de Espera recibidos:", this.listaEspera)
      } catch (err) {
          this.loadError = "No se pudo cargar la lista de espera."
          if (typeof this.mostrarToast === 'function') {
            this.mostrarToast("⚠ Error al cargar lista de espera", "error")
          }
          console.error("Error cargando lista de espera:", err)
        } finally {
          this.loading.lista = false
        }
    },

    async cargarCatalogoAgregar() {
      try {
        const res = await ApiService.get('/lista-espera/create')
        this.catalogoMedicos = res.data.medicos || []
        this.catalogoEspecialidades = res.data.especialidades || []
      } catch (error) {
        console.error('Error al cargar catálogo de médicos/especialidades:', error)
      }
    },

    onEspecialidadTriageChange() {
      if (this.triageForm.medico_id) {
        const sigueSiendoValido = this.medicosCatalogoFiltrados.some(m => m.id === this.triageForm.medico_id)
        if (!sigueSiendoValido) this.triageForm.medico_id = ''
      }
    },

    async finalizarPaciente(paciente) {
      if (!paciente) return

      const registroLE = this.listaEspera.find(
        item => item.paciente_id === paciente.id && item.estado !== 'Cancelada' && item.estado !== 'Finalizada'
      )
      
      if (!registroLE) {
        if (window.Swal) {
          window.Swal.fire({
            icon: 'info',
            title: 'Sin registro activo hoy',
            text: 'Este paciente no se encuentra activo en la lista de espera de hoy.'
          })
        }
        return
      }

      let confirmado = true
      if (window.Swal) {
        const resultado = await window.Swal.fire({
          icon: 'warning',
          title: '¿Marcar como no presentado / abandono?',
          text: `${this.nombreCompleto(paciente)} se cambiará a estado Cancelado en la lista de espera.`,
          showCancelButton: true,
          confirmButtonText: 'Sí, cancelar',
          cancelButtonText: 'Volver',
          confirmButtonColor: '#dc2626'
        })
        confirmado = resultado.isConfirmed
      } else {
        confirmado = window.confirm(`¿Marcar como abandonó a ${this.nombreCompleto(paciente)}?`)
      }
      if (!confirmado) return

      this.finalizandoId = paciente.id

      try {
        await ApiService.patch(`/lista-espera/${registroLE.id}/estado`, { estado: 'Cancelada' })

        await this.obtenerListaEspera()
        eventBus.emit('consulta-finalizada')

        if (this.pacienteActivo && this.pacienteActivo.id === paciente.id) {
          this.pacienteActivo = this.pacientesFiltrados[0] || null
        }

        if (window.Swal) {
          window.Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: 'Paciente marcado como cancelado', showConfirmButton: false,
            timer: 1400, timerProgressBar: true
          })
        }
      } catch (err) {
        console.error('Error al actualizar el estado en lista de espera:', err)
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error',
            title: 'No se pudo actualizar el estado',
            text: err.response?.data?.message || 'Intenta de nuevo.'
          })
        }
      } finally {
        this.finalizandoId = null
      }
    },

    async guardarCambiosPaciente(paciente) {
      const objetivo = paciente || this.pacienteActivo
      if (!objetivo) return
      try {
        const { triages, ...datosPaciente } = objetivo
        await ApiService.put('/pacientes/' + objetivo.id, datosPaciente)
        await this.obtenerPacientes()
      } catch (error) {
        console.error('Error al guardar paciente:', error)
        throw error
      }
    },

    async guardarTriageEnBackend(pacienteId, payload) {
      const { data } = await axios.post(`/triage/guardar/${pacienteId}`, payload)
      return data
    },

    onEspecialidadChange() {
      if (this.medicoSeleccionado) {
        const sigueSiendoValido = this.medicosFiltrados.some(m => m.id === this.medicoSeleccionado)
        if (!sigueSiendoValido) this.medicoSeleccionado = ''
      }
    },

    obtenerFechaHoyISO() {
      return obtenerFechaHoyISO()
    },

    inicializarPacienteActivo() {
      this.pacienteActivo = this.pacientesFiltrados[0] || null
    },

    fechaSolo(fecha) {
      if (!fecha) return ''
      return String(fecha).slice(0, 10)
    },

    obtenerCitaHoyActiva(paciente) {
      if (!paciente) return null
      const hoy = this.obtenerFechaHoyISO()
      const citasDelPaciente = this.citasPorPacienteId.get(paciente.id) || []
      return citasDelPaciente.find(c => {
        const fechaOk = this.fechaSolo(c.fecha) === hoy
        const estadoOk = !['Cancelada', 'Finalizada'].includes(c.estado)
        const medicoOk = !this.medicoSeleccionado ||
          (c.medico && (c.medico.id ?? c.medico.nombre) === this.medicoSeleccionado)
        const especialidadOk = !this.especialidadSeleccionada ||
          (c.especialidad && (c.especialidad.id ?? c.especialidad.nombre) === this.especialidadSeleccionada)
        return fechaOk && estadoOk && medicoOk && especialidadOk
      }) || null
    },

    tieneCitaHoy(paciente) {
      return !!this.obtenerCitaHoyActiva(paciente)
    },

    horaCitaPaciente(p) {
      const fechaObjetivo = this.fechaFiltro || this.obtenerFechaHoyISO()
      const citasDelPaciente = this.citasPorPacienteId.get(p.id) || []
      const relevantes = citasDelPaciente.filter(
        c => this.fechaSolo(c.fecha) === fechaObjetivo && c.estado !== 'Cancelada'
      )
      if (!relevantes.length) return null
      return relevantes.map(c => c.hora).sort()[0]
    },

    async agregarPacienteAListaHoy(paciente) {
      const medicoId = this.triageForm.medico_id || this.medicoSeleccionado
      const especialidadId = this.triageForm.especialidad_id || this.especialidadSeleccionada

      try {
        await ApiService.post('/lista-espera', {
          paciente_id:      paciente.id,
          medico_id:        medicoId || null,
          especialidad_id:  especialidadId || null,
          observaciones:    'Agregado a la lista de hoy desde la búsqueda de paciente'
        })

        await this.obtenerListaEspera()
        this.busqueda = ''

        return true
      } catch (error) {
        console.error('Error al agregar paciente a la lista de hoy:', error)
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error',
            title: 'No se pudo agregar a la lista de hoy',
            text: error.response?.data?.message || 'Intenta de nuevo.'
          })
        }
        return false
      }
    },

    endpointEstadoCita(cita) {
      const idStr = String(cita.id)

      if (cita.origen === 'consulta' || idStr.startsWith('consulta-')) {
        return `/api/consultas/${idStr.replace('consulta-', '')}/estado`
      }

      return `/api/citas/${idStr.replace('cita-', '')}/estado`
    },

    cargarSeleccionPrevia() {
      const guardado = localStorage.getItem(CLAVE_PACIENTE_SELECCIONADO)
      if (!guardado) return
      try {
        const data = JSON.parse(guardado)
        this.pacienteSeleccionadoId = data.id || null
      } catch (error) {
        console.error('No se pudo leer el paciente seleccionado guardado:', error)
      }
    },

    seleccionarParaConsulta(paciente) {
      if (!paciente) return

      const payload = {
        id:          paciente.id,
        paciente_id: paciente.paciente_id,
        nombre:      this.nombreCompleto(paciente)
      }

      localStorage.setItem(CLAVE_PACIENTE_SELECCIONADO, JSON.stringify(payload))
      this.pacienteSeleccionadoId = paciente.id

      if (window.Swal) {
        window.Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: `${payload.nombre} seleccionado`,
          showConfirmButton: false,
          timer: 1600,
          timerProgressBar: true
        })
      }
    },

    verPaciente(paciente) {
      this.pacienteActivo = paciente
      this.editMode = false
    },

    iniciarEdicion(paciente) {
      if (!paciente) return
      const t = this.ultimoTriage(paciente) || {}
      this.editMode = true
      this.editForm = {
        diagnostico: t.motivo_consulta || '',
        sintomas:    t.sintomas        || '',
        triage:      t.estado          || '',
        estado:      paciente.estado   || ''
      }
    },

    cancelarEdicion() {
      this.editMode = false
    },

    async guardarEdicion() {
      if (!this.pacienteActivo) return

      this.guardandoEdicion = true
      try {
        const triageActualizado = await this.guardarTriageEnBackend(this.pacienteActivo.id, {
          motivo_consulta: this.editForm.diagnostico,
          sintomas:         this.editForm.sintomas,
          estado_triage:    this.editForm.triage
        })

        this.pacienteActivo.estado = this.editForm.estado
        await this.guardarCambiosPaciente(this.pacienteActivo)

        this.editMode = false

        if (window.Swal) {
          window.Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: 'Cambios guardados', showConfirmButton: false,
            timer: 1400, timerProgressBar: true
          })
        }
      } catch (error) {
        console.error('Error al guardar la edición del panel:', error)
        if (window.Swal) {
          window.Swal.fire({ icon: 'error', title: 'No se pudieron guardar los cambios', text: 'Intenta de nuevo.' })
        }
      } finally {
        this.guardandoEdicion = false
      }
    },

    abrirModal(tipo, paciente) {
      if (!paciente) return
      this.modal.tipo    = tipo
      this.modal.paciente = paciente

      if (tipo === 'triage') {
        this.triageForm = {
          presion: '',
          saturacion: null,
          temperatura: null,
          frecuencia_cardiaca: null,
          frecuencia_respiratoria: null,
          peso: null,
          talla: null,

          imc: null,
          imc_percentil: null,
          imc_clasificacion: '',

          motivo_consulta: '',
          medico_id: '',
          especialidad_id: ''
        }
      }

      if (tipo === 'expediente') {
        const t = this.ultimoTriage(paciente) || {}
        this.form = {
          paciente_id:              paciente.id                         || '',
          codigo_paciente:          paciente.paciente_id                || '',
          telefono:                 paciente.telefono                   || '',
          email:                    paciente.email                      || '',
          sexo:                     paciente.sexo                       || '',
          tipo_sangre:              paciente.tipo_sangre                || '',
          fecha_nacimiento:         paciente.fecha_nacimiento           || '',
          curp:                     paciente.curp                       || '',
          direccion:                paciente.direccion                  || '',
          contacto_emergencia:      paciente.contacto_emergencia        || '',
          telefono_emergencia:      paciente.telefono_emergencia        || '',
          peso:                     t.peso                              || '',
          talla:                    t.talla                             || '',
          imc:                      t.imc                               || '',
          imc_percentil:            t.imc_percentil                     || '',
          imc_clasificacion:        t.imc_clasificacion                 || '',
          presion:                  t.presion                           || '',
          temperatura:              t.temperatura                       || '',
          saturacion:               t.saturacion                        || '',
          frecuencia_cardiaca:      t.frecuencia_cardiaca               || '',
          motivo_consulta:          t.motivo_consulta                   || '',
          sintomas:                 t.sintomas                          || '',
          diagnostico:              '',
          alergias:                 paciente.alergias                   || '',
          enfermedades_cronicas:    paciente.enfermedades_cronicas      || '',
          medicamentos_actuales:    paciente.medicamentos_actuales      || '',
          antecedentes_quirurgicos: paciente.antecedentes_quirurgicos   || ''
        }
        this.previews = []
      }
    },

    cerrarModal() {
      this.modal.tipo     = ''
      this.modal.paciente = null
    },

    abrirExpedienteDesdeFila(paciente) {
      this.abrirModal('expediente', paciente)
    },

    irAExpediente(paciente) {
      this.abrirModal('expediente', paciente)
    },

    guardarExpediente() {
      console.log('Expediente guardado:', {
        paciente: this.modal.paciente,
        form:     this.form,
        imagenes: this.previews.length
      })
      this.cerrarModal()
    },

    async guardarTriageRapido() {
      const paciente = this.modal.paciente
      if (!paciente) return

      const resultadoIMC = this.imcTriage

      if (resultadoIMC) {
        this.triageForm.imc = resultadoIMC.bmi ?? null

        this.triageForm.imc_percentil =
          resultadoIMC.percentil ??
          resultadoIMC.percentile ??
          null

        this.triageForm.imc_clasificacion =
          resultadoIMC.clasificacion ??
          resultadoIMC.tipo ??
          ''
      }

      if (!this.tieneCitaHoy(paciente)) {
        const seAgrego = await this.agregarPacienteAListaHoy(paciente)
        if (!seAgrego) return
      }

      this.guardandoTriage = true
      try {
        const resultado = await this.guardarTriageEnBackend(paciente.id, this.triageForm)

        if (resultado?.triage) {
          if (!paciente.triages) paciente.triages = []
          paciente.triages.push(resultado.triage)
        }

        await this.obtenerPacientes()

        const nivel = resultado?.triage?.estado
        const iconosPorNivel = { Rojo: '🔴', Amarillo: '🟡', Verde: '🟢' }

        if (window.Swal) {
          if (nivel) {
            window.Swal.fire({
              toast: true, position: 'top-end', icon: 'success',
              title: 'Signos vitales actualizados',
              text: `La IA clasificó a este paciente como ${iconosPorNivel[nivel] || ''} ${nivel}`,
              showConfirmButton: false,
              timer: 2600, timerProgressBar: true
            })
          } else {
            window.Swal.fire({
              toast: true, position: 'top-end', icon: 'success',
              title: 'Signos vitales actualizados', showConfirmButton: false,
              timer: 1400, timerProgressBar: true
            })
          }
        }

        this.cerrarModal()
        this.$emit('triage-guardado')
      } catch (error) {
        console.error('Error al guardar los signos vitales:', error)
        if (window.Swal) {
          window.Swal.fire({ icon: 'error', title: 'No se pudieron guardar los signos vitales', text: 'Intenta de nuevo.' })
        }
      } finally {
        this.guardandoTriage = false
      }
    },

    onFileChange(event) {
      this._leerArchivos(event.target.files)
    },

    onDrop(event) {
      this._leerArchivos(event.dataTransfer.files)
    },

    _leerArchivos(files) {
      for (const file of files) {
        if (!file.type.startsWith('image/')) continue
        const reader = new FileReader()
        reader.onload = e => { this.previews.push(e.target.result) }
        reader.readAsDataURL(file)
      }
    },

    formatearFecha(fecha) {
      if (!fecha) return '—'
      const [anio, mes, dia] = fecha.split('-')
      const meses = [
        'enero','febrero','marzo','abril','mayo','junio',
        'julio','agosto','septiembre','octubre','noviembre','diciembre'
      ]
      return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`
    },

    avatarColor(nombre) {
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
      if (!paciente?.triages?.length) return null
      return paciente.triages[paciente.triages.length - 1]
    },

    nombreCompleto(p) {
      if (!p) return ''
      return [p.nombre, p.apellido_paterno, p.apellido_materno]
        .filter(Boolean)
        .join(' ')
    },

    initials(nombre) {
      if (!nombre) return ''
      return nombre
        .split(' ')
        .map(n => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },

    chipClass(estado) {
      switch (estado) {
        case 'Atendido':  return 'cc-chip--green'
        case 'En espera': return 'cc-chip--amber'
        case 'Cancelado': return 'cc-chip--red'
        default:          return 'cc-chip--gray'
      }
    },

    triageClass(estado) {
      switch (estado) {
        case 'grave':    case 'Rojo':     return 'cc-chip--red'
        case 'moderado': case 'Amarillo': return 'cc-chip--amber'
        case 'leve':     case 'Verde':    return 'cc-chip--green'
        default:                          return 'cc-chip--gray'
      }
    },

    statusLabel(status) {
      if (status === 'critical') return 'Fuera de rango'
      if (status === 'warning') return 'Vigilar'
      if (status === 'normal') return 'Normal'
      return ''
    }
  }
}
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css');
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

/* ─── Reset & base ─── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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

@media (max-width: 1024px) {
  .cc-layout { grid-template-columns: 1fr; }
}

.cc-panel,
.cc-table-section {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}

.cc-label { font-size: 13px; font-weight: 600; color: #374151; }

.cc-dot {
  display: inline-block;
  width: 8px; height: 8px;
  border-radius: 50%;
  margin-right: 8px;
}
.cc-dot--blue { background: #185FA5; }
.cc-dot--gray { background: #9ca3af; }

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

.cc-hero {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 22px;
  background: #f8faff;
  border-bottom: 1px solid #f0f2f5;
}

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

.cc-panel__actions {
  display: flex; gap: 8px;
  padding: 14px 22px;
  background: #f8fafc;
  border-top: 1px solid #f0f2f5;
}

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
.cc-chip--gray  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

.cc-mono {
  font-family: 'Inter', monospace;
  font-size: 11px; color: #185FA5; font-weight: 600;
}
.cc-mono--cell { color: #185FA5; font-size: 11px; font-weight: 600; }

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

.cc-table-filtros {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding: 12px 24px;
  border-bottom: 1px solid #f0f2f5;
  background: #fafbfc;
  transition: opacity 0.15s;
}
.cc-table-filtros--inactivos {
  opacity: 0.5;
}

.cc-filtro-select {
  height: 36px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #185FA5;
  font-size: 12.5px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  outline: none;
  transition: border-color 0.15s;
}
.cc-filtro-select:hover,
.cc-filtro-select:focus {
  border-color: #185FA5;
}
.cc-filtro-select:disabled {
  cursor: not-allowed;
}

.cc-filtro-fecha {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 36px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #185FA5;
  cursor: pointer;
  transition: border-color 0.15s;
}
.cc-filtro-fecha:hover {
  border-color: #185FA5;
}
.cc-filtro-fecha i {
  font-size: 15px;
  flex-shrink: 0;
}
.cc-filtro-fecha-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 12.5px;
  font-weight: 600;
  font-family: inherit;
  color: #185FA5;
  cursor: pointer;
}
.cc-filtro-fecha-input:disabled {
  cursor: not-allowed;
}

.cc-filtro-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 36px;
  padding: 0 14px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
  font-size: 12.5px;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  outline: none;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.cc-filtro-toggle:hover {
  border-color: #185FA5;
  color: #185FA5;
}
.cc-filtro-toggle--activo {
  background: #185FA5;
  border-color: #185FA5;
  color: #fff;
}
.cc-filtro-toggle:disabled {
  cursor: not-allowed;
}

.cc-hint {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 24px;
  font-size: 12px; color: #185FA5;
  background: #eff6ff;
  border-bottom: 1px solid #dbeafe;
}
.cc-hint i { font-size: 15px; flex-shrink: 0; }

.cc-table-wrap { overflow-x: auto; }

.cc-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.cc-table thead th {
  padding: 10px 24px;
  text-align: left;
  font-size: 12px; font-weight: 600; color: #6b7280;
  background: #fafafa;
  border-bottom: 1px solid #f0f2f5;
  white-space: nowrap;
}
.cc-th--right { text-align: right; }

.cc-row td {
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
  color: #111827;
  background: #fff;
}
.cc-row:last-child td { border-bottom: none; }
.cc-row:hover td      { background: #fafbff; cursor: pointer; }
.cc-row--active td    { background: #eff6ff; }

.cc-row--selected td {
  background: #ecfdf5;
  box-shadow: inset 3px 0 0 #059669;
}

.cc-selected-icon {
  color: #059669;
  font-size: 14px;
  margin-left: 6px;
}

.cc-table-empty {
  text-align: center; padding: 40px 16px;
  color: #9ca3af; font-size: 13px;
}

.cc-td--patient { display: flex; align-items: center; gap: 14px; }
.cc-td--actions { text-align: right; white-space: nowrap; }

.cc-mini-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  color: #fff; flex-shrink: 0;
  letter-spacing: 0.03em;
}
.cc-mini-avatar--lg {
  width: 48px; height: 48px;
  border-radius: 50%; font-size: 16px;
}

.cc-patient-name {
  font-weight: 700; color: #111827;
  font-size: 13.5px; line-height: 1.3;
  display: flex; align-items: center;
}

.cc-patient-diag {
  font-size: 11.5px; color: #185FA5;
  font-weight: 600; margin-top: 2px;
}

.cc-icon-btn {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: #f0f2f5;
  border: none; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 17px;
  transition: background 0.15s, transform 0.12s;
  margin-left: 6px;
}
.cc-icon-btn:hover  { transform: translateY(-1px); }
.cc-icon-btn:active { transform: scale(0.94); }
.cc-icon-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.cc-icon-btn--view       { background: #f0f2f5; color: #2563eb; }
.cc-icon-btn--view:hover { background: #dbeafe; }

.cc-icon-btn--add       { background: #f0f2f5; color: #059669; }
.cc-icon-btn--add:hover { background: #d1fae5; }

.cc-icon-btn--triage       { background: #f0f2f5; color: #7c3aed; }
.cc-icon-btn--triage:hover { background: #ede9fe; }

.cc-icon-btn--folder       { background: #f0f2f5; color: #06b6d4; }
.cc-icon-btn--folder:hover { background: #cffafe; }

.cc-icon-btn--edit       { background: #f0f2f5; color: #d97706; }
.cc-icon-btn--edit:hover { background: #fef3c7; }

.cc-icon-btn--danger       { background: #f0f2f5; color: #ef4444; }
.cc-icon-btn--danger:hover:not(:disabled) { background: #fee2e2; }

.cc-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.5);
  z-index: 9000;
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}

.cc-modal {
  background: #fff;
  border-radius: 20px;
  width: 100%; max-width: 480px;
  overflow: hidden;
  display: flex; flex-direction: column;
  max-height: 90vh;
  box-shadow: 0 20px 60px rgba(0,0,0,0.18);
}
.cc-modal--lg { max-width: 680px; }
.cc-modal--md { max-width: 620px; }

.cc-modal__header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 18px 24px;
  font-size: 14px; font-weight: 700; color: #fff;
  gap: 12px; flex-shrink: 0;
}
.cc-modal__header--dark { background: #0f172a; }
.cc-modal__header--blue { background: #185FA5; }

.cc-modal__close {
  background: transparent; border: none;
  color: rgba(255,255,255,0.7);
  font-size: 18px; cursor: pointer;
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 8px; flex-shrink: 0;
}
.cc-modal__close:hover { background: rgba(255,255,255,0.1); color: #fff; }

.cc-modal__body { padding: 24px; overflow-y: auto; flex: 1; }

.cc-triage-aviso {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  color: #92400e;
  font-size: 12.5px;
  line-height: 1.5;
  border-radius: 10px;
  padding: 10px 12px;
  margin-bottom: 18px;
}
.cc-triage-aviso i { margin-top: 2px; flex-shrink: 0; }

.cc-modal__grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 14px;
  background: #f8fafc; border: 1px solid #e8eaed;
  border-radius: 12px; padding: 16px;
}
.cc-modal__grid-full { grid-column: 1 / -1; }

.cc-modal__footer {
  display: flex; justify-content: space-between; gap: 10px;
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 1px solid #f0f2f5;
  flex-shrink: 0;
}

.cc-field-label {
  font-size: 11px; font-weight: 600; color: #9ca3af;
  text-transform: uppercase; letter-spacing: 0.06em;
  margin-bottom: 6px;
  display: flex; align-items: center; gap: 5px;
}
.cc-field-value        { font-size: 14px; font-weight: 600; color: #111827; }
.cc-field-value--muted { font-size: 13px; font-weight: 400; color: #6b7280; line-height: 1.6; }

.cc-exp-ref {
  display: flex; align-items: center; gap: 14px;
  background: #f8fafc; border: 1px solid #e8eaed;
  border-radius: 12px; padding: 14px 16px;
  margin-bottom: 22px;
}
.cc-exp-ref__name  { font-size: 15px; font-weight: 700; color: #111827; }
.cc-exp-ref__folio { font-size: 12px; color: #9ca3af; margin-top: 2px; }

.cc-overall-badge {
  margin-left: auto;
  display: inline-flex; align-items: center; gap: 7px;
  padding: 6px 12px; border-radius: 999px;
  font-size: 12px; font-weight: 700;
  white-space: nowrap;
}
.cc-overall-dot { width: 7px; height: 7px; border-radius: 50%; }
.cc-badge-normal   { background: #e4f7ef; color: #067a56; }
.cc-badge-normal .cc-overall-dot { background: #0e9f6e; }
.cc-badge-warning  { background: #fdf1df; color: #a15a05; }
.cc-badge-warning .cc-overall-dot { background: #d97706; }
.cc-badge-critical { background: #fce8e8; color: #b31414; }
.cc-badge-critical .cc-overall-dot { background: #dc2626; animation: cc-pulse 1.4s infinite; }

@keyframes cc-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

.cc-motivo-panel {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 16px;
}
.cc-motivo-panel-head {
  display: flex; align-items: baseline; justify-content: space-between;
  margin-bottom: 10px; padding: 0 2px;
  flex-wrap: wrap; gap: 4px;
}
.cc-motivo-panel-head > span:first-child {
  font-size: 13.5px; font-weight: 700; color: #111827;
  display: flex; align-items: center; gap: 6px;
}
.cc-motivo-panel-sub { font-size: 11px; color: #9ca3af; }
.cc-motivo-textarea {
  width: 100%;
  border: 1px solid #e5e7eb; border-radius: 10px;
  padding: 10px 12px;
  font-size: 13px; font-family: inherit; color: #111827;
  resize: vertical; outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cc-motivo-textarea:focus {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}

.cc-vitals-panel {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 14px;
  padding: 18px;
}
.cc-vitals-panel-head {
  display: flex; align-items: baseline; justify-content: space-between;
  margin-bottom: 14px; padding: 0 2px;
}
.cc-vitals-panel-head > span:first-child {
  font-size: 13.5px; font-weight: 700; color: #111827; letter-spacing: .2px;
}
.cc-vitals-panel-sub { font-size: 11px; color: #9ca3af; }

.cc-vitals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
}
.cc-vital-card {
  background: #f8fafc;
  border: 1px solid #e8eaed;
  border-radius: 12px; padding: 12px 14px;
  transition: border-color .2s, background .2s;
}
.cc-vital-label {
  font-size: 10.5px; font-weight: 600; color: #9ca3af;
  letter-spacing: .3px; margin-bottom: 7px; text-transform: uppercase;
  display: flex; align-items: center; justify-content: space-between; gap: 6px;
}
.cc-vital-readout { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
.cc-vital-input {
  width: 100%; background: transparent; border: none; outline: none;
  font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: 19px;
  color: #111827; padding: 0; min-width: 0;
}
.cc-vital-input::placeholder { color: #cbd5e1; }
.cc-vital-unit { font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; color: #9ca3af; flex-shrink: 0; }

.cc-vital-status-tag {
  display: inline-block; margin-top: 8px; font-size: 10px; font-weight: 700;
  letter-spacing: .3px; text-transform: uppercase; padding: 3px 8px; border-radius: 999px;
}
.cc-v-normal   { background: #e4f7ef; border-color: rgba(14,159,110,.3); }
.cc-v-normal .cc-vital-status-tag { background: rgba(14,159,110,.14); color: #067a56; }
.cc-v-warning  { background: #fdf1df; border-color: rgba(217,119,6,.3); }
.cc-v-warning .cc-vital-input { color: #a15a05; }
.cc-v-warning .cc-vital-status-tag { background: rgba(217,119,6,.16); color: #a15a05; }
.cc-v-critical { background: #fce8e8; border-color: rgba(220,38,38,.35); }
.cc-v-critical .cc-vital-input { color: #b31414; }
.cc-v-critical .cc-vital-status-tag { background: rgba(220,38,38,.16); color: #b31414; }

/* ─── NUEVO: Tarjeta de IMC dentro del modal de triage ─── */
.cc-vital-card-imc {
  grid-column: span 2;
}

.cc-vital-imc-percentil {
  font-size: 10px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0;
  color: #9ca3af;
}

.cc-vital-value-imc {
  font-family: 'IBM Plex Mono', monospace;
  font-weight: 700;
  font-size: 19px;
  color: #111827;
}

.cc-vital-imc-badge {
  font-family: 'Inter', sans-serif;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
}

.cc-imc-badge-normal   { background: #e4f7ef; color: #067a56; }
.cc-imc-badge-warning  { background: #fdf1df; color: #a15a05; }
.cc-imc-badge-critical { background: #fce8e8; color: #b31414; }
.cc-imc-badge-neutro   { background: #f8fafc; color: #9ca3af; }

.cc-vital-card-imc-vacio {
  grid-column: span 2;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.cc-vital-imc-hint {
  font-size: 11px;
  color: #9ca3af;
  font-weight: 400;
}

.cc-section-label {
  font-size: 13px; font-weight: 700; color: #374151;
  margin-bottom: 14px;
  display: flex; align-items: center; gap: 8px;
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

.cc-previews { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
.cc-preview  { position: relative; width: 86px; height: 86px; }
.cc-preview img {
  width: 100%; height: 100%;
  object-fit: cover; border-radius: 10px;
  border: 1px solid #e5e7eb;
}
.cc-preview button {
  position: absolute; top: -6px; right: -6px;
  width: 22px; height: 22px; border-radius: 50%;
  background: #ef4444; color: #fff;
  border: none; cursor: pointer; font-size: 12px;
  display: flex; align-items: center; justify-content: center;
}

.cc-fade-enter-active, .cc-fade-leave-active { transition: opacity 0.2s; }
.cc-fade-enter-from,   .cc-fade-leave-to     { opacity: 0; }

.cc-asignacion-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.cc-select-full {
  width: 100%;
  height: 42px;
  padding: 0 12px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 13px;
  font-family: inherit;
  color: #111827;
  background: #fff;
  outline: none;
  cursor: pointer;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.cc-select-full:focus {
  border-color: #185FA5;
  box-shadow: 0 0 0 3px rgba(24,95,165,0.1);
}
</style>