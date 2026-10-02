<template>
  <div class="hoja2-riesgosototoxicos-delli">
    <!-- SECTION: EVALUACIÓN RIESGO AUDITIVO -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-exclamation-triangle mr-2"></i> EVALUACIÓN RIESGO AUDITIVO
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- RIESGO AUDITIVO -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">NIVEL DE RIESGO AUDITIVO <span class="text-danger">*</span></label>
            <select class="form-control" v-model="localData.riesgo_auditivo" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="Bajo (80-<82 dB)">Bajo (80-&lt;82 dB)</option>
              <option value="Medio (82-84.9 dB)">Medio (82-84.9 dB)</option>
              <option value="Muy Alto (>85 dB)">Muy Alto (&gt;85 dB)</option>
              <option value="Crítico (>100 dB)">Crítico (&gt;100 dB)</option>
            </select>
            <small class="text-muted">Según norma ISO</small>
          </div>

          <!-- DURACIÓN EXPOSICIÓN -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">DURACIÓN EXPOSICIÓN DIARIA</label>
            <select class="form-control" v-model="localData.duracion_exposicion" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="24 horas">24 horas (continuo)</option>
              <option value="16 horas">16 horas</option>
              <option value="8 horas">8 horas (jornada laboral)</option>
              <option value="4 horas">4 horas</option>
              <option value="2 horas">2 horas</option>
              <option value="1 hora">1 hora</option>
              <option value="30 minutos">30 minutos</option>
              <option value="15 minutos">15 minutos</option>
            </select>
          </div>

          <!-- MEDIDAS DE CONTROL -->
          <div class="col-md-12 mb-3">
            <label class="font-weight-bold">MEDIDAS DE CONTROL IMPLEMENTADAS</label>
            <textarea 
              class="form-control" 
              v-model="localData.medidas_control"
              @change="emitChange"
              placeholder="Describa las medidas técnicas y organizacionales en vigor"
              rows="3"
              style="border-radius: 4px; font-size: 13px; padding: 10px 12px;"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PROTECCIÓN AUDITIVA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-shield-alt mr-2"></i> PROTECCIÓN AUDITIVA PERSONAL (EPP)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- USO EPP -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">USO DE PROTECTOR AUDITIVO</label>
            <select class="form-control" v-model="localData.uso_proteccion" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="Siempre">Siempre</option>
              <option value="Usualmente">Usualmente (70-90%)</option>
              <option value="Ocasionalmente">Ocasionalmente (30-70%)</option>
              <option value="Nunca">Nunca</option>
            </select>
          </div>

          <!-- TIPO PROTECTOR -->
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold">TIPO DE PROTECTOR AUDITIVO</label>
            <select class="form-control" v-model="localData.tipo_protector" @change="emitChange" style="height: 38px;">
              <option value="">-- Seleccionar --</option>
              <option value="Tapones desechables">Tapones desechables (foam)</option>
              <option value="Tapones reutilizables">Tapones reutilizables</option>
              <option value="Híbridos">Híbridos (tapones + banda)</option>
              <option value="Orejeras">Orejeras / Cascos</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: AGENTES OTOTÓXICOS QUÍMICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-flask-vial mr-2"></i> AGENTES OTOTÓXICOS QUÍMICOS
        </h5>
      </div>
      <div class="card-body">
        <!-- DISOLVENTES -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold text-info">DISOLVENTES ORGÁNICOS</h6>
          </div>
          <div class="col-md-3" v-for="disolvente in ['Tolueno', 'Xileno', 'Estireno', 'Tricloroetileno']" :key="disolvente">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="disolventes_list" :value="disolvente" @change="emitChange">
              {{ disolvente }}
            </label>
          </div>
        </div>

        <!-- METALES -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold text-warning">METALES PESADOS</h6>
          </div>
          <div class="col-md-3" v-for="metal in ['Mercurio', 'Manganeso', 'Plomo', 'Arsénico']" :key="metal">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="metales_list" :value="metal" @change="emitChange">
              {{ metal }}
            </label>
          </div>
        </div>

        <!-- GASES -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold text-danger">GASES TÓXICOS</h6>
          </div>
          <div class="col-md-3" v-for="gas in ['Monóxido de Carbono', 'Cianuro de Hidrógeno']" :key="gas">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="gases_list" :value="gas" @change="emitChange">
              {{ gas }}
            </label>
          </div>
        </div>

        <!-- SALES -->
        <div class="row mb-3">
          <div class="col-md-12">
            <h6 class="font-weight-bold text-success">SALES CIANURO</h6>
          </div>
          <div class="col-md-3">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="sales_list" value="Cianuros" @change="emitChange">
              Cianuros
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: FÁRMACOS OTOTÓXICOS -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-pills mr-2"></i> FÁRMACOS OTOTÓXICOS
        </h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info" role="alert">
          <small><i class="fas fa-info-circle mr-1"></i> Marque SI si está tomando o ha tomado el fármaco. Especifique dosis y duración.</small>
        </div>

        <!-- Tabla de fármacos -->
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead style="background: #ecf0f1;">
              <tr>
                <th>Grupo Farmacológico</th>
                <th>Medicamento</th>
                <th>Uso Actual</th>
                <th>Dosis / Duración</th>
              </tr>
            </thead>
            <tbody>
              <!-- AMINOGLUCÓSIDOS -->
              <tr v-for="farmacos in getGruposFarmacos()" :key="farmacos.grupo">
                <td v-if="farmacos.medicamentos[0]" rowspan="">
                  <strong>{{ farmacos.grupo }}</strong>
                </td>
                <td v-for="(med, idx) in farmacos.medicamentos" :key="med">
                  <label class="custom-checkbox mb-0">
                    <input type="checkbox" v-model="farmacos_list" :value="med" @change="emitChange">
                    {{ med }}
                  </label>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION: HÁBITOS -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-smoking-ban mr-2"></i> HÁBITOS NOCIVOS
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- TABAQUISMO -->
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">CONSUMO DE TABACO</label>
            <div class="custom-control custom-radio">
              <input type="radio" id="tabaco_si" name="tabaquismo" value="true" v-model="localData.tabaquismo" @change="emitChange" class="custom-control-input">
              <label class="custom-control-label" for="tabaco_si">SÍ</label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="tabaco_no" name="tabaquismo" value="false" v-model="localData.tabaquismo" @change="emitChange" class="custom-control-input">
              <label class="custom-control-label" for="tabaco_no">NO</label>
            </div>
          </div>

          <!-- CIGARRILLOS/DÍA -->
          <div class="col-md-4 mb-3" v-if="localData.tabaquismo === 'true'">
            <label class="font-weight-bold">CIGARRILLOS / DÍA</label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="localData.cigarrillos_dia"
              @change="emitChange"
              placeholder="Cantidad"
              style="height: 38px;"
            >
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: OTROS FACTORES DE RIESGO -->
    <div class="card">
      <div class="card-header" style="background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-biohazard mr-2"></i> OTROS FACTORES DE RIESGO
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- TOXICOMANÍAS -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">TOXICOMANÍAS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="toxicomanias" v-model="localData.toxicomanias" @change="emitChange">
              <label class="custom-control-label" for="toxicomanias">SÍ</label>
            </div>
          </div>

          <!-- ANTIBIÓTICOS -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">ANTIBIÓTICOS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="antibioticos" v-model="localData.antibioticos" @change="emitChange">
              <label class="custom-control-label" for="antibioticos">SÍ</label>
            </div>
          </div>

          <!-- DIURÉTICOS -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">DIURÉTICOS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="diureticos" v-model="localData.diureticos" @change="emitChange">
              <label class="custom-control-label" for="diureticos">SÍ</label>
            </div>
          </div>

          <!-- SALICILATOS -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">SALICILATOS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="salicilatos" v-model="localData.salicilatos" @change="emitChange">
              <label class="custom-control-label" for="salicilatos">SÍ</label>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- ARMAS DE FUEGO / CAÑERÍAS -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">ARMAS DE FUEGO / CAÑERÍAS</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="armas_fuego_caneria" v-model="localData.armas_fuego_caneria" @change="emitChange">
              <label class="custom-control-label" for="armas_fuego_caneria">SÍ</label>
            </div>
          </div>

          <!-- NIVELES ALTOS DE MÚSICA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">NIVELES ALTOS DE MÚSICA</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="niveles_altos_musica" v-model="localData.niveles_altos_musica" @change="emitChange">
              <label class="custom-control-label" for="niveles_altos_musica">SÍ</label>
            </div>
          </div>

          <!-- LUGARES RUIDOSOS / DISCOTECA -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">LUGARES RUIDOSOS / DISCOTECA</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="lugares_ruidosos_discoteca" v-model="localData.lugares_ruidosos_discoteca" @change="emitChange">
              <label class="custom-control-label" for="lugares_ruidosos_discoteca">SÍ</label>
            </div>
          </div>

          <!-- AUTOMOVILISMO / MOTOCICLISMO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">AUTOMOVILISMO / MOTOCICLISMO</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="automovilismo_motociclismo" v-model="localData.automovilismo_motociclismo" @change="emitChange">
              <label class="custom-control-label" for="automovilismo_motociclismo">SÍ</label>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- OTROS PASATIEMPOS RUIDO -->
          <div class="col-md-3 mb-3">
            <label class="font-weight-bold">OTROS PASATIEMPOS RUIDO</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="otros_pasatiempos_ruido" v-model="localData.otros_pasatiempos_ruido" @change="emitChange">
              <label class="custom-control-label" for="otros_pasatiempos_ruido">SÍ</label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja2RiesgosototoxicosDelli',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        riesgo_auditivo: '',
        duracion_exposicion: '',
        medidas_control: '',
        uso_proteccion: '',
        tipo_protector: '',
        tabaquismo: 'false',
        cigarrillos_dia: 0,
        agentes_ototoxicos: [],
        // Nuevos campos de Hoja 2 según Excel
        toxicomanias: false,
        antibioticos: false,
        diureticos: false,
        salicilatos: false,
        armas_fuego_caneria: false,
        niveles_altos_musica: false,
        lugares_ruidosos_discoteca: false,
        automovilismo_motociclismo: false,
        otros_pasatiempos_ruido: false
      },
      disolventes_list: [],
      metales_list: [],
      gases_list: [],
      sales_list: [],
      farmacos_list: [],
      farmacos_grupos: {
        'Aminoglucósidos': ['Estreptomicina', 'Neomicina', 'Gentamicina', 'Tobramicina', 'Amikacina', 'Kanamicina', 'Paromomicina'],
        'Macrólidos': ['Eritromicina', 'Azitromicina', 'Claritromicina', 'Clindamicina', 'Lincomicina'],
        'Glucopeptídicos': ['Vancomicina', 'Teicoplanina'],
        'Otros Antibióticos': ['Minociclina', 'Clorafenicol', 'Cefalexina'],
        'Salicilatos': ['AAS (Aspirina)', 'Ácido Acetilsalicílico'],
        'Diuréticos': ['Furosemida', 'Bumetanida', 'Piretanida', 'Torasemida'],
        'Antimaláricos': ['Quinina', 'Cloroquina', 'Hidroxicloroquina', 'Primaquina', 'Pirimetamina'],
        'Beta-bloqueadores': ['Propranolol', 'Practolol'],
        'Citostáticos': ['Bleomicina', 'Cisplatino', 'Vincristina', 'Carboplatino', 'Ciclofosfamida'],
        'Otros': ['Desferroxiamina', 'Nortriptilina', 'Imipramina', 'Quinidina']
      }
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
      const agentes = [
        ...this.disolventes_list,
        ...this.metales_list,
        ...this.gases_list,
        ...this.sales_list
      ]

      this.localData.agentes_ototoxicos = agentes

      this.$emit('update:modelValue', {
        ...this.localData,
        farmacos_ototoxicos: this.farmacos_list,
        // Nuevos campos de Hoja 2 según Excel
        toxicomanias: this.toxicomanias,
        antibioticos: this.antibioticos,
        diureticos: this.diureticos,
        salicilatos: this.salicilatos,
        armas_fuego_caneria: this.armas_fuego_caneria,
        niveles_altos_musica: this.niveles_altos_musica,
        lugares_ruidosos_discoteca: this.lugares_ruidosos_discoteca,
        automovilismo_motociclismo: this.automovilismo_motociclismo,
        otros_pasatiempos_ruido: this.otros_pasatiempos_ruido
      })
    },
    getGruposFarmacos() {
      return Object.keys(this.farmacos_grupos).map(grupo => ({
        grupo,
        medicamentos: this.farmacos_grupos[grupo]
      }))
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
.hoja2-riesgosototoxicos-delli {
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
  height: 38px;
}

.form-control:focus {
  border-color: #5F6E7E;
  box-shadow: 0 0 0 0.2rem rgba(95, 110, 126, 0.25);
}

.custom-checkbox {
  display: block;
  padding-left: 1.5rem;
  margin-bottom: 0.75rem;
  cursor: pointer;
}

.custom-checkbox input[type="checkbox"] {
  margin-left: -1.5rem;
  margin-right: 0.5rem;
}

.custom-checkbox input[type="checkbox"]:checked + .text-muted {
  color: #495057 !important;
}

label {
  font-size: 13px;
  margin-bottom: 6px;
  color: #2c3e50;
}

.table {
  margin-bottom: 0;
  font-size: 13px;
}

.table thead th {
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #dee2e6;
}

h6 {
  font-size: 12px;
  margin-top: 10px;
  margin-bottom: 8px;
}

.alert {
  font-size: 12px;
  margin-bottom: 15px;
}
</style>