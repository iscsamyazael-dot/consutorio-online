<template>
  <div class="seccion-bioquimica-nutricion">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-flask text-purple-600"></i>
        Bioquímica Sanguínea
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Ingrese valores de laboratorio. Se comparan con valores de referencia estándar.
      </p>

      <!-- Perfil glucémico y lípidos -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        <div class="border rounded-lg p-3" :class="glucosaClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Glucosa (mg/dL)</label>
          <input
            type="number"
            :value="form.glucosa_mg_dl"
            @input="updateField('glucosa_mg_dl', $event.target.value)"
            step="1"
            min="0"
            max="500"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: 70-100 (ayunas)</div>
          <div class="text-xs mt-1" v-if="form.glucosa_mg_dl">
            <span :class="glucosaStatusClass">{{ glucosaStatus }}</span>
          </div>
        </div>

        <div class="border rounded-lg p-3" :class="trigliceridosClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Triglicéridos (mg/dL)</label>
          <input
            type="number"
            :value="form.trigliceridos_mg_dl"
            @input="updateField('trigliceridos_mg_dl', $event.target.value)"
            step="1"
            min="0"
            max="1000"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: &lt; 150</div>
          <div class="text-xs mt-1" v-if="form.trigliceridos_mg_dl">
            <span :class="trigliceridosStatusClass">{{ trigliceridosStatus }}</span>
          </div>
        </div>

        <div class="border rounded-lg p-3" :class="colesterolClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Colesterol Total (mg/dL)</label>
          <input
            type="number"
            :value="form.colesterol_total_mg_dl"
            @input="updateField('colesterol_total_mg_dl', $event.target.value)"
            step="1"
            min="0"
            max="500"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: &lt; 200</div>
          <div class="text-xs mt-1" v-if="form.colesterol_total_mg_dl">
            <span :class="colesterolStatusClass">{{ colesterolStatus }}</span>
          </div>
        </div>

        <div class="border rounded-lg p-3" :class="hdlClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">HDL (mg/dL)</label>
          <input
            type="number"
            :value="form.hdl_mg_dl"
            @input="updateField('hdl_mg_dl', $event.target.value)"
            step="1"
            min="0"
            max="200"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: H &gt;40, M &gt;50</div>
          <div class="text-xs mt-1" v-if="form.hdl_mg_dl">
            <span :class="hdlStatusClass">{{ hdlStatus }}</span>
          </div>
        </div>

        <div class="border rounded-lg p-3" :class="ldlClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">LDL (mg/dL)</label>
          <input
            type="number"
            :value="form.ldl_mg_dl"
            @input="updateField('ldl_mg_dl', $event.target.value)"
            step="1"
            min="0"
            max="500"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: &lt; 100 (óptimo)</div>
          <div class="text-xs mt-1" v-if="form.ldl_mg_dl">
            <span :class="ldlStatusClass">{{ ldlStatus }}</span>
          </div>
        </div>

        <div class="border rounded-lg p-3" :class="acidoUricoClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Ácido Úrico (mg/dL)</label>
          <input
            type="number"
            :value="form.acido_urico_mg_dl"
            @input="updateField('acido_urico_mg_dl', $event.target.value)"
            step="0.1"
            min="0"
            max="20"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: H 3.4-7.0, M 2.4-6.0</div>
          <div class="text-xs mt-1" v-if="form.acido_urico_mg_dl">
            <span :class="acidoUricoStatusClass">{{ acidoUricoStatus }}</span>
          </div>
        </div>

        <div class="border rounded-lg p-3" :class="hemoglobinaClass">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Hemoglobina (g/dL)</label>
          <input
            type="number"
            :value="form.hemoglobina_g_dl"
            @input="updateField('hemoglobina_g_dl', $event.target.value)"
            step="0.1"
            min="0"
            max="25"
            class="input-field w-full"
          />
          <div class="text-xs text-gray-500 mt-1">Referencia: H 13.5-17.5, M 12.0-15.5</div>
          <div class="text-xs mt-1" v-if="form.hemoglobina_g_dl">
            <span :class="hemoglobinaStatusClass">{{ hemoglobinaStatus }}</span>
          </div>
        </div>
      </div>

      <!-- Cálculo LDL estimado (Friedewald) -->
      <div v-if="ldlEstimado !== null" class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded">
        <h4 class="font-semibold text-purple-800 mb-1">LDL Estimado (Fórmula Friedewald)</h4>
        <div class="text-sm text-purple-700">
          LDL = Colesterol Total - HDL - (Triglicéridos/5)
          = {{ form.colesterol_total_mg_dl }} - {{ form.hdl_mg_dl }} - ({{ form.trigliceridos_mg_dl }}/5)
          = <strong>{{ ldlEstimado }} mg/dL</strong>
        </div>
        <p class="text-xs text-purple-600 mt-1">Válido solo si Triglicéridos &lt; 400 mg/dL</p>
      </div>

      <!-- Perfil de riesgo cardiovascular -->
      <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-heart text-red-600"></i>
          Perfil de Riesgo Cardiovascular
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
          <div v-if="form.trigliceridos_mg_dl && form.hdl_mg_dl">
            <span class="font-semibold text-gray-600">Índice Aterogénico (TG/HDL): </span>
            <span class="font-bold" :class="indiceAterogenicoClass">{{ indiceAterogenico }}</span>
            <span class="ml-2" :class="indiceAterogenicoClass">{{ indiceAterogenicoRiesgo }}</span>
          </div>
          <div v-if="form.colesterol_total_mg_dl && form.hdl_mg_dl">
            <span class="font-semibold text-gray-600">Ratio Col/HDL: </span>
            <span class="font-bold" :class="ratioColHdlClass">{{ ratioColHdl }}</span>
            <span class="ml-2" :class="ratioColHdlClass">{{ ratioColHdlRiesgo }}</span>
          </div>
          <div v-if="form.glucosa_mg_dl">
            <span class="font-semibold text-gray-600">Glucosa en ayunas: </span>
            <span :class="glucosaStatusClass">{{ glucosaStatus }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Síndrome Metabólico: </span>
            <span class="font-bold" :class="sindromeMetabolicoClass">{{ sindromeMetabolico }}</span>
          </div>
        </div>
      </div>

      <!-- Tabla de referencia -->
      <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-table"></i>
          Valores de Referencia (Adultos)
        </h4>
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-gray-300">
              <th class="text-left p-1">Parámetro</th>
              <th class="text-left p-1">Hombres</th>
              <th class="text-left p-1">Mujeres</th>
              <th class="text-left p-1">Alerta</th>
            </tr>
          </thead>
          <tbody>
            <tr><td class="p-1 font-medium">Glucosa</td><td class="p-1">70-100</td><td class="p-1">70-100</td><td class="p-1 text-red-600">&ge;126 DM</td></tr>
            <tr><td class="p-1 font-medium">Triglicéridos</td><td class="p-1">&lt;150</td><td class="p-1">&lt;150</td><td class="p-1 text-red-600">&ge;200</td></tr>
            <tr><td class="p-1 font-medium">Col. Total</td><td class="p-1">&lt;200</td><td class="p-1">&lt;200</td><td class="p-1 text-red-600">&ge;240</td></tr>
            <tr><td class="p-1 font-medium">HDL</td><td class="p-1">&gt;40</td><td class="p-1">&gt;50</td><td class="p-1 text-red-600">&lt;40/&lt;50</td></tr>
            <tr><td class="p-1 font-medium">LDL</td><td class="p-1">&lt;100</td><td class="p-1">&lt;100</td><td class="p-1 text-red-600">&ge;160</td></tr>
            <tr><td class="p-1 font-medium">Ác. Úrico</td><td class="p-1">3.4-7.0</td><td class="p-1">2.4-6.0</td><td class="p-1 text-red-600">&gt;7.0/&gt;6.0</td></tr>
            <tr><td class="p-1 font-medium">Hb</td><td class="p-1">13.5-17.5</td><td class="p-1">12.0-15.5</td><td class="p-1 text-red-600">&lt;13.5/&lt;12.0</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
// Clases completas (no construidas con .replace) para que Tailwind no las purgue
const CARD_CLASSES = {
  'text-green-600': 'border-green-200 bg-green-50',
  'text-yellow-600': 'border-yellow-200 bg-yellow-50',
  'text-orange-600': 'border-orange-200 bg-orange-50',
  'text-red-600': 'border-red-200 bg-red-50',
  'text-blue-600': 'border-blue-200 bg-blue-50'
}

export default {
  name: 'BioquimicaNutricion',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },

    esMujer() {
      const genero = this.form.paciente?.genero
      return genero === 'F' || genero === 'Femenino'
    },

    // Glucosa
    glucosaStatus() {
      if (!this.form.glucosa_mg_dl) return ''
      const v = this.form.glucosa_mg_dl
      if (v < 70) return 'Hipoglucemia'
      if (v <= 100) return 'Normal'
      if (v <= 125) return 'Glucosa alterada en ayunas'
      return 'Diabetes probable'
    },
    glucosaStatusClass() {
      if (!this.form.glucosa_mg_dl) return ''
      const v = this.form.glucosa_mg_dl
      if (v < 70) return 'text-orange-600'
      if (v <= 100) return 'text-green-600'
      if (v <= 125) return 'text-yellow-600'
      return 'text-red-600'
    },
    glucosaClass() {
      return this.cardClass(this.glucosaStatusClass)
    },

    // Triglicéridos
    trigliceridosStatus() {
      if (!this.form.trigliceridos_mg_dl) return ''
      const v = this.form.trigliceridos_mg_dl
      if (v < 150) return 'Normal'
      if (v < 200) return 'Límite alto'
      if (v < 500) return 'Alto'
      return 'Muy alto'
    },
    trigliceridosStatusClass() {
      if (!this.form.trigliceridos_mg_dl) return ''
      const v = this.form.trigliceridos_mg_dl
      if (v < 150) return 'text-green-600'
      if (v < 200) return 'text-yellow-600'
      if (v < 500) return 'text-orange-600'
      return 'text-red-600'
    },
    trigliceridosClass() {
      return this.cardClass(this.trigliceridosStatusClass)
    },

    // Colesterol Total
    colesterolStatus() {
      if (!this.form.colesterol_total_mg_dl) return ''
      const v = this.form.colesterol_total_mg_dl
      if (v < 200) return 'Deseable'
      if (v < 240) return 'Límite alto'
      return 'Alto'
    },
    colesterolStatusClass() {
      if (!this.form.colesterol_total_mg_dl) return ''
      const v = this.form.colesterol_total_mg_dl
      if (v < 200) return 'text-green-600'
      if (v < 240) return 'text-yellow-600'
      return 'text-red-600'
    },
    colesterolClass() {
      return this.cardClass(this.colesterolStatusClass)
    },

    // HDL
    hdlStatus() {
      if (!this.form.hdl_mg_dl) return ''
      const v = this.form.hdl_mg_dl
      const limite = this.esMujer ? 50 : 40
      if (v >= limite) return 'Óptimo/Protector'
      if (v >= 40) return 'Aceptable'
      return 'Bajo (Riesgo ↑)'
    },
    hdlStatusClass() {
      if (!this.form.hdl_mg_dl) return ''
      const v = this.form.hdl_mg_dl
      const limite = this.esMujer ? 50 : 40
      if (v >= limite) return 'text-green-600'
      if (v >= 40) return 'text-yellow-600'
      return 'text-red-600'
    },
    hdlClass() {
      return this.cardClass(this.hdlStatusClass)
    },

    // LDL
    ldlStatus() {
      if (!this.form.ldl_mg_dl) return ''
      const v = this.form.ldl_mg_dl
      if (v < 100) return 'Óptimo'
      if (v < 130) return 'Casi óptimo'
      if (v < 160) return 'Límite alto'
      if (v < 190) return 'Alto'
      return 'Muy alto'
    },
    ldlStatusClass() {
      if (!this.form.ldl_mg_dl) return ''
      const v = this.form.ldl_mg_dl
      if (v < 100) return 'text-green-600'
      if (v < 130) return 'text-blue-600'
      if (v < 160) return 'text-yellow-600'
      if (v < 190) return 'text-orange-600'
      return 'text-red-600'
    },
    ldlClass() {
      return this.cardClass(this.ldlStatusClass)
    },

    // Ácido Úrico
    acidoUricoStatus() {
      if (!this.form.acido_urico_mg_dl) return ''
      const max = this.esMujer ? 6.0 : 7.0
      return this.form.acido_urico_mg_dl <= max ? 'Normal' : 'Hiperuricemia'
    },
    acidoUricoStatusClass() {
      if (!this.form.acido_urico_mg_dl) return ''
      const max = this.esMujer ? 6.0 : 7.0
      return this.form.acido_urico_mg_dl <= max ? 'text-green-600' : 'text-red-600'
    },
    acidoUricoClass() {
      return this.cardClass(this.acidoUricoStatusClass)
    },

    // Hemoglobina
    hemoglobinaStatus() {
      if (!this.form.hemoglobina_g_dl) return ''
      const v = this.form.hemoglobina_g_dl
      const min = this.esMujer ? 12.0 : 13.5
      const max = this.esMujer ? 15.5 : 17.5
      if (v < min) return 'Anemia'
      if (v <= max) return 'Normal'
      return 'Policitemia/Concentración'
    },
    hemoglobinaStatusClass() {
      if (!this.form.hemoglobina_g_dl) return ''
      const v = this.form.hemoglobina_g_dl
      const min = this.esMujer ? 12.0 : 13.5
      const max = this.esMujer ? 15.5 : 17.5
      if (v < min) return 'text-red-600'
      if (v <= max) return 'text-green-600'
      return 'text-blue-600'
    },
    hemoglobinaClass() {
      return this.cardClass(this.hemoglobinaStatusClass)
    },

    // LDL Estimado (Friedewald)
    ldlEstimado() {
      const { colesterol_total_mg_dl: ct, hdl_mg_dl: hdl, trigliceridos_mg_dl: tg } = this.form
      if (ct && hdl && tg && tg < 400) {
        return Math.round(ct - hdl - tg / 5)
      }
      return null
    },

    // Índice Aterogénico
    indiceAterogenico() {
      if (this.form.trigliceridos_mg_dl && this.form.hdl_mg_dl > 0) {
        return (this.form.trigliceridos_mg_dl / this.form.hdl_mg_dl).toFixed(2)
      }
      return null
    },
    indiceAterogenicoRiesgo() {
      if (!this.indiceAterogenico) return ''
      const v = parseFloat(this.indiceAterogenico)
      if (v < 3.5) return 'Bajo'
      if (v < 5) return 'Moderado'
      return 'Alto'
    },
    indiceAterogenicoClass() {
      if (!this.indiceAterogenico) return ''
      const v = parseFloat(this.indiceAterogenico)
      if (v < 3.5) return 'text-green-600'
      if (v < 5) return 'text-yellow-600'
      return 'text-red-600'
    },

    // Ratio Col/HDL
    ratioColHdl() {
      if (this.form.colesterol_total_mg_dl && this.form.hdl_mg_dl > 0) {
        return (this.form.colesterol_total_mg_dl / this.form.hdl_mg_dl).toFixed(2)
      }
      return null
    },
    ratioColHdlRiesgo() {
      if (!this.ratioColHdl) return ''
      const v = parseFloat(this.ratioColHdl)
      if (v < 3.5) return 'Óptimo'
      if (v < 5) return 'Moderado'
      return 'Alto'
    },
    ratioColHdlClass() {
      if (!this.ratioColHdl) return ''
      const v = parseFloat(this.ratioColHdl)
      if (v < 3.5) return 'text-green-600'
      if (v < 5) return 'text-yellow-600'
      return 'text-red-600'
    },

    // Síndrome Metabólico (criterios ATP III / IDF)
    sindromeMetabolico() {
      let criterios = 0

      // Cintura
      if (this.form.circunferencia_cintura_cm) {
        const limite = this.esMujer ? 88 : 102
        if (this.form.circunferencia_cintura_cm > limite) criterios++
      }

      // Triglicéridos
      if (this.form.trigliceridos_mg_dl >= 150) criterios++

      // HDL
      if (this.form.hdl_mg_dl) {
        const limite = this.esMujer ? 50 : 40
        if (this.form.hdl_mg_dl < limite) criterios++
      }

      // Glucosa
      if (this.form.glucosa_mg_dl >= 100) criterios++

      // Presión arterial: no disponible aquí, no se cuenta

      if (criterios >= 3) return `SÍ (${criterios}/5 criterios)`
      if (criterios > 0) return `Parcial (${criterios}/5 criterios)`
      return 'No'
    },
    sindromeMetabolicoClass() {
      if (this.sindromeMetabolico.startsWith('SÍ')) return 'text-red-600'
      if (this.sindromeMetabolico.startsWith('Parcial')) return 'text-yellow-600'
      return 'text-green-600'
    }
  },
  methods: {
    cardClass(statusClass) {
      return CARD_CLASSES[statusClass] || ''
    },
    updateField(key, value) {
      const num = value === '' ? null : parseFloat(value)
      this.$emit('update:modelValue', {
        ...this.form,
        [key]: Number.isNaN(num) ? null : num
      })
    }
  }
}
</script>

<style scoped>
/* Estilos heredados */
</style>