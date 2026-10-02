<template>
  <div class="exposicion-riesgos">

    <!-- ===================== 1.1 FICHA DE IDENTIDAD ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-id-card mr-2"></i>1.1 Ficha de identidad</span>
        <button
          v-if="datosPuesto"
          type="button"
          class="btn btn-sm btn-header"
          title="Llena estos campos con lo capturado en Datos del puesto"
          @click="copiarDeDatosPuesto"
        >
          <i class="fas fa-copy mr-1"></i>Copiar de datos del puesto
        </button>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Primer apellido <span class="text-danger">*</span></label>
            <input v-model.trim="local.primer_apellido" type="text" class="form-control">
          </div>
          <div class="form-group col-md-4">
            <label>Segundo apellido</label>
            <input v-model.trim="local.segundo_apellido" type="text" class="form-control">
          </div>
          <div class="form-group col-md-4">
            <label>Nombre(s) <span class="text-danger">*</span></label>
            <input v-model.trim="local.nombres" type="text" class="form-control">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Tipo de sangre</label>
            <select v-model="local.tipo_sanguineo" class="form-control">
              <option value="">Seleccionar</option>
              <option v-for="t in tiposSangre" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Lugar de nacimiento</label>
            <input v-model.trim="local.lugar_nacimiento" type="text" class="form-control">
          </div>
          <div class="form-group col-md-3">
            <label>Teléfono</label>
            <input v-model.trim="local.telefono" type="tel" class="form-control" maxlength="15">
          </div>
          <div class="form-group col-md-3">
            <label>Celular</label>
            <input v-model.trim="local.celular" type="tel" class="form-control" maxlength="15">
          </div>
        </div>

        <div class="form-row mb-n3">
          <div class="form-group col-md-6">
            <label>Contacto de emergencia</label>
            <input v-model.trim="local.emergencia_nombre" type="text" class="form-control" placeholder="Nombre completo">
          </div>
          <div class="form-group col-md-3">
            <label>Teléfono de emergencia</label>
            <input v-model.trim="local.emergencia_telefono" type="tel" class="form-control" maxlength="15">
          </div>
          <div class="form-group col-md-3">
            <label>Relación</label>
            <input v-model.trim="local.emergencia_relacion" type="text" class="form-control" placeholder="Padre, hermano…">
          </div>
        </div>
      </div>
    </div>

    <!-- ============ 1.2 EXPOSICIÓN OCUPACIONAL (EMPRESAS ANTERIORES) ============ -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-building mr-2"></i>1.2 Exposición ocupacional (compañías anteriores)</span>
        <button
          type="button"
          class="btn btn-sm btn-header"
          :disabled="local.empresas_anteriores.length >= maxEmpresas"
          @click="agregarEmpresa"
        >
          <i class="fas fa-plus mr-1"></i>Agregar empresa
        </button>
      </div>
      <div class="card-body">
        <p v-if="!local.empresas_anteriores.length" class="estado-vacio">
          Sin empresas anteriores registradas. Usa “Agregar empresa” si el trabajador tuvo empleos previos.
        </p>

        <div
          v-for="(emp, i) in local.empresas_anteriores"
          :key="i"
          class="form-row empresa-row"
        >
          <div class="form-group col-md-5">
            <label>Empresa {{ i + 1 }}</label>
            <input v-model.trim="emp.nombre" type="text" class="form-control" placeholder="Nombre de la empresa">
          </div>
          <div class="form-group col-md-4">
            <label>Puesto</label>
            <input v-model.trim="emp.puesto" type="text" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Antigüedad</label>
            <input v-model.trim="emp.antiguedad" type="text" class="form-control" placeholder="Ej: 3 años">
          </div>
          <div class="form-group col-md-1 d-flex align-items-end">
            <button
              type="button"
              class="btn btn-outline-danger btn-block btn-eliminar"
              :title="`Quitar empresa ${i + 1}`"
              @click="quitarEmpresa(i)"
            >
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===================== 1.3 AGENTES ===================== -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-flask mr-2"></i>1.3 Agentes a los que ha estado expuesto</span>
        <span class="contador">{{ contar(local.agentes, agentesKeys) }} marcados</span>
      </div>
      <div class="card-body">
        <div v-for="grupo in gruposAgentes" :key="grupo.titulo" class="grupo">
          <h6 class="grupo-titulo">{{ grupo.titulo }}</h6>
          <div class="opciones-grid">
            <label
              v-for="op in grupo.opciones"
              :key="op.key"
              class="opcion"
              :class="{ activa: local.agentes[op.key] }"
            >
              <input v-model="local.agentes[op.key]" type="checkbox">
              <span>{{ op.label }}</span>
            </label>
          </div>
        </div>

        <div class="form-group mt-3 mb-0">
          <label>Otros agentes (especificar)</label>
          <textarea v-model="local.agentes.otros" class="form-control" rows="2"></textarea>
        </div>
      </div>
    </div>

    <!-- ============ 1.4 CONDICIONES DE ALTO RIESGO ============ -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-hard-hat mr-2"></i>1.4 Condiciones de alto riesgo que realiza en su trabajo</span>
        <span class="contador">{{ contar(local.condiciones_riesgo, condicionesKeys) }} marcadas</span>
      </div>
      <div class="card-body">
        <div class="opciones-grid">
          <label
            v-for="op in condicionesRiesgo"
            :key="op.key"
            class="opcion"
            :class="{ activa: local.condiciones_riesgo[op.key] }"
          >
            <input v-model="local.condiciones_riesgo[op.key]" type="checkbox">
            <span>{{ op.label }}</span>
          </label>
        </div>
      </div>
    </div>

    <!-- ============ 1.5 OTRAS CONDICIONES DEL PROCESO ============ -->
    <div class="card ficha-card">
      <div class="card-header ficha-header">
        <span><i class="fas fa-brain mr-2"></i>1.5 Otras condiciones dentro del proceso de trabajo</span>
      </div>
      <div class="card-body">
        <div class="grupo">
          <h6 class="grupo-titulo">Realiza su trabajo generalmente</h6>
          <div class="opciones-grid">
            <label
              v-for="op in posturas"
              :key="op.value"
              class="opcion"
              :class="{ activa: local.otras_condiciones.realiza_generalmente === op.value }"
            >
              <input
                v-model="local.otras_condiciones.realiza_generalmente"
                type="radio"
                name="realiza_generalmente"
                :value="op.value"
              >
              <span>{{ op.label }}</span>
            </label>
          </div>
        </div>

        <div class="grupo">
          <h6 class="grupo-titulo">Su trabajo le exige o le genera</h6>
          <div class="opciones-grid">
            <label
              v-for="op in exigencias"
              :key="op.key"
              class="opcion"
              :class="{ activa: local.otras_condiciones[op.key] }"
            >
              <input v-model="local.otras_condiciones[op.key]" type="checkbox">
              <span>{{ op.label }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
// ---------------------------------------------------------------------------
// Catálogos. Si alguna clave no coincide con las que ya guarda tu BD,
// cámbiala aquí y el resto del componente se ajusta solo.
// ---------------------------------------------------------------------------
const GRUPOS_AGENTES = [
  {
    titulo: 'Polvos y fibras',
    opciones: [
      { key: 'polvo_mineral', label: 'Polvo mineral' },
      { key: 'asbesto', label: 'Asbesto' },
      { key: 'silice', label: 'Sílice cristalina' },
      { key: 'berilio', label: 'Berilio' },
    ],
  },
  {
    titulo: 'Metales y químicos',
    opciones: [
      { key: 'cadmio', label: 'Cadmio' },
      { key: 'plomo', label: 'Plomo' },
      { key: 'mercurio', label: 'Mercurio' },
      { key: 'cromo', label: 'Cromo' },
      { key: 'arsenico', label: 'Arsénico' },
      { key: 'niquel', label: 'Níquel' },
      { key: 'dioxinas', label: 'Dioxinas' },
    ],
  },
  {
    titulo: 'Agentes físicos',
    opciones: [
      { key: 'vibraciones', label: 'Vibraciones (cuerpo/mano)' },
      { key: 'ruido', label: 'Ruido' },
      { key: 'radiacion_ionizante', label: 'Radiación ionizante' },
      { key: 'radiacion_no_ionizante', label: 'Radiación no ionizante' },
      { key: 'temperatura_extrema', label: 'Temperatura extrema' },
      { key: 'estres_termico', label: 'Estrés térmico' },
      { key: 'exposicion_solar', label: 'Exposición solar' },
    ],
  },
]

const CONDICIONES_RIESGO = [
  { key: 'trabajo_altura_2m', label: 'Trabajo en altura (>2 m)' },
  { key: 'espacio_confinado', label: 'Espacios confinados' },
  { key: 'maquinaria_pesada', label: 'Maquinaria pesada' },
  { key: 'cargas_25kg', label: 'Cargas >25 kg' },
  { key: 'sustancias_quimicas', label: 'Sustancias químicas' },
  { key: 'agentes_biologicos', label: 'Agentes biológicos' },
  { key: 'radiaciones_ionizantes', label: 'Radiaciones ionizantes' },
  { key: 'radiaciones_no_ionizantes', label: 'Radiaciones no ionizantes' },
  { key: 'turnos_rotativos_nocturnos', label: 'Turnos rotativos/nocturnos' },
  { key: 'estres_ocupacional', label: 'Estrés ocupacional alto' },
  { key: 'trabajo_repetitivo', label: 'Trabajo repetitivo' },
  { key: 'posturas_forzadas', label: 'Posturas forzadas/incómodas' },
  { key: 'vibraciones_2', label: 'Vibraciones' },
  { key: 'temperatura_extrema_2', label: 'Temperatura extrema' },
  { key: 'exposicion_solar_prolongada', label: 'Exposición solar prolongada' },
]

const POSTURAS = [
  { value: 'de_pie', label: 'De pie' },
  { value: 'sentado', label: 'Sentado' },
  { value: 'alternando', label: 'Alternando de pie y sentado' },
  { value: 'movimiento_constante', label: 'En movimiento constante' },
]

const EXIGENCIAS = [
  { key: 'carga_mental', label: 'Carga mental importante' },
  { key: 'decisiones_criticas', label: 'Decisiones críticas/responsabilidad' },
  { key: 'responsabilidad_personas', label: 'Responsabilidad de personas' },
  { key: 'comunicacion_constante', label: 'Comunicación constante' },
  { key: 'precision_extrema', label: 'Precisión extrema' },
  { key: 'ritmo_acelerado', label: 'Ritmo acelerado/presión de tiempo' },
  { key: 'monotonia', label: 'Monotonía/aburrimiento' },
  { key: 'falta_autonomia', label: 'Falta de autonomía en decisiones' },
  { key: 'conflictos', label: 'Conflictos laborales' },
  { key: 'acoso', label: 'Acoso laboral' },
  { key: 'discriminacion', label: 'Discriminación' },
]

const CAMPOS_IDENTIDAD = [
  'primer_apellido', 'segundo_apellido', 'nombres', 'tipo_sanguineo', 'lugar_nacimiento',
  'telefono', 'celular', 'emergencia_nombre', 'emergencia_telefono', 'emergencia_relacion',
]

const boolMap = (keys, origen = {}) =>
  keys.reduce((acc, k) => ({ ...acc, [k]: Boolean(origen[k]) }), {})

export default {
  name: 'ExposicionRiesgos',

  props: {
    modelValue: { type: Object, default: () => ({}) },
    // Opcional: pásale form.datos_puesto para habilitar "Copiar de datos del puesto"
    datosPuesto: { type: Object, default: null },
  },

  emits: ['update:modelValue'],

  data() {
    return {
      local: this.normalizar(this.modelValue),
      tiposSangre: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
      maxEmpresas: 5,
      gruposAgentes: GRUPOS_AGENTES,
      condicionesRiesgo: CONDICIONES_RIESGO,
      posturas: POSTURAS,
      exigencias: EXIGENCIAS,
    }
  },

  computed: {
    agentesKeys() {
      return GRUPOS_AGENTES.flatMap(g => g.opciones.map(o => o.key))
    },
    condicionesKeys() {
      return CONDICIONES_RIESGO.map(o => o.key)
    },
  },

  watch: {
    // Padre → hijo (evita bucle comparando contenido)
    modelValue: {
      deep: true,
      handler(nuevo) {
        if (JSON.stringify(nuevo) === JSON.stringify(this.local)) return
        this.local = this.normalizar(nuevo)
      },
    },
    // Hijo → padre
    local: {
      deep: true,
      handler(valor) {
        this.$emit('update:modelValue', JSON.parse(JSON.stringify(valor)))
      },
    },
  },

  mounted() {
    // Entrega al Master la estructura completa desde el inicio
    this.$emit('update:modelValue', JSON.parse(JSON.stringify(this.local)))
  },

  methods: {
    normalizar(v = {}) {
      v = v || {}
      const identidad = CAMPOS_IDENTIDAD.reduce((acc, k) => ({ ...acc, [k]: v[k] ?? '' }), {})
      const agentesKeys = GRUPOS_AGENTES.flatMap(g => g.opciones.map(o => o.key))
      const otras = v.otras_condiciones || {}

      return {
        ...identidad,
        empresas_anteriores: Array.isArray(v.empresas_anteriores)
          ? v.empresas_anteriores.map(e => ({
              nombre: e?.nombre ?? '',
              puesto: e?.puesto ?? '',
              antiguedad: e?.antiguedad ?? '',
            }))
          : [],
        agentes: {
          ...boolMap(agentesKeys, v.agentes),
          otros: v.agentes?.otros ?? '',
        },
        condiciones_riesgo: boolMap(CONDICIONES_RIESGO.map(o => o.key), v.condiciones_riesgo),
        otras_condiciones: {
          realiza_generalmente: otras.realiza_generalmente ?? '',
          ...boolMap(EXIGENCIAS.map(o => o.key), otras),
        },
      }
    },

    agregarEmpresa() {
      if (this.local.empresas_anteriores.length >= this.maxEmpresas) return
      this.local.empresas_anteriores.push({ nombre: '', puesto: '', antiguedad: '' })
    },

    quitarEmpresa(i) {
      const emp = this.local.empresas_anteriores[i]
      const tieneDatos = emp.nombre || emp.puesto || emp.antiguedad
      if (tieneDatos && !window.confirm(`¿Quitar la empresa ${i + 1}?`)) return
      this.local.empresas_anteriores.splice(i, 1)
    },

    copiarDeDatosPuesto() {
      const d = this.datosPuesto || {}
      const mapa = {
        primer_apellido: d.apellido_paterno,
        segundo_apellido: d.apellido_materno,
        nombres: d.nombre,
        tipo_sanguineo: d.tipo_sanguineo,
        lugar_nacimiento: d.lugar_nacimiento,
        telefono: d.telefono,
        celular: d.celular,
        emergencia_nombre: d.emergencia_nombre,
        emergencia_telefono: d.emergencia_telefono,
        emergencia_relacion: d.emergencia_relacion,
      }
      // Solo copia lo que sí tiene valor, para no borrar lo ya capturado aquí
      Object.entries(mapa).forEach(([campo, valor]) => {
        if (valor !== undefined && valor !== null && String(valor).trim() !== '') {
          this.local[campo] = valor
        }
      })
    },

    contar(obj, keys) {
      return keys.filter(k => obj[k]).length
    },
  },
}
</script>

<style scoped>
/* ---------- Tarjetas ---------- */
.ficha-card {
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  box-shadow: none;
  margin-bottom: 1.25rem;
}

.ficha-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  flex-wrap: wrap;
  background: linear-gradient(90deg, #5F6E7E, #4A5568);
  color: #fff;
  font-size: .95rem;
  font-weight: 600;
  padding: .7rem 1.25rem;
  border-bottom: 0;
  border-radius: 6px 6px 0 0;
}

.card-body {
  padding: 1.25rem;
}

.btn-header {
  background: rgba(255, 255, 255, .15);
  border: 1px solid rgba(255, 255, 255, .35);
  color: #fff;
  font-size: .8rem;
}
.btn-header:hover:not(:disabled) {
  background: rgba(255, 255, 255, .28);
  color: #fff;
}
.btn-header:disabled {
  opacity: .5;
}

.contador {
  background: rgba(255, 255, 255, .18);
  border-radius: 10px;
  padding: .15rem .65rem;
  font-size: .75rem;
  font-weight: 600;
}

/* ---------- Campos ---------- */
.form-group label {
  font-size: .8rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: .35rem;
}

.form-control {
  height: 38px;
  padding: 10px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  font-size: .875rem;
  color: #1F2937;
}
select.form-control {
  padding-top: 6px;
  padding-bottom: 6px;
}
textarea.form-control {
  height: auto;
  min-height: 70px;
}
.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 .15rem rgba(0, 123, 255, .2);
}

