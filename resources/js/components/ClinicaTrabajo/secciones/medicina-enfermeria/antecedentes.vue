<template>
  <div class="antecedentes">

    <!-- ===================== 2.1 ACCIDENTES DE TRABAJO ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-user-injured mr-2"></i>2.1 Accidentes de trabajo</span>
        <button type="button" class="btn btn-sm btn-header" @click="agregarAccidente">
          <i class="fas fa-plus mr-1"></i>Agregar accidente
        </button>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre todos los accidentes laborales previos. Si no ha tenido ninguno, quite el registro vacío.</p>

        <p v-if="!local.accidentes.length" class="estado-vacio">
          Sin accidentes registrados. Usa “Agregar accidente” si el trabajador tuvo alguno.
        </p>

        <div v-for="(acc, i) in local.accidentes" :key="i" class="bloque-repetible">
          <div class="bloque-titulo">
            <span>Accidente {{ i + 1 }}</span>
            <button type="button" class="btn-quitar" :title="`Quitar accidente ${i + 1}`" @click="quitarAccidente(i)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group col-md-2">
              <label>Fecha</label>
              <input v-model="acc.fecha" type="date" class="form-control">
            </div>
            <div class="form-group col-md-4">
              <label>Empresa</label>
              <input v-model.trim="acc.empresa" type="text" class="form-control">
            </div>
            <div class="form-group col-md-3">
              <label>Tipo de accidente</label>
              <input v-model.trim="acc.tipo" type="text" class="form-control" placeholder="Caída, golpe, corte…">
            </div>
            <div class="form-group col-md-3">
              <label>Parte del cuerpo afectada</label>
              <input v-model.trim="acc.parte_cuerpo" type="text" class="form-control">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-2">
              <label>Días de incapacidad</label>
              <input v-model="acc.dias_incapacidad" type="number" min="0" class="form-control">
            </div>
            <div class="form-group col-md-10">
              <label>Secuelas</label>
              <input v-model.trim="acc.secuelas" type="text" class="form-control" placeholder="Ninguna, cicatriz, limitación de movimiento…">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 2.2 ENFERMEDADES LABORALES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-lungs-virus mr-2"></i>2.2 Enfermedades laborales</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre las enfermedades profesionales diagnosticadas.</p>

        <div v-for="(enf, i) in local.enfermedades_laborales" :key="i" class="bloque-repetible">
          <div class="bloque-titulo">
            <span>Enfermedad {{ i + 1 }}</span>
            <button type="button" class="btn-quitar" :title="`Quitar enfermedad ${i + 1}`" @click="quitarEnfermedadLaboral(i)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Diagnóstico</label>
              <input v-model.trim="enf.diagnostico" type="text" class="form-control" placeholder="Nombre de la enfermedad profesional">
            </div>
            <div class="form-group col-md-6">
              <label>Fecha (mm/aa)</label>
              <input v-model="enf.fecha" type="month" class="form-control">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 2.3 INCAPACIDADES ÚLTIMOS 3 MESES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-calendar-times mr-2"></i>2.3 Incapacidades últimos 3 meses</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre incapacidades por motivos de salud en los últimos 3 meses.</p>

        <div class="form-group mb-3">
          <label class="opcion">
            <input v-model="local.incapacidades_3m.tiene" type="checkbox">
            <span>¿Ha tenido alguna incapacidad por salud en los últimos 3 meses?</span>
          </label>
        </div>

        <template v-if="local.incapacidades_3m.tiene">
          <div v-for="(inc, i) in local.incapacidades_3m.items" :key="i" class="bloque-repetible">
            <div class="bloque-titulo">
              <span>Incapacidad {{ i + 1 }}</span>
              <button type="button" class="btn-quitar" :title="`Quitar incapacidad ${i + 1}`" @click="quitarIncapacidad3m(i)">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
            <div class="form-row">
              <div class="form-group col-md-8">
                <label>Motivo</label>
                <input v-model.trim="inc.motivo" type="text" class="form-control" placeholder="Motivo de la incapacidad">
              </div>
              <div class="form-group col-md-4">
                <label>Número de días</label>
                <input v-model="inc.dias" type="number" min="0" class="form-control">
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="agregarIncapacidad3m">
            <i class="fas fa-plus mr-1"></i>Agregar incapacidad
          </button>
        </template>
      </div>
    </div>

    <!-- ===================== 2.4 HEREDO-FAMILIARES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-users mr-2"></i>2.2 Antecedentes heredo-familiares</span>
        <span class="contador">{{ contarPresentes(local.heredo_familiares, heredoKeys) }} marcados</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Marque las enfermedades presentes en la familia e indique el parentesco.</p>

        <div class="opciones-grid amplio">
          <div
            v-for="op in heredo"
            :key="op.key"
            class="opcion-detalle"
            :class="{ activa: local.heredo_familiares[op.key].presente }"
          >
            <label class="opcion">
              <input v-model="local.heredo_familiares[op.key].presente" type="checkbox">
              <span>{{ op.label }}</span>
            </label>
            <div v-if="local.heredo_familiares[op.key].presente" class="detalle">
              <input
                v-model.trim="local.heredo_familiares[op.key].parentesco"
                type="text"
                class="form-control"
                placeholder="Parentesco: padre, madre, abuelo…"
                :aria-label="`Parentesco: ${op.label}`"
              >
            </div>
          </div>
        </div>

        <div class="form-group mt-3 mb-0">
          <label>Otros antecedentes familiares</label>
          <textarea v-model="local.heredo_familiares.otros" class="form-control" rows="2" placeholder="Especificar enfermedad y parentesco…"></textarea>
        </div>
      </div>
    </div>

    <!-- ===================== 2.3 NO PATOLÓGICOS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-walking mr-2"></i>2.3 Antecedentes personales no patológicos</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Seleccione la opción que corresponda. El campo de detalle aparece cuando aplica.</p>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Tabaquismo</label>
            <select v-model="local.no_patologicos.tabaquismo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="nunca">Nunca fumó</option>
              <option value="exfumador">Exfumador</option>
              <option value="activo">Fumador activo</option>
            </select>
            <input
              v-if="local.no_patologicos.tabaquismo && local.no_patologicos.tabaquismo !== 'nunca'"
              v-model.trim="local.no_patologicos.tabaquismo_detalles"
              type="text"
              class="form-control mt-2"
              placeholder="Cigarrillos/día, años…"
            >
          </div>

          <div class="form-group col-md-3">
            <label>Alcoholismo</label>
            <select v-model="local.no_patologicos.alcoholismo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="no">No consume</option>
              <option value="ocasional">Ocasional</option>
              <option value="moderado">Moderado</option>
              <option value="frecuente">Frecuente</option>
            </select>
            <input
              v-if="local.no_patologicos.alcoholismo && local.no_patologicos.alcoholismo !== 'no'"
              v-model.trim="local.no_patologicos.alcoholismo_detalles"
              type="text"
              class="form-control mt-2"
              placeholder="Tipo, frecuencia…"
            >
          </div>

          <div class="form-group col-md-3">
            <label>Actividad física</label>
            <select v-model="local.no_patologicos.actividad_fisica" class="form-control">
              <option value="">Seleccionar</option>
              <option value="sedentario">Sedentario</option>
              <option value="leve">Leve (1-2 veces/semana)</option>
              <option value="moderada">Moderada (3-4 veces/semana)</option>
              <option value="intensa">Intensa (5+ veces/semana)</option>
            </select>
          </div>

          <div class="form-group col-md-3">
            <label>Estrés / problemas emocionales</label>
            <select v-model="local.no_patologicos.estres" class="form-control">
              <option value="">Seleccionar</option>
              <option value="bajo">Bajo</option>
              <option value="moderado">Moderado</option>
              <option value="alto">Alto</option>
              <option value="muy_alto">Muy alto</option>
            </select>
          </div>
        </div>

        <div v-if="local.no_patologicos.estres && local.no_patologicos.estres !== 'bajo'" class="form-group">
          <label>Detalle de estrés</label>
          <input v-model.trim="local.no_patologicos.estres_detalles" type="text" class="form-control" placeholder="Causas, síntomas…">
        </div>

        <div class="grupo mt-1">
          <h6 class="grupo-titulo">Consumo de drogas</h6>
          <div class="form-row">
            <div class="col-md-4">
              <div class="opciones-grid compacto">
                <label class="opcion" :class="{ activa: consumoDrogas === 'no' }">
                  <input v-model="consumoDrogas" type="radio" name="consumo_drogas" value="no">
                  <span>No consume</span>
                </label>
                <label class="opcion" :class="{ activa: consumoDrogas === 'si' }">
                  <input v-model="consumoDrogas" type="radio" name="consumo_drogas" value="si">
                  <span>Sí consume</span>
                </label>
              </div>
            </div>
            <div v-if="consumoDrogas === 'si'" class="col-md-8">
              <input
                v-model.trim="local.no_patologicos.drogas_detalles"
                type="text"
                class="form-control"
                placeholder="Sustancia, frecuencia, último consumo…"
                aria-label="Detalle de consumo de drogas"
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== E. VACUNACIÓN ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-syringe mr-2"></i>E. Vacunación</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Verifique que el esquema de vacunación esté completo.</p>

        <div class="opciones-grid amplio">
          <div
            v-for="vacuna in vacunas"
            :key="vacuna.key"
            class="opcion-detalle"
          >
            <label class="opcion">
              <input
                v-model="local.vacunas[vacuna.key].presente"
                type="checkbox"
              >
              <span>{{ vacuna.label }}</span>
            </label>
            <div v-if="local.vacunas[vacuna.key].presente" class="detalle">
              <input
                v-model="local.vacunas[vacuna.key].fecha"
                type="month"
                class="form-control"
                placeholder="mm/aa"
              >
            </div>
          </div>
        </div>

        <div class="form-group mt-3 mb-0">
          <label>Otros vacunación</label>
          <textarea v-model="local.vacunas.otros" class="form-control" rows="2"
            placeholder="Vacuna y fecha…"></textarea>
        </div>
      </div>
    </div>

    <!-- ===================== 2.4 GINECO / UROLÓGICO ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-venus-mars mr-2"></i>2.4 Antecedentes gineco-obstétricos / urológicos</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Seleccione el sexo biológico para mostrar los antecedentes que correspondan.</p>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Sexo biológico</label>
            <select v-model="local.genero" class="form-control">
              <option value="">Seleccionar</option>
              <option value="F">Femenino</option>
              <option value="M">Masculino</option>
              <option value="Otro">Prefiero no especificar</option>
            </select>
          </div>
        </div>

        <!-- FEMENINO -->
        <template v-if="local.genero === 'F'">
          <div class="form-row">
            <div class="form-group col-md-2">
              <label>Menarquía (edad)</label>
              <input v-model="local.gineco.menarquia" type="number" min="0" class="form-control">
            </div>
            <div class="form-group col-md-2">
              <label>Ciclo (días)</label>
              <input v-model="local.gineco.ciclo_dias" type="number" min="0" class="form-control" placeholder="Ej: 28">
            </div>
            <div class="form-group col-md-2">
              <label>Duración (días)</label>
              <input v-model="local.gineco.duracion_dias" type="number" min="0" class="form-control" placeholder="Ej: 5">
            </div>
            <div class="form-group col-md-2">
              <label>Gestaciones</label>
              <input v-model="local.gineco.gestaciones" type="number" min="0" class="form-control">
            </div>
            <div class="form-group col-md-2">
              <label>Partos</label>
              <input v-model="local.gineco.partos" type="number" min="0" class="form-control">
            </div>
            <div class="form-group col-md-2">
              <label>Abortos</label>
              <input v-model="local.gineco.abortos" type="number" min="0" class="form-control">
            </div>
          </div>

          <h6 class="grupo-titulo">Marque si presenta</h6>
          <div class="opciones-grid">
            <label
              v-for="op in ginecoOpciones"
              :key="op.key"
              class="opcion"
              :class="{ activa: local.gineco[op.key] }"
            >
              <input v-model="local.gineco[op.key]" type="checkbox">
              <span>{{ op.label }}</span>
            </label>
          </div>
        </template>

        <!-- MASCULINO -->
        <template v-if="local.genero === 'M'">
          <h6 class="grupo-titulo">Marque si presenta</h6>
          <div class="opciones-grid">
            <label
              v-for="op in uroOpciones"
              :key="op.key"
              class="opcion"
              :class="{ activa: local.urologo[op.key] }"
            >
              <input v-model="local.urologo[op.key]" type="checkbox">
              <span>{{ op.label }}</span>
            </label>
          </div>
          <div class="form-group mt-3 mb-0">
            <label>Otras alteraciones urológicas</label>
            <textarea v-model="local.urologo.otros" class="form-control" rows="2" placeholder="Especificar…"></textarea>
          </div>
        </template>
      </div>
    </div>

    <!-- ===================== 2.5 PATOLÓGICOS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-notes-medical mr-2"></i>2.5 Antecedentes personales patológicos</span>
        <span class="contador">{{ contarPresentes(local.patologicos, patologicosKeys) }} marcadas</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Marque las enfermedades que ha padecido e indique año de diagnóstico y estado actual.</p>

        <div v-for="grupo in gruposPatologicos" :key="grupo.titulo" class="grupo">
          <h6 class="grupo-titulo">{{ grupo.titulo }}</h6>
          <div class="opciones-grid amplio">
            <div
              v-for="op in grupo.opciones"
              :key="op.key"
              class="opcion-detalle"
              :class="{ activa: local.patologicos[op.key].presente }"
            >
              <label class="opcion">
                <input v-model="local.patologicos[op.key].presente" type="checkbox">
                <span>{{ op.label }}</span>
              </label>
              <div v-if="local.patologicos[op.key].presente" class="detalle">
                <input
                  v-model.trim="local.patologicos[op.key].observaciones"
                  type="text"
                  class="form-control"
                  :placeholder="op.placeholder || 'Año, estado actual…'"
                  :aria-label="`Observaciones: ${op.label}`"
                >
              </div>
            </div>
          </div>
        </div>

        <div class="form-group mt-3 mb-0">
          <label>Otras enfermedades o condiciones relevantes</label>
          <textarea v-model="local.patologicos.otros" class="form-control" rows="2" placeholder="Diagnóstico, año, estado actual…"></textarea>
        </div>
      </div>
    </div>

    <!-- ===================== 2.6 CIRUGÍAS/ACCIDENTES NO LABORALES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-hospital-user mr-2"></i>2.6 Cirugías / Accidentes no laborales</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre cirugías o accidentes ocurridos fuera del trabajo.</p>

        <div v-for="(item, i) in local.cirugias_accidentes_no_laborales" :key="i" class="bloque-repetible">
          <div class="bloque-titulo">
            <span>Cirugía/Accidente {{ i + 1 }}</span>
            <button type="button" class="btn-quitar" :title="`Quitar registro ${i + 1}`" @click="quitarRegistroRepetible('cirugias_accidentes_no_laborales', i)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group col-md-12">
              <label>Descripción</label>
              <textarea v-model.trim="item.descripcion" type="text" class="form-control" placeholder="Cirugía, accidente, motivo, parte del cuerpo, fecha…"></textarea>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="agregarRegistroRepetible('cirugias_accidentes_no_laborales')">
          <i class="fas fa-plus mr-1"></i>Agregar registro
        </button>
      </div>
    </div>

    <!-- ===================== 2.7 ALERGIAS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-star-of-life mr-2"></i>2.7 Alergias</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre alergias a medicamentos, alimentos o sustancias.</p>

        <div v-for="(alergia, i) in local.alergias" :key="i" class="bloque-repetible">
          <div class="bloque-titulo">
            <span>Alergia {{ i + 1 }}</span>
            <button type="button" class="btn-quitar" :title="`Quitar alergia ${i + 1}`" @click="quitarRegistroRepetible('alergias', i)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Sustancia</label>
              <input v-model.trim="alergia.sustancia" type="text" class="form-control" placeholder="Ej: Penicilina, mariscos…">
            </div>
            <div class="form-group col-md-4">
              <label>Tipo de reacción</label>
              <select v-model="alergia.tipo_reaccion" class="form-control">
                <option value="">Seleccionar</option>
                <option value="leve">Leve (sarpullido, picazón)</option>
                <option value="moderada">Moderada (dificultad respiratoria)</option>
                <option value="severa">Severa (anafilaxia)</option>
              </select>
            </div>
            <div class="form-group col-md-2">
              <label>Hospitalización</label>
              <select v-model="alergia.hospitalizacion" class="form-control">
                <option value="">Seleccionar</option>
                <option value="no">No</option>
                <option value="si">Sí</option>
              </select>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="agregarRegistroRepetible('alergias')">
          <i class="fas fa-plus mr-1"></i>Agregar alergia
        </button>
      </div>
    </div>

    <!-- ===================== 2.8 TRANSUSIONES SANGUÍNEAS ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-blood-type mr-2"></i>2.8 Transfusiones sanguíneas</span>
      </div>
      <div class="card-body">
        <p class="instruccion">Registre transfusiones sanguíneas previas.</p>

        <div v-for="(transf, i) in local.transfusiones" :key="i" class="bloque-repetible">
          <div class="bloque-titulo">
            <span>Transfusión {{ i + 1 }}</span>
            <button type="button" class="btn-quitar" :title="`Quitar transfusión ${i + 1}`" @click="quitarRegistroRepetible('transfusiones', i)">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Motivo</label>
              <input v-model.trim="transf.motivo" type="text" class="form-control" placeholder="Motivo de la transfusión">
            </div>
            <div class="form-group col-md-6">
              <label>Fecha (mm/aa)</label>
              <input v-model="transf.fecha" type="month" class="form-control">
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" @click="agregarRegistroRepetible('transfusiones')">
          <i class="fas fa-plus mr-1"></i>Agregar transfusión
        </button>
      </div>
    </div>

  </div>
