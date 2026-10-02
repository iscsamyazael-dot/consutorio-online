<template>
  <div class="clinico-examen">

    <!-- ===================== 3.1 SIGNOS VITALES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-heartbeat mr-2"></i>3.1 Signos vitales</span>
        <button
          v-if="datosPuesto"
          type="button"
          class="btn btn-sm btn-header"
          title="Usa los signos vitales capturados en Datos del puesto"
          @click="copiarSignosVitales"
        >
          <i class="fas fa-copy mr-1"></i>Copiar de datos del puesto
        </button>
      </div>
      <div class="card-body">
        <div class="form-row mb-n3">
          <div class="form-group col-md-2">
            <label>FC (lat/min)</label>
            <input v-model="local.signos_vitales.fc" type="number" min="30" max="220" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>FR (resp/min)</label>
            <input v-model="local.signos_vitales.fr" type="number" min="6" max="60" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>TA (mmHg)</label>
            <input v-model.trim="local.signos_vitales.ta" type="text" class="form-control" placeholder="120/80">
          </div>
          <div class="form-group col-md-2">
            <label>Temperatura (°C)</label>
            <input v-model="local.signos_vitales.temperatura" type="number" min="34" max="42" step="0.1" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>SatO₂ (%)</label>
            <input v-model="local.signos_vitales.saturacion" type="number" min="50" max="100" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Glucosa (mg/dL)</label>
            <input v-model="local.signos_vitales.glucosa" type="number" min="0" class="form-control">
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 3.2 ANTROPOMETRÍA ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-weight mr-2"></i>3.2 Antropometría</span>
      </div>
      <div class="card-body">
        <p class="instruccion">El IMC se calcula solo al capturar peso y talla.</p>
        <div class="form-row mb-n3">
          <div class="form-group col-md-2">
            <label>Peso (kg)</label>
            <input v-model="local.antropometria.peso" type="number" min="0" step="0.1" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Talla (cm)</label>
            <input v-model="local.antropometria.talla" type="number" min="0" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>IMC (kg/m²)</label>
            <input :value="local.antropometria.imc" type="text" class="form-control" readonly>
          </div>
          <div class="form-group col-md-2">
            <label>Categoría IMC</label>
            <input :value="categoriaIMC" type="text" class="form-control" :class="claseIMC" readonly>
          </div>
          <div class="form-group col-md-2">
            <label>Cintura (cm)</label>
            <input v-model="local.antropometria.perimetro_cintura" type="number" min="0" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Cadera (cm)</label>
            <input v-model="local.antropometria.perimetro_cadera" type="number" min="0" class="form-control">
          </div>
        </div>

        <div class="form-row mb-n3">
          <div class="form-group col-md-6">
            <label>Complexión física</label>
            <select v-model="local.antropometria.complexion_fisica" class="form-control">
              <option value="">Seleccionar</option>
              <option value="ectomorfo">Ectomorfo</option>
              <option value="mesomorfo">Mesomorfo</option>
              <option value="endomorfo">Endomorfo</option>
            </select>
          </div>
          <div class="form-group col-md-6">
            <label>Estado nutricional</label>
            <input :value="estadoNutricional" type="text" class="form-control" :class="claseEstadoNutricional" readonly>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 3.3 COMORBILIDADES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-clipboard-list mr-2"></i>3.3 Comorbilidades identificadas</span>
        <span class="contador">{{ totalComorbilidades }} marcadas</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Marque las comorbilidades presentes al momento del examen.</p>
        <div class="opciones-grid">
          <label
            v-for="op in comorbilidades"
            :key="op.key"
            class="opcion"
            :class="{ activa: local.comorbilidades[op.key] }"
          >
            <input v-model="local.comorbilidades[op.key]" type="checkbox">
            <span>{{ op.label }}</span>
          </label>

          <div class="opcion-detalle" :class="{ activa: local.comorbilidades.otras_presente }">
            <label class="opcion">
              <input v-model="local.comorbilidades.otras_presente" type="checkbox">
              <span>Otras</span>
            </label>
            <div v-if="local.comorbilidades.otras_presente" class="detalle">
              <input
                v-model.trim="local.comorbilidades.otras"
                type="text"
                class="form-control"
                placeholder="Especificar…"
                aria-label="Otras comorbilidades"
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 3.4 EXPLORACIÓN POR SISTEMAS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-user-md mr-2"></i>3.4 Exploración física por sistemas</span>
      </div>
      <div class="card-body">
        <p class="instruccion">
          Registre los síntomas referidos y los hallazgos de cada sistema. Marque los signos presentes cuando aplique.
        </p>

        <div v-for="(sistema, idx) in sistemas" :key="sistema.key" class="sistema-bloque">
          <div class="sistema-titulo">
            <span class="sistema-numero">{{ idx + 1 }}</span>
            <div>
              <strong>{{ sistema.nombre }}</strong>
              <small v-if="sistema.pista" class="d-block text-muted">{{ sistema.pista }}</small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Interrogatorio (síntomas referidos)</label>
              <textarea v-model="local.sistemas[sistema.key].interrogatorio" class="form-control" rows="2" placeholder="Síntomas que refiere el trabajador…"></textarea>
            </div>
            <div class="form-group col-md-6">
              <label>Exploración física (hallazgos)</label>
              <textarea v-model="local.sistemas[sistema.key].exploracion" class="form-control" rows="2" placeholder="Resultados del examen físico…"></textarea>
            </div>
          </div>

          <!-- OJOS -->
          <div v-if="sistema.key === 'ojos'" class="form-row">
            <div class="form-group col-md-3">
              <label>Agudeza visual OD (sin corrección)</label>
              <input v-model.trim="local.sistemas.ojos.agudeza_visual_od" type="text" class="form-control" placeholder="Ej: 20/20">
            </div>
            <div class="form-group col-md-3">
              <label>Agudeza visual OI (sin corrección)</label>
              <input v-model.trim="local.sistemas.ojos.agudeza_visual_oi" type="text" class="form-control" placeholder="Ej: 20/20">
            </div>
            <div class="form-group col-md-3">
              <label>Test de Ishihara</label>
              <select v-model="local.sistemas.ojos.ishihara" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normal">Normal</option>
                <option value="protanopia">Protanopia</option>
                <option value="deuteranopia">Deuteranopia</option>
                <option value="tritanopia">Tritanopia</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Presión intraocular (mmHg)</label>
              <input v-model="local.sistemas.ojos.presion_intraocular" type="number" min="0" class="form-control">
            </div>
          </div>

          <!-- OÍDOS -->
          <div v-if="sistema.key === 'oidos'" class="form-row">
            <div class="form-group col-md-6">
              <label>Marque si presenta</label>
              <div class="opciones-grid compacto">
                <label class="opcion" :class="{ activa: local.sistemas.oidos.acufenos }">
                  <input v-model="local.sistemas.oidos.acufenos" type="checkbox">
                  <span>Acúfenos (zumbido)</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.oidos.mareos }">
                  <input v-model="local.sistemas.oidos.mareos" type="checkbox">
                  <span>Mareos/vértigo</span>
                </label>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Audiometría (si aplica)</label>
              <input v-model.trim="local.sistemas.oidos.audiometria" type="text" class="form-control" placeholder="Resultado…">
            </div>
          </div>

          <!-- NARIZ -->
          <div v-if="sistema.key === 'nariz'" class="form-row">
            <div class="form-group col-md-6">
              <label>Marque si presenta</label>
              <div class="opciones-grid compacto">
                <label class="opcion" :class="{ activa: local.sistemas.nariz.epistaxis }">
                  <input v-model="local.sistemas.nariz.epistaxis" type="checkbox">
                  <span>Epistaxis (sangrado)</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.nariz.obstruccion }">
                  <input v-model="local.sistemas.nariz.obstruccion" type="checkbox">
                  <span>Obstrucción nasal</span>
                </label>
              </div>
            </div>
            <div class="form-group col-md-3">
              <label>Desviación septal</label>
              <select v-model="local.sistemas.nariz.desviacion_septal" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no">No</option>
                <option value="leve">Leve</option>
                <option value="moderada">Moderada</option>
                <option value="severa">Severa</option>
              </select>
            </div>
          </div>

          <!-- CARDIOVASCULAR -->
          <div v-if="sistema.key === 'cardiovascular'" class="form-row">
            <div class="form-group col-md-3">
              <label>Auscultación cardíaca</label>
              <select v-model="local.sistemas.cardiovascular.auscultacion" class="form-control">
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
              <select v-model="local.sistemas.cardiovascular.pulsos_perifericos" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normales">Normales</option>
                <option value="debiles">Débiles</option>
                <option value="aumentados">Aumentados</option>
                <option value="asimetricos">Asimétricos</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Edemas periféricos</label>
              <select v-model="local.sistemas.cardiovascular.edemas" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no">No</option>
                <option value="leve">Leve</option>
                <option value="moderado">Moderado</option>
                <option value="severo">Severo</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Várices</label>
              <select v-model="local.sistemas.cardiovascular.varices" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no">No</option>
                <option value="miembros_inferiores">Miembros inferiores</option>
                <option value="miembros_superiores">Miembros superiores</option>
                <option value="generalizadas">Generalizadas</option>
              </select>
            </div>
          </div>

          <!-- PULMONAR -->
          <div v-if="sistema.key === 'pulmonar'" class="form-row">
            <div class="form-group col-md-4">
              <label>Auscultación pulmonar</label>
              <select v-model="local.sistemas.pulmonar.auscultacion" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normal">Normal</option>
                <option value="sibilancias">Sibilancias</option>
                <option value="estertores">Estertores</option>
                <option value="disminuido">Murmullo disminuido</option>
                <option value="bronquial">Murmullo bronquial</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>Tos / esputo</label>
              <select v-model="local.sistemas.pulmonar.tos_esputo" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no">No</option>
                <option value="ocasional">Ocasional</option>
                <option value="frecuente">Frecuente</option>
                <option value="productiva">Productiva</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label>Espirometría (si aplica)</label>
              <input v-model.trim="local.sistemas.pulmonar.espirometria" type="text" class="form-control" placeholder="FEV1/FVC…">
            </div>
          </div>

          <!-- GASTROINTESTINAL -->
          <div v-if="sistema.key === 'gastrointestinal'" class="form-row">
            <div class="form-group col-md-3">
              <label>Palpación abdominal</label>
              <select v-model="local.sistemas.gastrointestinal.palpacion" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normal">Normal</option>
                <option value="sensibilidad_epigastrica">Sensibilidad epigástrica</option>
                <option value="masas">Masas palpables</option>
                <option value="distension">Distensión</option>
                <option value="defensa">Defensa muscular</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Ruidos intestinales</label>
              <select v-model="local.sistemas.gastrointestinal.ruidos_intestinales" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normales">Normales</option>
                <option value="aumentados">Aumentados</option>
                <option value="disminuidos">Disminuidos</option>
                <option value="ausentes">Ausentes</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Tamaño del hígado</label>
              <select v-model="local.sistemas.gastrointestinal.tamano_higado" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normal">Normal</option>
                <option value="hepatomegalia_leve">Hepatomegalia leve</option>
                <option value="hepatomegalia_moderada">Hepatomegalia moderada</option>
                <option value="hepatomegalia_severa">Hepatomegalia severa</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Bazo</label>
              <select v-model="local.sistemas.gastrointestinal.bazo" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normal">Normal</option>
                <option value="palpable">Palpable</option>
                <option value="agrandado">Agrandado</option>
              </select>
            </div>
          </div>

          <!-- GENITOURINARIO -->
          <div v-if="sistema.key === 'genitourinario'" class="form-row">
            <div class="form-group col-md-8">
              <label>Hallazgos genitales</label>
              <input v-model.trim="local.sistemas.genitourinario.hallazgos_genitales" type="text" class="form-control" placeholder="Describir hallazgos…">
            </div>
            <div class="form-group col-md-4">
              <label>Puño percusión renal</label>
              <select v-model="local.sistemas.genitourinario.puño_percusion" class="form-control">
                <option value="">Seleccionar</option>
                <option value="negativa">Negativa (sin dolor)</option>
                <option value="positiva_derecha">Positiva derecha</option>
                <option value="positiva_izquierda">Positiva izquierda</option>
                <option value="positiva_bilateral">Positiva bilateral</option>
              </select>
            </div>
          </div>

          <!-- CUELLO -->
          <div v-if="sistema.key === 'cuello'" class="form-row">
            <div class="form-group col-md-3">
              <label>Tiroides</label>
              <select v-model="local.sistemas.cuello.tiroides" class="form-control">
                <option value="">Seleccionar</option>
                <option value="normal">Normal</option>
                <option value="bocio">Bocio</option>
                <option value="nodulo">Nódulo</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Tráquea</label>
              <select v-model="local.sistemas.cuello.traquea" class="form-control">
                <option value="">Seleccionar</option>
                <option value="centrada">Centrada</option>
                <option value="desviada">Desviada</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Adenopatías</label>
              <select v-model="local.sistemas.cuello.adenopatias" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no_palpables">No palpables</option>
                <option value="palpables">Palpables</option>
              </select>
            </div>
            <div class="form-group col-md-3">
              <label>Neoplasias</label>
              <select v-model="local.sistemas.cuello.neoplasias" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no">No</option>
                <option value="si">Sí</option>
              </select>
            </div>
          </div>

          <!-- PIEL Y TEGUMENTOS -->
          <div v-if="sistema.key === 'piel'" class="form-row">
            <div class="form-group col-md-6">
              <label>Marque si presenta</label>
              <div class="opciones-grid compacto">
                <label class="opcion" :class="{ activa: local.sistemas.piel.dermatitis }">
                  <input v-model="local.sistemas.piel.dermatitis" type="checkbox">
                  <span>Dermatitis</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.piel.verrugas }">
                  <input v-model="local.sistemas.piel.verrugas" type="checkbox">
                  <span>Verrugas</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.piel.nevos }">
                  <input v-model="local.sistemas.piel.nevos" type="checkbox">
                  <span>Nevos</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.piel.nodulos }">
                  <input v-model="local.sistemas.piel.nodulos" type="checkbox">
                  <span>Nódulos</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.piel.acne }">
                  <input v-model="local.sistemas.piel.acne" type="checkbox">
                  <span>Acné</span>
                </label>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Exploración física (hallazgos)</label>
              <textarea v-model="local.sistemas.piel.exploracion" class="form-control" rows="2" placeholder="Resultados del examen físico…"></textarea>
            </div>
          </div>

          <!-- INMUNOLÓGICO -->
          <div v-if="sistema.key === 'inmunologico'" class="form-row">
            <div class="form-group col-md-6">
              <label>Marque si presenta</label>
              <div class="opciones-grid compacto">
                <label class="opcion" :class="{ activa: local.sistemas.inmunologico.lupus }">
                  <input v-model="local.sistemas.inmunologico.lupus" type="checkbox">
                  <span>Lupus</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.inmunologico.vih }">
                  <input v-model="local.sistemas.inmunologico.vih" type="checkbox">
                  <span>VIH</span>
                </label>
                <label class="opcion" :class="{ activa: local.sistemas.inmunologico.artritis_reumatoide }">
                  <input v-model="local.sistemas.inmunologico.artritis_reumatoide" type="checkbox">
                  <span>Artritis reumatoide</span>
                </label>
              </div>
            </div>
            <div class="form-group col-md-6">
              <label>Exploración física (hallazgos)</label>
              <textarea v-model="local.sistemas.inmunologico.exploracion" class="form-control" rows="2" placeholder="Resultados del examen físico…"></textarea>
            </div>
          </div>

          <div class="form-group mb-0">
            <label>Notas adicionales</label>
            <input v-model.trim="local.sistemas[sistema.key].notas" type="text" class="form-control" placeholder="Otros hallazgos relevantes…">
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 3.5 MEDICAMENTOS Y ALERGIAS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-pills mr-2"></i>3.5 Medicamentos actuales y alergias</span>
      </div>
      <div class="card-body">
        <div class="form-row mb-n3">
          <div class="form-group col-md-6">
            <label>Medicamentos que consume actualmente</label>
            <textarea v-model="local.medicamentos" class="form-control" rows="3" placeholder="Nombre, dosis, frecuencia…"></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>Alergias</label>
            <textarea v-model="local.alergias" class="form-control" rows="3" placeholder="Medicamentos, alimentos, sustancias y tipo de reacción…"></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 3.6 PRUEBAS COMPLEMENTARIAS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-vials mr-2"></i>3.6 Pruebas y exámenes complementarios</span>
        <button type="button" class="btn btn-sm btn-header" @click="agregarPrueba">
          <i class="fas fa-plus mr-1"></i>Agregar prueba
        </button>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre laboratorios, imagenología, espirometría, electrocardiograma u otros estudios, si aplican.</p>

        <p v-if="!local.pruebas_complementarias.length" class="estado-vacio">
          Sin pruebas registradas. Usa “Agregar prueba” para capturar un estudio.
        </p>

        <div v-for="(prueba, i) in local.pruebas_complementarias" :key="i" class="form-row prueba-row">
          <div class="form-group col-md-4">
            <label>Tipo de prueba</label>
            <input v-model.trim="prueba.tipo" type="text" class="form-control" placeholder="Ej: Radiografía de tórax">
          </div>
          <div class="form-group col-md-2">
            <label>Fecha</label>
            <input v-model="prueba.fecha" type="date" class="form-control">
          </div>
          <div class="form-group col-md-5">
            <label>Resultado / hallazgos</label>
            <input v-model.trim="prueba.resultado" type="text" class="form-control">
          </div>
          <div class="form-group col-md-1 d-flex align-items-end">
            <button
              type="button"
              class="btn btn-outline-danger btn-block btn-eliminar"
              :title="`Quitar prueba ${i + 1}`"
              @click="quitarPrueba(i)"
            >
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import modeloSincronizado from '../../mixins/modeloSincronizado'

