<template>
  <div class="clinico-examen-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="fas fa-stethoscope" aria-hidden="true"></i>
      <h2>Examen Clínico por Sistemas (WORLDSTRIDE - HOJA 3)</h2>
    </div>

    <!-- SIGNOS VITALES -->
    <div class="card-section">
      <div class="card-header">
        <h3>Signos Vitales</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-2">
            <label>FC (lat/min)</label>
            <input type="number" v-model="form.signos_vitales.fc" class="form-control" min="40" max="200" />
          </div>
          <div class="form-group col-md-2">
            <label>FR (resp/min)</label>
            <input type="number" v-model="form.signos_vitales.fr" class="form-control" min="8" max="40" />
          </div>
          <div class="form-group col-md-2">
            <label>TA (mmHg)</label>
            <input type="text" v-model="form.signos_vitales.ta" class="form-control" placeholder="120/80" />
          </div>
          <div class="form-group col-md-2">
            <label>Temp (°C)</label>
            <input type="number" v-model="form.signos_vitales.temperatura" class="form-control" min="35" max="41" step="0.1" />
          </div>
          <div class="form-group col-md-2">
            <label>SatO2 (%)</label>
            <input type="number" v-model="form.signos_vitales.saturacion" class="form-control" min="70" max="100" />
          </div>
          <div class="form-group col-md-2">
            <label>Glucosa (mg/dL)</label>
            <input type="number" v-model="form.signos_vitales.glucosa" class="form-control" />
          </div>
        </div>
      </div>
    </div>

    <!-- ANTROPOMETRÍA -->
    <div class="card-section">
      <div class="card-header">
        <h3>Antropometría</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-2">
            <label>Peso (kg)</label>
            <input type="number" v-model="form.antropometria.peso" class="form-control" step="0.1" />
          </div>
          <div class="form-group col-md-2">
            <label>Talla (cm)</label>
            <input type="number" v-model="form.antropometria.talla" class="form-control" />
          </div>
          <div class="form-group col-md-2">
            <label>IMC (kg/m²)</label>
            <input type="number" v-model="form.antropometria.imc" class="form-control" step="0.1" readonly />
          </div>
          <div class="form-group col-md-2">
            <label>Categoría IMC</label>
            <input type="text" v-model="categoriaIMC" class="form-control" readonly />
          </div>
          <div class="form-group col-md-2">
            <label>Perímetro Cintura (cm)</label>
            <input type="number" v-model="form.antropometria.perimetro_cintura" class="form-control" />
          </div>
          <div class="form-group col-md-2">
            <label>Perímetro Cadera (cm)</label>
            <input type="number" v-model="form.antropometria.perimetro_cadera" class="form-control" />
          </div>
        </div>
      </div>
    </div>

    <!-- COMORBILIDADES -->
    <div class="card-section">
      <div class="card-header">
        <h3>Comorbilidades Identificadas (Marque las presentes)</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="col-md-6">
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-diabetes" v-model="form.comorbilidades.diabetes" class="custom-control-input" />
              <label class="custom-control-label" for="comor-diabetes">Diabetes</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-hipertension" v-model="form.comorbilidades.hipertension" class="custom-control-input" />
              <label class="custom-control-label" for="comor-hipertension">Hipertensión arterial</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-asma" v-model="form.comorbilidades.asma" class="custom-control-input" />
              <label class="custom-control-label" for="comor-asma">Asma/EPOC</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-cardiopatia" v-model="form.comorbilidades.cardiopatia" class="custom-control-input" />
              <label class="custom-control-label" for="comor-cardiopatia">Cardiopatía</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-artritis" v-model="form.comorbilidades.artritis" class="custom-control-input" />
              <label class="custom-control-label" for="comor-artritis">Artritis/Artrosis</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-depression" v-model="form.comorbilidades.depresion" class="custom-control-input" />
              <label class="custom-control-label" for="comor-depression">Depresión</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-ansiedad" v-model="form.comorbilidades.ansiedad" class="custom-control-input" />
              <label class="custom-control-label" for="comor-ansiedad">Ansiedad</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-hiperlipidemia" v-model="form.comorbilidades.hiperlipidemia" class="custom-control-input" />
              <label class="custom-control-label" for="comor-hiperlipidemia">Hiperlipidemia/Colesterol alto</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-tiroidea" v-model="form.comorbilidades.tiroidea" class="custom-control-input" />
              <label class="custom-control-label" for="comor-tiroidea">Enfermedad tiroidea</label>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" id="comor-otras" v-model="form.comorbilidades.otras_presente" class="custom-control-input" />
              <label class="custom-control-label" for="comor-otras">Otras</label>
            </div>
            <input v-if="form.comorbilidades.otras_presente" type="text" v-model="form.comorbilidades.otras" class="form-control mt-2 form-control-sm" placeholder="Especificar..." />
          </div>
        </div>
      </div>
    </div>

    <!-- 11 SISTEMAS DE EXAMEN FÍSICO -->
    <div v-for="(sistema, idx) in sistemas" :key="idx" class="card-section">
      <div class="card-header">
        <h3>{{ idx + 1 }}. {{ sistema.nombre }}</h3>
      </div>
      <div class="card-body">
        <!-- INTERROGATORIO -->
        <div class="form-group">
          <label class="font-weight-bold">Interrogatorio (síntomas referidos):</label>
          <textarea v-model="form.sistemas[sistema.key].interrogatorio" class="form-control" rows="2" placeholder="Síntomas relatados por el trabajador..."></textarea>
        </div>

        <!-- EXPLORACIÓN -->
        <div class="form-group">
          <label class="font-weight-bold">Exploración Física (hallazgos):</label>
          <textarea v-model="form.sistemas[sistema.key].exploracion" class="form-control" rows="2" placeholder="Resultados del examen físico..."></textarea>
        </div>

        <!-- CAMPOS ESPECÍFICOS POR SISTEMA -->
        <div v-if="sistema.key === 'ojos'" class="form-row mt-3">
          <div class="form-group col-md-3">
            <label>Agudeza Visual OD (sin corrección)</label>
            <input type="text" v-model="form.sistemas.ojos.agudeza_visual_od" class="form-control" placeholder="Ej: 20/20" />
          </div>
          <div class="form-group col-md-3">
            <label>Agudeza Visual OI (sin corrección)</label>
            <input type="text" v-model="form.sistemas.ojos.agudeza_visual_oi" class="form-control" placeholder="Ej: 20/20" />
          </div>
          <div class="form-group col-md-3">
            <label>Test Ishihara (daltonismo)</label>
            <select v-model="form.sistemas.ojos.ishihara" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normal">Normal</option>
              <option value="protanopia">Protanopia</option>
              <option value="deuteranopia">Deuteranopia</option>
              <option value="tritanopia">Tritanopia</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Presión intraocular (mmHg)</label>
            <input type="number" v-model="form.sistemas.ojos.presion_intraocular" class="form-control" />
          </div>
        </div>

        <div v-if="sistema.key === 'oidos'" class="form-row mt-3">
          <div class="form-group col-md-4">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" id="acufenos" v-model="form.sistemas.oidos.acufenos" class="custom-control-input" />
              <label class="custom-control-label" for="acufenos">Acúfenos (zumbido de oídos)</label>
            </div>
          </div>
          <div class="form-group col-md-4">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" id="mareos" v-model="form.sistemas.oidos.mareos" class="custom-control-input" />
              <label class="custom-control-label" for="mareos">Mareos/Vértigo</label>
            </div>
          </div>
          <div class="form-group col-md-4">
            <label>Audiometría (si aplica)</label>
            <input type="text" v-model="form.sistemas.oidos.audiometria" class="form-control" placeholder="Resultado..." />
          </div>
        </div>

        <div v-if="sistema.key === 'nariz'" class="form-row mt-3">
          <div class="form-group col-md-4">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" id="epistaxis" v-model="form.sistemas.nariz.epistaxis" class="custom-control-input" />
              <label class="custom-control-label" for="epistaxis">Epistaxis (sangrado nasal)</label>
            </div>
          </div>
          <div class="form-group col-md-4">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" id="obstruccion" v-model="form.sistemas.nariz.obstruccion" class="custom-control-input" />
              <label class="custom-control-label" for="obstruccion">Obstrucción nasal</label>
            </div>
          </div>
          <div class="form-group col-md-4">
            <label>Desviación septal</label>
            <select v-model="form.sistemas.nariz.desviacion_septal" class="form-control">
              <option value="">Seleccionar</option>
              <option value="no">No</option>
              <option value="leve">Leve</option>
              <option value="moderada">Moderada</option>
              <option value="severa">Severa</option>
            </select>
          </div>
        </div>

        <div v-if="sistema.key === 'cardiovascular'" class="form-row mt-3">
          <div class="form-group col-md-3">
            <label>Auscultación Cardíaca</label>
            <select v-model="form.sistemas.cardiovascular.auscultacion" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normal">Normal</option>
              <option value="taquicardia">Taquicardia</option>
              <option value="bradicardia">Bradicardia</option>
              <option value="arritmia">Arritmia</option>
              <option value="soplo">Soplo</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Pulsos periféricos</label>
            <select v-model="form.sistemas.cardiovascular.pulsos_perifericos" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normales">Normales</option>
              <option value="debiles">Débiles</option>
              <option value="aumentados">Aumentados</option>
              <option value="asimetricos">Asimétricos</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Edemas periféricos</label>
            <select v-model="form.sistemas.cardiovascular.edemas" class="form-control">
              <option value="">Seleccionar</option>
              <option value="no">No</option>
              <option value="leve">Leve</option>
              <option value="moderado">Moderado</option>
              <option value="severo">Severo</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Varices</label>
            <select v-model="form.sistemas.cardiovascular.varices" class="form-control">
              <option value="">Seleccionar</option>
              <option value="no">No</option>
              <option value="miembros_inferiores">Miembros inferiores</option>
              <option value="miembros_superiores">Miembros superiores</option>
              <option value="generalizadas">Generalizadas</option>
            </select>
          </div>
        </div>

        <div v-if="sistema.key === 'pulmonar'" class="form-row mt-3">
          <div class="form-group col-md-4">
            <label>Auscultación Pulmonar</label>
            <select v-model="form.sistemas.pulmonar.auscultacion" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normal">Normal</option>
              <option value="sibilancias">Sibilancias</option>
              <option value="estertores">Estertores</option>
              <option value="disminuido">Murmullo disminuido</option>
              <option value="bronquial">Murmullo bronquial</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Tos productiva/Esputo</label>
            <select v-model="form.sistemas.pulmonar.tos_esputo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="no">No</option>
              <option value="ocasional">Ocasional</option>
              <option value="frecuente">Frecuente</option>
              <option value="productiva">Productiva (especificar tipo)</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Espirometría (si aplica)</label>
            <input type="text" v-model="form.sistemas.pulmonar.espirometria" class="form-control" placeholder="Resultado FEV1/FVC..." />
          </div>
        </div>

        <div v-if="sistema.key === 'gastrointestinal'" class="form-row mt-3">
          <div class="form-group col-md-3">
            <label>Palpación Abdominal</label>
            <select v-model="form.sistemas.gastrointestinal.palpacion" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normal">Normal</option>
              <option value="sensibilidad_epigastrica">Sensibilidad epigástrica</option>
              <option value="masas">Masas palpables</option>
              <option value="distension">Distensión</option>
              <option value="defensa">Defensa muscular</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Ruidos Intestinales</label>
            <select v-model="form.sistemas.gastrointestinal.ruidos_intestinales" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normales">Normales</option>
              <option value="aumentados">Aumentados</option>
              <option value="disminuidos">Disminuidos</option>
              <option value="ausentes">Ausentes</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Tamaño Hígado</label>
            <select v-model="form.sistemas.gastrointestinal.tamano_higado" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normal">Normal</option>
              <option value="hepatomegalia_leve">Hepatomegalia leve</option>
              <option value="hepatomegalia_moderada">Hepatomegalia moderada</option>
              <option value="hepatomegalia_severa">Hepatomegalia severa</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Bazo</label>
            <select v-model="form.sistemas.gastrointestinal.bazo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="normal">Normal</option>
              <option value="palpable">Palpable</option>
              <option value="agrandado">Agrandado</option>
            </select>
          </div>
        </div>

        <div v-if="sistema.key === 'genitourinario'" class="form-row mt-3">
          <div class="form-group col-md-6">
            <label>Hallazgos Genitales</label>
            <textarea v-model="form.sistemas.genitourinario.hallazgos_genitales" class="form-control" rows="2" placeholder="Describir hallazgos..."></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>Puño Percusión Renal</label>
            <select v-model="form.sistemas.genitourinario.puño_percusion" class="form-control">
              <option value="">Seleccionar</option>
              <option value="negativa">Negativa (sin dolor)</option>
              <option value="positiva_derecha">Positiva derecha</option>
              <option value="positiva_izquierda">Positiva izquierda</option>
              <option value="positiva_bilateral">Positiva bilateral</option>
            </select>
          </div>
        </div>

        <!-- NOTAS GENERALES DEL SISTEMA -->
        <div class="form-group mt-3">
          <label class="font-weight-bold">Notas Adicionales / Hallazgos Relevantes:</label>
          <textarea v-model="form.sistemas[sistema.key].notas" class="form-control" rows="2" placeholder="Otros hallazgos importantes..."></textarea>
        </div>
      </div>
    </div>

    <!-- MEDICAMENTOS Y ALERGIAS -->
    <div class="card-section">
      <div class="card-header">
        <h3>Medicamentos Actuales y Alergias</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Medicamentos que consume actualmente:</label>
          <textarea v-model="form.medicamentos" class="form-control" rows="3" placeholder="Nombre, dosis, frecuencia..."></textarea>
        </div>
        <div class="form-group">
          <label>Alergias (medicamentos, alimentos, sustancias):</label>
          <textarea v-model="form.alergias" class="form-control" rows="2" placeholder="Especificar alérgeno y tipo de reacción..."></textarea>
        </div>
      </div>
    </div>

    <!-- PRUEBAS/EXÁMENES COMPLEMENTARIOS -->
    <div class="card-section">
      <div class="card-header">
        <h3>Pruebas y Exámenes Complementarios (Si aplican)</h3>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Registre resultados de laboratorios, imagenología, espirometría, electrocardiograma, etc.</p>
        
        <div v-for="(prueba, idx) in form.pruebas_complementarias" :key="idx" class="form-row mb-3 pb-3 border-bottom">
          <div class="form-group col-md-3">
            <label>Tipo de Prueba</label>
            <input type="text" v-model="prueba.tipo" class="form-control" placeholder="Ej: Radiografía de tórax" />
          </div>
          <div class="form-group col-md-3">
            <label>Fecha</label>
            <input type="date" v-model="prueba.fecha" class="form-control" />
          </div>
          <div class="form-group col-md-6">
            <label>Resultado/Hallazgos</label>
            <textarea v-model="prueba.resultado" class="form-control" rows="1" placeholder="Resultado..."></textarea>
          </div>
        </div>

        <button @click="agregarPrueba" class="btn btn-sm btn-outline-primary mt-2">
          <i class="fas fa-plus mr-1"></i> Agregar prueba
        </button>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'ClinícoExamenCompleto',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      sistemas: [
        { key: 'cabeza', nombre: 'Cabeza' },
        { key: 'ojos', nombre: 'Ojos (Agudeza visual, daltonismo, tensión ocular)' },
        { key: 'oidos', nombre: 'Oídos (Audición, acúfenos, vértigo)' },
        { key: 'nariz', nombre: 'Nariz (Epistaxis, obstrucción, tabique)' },
        { key: 'cardiovascular', nombre: 'Cardiovascular (Frecuencia, auscultación, soplos)' },
        { key: 'pulmonar', nombre: 'Pulmonar (Auscultación, tos, espirometría)' },
        { key: 'gastrointestinal', nombre: 'Gastrointestinal (Palpación, ruidos, hígado)' },
        { key: 'abdomen', nombre: 'Abdomen General' },
        { key: 'miembros_superiores', nombre: 'Miembros Superiores' },
        { key: 'miembros_inferiores', nombre: 'Miembros Inferiores' },
        { key: 'genitourinario', nombre: 'Genitourinario' }
      ],
      form: this.modelValue || {
        signos_vitales: {
          fc: '',
          fr: '',
          ta: '',
          temperatura: '',
          saturacion: '',
          glucosa: ''
        },
        antropometria: {
          peso: '',
          talla: '',
          imc: '',
          perimetro_cintura: '',
          perimetro_cadera: ''
        },
        comorbilidades: {
          diabetes: false,
          hipertension: false,
          asma: false,
          cardiopatia: false,
          artritis: false,
          depresion: false,
          ansiedad: false,
          hiperlipidemia: false,
          tiroidea: false,
          otras_presente: false,
          otras: ''
        },
        sistemas: {
          cabeza: { interrogatorio: '', exploracion: '', notas: '' },
          ojos: { 
            interrogatorio: '', exploracion: '', notas: '',
            agudeza_visual_od: '', agudeza_visual_oi: '', ishihara: '', presion_intraocular: ''
          },
          oidos: { 
            interrogatorio: '', exploracion: '', notas: '',
            acufenos: false, mareos: false, audiometria: ''
          },
          nariz: { 
            interrogatorio: '', exploracion: '', notas: '',
            epistaxis: false, obstruccion: false, desviacion_septal: ''
          },
          cardiovascular: { 
            interrogatorio: '', exploracion: '', notas: '',
            auscultacion: '', pulsos_perifericos: '', edemas: '', varices: ''
          },
          pulmonar: { 
            interrogatorio: '', exploracion: '', notas: '',
            auscultacion: '', tos_esputo: '', espirometria: ''
          },
          gastrointestinal: { 
            interrogatorio: '', exploracion: '', notas: '',
            palpacion: '', ruidos_intestinales: '', tamano_higado: '', bazo: ''
          },
          abdomen: { interrogatorio: '', exploracion: '', notas: '' },
          miembros_superiores: { interrogatorio: '', exploracion: '', notas: '' },
          miembros_inferiores: { interrogatorio: '', exploracion: '', notas: '' },
          genitourinario: { 
            interrogatorio: '', exploracion: '', notas: '',
            hallazgos_genitales: '', puño_percusion: ''
          }
        },
        medicamentos: '',
        alergias: '',
        pruebas_complementarias: [
          { tipo: '', fecha: '', resultado: '' }
        ]
      }
    }
  },
  computed: {
    categoriaIMC() {
      if (!this.form.antropometria.imc) return ''
      const imc = parseFloat(this.form.antropometria.imc)
      if (imc < 18.5) return 'Bajo peso'
      if (imc < 25) return 'Peso normal'
      if (imc < 30) return 'Sobrepeso'
      if (imc < 35) return 'Obesidad I'
      if (imc < 40) return 'Obesidad II'
      return 'Obesidad III'
    }
  },
  methods: {
    agregarPrueba() {
      this.form.pruebas_complementarias.push({
        tipo: '',
        fecha: '',
        resultado: ''
      })
    },
    calcularIMC() {
      const peso = parseFloat(this.form.antropometria.peso)
      const talla = parseFloat(this.form.antropometria.talla) / 100
      if (peso && talla) {
        this.form.antropometria.imc = (peso / (talla * talla)).toFixed(1)
      }
    }
  },
  watch: {
    modelValue(newVal) {
      this.form = newVal
    },
    form: {
      handler(newVal) {
        this.$emit('update:modelValue', newVal)
      },
      deep: true
    },
    'form.antropometria.peso'() {
      this.calcularIMC()
    },
    'form.antropometria.talla'() {
      this.calcularIMC()
    }
  }
}
</script>

<style scoped>
.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1rem;
}

.section-header i {
  font-size: 24px;
  color: #5F6E7E;
}

.section-header h2 {
  font-size: 18px;
  font-weight: 500;
  color: #1F2937;
  margin: 0;
}

.card-section {
  background: #FFFFFF;
  border: 0.5px solid #E5E7EB;
  border-radius: 6px;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%);
  padding: 12px 20px;
}

.card-header h3 {
  font-size: 14px;
  font-weight: 500;
  color: #FFFFFF;
  margin: 0;
}

.card-body {
  padding: 20px;
}

.form-control {
  padding: 10px 12px;
  border: 0.5px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
}

.custom-control-label {
  font-size: 14px;
  color: #1F2937;
}

.font-weight-bold {
  font-weight: 600;
}
</style>