</template>

<script>
import modeloSincronizado from '../../mixins/modeloSincronizado'

// ---------------------------------------------------------------------------
// Catálogos (las claves coinciden con form.antecedentes del Master)
// ---------------------------------------------------------------------------
const HEREDO = [
  { key: 'cancer', label: 'Cáncer' },
  { key: 'diabetes', label: 'Diabetes' },
  { key: 'hipertension', label: 'Hipertensión arterial' },
  { key: 'cardiopatias', label: 'Cardiopatías' },
  { key: 'infarto', label: 'Infarto del miocardio' },
  { key: 'acv', label: 'Accidente cerebrovascular' },
  { key: 'pulmonar', label: 'Asma/EPOC/problemas pulmonares' },
  { key: 'tuberculosis', label: 'Tuberculosis' },
  { key: 'hepatica', label: 'Enfermedades hepáticas' },
  { key: 'renal', label: 'Enfermedades renales' },
  { key: 'artritis', label: 'Artritis/enfermedades reumáticas' },
  { key: 'osteoporosis', label: 'Osteoporosis' },
  { key: 'mental', label: 'Depresión/problemas mentales' },
  { key: 'adiccion', label: 'Alcoholismo/adicciones' },
]

const GRUPOS_PATOLOGICOS = [
  {
    titulo: 'Cardiovascular',
    opciones: [
      { key: 'hipertension', label: 'Hipertensión arterial' },
      { key: 'cardiopatia', label: 'Cardiopatía', placeholder: 'Año, tipo…' },
      { key: 'infarto', label: 'Infarto del miocardio', placeholder: 'Año, secuelas…' },
      { key: 'arritmia', label: 'Arritmias cardíacas' },
    ],
  },
  {
    titulo: 'Respiratorio',
    opciones: [
      { key: 'asma', label: 'Asma' },
      { key: 'epoc', label: 'EPOC/enfisema' },
      { key: 'tuberculosis', label: 'Tuberculosis', placeholder: 'Año, tratamiento completo…' },
      { key: 'neumonia', label: 'Neumonía' },
      { key: 'silicosis', label: 'Silicosis/neumoconiosis' },
    ],
  },
  {
    titulo: 'Digestivo',
    opciones: [
      { key: 'ulcera', label: 'Úlcera gástrica/duodenal' },
      { key: 'gastritis', label: 'Gastritis crónica' },
      { key: 'hepatitis', label: 'Hepatitis', placeholder: 'Tipo (A, B, C), año…' },
      { key: 'cirrosis', label: 'Cirrosis hepática' },
    ],
  },
  {
    titulo: 'Endocrino',
    opciones: [
      { key: 'diabetes', label: 'Diabetes', placeholder: 'Tipo, año, control…' },
      { key: 'hipertiroidismo', label: 'Hipertiroidismo' },
      { key: 'hipotiroidismo', label: 'Hipotiroidismo' },
    ],
  },
  {
    titulo: 'Renal / urológico',
    opciones: [
      { key: 'nefritis', label: 'Nefritis/problemas renales' },
      { key: 'litiasis', label: 'Litiasis renal (piedras)' },
    ],
  },
  {
    titulo: 'Osteomuscular',
    opciones: [
      { key: 'artrosis', label: 'Artrosis' },
      { key: 'artritis', label: 'Artritis reumatoide' },
      { key: 'osteoporosis', label: 'Osteoporosis' },
      { key: 'hernia_discal', label: 'Hernia discal', placeholder: 'Nivel, año, cirugía…' },
      { key: 'sindrome_tunel_carpal', label: 'Síndrome del túnel carpiano', placeholder: 'Lado, año…' },
    ],
  },
  {
    titulo: 'Neurológico y salud mental',
    opciones: [
      { key: 'epilepsia', label: 'Epilepsia', placeholder: 'Última crisis, tratamiento…' },
      { key: 'migraña', label: 'Migraña/cefaleas crónicas' },
      { key: 'depresion', label: 'Depresión' },
      { key: 'ansiedad', label: 'Trastorno de ansiedad' },
    ],
  },
]