const COMORBILIDADES = [
  { key: 'diabetes', label: 'Diabetes' },
  { key: 'hipertension', label: 'Hipertensión arterial' },
  { key: 'asma', label: 'Asma/EPOC' },
  { key: 'cardiopatia', label: 'Cardiopatía' },
  { key: 'artritis', label: 'Artritis/artrosis' },
  { key: 'depresion', label: 'Depresión' },
  { key: 'ansiedad', label: 'Ansiedad' },
  { key: 'hiperlipidemia', label: 'Hiperlipidemia/colesterol alto' },
  { key: 'tiroidea', label: 'Enfermedad tiroidea' },
]

const SISTEMAS = [
  { key: 'cabeza', nombre: 'Cabeza' },
  { key: 'cuello', nombre: 'Cuello', pista: 'Tiroides, tráquea, adenopatías, neoplasias' },
  { key: 'ojos', nombre: 'Ojos', pista: 'Agudeza visual, daltonismo, tensión ocular' },
  { key: 'oidos', nombre: 'Oídos', pista: 'Audición, acúfenos, vértigo' },
  { key: 'nariz', nombre: 'Nariz', pista: 'Epistaxis, obstrucción, tabique' },
  { key: 'cardiovascular', nombre: 'Cardiovascular', pista: 'Ritmo, auscultación, soplos, pulsos' },
  { key: 'pulmonar', nombre: 'Pulmonar', pista: 'Auscultación, tos, espirometría' },
  { key: 'gastrointestinal', nombre: 'Gastrointestinal', pista: 'Palpación, ruidos, hígado, bazo' },
  { key: 'abdomen', nombre: 'Abdomen general' },
  { key: 'miembros_superiores', nombre: 'Miembros superiores' },
  { key: 'miembros_inferiores', nombre: 'Miembros inferiores' },
  { key: 'genitourinario', nombre: 'Genitourinario' },
  { key: 'piel', nombre: 'Piel y Tegumentos' },
  { key: 'inmunologico', nombre: 'Inmunológico', pista: 'Lupus, VIH, artritis reumatoide' },
]

