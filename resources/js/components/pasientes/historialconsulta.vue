<template>
  <div class="col-lg-9">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

      <!-- HEADER TABS -->
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <ul class="nav nav-tabs custom-tabs border-0">

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'consultas' }"
              @click="tabActiva = 'consultas'"
              type="button">
              <i class="fas fa-stethoscope"></i> Consultas
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'recetas' }"
              @click="tabActiva = 'recetas'"
              type="button">
              <i class="fas fa-prescription"></i> Recetas
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'archivos' }"
              @click="tabActiva = 'archivos'"
              type="button">
              <i class="fas fa-file-medical"></i> Archivos
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'notasPsoapp' }"
              @click="tabActiva = 'notasPsoapp'"
              type="button">
              <i class="fas fa-clipboard-list"></i> Notas PSOAPP
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: tabActiva === 'historiaClinica' }"
              @click="tabActiva = 'historiaClinica'"
              type="button">
              <i class="fas fa-notes-medical"></i> Historia Clínica
            </button>
          </li>

        </ul>
      </div>

      <!-- CONTENIDO TABS -->
      <div class="card-body p-4">

        <!-- CONSULTAS -->
        <div v-if="tabActiva === 'consultas'">
          <div class="section-title">
            <h5>Historial de consultas</h5>
            <small>Registro cronológico de atención médica</small>
          </div>

          <div class="consultas-scroll">

            <div v-if="infoConsultas.length === 0" class="alert alert-info text-center">
              <i class="fas fa-folder-open mr-2"></i>
              No se encuentran consultas registradas para este paciente.
            </div>

            <div
              v-else
              class="timeline-card"
              v-for="consulta in infoConsultas"
              :key="consulta.id">
              <div class="timeline-dot" :class="colorPunto(consulta.estado)"></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                 <div class="fecha-hora">
                    <h6 class="fw-bold mb-0">
                      <i class="fas fa-calendar-alt text-primary me-2"></i>
                      {{ formatearFecha(consulta.fecha) }}
                    </h6>

                    <span class="hora-consulta">
                      <i class="fas fa-clock text-primary me-1"></i>
                      {{ formatearHora(consulta.fecha) }}
                    </span>
                  </div>
                  <span
                    class="badge rounded-pill px-3 py-2"
                    :class="colorBadge(consulta.estado)">
                    {{ consulta.tipo_consulta }}
                  </span>
                </div>

                <template v-if="consulta.motivo">
                  <p class="mt-2 mb-1"><strong>Motivo:</strong> {{ consulta.motivo }}</p>
                  <p class="text-muted small mb-3" v-if="consulta.diagnostico">
                    Diagnóstico: {{ consulta.diagnostico }}
                  </p>
                  <a :href="`/HistorialConsulta/${consulta.id}`" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Ver consulta completa
                  </a>
                </template>

                <p class="mt-2 mb-0 text-muted" v-else>
                  {{ consulta.descripcion }}
                </p>
              </div>
            </div>

          </div>
          <!-- fin .consultas-scroll -->
        </div>

        <!-- RECETAS -->
        <div v-if="tabActiva === 'recetas'">

          <div class="section-title">
            <h5>Historial de recetas</h5>
            <small>Tratamientos indicados al paciente</small>
          </div>

          <div class="recetas-scroll">

            <div
              v-if="infoRecetas.length === 0"
              class="alert alert-info text-center"
            >
              <i class="fas fa-prescription me-2"></i>
              No se encuentran recetas registradas para este paciente.
            </div>

            <div
              v-else
              v-for="receta in infoRecetas"
              :key="receta.id"
              class="record-card"
              :class="{ 'record-card--editando': recetaEditando === receta.id }"
            >

              <!-- MODO LECTURA -->
              <template v-if="recetaEditando !== receta.id">

                <div>

                  <h6 class="fw-bold mb-1">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>

                    {{ formatearFecha(receta.created_at) }}
                    <span class="text-muted fw-normal">
                      &middot; {{ formatearHora(receta.created_at) }}
                    </span>
                  </h6>

                  <p class="mb-1">
                    <strong>Medicamentos:</strong>
                  </p>

                  <ul
                    v-if="parseMedicamentos(receta.medicamentos).length"
                    class="mb-2"
                  >
                    <li
                      v-for="(med, index) in parseMedicamentos(receta.medicamentos)"
                      :key="index"
                    >
                      {{ med.nombre }}

                      <span v-if="med.dosis">
                        - {{ med.dosis }}
                      </span>

                      <span v-if="med.frecuencia">
                        - {{ med.frecuencia }}
                      </span>

                      <span v-if="med.duracion">
                        - {{ med.duracion }}
                      </span>

                      <span v-if="med.instrucciones">
                        ({{ med.instrucciones }})
                      </span>
                    </li>
                  </ul>

                  <p
                    v-if="receta.indicaciones_generales"
                    class="mb-0 text-muted"
                  >
                    <strong>Indicaciones:</strong>
                    {{ receta.indicaciones_generales }}
                  </p>

                </div>

                <div class="d-flex flex-column gap-2">
                  <button
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    @click="iniciarEdicionReceta(receta)"
                  >
                    <i class="fas fa-pen me-1"></i>
                    Editar
                  </button>

                  <button
                    class="btn btn-sm btn-outline-primary rounded-pill px-3"
                    @click="verPdfReceta(receta)"
                  >
                    <i class="fas fa-file-pdf me-1"></i>
                    Ver PDF
                  </button>
                </div>

              </template>

              <!-- MODO EDICIÓN -->
              <template v-else>

                <div class="w-100">

                  <h6 class="fw-bold mb-3">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    {{ formatearFecha(receta.created_at) }}
                    <span class="text-muted fw-normal">
                      &middot; {{ formatearHora(receta.created_at) }}
                    </span>
                  </h6>

                  <p class="mb-2"><strong>Medicamentos:</strong></p>

                  <div
                    v-for="(med, index) in edicionReceta.medicamentos"
                    :key="index"
                    class="receta-med-edit-row"
                  >
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="med.nombre"
                      placeholder="Nombre">
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="med.dosis"
                      placeholder="Dosis">
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="med.frecuencia"
                      placeholder="Frecuencia">
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="med.duracion"
                      placeholder="Duración">
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      v-model="med.instrucciones"
                      placeholder="Instrucciones">

                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      @click="eliminarMedicamentoEdicion(index)"
                      title="Quitar medicamento">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>

                  <button
                    type="button"
                    class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-3"
                    @click="agregarMedicamentoEdicion">
                    <i class="fas fa-plus me-1"></i>
                    Agregar medicamento
                  </button>

                  <div class="mb-2">
                    <label class="fw-bold small mb-1 d-block">Indicaciones</label>
                    <textarea
                      class="form-control"
                      rows="2"
                      v-model="edicionReceta.indicaciones_generales"
                      placeholder="Indicaciones generales..."></textarea>
                  </div>

                  <div class="psoapp-actions">
                    <button
                      class="btn btn-sm btn-primary rounded-pill px-3"
                      :disabled="guardandoReceta"
                      @click="guardarEdicionReceta(receta)">
                      <i class="fas fa-save me-1"></i>
                      {{ guardandoReceta ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                    <button
                      class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                      :disabled="guardandoReceta"
                      @click="cancelarEdicionReceta">
                      Cancelar
                    </button>
                  </div>

                </div>

              </template>

            </div>

          </div>
          <!-- fin .recetas-scroll -->

        </div>

        <!-- ARCHIVOS -->
        <div v-if="tabActiva === 'archivos'">
          <div class="section-title">
            <h5>Archivos clínicos</h5>
            <small>Estudios, análisis e imágenes médicas</small>
          </div>

          <div class="row">
            <div v-if="infoArchivos.length === 0" class="col-12">
              <div class="alert alert-info text-center">
                  <i class="fas fa-folder-open mr-2"></i>
                   No se encuentran archivos clínicos para este paciente.
              </div>            
            </div>
            <div 
              class="col-md-4" 
              v-else 
              v-for="documentos in infoArchivos" 
              :key="documentos?.id">
              <div class="file-card">
                <i :class="obtenerIcono(documentos?.archivo_url)"></i>
                  <h6>{{ documentos.tipo_archivo }}</h6>
                  <small class="text-muted">{{ documentos.fecha_subida }}</small>
                <button 
                    class="btn btn-sm btn-outline-primary rounded-pill mt-3"
                    v-if="documentos.archivo_url"
                    @click="verArchivo(documentos)">
                  Ver archivo
                </button>
                <button 
                    v-else
                    class="btn btn-sm btn-outline-primary rounded-pill mt-3"
                    disabled>
                  Sin Archivo
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- NOTAS -->
        <div v-if="tabActiva === 'notas'">
          <div class="section-title">
            <h5>Notas médicas</h5>
            <small>Observaciones generales del paciente</small>
          </div>

          <div class="note-card">
            <small class="text-muted">15 Mayo 2026</small>
            <p class="mb-0 mt-2">
              Paciente reporta mejoría notable. Continuar tratamiento actual.
            </p>
          </div>

          <div class="note-card">
            <small class="text-muted">03 Abril 2026</small>
            <p class="mb-0 mt-2">
              Se recomienda mantener hidratación y vigilancia de síntomas.
            </p>
          </div>
        </div>

        <!-- NOTAS PSOAPP -->
        <div v-if="tabActiva === 'notasPsoapp'">
          <div class="section-title">
            <h5>Notas PSOAPP</h5>
            <small>Presentación · Subjetivo · Objetivo · Análisis · Plan · Pronóstico</small>
          </div>

          <div class="psoapp-scroll">

            <div v-if="infoNotasPsoapp.length === 0" class="alert alert-info text-center">
              <i class="fas fa-clipboard-list mr-2"></i>
              No se encuentran notas PSOAPP registradas para este paciente.
            </div>

            <div
              v-else
              class="psoapp-hist-card"
              v-for="nota in infoNotasPsoapp"
              :key="nota.id">

              <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                <h6 class="fw-bold mb-0">
                  <i class="fas fa-calendar-alt text-primary me-2"></i>
                  {{ formatearFecha(nota.fecha) }}
                  <span class="text-muted fw-normal">&middot; {{ formatearHora(nota.fecha) }}</span>
                </h6>
                <span
                  class="badge rounded-pill px-3 py-2"
                  :class="nota.editado ? 'bg-warning text-dark' : 'bg-success'">
                  {{ nota.editado ? 'Editado' : 'Nota final' }}
                </span>
              </div>

              <!-- MODO LECTURA -->
              <template v-if="notaEditando !== nota.id">

                <div class="psoapp-item" v-if="nota.presentacion">
                  <span class="psoapp-letra bg-primary">P</span>
                  <div><strong>Presentación</strong><p class="mb-0 text-muted">{{ nota.presentacion }}</p></div>
                </div>

                <div class="psoapp-item" v-if="nota.subjetivo">
                  <span class="psoapp-letra bg-info">S</span>
                  <div><strong>Subjetivo</strong><p class="mb-0 text-muted">{{ nota.subjetivo }}</p></div>
                </div>

                <div class="psoapp-item" v-if="nota.objetivo">
                  <span class="psoapp-letra bg-info">O</span>
                  <div><strong>Objetivo</strong><p class="mb-0 text-muted">{{ nota.objetivo }}</p></div>
                </div>

                <div class="psoapp-item" v-if="nota.analisis">
                  <span class="psoapp-letra bg-warning">A</span>
                  <div><strong>Análisis</strong><p class="mb-0 text-muted">{{ nota.analisis }}</p></div>
                </div>

                <div class="psoapp-item" v-if="nota.plan">
                  <span class="psoapp-letra bg-success">P</span>
                  <div><strong>Plan</strong><p class="mb-0 text-muted">{{ nota.plan }}</p></div>
                </div>

                <div class="psoapp-item" v-if="nota.pronostico">
                  <span class="psoapp-letra bg-success">P</span>
                  <div><strong>Pronóstico</strong><p class="mb-0 text-muted">{{ nota.pronostico }}</p></div>
                </div>

                <div
                  v-if="!nota.presentacion && !nota.subjetivo && !nota.objetivo && !nota.analisis && !nota.plan && !nota.pronostico"
                  class="text-muted small mb-2">
                  Esta nota todavía no tiene contenido. Presiona "Editar" para capturarlo.
                </div>

                <div class="psoapp-actions">
                  <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="iniciarEdicion(nota)">
                    <i class="fas fa-pen me-1"></i> Editar
                  </button>
                  <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="verPdfDiagnostico(nota)">
                    <i class="fas fa-eye me-1"></i> Ver PDF
                  </button>
                  <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="descargarPdfDiagnostico(nota)">
                    <i class="fas fa-download me-1"></i> Descargar PDF
                  </button>
                </div>

              </template>

              <!-- MODO EDICIÓN -->
              <template v-else>

                <div class="psoapp-edit-field">
                  <label class="fw-bold small mb-1 d-block">Presentación</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    v-model="edicionPsoapp.presentacion"
                    placeholder="Ficha de identificación y motivo de consulta..."></textarea>
                </div>

                <div class="psoapp-edit-field">
                  <label class="fw-bold small mb-1 d-block">Subjetivo</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    v-model="edicionPsoapp.subjetivo"
                    placeholder="Anamnesis referida por el paciente..."></textarea>
                </div>

                <div class="psoapp-edit-field">
                  <label class="fw-bold small mb-1 d-block">Objetivo</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    v-model="edicionPsoapp.objetivo"
                    placeholder="Exploración física y estudios..."></textarea>
                </div>

                <div class="psoapp-edit-field">
                  <label class="fw-bold small mb-1 d-block">Análisis</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    v-model="edicionPsoapp.analisis"
                    placeholder="Diagnóstico y razonamiento clínico..."></textarea>
                </div>

                <div class="psoapp-edit-field">
                  <label class="fw-bold small mb-1 d-block">Plan</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    v-model="edicionPsoapp.plan"
                    placeholder="Tratamiento, dosis, indicaciones..."></textarea>
                </div>

                <div class="psoapp-edit-field">
                  <label class="fw-bold small mb-1 d-block">Pronóstico</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    v-model="edicionPsoapp.pronostico"
                    placeholder="Evolución esperada para la vida y la función..."></textarea>
                </div>

                <div class="psoapp-actions">
                  <button
                    class="btn btn-sm btn-primary rounded-pill px-3"
                    :disabled="guardandoPsoapp"
                    @click="guardarEdicionPsoapp(nota)">
                    <i class="fas fa-save me-1"></i>
                    {{ guardandoPsoapp ? 'Guardando...' : 'Guardar cambios' }}
                  </button>
                  <button
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    :disabled="guardandoPsoapp"
                    @click="cancelarEdicion">
                    Cancelar
                  </button>
                </div>

              </template>

            </div>
          </div>
          <!-- fin .psoapp-scroll -->
        </div>

        <!-- HISTORIA CLÍNICA -->
        <div v-if="tabActiva === 'historiaClinica'">
          <div class="section-title">
            <h5>Historia Clínica</h5>
            <small>Documento base del expediente (NOM-004-SSA3-2012)</small>
          </div>

          <div class="psoapp-hist-card" v-if="infoExpediente">

            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
              <h6 class="fw-bold mb-0">
                <i class="fas fa-clipboard-check text-primary me-2"></i>
                Progreso: {{ progresoExpediente.completados }}/{{ progresoExpediente.total }}
              </h6>
              <span
                class="badge rounded-pill px-3 py-2"
                :class="infoExpediente.revisado_medico ? 'bg-success' : 'bg-warning text-dark'">
                {{ infoExpediente.revisado_medico ? 'Revisado por el médico' : 'Pendiente de revisión' }}
              </span>
            </div>

            <!-- FICHA DE IDENTIFICACIÓN -->
            <div class="ficha-identificacion mb-4">
              <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">
                FICHA DE IDENTIFICACIÓN
              </h6>
              <div class="row g-2">
                <div class="col-md-4 col-6">
                  <small class="text-muted d-block">Nombre completo</small>
                  <strong>{{ infoPacienteCompleto.nombre || '—' }} {{ infoPacienteCompleto.apellido_paterno }} {{ infoPacienteCompleto.apellido_materno }}</strong>
                </div>
                <div class="col-md-2 col-6">
                  <small class="text-muted d-block">Edad</small>
                  <strong>{{ infoPacienteCompleto.edad_formateada || 'N/D' }}</strong>
                </div>
                <div class="col-md-2 col-6">
                  <small class="text-muted d-block">Sexo</small>
                  <strong>{{ infoPacienteCompleto.sexo || 'N/D' }}</strong>
                </div>
                <div class="col-md-2 col-6">
                  <small class="text-muted d-block">Tipo de sangre</small>
                  <strong>{{ infoPacienteCompleto.tipo_sangre || 'N/D' }}</strong>
                </div>
                <div class="col-md-2 col-6">
                  <small class="text-muted d-block">Teléfono</small>
                  <strong>{{ infoPacienteCompleto.telefono || 'N/D' }}</strong>
                </div>
                 <div class="col-md-5 col-6">
                  <small class="text-muted d-block">Correo</small>
                  <strong>{{ infoPacienteCompleto.email || 'N/D' }}</strong>
               </div>
               <div class="col-md-3 col-6">
                 <small class="text-muted d-block">Fecha de nacimiento</small>
                 <strong>{{ formatearFecha(infoPacienteCompleto.fecha_nacimiento) || 'N/D' }}</strong>
               </div>
               <div class="col-md-4 col-6">
                 <small class="text-muted d-block">CURP</small>
                 <strong>{{ infoPacienteCompleto.curp || 'N/D' }}</strong>
               </div>
               <div class="col-md-6 col-12">
                 <small class="text-muted d-block">Dirección</small>
                 <strong>{{ infoPacienteCompleto.direccion || 'N/D' }}</strong>
               </div>
                <div class="col-md-3 col-6">
                  <small class="text-muted d-block">Alergias</small>
                  <strong :class="infoExpediente.alergias ? 'text-danger' : ''">
                    {{ infoPacienteCompleto.alergias || 'Ninguna registrada' }}
                  </strong>
                </div>
                <div class="col-md-3 col-6">
                    <small class="text-muted d-block">Alergia a medicamentos</small>
                    <strong :class="hayAlergiaMedicamentos ? 'text-danger' : ''">
                        {{ textoAlergiaMedicamentos }}
                    </strong>
                </div>
              </div>
            </div>

            <!-- SIGNOS VITALES DE REFERENCIA -->
            <div class="signos-vitales-ref mb-4" v-if="infoTriage">
              <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">
                SIGNOS VITALES DE REFERENCIA
                <small class="text-muted fw-normal">(último registro: {{ formatearFecha(infoTriage.created_at) }})</small>
              </h6>
              <div class="row g-2">
                <div class="col-4 col-md-2" v-for="dato in datosVitales" :key="dato.etiqueta">
                  <div class="text-center p-2 border rounded-3 bg-light">
                    <small class="text-muted d-block">{{ dato.etiqueta }}</small>
                    <strong>{{ dato.valor || 'N/D' }}</strong>
                  </div>
                </div>
              </div>
            </div>
            <div class="alert alert-light border text-muted small mb-4" v-else>
              <i class="fas fa-info-circle me-1"></i> Este paciente aún no tiene signos vitales registrados.
            </div>

            <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">
              INTERROGATORIO Y EXPLORACIÓN
            </h6>

            <!-- MODO LECTURA -->
            <template v-if="!editandoExpediente">

              <div class="psoapp-item" v-for="campo in camposExpediente" :key="campo.clave">
                <span class="psoapp-letra bg-info">{{ campo.letra }}</span>
                <div>
                  <strong>{{ campo.etiqueta }}</strong>
                  <p class="mb-0 text-muted">
                    {{ infoExpediente[campo.clave] || 'Sin datos capturados todavía.' }}
                  </p>
                </div>
              </div>

              <div class="psoapp-item" v-if="infoExpediente.enfermedades_cronicas">
                <span class="psoapp-letra bg-secondary">G</span>
                <div>
                  <strong>Enfermedades crónicas</strong>
                  <p class="mb-0 text-muted">{{ infoExpediente.enfermedades_cronicas }}</p>
                </div>
              </div>

              <div class="psoapp-actions">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="iniciarEdicionExpediente">
                  <i class="fas fa-pen me-1"></i> Editar
                </button>
                <button
                  v-if="!infoExpediente.revisado_medico"
                  class="btn btn-sm btn-outline-success rounded-pill px-3"
                  @click="marcarExpedienteRevisado">
                  <i class="fas fa-check me-1"></i> Marcar como revisado
                </button>
              </div>

            </template>

            <!-- MODO EDICIÓN (igual que ya la tenías) -->
            <template v-else>

              <div class="psoapp-edit-field" v-for="campo in camposExpediente" :key="campo.clave">
                <label class="fw-bold small mb-1 d-block">{{ campo.etiqueta }}</label>
                <textarea
                  class="form-control"
                  rows="2"
                  v-model="edicionExpediente[campo.clave]"
                  :placeholder="campo.placeholder"></textarea>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-4">
                  <label class="fw-bold small mb-1 d-block">Tipo de sangre</label>
                  <input type="text" class="form-control form-control-sm" v-model="edicionExpediente.tipo_sangre">
                </div>
                <div class="col-md-4">
                  <label class="fw-bold small mb-1 d-block">Alergias</label>
                  <input type="text" class="form-control form-control-sm" v-model="edicionExpediente.alergias">
                </div>
                <div class="col-md-4">
                  <label class="fw-bold small mb-1 d-block">Enfermedades crónicas</label>
                  <input type="text" class="form-control form-control-sm" v-model="edicionExpediente.enfermedades_cronicas">
                </div>
              </div>

              <div class="psoapp-actions">
                <button
                  class="btn btn-sm btn-primary rounded-pill px-3"
                  :disabled="guardandoExpediente"
                  @click="guardarEdicionExpediente">
                  <i class="fas fa-save me-1"></i>
                  {{ guardandoExpediente ? 'Guardando...' : 'Guardar cambios' }}
                </button>
                <button
                  class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                  :disabled="guardandoExpediente"
                  @click="editandoExpediente = false">
                  Cancelar
                </button>
              </div>

            </template>

                          <!-- EVOLUCIÓN DEL PADECIMIENTO (solo lectura, reutiliza Notas PSOAPP) -->
              <hr class="my-4">
              <h6 class="fw-bold text-muted mb-1" style="font-size:13px; letter-spacing:0.5px;">
                EVOLUCIÓN DEL PADECIMIENTO
              </h6>
              <small class="text-muted d-block mb-3">
                Comparación cronológica de cómo llegó el paciente en cada consulta (ver también el tab "Notas PSOAPP" para la nota completa y editarla).
              </small>

              <div v-if="infoNotasPsoapp.length === 0" class="alert alert-light border text-muted small">
                <i class="fas fa-info-circle me-1"></i> Este paciente aún no tiene notas de evolución registradas.
              </div>

              <div v-else class="evolucion-scroll">
                <div
                  class="evolucion-card"
                  v-for="nota in infoNotasPsoapp"
                  :key="'evo-' + nota.id">

                  <h6 class="fw-bold mb-2 small">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    {{ formatearFecha(nota.fecha) }}
                    <span class="text-muted fw-normal">&middot; {{ formatearHora(nota.fecha) }}</span>
                  </h6>

                  <p class="mb-1" v-if="nota.subjetivo">
                    <strong>Padecimiento referido:</strong> {{ nota.subjetivo }}
                  </p>
                  <p class="mb-1" v-if="nota.objetivo">
                    <strong>Exploración:</strong> {{ nota.objetivo }}
                  </p>
                  <p class="mb-0" v-if="nota.plan">
                    <strong>Plan:</strong> {{ nota.plan }}
                  </p>

                  <p v-if="!nota.subjetivo && !nota.objetivo && !nota.plan" class="text-muted small mb-0">
                    Esta consulta todavía no tiene nota de evolución capturada.
                  </p>
                </div>
              </div>

          </div>
        </div>

      </div>
      <!-- fin .card-body -->
    </div>
    <!-- fin .card -->

    <!--Modal para mostrar los archivos a traves de un modal-->
    <div class="modal fade" id="modalArchivo">
      <div class="modal-dialog modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">Vista De Los Archivos Clínicos</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  </button>
              </div>
              <div class="modal-body">
                  <img
                      v-if="archivoSeleccionado && esImagen()"
                      :src="archivoSeleccionado"
                      class="img-fluid">
                  <iframe
                      v-else-if="archivoSeleccionado && esPDF()"
                      :src="archivoSeleccionado"
                      width="100%"
                      height="700"
                      frameborder="0"
                  ></iframe>
                  <div v-else-if="archivoSeleccionado" class="text-center p-5">
                      <i class="fas fa-file-alt text-secondary mb-3" style="font-size:60px;"></i>
                      <p>Este tipo de archivo no se puede previsualizar en el navegador.</p>
                      <a :href="archivoSeleccionado" class="btn btn-primary" target="_blank" download>
                          <i class="fas fa-download me-2"></i>Descargar archivo
                      </a>
                  </div>
              </div>
          </div>
      </div>
    </div>
    <!--Aqui termina el modal para ver los archivos-->

  </div>
</template>

<style scoped>
body {
  background: #f4f6f9;
}
.fecha-hora {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

.hora-consulta {
  color: #6c757d;
  font-size: 14px;
  font-weight: 600;
}

.rounded-4 {
  border-radius: 24px !important;
}

.custom-tabs .nav-link {
  border: none;
  color: #6c757d;
  font-weight: 700;
  padding: 14px 18px;
  border-radius: 16px 16px 0 0;
  cursor: pointer;
  background: transparent;
}

.custom-tabs .nav-link.active {
  background: #f4f8ff;
  color: #0d6efd;
}

.section-title {
  margin-bottom: 20px;
   text-align: center; 
}

.section-title h5 {
  font-weight: 800;
  margin-bottom: 2px;
}

.section-title small {
  color: #6c757d;
}

.consultas-scroll {
  max-height: 480px;
  overflow-y: auto;
  padding-right: 8px;
}

.consultas-scroll::-webkit-scrollbar {
  width: 6px;
}

.consultas-scroll::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}

.consultas-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.recetas-scroll {
  max-height: 480px;
  overflow-y: auto;
  padding-right: 8px;
}

.recetas-scroll::-webkit-scrollbar {
  width: 6px;
}

.recetas-scroll::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}

.recetas-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.psoapp-scroll {
  max-height: 480px;
  overflow-y: auto;
  padding-right: 8px;
}

.psoapp-scroll::-webkit-scrollbar {
  width: 6px;
}

.psoapp-scroll::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}

