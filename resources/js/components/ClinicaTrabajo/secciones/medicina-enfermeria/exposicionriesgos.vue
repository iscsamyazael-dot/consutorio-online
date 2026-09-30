<template>
  <div class="exposicion-riesgos-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="ti ti-alert-triangle" aria-hidden="true"></i>
      <h2>Exposición laboral y riesgos ocupacionales</h2>
    </div>

    <!-- CARD: DESCRIPCIÓN DEL PUESTO -->
    <div class="card-section">
      <div class="card-header">
        <h3>Descripción de actividades y tareas</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="descripcion">Actividades principales del puesto</label>
          <textarea
            id="descripcion"
            v-model="form.descripcion_puesto"
            placeholder="Descripción detallada de las actividades principales, tareas frecuentes y responsabilidades..."
            class="textarea-field"
            rows="4"
          ></textarea>
          <span class="helper-text">Describir de forma clara y detallada</span>
        </div>
      </div>
    </div>

    <!-- CARD: RIESGOS LABORALES -->
    <div class="card-section">
      <div class="card-header">
        <h3>Riesgos laborales identificados</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label>Selecciona los riesgos aplicables</label>
          <div class="checkbox-grid">
            <div v-for="riesgo in riesgosDisponibles" :key="riesgo.id" class="checkbox-item">
              <input
                :id="`riesgo-${riesgo.id}`"
                type="checkbox"
                :value="riesgo.id"
                :checked="form.riesgos_seleccionados?.includes(riesgo.id)"
                @change="toggleRiesgo(riesgo.id)"
                class="checkbox-input"
              />
              <label :for="`riesgo-${riesgo.id}`" class="checkbox-label">
                <span class="checkbox-mark"></span>
                {{ riesgo.nombre }}
              </label>
            </div>
          </div>
        </div>

        <!-- RESUMEN DE RIESGOS SELECCIONADOS -->
        <div v-if="form.riesgos_seleccionados?.length" class="badge-group">
          <span v-for="riesgoId in form.riesgos_seleccionados" :key="riesgoId" class="badge">
            {{ obtenerNombreRiesgo(riesgoId) }}
            <i class="ti ti-x"></i>
          </span>
        </div>
      </div>
    </div>

    <!-- CARD: EQUIPOS DE PROTECCIÓN PERSONAL -->
    <div class="card-section">
      <div class="card-header">
        <h3>Equipos de protección personal (EPI)</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label>EPI disponible en el puesto</label>
          <div class="checkbox-grid">
            <div class="checkbox-item">
              <input
                id="epi-casco"
                type="checkbox"
                :checked="form.epi_casco"
                @change="form.epi_casco = $event.target.checked"
                class="checkbox-input"
              />
              <label for="epi-casco" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Casco
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="epi-guantes"
                type="checkbox"
                :checked="form.epi_guantes"
                @change="form.epi_guantes = $event.target.checked"
                class="checkbox-input"
              />
              <label for="epi-guantes" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Guantes
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="epi-mascarilla"
                type="checkbox"
                :checked="form.epi_mascarilla"
                @change="form.epi_mascarilla = $event.target.checked"
                class="checkbox-input"
              />
              <label for="epi-mascarilla" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Mascarilla/Respirador
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="epi-arnés"
                type="checkbox"
                :checked="form.epi_arnes"
                @change="form.epi_arnes = $event.target.checked"
                class="checkbox-input"
              />
              <label for="epi-arnés" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Arnés/Cinturón seguridad
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="epi-lentes"
                type="checkbox"
                :checked="form.epi_lentes"
                @change="form.epi_lentes = $event.target.checked"
                class="checkbox-input"
              />
              <label for="epi-lentes" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Lentes de protección
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="epi-botas"
                type="checkbox"
                :checked="form.epi_botas"
                @change="form.epi_botas = $event.target.checked"
                class="checkbox-input"
              />
              <label for="epi-botas" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Botas de seguridad
              </label>
            </div>
          </div>

          <div class="toggle-group">
            <input
              id="usa-epi"
              type="checkbox"
              :checked="form.usa_epi_regularmente"
              @change="form.usa_epi_regularmente = $event.target.checked"
              class="checkbox-input"
            />
            <label for="usa-epi" class="toggle-label">
              El trabajador usa EPI regularmente
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD: EXPOSICIÓN PREVIA -->
    <div class="card-section">
      <div class="card-header">
        <h3>Historial de exposición previa</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="exposicion-previa">
            Antecedentes ocupacionales a otros riesgos
          </label>
          <textarea
            id="exposicion-previa"
            v-model="form.historial_exposicion"
            placeholder="Ej: Trabajó en construcción (asbesto), industria química (disolventes), etc..."
            class="textarea-field"
            rows="3"
          ></textarea>
          <span class="helper-text">Documentar puestos anteriores y riesgos asociados</span>
        </div>

        <div class="form-group">
          <label for="enfermedades-previas">Enfermedades ocupacionales previas</label>
          <textarea
            id="enfermedades-previas"
            v-model="form.enfermedades_ocupacionales_previas"
            placeholder="Ej: Dermatitis de contacto, hipoacusia, tendinitis, etc..."
            class="textarea-field"
            rows="2"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- INFO BOX -->
    <div class="info-box warning">
      <i class="ti ti-info-circle" aria-hidden="true"></i>
      <div>
        <p class="info-label">Importante:</p>
        <p class="info-text">
          La documentación completa de riesgos y exposición es esencial para la aptitud médica ocupacional.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../../../../services/ApiService'

