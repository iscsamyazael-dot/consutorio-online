<template>
  <div class="col-lg-9">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

      <!-- HEADER TABS ORGANIZADAS PARA CLÍNICA LABORAL -->
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <ul class="nav nav-tabs custom-tabs border-0 flex-wrap gap-2">
          
          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'ocupacional' }" @click="tabActiva = 'ocupacional'" type="button">
              <i class="fas fa-briefcase-medical text-primary me-1"></i> Ocupacional
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'psicologica' }" @click="tabActiva = 'psicologica'" type="button">
              <i class="fas fa-brain text-info me-1"></i> Psicológica
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'nutricional' }" @click="tabActiva = 'nutricional'" type="button">
              <i class="fas fa-apple-alt text-success me-1"></i> Nutricional
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'audiologiaHistoria' }" @click="tabActiva = 'audiologiaHistoria'" type="button">
              <i class="fas fa-stethoscope text-warning me-1"></i> Audiología - Historia Clínica
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'audiologiaAltoRiesgo' }" @click="tabActiva = 'audiologiaAltoRiesgo'" type="button">
              <i class="fas fa-exclamation-triangle text-danger me-1"></i> Audiología - Alto Riesgo
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'espirometria' }" @click="tabActiva = 'espirometria'" type="button">
              <i class="fas fa-lungs text-danger me-1"></i> Espirometría
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'ergonomica' }" @click="tabActiva = 'ergonomica'" type="button">
              <i class="fas fa-chair text-secondary me-1"></i> Ergonómica
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'archivos' }" @click="tabActiva = 'archivos'" type="button">
              <i class="fas fa-folder-open text-dark me-1"></i> Archivos
            </button>
          </li>

          <li class="nav-item">
            <button class="nav-link" :class="{ active: tabActiva === 'historiaClinica' }" @click="tabActiva = 'historiaClinica'" type="button">
              <i class="fas fa-notes-medical text-primary me-1"></i> Historia Clínica General
            </button>
          </li>

        </ul>
      </div>

      <!-- CONTENIDO TABS -->
      <div class="card-body p-4">

        <!-- 1. VALORACIÓN OCUPACIONAL -->
        <div v-if="tabActiva === 'ocupacional'">
          <div class="section-title">
            <h5><i class="fas fa-briefcase-medical text-primary me-2"></i>Valoraciones Ocupacionales</h5>
            <small>Exámenes médicos de ingreso, periódicos, egreso y cambio de puesto.</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoValoracionesOcupacionales.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran valoraciones ocupacionales registradas.
            </div>
            <div v-else v-for="val in infoValoracionesOcupacionales" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-primary"></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>{{ formatearFecha(val.fecha) }}</h6>
                  <span class="badge rounded-pill px-3 py-2" :class="val.tipo === 'Ingreso' ? 'bg-success' : 'bg-info'">{{ val.tipo || 'Evaluación' }}</span>
                </div>
                <p class="mt-2 mb-1"><strong>Puesto evaluado:</strong> {{ val.puesto || 'No especificado' }}</p>
                <p class="text-muted small mb-2"><strong>Dictamen:</strong> {{ val.dictamen || 'Pendiente' }}</p>
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="verDetalleValoracion(val)">
                  <i class="fas fa-eye me-1"></i> Ver detalle
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. VALORACIÓN PSICOLÓGICA -->
        <div v-if="tabActiva === 'psicologica'">
          <div class="section-title">
            <h5><i class="fas fa-brain text-info me-2"></i>Valoraciones Psicológicas</h5>
            <small>Evaluaciones de riesgo psicosocial y exámenes psicológicos ocupacionales.</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoValoracionesPsicologicas.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran evaluaciones psicológicas registradas.
            </div>
            <div v-else v-for="val in infoValoracionesPsicologicas" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-info"></div>
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-2"><i class="fas fa-calendar-alt text-info me-2"></i>{{ formatearFecha(val.fecha) }}</h6>
                <p class="mb-1"><strong>Instrumento aplicado:</strong> {{ val.instrumento || 'No especificado' }}</p>
                <p class="text-muted small mb-2"><strong>Resultado:</strong> {{ val.resultado || 'Pendiente' }}</p>
                <button class="btn btn-sm btn-outline-info rounded-pill px-3"><i class="fas fa-eye me-1"></i> Ver detalle</button>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. VALORACIÓN NUTRICIONAL -->
        <div v-if="tabActiva === 'nutricional'">
          <div class="section-title">
            <h5><i class="fas fa-apple-alt text-success me-2"></i>Valoraciones Nutricionales</h5>
            <small>Evaluación del estado nutricional del trabajador.</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoValoracionesNutricionales.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran evaluaciones nutricionales registradas.
            </div>
            <div v-else v-for="val in infoValoracionesNutricionales" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-success"></div>
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-2"><i class="fas fa-calendar-alt text-success me-2"></i>{{ formatearFecha(val.fecha) }}</h6>
                <p class="mb-1"><strong>IMC:</strong> {{ val.imc || 'N/D' }} | <strong>% Grasa:</strong> {{ val.grasa || 'N/D' }}%</p>
                <p class="text-muted small mb-2"><strong>Observaciones:</strong> {{ val.observaciones || 'Sin observaciones' }}</p>
                <button class="btn btn-sm btn-outline-success rounded-pill px-3"><i class="fas fa-eye me-1"></i> Ver detalle</button>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. AUDIOLOGÍA - HISTORIA CLÍNICA -->
        <div v-if="tabActiva === 'audiologiaHistoria'">
          <div class="section-title">
            <h5><i class="fas fa-stethoscope text-warning me-2"></i>Audiología - Historia Clínica</h5>
            <small>Antecedentes de hipoacusia, exposición a ruido y evaluaciones audiométricas previas.</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoAudiologiaHistoria.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran registros de historia clínica audiológica.
            </div>
            <div v-else v-for="val in infoAudiologiaHistoria" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-warning"></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-warning me-2"></i>{{ formatearFecha(val.fecha) }}</h6>
                  <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">{{ val.tipo || 'Evaluación' }}</span>
                </div>
                <p class="mt-2 mb-1"><strong>Antecedentes:</strong> {{ val.antecedentes || 'Sin antecedentes registrados' }}</p>
                <p class="text-muted small mb-2"><strong>Exposición a ruido:</strong> {{ val.exposicion_ruido || 'No especificada' }}</p>
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" @click="verDetalleAudiologia(val)">
                  <i class="fas fa-eye me-1"></i> Ver detalle completo
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. AUDIOLOGÍA - TRABAJO ALTO RIESGO -->
        <div v-if="tabActiva === 'audiologiaAltoRiesgo'">
          <div class="section-title">
            <h5><i class="fas fa-exclamation-triangle text-danger me-2"></i>Audiología - Trabajo de Alto Riesgo</h5>
            <small>Evaluaciones audiométricas para puestos con exposición a ruido (NOM-011-STPS-2013).</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoAudiologiaAltoRiesgo.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran evaluaciones de alto riesgo registradas.
            </div>
            <div v-else v-for="val in infoAudiologiaAltoRiesgo" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-danger"></div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-danger me-2"></i>{{ formatearFecha(val.fecha) }}</h6>
                  <span class="badge rounded-pill px-3 py-2 bg-danger">{{ val.nivel_riesgo || 'Alto Riesgo' }}</span>
                </div>
                <p class="mt-2 mb-1"><strong>Puesto de trabajo:</strong> {{ val.puesto || 'No especificado' }}</p>
                <p class="text-muted small mb-1"><strong>Nivel de presión sonora:</strong> {{ val.nivel_presion_sonora || 'N/D' }} dB</p>
                <p class="text-muted small mb-1"><strong>Umbral auditivo:</strong> {{ val.umbral_auditivo || 'N/D' }} dB</p>
                <p class="text-muted small mb-2"><strong>Dictamen:</strong> 
                  <span :class="val.dictamen === 'Apto' ? 'text-success fw-bold' : 'text-danger fw-bold'">
                    {{ val.dictamen || 'Pendiente' }}
                  </span>
                </p>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-danger rounded-pill px-3" @click="verAudiometria(val)">
                    <i class="fas fa-chart-line me-1"></i> Ver audiometría
                  </button>
                  <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="verDetalleAudiologia(val)">
                    <i class="fas fa-eye me-1"></i> Ver expediente
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 6. VALORACIÓN ESPIROMETRÍA -->
        <div v-if="tabActiva === 'espirometria'">
          <div class="section-title">
            <h5><i class="fas fa-lungs text-danger me-2"></i>Historial de Espirometrías</h5>
            <small>Pruebas de función pulmonar (NOM-010-STPS).</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoValoracionesEspirometria.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran espirometrías registradas.
            </div>
            <div v-else v-for="(val, i) in infoValoracionesEspirometria" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-danger"></div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between flex-wrap gap-2">
                    <h6 class="fw-bold mb-0">
                      <i class="fas fa-calendar-alt text-danger me-2"></i>
                      {{ formatearFecha(val.fecha_prueba || val.fecha_valoracion) }}
                    </h6>
                    <span class="badge rounded-pill px-3 py-2" :class="colorAptitudEspirometria(val.aptitud)">
                      {{ val.aptitud || 'Sin aptitud' }}
                    </span>
                  </div>

                  <p class="mt-2 mb-1">
                    <strong>Folio:</strong> {{ val.folio }} &middot; <strong>Tipo:</strong> {{ val.tipo_evaluacion }}
                    <span v-if="val.puesto"> &middot; <strong>Puesto:</strong> {{ val.puesto.nombre }}</span>
                  </p>

                  <p class="mb-1">
                    <strong>FEV1:</strong> {{ val.fev1_l ?? 'N/D' }} L ({{ val.fev1_porcentaje_predicho ?? 'N/D' }}%)
                    <span v-if="tendenciaEspirometria(i)" :class="tendenciaEspirometria(i).clase" class="small ms-1">
                      {{ tendenciaEspirometria(i).texto }}
                    </span>
                    | <strong>FVC:</strong> {{ val.fvc_l ?? 'N/D' }} L ({{ val.fvc_porcentaje_predicho ?? 'N/D' }}%)
                    | <strong>FEV1/FVC:</strong> {{ val.fev1_fvc_ratio ?? 'N/D' }}%
                    <span :class="estadoRelacionEspirometria(val).clase" class="small ms-1">
                      {{ estadoRelacionEspirometria(val).texto }}
                    </span>
                  </p>

                  <p class="text-muted small mb-1">
                    <strong>Z FEV1:</strong> {{ val.fev1_z ?? 'N/D' }} &middot;
                    <strong>Z FEV1/FVC:</strong> {{ val.fev1_fvc_z ?? 'N/D' }} &middot;
                    <strong>Interpretación:</strong> {{ val.interpretacion_sistema || 'Pendiente' }} &middot;
                    <strong>Calidad:</strong> {{ val.calidad_sesion || 'N/D' }}
                  </p>

                  <p class="small mb-1" v-if="val.diagnostico_medico">
                    <strong>Diagnóstico:</strong> {{ resumirTexto(val.diagnostico_medico) }}
                  </p>

                  <p class="small mb-2 text-warning" v-if="val.aptitud && val.aptitud !== 'Apto' && val.recomendaciones">
                    <strong>Restricciones / recomendaciones:</strong> {{ resumirTexto(val.recomendaciones) }}
                  </p>

                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" @click="abrirDetalleEspirometria(val)">
                      <i class="fas fa-eye me-1"></i> Ver detalle
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="imprimirEspirometria(val)">
                      <i class="fas fa-print me-1"></i> Imprimir
                    </button>
                  </div>
                </div>
            </div>
          </div>
        </div>

        <!-- 7. VALORACIÓN ERGONÓMICA -->
        <div v-if="tabActiva === 'ergonomica'">
          <div class="section-title">
            <h5><i class="fas fa-chair text-secondary me-2"></i>Valoración Ergonómica</h5>
            <small>Evaluación de puestos de trabajo y factores de riesgo ergonómico.</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoValoracionesErgonomicas.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran evaluaciones ergonómicas registradas.
            </div>
            <div v-else v-for="val in infoValoracionesErgonomicas" :key="val.id" class="timeline-card">
              <div class="timeline-dot bg-secondary"></div>
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-2"><i class="fas fa-calendar-alt text-secondary me-2"></i>{{ formatearFecha(val.fecha) }}</h6>
                <p class="mb-1"><strong>Método:</strong> {{ val.metodo || 'REBA/RULA/OCRA' }}</p>
                <p class="text-muted small mb-2"><strong>Nivel de riesgo:</strong> {{ val.nivel_riesgo || 'Bajo' }}</p>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-eye me-1"></i> Ver detalle</button>
              </div>
            </div>
          </div>
        </div>

        <!-- 8. ARCHIVOS CLÍNICOS -->
        <div v-if="tabActiva === 'archivos'">
          <div class="section-title">
            <h5><i class="fas fa-folder-open text-dark me-2"></i>Archivos y Estudios Complementarios</h5>
            <small>Estudios de laboratorio, gabinete y documentos digitales.</small>
          </div>
          <div class="row">
            <div v-if="infoArchivos.length === 0" class="col-12">
              <div class="alert alert-light border text-center text-muted">
                <i class="fas fa-folder-open me-2"></i> No se encuentran archivos clínicos para este paciente.
              </div>            
            </div>
            <div class="col-md-4" v-else v-for="documentos in infoArchivos" :key="documentos?.id">
              <div class="file-card">
                <i :class="obtenerIcono(documentos?.archivo_url)"></i>
                <h6>{{ documentos.tipo_archivo || 'Documento' }}</h6>
                <small class="text-muted">{{ formatearFecha(documentos.fecha_subida) }}</small>
                <button class="btn btn-sm btn-outline-primary rounded-pill mt-3" v-if="documentos.archivo_url" @click="verArchivo(documentos)">
                  <i class="fas fa-eye me-1"></i> Ver archivo
                </button>
                <button v-else class="btn btn-sm btn-outline-secondary rounded-pill mt-3" disabled>
                  Sin Archivo
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 9. HISTORIA CLÍNICA GENERAL (NOM-004) -->
        <div v-if="tabActiva === 'historiaClinica'">
          <div class="section-title">
            <h5><i class="fas fa-notes-medical text-primary me-2"></i>Historia Clínica General</h5>
            <small>Documento base del expediente (NOM-004-SSA3-2012)</small>
          </div>

          <div class="psoapp-hist-card" v-if="infoExpediente">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
              <h6 class="fw-bold mb-0"><i class="fas fa-clipboard-check text-primary me-2"></i>Progreso: {{ progresoExpediente.completados }}/{{ progresoExpediente.total }}</h6>
              <span class="badge rounded-pill px-3 py-2" :class="infoExpediente.revisado_medico ? 'bg-success' : 'bg-warning text-dark'">
                {{ infoExpediente.revisado_medico ? 'Revisado por el médico' : 'Pendiente de revisión' }}
              </span>
            </div>

            <!-- FICHA DE IDENTIFICACIÓN -->
            <div class="ficha-identificacion mb-4">
              <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">FICHA DE IDENTIFICACIÓN</h6>
              <div class="row g-2">
                <div class="col-md-4 col-6"><small class="text-muted d-block">Nombre completo</small><strong>{{ infoPacienteCompleto.nombre || '—' }} {{ infoPacienteCompleto.apellido_paterno }} {{ infoPacienteCompleto.apellido_materno }}</strong></div>
                <div class="col-md-2 col-6"><small class="text-muted d-block">Edad</small><strong>{{ infoPacienteCompleto.edad_formateada || 'N/D' }}</strong></div>
                <div class="col-md-2 col-6"><small class="text-muted d-block">Sexo</small><strong>{{ infoPacienteCompleto.sexo || 'N/D' }}</strong></div>
                <div class="col-md-2 col-6"><small class="text-muted d-block">Tipo de sangre</small><strong>{{ infoPacienteCompleto.tipo_sangre || 'N/D' }}</strong></div>
                <div class="col-md-2 col-6"><small class="text-muted d-block">Teléfono</small><strong>{{ infoPacienteCompleto.telefono || 'N/D' }}</strong></div>
                <div class="col-md-4 col-6"><small class="text-muted d-block">Correo</small><strong>{{ infoPacienteCompleto.email || 'N/D' }}</strong></div>
                <div class="col-md-3 col-6"><small class="text-muted d-block">Fecha de nacimiento</small><strong>{{ formatearFecha(infoPacienteCompleto.fecha_nacimiento) || 'N/D' }}</strong></div>
                <div class="col-md-3 col-6"><small class="text-muted d-block">CURP</small><strong>{{ infoPacienteCompleto.curp || 'N/D' }}</strong></div>
                <div class="col-md-6 col-12"><small class="text-muted d-block">Dirección</small><strong>{{ infoPacienteCompleto.direccion || 'N/D' }}</strong></div>
                <div class="col-md-3 col-6"><small class="text-muted d-block">Alergias</small><strong :class="infoExpediente.alergias ? 'text-danger' : ''">{{ infoPacienteCompleto.alergias || 'Ninguna registrada' }}</strong></div>
                <div class="col-md-3 col-6"><small class="text-muted d-block">Alergia a medicamentos</small><strong :class="hayAlergiaMedicamentos ? 'text-danger' : ''">{{ textoAlergiaMedicamentos }}</strong></div>
              </div>
            </div>

            <!-- SIGNOS VITALES -->
            <div class="signos-vitales-ref mb-4" v-if="infoTriage">
              <h6 class="fw-bold text-muted mb-2" style="font-size:13px; letter-spacing:0.5px;">SIGNOS VITALES DE REFERENCIA <small class="text-muted fw-normal">(último: {{ formatearFecha(infoTriage.created_at) }})</small></h6>
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

            <!-- ACCIONES EXPEDIENTE -->
            <div class="psoapp-actions mt-3">
              <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="iniciarEdicionExpediente">
                <i class="fas fa-pen me-1"></i> Editar Historia Clínica
              </button>
              <button v-if="!infoExpediente.revisado_medico" class="btn btn-sm btn-outline-success rounded-pill px-3" @click="marcarExpedienteRevisado">
                <i class="fas fa-check me-1"></i> Marcar como revisado
              </button>
            </div>
            
            <!-- Aquí puedes expandir el modo edición con tus campos originales si lo necesitas -->
            <div v-if="editandoExpediente" class="alert alert-info mt-3">
              <i class="fas fa-info-circle me-1"></i> Modo edición activo. (Integra aquí tus campos de edición originales del expediente).
              <div class="mt-2">
                <button class="btn btn-sm btn-primary rounded-pill px-3" @click="guardarEdicionExpediente"><i class="fas fa-save me-1"></i> Guardar cambios</button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-2" @click="editandoExpediente = false">Cancelar</button>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- MODAL PARA VER ARCHIVOS -->
    <div class="modal fade" id="modalArchivo" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Vista De Los Archivos Clínicos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img v-if="archivoSeleccionado && esImagen()" :src="archivoSeleccionado" class="img-fluid">
            <iframe v-else-if="archivoSeleccionado && esPDF()" :src="archivoSeleccionado" width="100%" height="700" frameborder="0"></iframe>
            <div v-else-if="archivoSeleccionado" class="p-5">
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

    <!-- MODAL DETALLE DE ESPIROMETRÍA -->
    <div class="modal fade" id="modalDetalleEspirometria" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" v-if="espirometriaDetalle">

          <!-- HEADER CON GRADIENTE -->
          <div class="modal-header bg-gradient-medical text-white border-0 py-3">
            <div class="d-flex align-items-center">
              <div class="icon-circle bg-white bg-opacity-25 me-3">
                <i class="fas fa-lungs fs-4"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-1">
                  Espirometría
                  <span class="badge bg-white bg-opacity-25 text-white ms-2 align-middle">
                    #{{ espirometriaDetalle.folio }}
                  </span>
                </h5>
                <small class="opacity-75">
                  <i class="far fa-calendar-alt me-1"></i>
                  {{ formatearFecha(espirometriaDetalle.fecha_prueba || espirometriaDetalle.fecha_valoracion) }}
                  <span class="mx-1">•</span>
                  <i class="fas fa-stethoscope me-1"></i>
                  {{ espirometriaDetalle.tipo_evaluacion || 'Sin tipo' }}
                </small>
              </div>
            </div>
            <span class="badge rounded-pill px-3 py-2 ms-auto me-3 shadow-sm" :class="colorAptitudEspirometria(espirometriaDetalle.aptitud)">
              <i class="fas fa-clipboard-check me-1"></i>
              {{ espirometriaDetalle.aptitud || 'Sin aptitud' }}
            </span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <!-- BODY -->
          <div class="modal-body bg-light-subtle">

            <!-- TARJETAS DE INFORMACIÓN GENERAL -->
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-primary-soft text-primary">
                    <i class="fas fa-briefcase-medical"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Puesto evaluado</small>
                    <strong class="d-block text-dark">{{ espirometriaDetalle.puesto?.nombre || 'No registrado' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-success-soft text-success">
                    <i class="fas fa-user-md"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Remitido por</small>
                    <strong class="d-block text-dark">{{ espirometriaDetalle.remitido_por || 'No registrado' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-info-soft text-info">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Calidad de sesión</small>
                    <strong class="d-block text-dark">
                      {{ espirometriaDetalle.calidad_sesion || 'N/D' }}
                      <span class="text-muted small">· {{ espirometriaDetalle.num_maniobras ?? 'N/D' }} maniobras</span>
                    </strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-warning-soft text-warning">
                    <i class="fas fa-file-medical-alt"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Interpretación</small>
                    <strong class="d-block text-dark">{{ espirometriaDetalle.interpretacion_sistema || 'Pendiente' }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- TARJETA DE TABAQUISMO -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-start-danger">
              <div class="card-body py-3">
                <div class="d-flex align-items-center">
                  <div class="info-card-icon bg-danger-soft text-danger me-3">
                    <i class="fas fa-smoking"></i>
                  </div>
                  <div class="flex-grow-1">
                    <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">
                      Tabaquismo
                    </small>
                    <strong v-if="espirometriaDetalle.cigarrillos_dia || espirometriaDetalle.anos_fumador" class="text-dark">
                      <i class="fas fa-smoke text-danger me-1"></i>
                      {{ espirometriaDetalle.cigarrillos_dia ?? '?' }} cig/día
                      <span class="text-muted">·</span>
                      {{ espirometriaDetalle.anos_fumador ?? '?' }} años
                      <span v-if="paquetesAnio(espirometriaDetalle)" class="badge bg-danger bg-opacity-10 text-danger ms-2">
                        {{ paquetesAnio(espirometriaDetalle) }} paquete-año
                      </span>
                    </strong>
                    <strong v-else class="text-muted fst-italic">No registrado</strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECCIÓN DE PARÁMETROS PRINCIPALES -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-primary-soft text-primary me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-wave-square"></i>
                </div>
                <h6 class="fw-bold mb-0 text-primary">Parámetros principales</h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead>
                      <tr class="bg-light">
                        <th class="border-0 ps-3 text-uppercase small fw-bold text-muted">
                          <i class="fas fa-vial me-1"></i> Parámetro
                        </th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted">Valor</th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted">% predicho</th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted">LLN</th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted">Z</th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted pe-3">Estado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="ps-3 fw-semibold">
                          <span class="badge bg-primary bg-opacity-10 text-primary me-2">FVC</span>
                          Capacidad vital forzada (L)
                        </td>
                        <td class="text-center fw-bold">{{ espirometriaDetalle.fvc_l ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fvc_porcentaje_predicho ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fvc_lln ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fvc_z ?? 'N/D' }}</td>
                        <td class="text-center pe-3">
                          <span class="badge rounded-pill px-3 py-2" :class="estadoLLN(espirometriaDetalle.fvc_l, espirometriaDetalle.fvc_lln).clase">
                            {{ estadoLLN(espirometriaDetalle.fvc_l, espirometriaDetalle.fvc_lln).texto }}
                          </span>
                        </td>
                      </tr>
                      <tr class="bg-light bg-opacity-50">
                        <td class="ps-3 fw-semibold">
                          <span class="badge bg-info bg-opacity-10 text-info me-2">FEV1</span>
                          Volumen espiratorio (L)
                        </td>
                        <td class="text-center fw-bold">{{ espirometriaDetalle.fev1_l ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fev1_porcentaje_predicho ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fev1_lln ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fev1_z ?? 'N/D' }}</td>
                        <td class="text-center pe-3">
                          <span class="badge rounded-pill px-3 py-2" :class="estadoLLN(espirometriaDetalle.fev1_l, espirometriaDetalle.fev1_lln).clase">
                            {{ estadoLLN(espirometriaDetalle.fev1_l, espirometriaDetalle.fev1_lln).texto }}
                          </span>
                        </td>
                      </tr>
                      <tr>
                        <td class="ps-3 fw-semibold">
                          <span class="badge bg-success bg-opacity-10 text-success me-2">FEV1/FVC</span>
                          Relación / Ratio (%)
                        </td>
                        <td class="text-center fw-bold">{{ espirometriaDetalle.fev1_fvc_ratio ?? 'N/D' }}</td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center">{{ espirometriaDetalle.fev1_fvc_lln ?? 'N/D' }}</td>
                        <td class="text-center">{{ espirometriaDetalle.fev1_fvc_z ?? 'N/D' }}</td>
                        <td class="text-center pe-3">
                          <span class="badge rounded-pill px-3 py-2" :class="estadoRelacionEspirometria(espirometriaDetalle).clase">
                            {{ estadoRelacionEspirometria(espirometriaDetalle).texto.replace('· ', '') || 'N/D' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- DIAGNÓSTICO -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-start-primary" v-if="espirometriaDetalle.diagnostico_medico">
              <div class="card-body py-3">
                <div class="d-flex align-items-start">
                  <div class="info-card-icon bg-primary-soft text-primary me-3">
                    <i class="fas fa-notes-medical"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fw-bold text-primary mb-1">Diagnóstico</h6>
                    <p class="mb-0 text-body">{{ espirometriaDetalle.diagnostico_medico }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- RESTRICCIONES Y RECOMENDACIONES -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-start-warning bg-warning bg-opacity-10"
                v-if="espirometriaDetalle.aptitud && espirometriaDetalle.aptitud !== 'Apto' && espirometriaDetalle.recomendaciones">
              <div class="card-body py-3">
                <div class="d-flex align-items-start">
                  <div class="info-card-icon bg-warning text-white me-3">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fw-bold text-warning mb-1">Restricciones y recomendaciones</h6>
                    <p class="mb-0 text-body">{{ espirometriaDetalle.recomendaciones }}</p>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- FOOTER -->
          <div class="modal-footer bg-white border-top py-3">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
              <i class="fas fa-times me-1"></i> Cerrar
            </button>
            <div class="ms-auto d-flex gap-2">
              <button type="button" class="btn btn-outline-primary rounded-pill px-4" @click="imprimirEspirometria(espirometriaDetalle)">
                <i class="fas fa-print me-1"></i> Imprimir
              </button>
              <a :href="urlEditarEspirometria(espirometriaDetalle)" class="btn btn-danger rounded-pill px-4 shadow-sm">
                <i class="fas fa-pen-to-square me-1"></i> Editar
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rounded-4 { border-radius: 24px !important; }

.custom-tabs .nav-link {
  border: none;
  color: #6c757d;
  font-weight: 600;
  padding: 12px 16px;
  border-radius: 12px 12px 0 0;
  cursor: pointer;
  background: transparent;
  transition: all 0.2s ease;
}

.custom-tabs .nav-link:hover {
  background: #f8f9fa;
  color: #0d6efd;
}

.custom-tabs .nav-link.active {
  background: #f4f8ff;
  color: #0d6efd;
  font-weight: 700;
  border-bottom: 3px solid #0d6efd;
}

.section-title {
  margin-bottom: 24px;
  text-align: left;
  border-bottom: 1px solid #edf0f4;
  padding-bottom: 12px;
}

.section-title h5 { font-weight: 800; margin-bottom: 4px; }
.section-title small { color: #6c757d; }

.valoraciones-scroll {
  max-height: 500px;
  overflow-y: auto;
  padding-right: 8px;
}

.valoraciones-scroll::-webkit-scrollbar { width: 6px; }
.valoraciones-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.valoraciones-scroll::-webkit-scrollbar-track { background: transparent; }

.timeline-card {
  background: #fff;
  border: 1px solid #edf0f4;
  border-radius: 16px;
  padding: 18px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  display: flex;
  gap: 16px;
  transition: transform 0.2s;
}

.timeline-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06); }

.timeline-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.file-card {
  border: 1px solid #edf0f4;
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.2s;
}

.file-card:hover { transform: translateY(-2px); }
.file-card i { font-size: 36px; margin-bottom: 12px; }
.file-card h6 { font-weight: 700; word-break: break-word; font-size: 14px; }

@media (max-width: 768px) {
  .custom-tabs .nav-link { padding: 10px 12px; font-size: 13px; }
  .timeline-card { flex-direction: column; }
}

  /* Gradiente del header */
  .bg-gradient-medical {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%);
  }

  /* Círculo con icono */
  .icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Tarjetas de información */
  .info-card {
    background: #fff;
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
  }

  .info-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
  }

  .info-card-content {
    flex: 1;
    min-width: 0;
  }

  /* Colores suaves para iconos */
  .bg-primary-soft { background-color: rgba(13, 110, 253, 0.12) !important; }
  .bg-success-soft { background-color: rgba(25, 135, 84, 0.12) !important; }
  .bg-info-soft { background-color: rgba(13, 202, 240, 0.15) !important; }
  .bg-warning-soft { background-color: rgba(255, 193, 7, 0.18) !important; }
  .bg-danger-soft { background-color: rgba(220, 53, 69, 0.12) !important; }

  /* Bordes laterales de colores */
  .border-start-primary { border-left-width: 4px !important; }
  .border-start-warning { border-left-width: 4px !important; }
  .border-start-danger { border-left-width: 4px !important; }

  /* Sombra grande para el modal */
  .shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
  }

  /* Badge de estados en la tabla */
  .badge.bg-success { background-color: #198754 !important; }
  .badge.bg-warning { background-color: #ffc107 !important; color: #000 !important; }
  .badge.bg-danger { background-color: #dc3545 !important; }
</style>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'

export default {
  name: 'ExpedienteTabsClinicaLaboral',
  data() {
    return {  
      tabActiva: 'ocupacional',
      
      // Arrays para cada tipo de valoración laboral
      infoValoracionesOcupacionales: [],
      infoValoracionesPsicologicas: [],
      infoValoracionesNutricionales: [],
      infoAudiologiaHistoria: [],
      infoAudiologiaAltoRiesgo: [],
      infoValoracionesEspirometria: [],
      espirometriaDetalle: null,
      infoValoracionesErgonomicas: [],
      
      infoArchivos: [],
      archivoSeleccionado: '',
      archivoExtension: '',

      // Datos del expediente general
      infoPacienteCompleto: {},
      infoTriage: null,
      infoExpediente: null,
      progresoExpediente: { completados: 0, total: 9 },
      editandoExpediente: false,
      edicionExpediente: {}
    }
  },
  
  mounted() {
    console.log('Cargando expediente laboral para paciente:', this.pacienteId);
  },

  computed: {
    datosVitales() {
      if (!this.infoTriage) return []
      const t = this.infoTriage
      return [
        { etiqueta: 'Presión', valor: t.presion ? t.presion + ' mmHg' : null },
        { etiqueta: 'Saturación', valor: t.saturacion ? t.saturacion + '%' : null },
        { etiqueta: 'Temp.', valor: t.temperatura ? t.temperatura + '°C' : null },
        { etiqueta: 'F. Card.', valor: t.frecuencia_cardiaca ? t.frecuencia_cardiaca + ' lpm' : null },
        { etiqueta: 'Peso', valor: t.peso ? t.peso + ' kg' : null },
        { etiqueta: 'IMC', valor: t.imc || null },
      ]
    },
    textoAlergiaMedicamentos() {
      const valor = this.infoPacienteCompleto?.alergia_medicamentos
      if (!valor || !valor.trim()) return 'No se encontraron alergias a medicamentos'
      if (valor.trim().toLowerCase() === 'ninguna') return 'Ninguna'
      return valor
    },
    hayAlergiaMedicamentos() {
      const valor = this.infoPacienteCompleto?.alergia_medicamentos
      return !!(valor && valor.trim() && valor.trim().toLowerCase() !== 'ninguna')
    }
  },

  methods: {
    // --- ORQUESTADOR DE CARGA DE DATOS ---
    async cargarDatosLaborales() {
      try {
        // DESCOMENTA Y AJUSTA ESTAS LÍNEAS CON TUS ENDPOINTS REALES DE BACKEND:
        // const respOcupacional = await ApiServiceClinicaTrabajo.get('/clinica-laboral/ocupacional/' + this.pacienteId)
        // this.infoValoracionesOcupacionales = respOcupacional.data || []
        
        // const respAudioHC = await ApiServiceClinicaTrabajo.get('/clinica-laboral/audiologia-hc/' + this.pacienteId)
        // this.infoAudiologiaHistoria = respAudioHC.data || []

        // const respAudioAR = await ApiServiceClinicaTrabajo.get('/clinica-laboral/audiologia-riesgo/' + this.pacienteId)
        // this.infoAudiologiaAltoRiesgo = respAudioAR.data || []

        // Cargamos datos que ya tienes funcionando:
        await this.obtenerArchivos()
        //await this.obtenerExpedienteClinico()
        await this.obtenerEspirometrias()

        
        console.log('Datos laborales cargados exitosamente.')
      } catch (error) {
        console.error("Error al cargar datos laborales:", error)
      }
    },

    async obtenerArchivos() {
      try {
        const response = await ApiServiceClinicaTrabajo.get('/ExpedienteDetalleClinicaTrabajo/' + this.pacienteId)
        this.infoArchivos = response.data.archivos || []
      } catch (error) {
        console.error("Error al obtener Archivos:", error)
      }
    },

    // async obtenerExpedienteClinico() {
    //   try {
    //     const response = await ApiServiceClinicaTrabajo.get('/expedienteClinico/' + this.pacienteId)
    //     this.infoExpediente = response.data.expediente
    //     this.progresoExpediente = response.data.progreso || { completados: 0, total: 9 }
        
    //     const respPaciente = await ApiServiceClinicaTrabajo.get('/ExpedienteDetalleClinicaTrabajo/' + this.pacienteId)
    //     this.infoPacienteCompleto = respPaciente.data
    //     this.infoTriage = (respPaciente.data.triages && respPaciente.data.triages[0]) || null
    //   } catch (error) {
    //     console.error('Error al obtener el expediente clínico:', error)
    //   }
    // },
    //Metodos para mostrar los datos de Espirometria//
    async obtenerEspirometrias() {
      try {
        const response = await ApiServiceClinicaTrabajo.get('/espirometria', {
          params: { paciente_id: this.pacienteId }
        })
        this.infoValoracionesEspirometria = response.data.data || []
      } catch (error) {
        console.error('Error al obtener espirometrías:', error)
      }
    },

    colorAptitudEspirometria(aptitud) {
      if (aptitud === 'Apto') return 'bg-success'
      if (aptitud === 'Apto con restricciones') return 'bg-warning text-dark'
      return 'bg-danger'
    },

    imprimirEspirometria(val) {
      window.open(`/espirometria-imprimir/${val.id}`, '_blank')
    },

    resumirTexto(texto, maximo = 140) {
      if (!texto) return ''
      return texto.length > maximo ? texto.slice(0, maximo).trimEnd() + '…' : texto
    },

    // Compara FEV1/FVC contra su límite inferior (LLN): bajo, limítrofe o normal
    estadoRelacionEspirometria(val) {
      const ratio = Number(val.fev1_fvc_ratio)
      const lln = Number(val.fev1_fvc_lln)
      if (val.fev1_fvc_ratio == null || val.fev1_fvc_lln == null) return { texto: '', clase: '' }
      if (ratio < lln) return { texto: '· Bajo LLN', clase: 'text-danger fw-bold' }
      if (ratio - lln < 1) return { texto: '· Limítrofe (LLN ' + lln + ')', clase: 'text-warning fw-bold' }
      return { texto: '· Normal (LLN ' + lln + ')', clase: 'text-success' }
    },

    // Cambio del FEV1 % predicho frente a la espirometría anterior del mismo paciente.
    // El listado viene ordenado de más reciente a más antigua, así que la anterior es i + 1.
    tendenciaEspirometria(i) {
      const actual = this.infoValoracionesEspirometria[i]
      const anterior = this.infoValoracionesEspirometria[i + 1]
      if (!anterior || actual.fev1_porcentaje_predicho == null || anterior.fev1_porcentaje_predicho == null) return null

      const delta = Number(actual.fev1_porcentaje_predicho) - Number(anterior.fev1_porcentaje_predicho)
      const flecha = delta > 0 ? '↑' : delta < 0 ? '↓' : '='
      return {
        texto: `${flecha} ${Math.abs(delta)} pts vs. anterior`,
        clase: delta <= -5 ? 'text-danger fw-bold' : delta < 0 ? 'text-warning' : 'text-success'
      }
    },

    abrirDetalleEspirometria(val) {
      this.espirometriaDetalle = val
      const modalEl = document.getElementById('modalDetalleEspirometria')
      const modal = new bootstrap.Modal(modalEl)
      modal.show()
    },

    // Misma ruta que antes abría "Ver detalle": la ficha de edición de la valoración
    urlEditarEspirometria(val) {
      return `/ficha-espirometria/${val.paciente_id}?valoracion=${val.id}`
    },

    paquetesAnio(val) {
      const cig = Number(val.cigarrillos_dia)
      const anos = Number(val.anos_fumador)
      if (!cig || !anos) return null
      return ((cig * anos) / 20).toFixed(1)
    },

    estadoLLN(valor, lln) {
      if (valor == null || lln == null) return { texto: 'N/D', clase: '' }
      if (Number(valor) < Number(lln)) return { texto: 'Bajo LLN', clase: 'text-danger fw-bold' }
      return { texto: 'Normal', clase: 'text-success' }
    },

    //Aqui terminan los metodos para ver los detalles de Espirometria//

    // --- UTILIDADES ---
    formatearFecha(fecha) {
      if (!fecha) return ''
      // Las fechas sin hora (AAAA-MM-DD) se fijan al mediodía local para no recorrer el día
      const f = new Date(String(fecha).length === 10 ? fecha + 'T12:00:00' : fecha)
      return f.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
    },

    verArchivo(documentos) {
      if (!documentos || !documentos.archivo_url) {
        Swal.fire({ icon: 'warning', title: 'Sin archivo', text: 'Este registro no tiene archivo asociado.' });
        return;
      }
      const baseURL = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '/';
      const base = baseURL.replace(/\/$/, '');
      this.archivoExtension = documentos.archivo_url.split('.').pop().toLowerCase();
      
      let ruta = documentos.archivo_url.startsWith('/') ? documentos.archivo_url : '/' + documentos.archivo_url;
      this.archivoSeleccionado = base + ruta;
      
      const modalEl = document.getElementById('modalArchivo');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    },

    esImagen() { return ['jpg','jpeg','png','gif','webp'].includes(this.archivoExtension) },
    esPDF() { return this.archivoExtension === 'pdf' },
    
    obtenerIcono(ruta) {
      if (!ruta) return 'fas fa-file-alt text-secondary';
      const ext = ruta.split('.').pop().toLowerCase();
      const iconos = {
        pdf: 'fas fa-file-pdf text-danger',
        doc: 'fas fa-file-word text-primary', docx: 'fas fa-file-word text-primary',
        xls: 'fas fa-file-excel text-success', xlsx: 'fas fa-file-excel text-success',
        jpg: 'fas fa-file-image text-info', jpeg: 'fas fa-file-image text-info', png: 'fas fa-file-image text-info'
      };
      return iconos[ext] || 'fas fa-file-alt text-secondary';
    },

    // --- ACCIONES ESPECÍFICAS ---
    verDetalleValoracion(val) { console.log('Ver detalle ocupacional:', val); },
    verAudiometria(val) { console.log('Ver gráfica de audiometría:', val); },
    verDetalleAudiologia(val) { console.log('Ver detalle de audiología:', val); },

    iniciarEdicionExpediente() { 
      this.edicionExpediente = { ...this.infoExpediente };
      this.editandoExpediente = true; 
    },
    
    async guardarEdicionExpediente() {
      try {
        const respuesta = await window.axios.post(`/#/${this.pacienteId}`, this.edicionExpediente)
        if (respuesta.data?.success) {
          this.infoExpediente = respuesta.data.expediente
          this.editandoExpediente = false
          Swal.fire({ icon: 'success', title: 'Expediente actualizado', timer: 1500, showConfirmButton: false })
        }
      } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el expediente.' })
      }
    },

    async marcarExpedienteRevisado() {
      try {
        const respuesta = await window.axios.post(`/#/${this.pacienteId}`, { marcar_revisado: true })
        if (respuesta.data?.success) {
          this.infoExpediente = respuesta.data.expediente
          Swal.fire({ icon: 'success', title: 'Marcado como revisado', timer: 1500, showConfirmButton: false })
        }
      } catch (error) {
        console.error('Error al marcar como revisado:', error)
      }
    }
  },

  props: {
    pacienteId: { type: [Number, String], required: true }
  },

  watch: {
    pacienteId: {
      immediate: true,
      handler(id) {
        if (id) {
          this.cargarDatosLaborales();
        }
      }
    }
  }
}
</script>