.psoapp-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.timeline-card,
.record-card,
.note-card,
.psoapp-hist-card {
  background: #fff;
  border: 1px solid #edf0f4;
  border-radius: 18px;
  padding: 18px;
  margin-bottom: 16px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
}

.timeline-card {
  display: flex;
  gap: 16px;
}

.timeline-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  margin-top: 5px;
  flex-shrink: 0;
}

.record-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.record-card--editando {
  align-items: stretch;
}

.receta-med-edit-row {
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr 1fr 1.4fr auto;
  gap: 8px;
  margin-bottom: 8px;
  align-items: center;
}

.receta-med-edit-row input {
  font-size: 13px;
}

.psoapp-item {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 12px;
}

.psoapp-item:last-of-type {
  margin-bottom: 4px;
}

.psoapp-letra {
  flex-shrink: 0;
  width: 26px;
  height: 26px;
  border-radius: 8px;
  color: #fff;
  font-weight: 800;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.psoapp-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 10px;
}

.psoapp-edit-field {
  margin-bottom: 12px;
}

.psoapp-edit-field textarea {
  font-size: 13px;
  resize: vertical;
}

.file-card {
  border: 1px solid #edf0f4;
  border-radius: 20px;
  padding: 22px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.file-card i {
  font-size: 42px;
  margin-bottom: 14px;
}

.file-card h6 {
  font-weight: 700;
  word-break: break-word;
}

.evolucion-scroll {
  max-height: 400px;
  overflow-y: auto;
  padding-right: 8px;
}

.evolucion-scroll::-webkit-scrollbar {
  width: 6px;
}

.evolucion-scroll::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}

