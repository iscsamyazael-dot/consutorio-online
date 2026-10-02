<template>
  <div class="hoja4-paraclinicodiagnosticos-tr">
    <!-- SECTION: EXÁMENES PARACLÍNICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-flask mr-2"></i> EXÁMENES PARACLÍNICOS
        </h5>
      </div>
      <div class="card-body">
        <!-- ELECTROCARDIOGRAMA -->
        <div class="row mb-4 pb-3 border-bottom">
          <div class="col-md-12">
            <h6 class="font-weight-bold">
              <i class="fas fa-heartbeat mr-2 text-danger"></i> ELECTROCARDIOGRAMA (ECG)
            </h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="ecg_realizado" value="true" v-model="localData.ecg.realizado" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="ecg_realizado" value="false" v-model="localData.ecg.realizado" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div v-if="localData.ecg.realizado === 'true'" class="col-md-10">
            <div class="row">
              <div class="col-md-3">
                <label class="font-weight-bold">Fecha</label>
                <input type="date" class="form-control" v-model="localData.ecg.fecha" @change="emitChange" style="height: 38px;">
              </div>
              <div class="col-md-3">
                <label class="font-weight-bold">Resultado</label>
                <select class="form-control" v-model="localData.ecg.resultado" @change="emitChange" style="height: 38px;">
                  <option value="Normal">Normal</option>
                  <option value="Anormal">Anormal</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="font-weight-bold">F.C. (por min)</label>
                <input type="number" class="form-control" v-model.number="localData.ecg.frecuencia_cardiaca" @change="emitChange" placeholder="Ej: 72" style="height: 38px;">
              </div>
              <div class="col-md-3">
                <label class="font-weight-bold">Ritmo</label>
                <input type="text" class="form-control" v-model="localData.ecg.ritmo" @change="emitChange" placeholder="Ej: Sinusal" style="height: 38px;">
              </div>
              <div class="col-md-12 mt-2">
                <label class="font-weight-bold">Reporte</label>
                <textarea class="form-control" v-model="localData.ecg.reporte" @change="emitChange" placeholder="Hallazgos ECG" rows="2" style="font-size: 12px;"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- RADIOGRAFÍA TÓRAX -->
        <div class="row mb-4 pb-3 border-bottom">
          <div class="col-md-12">
            <h6 class="font-weight-bold">
              <i class="fas fa-x-ray mr-2 text-primary"></i> RADIOGRAFÍA DE TÓRAX
            </h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="rx_realizado" value="true" v-model="localData.rx_torax.realizado" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="rx_realizado" value="false" v-model="localData.rx_torax.realizado" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div v-if="localData.rx_torax.realizado === 'true'" class="col-md-10">
            <div class="row">
              <div class="col-md-3">
                <label class="font-weight-bold">Fecha</label>
                <input type="date" class="form-control" v-model="localData.rx_torax.fecha" @change="emitChange" style="height: 38px;">
              </div>
              <div class="col-md-3">
                <label class="font-weight-bold">Resultado</label>
                <select class="form-control" v-model="localData.rx_torax.resultado" @change="emitChange" style="height: 38px;">
                  <option value="Normal">Normal</option>
                  <option value="Anormal">Anormal</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="font-weight-bold">Reporte</label>
                <textarea class="form-control" v-model="localData.rx_torax.reporte" @change="emitChange" placeholder="Hallazgos RX Tórax" rows="2" style="font-size: 12px;"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- EVALUACIÓN VISUAL -->
        <div class="row mb-4 pb-3 border-bottom">
          <div class="col-md-12">
            <h6 class="font-weight-bold">
              <i class="fas fa-eye mr-2 text-info"></i> EVALUACIÓN VISUAL
            </h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="eval_visual_realizado" value="true" v-model="localData.evaluacion_visual.realizado" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="eval_visual_realizado" value="false" v-model="localData.evaluacion_visual.realizado" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div v-if="localData.evaluacion_visual.realizado === 'true'" class="col-md-10">
            <div class="row">
              <div class="col-md-3">
                <label class="font-weight-bold">Fecha</label>
                <input type="date" class="form-control" v-model="localData.evaluacion_visual.fecha" @change="emitChange" style="height: 38px;">
              </div>
              <div class="col-md-9">
                <label class="font-weight-bold">Resultado</label>
                <div class="row">
                  <div class="col-md-3">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="eval_visual_tipo" value="Normal" v-model="localData.evaluacion_visual.tipo" @change="emitChange">
                      <span class="custom-control-label">Normal</span>
                    </label>
                  </div>
                  <div class="col-md-3">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="eval_visual_tipo" value="Miopía" v-model="localData.evaluacion_visual.tipo" @change="emitChange">
                      <span class="custom-control-label">Miopía</span>
                    </label>
                  </div>
                  <div class="col-md-3">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="eval_visual_tipo" value="Hipermetropía" v-model="localData.evaluacion_visual.tipo" @change="emitChange">
                      <span class="custom-control-label">Hipermetropía</span>
                    </label>
                  </div>
                  <div class="col-md-3">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="eval_visual_tipo" value="Astigmatismo" v-model="localData.evaluacion_visual.tipo" @change="emitChange">
                      <span class="custom-control-label">Astigmatismo</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- VISIÓN CERCANA -->
              <div class="col-md-12 mt-2">
                <label class="font-weight-bold">VISIÓN CERCANA</label>
                <div class="row">
                  <div class="col-md-4">
                    <label class="small">DER</label>
                    <input type="text" class="form-control form-control-sm" v-model="localData.evaluacion_visual.vision_cercana_der" @change="emitChange" placeholder="Ej: 20/20">
                  </div>
                  <div class="col-md-4">
                    <label class="small">IZQ</label>
                    <input type="text" class="form-control form-control-sm" v-model="localData.evaluacion_visual.vision_cercana_izq" @change="emitChange" placeholder="Ej: 20/20">
                  </div>
                  <div class="col-md-4">
                    <label class="small">A.O.</label>
                    <input type="text" class="form-control form-control-sm" v-model="localData.evaluacion_visual.vision_cercana_ao" @change="emitChange" placeholder="Ej: 20/20">
                  </div>
                </div>
              </div>

              <!-- VISIÓN LEJANA -->
              <div class="col-md-12 mt-2">
                <label class="font-weight-bold">VISIÓN LEJANA</label>
                <div class="row">
                  <div class="col-md-4">
                    <label class="small">DER</label>
                    <input type="text" class="form-control form-control-sm" v-model="localData.evaluacion_visual.vision_lejana_der" @change="emitChange" placeholder="Ej: 20/20">
                  </div>
                  <div class="col-md-4">
                    <label class="small">IZQ</label>
                    <input type="text" class="form-control form-control-sm" v-model="localData.evaluacion_visual.vision_lejana_izq" @change="emitChange" placeholder="Ej: 20/20">
                  </div>
                  <div class="col-md-4">
                    <label class="small">A.O.</label>
                    <input type="text" class="form-control form-control-sm" v-model="localData.evaluacion_visual.vision_lejana_ao" @change="emitChange" placeholder="Ej: 20/20">
                  </div>
                </div>
              </div>

              <div class="col-md-12 mt-2">
                <label class="font-weight-bold">Reporte</label>
                <textarea class="form-control" v-model="localData.evaluacion_visual.reporte" @change="emitChange" placeholder="Hallazgos visuales" rows="2" style="font-size: 12px;"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- AUDIOMETRÍA -->
        <div class="row mb-4 pb-3 border-bottom">
          <div class="col-md-12">
            <h6 class="font-weight-bold">
              <i class="fas fa-volume-up mr-2 text-warning"></i> AUDIOMETRÍA
            </h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="audio_realizado" value="true" v-model="localData.audiometria.realizado" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="audio_realizado" value="false" v-model="localData.audiometria.realizado" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div v-if="localData.audiometria.realizado === 'true'" class="col-md-10">
            <div class="row">
              <div class="col-md-3">
                <label class="font-weight-bold">Fecha</label>
                <input type="date" class="form-control" v-model="localData.audiometria.fecha" @change="emitChange" style="height: 38px;">
              </div>
              <div class="col-md-9">
                <label class="font-weight-bold">Resultado</label>
                <div class="row">
                  <div class="col-md-3">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="audio_resultado" value="Normal" v-model="localData.audiometria.resultado" @change="emitChange">
                      <span class="custom-control-label">Normal</span>
                    </label>
                  </div>
                  <div class="col-md-9">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="audio_resultado" value="Hipoacusia" v-model="localData.audiometria.resultado" @change="emitChange">
                      <span class="custom-control-label">Hipoacusia</span>
                    </label>
                  </div>
                </div>
                <!-- Tipo de hipoacusia -->
                <div v-if="localData.audiometria.resultado === 'Hipoacusia'" class="mt-2">
                  <label class="small font-weight-bold">Tipo de Hipoacusia:</label>
                  <div class="row">
                    <div class="col-md-4">
                      <label class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="audio_tipo" value="Neurosensorial" v-model="localData.audiometria.tipo_hipoacusia" @change="emitChange">
                        <span class="custom-control-label">Neurosensorial</span>
                      </label>
                    </div>
                    <div class="col-md-4">
                      <label class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="audio_tipo" value="Conductiva" v-model="localData.audiometria.tipo_hipoacusia" @change="emitChange">
                        <span class="custom-control-label">Conductiva</span>
                      </label>
                    </div>
                    <div class="col-md-4">
                      <label class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="audio_tipo" value="Mixta" v-model="localData.audiometria.tipo_hipoacusia" @change="emitChange">
                        <span class="custom-control-label">Mixta</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TABLA DE FRECUENCIAS -->
              <div class="col-md-12 mt-3">
                <label class="font-weight-bold">FRECUENCIAS (Hz)</label>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead style="background: #ecf0f1;">
                      <tr>
                        <th>Oído</th>
                        <th v-for="freq in frecuencias" :key="freq">{{ freq }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="font-weight-bold">Derecho</td>
                        <td v-for="freq in frecuencias" :key="'der-' + freq">
                          <input 
                            type="number" 
                            class="form-control form-control-sm" 
                            v-model.number="localData.audiometria.frecuencias.derecho[freq]" 
                            @change="emitChange"
                            style="width: 60px;"
                            min="0"
                            max="120"
                          >
                        </td>
                      </tr>
                      <tr>
                        <td class="font-weight-bold">Izquierdo</td>
                        <td v-for="freq in frecuencias" :key="'izq-' + freq">
                          <input 
                            type="number" 
                            class="form-control form-control-sm" 
                            v-model.number="localData.audiometria.frecuencias.izquierdo[freq]" 
                            @change="emitChange"
                            style="width: 60px;"
                            min="0"
                            max="120"
                          >
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="col-md-12 mt-2">
                <label class="font-weight-bold">Reporte</label>
                <textarea class="form-control" v-model="localData.audiometria.reporte" @change="emitChange" placeholder="Hallazgos audiométricos" rows="2" style="font-size: 12px;"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- ESPIROMETRÍA -->
        <div class="row">
          <div class="col-md-12">
            <h6 class="font-weight-bold">
              <i class="fas fa-lungs mr-2 text-success"></i> ESPIROMETRÍA
            </h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="espiro_realizado" value="true" v-model="localData.espirometria.realizado" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="espiro_realizado" value="false" v-model="localData.espirometria.realizado" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div v-if="localData.espirometria.realizado === 'true'" class="col-md-10">
            <div class="row">
              <div class="col-md-3">
                <label class="font-weight-bold">Fecha</label>
                <input type="date" class="form-control" v-model="localData.espirometria.fecha" @change="emitChange" style="height: 38px;">
              </div>
              <div class="col-md-9">
                <label class="font-weight-bold">Resultado</label>
                <div class="row">
                  <div class="col-md-2">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="espiro_resultado" value="Normal" v-model="localData.espirometria.resultado" @change="emitChange">
                      <span class="custom-control-label">Normal</span>
                    </label>
                  </div>
                  <div class="col-md-2">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="espiro_resultado" value="Obstructivo" v-model="localData.espirometria.resultado" @change="emitChange">
                      <span class="custom-control-label">Obstructivo</span>
                    </label>
                  </div>
                  <div class="col-md-2">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="espiro_resultado" value="Restrictivo" v-model="localData.espirometria.resultado" @change="emitChange">
                      <span class="custom-control-label">Restrictivo</span>
                    </label>
                  </div>
                  <div class="col-md-2">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="espiro_resultado" value="Mixto" v-model="localData.espirometria.resultado" @change="emitChange">
                      <span class="custom-control-label">Mixto</span>
                    </label>
                  </div>
                  <div class="col-md-2">
                    <label class="custom-control custom-radio">
                      <input type="radio" class="custom-control-input" name="espiro_resultado" value="PRISm" v-model="localData.espirometria.resultado" @change="emitChange">
                      <span class="custom-control-label">PRISm</span>
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-12 mt-2">
                <label class="font-weight-bold">Reporte</label>
                <textarea class="form-control" v-model="localData.espirometria.reporte" @change="emitChange" placeholder="Hallazgos espirometría" rows="2" style="font-size: 12px;"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: DIAGNÓSTICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-stethoscope mr-2"></i> DIAGNÓSTICOS FINALES (hasta 10)
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-secondary" style="font-size: 12px;">
          <i class="fas fa-info-circle mr-2"></i> Ingrese hasta 10 diagnósticos. El primero (principal) es obligatorio.
        </div>

        <div v-for="(diag, idx) in localData.diagnosticos" :key="'diag-' + idx" class="row mb-2">
          <div class="col-md-12">
            <label class="font-weight-bold">
              DIAGNÓSTICO {{ idx + 1 }} 
              <span v-if="idx === 0" class="text-danger">*</span>
            </label>
            <textarea 
              class="form-control" 
              v-model="localData.diagnosticos[idx]"
              @change="emitChange"
              :placeholder="idx === 0 ? 'Diagnóstico principal (OBLIGATORIO)' : 'Diagnóstico secundario ' + (idx + 1)"
              rows="2"
              style="font-size: 12px; border-radius: 4px; padding: 8px 10px;"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: ENFERMEDAD PROFESIONAL -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-briefcase-medical mr-2"></i> ENFERMEDAD PROFESIONAL
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12">
            <h6 class="font-weight-bold">¿SOSPECHA DE ENFERMEDAD PROFESIONAL?</h6>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="enfermedad_prof" value="true" v-model="localData.enfermedad_profesional" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="enfermedad_prof" value="false" v-model="localData.enfermedad_profesional" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
        </div>

        <!-- RESUMEN FINAL -->
        <div class="alert alert-success mt-3 mb-0">
          <strong>✓ Resumen Examen Médico - Trabajo Alto Riesgo</strong>
          <ul class="mb-0 pl-3" style="font-size: 12px; margin-top: 8px;">
            <li><strong>Paciente:</strong> [Por heredar del Master]</li>
            <li><strong>Diagnóstico Principal:</strong> {{ localData.diagnosticos[0] || 'No ingresado' }}</li>
            <li><strong>Enfermedad Profesional:</strong> {{ localData.enfermedad_profesional === 'true' ? 'SÍ' : 'NO' }}</li>
            <li><strong>Estado:</strong> Completado</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja4ParaclinicodiagnosticosTr',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      frecuencias: [125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000],
      localData: {
        ecg: { 
          realizado: 'false', 
          fecha: '', 
          resultado: 'Normal', 
          frecuencia_cardiaca: null,
          ritmo: '',
          reporte: '' 
        },
        rx_torax: { 
          realizado: 'false', 
          fecha: '', 
          resultado: 'Normal', 
          reporte: '' 
        },
        evaluacion_visual: { 
          realizado: 'false', 
          fecha: '', 
          tipo: 'Normal',
          vision_cercana_der: '',
          vision_cercana_izq: '',
          vision_cercana_ao: '',
          vision_lejana_der: '',
          vision_lejana_izq: '',
          vision_lejana_ao: '',
          reporte: '' 
        },
        audiometria: { 
          realizado: 'false', 
          fecha: '', 
          resultado: 'Normal',
          tipo_hipoacusia: '',
          frecuencias: {
            derecho: { 125: null, 250: null, 500: null, 1000: null, 2000: null, 3000: null, 4000: null, 6000: null, 8000: null },
            izquierdo: { 125: null, 250: null, 500: null, 1000: null, 2000: null, 3000: null, 4000: null, 6000: null, 8000: null }
          },
          reporte: '' 
        },
        espirometria: { 
          realizado: 'false', 
          fecha: '', 
          resultado: 'Normal',
          reporte: '' 
        },
        diagnosticos: ['', '', '', '', '', '', '', '', '', ''],
        enfermedad_profesional: 'false'
      }
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = JSON.parse(JSON.stringify(newVal))
        }
      },
      deep: true
    }
  },
  methods: {
    emitChange() {
      this.$emit('update:modelValue', JSON.parse(JSON.stringify(this.localData)))
    }
  },
  mounted() {
    if (this.modelValue && Object.keys(this.modelValue).length > 0) {
      this.localData = JSON.parse(JSON.stringify(this.modelValue))
    }
  }
}
</script>

<style scoped>
.hoja4-paraclinicodiagnosticos-tr {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.form-control:focus {
  border-color: #0c5460;
  box-shadow: 0 0 0 0.2rem rgba(12, 84, 96, 0.25);
}

.form-control-sm {
  font-size: 12px;
  border-radius: 3px;
  padding: 4px 8px;
}

.custom-control {
  position: relative;
  display: block;
  min-height: 1.5rem;
  padding-left: 1.5rem;
  margin-bottom: 0.5rem;
}

.custom-control-label {
  cursor: pointer;
  color: #2c3e50;
  user-select: none;
}

.alert {
  font-size: 12px;
  margin-bottom: 15px;
  border-radius: 4px;
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

h6 {
  font-size: 13px;
  margin-bottom: 10px;
  color: #2c3e50;
}

.border-bottom {
  border-bottom: 1px solid #dee2e6 !important;
}

textarea.form-control {
  resize: vertical;
}

i {
  width: 14px;
}

.table {
  font-size: 11px;
  margin-bottom: 0;
}

.table thead th {
  background: #ecf0f1;
  padding: 6px 4px;
  text-align: center;
  font-weight: 600;
}

.table tbody td {
  padding: 4px;
  vertical-align: middle;
  text-align: center;
}
</style>