const GINECO_OPCIONES = [
  { key: 'desorden_menstrual', label: 'Desorden menstrual' },
  { key: 'dismenorrea', label: 'Dismenorrea (cólicos intensos)' },
  { key: 'anticonceptivos', label: 'Usa anticonceptivos' },
]

const URO_OPCIONES = [
  { key: 'disfuncion_erectil', label: 'Disfunción eréctil' },
  { key: 'infertilidad', label: 'Infertilidad' },
  { key: 'problemas_prostaticos', label: 'Problemas prostáticos' },
  { key: 'infecciones_urinarias', label: 'Infecciones urinarias recurrentes' },
]

const VACUNAS = [
  { key: 'covid19', label: 'COVID-19' },
  { key: 'td', label: 'Tétanos/Difteria (Td)' },
  { key: 'tdpa', label: 'Tétanos/Difteria/Tos ferina (Tdpa)' },
  { key: 'influenza', label: 'Influenza' },
  { key: 'hepatitis_ab', label: 'Hepatitis A/B' },
  { key: 'sr', label: 'Sarampión/Rubéola (SR)' },
]

const accidenteVacio = () => ({
  fecha: '', empresa: '', tipo: '', parte_cuerpo: '', dias_incapacidad: '', secuelas: '',
})

const presenteVacio = (campo) => ({ presente: false, [campo]: '' })

