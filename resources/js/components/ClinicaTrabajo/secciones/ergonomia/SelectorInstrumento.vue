<template>
  <div class="seccion-selector-instrumento-ergonomia">
    <div class="card mb-4">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h3 class="mb-0">
          <i class="fas fa-tools mr-2"></i>
          Seleccionar Instrumento de Evaluación Ergonómica
        </h3>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Seleccione el instrumento de evaluación ergonómica que será utilizado. Cada instrumento tiene campos específicos.
        </p>

        <!-- Fila 1: Tarjetas de instrumentos -->
        <div class="row mb-4">
          <div v-for="instrumento in instrumentosDisponibles" :key="instrumento.key" class="col-md-3 mb-3">
            <label class="instrumento-card d-block p-3 border rounded"
                   :class="{
                     'instrumento-selected': instrumento.key === form.instrumento_utilizado,
                     'instrumento-default': instrumento.key !== form.instrumento_utilizado && form.instrumento_utilizado,
                     'instrumento-normal': !form.instrumento_utilizado
                   }"
                   style="cursor: pointer; transition: all 0.2s;">
              <div class="text-center mb-2" style="font-size: 2rem;">{{ instrumento.icono }}</div>
              <div class="font-weight-bold text-center">{{ instrumento.nombre }}</div>
              <div class="text-muted small text-center">{{ instrumento.descripcion }}</div>
              <div class="mt-2 pt-2 border-top text-center">
                <small style="color: #2E8B57;">{{ instrumento.puntuacionMaxima }} pts max</small>
              </div>
              <input
                type="radio"
                name="grupoInstrumento"
                :value="instrumento.key"
                v-model="form.instrumento_utilizado"
                @change="onInstrumentoSeleccionado(instrumento.key)"
                style="position: absolute; opacity: 0;"
              />
            </label>
          </div>
        </div>

        <!-- Mostrar formulario específico según instrumento -->
        <div v-if="form.instrumento_utilizado" class="mt-4 p-4 border-top">
          <h4 class="font-weight-bold mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            Formulario Específico: {{ instrumentoSeleccionado?.nombre }}
          </h4>
          <p class="text-muted small mb-3">
            Los siguientes campos se adaptarán automáticamente según el instrumento seleccionado.
          </p>
          <InstrumentoEspecifico
            :modelValue="form"
            @update:modelValue="updateForm"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import InstrumentoEspecifico from './InstrumentoEspecifico.vue'

export default {
  name: 'SelectorInstrumentoErgonomia',
  components: {
    InstrumentoEspecifico
  },
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      instrumentosDisponibles: [
        {
          key: 'RULA',
          nombre: 'RULA',
          icono: '📊',
          descripcion: 'Rapid Upper Limb Assessment - Evaluación de extremidades superiores',
          puntuacionMaxima: 7
        },
        {
          key: 'REBA',
          nombre: 'REBA',
          icono: '📈',
          descripcion: 'Rapid Entire Body Assessment - Evaluación corporal completa',
          puntuacionMaxima: 15
        },
        {
          key: 'NIOSH',
          nombre: 'NIOSH',
          icono: '📋',
          descripcion: 'National Institute for Occupational Safety and Health - Evaluación de levantamiento',
          puntuacionMaxima: 10
        },
        {
          key: 'OTRO',
          nombre: 'OTRO',
          icono: '🔧',
          descripcion: 'Otro instrumento personalizado',
          puntuacionMaxima: 0
        }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    instrumentoSeleccionado() {
      return this.instrumentosDisponibles.find(i => i.key === this.form.instrumento_utilizado)
    }
  },
  methods: {
    updateField(key, value) {
      this.$emit('update:modelValue', { ...this.form, [key]: value })
    },
    updateForm(nuevoForm) {
      this.$emit('update:modelValue', nuevoForm)
    },
    onInstrumentoSeleccionado(key) {
      this.$emit('update:modelValue', { ...this.form, instrumento_utilizado: key })
    }
  }
}
</script>

<style scoped>
.seccion-selector-instrumento-ergonomia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.instrumento-card {
  border: 2px solid #dee2e6;
  border-radius: 8px;
  transition: all 0.2s;
}

.instrumento-card:hover {
  border-color: #adb5bd;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.instrumento-selected {
  border-color: #2E8B57 !important;
  background-color: #f0fdf4 !important;
}

.instrumento-default {
  border-color: #f97316;
  background-color: #fff7ed;
}

.instrumento-normal {
  border-color: #dee2e6;
  background-color: white;
}

.font-weight-bold {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}
</style>