const base = () => ({ interrogatorio: '', exploracion: '', notas: '' })

const crearVacio = () => ({
  signos_vitales: { fc: '', fr: '', ta: '', temperatura: '', saturacion: '', glucosa: '' },
  antropometria: { peso: '', talla: '', imc: '', perimetro_cintura: '', perimetro_cadera: '', complexion_fisica: '', estado_nutricional: '' },
  comorbilidades: {
    ...Object.fromEntries(COMORBILIDADES.map(c => [c.key, false])),
    otras_presente: false,
    otras: '',
  },
  sistemas: {
    cabeza: base(),
    cuello: {
      interrogatorio: '',
      exploracion: '',
      notas: '',
      tiroides: '',
      traquea: '',
      adenopatias: '',
      neoplasias: ''
    },
    ojos: { ...base(), agudeza_visual_od: '', agudeza_visual_oi: '', ishihara: '', presion_intraocular: '' },
    oidos: { ...base(), acufenos: false, mareos: false, audiometria: '' },
    nariz: { ...base(), epistaxis: false, obstruccion: false, desviacion_septal: '' },
    cardiovascular: { ...base(), auscultacion: '', pulsos_perifericos: '', edemas: '', varices: '' },
    pulmonar: { ...base(), auscultacion: '', tos_esputo: '', espirometria: '' },
    gastrointestinal: { ...base(), palpacion: '', ruidos_intestinales: '', tamano_higado: '', bazo: '' },
    abdomen: base(),
    miembros_superiores: base(),
    miembros_inferiores: base(),
    genitourinario: { ...base(), hallazgos_genitales: '', puño_percusion: '' },
    piel: {
      interrogatorio: '',
      exploracion: '',
      notas: '',
      dermatitis: false,
      verrugas: false,
      nevos: false,
      nodulos: false,
      acne: false
    },
    inmunologico: {
      interrogatorio: '',
      exploracion: '',
      notas: '',
      lupus: false,
      vih: false,
      artritis_reumatoide: false
    }
  },
  medicamentos: '',
  alergias: '',
  pruebas_complementarias: [{ tipo: '', fecha: '', resultado: '' }],
})

