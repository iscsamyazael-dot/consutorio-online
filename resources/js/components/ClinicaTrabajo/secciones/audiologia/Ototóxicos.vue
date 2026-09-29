<template>
  <div class="seccion-ototoxicos-audiologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-flask text-purple-600"></i>
        Ototóxicos y Medicamentos
      </h3>

      <p class="text-sm text-gray-600 mb-4">
        Registre el consumo actual o antecedente de medicamentos con potencial ototoxicidad.
        <span class="font-semibold">La ototoxicidad puede ser sinérgica con la exposición a ruido.</span>
      </p>

      <!-- Antibióticos aminoglucósidos -->
      <div class="mb-4 p-3 border rounded-lg bg-red-50">
        <h4 class="font-semibold text-red-800 mb-2 flex items-center gap-2">
          <i class="icon icon-alert"></i>
          Antibióticos Aminoglucósidos (Alta ototoxicidad)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.antibioticos_consume"
              class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
              @change="updateField('antibioticos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Consumo actual o reciente</span>
          </label>
          <div v-if="form.antibioticos_consume" class="md:col-span-2 space-y-2">
            <input
              type="text"
              v-model="form.antibioticos_detalle"
              @input="updateField('antibioticos_detalle', $event.target.value)"
              class="input-field w-full"
              placeholder="Especifique: Gentamicina, Amikacina, Tobramicina, Estreptomicina, Neomicina..."
            />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <input
                type="number"
                v-model.number="form.antibioticos_dosis"
                @input="updateField('antibioticos_dosis', $event.target.value)"
                class="input-field"
                placeholder="Dosis (mg/día)"
              />
              <input
                type="number"
                v-model.number="form.antibioticos_duracion"
                @input="updateField('antibioticos_duracion', $event.target.value)"
                class="input-field"
                placeholder="Duración (días)"
              />
              <input
                type="number"
                v-model.number="form.antibioticos_anos"
                @input="updateField('antibioticos_anos', $event.target.value)"
                class="input-field"
                placeholder="Hace años"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Diuréticos de asa -->
      <div class="mb-4 p-3 border rounded-lg bg-orange-50">
        <h4 class="font-semibold text-orange-800 mb-2 flex items-center gap-2">
          <i class="icon icon-droplet"></i>
          Diuréticos de Asa (Furosemida, Bumetanida)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.diureticos_consume"
              class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
              @change="updateField('diureticos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Consumo actual o reciente</span>
          </label>
          <div v-if="form.diureticos_consume" class="md:col-span-2 space-y-2">
            <input
              type="text"
              v-model="form.diureticos_detalle"
              @input="updateField('diureticos_detalle', $event.target.value)"
              class="input-field w-full"
              placeholder="Furosemida, Bumetanida, Torsemida, Ácido etacrínico..."
            />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <input
                type="number"
                v-model.number="form.diureticos_dosis"
                @input="updateField('diureticos_dosis', $event.target.value)"
                class="input-field"
                placeholder="Dosis (mg/día)"
              />
              <input
                type="number"
                v-model.number="form.diureticos_duracion"
                @input="updateField('diureticos_duracion', $event.target.value)"
                class="input-field"
                placeholder="Duración (días)"
              />
              <input
                type="number"
                v-model.number="form.diureticos_anos"
                @input="updateField('diureticos_anos', $event.target.value)"
                class="input-field"
                placeholder="Hace años"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Salicilatos -->
      <div class="mb-4 p-3 border rounded-lg bg-yellow-50">
        <h4 class="font-semibold text-yellow-800 mb-2 flex items-center gap-2">
          <i class="icon icon-pill"></i>
          Salicilatos (Ácido acetilsalicílico / Aspirina - dosis altas)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.salicilatos_consume"
              class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
              @change="updateField('salicilatos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Consumo crónico / dosis altas (> 3g/día)</span>
          </label>
          <div v-if="form.salicilatos_consume" class="md:col-span-2 space-y-2">
            <input
              type="text"
              v-model="form.salicilatos_detalle"
              @input="updateField('salicilatos_detalle', $event.target.value)"
              class="input-field w-full"
              placeholder="Aspirina, AAS, Otros salicilatos..."
            />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <input
                type="number"
                v-model.number="form.salicilatos_dosis"
                @input="updateField('salicilatos_dosis', $event.target.value)"
                class="input-field"
                placeholder="Dosis (mg/día)"
              />
              <input
                type="number"
                v-model.number="form.salicilatos_duracion"
                @input="updateField('salicilatos_duracion', $event.target.value)"
                class="input-field"
                placeholder="Duración (días/semanas)"
              />
              <input
                type="number"
                v-model.number="form.salicilatos_anos"
                @input="updateField('salicilatos_anos', $event.target.value)"
                class="input-field"
                placeholder="Hace años"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Antimaláricos -->
      <div class="mb-4 p-3 border rounded-lg bg-blue-50">
        <h4 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
          <i class="icon icon-globe"></i>
          Antimaláricos (Cloroquina, Hidroxicloroquina, Quinina)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.antimalarcicos_consume"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              @change="updateField('antimalarcicos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Consumo actual o reciente</span>
          </label>
          <div v-if="form.antimalarcicos_consume" class="md:col-span-2 space-y-2">
            <input
              type="text"
              v-model="form.antimalarcicos_detalle"
              @input="updateField('antimalarcicos_detalle', $event.target.value)"
              class="input-field w-full"
              placeholder="Cloroquina, Hidroxicloroquina, Quinina, Mefloquina..."
            />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
              <input
                type="number"
                v-model.number="form.antimalarcicos_dosis"
                @input="updateField('antimalarcicos_dosis', $event.target.value)"
                class="input-field"
                placeholder="Dosis (mg/día)"
              />
              <input
                type="number"
                v-model.number="form.antimalarcicos_duracion"
                @input="updateField('antimalarcicos_duracion', $event.target.value)"
                class="input-field"
                placeholder="Duración (semanas/meses)"
              />
              <input
                type="number"
                v-model.number="form.antimalarcicos_anos"
                @input="updateField('antimalarcicos_anos', $event.target.value)"
                class="input-field"
                placeholder="Hace años"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Otros ototóxicos -->
      <div class="mb-4 p-3 border rounded-lg bg-gray-50">
        <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
          <i class="icon icon-plus"></i>
          Otros Medicamentos Potencialmente Ototóxicos
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.quimioterapicos_consume"
              class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500"
              @change="updateField('quimioterapicos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Quimioterápicos (Cisplatino, Carboplatino)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.glicopeptidos_consume"
              class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500"
              @change="updateField('glicopeptidos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Glicopéptidos (Vancomicina)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.macrolidos_consume"
              class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500"
              @change="updateField('macrolidos_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Macrólidos (Eritromicina, Azitromicina)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input
              type="checkbox"
              v-model="form.antiretrovirales_consume"
              class="w-4 h-4 text-gray-600 border-gray-300 rounded focus:ring-gray-500"
              @change="updateField('antiretrovirales_consume', $event.target.checked)"
            />
            <span class="text-sm text-gray-700">Antirretrovirales</span>
          </label>
        </div>
        <div v-if="form.quimioterapicos_consume || form.glicopeptidos_consume || form.macrolidos_consume || form.antiretrovirales_consume" class="mt-2">
          <textarea
            v-model="form.otros_ototoxicos_detalle"
            @input="updateField('otros_ototoxicos_detalle', $event.target.value)"
            rows="2"
            class="input-field w-full"
            placeholder="Detalle otros medicamentos, dosis, duración..."
          ></textarea>
        </div>
      </div>

      <!-- Resumen de riesgo ototóxico -->
      <div v-if="ototoxicosActivos.length > 0" class="p-3 bg-purple-50 border border-purple-200 rounded">
        <h4 class="font-semibold text-purple-800 mb-2">⚠️ Factores de Riesgo Ototóxico Identificados</h4>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="o in ototoxicosActivos"
            :key="o.key"
            class="badge badge-purple"
          >
            {{ o.label }}
          </span>
        </div>
        <p class="text-xs text-purple-700 mt-2">
          <strong>Recomendación:</strong> Audiometría de línea base y seguimiento cada 6 meses.
          Riesgo sinérgico con exposición a ruido laboral > 85 dB(A).
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OtotóxicosAudiologia',
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
    ototoxicosActivos() {
      const activos = []
      if (this.form.antibioticos_consume) activos.push({ key: 'antibioticos', label: 'Aminoglucósidos' })
      if (this.form.diureticos_consume) activos.push({ key: 'diureticos', label: 'Diuréticos asa' })
      if (this.form.salicilatos_consume) activos.push({ key: 'salicilatos', label: 'Salicilatos' })
      if (this.form.antimalarcicos_consume) activos.push({ key: 'antimalaricos', label: 'Antimaláricos' })
      if (this.form.quimioterapicos_consume) activos.push({ key: 'quimioterapicos', label: 'Quimioterápicos' })
      if (this.form.glicopeptidos_consume) activos.push({ key: 'glicopeptidos', label: 'Glicopéptidos' })
      if (this.form.macrolidos_consume) activos.push({ key: 'macrolidos', label: 'Macrólidos' })
      if (this.form.antiretrovirales_consume) activos.push({ key: 'antiretrovirales', label: 'Antirretrovirales' })
      return activos
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
.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
}
.badge-purple {
  background: #f3e8ff;
  color: #7e22ce;
}
</style>