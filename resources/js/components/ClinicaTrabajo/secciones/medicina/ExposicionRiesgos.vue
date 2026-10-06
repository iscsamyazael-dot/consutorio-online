<template>
  <div class="seccion-exposicion-riesgos-medicina">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-alert text-orange-600"></i>
        Exposición a Riesgos Laborales
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Identifique los riesgos laborales a los que está expuesto el trabajador según su puesto.
      </p>

      <!-- Riesgos físicos -->
      <div class="mb-4">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-radio text-blue-500"></i>
          Riesgos Físicos
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label v-for="riesgo in riesgosFisicos" :key="riesgo.key" class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition">
            <input type="checkbox" :checked="form[riesgo.key]" @change="updateField(riesgo.key, $event.target.checked)" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
            <span class="text-sm text-gray-700">{{ riesgo.label }}</span>
          </label>
        </div>
      </div>

      <!-- Riesgos químicos -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-flask text-purple-500"></i>
          Riesgos Químicos
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label v-for="riesgo in riesgosQuimicos" :key="riesgo.key" class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition">
            <input type="checkbox" :checked="form[riesgo.key]" @change="updateField(riesgo.key, $event.target.checked)" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500" />
            <span class="text-sm text-gray-700">{{ riesgo.label }}</span>
          </label>
        </div>
      </div>

      <!-- Riesgos biológicos -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-bug text-green-500"></i>
          Riesgos Biológicos
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label v-for="riesgo in riesgosBiologicos" :key="riesgo.key" class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition">
            <input type="checkbox" :checked="form[riesgo.key]" @change="updateField(riesgo.key, $event.target.checked)" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" />
            <span class="text-sm text-gray-700">{{ riesgo.label }}</span>
          </label>
        </div>
      </div>

      <!-- Riesgos ergonómicos -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-body text-indigo-500"></i>
          Riesgos Ergonómicos
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label v-for="riesgo in riesgosErgonomicos" :key="riesgo.key" class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition">
            <input type="checkbox" :checked="form[riesgo.key]" @change="updateField(riesgo.key, $event.target.checked)" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
            <span class="text-sm text-gray-700">{{ riesgo.label }}</span>
          </label>
        </div>
      </div>

      <!-- Riesgos psicosociales -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-brain text-pink-500"></i>
          Riesgos Psicosociales (NOM-035)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label v-for="riesgo in riesgosPsicosociales" :key="riesgo.key" class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition">
            <input type="checkbox" :checked="form[riesgo.key]" @change="updateField(riesgo.key, $event.target.checked)" class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500" />
            <span class="text-sm text-gray-700">{{ riesgo.label }}</span>
          </label>
        </div>
      </div>

      <!-- Detalle de exposiciones -->
      <div v-if="riesgosSeleccionados.length > 0" class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-2">Detalle de Exposiciones Seleccionadas</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="riesgo in riesgosSeleccionados" :key="riesgo" class="border rounded-lg p-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ riesgo }}</label>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-xs text-gray-500 mb-1">Frecuencia</label>
                <select v-model="form[`${riesgo}_frecuencia`]" class="input-field w-full text-sm">
                  <option value="">Seleccionar...</option>
                  <option value="continua">Continua</option>
                  <option value="intermitente">Intermitente</option>
                  <option value="ocasional">Ocasional</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-gray-500 mb-1">Intensidad</label>
                <select v-model="form[`${riesgo}_intensidad`]" class="input-field w-full text-sm">
                  <option value="">Seleccionar...</option>
                  <option value="baja">Baja</option>
                  <option value="media">Media</option>
                  <option value="alta">Alta</option>
                </select>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Medidas de Control</label>
                <textarea v-model="form[`${riesgo}_controles`]" rows="2" class="input-field w-full text-sm" placeholder="EPP, controles de ingeniería, administrativos..."></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- EPP Requerido -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-shield text-emerald-500"></i>
          Equipo de Protección Personal (EPP) Requerido
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label v-for="epp in eppItems" :key="epp.key" class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="form.epp" :value="epp.key" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" />
            <span class="text-sm text-gray-700">{{ epp.label }}</span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ExposiciónRiesgosMedicina',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      riesgosFisicos: [
        { key: 'riesgo_ruido', label: 'Ruido' },
        { key: 'riesgo_vibraciones', label: 'Vibraciones' },
        { key: 'riesgo_temperatura_extrema', label: 'Temperatura Extrema' },
        { key: 'riesgo_iluminacion', label: 'Iluminación Inadecuada' },
        { key: 'riesgo_radiacion_ionizante', label: 'Radiación Ionizante' },
        { key: 'riesgo_radiacion_no_ionizante', label: 'Radiación No Ionizante' },
        { key: 'riesgo_presion_anormal', label: 'Presión Anormal' },
      ],
      riesgosQuimicos: [
        { key: 'riesgo_polvos', label: 'Polvos' },
        { key: 'riesgo_humos', label: 'Humos' },
        { key: 'riesgo_gases_vapores', label: 'Gases y Vapores' },
        { key: 'riesgo_solventes', label: 'Solventes' },
        { key: 'riesgo_metales_pesados', label: 'Metales Pesados' },
        { key: 'riesgo_cancerigenos', label: 'Cancerígenos' },
        { key: 'riesgo_pesticidas', label: 'Pesticidas' },
        { key: 'riesgo_otros_quimicos', label: 'Otros Químicos' },
      ],
      riesgosBiologicos: [
        { key: 'riesgo_bacterias', label: 'Bacterias' },
        { key: 'riesgo_virus', label: 'Virus' },
        { key: 'riesgo_hongos', label: 'Hongos' },
        { key: 'riesgo_parasitos', label: 'Parásitos' },
        { key: 'riesgo_sangre_fluidos', label: 'Sangre/Fluidos Corporales' },
        { key: 'riesgo_animales', label: 'Animales/Insectos' },
      ],
      riesgosErgonomicos: [
        { key: 'riesgo_posturas_forzadas', label: 'Posturas Forzadas' },
        { key: 'riesgo_movimientos_repetitivos', label: 'Movimientos Repetitivos' },
        { key: 'riesgo_manipulacion_cargas', label: 'Manipulación de Cargas' },
        { key: 'riesgo_fuerza_excesiva', label: 'Fuerza Excesiva' },
        { key: 'riesgo_trabajo_estatico', label: 'Trabajo Estático' },
        { key: 'riesgo_espacios_confinados', label: 'Espacios Confinados' },
      ],
      riesgosPsicosociales: [
        { key: 'riesgo_carga_mental', label: 'Carga Mental Excesiva' },
        { key: 'riesgo_control_trabajo', label: 'Bajo Control del Trabajo' },
        { key: 'riesgo_jornada_larga', label: 'Jornada Larga/Turnos' },
        { key: 'riesgo_violencia_acoso', label: 'Violencia/Acoso Laboral' },
        { key: 'riesgo_inestabilidad', label: 'Inestabilidad Laboral' },
        { key: 'riesgo_falta_apoyo', label: 'Falta de Apoyo Social' },
      ],
      eppItems: [
        { key: 'casco', label: 'Casco' },
        { key: 'proteccion_auditiva', label: 'Protección Auditiva' },
        { key: 'proteccion_ocular', label: 'Protección Ocular' },
        { key: 'proteccion_respiratoria', label: 'Protección Respiratoria' },
        { key: 'guantes', label: 'Guantes' },
        { key: 'calzado_seguridad', label: 'Calzado de Seguridad' },
        { key: 'ropa_protectora', label: 'Ropa Protectora' },
        { key: 'arnes', label: 'Arnés/Anticaídas' },
        { key: 'proteccion_cara', label: 'Protección Facial' },
        { key: 'otro_epp', label: 'Otro EPP' },
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    riesgosSeleccionados() {
      const todos = [
        ...this.riesgosFisicos,
        ...this.riesgosQuimicos,
        ...this.riesgosBiologicos,
        ...this.riesgosErgonomicos,
        ...this.riesgosPsicosociales
      ]
      return todos.filter(r => this.form[r.key]).map(r => r.label)
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>