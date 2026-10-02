<template>
  <div class="hoja2-antecedentessalud-tr">
    <div class="alert alert-info">
      <i class="fas fa-info-circle mr-2"></i>
      <strong>Instrucciones:</strong> Marque las patologías presentes o antecedentes del paciente. Estos datos son críticos para determinar aptitud en trabajos de alto riesgo.
    </div>

    <!-- SECTION: PATOLOGÍA CORONARIA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-heart mr-2"></i> PATOLOGÍA CORONARIA (14 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.coronaria" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id].activo" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <div v-if="localData[patologia.id].activo && patologia.hasEvolucion" class="ml-4 mt-1">
              <div class="row">
                <div class="col-md-6">
                  <label class="small font-weight-bold">Evolución:</label>
                  <input type="text" class="form-control form-control-sm" v-model="localData[patologia.id].evolucion" @change="emitChange" placeholder="Ej: 5 años">
                </div>
                <div class="col-md-6">
                  <label class="small font-weight-bold">En control:</label>
                  <select class="form-control form-control-sm" v-model="localData[patologia.id].enControl" @change="emitChange">
                    <option value="">-- Seleccionar --</option>
                    <option value="Si">Sí</option>
                    <option value="No">No</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA VASCULAR -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-wave-square mr-2"></i> PATOLOGÍA VASCULAR
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.vascular" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA METABÓLICA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-weight mr-2"></i> PATOLOGÍA METABÓLICA
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData.obesidad.activo" @change="emitChange">
              <span class="custom-control-label">Obesidad</span>
            </label>
            <div v-if="localData.obesidad.activo" class="ml-4 mt-1">
              <label class="small font-weight-bold">Grado:</label>
              <select class="form-control form-control-sm" v-model="localData.obesidad.grado" @change="emitChange">
                <option value="">-- Seleccionar --</option>
                <option value="I">Grado I</option>
                <option value="II">Grado II</option>
                <option value="III">Grado III</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA NEUROLÓGICA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-brain mr-2"></i> PATOLOGÍA NEUROLÓGICA (14 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.neurologica" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA RESPIRATORIA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-lungs mr-2"></i> PATOLOGÍA RESPIRATORIA (7 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.respiratoria" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA OSTEOMUSCULAR -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-bone mr-2"></i> PATOLOGÍA OSTEOMUSCULAR (8 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.osteomuscular" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA VISUAL -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-eye mr-2"></i> PATOLOGÍA VISUAL (12 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.visual" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA AUDITIVA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-deaf mr-2"></i> PATOLOGÍA AUDITIVA (5 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.auditiva" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA HEMATOPOYÉTICA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-tint mr-2"></i> PATOLOGÍA HEMATOPOYÉTICA (6 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.hematopoyetica" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA GENITOURINARIA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-kidney mr-2"></i> PATOLOGÍA GENITOURINARIA (4 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.genitourinaria" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA DERMATOLÓGICA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-hand-paper mr-2"></i> PATOLOGÍA DERMATOLÓGICA (2 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.dermatologica" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA ENDOCRINOLÓGICA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-syringe mr-2"></i> PATOLOGÍA ENDOCRINOLÓGICA (5 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.endocrinologica" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA DIGESTIVA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-stomach mr-2"></i> PATOLOGÍA DIGESTIVA (6 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.digestiva" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: PATOLOGÍA PSIQUIÁTRICA / PSICOLÓGICA -->
    <div class="card mb-3">
      <div class="card-header" style="background: linear-gradient(135deg, #0c5460 0%, #063c48 100%); color: white;">
        <h5 class="mb-0">
          <i class="fas fa-user-md mr-2"></i> PATOLOGÍA PSIQUIÁTRICA / PSICOLÓGICA (9 elementos)
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3" v-for="patologia in patologias.ppsiquiatrica" :key="patologia.id">
            <label class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" v-model="localData[patologia.id]" @change="emitChange">
              <span class="custom-control-label">{{ patologia.label }}</span>
            </label>
            <small class="text-muted d-block ml-4">{{ patologia.desc }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- RESUMEN -->
    <div class="card mt-3">
      <div class="card-body">
        <h6 class="font-weight-bold">📊 RESUMEN DE PATOLOGÍAS MARCADAS</h6>
        <div v-if="patologiasActivas.length === 0" class="alert alert-success mb-0">
          ✓ Sin antecedentes patológicos relevantes
        </div>
        <div v-else class="alert alert-warning mb-0">
          <strong>Total de patologías detectadas:</strong> {{ patologiasActivas.length }}
          <div class="mt-2">
            <span v-for="pat in patologiasActivas" :key="pat" class="badge badge-warning mr-2 mb-1">{{ pat }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Hoja2AntecedentessaludTr',
  props: {
    modelValue: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      localData: {
        // CORONARIA - con evolución/control
        marcapaso: { activo: false },
        valvulopatias: { activo: false },
        hipertension_arterial: { activo: false },
        diabetes: { activo: false, evolucion: '', enControl: '' },
        taquicardia: { activo: false },
        protesis_valvulares: { activo: false },
        insuficiencia_vascular: { activo: false },
        cardiopatia_congenita: { activo: false },
        lesiones_vasculares: { activo: false },
        dislipidemia: { activo: false, evolucion: '', enControl: '' },
        arritmias: { activo: false },
        cardiopatia_isquemica: { activo: false },
        trombosis: { activo: false, enControl: '' },
        insuficiencia_cardiaca: { activo: false },
        // VASCULAR
        varices: false,
        trombosis_venosa: false,
        // METABÓLICA
        obesidad: { activo: false, grado: '' },
        // NEUROLÓGICA
        insomnio: false,
        convulsion: false,
        evc: false,
        letargo: false,
        paralisis: false,
        tce: false,
        migrana: false,
        hipercinesia: false,
        epilepsia: false,
        hipoestesia: false,
        sincope: false,
        hiperestesia: false,
        paresia: false,
        parestesia: false,
        // RESPIRATORIA
        asma: false,
        disnea: false,
        epoc: false,
        apnea_sueno: false,
        infectocontagiosos: false,
        tromboembolia_pulmonar: false,
        neumopatias_intersticiales: false,
        // OSTEOMUSCULAR
        artropatias: false,
        lesiones_columna: false,
        amputacion: false,
        arcos_movimiento_limitados: false,
        deformidad: false,
        espasticidad: false,
        rigidez: false,
        hipotonia: false,
        // VISUAL
        catarata: false,
        discromatopsia: false,
        glaucoma: false,
        ptosis_palpebral: false,
        estrabismo: false,
        ceguera_nocturna: false,
        retinopatia: false,
        estereopsis_anormal: false,
        diplopia: false,
        campimetria_anormal: false,
        nistagmo: false,
        movimientos_oculares: false,
        // AUDITIVA
        hipoacusia_severa: false,
        trauma_acustico: false,
        vertigo_equilibrio: false,
        tinnitus_acufeno: false,
        anacusia: false,
        // HEMATOPOYÉTICA
        anemia_grave: false,
        policitemia_vera: false,
        neutropenia: false,
        neoplasias: false,
        sx_mielodisplasicos: false,
        trastornos_plaquetas: false,
        // GENITOURINARIA
        enf_renal_cronica: false,
        pielonefritis: false,
        litiasis_renal: false,
        incontinencia_severa: false,
        // DERMATOLÓGICA
        dermatitis: false,
        lesiones_premalignas: false,
        // ENDOCRINOLÓGICA
        infeccion_act: false,
        hipertiroidismo: false,
        hipotiroidismo_sev: false,
        enf_addison: false,
        sx_cushing: false,
        // DIGESTIVA
        gastroparesia_sev: false,
        erge_sii_severo: false,
        ulcera_peptica: false,
        enf_crohn_activa: false,
        hepatopatia_grave: false,
        diverticulitis_aguda: false,
        // PSIQUIÁTRICA / PSICOLÓGICA
        trastorno_psicotico: false,
        trastorno_bipolar: false,
        depresion_mayor: false,
        trastorno_sueno_vigilia: false,
        trastorno_estres_post: false,
        ansiedad_generalizada: false,
        trastorno_personalidad: false,
        abuso_dependencia_sust: false,
        deficit_cognitivo: false
      },
      patologias: {
        coronaria: [
          { id: 'marcapaso', label: 'Marcapasos', hasEvolucion: false },
          { id: 'valvulopatias', label: 'Valvulopatías', hasEvolucion: false },
          { id: 'hipertension_arterial', label: 'Hipertensión arterial', hasEvolucion: false },
          { id: 'diabetes', label: 'Diabetes', hasEvolucion: true },
          { id: 'taquicardia', label: 'Taquicardia', hasEvolucion: false },
          { id: 'protesis_valvulares', label: 'Prótesis valvulares', hasEvolucion: false },
          { id: 'insuficiencia_vascular', label: 'Insuficiencia vascular', hasEvolucion: false },
          { id: 'cardiopatia_congenita', label: 'Cardiopatía congénita', hasEvolucion: false },
          { id: 'lesiones_vasculares', label: 'Lesiones vasculares', hasEvolucion: false },
          { id: 'dislipidemia', label: 'Dislipidemia', hasEvolucion: true },
          { id: 'arritmias', label: 'Arritmias', hasEvolucion: false },
          { id: 'cardiopatia_isquemica', label: 'Cardiopatía isquémica', hasEvolucion: false },
          { id: 'trombosis', label: 'Trombosis', hasEvolucion: false },
          { id: 'insuficiencia_cardiaca', label: 'Insuficiencia cardíaca', hasEvolucion: false }
        ],
        vascular: [
          { id: 'varices', label: 'Várices', desc: 'Dilatación venosa' },
          { id: 'trombosis_venosa', label: 'Trombosis venosa', desc: 'Coágulo en vena' }
        ],
        neurologica: [
          { id: 'insomnio', label: 'Insomnio', desc: 'Trastorno del sueño' },
          { id: 'convulsion', label: 'Convulsión', desc: 'Crisis convulsivas' },
          { id: 'evc', label: 'EVC', desc: 'Evento cerebrovascular' },
          { id: 'letargo', label: 'Letargo', desc: 'Fatiga extrema' },
          { id: 'paralisis', label: 'Parálisis', desc: 'Pérdida de movimiento' },
          { id: 'tce', label: 'TCE', desc: 'Traumatismo craneoencefálico' },
          { id: 'migrana', label: 'Migraña', desc: 'Cefalea severa' },
          { id: 'hipercinesia', label: 'Hipercinesia', desc: 'Hiperactividad' },
          { id: 'epilepsia', label: 'Epilepsia', desc: 'Trastorno convulsivo' },
          { id: 'hipoestesia', label: 'Hipoestesia', desc: 'Disminución sensibilidad' },
          { id: 'sincope', label: 'Síncope', desc: 'Pérdida de conciencia' },
          { id: 'hiperestesia', label: 'Hiperestesia', desc: 'Sensibilidad aumentada' },
          { id: 'paresia', label: 'Paresia', desc: 'Debilidad muscular parcial' },
          { id: 'parestesia', label: 'Parestesia', desc: 'Hormigueo / adormecimiento' }
        ],
        respiratoria: [
          { id: 'asma', label: 'Asma', desc: 'Enfermedad obstructiva' },
          { id: 'disnea', label: 'Disnea', desc: 'Dificultad respiratoria' },
          { id: 'epoc', label: 'EPOC', desc: 'Enfermedad pulmonar obstructiva crónica' },
          { id: 'apnea_sueno', label: 'Apnea del sueño', desc: 'Pausas respiratorias' },
          { id: 'infectocontagiosos', label: 'Infectocontagiosos', desc: 'Enfermedades infecciosas' },
          { id: 'tromboembolia_pulmonar', label: 'Tromboembolia pulmonar', desc: 'Coágulo en pulmón' },
          { id: 'neumopatias_intersticiales', label: 'Neumopatías intersticiales', desc: 'Enfermedad pulmonar intersticial' }
        ],
        osteomuscular: [
          { id: 'artropatias', label: 'Artropatías', desc: 'Enfermedad articular' },
          { id: 'lesiones_columna', label: 'Lesiones de columna', desc: 'Afectación vertebral' },
          { id: 'amputacion', label: 'Amputación', desc: 'Pérdida de extremidad' },
          { id: 'arcos_movimiento_limitados', label: 'Arcos de movimiento limitados', desc: 'Restricción movilidad' },
          { id: 'deformidad', label: 'Deformidad', desc: 'Alteración estructural' },
          { id: 'espasticidad', label: 'Espasticidad', desc: 'Rigidez muscular' },
          { id: 'rigidez', label: 'Rigidez', desc: 'Dificultad de movimiento' },
          { id: 'hipotonia', label: 'Hipotonía', desc: 'Bajo tono muscular' }
        ],
        visual: [
          { id: 'catarata', label: 'Catarata', desc: 'Opacidad del cristalino' },
          { id: 'discromatopsia', label: 'Discromatopsia', desc: 'Deficiencia de color' },
          { id: 'glaucoma', label: 'Glaucoma', desc: 'Presión intraocular elevada' },
          { id: 'ptosis_palpebral', label: 'Ptosis palpebral', desc: 'Caída del párpado' },
          { id: 'estrabismo', label: 'Estrabismo', desc: 'Desalineación ocular' },
          { id: 'ceguera_nocturna', label: 'Ceguera nocturna', desc: 'Visión pobre en oscuridad' },
          { id: 'retinopatia', label: 'Retinopatía', desc: 'Enfermedad retiniana' },
          { id: 'estereopsis_anormal', label: 'Estereopsis anormal', desc: 'Percepción profundidad deficiente' },
          { id: 'diplopia', label: 'Diplopía', desc: 'Visión doble' },
          { id: 'campimetria_anormal', label: 'Campimetría anormal', desc: 'Campo visual limitado' },
          { id: 'nistagmo', label: 'Nistagmo', desc: 'Movimiento ocular involuntario' },
          { id: 'movimientos_oculares', label: 'Movimientos oculares alterados', desc: 'Alteración motilidad' }
        ],
        auditiva: [
          { id: 'hipoacusia_severa', label: 'Hipoacusia severa o profunda', desc: 'Pérdida auditiva severa' },
          { id: 'trauma_acustico', label: 'Trauma acústico agudo/crónico', desc: 'Daño por ruido' },
          { id: 'vertigo_equilibrio', label: 'Vértigo / Alteraciones del equilibrio', desc: 'Trastorno vestibular' },
          { id: 'tinnitus_acufeno', label: 'Tinnitus o acúfeno severo', desc: 'Zumbido en oídos' },
          { id: 'anacusia', label: 'Anacusia', desc: 'Pérdida total de audición' }
        ],
        hematopoyetica: [
          { id: 'anemia_grave', label: 'Anemia grave-crónica', desc: 'Déficit hemoglobina' },
          { id: 'policitemia_vera', label: 'Policitemia vera', desc: 'Exceso de glóbulos rojos' },
          { id: 'neutropenia', label: 'Neutropenia mod-sev / Granulocitopenia', desc: 'Déficit neutrófilos' },
          { id: 'neoplasias', label: 'Neoplasias (Leucemia-Linfoma-Mieloma)', desc: 'Cáncer hematológico' },
          { id: 'sx_mielodisplasicos', label: 'Síndrome mielodisplásicos (Citopenias)', desc: 'Trastorno médula ósea' },
          { id: 'trastornos_plaquetas', label: 'Trastornos de plaquetas y coagulación', desc: 'Problemas coagulativos' }
        ],
        genitourinaria: [
          { id: 'enf_renal_cronica', label: 'Enfermedad renal crónica', desc: 'Insuficiencia renal' },
          { id: 'pielonefritis', label: 'Pielonefritis', desc: 'Infección renal' },
          { id: 'litiasis_renal', label: 'Litiasis renal activa', desc: 'Cálculos renales' },
          { id: 'incontinencia_severa', label: 'Incontinencia urinaria severa', desc: 'Pérdida de orina' }
        ],
        dermatologica: [
          { id: 'dermatitis', label: 'Dermatitis', desc: 'Inflamación de piel' },
          { id: 'lesiones_premalignas', label: 'Lesiones premalignas o malignas', desc: 'Lesiones cutáneas graves' }
        ],
        endocrinologica: [
          { id: 'infeccion_act', label: 'Infección ACT', desc: 'Infección adrenocorticotropa' },
          { id: 'hipertiroidismo', label: 'Hipertiroidismo', desc: 'Tiroides hiperactiva' },
          { id: 'hipotiroidismo_sev', label: 'Hipotiroidismo severo', desc: 'Tiroides hipoactiva' },
          { id: 'enf_addison', label: 'Enfermedad de Addison', desc: 'Insuficiencia suprarrenal' },
          { id: 'sx_cushing', label: 'Síndrome de Cushing', desc: 'Exceso de cortisol' }
        ],
        digestiva: [
          { id: 'gastroparesia_sev', label: 'Gastroparesia severa', desc: 'Parálisis gástrica' },
          { id: 'erge_sii_severo', label: 'ERGE / SII severo', desc: 'Reflujo o síndrome intestino irritable' },
          { id: 'ulcera_peptica', label: 'Úlcera péptica activa', desc: 'Úlcera gastroduodenal' },
          { id: 'enf_crohn_activa', label: 'Enfermedad de Crohn activa', desc: 'Inflamación digestiva' },
          { id: 'hepatopatia_grave', label: 'Hepatopatía grave', desc: 'Enfermedad hepática severa' },
          { id: 'diverticulitis_aguda', label: 'Diverticulitis aguda', desc: 'Inflamación divertículos' }
        ],
        ppsiquiatrica: [
          { id: 'trastorno_psicotico', label: 'Trastorno psicótico', desc: 'Psicosis' },
          { id: 'trastorno_bipolar', label: 'Trastorno bipolar', desc: 'Cambios de ánimo' },
          { id: 'depresion_mayor', label: 'Depresión mayor', desc: 'Depresión severa' },
          { id: 'trastorno_sueno_vigilia', label: 'Trastorno sueño/vigilia', desc: 'Alteración ciclo sueño' },
          { id: 'trastorno_estres_post', label: 'Trastorno de estrés postraumático', desc: 'TEPT' },
          { id: 'ansiedad_generalizada', label: 'Ansiedad generalizada', desc: 'Ansiedad crónica' },
          { id: 'trastorno_personalidad', label: 'Trastorno de personalidad', desc: 'Alteración personalidad' },
          { id: 'abuso_dependencia_sust', label: 'Abuso/dependencia de sustancias', desc: 'Toxicomanías' },
          { id: 'deficit_cognitivo', label: 'Déficit cognitivo', desc: 'Deterioro cognitivo' }
        ]
      }
    }
  },
  computed: {
    patologiasActivas() {
      const etiquetas = []
      // Recorrer grupos simples (booleanos)
      Object.keys(this.patologias).forEach(grupo => {
        if (grupo === 'coronaria') {
          this.patologias[grupo].forEach(pat => {
            if (this.localData[pat.id] && this.localData[pat.id].activo) {
              etiquetas.push(pat.label)
            }
          })
        } else {
          this.patologias[grupo].forEach(pat => {
            if (this.localData[pat.id]) {
              etiquetas.push(pat.label)
            }
          })
        }
      })
      // Metabólica
      if (this.localData.obesidad.activo) {
        etiquetas.push('Obesidad' + (this.localData.obesidad.grado ? ' Grado ' + this.localData.obesidad.grado : ''))
      }
      return etiquetas
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.localData = { ...this.localData, ...newVal }
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
      this.localData = { ...this.localData, ...this.modelValue }
    }
  }
}
</script>

<style scoped>
.hoja2-antecedentessalud-tr {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.custom-control {
  position: relative;
  display: block;
  min-height: 1.5rem;
  padding-left: 1.5rem;
  margin-bottom: 0.75rem;
}

.custom-control-label {
  cursor: pointer;
  font-weight: 500;
  color: #2c3e50;
  user-select: none;
}

.custom-control-label:hover {
  color: #0c5460;
}

.custom-control-input:checked ~ .custom-control-label {
  color: #0c5460;
  font-weight: 600;
}

.alert {
  font-size: 12px;
  margin-bottom: 15px;
  border-radius: 4px;
}

.badge {
  font-size: 11px;
  padding: 4px 8px;
}

small {
  font-size: 12px;
  display: block;
  margin-top: 2px;
}

h6 {
  font-size: 13px;
  color: #2c3e50;
}
</style>