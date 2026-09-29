<template>
  <div class="seccion-antecedentes-laborales-audiologia">
    <div class="bg-white border rounded-lg p-4 mb-4">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="icon icon-briefcase text-blue-600"></i>
        Antecedentes Laborales y Ototóxicos
      </h3>

      <!-- Exposición laboral detallada -->
      <div class="mb-4">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-industry text-gray-600"></i>
          Historial Laboral (Exposición a Ruido)
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Empresa Anterior</label>
            <input
              type="text"
              v-model="form.empresa_anterior"
              @input="updateField('empresa_anterior', $event.target.value)"
              class="input-field w-full"
              placeholder="Nombre de la empresa"
            />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Puesto Anterior</label>
            <input
              type="text"
              v-model="form.puesto_anterior"
              @input="updateField('puesto_anterior', $event.target.value)"
              class="input-field w-full"
              placeholder="Cargo/puesto"
            />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Años en Puesto Anterior</label>
            <input
              type="number"
              v-model.number="form.anos_puesto_anterior"
              @input="updateField('anos_puesto_anterior', $event.target.value)"
              min="0"
              max="50"
              class="input-field w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nivel Ruido Anterior (dB)</label>
            <input
              type="number"
              v-model.number="form.nivel_ruido_anterior"
              @input="updateField('nivel_ruido_anterior', $event.target.value)"
              min="0"
              max="140"
              class="input-field w-full"
            />
          </div>
        </div>
      </div>

      <!-- Ototóxicos laborales -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-flask text-purple-600"></i>
          Ototóxicos Laborales (Medicamentos/Sustancias)
        </h4>
        <p class="text-sm text-gray-600 mb-3">¿Consume o ha estado expuesto a:</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <label
            v-for="ototoxico in ototoxicosLaborales"
            :key="ototoxico.key"
            class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition"
          >
            <input
              type="checkbox"
              :checked="form[ototoxico.key]"
              @change="updateField(ototoxico.key, $event.target.checked)"
              class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
            />
            <span class="text-sm text-gray-700">{{ ototoxico.label }}</span>
          </label>
        </div>
      </div>

      <!-- Pasatiempos ruidosos -->
      <div class="mb-4 pt-4 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
          <i class="icon icon-music text-pink-600"></i>
          Pasatiempos y Actividades Ruidosas
        </h4>
        <p class="text-sm text-gray-600 mb-3">Actividades recreativas con exposición a ruido:</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <label
            v-for="pasatiempo in pasatiemposRuidosos"
            :key="pasatiempo.key"
            class="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50 transition"
          >
            <input
              type="checkbox"
              :checked="form[pasatiempo.key]"
              @change="updateField(pasatiempo.key, $event.target.checked)"
              class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
            />
            <span class="text-sm text-gray-700">{{ pasatiempo.label }}</span>
          </label>
        </div>

        <div v-if="form.otros_pasatiempos_ruidosos" class="mt-3">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Otros pasatiempos ruidosos (especificar)</label>
          <input
            type="text"
            v-model="form.otros_pasatiempos_detalle"
            @input="updateField('otros_pasatiempos_detalle', $event.target.value)"
            class="input-field w-full"
            placeholder="Describa otros pasatiempos con ruido intenso..."
          />
        </div>
      </div>

      <!-- Resumen de riesgo -->
      <div class="p-3 bg-indigo-50 border border-indigo-200 rounded">
        <h4 class="font-semibold text-indigo-800 mb-2">Resumen de Exposición Acumulada</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
          <div>
            <span class="font-semibold text-gray-600">Años exposición total: </span>
            {{ (form.anos_exposicion_ruido || 0) + (form.anos_puesto_anterior || 0) }} años
          </div>
          <div>
            <span class="font-semibold text-gray-600">Ototóxicos laborales: </span>
            {{ ototoxicosActivos.length }}/{{ ototoxicosLaborales.length }}
          </div>
          <div>
            <span class="font-semibold text-gray-600">Pasatiempos ruidosos: </span>
            {{ pasatiemposActivos.length }}/{{ pasatiemposRuidosos.length }}
          </div>
        </div>
        <div v-if="ototoxicosActivos.length > 0 || pasatiemposActivos.length > 0" class="mt-2 text-xs text-indigo-700">
          <p v-if="ototoxicosActivos.length > 0"><strong>Ototóxicos:</strong> {{ ototoxicosActivos.join(', ') }}</p>
          <p v-if="pasatiemposActivos.length > 0"><strong>Pasatiempos:</strong> {{ pasatiemposActivos.join(', ') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AntecedentesLaboralesAudiologia',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      ototoxicosLaborales: [
        { key: 'antibioticos_consume', label: 'Antibióticos (aminoglucósidos)' },
        { key: 'diureticos_consume', label: 'Diuréticos de asa (furosemida)' },
        { key: 'salicilatos_consume', label: 'Salicilatos (aspirina altas dosis)' },
        { key: 'antimalarcicos_consume', label: 'Antimaláricos (cloroquina)' }
      ],
      pasatiemposRuidosos: [
        { key: 'armas_fuego_caza', label: 'Armas de fuego / Caza' },
        { key: 'niveles_altos_musica', label: 'Música a volumen alto / Conciertos' },
        { key: 'lugares_ruidosos_discoteca', label: 'Discotecas / Bares ruidosos' },
        { key: 'automovilismo_motociclismo', label: 'Automovilismo / Motociclismo' },
        { key: 'otros_pasatiempos_ruidosos', label: 'Otros (especificar)' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    ototoxicosActivos() {
      return this.ototoxicosLaborales
        .filter(o => this.form[o.key])
        .map(o => o.label)
    },
    pasatiemposActivos() {
      return this.pasatiemposRuidosos
        .filter(p => this.form[p.key] && p.key !== 'otros_pasatiempos_ruidosos')
        .map(p => p.label)
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