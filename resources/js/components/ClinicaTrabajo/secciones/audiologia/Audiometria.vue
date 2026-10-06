<template>
  <div class="seccion-audiometria-audiologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-sound text-indigo-600"></i>
        Audiometría Tonental Puro (18 Frecuencias)
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Registro de umbrales auditivos para cada oído y frecuencia. Se calculará automáticamente el PTA (Promedio Tonal Puro).
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <!-- Tabla audiometría - Oído Derecho -->
        <div>
          <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="icon icon-ear text-red-500"></i>
            Oído Derecho (Der)
          </h4>

          <div class="grid grid-cols-5 gap-2 text-center">
            <div
              v-for="f in frecuencias"
              :key="f"
              class="border rounded p-2 text-center font-medium text-sm"
              :class="[
                'text-red-600 border-red-200',
                form['au_' + f + '_der'] > 80 ? 'bg-red-100' : '',
                form['au_' + f + '_der'] > 50 ? 'bg-yellow-100' : '',
              ]"
            >
              {{ form['au_' + f + '_der'] || '—' }}
            </div>
          </div>

          <div class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">PTA Derecho (500-1000-2000-3000 Hz)</label>
            <div class="input-field w-full bg-indigo-50 text-indigo-700 font-bold py-3 px-4 rounded text-xl" v-if="ptaDerCalculado">
              <strong>{{ ptaDerCalculado }}</strong> dB NPA
            </div>
            <div class="input-field w-full bg-gray-200 text-gray-400 py-3 px-4 rounded text-xl" v-else>
              <strong>—</strong>
            </div>
            <p class="text-xs text-indigo-600 mt-1">Promedio de: 500 + 1000 + 2000 + 3000 Hz</p>
          </div>

          <div class="mt-3 text-xs">
            <span class="font-medium text-gray-600">Clasificación:</span>
            <span v-if="ptaDerCalculado <= 20" class="text-green-600">Normal</span>
            <span v-else-if="ptaDerCalculado <= 40" class="text-blue-600">Leve</span>
            <span v-else-if="ptaDerCalculado <= 60" class="text-indigo-600">Moderada</span>
            <span v-else-if="ptaDerCalculado <= 90" class="text-orange-600">Severa</span>
            <span v-else class="text-red-600">Profunda</span>
          </div>
        </div>

        <!-- Tabla audiometría - Oído Izquierdo -->
        <div>
          <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="icon icon-ear text-indigo-500"></i>
            Oído Izquierdo (Izq)
          </h4>

          <div class="grid grid-cols-5 gap-2 text-center">
            <div
              v-for="f in frecuencias"
              :key="f"
              class="border rounded p-2 text-center font-medium text-sm"
              :class="[
                'text-indigo-600 border-indigo-200',
                form['au_' + f + '_izq'] > 80 ? 'bg-red-100' : '',
                form['au_' + f + '_izq'] > 50 ? 'bg-yellow-100' : '',
              ]"
            >
              {{ form['au_' + f + '_izq'] || '—' }}
            </div>
          </div>

          <div class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">PTA Izquierdo (500-1000-2000-3000 Hz)</label>
            <div class="input-field w-full bg-indigo-50 text-indigo-700 font-bold py-3 px-4 rounded text-xl" v-if="ptaIzqCalculado">
              <strong>{{ ptaIzqCalculado }}</strong> dB NPA
            </div>
            <div class="input-field w-full bg-gray-200 text-gray-400 py-3 px-4 rounded text-xl" v-else>
              <strong>—</strong>
            </div>
            <p class="text-xs text-indigo-600 mt-1">Promedio de: 500 + 1000 + 2000 + 3000 Hz</p>
          </div>

          <div class="mt-3 text-xs">
            <span class="font-medium text-gray-600">Clasificación:</span>
            <span v-if="ptaIzqCalculado <= 20" class="text-green-600">Normal</span>
            <span v-else-if="ptaIzqCalculado <= 40" class="text-blue-600">Leve</span>
            <span v-else-if="ptaIzqCalculado <= 60" class="text-indigo-600">Moderada</span>
            <span v-else-if="ptaIzqCalculado <= 90" class="text-orange-600">Severa</span>
            <span v-else class="text-red-600">Profunda</span>
          </div>
        </div>
      </div>

      <!-- Comparativo riesgo y recomendaciones -->
      <div v-if="ptaDerCalculado || ptaIzqCalculado" class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-chart text-indigo-600"></i>
          Análisis de Riesgo y Clasificación
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
          <div>
            <span class="font-semibold text-gray-600">PTA Promedio (Der+Izq)/2: </span>
            <span :class="ptaPromedioClass">{{ ptaPromedioCalculado }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Tipo Hipoacusia: </span>
            <span :class="tipoHipoacusiaClass">{{ tipoHipoacusia }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-600">Nivel: </span>
            <span :class="nivelClass">{{ nivelHipoacusia }}</span>
          </div>
        </div>

        <div class="mt-3">
          <p class="text-xs text-indigo-600">
            <strong>PTA ≤ 20 dB:</strong> Hipoacusia normal
            <br />
            <strong>PTA 21-40 dB:</strong> Hipoacusia leve - Dificultad en ruido, discursos rápidos
            <br />
            <strong>PTA 41-60 dB:</strong> Hipoacusia moderada - Dificultad en conversaciones normales
            <br />
            <strong>PTA 61-90 dB:</strong> Hipoacusia severa - Solo conversaciones intensas
            <br />
            <strong>PTA > 90 dB:</strong> Hipoacusia profunda - Solo sonidos muy intensos
          </p>
        </div>
      </div>

      <!-- Recomendaciones según PTA -->
      <div v-if="ptaPromedioCalculado" class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded">
        <h4 class="font-semibold text-indigo-800 mb-2">Recomendaciones Clínicas</h4>
        <ul class="text-sm text-indigo-700 space-y-1">
          <li v-if="ptaPromedioCalculado <= 20">
            <i class="icon icon-check text-green-500 mr-1"></i>
            Hearing within normal limits. Annual monitoring recommended if risk factors present.
          </li>
          <li v-else-if="ptaPromedioCalculado <= 40">
            <i class="icon icon-check text-green-500 mr-1"></i>
            Consider hearing conservation. Avoid excessive noise. Re-evaluate annually.
          </li>
          <li v-else-if="ptaPromedioCalculado <= 60">
            <i class="icon icon-alert text-orange-500 mr-1"></i>
            Hearing aids may be beneficial. Occupational hearing conservation program. Re-evaluate every 6 months.
          </li>
          <li v-else-if="ptaPromedioCalculado <= 90">
            <i class="icon icon-alert text-red-500 mr-1"></i>
            Hearing aids strongly recommended. Audiological rehabilitation. ENT referral. Re-evaluate every 3 months.
          </li>
          <li v-else>
            <i class="icon icon-alert text-red-500 mr-1"></i>
            Severe/profound hearing loss. Consider cochlear implant evaluation. Intensive rehabilitation.
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AudiometríaAudiologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      frecuencias: [125, 250, 500, 1000, 2000, 3000, 4000, 6000, 8000]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    ptaDerCalculado() {
      if (
        this.form.au_500_hz_der && this.form.au_1000_hz_der &&
        this.form.au_2000_hz_der && this.form.au_3000_hz_der
      ) {
        const valores = [this.form.au_500_hz_der, this.form.au_1000_hz_der, this.form.au_2000_hz_der, this.form.au_3000_hz_der]
        .filter(v => v > 0)
        return Math.round(valores.reduce((a, b) => a + b, 0) / valores.length)
      }
      return null
    },
    ptaIzqCalculado() {
      if (
        this.form.au_500_hz_izq && this.form.au_1000_hz_izq &&
        this.form.au_2000_hz_izq && this.form.au_3000_hz_izq
      ) {
        const valores = [this.form.au_500_hz_izq, this.form.au_1000_hz_izq, this.form.au_2000_hz_izq, this.form.au_3000_hz_izq]
        .filter(v => v > 0)
        return Math.round(valores.reduce((a, b) => a + b, 0) / valores.length)
      }
      return null
    },
    ptaPromedioCalculado() {
      if (this.ptaDerCalculado && this.ptaIzqCalculado) {
        return Math.round((this.ptaDerCalculado + this.ptaIzqCalculado) / 2)
      }
      if (this.ptaDerCalculado) return this.ptaDerCalculado
      if (this.ptaIzqCalculado) return this.ptaIzqCalculado
      return null
    },
    tipoHipoacusia() {
      if (!this.ptaPromedioCalculado) return ''
      const pta = this.ptaPromedioCalculado
      if (pta <= 20) return 'Normal'
      if (pta <= 40) return 'Leve'
      if (pta <= 60) return 'Moderada'
      if (pta <= 90) return 'Severa'
      return 'Profunda'
    },
    tipoHipoacusiaClass() {
      if (!this.tipoHipoacusia) return ''
      const clases = {
        Normal: 'text-green-600',
        Leve: 'text-blue-600',
        Moderada: 'text-indigo-600',
        Severa: 'text-orange-600',
        Profunda: 'text-red-600'
      }
      return clases[this.tipoHipoacusia] || ''
    },
    nivelClass() {
      if (!this.tipoHipoacusia) return ''
      const clases = {
        Normal: 'text-green-600',
        Leve: 'text-blue-600',
        Moderada: 'text-indigo-600',
        Severa: 'text-orange-600',
        Profunda: 'text-red-600'
      }
      return clases[this.tipoHipoacusia] || ''
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