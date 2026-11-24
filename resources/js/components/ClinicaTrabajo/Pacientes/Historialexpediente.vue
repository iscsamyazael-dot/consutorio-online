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
            <div v-else>
              <div v-for="val in infoValoracionesOcupacionales" :key="val.id" class="val-card">

                <!-- Header -->
                <div class="val-card-header">
                  <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="val-fecha">
                      <i class="fas fa-calendar-alt me-1"></i>
                      <strong>{{ formatearFecha(val.fecha_valoracion) }}</strong>
                    </div>
                    <div class="val-meta-info">
                      <span class="val-meta-item">
                        <i class="fas fa-file-invoice me-1"></i>
                        <span>Folio {{ val.folio }}</span>
                      </span>
                      <span class="val-meta-separator">·</span>
                      <span class="val-meta-item">
                        <i class="fas fa-clipboard-list me-1"></i>
                        <span>{{ val.tipo ? val.tipo.replace(/_/g, ' ') : 'Sin tipo' }}</span>
                      </span>
                      <span v-if="val.puesto_nombre_snapshot">
                        <span class="val-meta-separator">·</span>
                        <span class="val-meta-item">
                          <i class="fas fa-briefcase me-1"></i>
                          <span>{{ val.puesto_nombre_snapshot }}</span>
                        </span>
                      </span>
                      <span v-if="val.empresa_cliente">
                        <span class="val-meta-separator">·</span>
                        <span class="val-meta-item">
                          <i class="fas fa-building me-1"></i>
                          <span>{{ val.empresa_cliente.razon_social }}</span>
                        </span>
                      </span>
                    </div>
                  </div>
                  <span class="val-badge-dictamen" :class="claseDictamen(val.dictamen)">
                    <i class="fas fa-clipboard-check me-1"></i>
                    {{ etiquetaDictamen(val.dictamen) }}
                  </span>
                </div>

                <!-- Grid de Métricas con fondos de color suave -->
                <div class="val-metricas-grid">

                  <!-- Dictamen - Azul -->
                  <div class="val-metrica val-metrica-blue">
                    <div class="val-metrica-label">
                      <i class="fas fa-user-md"></i>
                      <span>DICTAMEN</span>
                    </div>
                    <div class="val-metrica-valor">
                      {{ etiquetaDictamen(val.dictamen) }}
                    </div>
                  </div>

                  <!-- Próximo examen - Teal -->
                  <div class="val-metrica val-metrica-teal">
                    <div class="val-metrica-label">
                      <i class="fas fa-calendar-check"></i>
                      <span>PRÓXIMO EXAMEN</span>
                    </div>
                    <div class="val-metrica-valor" :class="claseVigencia(val.vigencia_hasta)">
                      {{ textoVigencia(val.vigencia_hasta) }}
                    </div>
                    <small v-if="val.vigencia_hasta" class="val-metrica-subtexto">
                      {{ formatearFecha(val.vigencia_hasta) }}
                    </small>
                  </div>

                  <!-- Presión arterial - Rosa -->
                  <div class="val-metrica val-metrica-rose">
                    <div class="val-metrica-label">
                      <i class="fas fa-heartbeat"></i>
                      <span>PRESIÓN ARTERIAL</span>
                    </div>
                    <div class="val-metrica-valor">
                      {{ val.ta_sistolica ?? 'N/D' }}/{{ val.ta_diastolica ?? 'N/D' }} mmHg
                    </div>
                    <small :class="clasificarPresionOcupacional(val.ta_sistolica, val.ta_diastolica).clase">
                      {{ clasificarPresionOcupacional(val.ta_sistolica, val.ta_diastolica).texto }}
                    </small>
                  </div>

                  <!-- IMC - Ámbar -->
                  <div class="val-metrica val-metrica-amber">
                    <div class="val-metrica-label">
                      <i class="fas fa-weight"></i>
                      <span>IMC</span>
                    </div>
                    <div class="val-metrica-valor" :class="claseImc(val.imc)">
                      {{ val.imc ?? 'N/D' }}
                    </div>
                    <small v-if="val.imc_clasificacion" class="val-metrica-subtexto">
                      {{ val.imc_clasificacion }}
                    </small>
                  </div>

                </div>

                <!-- Banner de Diagnóstico -->
                <div class="val-banner val-banner-warning" v-if="val.diagnostico_clinico">
                  <div class="val-banner-icon">
                    <i class="fas fa-file-medical-alt"></i>
                  </div>
                  <div class="val-banner-content">
                    <strong>Diagnóstico:</strong>
                    <span>{{ resumirTexto(val.diagnostico_clinico, 220) }}</span>
                  </div>
                </div>

                <!-- Banner de Restricciones -->
                <div class="val-banner val-banner-danger" v-if="val.restricciones">
                  <div class="val-banner-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="val-banner-content">
                    <strong>Restricciones:</strong> {{ resumirTexto(val.restricciones, 220) }}
                  </div>
                </div>

                <!-- Banner de Recomendaciones -->
                <div class="val-banner val-banner-primary" v-if="val.recomendaciones">
                  <div class="val-banner-icon">
                    <i class="fas fa-clipboard-check"></i>
                  </div>
                  <div class="val-banner-content">
                    <strong>Recomendaciones:</strong>
                    <div>{{ val.recomendaciones }}</div>
                  </div>
                </div>

                <!-- Banner de Plan de intervención -->
                <div class="val-banner val-banner-primary" v-if="val.plan_intervencion">
                  <div class="val-banner-icon">
                    <i class="fas fa-tasks"></i>
                  </div>
                  <div class="val-banner-content">
                    <strong>Plan de intervención:</strong>
                    <div>{{ val.plan_intervencion }}</div>
                  </div>
                </div>

                <!-- Acciones -->
                <div class="val-acciones">
                  <button class="btn btn-outline-secondary rounded-pill px-3" @click="verDetalleValoracion(val)">
                    <i class="fas fa-eye me-1"></i> Ver detalle
                  </button>
                  <button class="btn btn-outline-primary rounded-pill px-3" @click="imprimirOcupacional(val)">
                    <i class="fas fa-print me-1"></i> Imprimir
                  </button>
                </div>

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
            <h5><i class="fas fa-stethoscope text-warning me-2"></i>Historial de Audiometrías (Historia Clínica)</h5>
            <small>Evaluaciones de historia clínica audiológica (NOM-011-STPS).</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoAudiologiaHistoria.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran registros de historia clínica audiológica.
            </div>
            <div v-else v-for="(val, i) in infoAudiologiaHistoria" :key="val.id" class="audio-card-v2">

              <!-- Header con badge flotante -->
              <div class="audio-card-header">
                <div class="audio-header-left">
                  <div class="audio-fecha">
                    <i class="fas fa-calendar-alt text-secondary me-1"></i>
                    {{ formatearFecha(val.created_at?.slice(0, 10)) }}
                  </div>
                  <div class="audio-folio">
                    <span class="audio-folio-item">
                      <i class="fas fa-file-invoice me-1"></i>
                      Folio {{ val.folio }}
                    </span>
                    <span class="audio-separador">·</span>
                    <span class="audio-folio-item">
                      <i class="fas fa-clipboard-list me-1"></i>
                      {{ val.audiometria?.tipo_audiometria || 'Evaluación' }}
                    </span>
                    <span class="audio-separador">·</span>
                    <span v-if="val.puesto" class="audio-separador">·</span>
                    <span v-if="val.puesto" class="audio-folio-item">
                      <i class="fas fa-briefcase me-1"></i>
                      {{ val.puesto?.nombre || 'No especificado' }}
                    </span>
                  </div>
                </div>
                <span class="badge-aptitud" :class="getBadgeAptitudClass(val.aptitud)">
                  <i class="fas fa-exclamation-triangle me-1" v-if="val.aptitud === 'Apto con restricciones'"></i>
                  {{ val.aptitud || 'Sin aptitud' }}
                </span>
              </div>

              <!-- Grid de Métricas -->
              <div class="audio-metricas-grid">
                
                <!-- Riesgo Auditivo -->
                <div class="audio-metrica-card riesgo">
                  <div class="audio-metrica-icono">
                    <i class="fas fa-volume-up"></i>
                  </div>
                  <div class="audio-metrica-content">
                    <div class="audio-metrica-label">RIESGO AUDITIVO</div>
                    <div class="audio-metrica-valor">
                      {{ separarRiesgoAuditivo(val.riesgo_auditivo).titulo }}
                    </div>
                    <div
                      v-if="separarRiesgoAuditivo(val.riesgo_auditivo).subtitulo"  
                      class="audio-metrica-subtexto riesgo-texto">
                      {{ separarRiesgoAuditivo(val.riesgo_auditivo).subtitulo }}
                    </div>
                  </div>
                </div>

                <!-- EPP Auditivo -->
                <div class="audio-metrica-card info">
                  <div class="audio-metrica-icono">
                    <i class="fas fa-headphones"></i>
                  </div>
                  <div class="audio-metrica-content">
                    <div class="audio-metrica-label">EPP AUDITIVO</div>
                    <div class="audio-metrica-valor text-left">
                      {{ val.epp_auditivo || 'N/D' }}
                    </div>
                    <!-- ✅ Dinámico: solo se muestra si el riesgo es Alto o Muy Alto -->
                    <div 
                      v-if="esEppObligatorio(val.riesgo_auditivo)" 
                      class="audio-metrica-subtexto info-text"
                    >
                      USO OBLIGATORIO
                    </div>
                  </div>
                </div>

                <!-- PTA Derecho -->
                <div class="audio-metrica-card" :class="getClasePTA(val.pta_der)">
                  <div class="audio-metrica-icono">
                    <i class="fas fa-deaf"></i>
                  </div>
                  <div class="audio-metrica-content">
                    <div class="audio-metrica-label">PTA DERECHO</div>
                    <div class="audio-metrica-valor">
                      {{ dB(val.pta_der) }}
                    </div>
                    <div class="audio-metrica-texto" :class="getTextoClasificacionClass(val.clasificacion_der)">
                      {{ val.clasificacion_der || 'N/D' }}
                    </div>
                  </div>
                </div>

                <!-- PTA Izquierdo -->
                <div class="audio-metrica-card" :class="getClasePTA(val.pta_izq)">
                  <div class="audio-metrica-icono">
                    <i class="fas fa-deaf"></i>
                  </div>
                  <div class="audio-metrica-content">
                    <div class="audio-metrica-label">PTA IZQUIERDO</div>
                    <div class="audio-metrica-valor">
                      {{ dB(val.pta_izq) }}
                    </div>
                    <div class="audio-metrica-texto" :class="getTextoClasificacionClass(val.clasificacion_izq)">
                      {{ val.clasificacion_izq || 'N/D' }}
                    </div>
                  </div>
                </div>

                <!-- PTA Promedio -->
                <div class="audio-metrica-card" :class="getClasePTAPromedio(val.pta_promedio)">
                  <div class="audio-metrica-icono">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <div class="audio-metrica-content">
                    <div class="audio-metrica-label">PTA PROMEDIO</div>
                    <div class="audio-metrica-valor">
                      {{ dB(val.pta_promedio) }}
                    </div>
                    <!-- ✅ Dinámico: usa la clasificación real de la API -->
                    <div 
                      class="audio-metrica-texto" 
                      :class="getTextoClasificacionClass(val.clasificacion)"
                    >
                      {{ getTextoClasificacionPromedio(val.clasificacion) }}
                    </div>
                  </div>
                </div>

              </div>

              <!-- Valoración -->
              <div class="audio-valoracion-box" v-if="val.valoracion">
                <div class="audio-valoracion-icono">
                  <i class="fas fa-stethoscope"></i>
                </div>
                <div class="audio-valoracion-content">
                  <strong>Valoración:</strong> {{ resumirTexto(val.valoracion, 220) }}
                </div>
              </div>

              <!-- Restricciones -->
              <div class="audio-restricciones-box" v-if="val.restricciones">
                <div class="audio-restricciones-icono">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="audio-restricciones-content">
                  <strong>Restricciones:</strong> {{ resumirTexto(val.restricciones, 220) }}
                </div>
              </div>

              <!-- Acciones -->
              <div class="audio-acciones">
                <button class="btn-audio" @click="abrirDetalleAudiologia(val)">
                  <i class="fas fa-eye me-1"></i> Ver detalle
                </button>
                <button class="btn-audio btn-audio-primary" @click="imprimirAudiologia(val)">
                  <i class="fas fa-print me-1"></i> Imprimir
                </button>
              </div>

            </div>
          </div>
        </div>

                <!-- 5. AUDIOLOGÍA - TRABAJO ALTO RIESGO -->
        <div v-if="tabActiva === 'audiologiaAltoRiesgo'">
          <div class="section-title">
            <h5><i class="fas fa-exclamation-triangle text-danger me-2"></i>Audiología - Trabajo de Alto Riesgo</h5>
            <small>Examen de aptitud para trabajo en altura y espacio confinado.</small>
          </div>
          <div class="valoraciones-scroll">
            <div v-if="infoAudiologiaAltoRiesgo.length === 0" class="alert alert-light border text-center text-muted">
              <i class="fas fa-clipboard-check me-2"></i> No se encuentran exámenes de alto riesgo registrados.
            </div>

            <div v-else v-for="val in infoAudiologiaAltoRiesgo" :key="val.id" class="tr-card">
              <div class="tr-card-header">
                <div>
                  <div class="fw-bold">
                    <i class="fas fa-calendar-alt text-danger me-1"></i>{{ formatearFecha(val.fecha) }}
                  </div>
                  <small class="text-muted">Folio {{ val.folio }} · {{ val.puesto_nombre }}</small>
                </div>
                <span class="badge rounded-pill px-3 py-2" :class="colorAptitudTR(val.aptitud)">
                  {{ val.aptitud || 'Sin aptitud' }}
                </span>
              </div>

              <div class="tr-card-riesgos">
                <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                <strong>Riesgos evaluados:</strong> {{ val.riesgos_texto }}
              </div>

              <div class="tr-card-metricas">
                <div class="tr-metrica">
                  <small class="text-muted">Diagnóstico principal</small>
                  <strong>{{ val.diagnostico_principal || 'N/D' }}</strong>
                </div>
                <div class="tr-metrica">
                  <small class="text-muted">PTA promedio</small>
                  <strong>{{ ptaTR(val) }}</strong>
                </div>
                <div class="tr-metrica">
                  <small class="text-muted">Presión · F. cardiaca</small>
                  <strong>{{ val.presion_arterial || 'N/D' }} · {{ val.frecuencia_cardiaca ? val.frecuencia_cardiaca + ' lpm' : 'N/D' }}</strong>
                </div>
              </div>

              <p v-if="val.observaciones" class="small text-muted mb-3">
                <strong>Observaciones:</strong> {{ resumirTexto(val.observaciones, 160) }}
              </p>

              <div class="tr-card-acciones">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="abrirDetalleAltoRiesgo(val)">
                  <i class="fas fa-eye me-1"></i> Ver detalle
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="imprimirAltoRiesgo(val)">
                  <i class="fas fa-print me-1"></i> Imprimir
                </button>
                <a :href="urlEditarAltoRiesgo(val)" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                  <i class="fas fa-pen me-1"></i> Editar
                </a>
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

            <div v-else v-for="(val, i) in infoValoracionesEspirometria" :key="val.id" class="espiro-card">

              <!-- Header -->
              <div class="espiro-card-header">
                <div>
                  <div class="espiro-fecha">
                    <i class="fas fa-calendar-alt text-secondary me-1"></i>
                    {{ formatearFecha(val.fecha_prueba || val.fecha_valoracion) }}
                  </div>
                  <div class="espiro-folio">
                    <span class="espiro-folio-item">
                      <i class="fas fa-file-invoice me-1"></i>
                      Folio {{ val.folio }}
                    </span>
                    <span class="espiro-separador">·</span>
                    <span class="espiro-folio-item">
                      <i class="fas fa-clipboard-list me-1"></i>
                      {{ val.tipo_evaluacion || 'Sin tipo' }}
                    </span>
                    <span v-if="val.puesto">
                      <span class="espiro-separador">·</span>
                      <span class="espiro-folio-item">
                        <i class="fas fa-briefcase me-1"></i>
                        {{ val.puesto.nombre }}
                      </span>
                    </span>
                  </div>
                </div>
                <span class="badge rounded-pill px-3 py-2" :class="colorAptitudEspirometria(val.aptitud)">
                  {{ val.aptitud || 'Sin aptitud' }}
                </span>
              </div>

              <!-- Grid de Métricas -->
              <div class="espiro-metricas-grid">
                
                <!-- FEV1 -->
                <div class="espiro-metrica" :class="getClaseMetrica(val.fev1_porcentaje_predicho)">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-lungs"></i>
                    <span class="espiro-metrica-label">FEV1</span>
                  </div>
                  <div class="espiro-metrica-valor">
                    {{ val.fev1_l ?? 'N/D' }} L
                  </div>
                  <div class="espiro-metrica-porcentaje" :class="getClaseTexto(val.fev1_porcentaje_predicho)">
                    {{ val.fev1_porcentaje_predicho ?? 'N/D' }}%
                    <span v-if="tendenciaEspirometria(i)" class="espiro-tendencia" :class="tendenciaEspirometria(i).clase">
                      {{ tendenciaEspirometria(i).texto }}
                    </span>
                  </div>
                </div>

                <!-- FVC -->
                <div class="espiro-metrica" :class="getClaseMetrica(val.fvc_porcentaje_predicho)">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-wind"></i>
                    <span class="espiro-metrica-label">FVC</span>
                  </div>
                  <div class="espiro-metrica-valor">
                    {{ val.fvc_l ?? 'N/D' }} L
                  </div>
                  <div class="espiro-metrica-porcentaje" :class="getClaseTexto(val.fvc_porcentaje_predicho)">
                    {{ val.fvc_porcentaje_predicho ?? 'N/D' }}%
                  </div>
                </div>

                <!-- FEV1/FVC -->
                <div class="espiro-metrica" :class="getClaseMetricaRatio(val.fev1_fvc_ratio)">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-percentage"></i>
                    <span class="espiro-metrica-label">FEV1/FVC</span>
                  </div>
                  <div class="espiro-metrica-valor">
                    {{ val.fev1_fvc_ratio ?? 'N/D' }}%
                  </div>
                  <div class="espiro-metrica-lln">
                    <span class="audio-clas" :class="estadoRelacionEspirometria(val).clase">
                      {{ estadoRelacionEspirometria(val).texto.replace('· ', '') }}
                    </span>
                  </div>
                </div>

                <!-- Z FEV1 / Z Ratio -->
                <div class="espiro-metrica espiro-metrica-info">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-chart-bar"></i>
                    <span class="espiro-metrica-label">Z FEV1 / Z FEV1/FVC</span>
                  </div>
                  <div class="espiro-metrica-valor espiro-metrica-valor-sm">
                    {{ val.fev1_z ?? 'N/D' }} / {{ val.fev1_fvc_z ?? 'N/D' }}
                  </div>
                </div>

              </div>

              <!-- Interpretación -->
              <div class="espiro-interpretacion">
                <i class="fas fa-stethoscope me-2"></i>
                <strong>Interpretación:</strong>
                <span class="espiro-interpretacion-texto">{{ val.interpretacion_sistema || 'Pendiente' }}</span>
                <span class="espiro-separador-vertical"></span>
                <strong>Calidad:</strong>
                <span class="espiro-calidad-badge">{{ val.calidad_sesion || 'N/D' }}</span>
              </div>

              <!-- Diagnóstico -->
              <div class="espiro-diagnostico" v-if="val.diagnostico_medico">
                <i class="fas fa-file-medical-alt me-2"></i>
                <strong>Diagnóstico:</strong>
                <span class="espiro-diagnostico-texto">{{ resumirTexto(val.diagnostico_medico, 220) }}</span>
              </div>
             <!-- Recomendaciones y Observaciones Finales -->
              <div class="espiro-recomendaciones" v-if="val.recomendaciones">
                <i class="fas fa-clipboard-check me-2"></i>
                <strong>Recomendaciones y Observaciones Finales:</strong>
                <div class="espiro-recomendaciones-texto">
                  {{ val.recomendaciones }}
                </div>
              </div>

              <!-- Acciones -->
              <div class="espiro-acciones">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="abrirDetalleEspirometria(val)">
                  <i class="fas fa-eye me-1"></i> Ver detalle
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="imprimirEspirometria(val)">
                  <i class="fas fa-print me-1"></i> Imprimir
                </button>
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

            <div v-else v-for="val in infoValoracionesErgonomicas" :key="val.id" class="espiro-card">

              <!-- Header -->
              <div class="espiro-card-header">
                <div>
                  <div class="espiro-fecha">
                    <i class="fas fa-calendar-alt text-secondary me-1"></i>
                    {{ formatearFecha(val.fecha_valoracion) }}
                  </div>
                  <div class="espiro-folio">
                    <span class="espiro-folio-item">
                      <i class="fas fa-file-invoice me-1"></i> Folio {{ val.folio }}
                    </span>
                    <span class="espiro-separador">·</span>
                    <span class="espiro-folio-item">
                      <i class="fas fa-clipboard-list me-1"></i> {{ val.tipo_evaluacion || 'Sin tipo' }}
                    </span>
                    <span v-if="val.puesto">
                      <span class="espiro-separador">·</span>
                      <span class="espiro-folio-item">
                        <i class="fas fa-briefcase me-1"></i> {{ val.puesto.nombre }}
                      </span>
                    </span>
                  </div>
                </div>
                <span class="badge rounded-pill px-3 py-2" :class="'bg-' + colorAptitudErgonomia(val)">
                  {{ val.aptitud || 'Sin aptitud' }}
                </span>
              </div>

              <!-- Grid de Métricas -->
              <div class="espiro-metricas-grid">

                <!-- Instrumento -->
                <div class="espiro-metrica primaria">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-clipboard-check"></i>
                    <span class="espiro-metrica-label">Instrumento</span>
                  </div>
                  <div class="espiro-metrica-valor">{{ val.instrumento_utilizado || '—' }}</div>
                  <div class="espiro-metrica-porcentaje info">Método de evaluación</div>
                </div>

                <!-- Puntuación -->
                <div class="espiro-metrica" :class="claseErgo(val)">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-calculator"></i>
                    <span class="espiro-metrica-label">Puntuación</span>
                  </div>
                  <div class="espiro-metrica-valor">{{ puntuacionErgonomia(val) }}</div>
                  <div class="espiro-metrica-porcentaje" :class="claseErgo(val)">Puntaje del instrumento</div>
                </div>

                <!-- Nivel de riesgo -->
                <div class="espiro-metrica" :class="claseErgo(val)">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="espiro-metrica-label">Nivel de riesgo</span>
                  </div>
                  <div class="espiro-metrica-valor text-capitalize">{{ nivelErgonomia(val).replace(/_/g, ' ') }}</div>
                  <div class="espiro-metrica-porcentaje" :class="claseErgo(val)">Riesgo del puesto</div>
                </div>

                <!-- Seguimiento -->
                <div class="espiro-metrica" :class="val.seguimiento_requerido ? 'alerta' : 'normal'">
                  <div class="espiro-metrica-icono">
                    <i class="fas fa-calendar-check"></i>
                    <span class="espiro-metrica-label">Seguimiento</span>
                  </div>
                  <div class="espiro-metrica-valor">{{ val.seguimiento_requerido ? 'Sí' : 'No' }}</div>
                  <div class="espiro-metrica-porcentaje" :class="val.seguimiento_requerido ? 'alerta' : 'normal'">
                    {{ val.seguimiento_requerido ? (val.plazo_proximo_seguimiento || 'Sin plazo') : 'Sin seguimiento' }}
                  </div>
                </div>

              </div>

              <!-- Interpretación -->
              <div class="espiro-interpretacion">
                <i class="fas fa-stethoscope me-2"></i>
                <strong>Interpretación:</strong>
                <span class="espiro-interpretacion-texto">Nivel de riesgo {{ nivelErgonomia(val).replace(/_/g, ' ') }}</span>
                <span class="espiro-separador-vertical"></span>
                <strong>Instrumento:</strong>
                <span class="espiro-interpretacion-texto">{{ val.instrumento_utilizado || '—' }}</span>
              </div>

              <!-- Diagnóstico -->
              <div class="espiro-diagnostico" v-if="val.diagnostico_relacionado">
                <i class="fas fa-file-medical-alt me-2"></i>
                <strong>Diagnóstico:</strong>
                <span class="espiro-diagnostico-texto">{{ resumirTexto(val.diagnostico_relacionado, 220) }}</span>
              </div>

              <!-- Recomendaciones -->
              <div class="espiro-recomendaciones" v-if="val.acciones_recomendadas">
                <i class="fas fa-clipboard-check me-2"></i>
                <strong>Recomendaciones:</strong>
                <div class="espiro-recomendaciones-texto">{{ val.acciones_recomendadas }}</div>
              </div>

              <!-- Acciones -->
              <div class="espiro-acciones">
                <a :href="`/ergonomia-imprimir/${val.id}`" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                  <i class="fas fa-print me-1"></i> Imprimir
                </a>  
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="abrirDetalleErgonomia(val)">
                  <i class="fas fa-eye me-1"></i> Ver detalle
                </button>

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

    
    <!-- MODAL DETALLE VALORACIÓN OCUPACIONAL MEJORADO -->
    <div class="modal fade" id="modalDetalleOcupacional" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" v-if="modalOcupacional">

          <!-- HEADER CON GRADIENTE MEJORADO -->
          <div class="modal-header bg-gradient-medical text-white border-0 py-4">
            <div class="d-flex align-items-center flex-grow-1">
              <div class="icon-circle bg-white bg-opacity-25 me-3 p-3 rounded-circle">
                <i class="fas fa-briefcase-medical fs-3"></i>
              </div>
              <div>
                <h4 class="modal-title fw-bold mb-1">
                  Valoración ocupacional
                  <span class="badge bg-white bg-opacity-25 text-white ms-2 align-middle px-3 py-2">
                    <i class="fas fa-hashtag me-1"></i>
                    {{ modalOcupacional.folio }}
                  </span>
                </h4>
                <div class="d-flex align-items-center gap-3 opacity-75">
                  <small>
                    <i class="far fa-calendar-alt me-1"></i>
                    {{ formatearFecha(modalOcupacional.fecha_valoracion) }}
                  </small>
                  <span class="opacity-50">•</span>
                  <small>
                    <i class="fas fa-clipboard-list me-1"></i>
                    {{ modalOcupacional.tipo ? modalOcupacional.tipo.replace(/_/g, ' ') : 'Sin tipo' }}
                  </small>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <span class="badge rounded-pill px-4 py-2 shadow-sm" :class="claseDictamen(modalOcupacional.dictamen)">
                <i class="fas fa-clipboard-check me-2"></i>
                {{ etiquetaDictamen(modalOcupacional.dictamen) }}
              </span>
              <button type="button" class="btn-close btn-close-white" @click="cerrarDetalleOcupacional" aria-label="Cerrar"></button>
            </div>
          </div>

          <!-- BODY MEJORADO -->
          <div class="modal-body bg-light-subtle p-4">

            <!-- TARJETAS DE INFORMACIÓN GENERAL MEJORADAS -->
            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <div class="info-card-modern h-100 border-0 shadow-sm">
                  <div class="info-card-icon-modern bg-primary-soft text-primary">
                    <i class="fas fa-briefcase fs-4"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold d-block mb-1" style="font-size: 0.7rem;">
                      <i class="fas fa-user-tie me-1"></i> Puesto evaluado
                    </small>
                    <strong class="d-block text-dark fs-6">{{ modalOcupacional.puesto_nombre_snapshot || 'No registrado' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card-modern h-100 border-0 shadow-sm">
                  <div class="info-card-icon-modern bg-success-soft text-success">
                    <i class="fas fa-building fs-4"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold d-block mb-1" style="font-size: 0.7rem;">
                      <i class="fas fa-industry me-1"></i> Empresa
                    </small>
                    <strong class="d-block text-dark fs-6">{{ modalOcupacional.empresa_cliente?.razon_social || 'No registrada' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card-modern h-100 border-0 shadow-sm">
                  <div class="info-card-icon-modern bg-info-soft text-info">
                    <i class="fas fa-calendar-check fs-4"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold d-block mb-1" style="font-size: 0.7rem;">
                      <i class="fas fa-clock me-1"></i> Próximo examen
                    </small>
                    <strong class="d-block fs-6" :class="claseVigencia(modalOcupacional.vigencia_hasta)">
                      {{ textoVigencia(modalOcupacional.vigencia_hasta) }}
                    </strong>
                    <small v-if="modalOcupacional.vigencia_hasta" class="text-muted d-block mt-1">
                      <i class="far fa-calendar me-1"></i> {{ formatearFecha(modalOcupacional.vigencia_hasta) }}
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card-modern h-100 border-0 shadow-sm">
                  <div class="info-card-icon-modern bg-danger-soft text-danger">
                    <i class="fas fa-heartbeat fs-4"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold d-block mb-1" style="font-size: 0.7rem;">
                      <i class="fas fa-stethoscope me-1"></i> Presión arterial
                    </small>
                    <strong class="d-block text-dark fs-6">
                      {{ modalOcupacional.ta_sistolica ?? 'N/D' }}/{{ modalOcupacional.ta_diastolica ?? 'N/D' }} mmHg
                    </strong>
                    <span class="small d-block mt-1" :class="clasificarPresionOcupacional(modalOcupacional.ta_sistolica, modalOcupacional.ta_diastolica).clase">
                      <i class="fas fa-info-circle me-1"></i> {{ clasificarPresionOcupacional(modalOcupacional.ta_sistolica, modalOcupacional.ta_diastolica).texto }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- SIGNOS VITALES Y SOMATOMETRÍA MEJORADO -->
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-3">
                <div class="info-card-icon-modern bg-primary-soft text-primary me-3">
                  <i class="fas fa-heartbeat fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">
                  <i class="fas fa-notes-medical me-2"></i>Signos vitales y somatometría
                </h5>
              </div>
              <div class="card-body p-4">
                <div class="row g-4">
                  <div class="col-6 col-md-4 col-lg-2">
                    <div class="vital-sign-box text-center p-3 rounded-3 bg-light">
                      <i class="fas fa-heart text-danger mb-2 fs-4"></i>
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Frecuencia cardíaca</small>
                      <strong class="d-block fs-5 text-dark">{{ modalOcupacional.fc ?? 'N/D' }} <small class="text-muted">lpm</small></strong>
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-2">
                    <div class="vital-sign-box text-center p-3 rounded-3 bg-light">
                      <i class="fas fa-lungs text-info mb-2 fs-4"></i>
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Frecuencia respiratoria</small>
                      <strong class="d-block fs-5 text-dark">{{ modalOcupacional.fr ?? 'N/D' }} <small class="text-muted">rpm</small></strong>
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-2">
                    <div class="vital-sign-box text-center p-3 rounded-3 bg-light">
                      <i class="fas fa-tint text-primary mb-2 fs-4"></i>
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Saturación O2</small>
                      <strong class="d-block fs-5 text-dark">{{ modalOcupacional.spo2 ?? 'N/D' }} <small class="text-muted">%</small></strong>
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-2">
                    <div class="vital-sign-box text-center p-3 rounded-3 bg-light">
                      <i class="fas fa-thermometer-half text-warning mb-2 fs-4"></i>
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Temperatura</small>
                      <strong class="d-block fs-5 text-dark">{{ modalOcupacional.temperatura_c ?? 'N/D' }} <small class="text-muted">°C</small></strong>
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-2">
                    <div class="vital-sign-box text-center p-3 rounded-3 bg-light">
                      <i class="fas fa-weight text-secondary mb-2 fs-4"></i>
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Peso / Talla</small>
                      <strong class="d-block fs-6 text-dark">
                        {{ modalOcupacional.peso_kg ?? 'N/D' }} kg
                        <br>
                        <small>{{ modalOcupacional.estatura_cm ?? 'N/D' }} cm</small>
                      </strong>
                    </div>
                  </div>
                  <div class="col-6 col-md-4 col-lg-2">
                    <div class="vital-sign-box text-center p-3 rounded-3 bg-light">
                      <i class="fas fa-calculator text-success mb-2 fs-4"></i>
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">IMC</small>
                      <strong class="d-block fs-5" :class="claseImc(modalOcupacional.imc)">
                        {{ modalOcupacional.imc ?? 'N/D' }}
                      </strong>
                      <small v-if="modalOcupacional.imc_clasificacion" class="text-muted d-block mt-1">
                        {{ modalOcupacional.imc_clasificacion }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- RIESGOS DEL PUESTO MEJORADO -->
            <div class="card border-0 shadow-sm mb-4" v-if="modalOcupacional.riesgos">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-3">
                <div class="info-card-icon-modern bg-warning-soft text-warning me-3">
                  <i class="fas fa-hard-hat fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">
                  <i class="fas fa-exclamation-triangle me-2"></i>Riesgos del puesto
                </h5>
              </div>
              <div class="card-body p-4">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="risk-item p-3 rounded-3 bg-light h-100">
                      <div class="d-flex align-items-start">
                        <i class="fas fa-wind text-info fs-4 me-3 mt-1"></i>
                        <div class="flex-grow-1">
                          <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                            <i class="fas fa-biohazard me-1"></i> Agentes de exposición
                          </small>
                          <div class="text-dark">{{ textoRiesgos(modalOcupacional.riesgos.agentes, modalOcupacional.riesgos.agentes_otros) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="risk-item p-3 rounded-3 bg-light h-100">
                      <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-circle text-danger fs-4 me-3 mt-1"></i>
                        <div class="flex-grow-1">
                          <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                            <i class="fas fa-skull-crossbones me-1"></i> Condiciones de riesgo
                          </small>
                          <div class="text-dark">{{ textoRiesgos(modalOcupacional.riesgos.condiciones) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="risk-item p-3 rounded-3 bg-light h-100">
                      <div class="d-flex align-items-start">
                        <i class="fas fa-chair text-secondary fs-4 me-3 mt-1"></i>
                        <div class="flex-grow-1">
                          <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                            <i class="fas fa-user-md me-1"></i> Postura habitual
                          </small>
                          <div class="text-dark">{{ modalOcupacional.riesgos.postura || 'No registrada' }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="risk-item p-3 rounded-3 bg-light h-100">
                      <div class="d-flex align-items-start">
                        <i class="fas fa-tasks text-primary fs-4 me-3 mt-1"></i>
                        <div class="flex-grow-1">
                          <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                            <i class="fas fa-bolt me-1"></i> Exigencias del puesto
                          </small>
                          <div class="text-dark">{{ textoRiesgos(modalOcupacional.riesgos.exigencias) }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- DIAGNÓSTICO Y APTITUD MEJORADO -->
            <div class="card border-0 shadow-sm mb-4" v-if="modalOcupacional.diagnostico_clinico || modalOcupacional.cie11_codigo || modalOcupacional.analisis_correlacion_riesgos || modalOcupacional.restricciones">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-3">
                <div class="info-card-icon-modern bg-warning-soft text-warning me-3">
                  <i class="fas fa-file-medical-alt fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">
                  <i class="fas fa-stethoscope me-2"></i>Diagnóstico y aptitud
                </h5>
              </div>
              <div class="card-body p-4">
                <div v-if="modalOcupacional.diagnostico_clinico" class="mb-4">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-diagnoses text-primary fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-notes-medical me-1"></i> Diagnóstico clínico
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3" style="white-space: pre-line">{{ modalOcupacional.diagnostico_clinico }}</div>
                    </div>
                  </div>
                </div>
                <div v-if="modalOcupacional.cie11_codigo" class="mb-4">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-code text-info fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-book-medical me-1"></i> CIE-11
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3">
                        <span class="badge bg-info text-white me-2">{{ modalOcupacional.cie11_codigo }}</span>
                        {{ modalOcupacional.cie11_titulo }}
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="modalOcupacional.analisis_correlacion_riesgos" class="mb-4">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-search text-warning fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-microscope me-1"></i> Hallazgos relevantes para la aptitud
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3" style="white-space: pre-line">{{ modalOcupacional.analisis_correlacion_riesgos }}</div>
                    </div>
                  </div>
                </div>
                <div v-if="modalOcupacional.restricciones" class="mb-0">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-ban text-danger fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-exclamation-triangle me-1"></i> Restricciones y limitaciones
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3 border-start border-danger border-4" style="white-space: pre-line">{{ modalOcupacional.restricciones }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- RECOMENDACIONES Y SEGUIMIENTO MEJORADO -->
            <div class="card border-0 shadow-sm mb-4" v-if="modalOcupacional.recomendaciones || modalOcupacional.derivaciones_especialista">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-3">
                <div class="info-card-icon-modern bg-success-soft text-success me-3">
                  <i class="fas fa-clipboard-check fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">
                  <i class="fas fa-user-md me-2"></i>Recomendaciones y seguimiento
                </h5>
              </div>
              <div class="card-body p-4">
                <div v-if="modalOcupacional.recomendaciones" class="mb-4">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-lightbulb text-success fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-clipboard-list me-1"></i> Recomendaciones médicas
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3 border-start border-success border-4" style="white-space: pre-line">{{ modalOcupacional.recomendaciones }}</div>
                    </div>
                  </div>
                </div>
                <div v-if="modalOcupacional.derivaciones_especialista" class="mb-0">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-user-md text-info fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-hospital me-1"></i> Derivaciones a especialistas
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3 border-start border-info border-4" style="white-space: pre-line">{{ modalOcupacional.derivaciones_especialista }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- DICTAMEN FINAL MEJORADO -->
            <div class="card border-0 shadow-sm mb-4" v-if="modalOcupacional.dictamen_justificacion || modalOcupacional.observaciones">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-3">
                <div class="info-card-icon-modern bg-primary-soft text-primary me-3">
                  <i class="fas fa-file-signature fs-5"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">
                  <i class="fas fa-gavel me-2"></i>Dictamen final
                </h5>
              </div>
              <div class="card-body p-4">
                <div v-if="modalOcupacional.dictamen_justificacion" class="mb-4">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-file-alt text-primary fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-check-circle me-1"></i> Conclusión médica
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3" style="white-space: pre-line">{{ modalOcupacional.dictamen_justificacion }}</div>
                    </div>
                  </div>
                </div>
                <div v-if="modalOcupacional.observaciones" class="mb-0">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-comment-medical text-secondary fs-4 me-3 mt-1"></i>
                    <div class="flex-grow-1">
                      <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size: 0.7rem;">
                        <i class="fas fa-eye me-1"></i> Observaciones
                      </small>
                      <div class="text-dark p-3 bg-light rounded-3 border-start border-secondary border-4" style="white-space: pre-line">{{ modalOcupacional.observaciones }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- FOOTER MEJORADO -->
          <div class="modal-footer bg-white border-top py-4">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" @click="cerrarDetalleOcupacional">
              <i class="fas fa-times me-2"></i> Cerrar
            </button>
            <div class="ms-auto d-flex gap-2">
              <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2" @click="imprimirOcupacional(modalOcupacional)">
                <i class="fas fa-print me-2"></i> Imprimir
              </button>
              <a
                v-if="!modalOcupacional.esta_firmada"
                :href="urlEditarOcupacional(modalOcupacional)"
                class="btn btn-outline-warning rounded-pill px-4 py-2"
              >
                <i class="fas fa-edit me-2"></i> Editar
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--AQUI TERMINA EL MODAL DETALLE FICHA OCUPACIONAL-->

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
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal('modalDetalleEspirometria')" aria-label="Cerrar"></button>
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
                v-if="espirometriaDetalle.recomendaciones">
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
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4"  @click="cerrarModal('modalDetalleEspirometria')">
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
    <!-- AQUI TERMINA EL MODAL DETALLE ESPIROMETRIA -->

    <!-- MODAL DETALLE DE AUDIOLOGÍA - HISTORIA CLÍNICA -->
    <div class="modal fade" id="modalDetalleAudiologia" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" v-if="audiologiaDetalle">

          <!-- HEADER CON GRADIENTE -->
          <div class="modal-header bg-gradient-medical text-white border-0 py-3">
            <div class="d-flex align-items-center">
              <div class="icon-circle bg-white bg-opacity-25 me-3">
                <i class="fas fa-stethoscope fs-4"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-1">
                  Audiología - Historia Clínica
                  <span class="badge bg-white bg-opacity-25 text-white ms-2 align-middle">
                    #{{ audiologiaDetalle.folio }}
                  </span>
                </h5>
                <small class="opacity-75">
                  <i class="far fa-calendar-alt me-1"></i>
                  {{ formatearFecha(audiologiaDetalle.created_at?.slice(0, 10)) }}
                  <span class="mx-1">•</span>
                  <i class="fas fa-clipboard-list me-1"></i>
                  {{ audiologiaDetalle.audiometria?.tipo_audiometria || 'Evaluación' }}
                </small>
              </div>
            </div>
            <span class="badge rounded-pill px-3 py-2 ms-auto me-3 shadow-sm" :class="colorAptitudAudiologia(audiologiaDetalle.aptitud)">
              <i class="fas fa-clipboard-check me-1"></i>
              {{ audiologiaDetalle.aptitud || 'Sin aptitud' }}
            </span>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal('modalDetalleAudiologia')" aria-label="Cerrar"></button>
          </div>

          <!-- BODY -->
          <div class="modal-body bg-light-subtle">

            <!-- TARJETAS DE INFORMACIÓN GENERAL -->
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-danger-soft text-danger">
                    <i class="fas fa-volume-up"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Riesgo auditivo</small>
                    <strong class="d-block text-dark">{{ audiologiaDetalle.riesgo_auditivo || 'No registrado' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-info-soft text-info">
                    <i class="fas fa-headphones"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">EPP auditivo</small>
                    <strong class="d-block text-dark">{{ audiologiaDetalle.epp_auditivo || 'No registrado' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-success-soft text-success">
                    <i class="fas fa-building"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Antigüedad en la empresa</small>
                    <strong class="d-block text-dark">
                      {{ audiologiaDetalle.anos_empresa != null ? audiologiaDetalle.anos_empresa + ' años' : 'No registrado' }}
                    </strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-primary-soft text-primary">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">PTA promedio</small>
                    <strong class="d-block text-dark">
                      {{ audiologiaDetalle.audiometria?.pta_promedio != null ? audiologiaDetalle.audiometria.pta_promedio + ' dB' : 'N/D' }}
                      <span class="text-muted small">· {{ audiologiaDetalle.audiometria?.clasificacion || 'N/D' }}</span>
                    </strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECCIÓN DE AUDIOMETRÍA POR OÍDO -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-primary-soft text-primary me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-deaf"></i>
                </div>
                <h6 class="fw-bold mb-0 text-primary">Audiometría por oído</h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead>
                      <tr class="bg-light">
                        <th class="border-0 ps-3 text-uppercase small fw-bold text-muted">
                          <i class="fas fa-ear-listen me-1"></i> Oído
                        </th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted">PTA (dB)</th>
                        <th class="border-0 text-center text-uppercase small fw-bold text-muted pe-3">Clasificación</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="ps-3 fw-semibold">
                          <span class="badge bg-primary bg-opacity-10 text-primary me-2">DER</span>
                          Oído derecho
                        </td>
                        <td class="text-center fw-bold">{{ audiologiaDetalle.audiometria?.pta_der ?? 'N/D' }}</td>
                        <td class="text-center pe-3">
                          <span class="badge rounded-pill px-3 py-2" :class="getBadgeBootstrapClass(audiologiaDetalle.audiometria?.clasificacion_der)">
                            {{ audiologiaDetalle.audiometria?.clasificacion_der }}
                          </span>
                        </td>
                      </tr>
                      <tr class="bg-light bg-opacity-50">
                        <td class="ps-3 fw-semibold">
                          <span class="badge bg-info bg-opacity-10 text-info me-2">IZQ</span>
                          Oído izquierdo
                        </td>
                        <td class="text-center fw-bold">{{ audiologiaDetalle.audiometria?.pta_izq ?? 'N/D' }}</td>
                        <td class="text-center pe-3">
                          <span class="badge rounded-pill px-3 py-2" :class="getBadgeBootstrapClass(audiologiaDetalle.audiometria?.clasificacion_izq)">
                            {{ audiologiaDetalle.audiometria?.clasificacion_izq || 'N/D' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p class="small text-muted px-3 py-2 mb-0">
                  <strong>Tipo de hipoacusia:</strong> {{ audiologiaDetalle.audiometria?.tipo_hipoacusia || 'No determinado' }}
                </p>
              </div>
            </div>

            <!-- VALORACIÓN -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-start-primary" v-if="audiologiaDetalle.audiometria?.valoracion">
              <div class="card-body py-3">
                <div class="d-flex align-items-start">
                  <div class="info-card-icon bg-primary-soft text-primary me-3">
                    <i class="fas fa-stethoscope"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fw-bold text-primary mb-1">Valoración</h6>
                    <p class="mb-0 text-body" style="white-space: pre-line;">{{ audiologiaDetalle.audiometria.valoracion }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- RESTRICCIONES -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-start-warning bg-warning bg-opacity-10" v-if="audiologiaDetalle.audiometria?.restricciones">
              <div class="card-body py-3">
                <div class="d-flex align-items-start">
                  <div class="info-card-icon bg-warning-soft text-warning me-3">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fw-bold text-warning mb-1">Restricciones</h6>
                    <p class="mb-0 text-body" style="white-space: pre-line;">{{ audiologiaDetalle.audiometria.restricciones }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- RECOMENDACIONES -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-start-primary"
                v-if="Array.isArray(audiologiaDetalle.audiometria?.recomendaciones) && audiologiaDetalle.audiometria.recomendaciones.length">
              <div class="card-body py-3">
                <div class="d-flex align-items-start">
                  <div class="info-card-icon bg-primary-soft text-primary me-3">
                    <i class="fas fa-clipboard-check"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="fw-bold text-primary mb-2">Recomendaciones</h6>
                    <ol class="mb-0 ps-3">
                      <li v-for="(rec, i) in audiologiaDetalle.audiometria.recomendaciones" :key="i" class="mb-1">{{ rec }}</li>
                    </ol>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- FOOTER -->
          <div class="modal-footer bg-white border-top py-3">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4"  @click="cerrarModal('modalDetalleAudiologia')">
              <i class="fas fa-times me-1"></i> Cerrar
            </button>
            <div class="ms-auto d-flex gap-2">
              <button type="button" class="btn btn-outline-primary rounded-pill px-4" @click="imprimirAudiologia(audiologiaDetalle)">
                <i class="fas fa-print me-1"></i> Imprimir
              </button>
              <a :href="urlEditarAudiologia(audiologiaDetalle)" class="btn btn-warning rounded-pill px-4 shadow-sm">
                <i class="fas fa-pen-to-square me-1"></i> Editar
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- AQUI TERMINA EL MODAL DETALLE DE AUDIOLOGÍA - HISTORIA CLÍNICA -->
    
    <!-- INICIA EL MODAL PARA VER LOS DETALLES DE ERGONOMIA -->
    <div class="modal fade" id="modalDetalleErgonomia" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" v-if="ergonomiaDetalle">

          <!-- HEADER -->
          <div class="modal-header bg-gradient-medical text-white border-0 py-3">
            <div class="d-flex align-items-center">
              <div class="icon-circle bg-white bg-opacity-25 me-3">
                <i class="fas fa-chair fs-4"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-1">
                  Valoración Ergonómica
                  <span class="badge bg-white bg-opacity-25 text-white ms-2 align-middle">
                    <i class="fas fa-tag me-1" style="font-size: 0.7em;"></i>
                    #{{ ergonomiaDetalle.folio }}
                  </span>
                </h5>
                <small class="opacity-75">
                  <i class="far fa-calendar-alt me-1"></i>
                  {{ formatearFecha(ergonomiaDetalle.fecha_valoracion) }}
                  <span class="mx-1">•</span>
                  <i class="far fa-clock me-1"></i>{{ horaErgonomiaDetalle || '—' }}
                  <span class="mx-1">•</span>
                  <i class="fas fa-clipboard-list me-1"></i>
                  {{ ergonomiaDetalle.tipo_evaluacion || 'Evaluación' }}
                </small>
              </div>
            </div>
            <span class="badge rounded-pill px-3 py-2 ms-auto me-3 shadow-sm" :class="'bg-' + colorAptitudErgonomia(ergonomiaDetalle)">
              <i class="fas fa-clipboard-check me-1"></i>
              {{ ergonomiaDetalle.aptitud || 'Sin aptitud' }}
            </span>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal('modalDetalleErgonomia')" aria-label="Cerrar"></button>
          </div>

          <!-- BODY -->
          <div class="modal-body bg-light-subtle">

            <!-- TARJETAS DE INFORMACIÓN GENERAL -->
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-primary-soft text-primary">
                    <i class="fas fa-clipboard-check"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Instrumento</small>
                    <strong class="d-block text-dark">{{ ergonomiaDetalle.instrumento_utilizado || 'No registrado' }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-info-soft text-info">
                    <i class="fas fa-calculator"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Puntuación</small>
                    <strong class="d-block text-dark">{{ puntuacionErgonomia(ergonomiaDetalle) }} pts</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-danger-soft text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Nivel de riesgo</small>
                    <strong class="d-block text-dark text-capitalize">{{ nivelErgonomia(ergonomiaDetalle).replace(/_/g, ' ') }}</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-card h-100 border-0 shadow-sm">
                  <div class="info-card-icon bg-success-soft text-success">
                    <i class="fas fa-calendar-check"></i>
                  </div>
                  <div class="info-card-content">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Requiere seguimiento</small>
                    <strong class="d-block" :class="ergonomiaDetalle.seguimiento_requerido ? 'text-warning' : 'text-success'">
                      <i :class="ergonomiaDetalle.seguimiento_requerido ? 'fas fa-exclamation-circle' : 'fas fa-check-circle'" class="me-1"></i>
                      {{ ergonomiaDetalle.seguimiento_requerido ? 'Sí' : 'No' }}
                    </strong>
                    <small v-if="ergonomiaDetalle.seguimiento_requerido" class="text-muted d-block mt-1">
                      <i class="far fa-clock me-1"></i>{{ ergonomiaDetalle.plazo_proximo_seguimiento || 'Sin plazo' }}
                    </small>
                  </div>
                </div>
              </div>
            </div>

            <!-- DATOS DEL TRABAJADOR Y PUESTO -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-primary-soft text-primary me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-user"></i>
                </div>
                <h6 class="fw-bold mb-0 text-primary">Trabajador y puesto</h6>
              </div>
              <div class="card-body small">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-user-circle text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Trabajador</small>
                        <div class="fw-semibold">{{ ergonomiaDetalle.paciente?.nombre || '—' }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-building text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Empresa</small>
                        <div class="fw-semibold">{{ ergonomiaDetalle.empresa?.razon_social || '—' }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-briefcase text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Puesto</small>
                        <div class="fw-semibold">{{ ergonomiaDetalle.puesto?.nombre || '—' }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-industry text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Departamento</small>
                        <div class="fw-semibold">{{ ergonomiaDetalle.puesto?.departamento || '—' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- DIAGNÓSTICO Y EXAMEN -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-warning">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-warning-soft text-warning me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-heartbeat"></i>
                </div>
                <h6 class="fw-bold mb-0 text-warning">Diagnóstico y examen médico</h6>
              </div>
              <div class="card-body small">
                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-search text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Hallazgos</small>
                        <div>{{ datosErgonomiaDetalle.examenMedico?.exploracion_osteomuscular_hallazgos || '—' }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Regiones con hallazgo</small>
                        <div>
                          <span v-if="regionesErgonomiaDetalle.length">{{ regionesErgonomiaDetalle.join(', ') }}</span>
                          <span v-else class="text-muted">Ninguna</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="my-2">
                <div class="d-flex align-items-start">
                  <i class="fas fa-stethoscope text-muted me-2 mt-1"></i>
                  <div>
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Diagnóstico relacionado</small>
                    <div>{{ ergonomiaDetalle.diagnostico_relacionado || '—' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- EVALUACIÓN CON INSTRUMENTO -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-primary-soft text-primary me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-ruler-combined"></i>
                </div>
                <h6 class="fw-bold mb-0 text-primary">Evaluación con instrumento</h6>
              </div>
              <div class="card-body small">
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <div class="p-2 bg-light rounded">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Instrumento</small>
                      <div class="fw-bold">{{ ergonomiaDetalle.instrumento_utilizado || '—' }}</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-2 bg-light rounded">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Puntuación</small>
                      <div class="fw-bold">{{ puntuacionErgonomia(ergonomiaDetalle) }}</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-2 bg-light rounded">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Nivel</small>
                      <div class="fw-bold text-capitalize">{{ nivelErgonomia(ergonomiaDetalle).replace(/_/g, ' ') }}</div>
                    </div>
                  </div>
                </div>
                <div class="mb-2">
                  <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">
                    <i class="fas fa-crosshairs me-1"></i>Áreas evaluadas
                  </small>
                  <p class="mb-0 mt-1" style="white-space: pre-line;">{{ areasErgonomiaDetalle(ergonomiaDetalle) }}</p>
                </div>
                <div>
                  <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">
                    <i class="fas fa-info-circle me-1"></i>Justificación del instrumento
                  </small>
                  <p class="mb-0 mt-1" style="white-space: pre-line;">{{ ergonomiaDetalle.justificacion_instrumento || '—' }}</p>
                </div>
              </div>
            </div>

            <!-- FACTORES DE RIESGO -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-warning-soft text-warning me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h6 class="fw-bold mb-0 text-warning">Factores de riesgo del puesto</h6>
              </div>
              <div class="card-body small">
                <!-- Métricas principales -->
                <div class="row g-2 mb-3">
                  <div class="col-md-4">
                    <div class="p-2 bg-light rounded text-center">
                      <i class="fas fa-weight-hanging text-muted mb-1"></i>
                      <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Peso manipulado</small>
                      <strong>{{ ergonomiaDetalle.peso_manipulado_kg ?? '—' }} kg</strong>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-2 bg-light rounded text-center">
                      <i class="fas fa-ruler-vertical text-muted mb-1"></i>
                      <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Altura</small>
                      <strong>{{ ergonomiaDetalle.altura_trabajador_cm ?? '—' }} cm</strong>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-2 bg-light rounded text-center">
                      <i class="fas fa-redo text-muted mb-1"></i>
                      <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Frecuencia</small>
                      <strong>{{ textoErgonomia(ergonomiaDetalle.frecuencia_levantamiento) }}</strong>
                    </div>
                  </div>
                </div>

                <hr class="my-2">

                <!-- Factores observados -->
                <div class="mb-2">
                  <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">
                    <i class="fas fa-eye me-1"></i>Factores observados
                  </small>
                  <div class="mt-1">
                    <span v-if="factoresErgonomiaDetalle.length" class="badge bg-light text-dark border me-1 mb-1" v-for="(factor, idx) in factoresErgonomiaDetalle" :key="idx">
                      {{ factor }}
                    </span>
                    <span v-else class="text-muted">Ninguno</span>
                  </div>
                </div>

                <!-- Posturas corporales -->
                <div>
                  <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">
                    <i class="fas fa-walking me-1"></i>Posturas corporales
                  </small>
                  <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge" :class="textoErgonomia(ergonomiaDetalle.postura_cuello) === 'Flexion' ? 'bg-warning text-dark' : 'bg-light text-dark border'">
                      <i class="fas fa-head-side-virus me-1"></i>Cuello: {{ textoErgonomia(ergonomiaDetalle.postura_cuello) }}
                    </span>
                    <span class="badge" :class="textoErgonomia(ergonomiaDetalle.postura_hombros) === 'Neutral' ? 'bg-light text-dark border' : 'bg-warning text-dark'">
                      <i class="fas fa-arrows-alt-h me-1"></i>Hombros: {{ textoErgonomia(ergonomiaDetalle.postura_hombros) }}
                    </span>
                    <span class="badge" :class="textoErgonomia(ergonomiaDetalle.postura_muneca) === 'Neutral' ? 'bg-light text-dark border' : 'bg-warning text-dark'">
                      <i class="fas fa-hand-paper me-1"></i>Muñeca: {{ textoErgonomia(ergonomiaDetalle.postura_muneca) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- RESTRICCIONES -->
            <div v-if="restriccionesDetalleErgonomia" class="card border-0 shadow-sm mb-3 border-start border-4 border-warning bg-warning bg-opacity-10">
              <div class="card-body py-3">
                <h6 class="fw-bold text-warning mb-1">
                  <i class="fas fa-ban me-2"></i>Restricciones laborales
                </h6>
                <p class="mb-0 text-body" style="white-space: pre-line;">{{ restriccionesDetalleErgonomia }}</p>
              </div>
            </div>

            <!-- RECOMENDACIONES -->
            <div v-if="ergonomiaDetalle.acciones_recomendadas || ergonomiaDetalle.recomendaciones_puesto"
                class="card border-0 shadow-sm mb-3 border-start border-4 border-success">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-success-soft text-success me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-lightbulb"></i>
                </div>
                <h6 class="fw-bold mb-0 text-success">Recomendaciones</h6>
              </div>
              <div class="card-body small">
                <div v-if="ergonomiaDetalle.acciones_recomendadas" class="mb-3">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <div>
                      <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">General</small>
                      <p class="mb-0 mt-1" style="white-space: pre-line;">{{ ergonomiaDetalle.acciones_recomendadas }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="ergonomiaDetalle.recomendaciones_puesto">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-user-check text-success me-2 mt-1"></i>
                    <div>
                      <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Para el puesto</small>
                      <p class="mb-0 mt-1" style="white-space: pre-line;">{{ ergonomiaDetalle.recomendaciones_puesto }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- SEGUIMIENTO Y OBSERVACIONES -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-primary">
              <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                <div class="info-card-icon bg-primary-soft text-primary me-2" style="width: 32px; height: 32px;">
                  <i class="fas fa-clipboard-list"></i>
                </div>
                <h6 class="fw-bold mb-0 text-primary">Seguimiento y observaciones</h6>
              </div>
              <div class="card-body small">
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="fas fa-user-md text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Tipo de seguimiento</small>
                        <div>
                          <span class="badge bg-light text-dark border">
                            {{ datosErgonomiaDetalle.examenMedico?.tipo_seguimiento || '—' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-start">
                      <i class="far fa-clock text-muted me-2 mt-1"></i>
                      <div>
                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Plazo</small>
                        <div>{{ ergonomiaDetalle.plazo_proximo_seguimiento || '—' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div>
                  <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">
                    <i class="fas fa-comment-medical me-1"></i>Notas del ergónomo
                  </small>
                  <div class="p-2 bg-light rounded mt-1" style="white-space: pre-line;">
                    {{ ergonomiaDetalle.notas_ergonomo || '—' }}
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- FOOTER -->
          <div class="modal-footer bg-white border-top py-3">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" @click="cerrarModal('modalDetalleErgonomia')">
              <i class="fas fa-times me-1"></i> Cerrar
            </button>
            <div class="ms-auto d-flex gap-2">
              <a :href="`/ergonomia-imprimir/${ergonomiaDetalle.id}`" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
                <i class="fas fa-print me-1"></i> Imprimir
              </a>
              <a :href="urlEditarErgonomia(ergonomiaDetalle)" class="btn btn-warning rounded-pill px-4 shadow-sm">
                <i class="fas fa-pen-to-square me-1"></i> Editar
              </a>
            </div>
          </div>

        </div>
      </div>
    </div>
    <!-- AQUI TERMINA EL MODAL DETALLE DE ERGONOMIA-->
    
    <!-- MODAL DETALLE ALTO RIESGO -->
    <div class="modal fade" id="modalDetalleAltoRiesgo" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" v-if="trDetalle">

          <div class="modal-header bg-gradient-medical text-white border-0 py-3">
            <div class="d-flex align-items-center">
              <div class="icon-circle bg-white bg-opacity-25 me-3">
                <i class="fas fa-exclamation-triangle fs-4"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold mb-1">
                  Trabajo de Alto Riesgo
                  <span class="badge bg-white bg-opacity-25 text-white ms-2 align-middle">#{{ trDetalle.folio }}</span>
                </h5>
                <small class="opacity-75">
                  <i class="far fa-calendar-alt me-1"></i>{{ formatearFecha(fechaLocalTR(trDetalle.created_at)) }}
                  <span class="mx-1">•</span>
                  <i class="fas fa-briefcase me-1"></i>{{ trDetalle.puesto?.nombre || 'Sin puesto' }}
                </small>
              </div>
            </div>
            <span class="badge rounded-pill px-3 py-2 ms-auto me-3 shadow-sm" :class="colorAptitudTR(trDetalle.aptitud)">
              {{ trDetalle.aptitud || 'Sin aptitud' }}
            </span>
            <button type="button" class="btn-close btn-close-white" @click="cerrarModal('modalDetalleAltoRiesgo')" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body bg-light-subtle">

            <!-- Riesgos y diagnóstico -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom py-2">
                <h6 class="fw-bold mb-0 text-danger">Riesgos y diagnóstico</h6>
              </div>
              <div class="card-body small">
                <p class="mb-1"><strong>Riesgos evaluados:</strong> {{ listaTR(trDetalle.riesgos) }}</p>
                <p class="mb-1"><strong>Diagnóstico principal:</strong> {{ trDetalle.diagnostico_principal || 'N/D' }}</p>
                <p class="mb-0"><strong>Enfermedad profesional:</strong> {{ trDetalle.enfermedad_profesional ? 'Sí' : 'No' }}</p>
              </div>
            </div>

            <!-- Antecedentes -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom py-2">
                <h6 class="fw-bold mb-0 text-primary">Antecedentes de salud</h6>
              </div>
              <div class="card-body small">
                <span v-if="trActivos(trDetalle.antecedentes_salud).length">
                  <span v-for="a in trActivos(trDetalle.antecedentes_salud)" :key="a" class="badge rounded-pill bg-danger text-white me-1 mb-1">{{ a }}</span>
                </span>
                <span v-else class="text-muted">Sin antecedentes positivos registrados</span>
              </div>
            </div>

            <!-- Signos vitales -->
            <div class="row g-2 mb-3">
              <div class="col-6 col-md" v-for="v in signosTR(trDetalle)" :key="v.etiqueta">
                <div class="text-center p-2 border rounded-3 bg-white h-100">
                  <small class="text-muted d-block">{{ v.etiqueta }}</small>
                  <strong>{{ v.valor }}</strong>
                </div>
              </div>
            </div>

            <!-- Exploración y estudios -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom py-2">
                <h6 class="fw-bold mb-0 text-primary">Exploración y estudios</h6>
              </div>
              <div class="card-body small">
                <p class="mb-1"><strong>Exploración física:</strong> {{ textoTR(trDetalle.exploracion_fisica) }}</p>
                <p class="mb-1"><strong>ECG:</strong> {{ textoTR(trDetalle.ecg) }}</p>
                <p class="mb-1"><strong>Radiografía de tórax:</strong> {{ textoTR(trDetalle.rx_torax) }}</p>
                <p class="mb-0"><strong>Evaluación visual:</strong> {{ textoTR(trDetalle.evaluacion_visual) }}</p>
              </div>
            </div>

            <!-- Audiometría -->
            <div class="card border-0 shadow-sm mb-3 border-start border-4 border-danger">
              <div class="card-header bg-white border-bottom py-2">
                <h6 class="fw-bold mb-0 text-danger">Audiometría</h6>
              </div>
              <div class="card-body small">
                <div class="table-responsive mb-2">
                  <table class="table table-sm table-bordered text-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Hz</th>
                        <th v-for="f in frecuenciasTR" :key="'h' + f">{{ f }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th class="table-light">Derecho (dB)</th>
                        <td v-for="f in frecuenciasTR" :key="'d' + f">{{ trFrecuencia(trDetalle, 'derecho', f) }}</td>
                      </tr>
                      <tr>
                        <th class="table-light">Izquierdo (dB)</th>
                        <td v-for="f in frecuenciasTR" :key="'i' + f">{{ trFrecuencia(trDetalle, 'izquierdo', f) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p class="mb-1">
                  <strong>PTA:</strong> {{ ptaTR(trDetalle) }}
                  <span class="mx-1">·</span>
                  <strong>Clasificación:</strong> {{ trDetalle.audiometria?.clasificacion || 'N/D' }}
                  <span class="mx-1">·</span>
                  <strong>Tipo:</strong> {{ trDetalle.audiometria?.tipo_hipoacusia || 'N/D' }}
                </p>
                <p class="mb-0 text-body">{{ trDetalle.audiometria?.reporte || 'Sin reporte' }}</p>
              </div>
            </div>

            <!-- Espirometría -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom py-2">
                <h6 class="fw-bold mb-0 text-primary">Espirometría</h6>
              </div>
              <div class="card-body small">
                <p class="mb-0">{{ textoTR(trDetalle.espirometria) }}</p>
              </div>
            </div>

            <!-- Diagnósticos -->
            <div class="card border-0 shadow-sm mb-3">
              <div class="card-header bg-white border-bottom py-2">
                <h6 class="fw-bold mb-0 text-primary">Diagnósticos</h6>
              </div>
              <div class="card-body small">
                <ul class="mb-0" v-if="diagnosticosTR(trDetalle).length">
                  <li v-for="(dx, i) in diagnosticosTR(trDetalle)" :key="i">{{ textoTR(dx) }}</li>
                </ul>
                <span v-else class="text-muted">Sin diagnósticos adicionales</span>
              </div>
            </div>

            <!-- Observaciones -->
            <div class="card mb-3" style="border-left: 4px solid #f0ad4e;">
              <div class="card-body">
                <h6 class="font-weight-bold text-warning mb-2">Observaciones</h6>
                <p class="mb-3">{{ trDetalle.observaciones || '—' }}</p>

                <h6 class="font-weight-bold text-warning mb-2">Restricciones</h6>
                <ul v-if="restriccionesTR(trDetalle).length" class="mb-3 pl-3">
                  <li v-for="(r, i) in restriccionesTR(trDetalle)" :key="i">{{ r }}</li>
                </ul>
                <p v-else class="mb-3" :class="trDetalle.aptitud === 'Apto con restricciones' ? 'text-danger' : 'text-muted'">
                  {{ trDetalle.aptitud === 'Apto con restricciones' ? 'Pendiente: no hay restricciones registradas' : 'No aplica' }}
                </p>

                <h6 class="font-weight-bold text-warning mb-2">Recomendaciones</h6>
                <ul v-if="recomendacionesTR(trDetalle).length" class="mb-3 pl-3">
                  <li v-for="(r, i) in recomendacionesTR(trDetalle)" :key="i">{{ r }}</li>
                </ul>
                <p v-else class="mb-3 text-muted">Sin recomendaciones registradas</p>

                <h6 class="font-weight-bold text-warning mb-2">Próxima evaluación</h6>
                <p class="mb-0">{{ proximaTR(trDetalle) }}</p>
              </div>
            </div>

          </div>

          <div class="modal-footer bg-white border-top py-3">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" @click="cerrarModal('modalDetalleAltoRiesgo')">
              <i class="fas fa-times me-1"></i> Cerrar
            </button>
            <div class="ms-auto d-flex gap-2">
              <button type="button" class="btn btn-outline-primary rounded-pill px-4" @click="imprimirAltoRiesgo(trDetalle)">
                <i class="fas fa-print me-1"></i> Imprimir
              </button>
              <a :href="urlEditarAltoRiesgo(trDetalle)" class="btn btn-warning rounded-pill px-4 shadow-sm">
                <i class="fas fa-pen-to-square me-1"></i> Editar
              </a>
            </div>
          </div>

        </div>
      </div>
    </div>
    <!-- AQUI TERMINA EL MODAL DETALLE ALTO RIESGO -->
  </div>
</template>

<style scoped>

/* Card principal */
/* Card principal */
.espiro-card {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

/* Header */
.espiro-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e9ecef;
}

.espiro-fecha {
  font-size: 16px;
  font-weight: 700;
  color: #212529;
  margin-bottom: 6px;
}

.espiro-folio {
  font-size: 12px;
  color: #6c757d;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

.espiro-folio-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.espiro-folio-item i {
  font-size: 11px;
  color: #adb5bd;
}

.espiro-separador {
  color: #ced4da;
  margin: 0 2px;
}

/* Grid de métricas */
.espiro-metricas-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

/* Cada métrica */
.espiro-metrica {
  background: #f8f9fa;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  padding: 16px;
  transition: all 0.2s ease;
}

.espiro-metrica:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Estados de color */
.espiro-metrica.normal {
  border-color: #28a745;
  background: #f8f9fa;
}

.espiro-metrica.alerta {
  border-color: #ffc107;
  background: #fff8e1;
}

.espiro-metrica.peligro {
  border-color: #dc3545;
  background: #fff5f5;
}

.espiro-metrica-info {
  border-color: #6c757d;
  background: #f8f9fa;
}

/* Icono y label */
.espiro-metrica-icono {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.espiro-metrica-icono i {
  font-size: 18px;
  color: #495057;
}

.espiro-metrica.normal .espiro-metrica-icono i {
  color: #28a745;
}

.espiro-metrica.alerta .espiro-metrica-icono i {
  color: #856404;
}

.espiro-metrica.peligro .espiro-metrica-icono i {
  color: #dc3545;
}

.espiro-metrica-label {
  font-size: 11px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Valor */
.espiro-metrica-valor {
  font-size: 24px;
  font-weight: 700;
  color: #212529;
  margin-bottom: 4px;
}

.espiro-metrica-valor-sm {
  font-size: 20px;
}

/* Porcentaje */
.espiro-metrica-porcentaje {
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
}

/* Recomendaciones */
.espiro-recomendaciones {
  background: #f0f9ff;
  border-left: 4px solid #0d6efd;
  padding: 16px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 13px;
  color: #212529;
  line-height: 1.6;
  display: flex;
  align-items: flex-start;
}

.espiro-recomendaciones i {
  color: #0d6efd;
  font-size: 18px;
  margin-top: 2px;
  flex-shrink: 0;
}

.espiro-recomendaciones-texto {
  margin-left: 4px;
  white-space: pre-line;
}

.espiro-metrica-porcentaje.normal {
  color: #28a745;
}

.espiro-metrica-porcentaje.alerta {
  color: #856404;
}

.espiro-metrica-porcentaje.peligro {
  color: #dc3545;
}

/* Variante azul (Instrumento) */
.espiro-metrica.primaria { border-color: #0d6efd; }
.espiro-metrica.primaria .espiro-metrica-icono i { color: #0d6efd; }
.espiro-metrica-porcentaje.info { color: #0d6efd; }

/* Variante gris neutra (sin datos) */
.espiro-metrica.info { border-color: #6c757d; }
.espiro-metrica.info .espiro-metrica-icono i { color: #6c757d; }

.espiro-tendencia {
  font-size: 11px;
  margin-left: 6px;
}

/* LLN */
.espiro-metrica-lln {
  font-size: 11px;
  font-weight: 600;
  margin-top: 4px;
}

/* Interpretación */
.espiro-interpretacion {
  background: #e7f3ff;
  border-left: 4px solid #0d6efd;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 13px;
  color: #212529;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.espiro-interpretacion i {
  color: #0d6efd;
  font-size: 16px;
}

.espiro-interpretacion-texto {
  margin-left: 4px;
}

.espiro-separador-vertical {
  width: 1px;
  height: 16px;
  background: #0d6efd;
  opacity: 0.3;
  margin: 0 4px;
}

.espiro-calidad-badge {
  background: #0d6efd;
  color: white;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Diagnóstico */
.espiro-diagnostico {
  background: #fff9e6;
  border-left: 4px solid #ffc107;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 12px;
  color: #856404;
  line-height: 1.5;
  display: flex;
  align-items: flex-start;
}

.espiro-diagnostico i {
  color: #856404;
  font-size: 16px;
  margin-top: 2px;
  flex-shrink: 0;
}

.espiro-diagnostico-texto {
  margin-left: 4px;
}

/* Restricciones */
.espiro-restricciones {
  background: #fff5f5;
  border-left: 4px solid #dc3545;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 12px;
  color: #721c24;
  line-height: 1.5;
}

.espiro-restricciones i {
  color: #dc3545;
}

/* Acciones */
.espiro-acciones {
  display: flex;
  gap: 10px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e9ecef;
}

.tr-card {
  background: #fff;
  border: 1px solid #edf0f4;
  border-left: 4px solid #dc3545;
  border-radius: 14px;
  padding: 18px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.tr-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.tr-card-riesgos {
  font-size: 13px;
  margin-bottom: 12px;
}

.tr-card-metricas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 10px;
  margin-bottom: 12px;
}

.tr-metrica {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  font-size: 13px;
}

.tr-card-acciones {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px solid #e9ecef;
}

/* Responsive */
@media (max-width: 768px) {
  .espiro-metricas-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .espiro-metricas-grid {
    grid-template-columns: 1fr;
  }
}
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

    /* =========================================
   AUDIOLOGÍA - PROPUESTA A (CORREGIDA FINAL)
   ========================================= */
  .audio-card-v2 {
    background: white;
    border: 1px solid #e5e9f0;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    position: relative;
  }

  .audio-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #edf0f4;
  }

  .audio-header-left {
    flex: 1;
  }

  .audio-fecha {
    font-size: 16px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 6px;
  }

  .audio-folio {
    font-size: 12px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .audio-folio-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .audio-folio-item i {
    font-size: 12px;
    color: #adb5bd;
  }

  .audio-separador {
    color: #ced4da;
  }

  /* Badge de aptitud */
  .badge-aptitud {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }

  .badge-aptitud.apto {
    background: #d4edda;
    color: #155724;
  }

  .badge-aptitud.restricciones {
    background: #ffc107;
    color: #212529;
  }

  .badge-aptitud.no-apto {
    background: #dc3545;
    color: white;
  }

  /* Grid de métricas */
  .audio-metricas-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 20px;
  }

  .audio-metrica-card {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .audio-metrica-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  /* Estados de color */
  .audio-metrica-card.normal {
    border-color: #28a745;
    background: #f8f9fa;
  }

  .audio-metrica-card.alerta {
    border-color: #ffc107;
    background: #fff8e1;
  }

  .audio-metrica-card.peligro {
    border-color: #dc3545;
    background: #fff5f5;
  }

  .audio-metrica-card.info {
    border-color: #0d6efd;
    background: #e7f3ff;
  }

  .audio-metrica-card.riesgo {
    border-color: #dc3545;
    background: #fff5f5;
  }

  /* Icono pequeño */
  .audio-metrica-icono {
    flex-shrink: 0;
  }

  .audio-metrica-icono i {
    font-size: 20px;
    color: #6c757d;
  }

  .audio-metrica-card.normal .audio-metrica-icono i {
    color: #28a745;
  }

  .audio-metrica-card.alerta .audio-metrica-icono i {
    color: #856404;
  }

  .audio-metrica-card.peligro .audio-metrica-icono i {
    color: #dc3545;
  }

  .audio-metrica-card.info .audio-metrica-icono i {
    color: #0d6efd;
  }

  .audio-metrica-card.riesgo .audio-metrica-icono i {
    color: #dc3545;
  }

  .riesgo-subtexto {
    color: #dc3545 !important;
    font-weight: 600;
    font-size: 12px;
    margin-top: 4px;
  }

  /* Contenido de la métrica */
  .audio-metrica-content {
    flex: 1;
    min-width: 0;
  }

  /* Label */
  .audio-metrica-label {
    font-size: 9px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
  }

  /* Valor */
  .audio-metrica-valor {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 4px;
    line-height: 1.2;
  }

  .audio-metrica-valor.text-left {
    text-align: left;
  }

  /* Subtexto */
  .audio-metrica-subtexto {
    font-size: 11px;
    color: #6c757d;
    font-weight: 500;
  }

  .audio-metrica-subtexto.info-text {
    color: #0d6efd;
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
  }

  .audio-metrica-subtexto.riesgo-texto {
    color: #dc3545;
    font-weight: 600;
  }

  /* Texto de clasificación (NO badge) */
  .audio-metrica-texto {
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
    text-transform: uppercase;
  }

  .audio-metrica-texto.normal-texto {
    color: #28a745;
  }

  .audio-metrica-texto.alerta-texto {
    color: #856404;
  }

  .audio-metrica-texto.peligro-texto {
    color: #dc3545;
  }

  /* Valoración */
  .audio-valoracion-box {
    background: #e7f3ff;
    border-left: 4px solid #0d6efd;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  .audio-valoracion-icono {
    color: #0d6efd;
    font-size: 18px;
    flex-shrink: 0;
  }

  .audio-valoracion-content {
    font-size: 13px;
    color: #212529;
    line-height: 1.5;
  }

  /* Restricciones */
  .audio-restricciones-box {
    background: #fff5f5;
    border-left: 4px solid #dc3545;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  .audio-restricciones-icono {
    color: #dc3545;
    font-size: 18px;
    flex-shrink: 0;
  }

  .audio-restricciones-content {
    font-size: 13px;
    color: #721c24;
    line-height: 1.5;
  }

  /* Acciones */
  .audio-acciones {
    display: flex;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid #edf0f4;
  }

  .btn-audio {
    padding: 8px 20px;
    border-radius: 20px;
    border: 1.5px solid #dee2e6;
    background: white;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .btn-audio:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
  }

  .btn-audio-primary {
    border-color: #0d6efd;
    color: #0d6efd;
  }

  .btn-audio-primary:hover {
    background: #e7f3ff;
  }

  /* Soft backgrounds para iconos */
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }

/* Info card mejorado */
.info-card {
  background: white;
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: transform 0.2s;
}
.info-card:hover {
  transform: translateY(-2px);
}
.info-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}
.info-card-content {
  flex: 1;
}

/* Bordes semánticos */
.border-start-warning { border-left-color: #ffc107 !important; }
.border-start-primary { border-left-color: #0d6efd !important; }
.border-start-success { border-left-color: #198754 !important; }

  /* Responsive */
  @media (max-width: 1200px) {
    .audio-metricas-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 768px) {
    .audio-metricas-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .audio-card-header {
      flex-direction: column;
      gap: 12px;
    }
    
    .badge-aptitud {
      align-self: flex-start;
    }
  }

  @media (max-width: 480px) {
    .audio-metricas-grid {
      grid-template-columns: 1fr;
    }
    
    .audio-metrica-card {
      padding: 12px;
    }
    
    .audio-metrica-valor {
      font-size: 16px;
    }
    
    .audio-metrica-icono i {
      font-size: 18px;
    }
  }

  /* Estilos personalizados para el modal de valoración ocupacional */
  .info-card-modern {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .info-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
  }

  .info-card-icon-modern {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .vital-sign-box {
    transition: all 0.3s ease;
    border: 2px solid transparent;
  }

  .vital-sign-box:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
  }

  .risk-item {
    transition: all 0.3s ease;
  }

  .risk-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(5px);
  }

  .bg-gradient-medical {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
  .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
  .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
  .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
  .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }

  .text-primary { color: #0d6efd !important; }
  .text-success { color: #198754 !important; }
  .text-info { color: #0dcaf0 !important; }
  .text-danger { color: #dc3545 !important; }
  .text-warning { color: #ffc107 !important; }

  .modal-body {
    max-height: 70vh;
    overflow-y: auto;
  }

  .card-header {
    border-radius: 12px 12px 0 0 !important;
  }

  .card {
    border-radius: 12px !important;
  }

  .badge {
    font-size: 0.85rem;
  }

  /* Animaciones suaves */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .card {
    animation: fadeIn 0.3s ease;
  }

  /* Scrollbar personalizado */
  .modal-body::-webkit-scrollbar {
    width: 8px;
  }

  .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
  }

  .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
  }

  /* ============================================
   TARJETA MODERNA DE VALORACIÓN OCUPACIONAL
   ============================================ */

/* ============================================
   TARJETA VALORACIÓN OCUPACIONAL - ESTILO MÉDICO
   ============================================ */

.espiro-card-modern {
  background: #ffffff;
  border: 1px solid #e8ecf1;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.3s ease;
}

.espiro-card-modern:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transform: translateY(-1px);
}

/* Header */
.espiro-card-header-modern {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f0f4f8;
}

.espiro-fecha-modern {
  font-size: 1.05rem;
  color: #2c3e50;
  font-weight: 600;
}

.espiro-fecha-modern i {
  color: #7f8c8d;
}

.espiro-meta-info {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.espiro-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.espiro-meta-item i {
  color: #95a5a6;
  font-size: 0.8rem;
}

.espiro-meta-separator {
  color: #d5dbdb;
  margin: 0 0.25rem;
}

/* Badge dictamen */
.badge-dictamen {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge-dictamen.apto {
  background-color: #e8f8f5;
  color: #1e8449;
  border: 1px solid #a9dfbf;
}

.badge-dictamen.apto-restricciones {
  background-color: #fef9e7;
  color: #b7950b;
  border: 1px solid #f9e79f;
}

.badge-dictamen.no-apto {
  background-color: #fdedec;
  color: #c0392b;
  border: 1px solid #f5b7b1;
}

/* ============================================
   GRID DE MÉTRICAS - SIN FONDOS GRISES
   ============================================ */

.espiro-metricas-grid-modern {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
  .espiro-metricas-grid-modern {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .espiro-metricas-grid-modern {
    grid-template-columns: 1fr;
  }
}

/* Tarjeta de métrica - Fondo blanco limpio */
.metrica-card {
  background: #ffffff;
  border: 1.5px solid #e8ecf1;
  border-radius: 10px;
  padding: 1.25rem;
  transition: all 0.3s ease;
}

.metrica-card:hover {
  border-color: #aed6f1;
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.08);
  transform: translateY(-2px);
}

/* Tarjeta destacada - Dictamen con borde azul médico */
.metrica-card-dictamen {
  border: 2px solid #3498db;
  background: linear-gradient(135deg, #ffffff 0%, #ebf5fb 100%);
}

.metrica-card-dictamen:hover {
  border-color: #2980b9;
  box-shadow: 0 4px 16px rgba(52, 152, 219, 0.15);
}

.metrica-card-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 0.75rem;
}

/* Iconos con colores médicos suaves */
.metrica-icon-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.icon-blue {
  background-color: #ebf5fb;
  color: #3498db;
  border: 1px solid #aed6f1;
}

.icon-teal {
  background-color: #e8f8f5;
  color: #1abc9c;
  border: 1px solid #a3e4d7;
}

.icon-rose {
  background-color: #fdedec;
  color: #e74c3c;
  border: 1px solid #f5b7b1;
}

.icon-amber {
  background-color: #fef9e7;
  color: #f39c12;
  border: 1px solid #f9e79f;
}

.metrica-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #7f8c8d;
}

.metrica-card-body {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.metrica-valor {
  font-weight: 700;
  color: #2c3e50;
  line-height: 1.3;
}

.metrica-valor-lg {
  font-size: 1.2rem;
}

.metrica-subtexto {
  font-size: 0.8rem;
  color: #7f8c8d;
}

/* ============================================
   BANNERS - COLORES MÉDICOS SUAVES
   ============================================ */

.banner-modern {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 8px;
  margin-bottom: 0.875rem;
  border-left: 4px solid;
}

.banner-icon {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.banner-content {
  flex-grow: 1;
  font-size: 0.9rem;
  line-height: 1.6;
  color: #34495e;
}

.banner-content strong {
  margin-right: 0.5rem;
  color: #2c3e50;
}

/* Banner Diagnóstico - Ámbar suave */
.banner-warning {
  background-color: #fef9e7;
  border-left-color: #f39c12;
}

.banner-warning .banner-icon {
  background-color: #fdebd0;
  color: #d68910;
}

/* Banner Restricciones - Rojo suave */
.banner-danger {
  background-color: #fdedec;
  border-left-color: #e74c3c;
}

.banner-danger .banner-icon {
  background-color: #fadbd8;
  color: #cb4335;
}

/* Banner Recomendaciones - Azul médico */
.banner-primary {
  background-color: #ebf5fb;
  border-left-color: #3498db;
}

.banner-primary .banner-icon {
  background-color: #d6eaf8;
  color: #2980b9;
}

/* ============================================
   ACCIONES
   ============================================ */

.espiro-acciones-modern {
  display: flex;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #f0f4f8;
  margin-top: 0.5rem;
}

.espiro-acciones-modern .btn {
  font-size: 0.875rem;
  padding: 0.5rem 1.25rem;
  transition: all 0.2s ease;
}

.espiro-acciones-modern .btn:hover {
  transform: translateY(-1px);
}

/* ============================================
   TARJETA VALORACIÓN OCUPACIONAL - CON FONDOS SUAVES
   ============================================ */

.val-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

/* Header */
.val-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f3f4f6;
  gap: 1rem;
}

.val-fecha {
  font-size: 1.1rem;
  color: #1f2937;
}

.val-fecha strong {
  font-weight: 700;
}

.val-fecha i {
  color: #6b7280;
}

.val-meta-info {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  font-size: 0.85rem;
  color: #6b7280;
}

.val-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.val-meta-item i {
  color: #9ca3af;
  font-size: 0.8rem;
}

.val-meta-separator {
  color: #d1d5db;
  margin: 0 0.25rem;
}

/* Badge dictamen */
.val-badge-dictamen {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
}

.val-badge-dictamen.apto {
  background-color: #dcfce7;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.val-badge-dictamen.apto-restricciones {
  background-color: #fef08a;
  color: #854d0e;
  border: 1px solid #fde047;
}

.val-badge-dictamen.no-apto {
  background-color: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* ============================================
   GRID DE MÉTRICAS CON FONDOS DE COLOR SUAVE
   ============================================ */

.val-metricas-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
  .val-metricas-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .val-metricas-grid {
    grid-template-columns: 1fr;
  }
}

/* Tarjeta base */
.val-metrica {
  border: 2px solid;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  transition: all 0.2s ease;
}

.val-metrica:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

/* Dictamen - Azul suave */
.val-metrica-blue {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.val-metrica-blue .val-metrica-label {
  color: #1e40af;
}

.val-metrica-blue .val-metrica-label i {
  color: #3b82f6;
}

/* Próximo examen - Teal suave */
.val-metrica-teal {
  border-color: #14b8a6;
  background-color: #f0fdfa;
}

.val-metrica-teal .val-metrica-label {
  color: #115e59;
}

.val-metrica-teal .val-metrica-label i {
  color: #14b8a6;
}

/* Presión arterial - Rosa/Rojo suave */
.val-metrica-rose {
  border-color: #f43f5e;
  background-color: #fff1f2;
}

.val-metrica-rose .val-metrica-label {
  color: #881337;
}

.val-metrica-rose .val-metrica-label i {
  color: #f43f5e;
}

/* IMC - Ámbar suave */
.val-metrica-amber {
  border-color: #f59e0b;
  background-color: #fffbeb;
}

.val-metrica-amber .val-metrica-label {
  color: #92400e;
}

.val-metrica-amber .val-metrica-label i {
  color: #f59e0b;
}

/* Label de métrica */
.val-metrica-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.val-metrica-label i {
  font-size: 1rem;
}

/* Valor de métrica */
.val-metrica-valor {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1.3;
}

.val-metrica-subtexto {
  font-size: 0.8rem;
  color: #6b7280;
  display: block;
  margin-top: 0.25rem;
}

/* ============================================
   BANNERS CON FONDOS SUAVES
   ============================================ */

.val-banner {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 10px;
  margin-bottom: 1rem;
  border-left: 4px solid;
}

.val-banner-icon {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.val-banner-content {
  flex-grow: 1;
  font-size: 0.9rem;
  line-height: 1.6;
  color: #374151;
}

.val-banner-content strong {
  margin-right: 0.5rem;
  color: #1f2937;
}

/* Banner Diagnóstico - Ámbar */
.val-banner-warning {
  background-color: #fffbeb;
  border-left-color: #f59e0b;
}

.val-banner-warning .val-banner-icon {
  background-color: #fef3c7;
  color: #d97706;
}

/* Banner Restricciones - Rojo */
.val-banner-danger {
  background-color: #fff1f2;
  border-left-color: #f43f5e;
}

.val-banner-danger .val-banner-icon {
  background-color: #ffe4e6;
  color: #e11d48;
}

/* Banner Recomendaciones/Plan - Azul */
.val-banner-primary {
  background-color: #eff6ff;
  border-left-color: #3b82f6;
}

.val-banner-primary .val-banner-icon {
  background-color: #dbeafe;
  color: #2563eb;
}

/* ============================================
   ACCIONES
   ============================================ */

.val-acciones {
  display: flex;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #f3f4f6;
  margin-top: 0.5rem;
}

.val-acciones .btn {
  font-size: 0.875rem;
  padding: 0.5rem 1.25rem;
  transition: all 0.2s ease;
}

.val-acciones .btn:hover {
  transform: translateY(-1px);
}

.val-badge-dictamen {
  text-transform: capitalize;
}

</style>

<script>
import ApiServiceClinicaTrabajo from '@/services/ApiServiceClinicaTrabajo'
import Swal from 'sweetalert2'

//Declaracion de las variables para SweetAlert//
const escapar = (texto) => String(texto ?? '')
  .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

const notificar = (icon, title, texto = '') => Swal.fire({
  icon,
  title,
  html: escapar(texto).replace(/\n/g, '<br>'),
  confirmButtonText: 'Entendido',
  confirmButtonColor: '#1E88E5'
})

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
      ergonomiaDetalle: null,
      trDetalle: null,
      modalOcupacional: null,

      infoValoracionesErgonomicas: [],
      audiologiaDetalle: null,
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
    },
    hoja4Detalle() {
      return this.audiologiaDetalle?.datos?.hoja4 || {}
    },
    tarjetasDetalleAudiologia() {
      const d = this.audiologiaDetalle
      if (!d) return []
      return [
        { etiqueta: 'Riesgo auditivo', valor: d.riesgo_auditivo || 'No registrado', icono: 'fas fa-volume-up', color: 'warning' },
        { etiqueta: 'EPP auditivo', valor: d.epp_auditivo || 'No registrado', icono: 'fas fa-headphones', color: 'info' },
        { etiqueta: 'Antigüedad en la empresa', valor: d.anos_empresa != null ? `${d.anos_empresa} años` : 'No registrado', icono: 'fas fa-building', color: 'success' },
        { etiqueta: 'PTA promedio', valor: d.pta_promedio != null ? `${d.pta_promedio} dB · ${d.clasificacion || 'N/D'}` : 'N/D', icono: 'fas fa-chart-line', color: 'primary' }
      ]
    },
    datosErgonomiaDetalle() {
      const d = this.ergonomiaDetalle?.datos_json
      if (!d) return {}
      return typeof d === 'string' ? JSON.parse(d) : d
    },
    regionesErgonomiaDetalle() {
      const h = this.datosErgonomiaDetalle?.examenMedico?.hallazgos || {}
      return Object.keys(h).filter(k => h[k] === true)
    },
    horaErgonomiaDetalle() {
      return (this.ergonomiaDetalle?.hora_valoracion || '').slice(0, 5)
    },
    factoresErgonomiaDetalle() {
      const etiquetas = {
        posturas_forzadas: 'Posturas forzadas',
        movimientos_repetitivos: 'Movimientos repetitivos',
        manipulacion_cargas: 'Manipulación de cargas',
        cargas_maximas_manipuladas: 'Cargas máximas manipuladas',
        exposicion_calor: 'Exposición al calor',
        exposicion_vibraciones: 'Exposición a vibraciones',
        iluminacion_insuficiente: 'Iluminación insuficiente'
      }
      return Object.entries(etiquetas)
        .filter(([campo]) => !!this.ergonomiaDetalle?.[campo])
        .map(([, texto]) => texto)
    },
    restriccionesDetalleErgonomia() {
      const r = (this.ergonomiaDetalle?.restricciones_laborales || '').trim()
      return r && r.toLowerCase() !== 'ninguna' ? r : ''
    },
    frecuenciasTR() {
      return ['125', '250', '500', '1000', '2000', '3000', '4000', '6000', '8000']
    },
  },

  methods: {
    // --- ORQUESTADOR DE CARGA DE DATOS ---
    async cargarDatosLaborales() {
      try {
        await this.obtenerArchivos()
        //await this.obtenerExpedienteClinico()
        await this.obtenerEspirometrias()
        await this.obtenerAudiologiaHistoria()
        await this.obtenerErgonomicas()
        await this.obtenerValoracionesOcupacionales()
        await this.obtenerAudiologiaAltoRiesgo()
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
      if (val.fev1_fvc_ratio == null || val.fev1_fvc_lln == null) return { texto: '', clase: 'audio-clas-na' }
      if (ratio < lln) return { texto: '· Bajo LLN', clase: 'audio-clas-grave' }
      if (ratio - lln < 1) return { texto: '· Limítrofe (LLN ' + lln + ')', clase: 'audio-clas-leve' }
      return { texto: '· Normal (LLN ' + lln + ')', clase: 'audio-clas-normal' }
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
        clase: delta <= -5 ? 'audio-cambio-malo' : delta < 0 ? 'audio-cambio-leve' : 'audio-cambio-bueno'
      }
    },

    async abrirDetalleEspirometria(val) {
      this.espirometriaDetalle = val
      await this.$nextTick()
      this.abrirModal('modalDetalleEspirometria')
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
    // Nuevos métodos para el diseño
    getClaseMetrica(porcentaje) {
      if (!porcentaje) return '';
      if (porcentaje >= 80) return 'normal';
      if (porcentaje >= 70) return 'alerta';
      return 'peligro';
    },

    getClaseMetricaRatio(ratio) {
      if (!ratio) return '';
      // Ajusta estos valores según tus criterios clínicos
      if (ratio >= 75) return 'normal';
      if (ratio >= 70) return 'alerta';
      return 'peligro';
    },

    getClaseTexto(porcentaje) {
      if (!porcentaje) return '';
      if (porcentaje >= 80) return 'normal';
      if (porcentaje >= 70) return 'alerta';
      return 'peligro';
    },
    //Aqui terminan los metodos para ver los detalles de Espirometria//

    //Metodo para traer los datos del Historial la ficha Audiologia Historia Clinica//
    async obtenerAudiologiaHistoria() {
      try {
        const response = await ApiServiceClinicaTrabajo.get('/DelliAudiologia', {
          params: { paciente_id: this.pacienteId }
        })

         console.log('📡 Datos Historia Clinica Auidiologia:', response)
        this.infoAudiologiaHistoria = (response.data.data || []).map(f => {
          const h4 = f.audiometria || {}
          return {
            ...f,
            pta_der: h4.pta_der ?? null,
            pta_izq: h4.pta_izq ?? null,
            clasificacion_der: h4.clasificacion_der ?? null,
            clasificacion_izq: h4.clasificacion_izq ?? null,
            tipo_hipoacusia: h4.tipo_hipoacusia ?? null,
            valoracion: h4.valoracion ?? null,
            restricciones: h4.restricciones ?? null,
            recomendaciones: Array.isArray(h4.recomendaciones) ? h4.recomendaciones : []
          }
        })
      } catch (error) {
        console.error('Error al obtener historia clínica audiológica:', error)
      }
    },

    colorAptitudAudiologia(aptitud) {
      if (aptitud === 'Apto') return 'bg-success'
      if (aptitud === 'Apto con restricciones') return 'bg-warning text-dark'
      return 'bg-danger'
    },

    colorClasificacionAudiologia(clasificacion) {
      if (clasificacion === 'NORMAL') return 'audio-clas-normal'
      if (clasificacion === 'HIPOACUSIA LEVE') return 'audio-clas-leve'
      if (clasificacion) return 'audio-clas-grave'
      return 'audio-clas-na'
    },

    tendenciaAudiologia(i) {
      const actual = this.infoAudiologiaHistoria[i]
      const anterior = this.infoAudiologiaHistoria[i + 1]
      if (!anterior || actual.pta_promedio == null || anterior.pta_promedio == null) return null

      const delta = Math.round(Number(actual.pta_promedio) - Number(anterior.pta_promedio))
      const flecha = delta > 0 ? '↑' : delta < 0 ? '↓' : '='
      return {
        texto: `${flecha} ${Math.abs(delta)} dB vs. anterior`,
        clase: delta >= 10 ? 'audio-cambio-malo' : delta > 0 ? 'audio-cambio-leve' : 'audio-cambio-bueno'
      }
    },

    // Determina si el EPP es obligatorio basado en el riesgo auditivo
    esEppObligatorio(riesgoAuditivo) {
      if (!riesgoAuditivo) return false
      const riesgo = riesgoAuditivo.toLowerCase()
      return riesgo.includes('alto') || riesgo.includes('muy alto')
    },
    
    dB(valor) {
      return valor == null || valor === '' ? 'N/D' : Math.round(Number(valor)) + ' dB'
    },

    // Nuevos métodos para el diseño de audiología
    getClaseRiesgoAuditivo(riesgo) {
      if (!riesgo) return 'info';
      const riesgoLower = riesgo.toLowerCase();
      if (riesgoLower.includes('muy alto') || riesgoLower.includes('alto')) return 'riesgo';
      if (riesgoLower.includes('medio')) return 'alerta';
      return 'normal';
    },
    
    // Métodos para audiología//
    getBadgeAptitudClass(aptitud) {
      if (aptitud === 'Apto') return 'apto';
      if (aptitud === 'Apto con restricciones') return 'restricciones';
      return 'no-apto';
    },
    getClasePTA(pta) {
      if (!pta) return 'normal';
      const valor = Number(pta);
      if (valor >= 40) return 'peligro';
      if (valor >= 25) return 'alerta';
      return 'normal';
    },

    getClasePTAPromedio(pta) {
      if (!pta) return 'normal';
      const valor = Number(pta);
      if (valor >= 40) return 'peligro';
      if (valor >= 25) return 'alerta';
      return 'normal';
    },

    getBadgeClasificacionClass(clasificacion) {
      if (!clasificacion) return 'normal';
      const clasifLower = clasificacion.toLowerCase();
      if (clasifLower === 'normal') return 'normal';
      if (clasifLower.includes('leve')) return 'alerta';
      return 'peligro';
    },

    getBadgeBootstrapClass(clasificacion) {
      if (!clasificacion) return 'bg-secondary text-white';
      
      const clasifLower = clasificacion.toLowerCase();
      
      if (clasifLower === 'normal') return 'bg-success text-white';
      if (clasifLower.includes('leve')) return 'bg-warning text-dark';
      if (clasifLower.includes('moderada')) return 'bg-warning text-dark';
      if (clasifLower.includes('severa')) return 'bg-danger text-white';
      if (clasifLower.includes('profunda')) return 'bg-danger text-white';
      
      return 'bg-secondary text-white';
    },

    getTextoClasificacionClass(clasificacion) {
      if (!clasificacion) return ''
      
      const clasifLower = clasificacion.toLowerCase()
      
      // ✅ NORMAL → Verde
      if (clasifLower === 'normal') return 'normal-texto'
      
      // ✅ LEVE y MODERADA → Amarillo/Marrón (alerta)
      if (clasifLower.includes('leve') || clasifLower.includes('moderada')) {
        return 'alerta-texto'
      }
      
      // ✅ SEVERA y PROFUNDA → Rojo (peligro)
      if (clasifLower.includes('severa') || clasifLower.includes('profunda')) {
        return 'peligro-texto'
      }
      
      return ''
    },

    // Obtiene el texto descriptivo de la clasificación del PTA promedio
    getTextoClasificacionPromedio(clasificacion) {
      if (!clasificacion) return 'Sin datos'
      
      const clasifLower = clasificacion.toLowerCase()
      
      if (clasifLower === 'normal') return 'Normal'
      if (clasifLower.includes('leve')) return 'Leve alteración'
      if (clasifLower.includes('moderada')) return 'Alteración moderada'
      if (clasifLower.includes('severa')) return 'Alteración severa'
      if (clasifLower.includes('profunda')) return 'Alteración profunda'
      
      return clasificacion
    },

    // Separa "Muy Alto (>85 dB)" en { titulo: "Muy Alto", subtitulo: ">85 dB" }
    separarRiesgoAuditivo(texto) {
      if (!texto) return { titulo: 'N/D', subtitulo: null }
      
      // Busca el paréntesis de apertura
      const match = texto.match(/^(.+?)\s*\((.+)\)$/)
      
      if (match) {
        return {
          titulo: match[1].trim(),      // "Muy Alto"
          subtitulo: match[2].trim()    // ">85 dB"
        }
      }
      
      // Si no hay paréntesis, devuelve todo como título
      return { titulo: texto, subtitulo: null }
    },

    

    async abrirDetalleAudiologia(val) {
      try {
        const { data } = await ApiServiceClinicaTrabajo.get(`/DelliAudiologia/${val.id}`)
        this.audiologiaDetalle = { ...data.data }
        await this.$nextTick()
        this.abrirModal('modalDetalleAudiologia')
      } catch (error) {
        await notificar('error', 'Error al cargar la evaluación', error.response?.data?.message || error.message)
      }
    },

    //Aqui terminan los metodos para ver los detalles de Audiologia Historia Clinica//

    imprimirAudiologia(val) {
      window.open(`/DelliAudiologia-imprimir/${val.id}`, '_blank')
    },

    urlEditarAudiologia(val) {
      return `/FichaDelliAudiologia/${val.paciente_id}?ficha=${val.id}`
    },

    //Metodos para mostrar los datos del resumen y detalles de la ficha Ocupacional//
    claseDictamen(dictamen) {
      const clases = {
        apto: 'bg-success',
        apto_con_restricciones: 'bg-warning text-dark',
        temporalmente_no_apto: 'bg-danger',
        no_apto: 'bg-danger'
      }
      return clases[dictamen] || 'bg-secondary'
    },

    etiquetaDictamen(dictamen) {
      if (!dictamen) return 'Pendiente';
      const texto = dictamen.replace(/_/g, ' ');
      return texto.charAt(0).toUpperCase() + texto.slice(1);
    },
    
    vigenciaVencida(fecha) {
      return !!fecha && new Date(fecha) < new Date()
    },

    diasParaVencer(fecha) {
      if (!fecha) return null
      const hoy = new Date()
      hoy.setHours(0, 0, 0, 0)
      const limite = new Date(String(fecha).split('T')[0] + 'T00:00:00')
      return Math.round((limite - hoy) / 86400000)
    },

    textoVigencia(fecha) {
      const dias = this.diasParaVencer(fecha)
      if (dias === null) return 'Definir vigencia'
      if (dias < 0) return `Vencido hace ${Math.abs(dias)} días`
      if (dias === 0) return 'Vence hoy'
      return `En ${dias} días`
    },

    claseVigencia(fecha) {
      const dias = this.diasParaVencer(fecha)
      if (dias === null) return 'text-muted'
      if (dias < 0) return 'text-danger'
      if (dias <= 30) return 'text-warning'
      return ''
    },

    clasificarPresionOcupacional(sistolica, diastolica) {
      if (sistolica == null || diastolica == null) return { texto: 'Sin dato', clase: 'text-muted' }
      if (sistolica >= 140 || diastolica >= 90) return { texto: 'Etapa 2', clase: 'text-danger' }
      if (sistolica >= 130 || diastolica >= 80) return { texto: 'Etapa 1', clase: 'text-warning' }
      if (sistolica >= 120) return { texto: 'Elevada', clase: 'text-warning' }
      return { texto: 'Normal', clase: 'text-success' }
    },

    claseImc(imc) {
      if (imc == null) return 'text-muted'
      if (imc >= 30) return 'text-danger'
      if (imc >= 25 || imc < 18.5) return 'text-warning'
      return 'text-success'
    },

    imprimirOcupacional(val) {
      window.open(`/FichaMedicinaOcupacional-imprimir/${val.id}`, '_blank')
    },

    urlEditarOcupacional(val) {
      return `/ficha-ocupacional/${val.paciente_id}?valoracion=${val.id}`
    },

    textoRiesgos(lista = [], otros = null) {
      const partes = [...lista]
      if (otros) partes.push(`Otros: ${otros}`)
      return partes.length ? partes.join(', ') : 'Ninguno'
    },

    async obtenerValoracionesOcupacionales() {
      try {
        const response = await ApiServiceClinicaTrabajo.get('/FichaMedicinaOcupacional', {
          params: { paciente_id: this.pacienteId }
        })
        this.infoValoracionesOcupacionales = response.data.data || []
      } catch (error) {
        console.error('Error al cargar valoraciones ocupacionales:', error)
        this.infoValoracionesOcupacionales = []
      }
    },

    //Aqui termina el metodo para mostrar los datos del resumen y detalles de la ficha Ocupacional//

    async obtenerErgonomicas() {
      try {
        const response = await ApiServiceClinicaTrabajo.get('/ergonomia', {
          params: { paciente_id: this.pacienteId }
        })
        this.infoValoracionesErgonomicas = response.data.data || []
      } catch (error) {
        console.error('Error al cargar valoraciones ergonómicas:', error)
        this.infoValoracionesErgonomicas = []
      }
    },
    puntuacionErgonomia(val) {
      if (val.instrumento_utilizado === 'RULA') return val.rula_puntuacion ?? '—'
      if (val.instrumento_utilizado === 'REBA') return val.reba_puntuacion ?? '—'
      return '—'
    },
    nivelErgonomia(val) {
      if (val.instrumento_utilizado === 'RULA') return val.rula_nivel_riesgo || '—'
      if (val.instrumento_utilizado === 'REBA') return val.reba_nivel_accion || '—'
      return '—'
    },
    colorNivelErgonomia(val) {
      const nivel = this.nivelErgonomia(val)
      if (['negligible', 'bajo', 'aceptable'].includes(nivel)) return 'success'
      if (['moderado', 'investigar'].includes(nivel)) return 'warning'
      if (nivel === '—') return 'secondary'
      return 'danger'
    },
    colorAptitudErgonomia(val) {
      const a = (val.aptitud || '').toLowerCase()
      if (a === 'apto') return 'success'
      if (a.includes('restricciones') || a.includes('condicionado')) return 'warning'
      if (a.includes('no apto')) return 'danger'
      return 'secondary'
    },
    // Mapea el color Bootstrap del nivel de riesgo a las clases de estado de espiro-metrica
    claseErgo(val) {
      const mapa = { success: 'normal', warning: 'alerta', danger: 'peligro', secondary: 'info' }
      return mapa[this.colorNivelErgonomia(val)] || 'info'
    },
    async abrirDetalleErgonomia(val) {
      try {
        const response = await ApiServiceClinicaTrabajo.get(`/ergonomia/${val.id}`)
        this.ergonomiaDetalle = response.data.data || response.data
      } catch (error) {
        console.error('Error al cargar detalle ergonómico:', error)
        this.ergonomiaDetalle = val
      }
      this.$nextTick(() => {
        const el = document.getElementById('modalDetalleErgonomia')
        if (window.jQuery && window.jQuery.fn.modal) {
          window.jQuery(el).modal('show')
        } else if (window.bootstrap?.Modal) {
          new window.bootstrap.Modal(el).show()
        } else {
          console.error('No se encontró Bootstrap ni jQuery para abrir el modal')
        }
      })
    },
    areasErgonomiaDetalle(val) {
      if (!val) return '—'
      if (val.instrumento_utilizado === 'RULA') return val.rula_areas_evaluadas || '—'
      if (val.instrumento_utilizado === 'REBA') return val.reba_areas_evaluadas || '—'
      if (val.instrumento_utilizado === 'NIOSH') return val.niosh_variables_evaluadas || '—'
      return '—'
    },
    textoErgonomia(valor) {
      if (!valor) return '—'
      const texto = valor.replace(/_/g, ' ')
      return texto.charAt(0).toUpperCase() + texto.slice(1)
    },
    urlEditarErgonomia(val) {
      return `/ficha-ergonomia/${val.paciente_id}?valoracion=${val.id}`
    },

    // --- AUDIOLOGÍA ALTO RIESGO (TRABAJO DE ALTO RIESGO) ---
    async obtenerAudiologiaAltoRiesgo() {
      try {
        const response = await ApiServiceClinicaTrabajo.get('/AudiologiaTrabajoAltoRiesgo', {
          params: { paciente_id: this.pacienteId }
        })
        // Soporta respuesta paginada (data.data.data) y lista directa (data.data)
        const lista = response.data.data?.data ?? response.data.data ?? []
        this.infoAudiologiaAltoRiesgo = lista
          .filter(f => String(f.paciente_id) === String(this.pacienteId))
          .map(f => ({
            ...f,
            fecha: this.fechaLocalTR(f.created_at),
            puesto_nombre: f.puesto?.nombre || 'No especificado',
            riesgos_texto: this.listaTR(f.riesgos) !== 'N/D' ? this.listaTR(f.riesgos) : (f.tipo_trabajo_riesgo || 'N/D')
          }))
      } catch (error) {
        console.error('Error al obtener exámenes de alto riesgo:', error)
        this.infoAudiologiaAltoRiesgo = []
      }
    },

    async abrirDetalleAltoRiesgo(val) {
      try {
        const response = await ApiServiceClinicaTrabajo.get(`/AudiologiaTrabajoAltoRiesgo/${val.id}`)
        this.trDetalle = response.data.data
      } catch (error) {
        console.error('Error al cargar el detalle TR:', error)
        this.trDetalle = val
      }
      await this.$nextTick()
      this.abrirModal('modalDetalleAltoRiesgo')
    },

    imprimirAltoRiesgo(val) {
      window.open(`/AudiologiaTrabajoAltoRiesgo-imprimir/${val.id}`, '_blank')
    },

    urlEditarAltoRiesgo(val) {
      return `/AudiologiaTrabajoAltoRiesgo/${val.paciente_id}?ficha=${val.id}`
    },

    colorAptitudTR(aptitud) {
      if (aptitud === 'Apto') return 'bg-success'
      if (aptitud === 'Apto con restricciones') return 'bg-warning text-dark'
      return 'bg-danger'
    },

    // Convierte valores anidados en texto legible, sin [object Object]
    textoTR(valor) {
      if (valor == null || valor === '' || valor === 'N/D') return 'N/D'
      if (typeof valor === 'boolean') return valor ? 'Sí' : 'No'
      if (typeof valor !== 'object') return String(valor)
      return Object.entries(valor)
        .filter(([, v]) => v != null && v !== '' && v !== 'N/D')
        .map(([k, v]) => {
          const etiqueta = k.replace(/_/g, ' ')
          return `${etiqueta}: ${typeof v === 'object' ? '(' + this.textoTR(v) + ')' : (v === true ? 'Sí' : v === false ? 'No' : v)}`
        })
        .join(' · ')
    },

    // Fecha local de México a partir de created_at (UTC)
    fechaLocalTR(iso) {
      if (!iso) return ''
      return new Date(iso).toLocaleDateString('en-CA', { timeZone: 'America/Mexico_City' })
    },

    // Riesgos legibles: "espacio_confinado" → "espacio confinado"
    listaTR(valor) {
      if (Array.isArray(valor)) return valor.length ? valor.map(r => String(r).replace(/_/g, ' ')).join(', ') : 'N/D'
      return valor ? String(valor).replace(/_/g, ' ') : 'N/D'
    },

    // Solo las claves marcadas como true, por ejemplo antecedentes de salud
    trActivos(obj) {
      if (!obj || typeof obj !== 'object') return []
      return Object.entries(obj)
        .filter(([, v]) => v === true || v === 'true')
        .map(([k]) => k.replace(/_/g, ' '))
    },

    signosTR(d) {
      if (!d) return []
      return [
        { etiqueta: 'Peso', valor: d.peso ? d.peso + ' kg' : 'N/D' },
        { etiqueta: 'Estatura', valor: d.estatura ? d.estatura + ' m' : 'N/D' },
        { etiqueta: 'IMC', valor: d.imc || 'N/D' },
        { etiqueta: 'Presión', valor: d.presion_arterial ? d.presion_arterial + ' mmHg' : 'N/D' },
        { etiqueta: 'F. cardiaca', valor: d.frecuencia_cardiaca ? d.frecuencia_cardiaca + ' lpm' : 'N/D' }
      ]
    },

    diagnosticosTR(d) {
      return (d?.diagnosticos || []).filter(x => x && (typeof x === 'string' ? x.trim() : Object.keys(x).length))
    },

    ptaTR(val) {
      const a = val.audiometria || {}
      if (a.pta_der == null && a.pta_izq == null) return 'N/D'
      return `D ${a.pta_der ?? 'N/D'} dB · I ${a.pta_izq ?? 'N/D'} dB`
    },
    
    restriccionesTR(d) {
      const r = d?.datos?.hoja4?.restricciones
      if (Array.isArray(r)) return r.filter(Boolean)
      if (!r) return []
      return String(r).split(/\r?\n/).map(s => s.trim()).filter(Boolean)
    },
    recomendacionesTR(d) {
      const r = d?.datos?.hoja4?.recomendaciones
      return Array.isArray(r) ? r.filter(Boolean) : []
    },
    proximaTR(d) {
      return d?.datos?.hoja4?.proxima_evaluacion || '—'
    },
    trFrecuencia(d, oido, f) {
      const v = d?.audiometria?.frecuencias?.[oido]?.[f]
      return v == null ? 'N/D' : v
    },

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
      
     this.abrirModal('modalArchivo')
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
    verDetalleValoracion(val) {
      this.modalOcupacional = val
      this.$nextTick(() => this.abrirModal('modalDetalleOcupacional'))
    },

    cerrarDetalleOcupacional() {
      this.cerrarModal('modalDetalleOcupacional')
    },
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
    },
    abrirModal(id) {
      window.$('#' + id).modal('show')
    },

    cerrarModal(id) {
      window.$('#' + id).modal('hide')
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