/* ---------- Empresas anteriores ---------- */
.empresa-row + .empresa-row {
  border-top: 1px dashed #E5E7EB;
  padding-top: .75rem;
}
.empresa-row:last-child .form-group {
  margin-bottom: 0;
}
.btn-eliminar {
  height: 38px;
}
.estado-vacio {
  margin: 0;
  padding: .9rem 1rem;
  border: 1px dashed #D1D5DB;
  border-radius: 4px;
  color: #6B7280;
  font-size: .85rem;
  text-align: center;
}

/* ---------- Grupos de opciones ---------- */
.grupo + .grupo {
  margin-top: 1.25rem;
}
.grupo-titulo {
  font-size: .85rem;
  font-weight: 600;
  color: #4A5568;
  margin-bottom: .6rem;
}

.opciones-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: .5rem;
}

.opcion {
  display: flex;
  align-items: center;
  gap: .6rem;
  min-height: 38px;
  margin: 0;
  padding: .45rem .75rem;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  background: #fff;
  color: #1F2937;
  font-size: .85rem;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
}
.opcion:hover {
  border-color: #9CA3AF;
  background: #F9FAFB;
}
.opcion.activa {
  border-color: #007bff;
  background: #EFF6FF;
  color: #0056b3;
}
.opcion input {
  width: 16px;
  height: 16px;
  margin: 0;
  flex-shrink: 0;
  accent-color: #007bff;
  cursor: pointer;
}
.opcion:focus-within {
  outline: 2px solid rgba(0, 123, 255, .4);
  outline-offset: 1px;
}
</style>