const crearVacio = () => ({
  accidentes: [accidenteVacio()],
  enfermedades_laborales: [
    { diagnostico: '', fecha: '' },
    { diagnostico: '', fecha: '' },
    { diagnostico: '', fecha: '' }
  ],
  incapacidades_3m: {
    tiene: false,
    items: [{ motivo: '', dias: '' }, { motivo: '', dias: '' }]
  },
  vacunas: {
    covid19: { presente: false, fecha: '' },
    td: { presente: false, fecha: '' },
    tdpa: { presente: false, fecha: '' },
    influenza: { presente: false, fecha: '' },
    hepatitis_ab: { presente: false, fecha: '' },
    sr: { presente: false, fecha: '' },
    otros: ''
  },
  cirugias_accidentes_no_laborales: [{ descripcion: '' }],
  alergias: [{ sustancia: '', reaccion: '', hospitalizacion: false }],
  transfusiones: [{ motivo: '', fecha: '' }],
  heredo_familiares: {
    ...Object.fromEntries(HEREDO.map(h => [h.key, presenteVacio('parentesco')])),
    otros: '',
  },
  no_patologicos: {
    tabaquismo: '',
    tabaquismo_detalles: '',
    alcoholismo: '',
    alcoholismo_detalles: '',
    drogas_no: true,
    drogas_si: false,
    drogas_detalles: '',
    actividad_fisica: '',
    estres: '',
    estres_detalles: '',
  },
  genero: '',
  gineco: {
    menarquia: '',
    ciclo_dias: '',
    duracion_dias: '',
    desorden_menstrual: false,
    dismenorrea: false,
    gestaciones: '',
    partos: '',
    abortos: '',
    anticonceptivos: false,
  },
  urologo: {
    disfuncion_erectil: false,
    infertilidad: false,
    problemas_prostaticos: false,
    infecciones_urinarias: false,
    otros: '',
  },
  patologicos: {
    ...Object.fromEntries(
      GRUPOS_PATOLOGICOS.flatMap(g => g.opciones).map(o => [o.key, presenteVacio('observaciones')])
    ),
    otros: '',
  },
})