export default {
  name: 'ClinicoExamen',

  mixins: [modeloSincronizado(crearVacio)],

  props: {
    // Opcional: pásale form.datos_puesto para habilitar "Copiar de datos del puesto"
    datosPuesto: { type: Object, default: null },
  },

  data() {
    return {
      comorbilidades: COMORBILIDADES,
      sistemas: SISTEMAS,
    }
  },

  computed: {
    totalComorbilidades() {
      const c = this.local.comorbilidades
      return COMORBILIDADES.filter(o => c[o.key]).length + (c.otras_presente ? 1 : 0)
    },
    categoriaIMC() {
      const imc = parseFloat(this.local.antropometria.imc)
      if (!imc) return ''
      if (imc < 18.5) return 'Bajo peso'
      if (imc < 25) return 'Peso normal'
      if (imc < 30) return 'Sobrepeso'
      if (imc < 35) return 'Obesidad I'
      if (imc < 40) return 'Obesidad II'
      return 'Obesidad III'
    },
    claseIMC() {
      const imc = parseFloat(this.local.antropometria.imc)
      if (!imc) return ''
      if (imc >= 18.5 && imc < 25) return 'imc-normal'
      if (imc >= 30 || imc < 18.5) return 'imc-alerta'
      return 'imc-precaucion'
    },
    estadoNutricional() {
      const imc = parseFloat(this.local.antropometria.imc)
      if (!imc) return ''
      if (imc < 18.5) return 'Bajo peso'
      if (imc < 25) return 'Normal'
      if (imc < 30) return 'Sobrepeso'
      if (imc < 35) return 'Obesidad I'
      if (imc < 40) return 'Obesidad II'
      return 'Obesidad III'
    },
    claseEstadoNutricional() {
      const imc = parseFloat(this.local.antropometria.imc)
      if (!imc) return ''
      if (imc >= 18.5 && imc < 25) return 'imc-normal'
      if (imc >= 30 || imc < 18.5) return 'imc-alerta'
      return 'imc-precaucion'
    },
  },

  watch: {
    'local.antropometria.peso'() { this.calcularIMC() },
    'local.antropometria.talla'() { this.calcularIMC() },
  },

  methods: {
    calcularIMC() {
      const peso = parseFloat(this.local.antropometria.peso)
      const talla = parseFloat(this.local.antropometria.talla) / 100
      this.local.antropometria.imc = peso > 0 && talla > 0
        ? (peso / (talla * talla)).toFixed(1)
        : ''
    },

    copiarSignosVitales() {
      const sv = this.datosPuesto?.signos_vitales || {}
      const mapa = {
        fc: sv.frecuencia_cardiaca,
        fr: sv.frecuencia_respiratoria,
        ta: sv.presion_arterial,
        temperatura: sv.temperatura,
        saturacion: sv.saturacion_oxigeno,
        glucosa: sv.glucosa,
      }
      // Solo copia lo que sí tiene valor, para no borrar lo ya capturado aquí
      Object.entries(mapa).forEach(([campo, valor]) => {
        if (valor !== undefined && valor !== null && String(valor).trim() !== '') {
          this.local.signos_vitales[campo] = valor
        }
      })
    },

    agregarPrueba() {
      this.local.pruebas_complementarias.push({ tipo: '', fecha: '', resultado: '' })
    },

    quitarPrueba(i) {
      const p = this.local.pruebas_complementarias[i]
      const tieneDatos = p.tipo || p.fecha || p.resultado
      if (tieneDatos && !window.confirm(`¿Quitar la prueba ${i + 1}?`)) return
      this.local.pruebas_complementarias.splice(i, 1)
    },
  },
}
</script>

<style scoped src="../../estilos/ficha-ocupacional.css"></style>

<style scoped>
/* Bloque de cada sistema */
.sistema-bloque {
  padding: 1rem 0;
  border-top: 1px solid #E5E7EB;
}
.sistema-bloque:first-of-type {
  border-top: 0;
  padding-top: 0;
}
.sistema-bloque:last-child {
  padding-bottom: 0;
}
.sistema-titulo {
  display: flex;
  align-items: center;
  gap: .65rem;
  margin-bottom: .75rem;
  color: #1F2937;
  font-size: .9rem;
}
.sistema-numero {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #4A5568;
  color: #fff;
  font-size: .75rem;
  font-weight: 600;
  flex-shrink: 0;
}

/* Categoría de IMC */
.imc-normal {
  color: #15803D;
  font-weight: 600;
}
.imc-precaucion {
  color: #B45309;
  font-weight: 600;
}
.imc-alerta {
  color: #B91C1C;
  font-weight: 600;
}

/* Pruebas complementarias */
.prueba-row + .prueba-row {
  border-top: 1px dashed #E5E7EB;
  padding-top: .75rem;
}
.prueba-row:last-child .form-group {
  margin-bottom: 0;
}
.btn-eliminar {
  height: 38px;
}
</style>