export default {
  name: 'ExposiciónRiesgos',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      form: this.modelValue,
      riesgosDisponibles: []
    }
  },
  watch: {
    modelValue(newVal) {
      this.form = newVal
    },
    form: {
      handler(newVal) {
        this.$emit('update:modelValue', newVal)
      },
      deep: true
    }
  },
  methods: {
    toggleRiesgo(riesgoId) {
      if (!this.form.riesgos_seleccionados) {
        this.form.riesgos_seleccionados = []
      }
      const index = this.form.riesgos_seleccionados.indexOf(riesgoId)
      if (index > -1) {
        this.form.riesgos_seleccionados.splice(index, 1)
      } else {
        this.form.riesgos_seleccionados.push(riesgoId)
      }
      this.$emit('update:modelValue', this.form)
    },
    obtenerNombreRiesgo(riesgoId) {
      const riesgo = this.riesgosDisponibles.find(r => r.id === riesgoId)
      return riesgo?.nombre || 'Riesgo'
    },
    async cargarRiesgos() {
      try {
        const response = await ApiService.catalogo.riesgosErgonomicos()
        this.riesgosDisponibles = response.data || []
      } catch (error) {
        console.error('Error cargando riesgos:', error)
      }
    }
  },
  mounted() {
    this.cargarRiesgos()
  }
}
</script>

<style scoped>
.exposicion-riesgos-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 0.5rem;
}

.section-header i {
  font-size: 24px;
  color: #5F6E7E;
}

.section-header h2 {
  font-size: 18px;
  font-weight: 500;
  color: #1F2937;
  margin: 0;
}

.card-section {
  background: #FFFFFF;
  border: 0.5px solid #E5E7EB;
  border-radius: 6px;
  border-left: 3px solid #5F6E7E;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%);
  padding: 12px 20px;
  border-bottom: 1px solid #E5E7EB;
}

.card-header h3 {
  font-size: 14px;
  font-weight: 500;
  color: #FFFFFF;
  margin: 0;
}

.card-body {
  padding: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
  margin: 0;
}

.textarea-field {
  padding: 10px 12px;
  border: 0.5px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  color: #1F2937;
  background: #FFFFFF;
  resize: vertical;
  transition: all 200ms ease;
}

.textarea-field:focus {
  outline: none;
  border-color: #5F6E7E;
  box-shadow: 0 0 0 2px rgba(95, 110, 126, 0.1);
}

.helper-text {
  font-size: 12px;
  color: #9CA3AF;
  margin: 0;
}

.checkbox-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
  margin-top: 8px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-input {
  display: none;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-size: 14px;
  color: #1F2937;
  margin: 0;
  user-select: none;
}

.checkbox-mark {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  background: #FFFFFF;
  transition: all 200ms ease;
  flex-shrink: 0;
}

.checkbox-input:checked + .checkbox-label .checkbox-mark {
  background: #0F6E9F;
  border-color: #0F6E9F;
  box-shadow: inset 0 0 0 2px #FFFFFF;
}

.checkbox-input:checked + .checkbox-label .checkbox-mark::after {
  content: '✓';
  color: #FFFFFF;
  font-size: 12px;
  font-weight: bold;
}

.checkbox-input:focus + .checkbox-label .checkbox-mark {
  box-shadow: 0 0 0 2px rgba(15, 110, 159, 0.2);
}

.toggle-group {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 0.5px solid #E5E7EB;
}

.toggle-label {
  font-size: 13px;
  color: #1F2937;
  margin: 0;
  cursor: pointer;
}

.badge-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 0.5px solid #E5E7EB;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #E0EEF7;
  color: #0F6E9F;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: 500;
}

.badge i {
  font-size: 12px;
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 200ms;
}

.badge i:hover {
  opacity: 1;
}

.info-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  border-radius: 6px;
  padding: 12px 16px;
}

.info-box.warning {
  background: #FEF3C7;
  border: 0.5px solid #FCD34D;
}

.info-box i {
  font-size: 16px;
  color: #D97706;
  margin-top: 2px;
  flex-shrink: 0;
}

.info-label {
  font-size: 12px;
  color: #92400E;
  margin: 0;
  font-weight: 600;
}

.info-text {
  font-size: 13px;
  color: #78350F;
  margin: 4px 0 0 0;
}
</style>