// Convierte el género de Datos del puesto (M/F/Masculino/Mujer…) al formato de esta hoja
const mapearGenero = (valor) => {
  const g = String(valor || '').trim().toUpperCase()
  if (['M', 'MASCULINO', 'HOMBRE', 'H'].includes(g)) return 'M'
  if (['F', 'FEMENINO', 'MUJER'].includes(g)) return 'F'
  return ''
}

export default {
  name: 'Antecedentes',

  mixins: [modeloSincronizado(crearVacio)],

  props: {
    // Opcional: pásale form.datos_puesto para precargar el sexo biológico
    datosPuesto: { type: Object, default: null },
  },

  data() {
    return {
      heredo: HEREDO,
      gruposPatologicos: GRUPOS_PATOLOGICOS,
      ginecoOpciones: GINECO_OPCIONES,
      uroOpciones: URO_OPCIONES,
      vacunas: VACUNAS,
    }
  },

  computed: {
    heredoKeys() {
      return HEREDO.map(h => h.key)
    },
    patologicosKeys() {
      return GRUPOS_PATOLOGICOS.flatMap(g => g.opciones.map(o => o.key))
    },
    // Un solo control para los dos booleanos que ya guarda el Master
    consumoDrogas: {
      get() {
        const np = this.local.no_patologicos
        if (np.drogas_si) return 'si'
        if (np.drogas_no) return 'no'
        return ''
      },
      set(valor) {
        this.local.no_patologicos.drogas_si = valor === 'si'
        this.local.no_patologicos.drogas_no = valor === 'no'
        if (valor !== 'si') this.local.no_patologicos.drogas_detalles = ''
      },
    },
  },

  mounted() {
    if (!this.local.genero && this.datosPuesto?.genero) {
      const genero = mapearGenero(this.datosPuesto.genero)
      if (genero) this.local.genero = genero
    }
  },

  methods: {
    agregarAccidente() {
      this.local.accidentes.push(accidenteVacio())
    },

    quitarAccidente(i) {
      const acc = this.local.accidentes[i]
      const tieneDatos = Object.values(acc).some(v => String(v ?? '').trim() !== '')
      if (tieneDatos && !window.confirm(`¿Quitar el accidente ${i + 1}?`)) return
      this.local.accidentes.splice(i, 1)
    },

    agregarRegistroRepetible(campo) {
      const max = { cirugias_accidentes_no_laborales: 10, alergias: 10, transfusiones: 10 }
      if (this.local[campo].length >= max[campo]) return
      const vacios = {
        cirugias_accidentes_no_laborales: { descripcion: '' },
        alergias: { sustancia: '', tipo_reaccion: '', hospitalizacion: false },
        transfusiones: { motivo: '', fecha: '' }
      }
      this.local[campo].push(vacios[campo] || {})
    },

    quitarRegistroRepetible(campo, i) {
      const item = this.local[campo][i]
      const tieneDatos = Object.values(item).some(v => String(v ?? '').trim() !== '')
      if (tieneDatos && !window.confirm(`¿Quitar el registro ${i + 1}?`)) return
      this.local[campo].splice(i, 1)
    },

    agregarIncapacidad3m() {
      if (this.local.incapacidades_3m.items.length >= 2) return
      this.local.incapacidades_3m.items.push({ motivo: '', dias: '' })
    },

    quitarIncapacidad3m(i) {
      const item = this.local.incapacidades_3m.items[i]
      const tieneDatos = Object.values(item).some(v => String(v ?? '').trim() !== '')
      if (tieneDatos && !window.confirm(`¿Quitar la incapacidad ${i + 1}?`)) return
      this.local.incapacidades_3m.items.splice(i, 1)
    },

    agregarEnfermedadLaboral() {
      if (this.local.enfermedades_laborales.length >= 3) return
      this.local.enfermedades_laborales.push({ diagnostico: '', fecha: '' })
    },

    quitarEnfermedadLaboral(i) {
      const item = this.local.enfermedades_laborales[i]
      const tieneDatos = Object.values(item).some(v => String(v ?? '').trim() !== '')
      if (tieneDatos && !window.confirm(`¿Quitar la enfermedad ${i + 1}?`)) return
      this.local.enfermedades_laborales.splice(i, 1)
    },

    contarPresentes(obj, keys) {
      return keys.filter(k => obj[k]?.presente).length
    },
  },
}
</script>

<style scoped src="../../estilos/ficha-ocupacional.css"></style>