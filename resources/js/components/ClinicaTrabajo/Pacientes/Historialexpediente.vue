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
    }
  },

  methods: {
    // --- ORQUESTADOR DE CARGA DE DATOS ---
    async cargarDatosLaborales() {
      try {
        await this.obtenerArchivos()
        //await this.obtenerExpedienteClinico()
        await this.obtenerEspirometrias()
        await this.obtenerAudiologiaHistoria()
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