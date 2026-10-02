<template>
  <div class="hoja3-antecedentessintomas-delli">
    <!-- SECTION: ANTECEDENTES MÉDICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-hospital-user mr-2"></i> ANTECEDENTES MÉDICOS PERSONALES
        </h5>
      </div>
      <div class="card-body">
        <!-- Historia Familiar Pérdida Auditiva -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold">HISTORIA FAMILIAR DE PÉRDIDA AUDITIVA</h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="historia_familiar" value="true" v-model="localData.historia_familiar_perdida" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="historia_familiar" value="false" v-model="localData.historia_familiar_perdida" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div class="col-md-5" v-if="localData.historia_familiar_perdida === 'true'">
            <input type="text" class="form-control" v-model="localData.parentesco" @change="emitChange" placeholder="Parentesco (padre, madre, hermano, etc.)" style="height: 38px;">
          </div>
          <div class="col-md-5" v-if="localData.historia_familiar_perdida === 'true'">
            <textarea class="form-control" v-model="localData.observaciones_familia" @change="emitChange" placeholder="Observaciones" rows="2"></textarea>
          </div>
        </div>

        <!-- Alergias -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold">ALERGIAS</h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="alergias" value="true" v-model="localData.alergias" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="alergias" value="false" v-model="localData.alergias" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div class="col-md-10" v-if="localData.alergias === 'true'">
            <input type="text" class="form-control" v-model="localData.alergias_especificar" @change="emitChange" placeholder="Especifique alergias (medicamentos, alimentos, etc.)" style="height: 38px;">
          </div>
        </div>

        <!-- COVID-19 -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold">ANTECEDENTE DE COVID-19</h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="covid19" value="true" v-model="localData.covid19" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="covid19" value="false" v-model="localData.covid19" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div class="col-md-10" v-if="localData.covid19 === 'true'">
            <textarea class="form-control" v-model="localData.covid19_obs" @change="emitChange" placeholder="Observaciones (fecha, gravedad, secuelas)" rows="2"></textarea>
          </div>
        </div>

        <!-- Resfriado Reciente -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold">RESFRIADO / INFECCIÓN VIRAL RECIENTE</h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="resfriado" value="true" v-model="localData.resfriado" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="resfriado" value="false" v-model="localData.resfriado" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div class="col-md-10" v-if="localData.resfriado === 'true'">
            <textarea class="form-control" v-model="localData.resfriado_obs" @change="emitChange" placeholder="Observaciones (duración, síntomas persistentes)" rows="2"></textarea>
          </div>
        </div>

        <!-- Otitis -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold">ANTECEDENTE DE OTITIS EXTERNA / MEDIA</h6>
          </div>
          <div class="col-md-2">
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="otitis" value="true" v-model="localData.otitis" @change="emitChange">
              <span class="custom-control-label">SÍ</span>
            </label>
            <label class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="otitis" value="false" v-model="localData.otitis" @change="emitChange">
              <span class="custom-control-label">NO</span>
            </label>
          </div>
          <div class="col-md-10" v-if="localData.otitis === 'true'">
            <textarea class="form-control" v-model="localData.otitis_obs" @change="emitChange" placeholder="Observaciones (tipo, lateralidad, tratamiento)" rows="2"></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: OTROS ANTECEDENTES -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-medkit mr-2"></i> OTROS ANTECEDENTES
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- SARAMPIÓN -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">SARAMPIÓN</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="sarampion" v-model="localData.sarampion" @change="emitChange">
              <label class="custom-control-label" for="sarampion">SÍ</label>
            </div>
          </div>

          <!-- HIPERTENSIÓN -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">HIPERTENSIÓN</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="hipertension" v-model="localData.hipertension" @change="emitChange">
              <label class="custom-control-label" for="hipertension">SÍ</label>
            </div>
          </div>

          <!-- PAPERAS -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">PAPERAS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="paperas" v-model="localData.paperas" @change="emitChange">
              <label class="custom-control-label" for="paperas">SÍ</label>
            </div>
          </div>

          <!-- MENINGITIS -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">MENINGITIS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="meningitis" v-model="localData.meningitis" @change="emitChange">
              <label class="custom-control-label" for="meningitis">SÍ</label>
            </div>
          </div>

          <!-- DIABETES -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">DIABETES</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="diabetes" v-model="localData.diabetes" @change="emitChange">
              <label class="custom-control-label" for="diabetes">SÍ</label>
            </div>
          </div>

          <!-- ENFERMEDAD ×2 -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">ENFERMEDAD 1</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="enfermedad1" v-model="localData.enfermedad1" @change="emitChange">
              <label class="custom-control-label" for="enfermedad1">SÍ</label>
            </div>
          </div>

          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">ENFERMEDAD 2</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="enfermedad2" v-model="localData.enfermedad2" @change="emitChange">
              <label class="custom-control-label" for="enfermedad2">SÍ</label>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- USO DE APARATO -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">USO DE APARATO</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="uso_aparat" v-model="localData.uso_aparat" @change="emitChange">
              <label class="custom-control-label" for="uso_aparat">SÍ</label>
            </div>
          </div>

          <!-- NINGUNA (otitis) -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">NINGUNA (otitis)</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="ninguna_otitis" v-model="localData.ninguna_otitis" @change="emitChange">
              <label class="custom-control-label" for="ninguna_otitis">SÍ</label>
            </div>
          </div>

          <!-- VERTIGO -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">VERTIGO</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="vertigo" v-model="localData.vertigo" @change="emitChange">
              <label class="custom-control-label" for="vertigo">SÍ</label>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- PARACUSIAS -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">PARACUSIAS (Willis)</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="paracusias_willis" v-model="localData.paracusias_willis" @change="emitChange">
              <label class="custom-control-label" for="paracusias_willis">SÍ</label>
            </div>
          </div>

          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">PARACUSIAS (Weber)</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="paracusias_weber" v-model="localData.paracusias_weber" @change="emitChange">
              <label class="custom-control-label" for="paracusias_weber">SÍ</label>
            </div>
          </div>

          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">PARACUSIAS (Negado)</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="paracusias_negado" v-model="localData.paracusias_negado" @change="emitChange">
              <label class="custom-control-label" for="paracusias_negado">SÍ</label>
            </div>
          </div>

          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">PARACUSIAS (Hipoacusia)</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="paracusias_hipo" v-model="localData.paracusias_hipo" @change="emitChange">
              <label class="custom-control-label" for="paracusias_hipo">SÍ</label>
            </div>
          </div>

          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">PARACUSIAS (Hipecusia)</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="paracusias_hiper" v-model="localData.paracusias_hiper" @change="emitChange">
              <label class="custom-control-label" for="paracusias_hiper">SÍ</label>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- FATIGA -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">FATIGA</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="fatiga" v-model="localData.fatiga" @change="emitChange">
              <label class="custom-control-label" for="fatiga">SÍ</label>
            </div>
          </div>

          <!-- SENSIBILIDAD -->
          <div class="col-md-2 mb-3">
            <label class="font-weight-bold">SENSIBILIDAD</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="sensibilidad" v-model="localData.sensibilidad" @change="emitChange">
              <label class="custom-control-label" for="sensibilidad">SÍ</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: SÍNTOMAS AUDITIVOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-volume-mute mr-2"></i> SÍNTOMAS AUDITIVOS
        </h5>
      </div>
      <div class="card-body">
        <!-- Descripción Hipoacusia -->
        <div class="row mb-3">
          <div class="col-md-12">
            <label class="font-weight-bold">DESCRIPCIÓN DE HIPOACUSIA (si aplica)</label>
            <textarea
              class="form-control"
              v-model="localData.descripcion_hipoacusia"
              @change="emitChange"
              placeholder="Describa la pérdida auditiva percibida"
              rows="2"
              style="border-radius: 4px; padding: 10px 12px;"
            ></textarea>
          </div>
        </div>

        <!-- Tiempo Evolución -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="font-weight-bold">TIEMPO DE EVOLUCIÓN</label>
            <input
              type="text"
              class="form-control"
              v-model="localData.tiempo_evolucion"
              @change="emitChange"
              placeholder="Ej: 6 meses, 2 años"
              style="height: 38px;"
            >
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold">
              <input type="checkbox" v-model="localData.niega_sintomatologia" @change="emitChange">
              Niega sintomatología auditiva
            </label>
          </div>
        </div>

        <!-- Síntomas Lateralizados -->
        <div class="row">
          <!-- OTALGIA -->
          <div class="col-md-12 mb-3">
            <h6 class="font-weight-bold text-danger">OTALGIA (Dolor Oído)</h6>
            <div class="row">
              <div class="col-md-4">
                <label>OÍDO DERECHO</label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" v-model="localData.otalgia_der" @change="emitChange">
                  <span class="custom-control-label">Presente</span>
                </label>
                <label v-if="localData.otalgia_der" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.otorrea_der" @change="emitChange">
                  Otorrea
                </label>
                <label v-if="localData.otalgia_der" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.prurito_der" @change="emitChange">
                  Prurito
                </label>
              </div>
              <div class="col-md-4">
                <label>OÍDO IZQUIERDO</label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" v-model="localData.otalgia_izq" @change="emitChange">
                  <span class="custom-control-label">Presente</span>
                </label>
                <label v-if="localData.otalgia_izq" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.otorrea_izq" @change="emitChange">
                  Otorrea
                </label>
                <label v-if="localData.otalgia_izq" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.prurito_izq" @change="emitChange">
                  Prurito
                </label>
              </div>
            </div>
          </div>

          <!-- OTODINIA -->
          <div class="col-md-12 mb-3">
            <h6 class="font-weight-bold text-warning">OTODINIA (Dolor Conducto)</h6>
            <div class="row">
              <div class="col-md-4">
                <label>OÍDO DERECHO</label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" v-model="localData.otodinia_der" @change="emitChange">
                  <span class="custom-control-label">Presente</span>
                </label>
                <label v-if="localData.otodinia_der" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.otorragia_der" @change="emitChange">
                  Otorragia
                </label>
                <label v-if="localData.otodinia_der" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.autofonia_der" @change="emitChange">
                  Autofonía
                </label>
              </div>
              <div class="col-md-4">
                <label>OÍDO IZQUIERDO</label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" v-model="localData.otodinia_izq" @change="emitChange">
                  <span class="custom-control-label">Presente</span>
                </label>
                <label v-if="localData.otodinia_izq" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.otorragia_izq" @change="emitChange">
                  Otorragia
                </label>
                <label v-if="localData.otodinia_izq" class="custom-checkbox ml-3">
                  <input type="checkbox" v-model="localData.autofonia_izq" @change="emitChange">
                  Autofonía
                </label>
              </div>
            </div>
          </div>

          <!-- Otros síntomas -->
          <div class="col-md-12 mb-3">
            <h6 class="font-weight-bold">OTROS SÍNTOMAS</h6>
            <label class="custom-checkbox">
              <input type="checkbox" v-model="localData.acufenos" @change="emitChange">
              Acúfenos (zumbidos/silbidos)
            </label>
            <label class="custom-checkbox">
              <input type="checkbox" v-model="localData.congestion" @change="emitChange">
              Congestión / Sensación Plenitud
            </label>
            <label class="custom-checkbox">
              <input type="checkbox" v-model="localData.algiacusia" @change="emitChange">
              Algiacusia (Sonidos molestos)
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: OTOSCOPIA -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-microscope mr-2"></i> EXAMEN OTOSCÓPICO
        </h5>
      </div>
      <div class="card-body">
        <!-- Conducto Auditivo Externo -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold">CONDUCTO AUDITIVO EXTERNO</h6>
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold">OÍDO DERECHO</label>
            <select class="form-control" v-model="localData.conducto_auditivo_der" @change="emitChange" style="height: 38px;">
              <option value="Normal">Normal</option>
              <option value="Patológico">Patológico</option>
            </select>
            <textarea v-if="localData.conducto_auditivo_der === 'Patológico'" class="form-control mt-2" v-model="localData.conducto_hallazgos_der" @change="emitChange" placeholder="Hallazgos" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold">OÍDO IZQUIERDO</label>
            <select class="form-control" v-model="localData.conducto_auditivo_izq" @change="emitChange" style="height: 38px;">
              <option value="Normal">Normal</option>
              <option value="Patológico">Patológico</option>
            </select>
            <textarea v-if="localData.conducto_auditivo_izq === 'Patológico'" class="form-control mt-2" v-model="localData.conducto_hallazgos_izq" @change="emitChange" placeholder="Hallazgos" rows="2"></textarea>
          </div>
        </div>

        <!-- Oído Medio / Tímpano -->
        <div class="row">
          <div class="col-md-12">
            <h6 class="font-weight-bold">OÍDO MEDIO / TÍMPANO</h6>
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold">OÍDO DERECHO</label>
            <select class="form-control" v-model="localData.oido_medio_der" @change="emitChange" style="height: 38px;">
              <option value="Normal">Normal</option>
              <option value="Patológico">Patológico</option>
            </select>
            <textarea v-if="localData.oido_medio_der === 'Patológico'" class="form-control mt-2" v-model="localData.oido_medio_hallazgos_der" @change="emitChange" placeholder="Hallazgos" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold">OÍDO IZQUIERDO</label>
            <select class="form-control" v-model="localData.oido_medio_izq" @change="emitChange" style="height: 38px;">
              <option value="Normal">Normal</option>
              <option value="Patológico">Patológico</option>
            </select>
            <textarea v-if="localData.oido_medio_izq === 'Patológico'" class="form-control mt-2" v-model="localData.oido_medio_hallazgos_izq" @change="emitChange" placeholder="Hallazgos" rows="2"></textarea>
          </div>
        </div>

        <!-- Examen Normal Confirmación -->
        <div class="row mt-3">
          <div class="col-md-12">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="examen_otoscopico_normal" @change="marcarTodoNormal">
              ✓ Confirmar: Examen otoscópico completamente normal (ambos oídos)
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja3AntecedentessintomasDelli',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        historia_familiar_perdida: 'false',
        parentesco: '',
        observaciones_familia: '',
        alergias: 'false',
        alergias_especificar: '',
        covid19: 'false',
        covid19_obs: '',
        resfriado: 'false',
        resfriado_obs: '',
        otitis: 'false',
        otitis_obs: '',
        descripcion_hipoacusia: '',
        tiempo_evolucion: '',
        niega_sintomatologia: false,
        otalgia_der: false,
        otalgia_izq: false,
        otorrea_der: false,
        otorrea_izq: false,
        prurito_der: false,
        prurito_izq: false,
        otodinia_der: false,
        otodinia_izq: false,
        otorragia_der: false,
        otorragia_izq: false,
        autofonia_der: false,
        autofonia_izq: false,
        acufenos: false,
        congestion: false,
        algiacusia: false,
        conducto_auditivo_der: 'Normal',
        conducto_auditivo_izq: 'Normal',
        conducto_hallazgos_der: '',
        conducto_hallazgos_izq: '',
        oido_medio_der: 'Normal',
        oido_medio_izq: 'Normal',
        oido_medio_hallazgos_der: '',
        oido_medio_hallazgos_izq: '',
        // Nuevos campos de Hoja 3 según Excel
        sarampion: false,
        hipertension: false,
        paperas: false,
        meningitis: false,
        diabetes: false,
        enfermedad1: false,
        enfermedad2: false,
        uso_aparat: false,
        ninguna_otitis: false,
        vertigo: false,
        paracusias_willis: false,
        paracusias_weber: false,
        paracusias_negado: false,
        paracusias_hipo: false,
        paracusias_hiper: false,
        fatiga: false,
        sensibilidad: false,
        examen_otoscopico: '',
        patron: ''
      },
      examen_otoscopico_normal: false
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = { ...newVal }
        }
      },
      deep: true
    }
  },
  methods: {
    emitChange() {
      this.$emit('update:modelValue', { ...this.localData })
    },
    marcarTodoNormal() {
      if (this.examen_otoscopico_normal) {
        this.localData.conducto_auditivo_der = 'Normal'
        this.localData.conducto_auditivo_izq = 'Normal'
        this.localData.oido_medio_der = 'Normal'
        this.localData.oido_medio_izq = 'Normal'
        this.localData.conducto_hallazgos_der = ''
        this.localData.conducto_hallazgos_izq = ''
        this.localData.oido_medio_hallazgos_der = ''
        this.localData.oido_medio_hallazgos_izq = ''
      }
      this.emitChange()
    }
  },
  mounted() {
    if (this.modelValue && Object.keys(this.modelValue).length > 0) {
      this.localData = { ...this.modelValue }
    }
  }
}
</script>

<style scoped>
.hoja3-antecedentessintomas-delli {
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
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

.custom-control {
  position: relative;
  display: block;
  min-height: 1.5rem;
  padding-left: 1.5rem;
}

.custom-control-input {
  position: absolute;
  left: 0;
  z-index: -1;
  width: 1rem;
  height: 1.25rem;
  opacity: 0;
}

.custom-control-label {
  position: relative;
  margin-bottom: 0;
  vertical-align: top;
  cursor: pointer;
}

.custom-checkbox .custom-control-label::before {
  border-radius: 0.25rem;
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

h6 {
  font-size: 12px;
  margin-top: 10px;
  margin-bottom: 8px;
}

.custom-checkbox {
  display: block;
  padding-left: 1.5rem;
  margin-bottom: 0.75rem;
}

.custom-checkbox input[type="checkbox"] {
  margin-left: -1.5rem;
  margin-right: 0.5rem;
}
</style>