.evolucion-card {
  background: #f9fafb;
  border: 1px solid #edf0f4;
  border-left: 3px solid #0d6efd;
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 12px;
}

@media (max-width: 768px) {
  .record-card {
    flex-direction: column;
    align-items: flex-start;
  }

  .receta-med-edit-row {
    grid-template-columns: 1fr;
  }

  .custom-tabs .nav-link {
    padding: 10px 12px;
    font-size: 13px;
  }
}
</style>
<script>
    import ApiService from '../../services/ApiService.js'
    export default {
        name: 'ExpedienteTabs',
        data() {
            return {  
              tabActiva: 'consultas',
              infoArchivos: [],
              infoConsultas: [],
              infoRecetas: [],
              infoNotasPsoapp: [],
              archivoSeleccionado:'',
              archivoExtension: '',

              // Edición de notas PSOAPP en el historial:
              // id de la nota que está en modo edición (null = ninguna)
              notaEditando: null,
              // copia editable de los 6 campos de la nota abierta
              edicionPsoapp: {
                presentacion: '',
                subjetivo: '',
                objetivo: '',
                analisis: '',
                plan: '',
                pronostico: ''
              },
              guardandoPsoapp: false,

              // Edición de recetas en el historial:
              // id de la receta que está en modo edición (null = ninguna)
              recetaEditando: null,
              // copia editable de la receta abierta
              edicionReceta: {
                medicamentos: [],
                indicaciones_generales: ''
              },
              guardandoReceta: false,
              infoPacienteCompleto: {},
              infoTriage: null,
              infoExpediente: null,
              progresoExpediente: { completados: 0, total: 9 },
              editandoExpediente: false,
              edicionExpediente: {},
              guardandoExpediente: false,
              camposExpediente: [
                { clave: 'antecedentes_heredofamiliares', letra: 'H', etiqueta: 'Antecedentes heredofamiliares', placeholder: 'Enfermedades de padres, hermanos, abuelos...' },
                { clave: 'antecedentes_medicos', letra: 'P', etiqueta: 'Antecedentes personales patológicos', placeholder: 'Alergias, transfusiones, tabaquismo, alcoholismo, uso de sustancias...' },
                { clave: 'antecedentes_quirurgicos', letra: 'Q', etiqueta: 'Antecedentes quirúrgicos', placeholder: 'Cirugías previas, año aproximado y motivo...' },
                { clave: 'medicamentos_actuales', letra: 'M', etiqueta: 'Medicamentos actuales', placeholder: 'Medicamentos que el paciente ya toma de forma habitual...' },
                { clave: 'antecedentes_no_patologicos', letra: 'N', etiqueta: 'Antecedentes personales no patológicos', placeholder: 'Alimentación, vivienda, ocupación, actividad física...' },
                { clave: 'padecimiento_actual', letra: 'A', etiqueta: 'Padecimiento actual', placeholder: 'Motivo de consulta y evolución...' },
                { clave: 'interrogatorio_aparatos_sistemas', letra: 'I', etiqueta: 'Interrogatorio por aparatos y sistemas', placeholder: 'Síntomas por aparato distintos al padecimiento actual...' },
                { clave: 'exploracion_fisica', letra: 'E', etiqueta: 'Exploración física', placeholder: 'Habitus exterior, signos vitales, hallazgos por región...' },
                { clave: 'plan_tratamiento_inicial', letra: 'T', etiqueta: 'Plan de tratamiento inicial', placeholder: 'Indicación terapéutica general...' },
              ],
            }
        },
        mounted() {
            console.log('PROP PacienteId:', this.pacienteId);
        },
        computed: {
          datosVitales() {
            if (!this.infoTriage) return []
            const t = this.infoTriage
            return [
              { etiqueta: 'Presión', valor: t.presion ? t.presion + ' mmHg' : null },
              { etiqueta: 'Saturación', valor: t.saturacion ? t.saturacion + '%' : null },
              { etiqueta: 'Temperatura', valor: t.temperatura ? t.temperatura + '°C' : null },
              { etiqueta: 'F. Cardiaca', valor: t.frecuencia_cardiaca ? t.frecuencia_cardiaca + ' lpm' : null },
              { etiqueta: 'F. Respiratoria', valor: t.frecuencia_respiratoria ? t.frecuencia_respiratoria + ' rpm' : null },
              { etiqueta: 'Peso', valor: t.peso ? t.peso + ' kg' : null },
              { etiqueta: 'Talla', valor: t.talla ? t.talla + ' cm' : null },
              { etiqueta: 'IMC', valor: t.imc || null },
            ]
          },

          textoAlergiaMedicamentos() {
                const valor = this.infoPacienteCompleto?.alergia_medicamentos

                if (!valor || !valor.trim()) {
                    return 'No se encontraron alergia a medicamentos'
                }

                if (valor.trim().toLowerCase() === 'ninguna') {
                    return 'Ninguna'
                }

                return valor
           },
            hayAlergiaMedicamentos() {
                const valor = this.infoPacienteCompleto?.alergia_medicamentos
                return !!(valor && valor.trim() && valor.trim().toLowerCase() !== 'ninguna')
            }
        },
        methods: {
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
          async obtenerConsultas(){
              try {
                  const response = await ApiService.get('/historialClinico?paciente_id=' + this.pacienteId)

                  const consultas = response.data.consultas || []

                  this.infoConsultas = consultas
                    .map((consulta) => {
                      const evaluacion = (consulta.evaluaciones && consulta.evaluaciones[0]) || null

                     return {
                        id: consulta.id,
                        fecha: consulta.created_at,
                        estado: consulta.estado_consulta,
                        tipo_consulta: this.formatearEstado(consulta.estado_consulta),
                        motivo: consulta.motivo_consulta,
                        diagnostico: consulta.diagnostico || (evaluacion ? evaluacion.diagnostico_probable : null),
                        descripcion: evaluacion ? evaluacion.recomendacion : null
                      }
                    })
                    .filter(consulta => !!(consulta.diagnostico || consulta.descripcion))

                  console.log('Historial clínico cargado:', this.infoConsultas)
              } catch(error){
                  console.error("Error al obtener el historial clínico:", error)
              }
          },
          // Trae todas las notas PSOAPP guardadas del paciente (de todas
          // sus consultas), con la fecha de la consulta a la que pertenecen.
          async obtenerNotasPsoapp(){
              try {
                  const response = await ApiService.get('/consultaIA/paciente/' + this.pacienteId + '/psoapp')
                  this.infoNotasPsoapp = response.data.notas_psoapp || []
                  console.log('Notas PSOAPP cargadas:', this.infoNotasPsoapp)
              } catch(error){
                  console.error("Error al obtener las notas PSOAPP:", error)
              }
          },

          // ── Edición de nota PSOAPP en el historial ──────────────────
          iniciarEdicion(nota){
            this.notaEditando = nota.id
            this.edicionPsoapp = {
              presentacion: nota.presentacion || '',
              subjetivo: nota.subjetivo || '',
              objetivo: nota.objetivo || '',
              analisis: nota.analisis || '',
              plan: nota.plan || '',
              pronostico: nota.pronostico || ''
            }
          },

          cancelarEdicion(){
            this.notaEditando = null
          },

          // Guarda los cambios reutilizando el MISMO endpoint que usa
          // NotaPSOAPP.vue (ConsultaIAController::guardarPsoapp), mapeando
          // los campos al formato P1/S/O/A/P2/P3 que ese endpoint espera.
          // No se toca el estado actual de la nota (borrador/final).
          async guardarEdicionPsoapp(nota){
            if (this.guardandoPsoapp) return
            this.guardandoPsoapp = true

            try {
              const respuesta = await window.axios.post(`/consultaIA/${nota.consulta_id}/psoapp`, {
                estado: nota.estado === 'final' ? 'final' : 'borrador',
                contenido: {
                  P1: { texto: this.edicionPsoapp.presentacion },
                  S:  { texto: this.edicionPsoapp.subjetivo },
                  O:  { texto: this.edicionPsoapp.objetivo },
                  A:  { texto: this.edicionPsoapp.analisis },
                  P2: { texto: this.edicionPsoapp.plan },
                  P3: { texto: this.edicionPsoapp.pronostico }
                }
              })

              if (respuesta.data && respuesta.data.success) {
                // Reflejamos los cambios en la tarjeta sin recargar todo
                // el historial de notas.
                Object.assign(nota, {
                  presentacion: this.edicionPsoapp.presentacion,
                  subjetivo: this.edicionPsoapp.subjetivo,
                  objetivo: this.edicionPsoapp.objetivo,
                  analisis: this.edicionPsoapp.analisis,
                  plan: this.edicionPsoapp.plan,
                  pronostico: this.edicionPsoapp.pronostico,
                  editado: true   // A partir de ahora el badge muestra "Editado"
                })

                this.notaEditando = null

                Swal.fire({
                  icon: 'success',
                  title: 'Nota actualizada',
                  text: 'Los cambios se guardaron correctamente.',
                  timer: 1800,
                  showConfirmButton: false
                })
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'No se pudo guardar',
                  text: 'Inténtalo de nuevo.'
                })
              }
            } catch (error) {
              console.error('Error al guardar la nota PSOAPP editada:', error)
              const mensaje = error?.response?.data?.error || 'No se pudo guardar la nota PSOAPP.'
              Swal.fire({ icon: 'error', title: 'Error', text: mensaje })
            } finally {
              this.guardandoPsoapp = false
            }
          },

          // ── Edición de receta en el historial ───────────────────────
          // Abre el modo edición para una receta, precargando una copia
          // editable de sus medicamentos (nunca se muta receta.medicamentos
          // directamente, para poder "Cancelar" sin perder lo original).
          iniciarEdicionReceta(receta){
            this.recetaEditando = receta.id
            const medicamentos = this.parseMedicamentos(receta.medicamentos)

            this.edicionReceta = {
              medicamentos: medicamentos.length
                ? medicamentos.map(med => ({
                    nombre: med.nombre || '',
                    dosis: med.dosis || '',
                    frecuencia: med.frecuencia || '',
                    duracion: med.duracion || '',
                    instrucciones: med.instrucciones || ''
                  }))
                : [{ nombre: '', dosis: '', frecuencia: '', duracion: '', instrucciones: '' }],
              indicaciones_generales: receta.indicaciones_generales || ''
            }
          },

          cancelarEdicionReceta(){
            this.recetaEditando = null
          },

          agregarMedicamentoEdicion(){
            this.edicionReceta.medicamentos.push({
              nombre: '', dosis: '', frecuencia: '', duracion: '', instrucciones: ''
            })
          },

          eliminarMedicamentoEdicion(index){
            // Si es el último, lo dejamos vacío en vez de quitar la fila,
            // para que siempre quede al menos un medicamento capturable.
            if (this.edicionReceta.medicamentos.length === 1) {
              this.edicionReceta.medicamentos[0] = {
                nombre: '', dosis: '', frecuencia: '', duracion: '', instrucciones: ''
              }
              return
            }
            this.edicionReceta.medicamentos.splice(index, 1)
          },

          // Guarda los cambios reutilizando el MISMO endpoint que usa
          // RecetaInteligente.vue (ConsultaIAController::guardarReceta,
          // ruta POST /consultaIA/{consultaId}/receta), que hace
          // updateOrCreate por consulta_id.
          async guardarEdicionReceta(receta){
            if (this.guardandoReceta) return

            const medicamentosValidos = this.edicionReceta.medicamentos
              .filter(med => (med.nombre || '').trim() !== '')

            if (!medicamentosValidos.length) {
              Swal.fire({
                icon: 'warning',
                title: 'Falta información',
                text: 'Agrega al menos un medicamento con nombre antes de guardar.'
              })
              return
            }

            if (!receta.consulta_id) {
              Swal.fire({
                icon: 'warning',
                title: 'Sin consulta asociada',
                text: 'Esta receta no tiene una consulta asociada para poder editarla.'
              })
              return
            }

            this.guardandoReceta = true

            try {
              const respuesta = await window.axios.post(`/consultaIA/${receta.consulta_id}/receta`, {
                medicamentos: medicamentosValidos,
                recomendacion: this.edicionReceta.indicaciones_generales
              })

              if (respuesta.data && respuesta.data.success) {
                // Reflejamos los cambios en la tarjeta sin recargar todo
                // el historial de recetas.
                Object.assign(receta, {
                  medicamentos: medicamentosValidos,
                  indicaciones_generales: this.edicionReceta.indicaciones_generales
                })

                this.recetaEditando = null

                Swal.fire({
                  icon: 'success',
                  title: 'Receta actualizada',
                  text: 'Los cambios se guardaron correctamente.',
                  timer: 1800,
                  showConfirmButton: false
                })
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'No se pudo guardar',
                  text: 'Inténtalo de nuevo.'
                })
              }
            } catch (error) {
              console.error('Error al guardar la receta editada:', error)
              const mensaje = error?.response?.data?.error || 'No se pudo guardar la receta.'
              Swal.fire({ icon: 'error', title: 'Error', text: mensaje })
            } finally {
              this.guardandoReceta = false
            }
          },
          
          async obtenerExpedienteClinico() {
            try {
              const response = await ApiService.get('/expedienteClinico/' + this.pacienteId)
              this.infoExpediente = response.data.expediente
              this.progresoExpediente = response.data.progreso
              

              // Ficha de identificación completa (nombre, edad, sexo, teléfono)
              const respPaciente = await ApiService.get('/ExpedienteDetalle/' + this.pacienteId)
              this.infoPacienteCompleto = respPaciente.data
              this.infoTriage = (respPaciente.data.triages && respPaciente.data.triages[0]) || null
            } catch (error) {
              console.error('Error al obtener el expediente clínico:', error)
            }
          },

          iniciarEdicionExpediente() {
            this.edicionExpediente = { ...this.infoExpediente }
            this.editandoExpediente = true
          },

          async guardarEdicionExpediente() {
            if (this.guardandoExpediente) return
            this.guardandoExpediente = true

            try {
              const respuesta = await window.axios.post(
                `/expedienteClinico/${this.pacienteId}`,
                this.edicionExpediente
              )

              if (respuesta.data && respuesta.data.success) {
                this.infoExpediente = respuesta.data.expediente
                this.editandoExpediente = false
                await this.obtenerExpedienteClinico() // refresca el progreso X/7

                Swal.fire({
                  icon: 'success',
                  title: 'Expediente actualizado',
                  timer: 1800,
                  showConfirmButton: false
                })
              }
            } catch (error) {
              console.error('Error al guardar el expediente clínico:', error)
              const mensaje = error?.response?.data?.error || 'No se pudo guardar el expediente clínico.'
              Swal.fire({ icon: 'error', title: 'Error', text: mensaje })
            } finally {
              this.guardandoExpediente = false
            }
          },

          async marcarExpedienteRevisado() {
              try {
                const respuesta = await window.axios.post(`/expedienteClinico/${this.pacienteId}`, {
                  marcar_revisado: true
                })
                if (respuesta.data && respuesta.data.success) {
                  this.infoExpediente = respuesta.data.expediente
                  Swal.fire({ icon: 'success', title: 'Marcado como revisado', timer: 1500, showConfirmButton: false })
                }
              } catch (error) {
                console.error('Error al marcar como revisado:', error)
              }
            },
          

          //Formatea la fecha que llega en created_at (ej: 2026-05-10T14:32:00.000000Z)//
          formatearFecha(fecha){
            if(!fecha) return ''
            const f = new Date(fecha)
            return f.toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
          },
          formatearHora(fecha){
            if(!fecha) return ''

            const f = new Date(fecha)

            return f.toLocaleTimeString('es-MX', {
              hour: '2-digit',
              minute: '2-digit',
              hour12: true
            })
            
          },
           // Formatea el estado
            formatearEstado(estado){
              if(!estado){
                return 'En proceso'
              }

              switch(estado.toLowerCase()){
                case 'en_proceso':
                  return 'En proceso'

                case 'finalizada':
                case 'completada':
                  return 'Finalizada'

                case 'cancelada':
                  return 'Cancelada'

                default:
                  return 'En proceso'
              }
            },

            colorPunto(estado){
              switch((estado || '').toLowerCase()){
                case 'finalizada':
                case 'completada':
                  return 'bg-success'

                case 'en_proceso':
                  return 'bg-warning'

                case 'urgencia':
                  return 'bg-danger'

                default:
                  return 'bg-secondary'
              }
            },
          colorBadge(estado){
            switch((estado || '').toLowerCase()){
              case 'finalizada':
              case 'completada':
                return 'bg-success'
              case 'en_proceso':
                return 'bg-warning text-dark'
              case 'urgencia':
                return 'bg-danger'
              default:
                return 'bg-secondary'
            }
          },
          async obtenerArchivos(){
                try {
                    const response = await ApiService.get('/ExpedienteDetalle/' + this.pacienteId)
                    this.infoArchivos = response.data.archivos || []
                    this.infoRecetas = response.data.recetas   || []

                    console.log('Archivos cargados:',this.infoArchivos)
                    console.log('Recetas cargadas:', this.infoRecetas)

                }catch(error){
                        console.error("Error al obtener Archivos:", error)
                }
            },
            verArchivo(documentos){
              if (!documentos || !documentos.archivo_url) {
                Swal.fire({
                  icon: 'warning',
                  title: 'Sin archivo',
                  text: 'Este registro no tiene archivo asociado.'
                });
                return;
              }

              const baseURL = document
                .querySelector('meta[name="base-url"]')
                .getAttribute('content');
              const base = baseURL.replace(/\/$/, '');

              this.archivoExtension = documentos.archivo_url.split('.').pop().toLowerCase();

              if (documentos.consulta_id) {
                this.archivoSeleccionado = `${base}/consultaIA/archivo/${documentos.id}/descargar`;
              } else {
                let ruta = documentos.archivo_url;
                ruta = ruta.startsWith('/') ? ruta : '/' + ruta;
                this.archivoSeleccionado = base + ruta;
              }

              console.log('URL del archivo a mostrar:', this.archivoSeleccionado);

              $('#modalArchivo').modal('show')
            },
            verPdfReceta(receta){
              if (!receta || !receta.consulta_id) {
                Swal.fire({
                  icon: 'warning',
                  title: 'Sin PDF disponible',
                  text: 'Esta receta no tiene una consulta asociada para generar el PDF.'
                });
                return;
              }

              const baseURL = document
                .querySelector('meta[name="base-url"]')
                .getAttribute('content');
              const base = baseURL.replace(/\/$/, '');

              this.archivoExtension = 'pdf';
              this.archivoSeleccionado = `${base}/consultaIA/${receta.consulta_id}/pdf/receta/ver`;

              console.log('URL del PDF de receta a mostrar:', this.archivoSeleccionado);

              $('#modalArchivo').modal('show')
            },
            // Previsualiza (inline, dentro del modal) el PDF de diagnóstico
            // de la consulta dueña de una nota PSOAPP.
            verPdfDiagnostico(nota){
              if (!nota || !nota.consulta_id) {
                Swal.fire({
                  icon: 'warning',
                  title: 'Sin PDF disponible',
                  text: 'Esta nota no tiene una consulta asociada para generar el PDF.'
                });
                return;
              }

              const baseURL = document
                .querySelector('meta[name="base-url"]')
                .getAttribute('content');
              const base = baseURL.replace(/\/$/, '');

              this.archivoExtension = 'pdf';
              this.archivoSeleccionado = `${base}/consultaIA/${nota.consulta_id}/pdf/diagnostico/ver`;

              $('#modalArchivo').modal('show')
            },
            // Fuerza la DESCARGA (a diferencia de verPdfDiagnostico, que
            // solo previsualiza) del PDF de diagnóstico. Usa la ruta
            // generarPdf, que siempre construye el PDF con la nota PSOAPP
            // más reciente de la consulta — por eso ya trae los cambios
            // guardados con guardarEdicionPsoapp() sin pasos extra.
            descargarPdfDiagnostico(nota){
              if (!nota || !nota.consulta_id) {
                Swal.fire({
                  icon: 'warning',
                  title: 'Sin PDF disponible',
                  text: 'Esta nota no tiene una consulta asociada para generar el PDF.'
                });
                return;
              }

              const baseURL = document
                .querySelector('meta[name="base-url"]')
                .getAttribute('content');
              const base = baseURL.replace(/\/$/, '');

              const url = `${base}/consultaIA/${nota.consulta_id}/pdf/diagnostico`;

              const enlace = document.createElement('a');
              enlace.href = url;
              enlace.target = '_blank';
              document.body.appendChild(enlace);
              enlace.click();
              document.body.removeChild(enlace);
            },
            esImagen(){
                return ['jpg','jpeg','png','gif','webp'].includes(this.archivoExtension)
            },
            esPDF(){
                return this.archivoExtension === 'pdf'
            },
            obtenerIcono(ruta){
              console.log('Ruta recibida:', ruta);
              if(!ruta){
                  return 'fas fa-file-alt text-secondary';
              }
              const extension = ruta.split('.').pop().toLowerCase();
              switch(extension){
                  case 'pdf':
                      return 'fas fa-file-pdf text-danger';
                  case 'doc':
                  case 'docx':
                      return 'fas fa-file-word text-primary';
                  case 'xls':
                  case 'xlsx':
                      return 'fas fa-file-excel text-success';
                  case 'ppt':
                  case 'pptx':
                      return 'fas fa-file-powerpoint text-warning';
                  case 'jpg':
                  case 'jpeg':
                  case 'png':
                  case 'gif':
                  case 'webp':
                      return 'fas fa-file-image text-info';
                  default:
                      return 'fas fa-file-alt text-secondary';
                }
          }
        },
        props:{
            pacienteId:{
                type: [Number, String],
                required: true
            }
        },
        watch:{
            pacienteId:{
                immediate:true,
                handler(id){
                    if(id){
                        this.obtenerArchivos();
                        this.obtenerConsultas();
                        this.obtenerNotasPsoapp();
                        this.obtenerExpedienteClinico();
                    }
                }
            }
        }
    }
</script>