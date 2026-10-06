<template>
  <div class="seccion-examen-medico-ergonomia">
    <div class="card mb-4">
      <div class="card-header" style="background: linear-gradient(135deg, #2E8B57 0%, #228B22 100%); color: white;">
        <h3 class="mb-0">
          <i class="fas fa-stethoscope mr-2"></i>
          Examen Médico Ergonómico
        </h3>
      </div>
      <div class="card-body">
        <p class="text-muted mb-4">
          Registro de hallazgos clínicos relevantes (dolor, lesiones, etc.) y la decisión sobre seguimiento.
        </p>

        <!-- Fila 1: Examen médico realizado, Seguimiento requerido, Próximo control -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">Examen Médico Realizado *</label>
            <div class="d-flex align-items-center">
              <input
                v-model="form.examen_medico_realizado"
                type="checkbox"
                style="width: 20px; height: 20px; margin-right: 8px;"
              />
              <span class="text-muted">Sí / No</span>
            </div>
          </div>
          <div v-if="form.examen_medico_realizado" class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">Requiere Seguimiento</label>
            <div class="d-flex align-items-center">
              <input
                v-model="form.seguimiento_requerido"
                type="checkbox"
                style="width: 20px; height: 20px; margin-right: 8px;"
              />
              <span class="text-muted">Sí / No</span>
            </div>
          </div>
          <div v-if="form.seguimiento_requerido" class="col-md-4 mb-3">
            <label class="font-weight-bold d-block mb-1">Fecha Próximo Control *</label>
            <input
              v-model="form.plazo_proximo_seguimiento"
              type="date"
              class="form-control"
              :min="fechaHoy"
              required
            />
          </div>
        </div>

        <!-- Fila 2: Hallazgos por región anatómica -->
        <div class="mb-4">
          <h4 class="font-weight-bold mb-3">
            <i class="fas fa-body-text mr-2"></i>
            Hallazgos por Región Anatómica
          </h4>
          <div class="row">
            <div v-for="region in regionesAnatomicas" :key="region.key" class="col-md-3 mb-2">
              <label class="d-flex align-items-center" style="cursor: pointer;">
                <input
                  type="checkbox"
                  :id="region.key"
                  :checked="getHallazgo(region.key)"
                  @change="setHallazgo(region.key, $event.target.checked)"
                  style="width: 16px; height: 16px; margin-right: 8px;"
                />
                <span class="text-sm">{{ region.label }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Fila 3: Detalles específicos -->
        <div class="mb-4">
          <h4 class="font-weight-bold mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            Detalles y Observaciones
          </h4>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold d-block mb-1">Diagnóstico Ergonómico / Clínico</label>
              <textarea
                v-model="form.diagnostico_ergonomico"
                rows="3"
                class="form-control"
                placeholder="Descripción del diagnóstico..."
              ></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold d-block mb-1">Observaciones Adicionales</label>
              <textarea
                v-model="form.observaciones_examen"
                rows="3"
                class="form-control"
                placeholder="Otras observaciones relevantes..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Fila 4: Plan de manejo -->
        <div class="mt-4 p-4" style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px;">
          <h4 class="font-weight-bold mb-3" style="color: #6b21a8;">
            <i class="fas fa-calendar mr-2"></i>
            Plan de Manejo y Seguimiento
          </h4>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Recomendaciones</label>
              <textarea
                v-model="form.recomendaciones_examen"
                rows="3"
                class="form-control"
                placeholder="Recomendaciones ergonómicas, posturales, etc."
              ></textarea>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Restricciones Laborales</label>
              <textarea
                v-model="form.restricciones_laborales"
                rows="3"
                class="form-control"
                placeholder="Limitaciones de carga, postura, movimientos..."
              ></textarea>
            </div>
            <div class="col-md-4 mb-3">
              <label class="font-weight-bold d-block mb-1">Derivación a Especialista</label>
              <select v-model="form.derivacion_especialista" class="form-control">
                <option value="">Ninguna</option>
                <option value="traumatologia">Traumatología</option>
                <option value="neurologia">Neurología</option>
                <option value="reumatologia">Reumatología</option>
                <option value="fisioterapia">Fisioterapia / Rehabilitación</option>
                <option value="psicologia">Psicología / Psiquiatría</option>
                <option value="otro">Otro</option>
              </select>
              <input
                v-if="form.derivacion_especialista === 'otro'"
                v-model="form.derivacion_especialista_otro"
                type="text"
                class="form-control mt-2"
                placeholder="Especifique especialista"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ExamenMedicoErgonomia',
  props: {
    modelValue: { type: Object, required: true }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      fechaHoy: new Date().toISOString().split('T')[0],
      regionesAnatomicas: [
        { key: 'hallazgo_cervical', label: 'Cervical' },
        { key: 'hallazgo_hombro_derecho', label: 'Hombro Derecho' },
        { key: 'hallazgo_hombro_izquierdo', label: 'Hombro Izquierdo' },
        { key: 'hallazgo_codo_derecho', label: 'Codo Derecho' },
        { key: 'hallazgo_codo_izquierdo', label: 'Codo Izquierdo' },
        { key: 'hallazgo_muneca_derecha', label: 'Muñeca Derecha' },
        { key: 'hallazgo_muneca_izquierda', label: 'Muñeca Izquierda' },
        { key: 'hallazgo_lumbar', label: 'Lumbar' },
        { key: 'hallazgo_dorsal', label: 'Dorsal' },
        { key: 'hallazgo_cadera_derecha', label: 'Cadera Derecha' },
        { key: 'hallazgo_cadera_izquierda', label: 'Cadera Izquierda' },
        { key: 'hallazgo_rodilla_derecha', label: 'Rodilla Derecha' },
        { key: 'hallazgo_rodilla_izquierda', label: 'Rodilla Izquierda' },
        { key: 'hallazgo_tobillo_derecho', label: 'Tobillo Derecho' },
        { key: 'hallazgo_tobillo_izquierdo', label: 'Tobillo Izquierdo' }
      ]
    }
  },
  computed: {
    form: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    }
  },
  methods: {
    getHallazgo(key) {
      if (!this.form.hallazgos) {
        return false
      }
      return this.form.hallazgos[key] || false
    },
    setHallazgo(key, value) {
      if (!this.form.hallazgos) {
        this.$emit('update:modelValue', {
          ...this.form,
          hallazgos: {}
        })
      }
      this.$emit('update:modelValue', {
        ...this.form,
        hallazgos: {
          ...this.form.hallazgos,
          [key]: value
        }
      })
    }
  }
}
</script>

<style scoped>
.seccion-examen-medico-ergonomia {
  background: #f8f9fa;
}

.card {
  border: 1px solid #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.form-control {
  border-radius: 4px;
  border: 1px solid #ced4da;
  font-size: 14px;
  padding: 10px 12px;
}

.font-weight-bold {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.text-muted {
  color: #6c757d !important;
}

.text-sm {
  font-size: 0.875rem;
  color: #495057;
}
</style>