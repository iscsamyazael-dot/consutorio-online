<template>
  <div class="aptitud-dictamen-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="ti ti-certificate" aria-hidden="true"></i>
      <h2>Diagnóstico y aptitud laboral</h2>
    </div>

    <!-- CARD: DIAGNÓSTICO CLÍNICO -->
    <div class="card-section">
      <div class="card-header">
        <h3>Diagnóstico clínico</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="diagnostico">Diagnóstico principal</label>
          <input
            id="diagnostico"
            v-model="form.diagnostico"
            type="text"
            placeholder="Ej: Sin hallazgos relevantes, Hipertensión controlada, etc..."
            class="input-field"
            required
          />
        </div>

        <div class="form-group">
          <label for="cie11">Código CIE-11 (opcional)</label>
          <input
            id="cie11"
            v-model="form.cie11_codigo"
            type="text"
            placeholder="Ej: BA80.0"
            class="input-field"
          />
        </div>

        <div class="form-group">
          <label for="hallazgos-relevantes">
            Hallazgos relevantes para la aptitud
          </label>
          <textarea
            id="hallazgos-relevantes"
            v-model="form.hallazgos_relevantes_aptitud"
            placeholder="Describir los hallazgos clínicos que impactan la aptitud laboral..."
            class="textarea-field"
            rows="3"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- CARD: APTITUD LABORAL (PROMINENTE) -->
    <div class="card-section aptitud-card">
      <div class="card-header aptitud-header">
        <i class="ti ti-award" aria-hidden="true"></i>
        <h3>Aptitud laboral</h3>
        <span class="required-badge">REQUERIDO</span>
      </div>

      <div class="card-body aptitud-body">
        <p class="aptitud-instruction">
          Selecciona el estado de aptitud laboral del trabajador
        </p>

        <div class="aptitud-options">
          <label
            class="aptitud-radio"
            :class="{ active: form.aptitud === 'apto' }"
          >
            <input
              type="radio"
              name="aptitud"
              value="apto"
              :checked="form.aptitud === 'apto'"
              @change="form.aptitud = 'apto'"
              class="radio-input"
            />
            <div class="radio-content">
              <div class="radio-mark"></div>
              <div class="radio-text">
                <span class="radio-title">APTO</span>
                <span class="radio-desc">Para el desempeño del puesto actual</span>
              </div>
              <i class="ti ti-check-circle radio-icon"></i>
            </div>
          </label>

          <label
            class="aptitud-radio"
            :class="{ active: form.aptitud === 'apto_con_restricciones' }"
          >
            <input
              type="radio"
              name="aptitud"
              value="apto_con_restricciones"
              :checked="form.aptitud === 'apto_con_restricciones'"
              @change="form.aptitud = 'apto_con_restricciones'"
              class="radio-input"
            />
            <div class="radio-content">
              <div class="radio-mark"></div>
              <div class="radio-text">
                <span class="radio-title">APTO CON RESTRICCIONES</span>
                <span class="radio-desc">Requiere limitaciones específicas</span>
              </div>
              <i class="ti ti-alert-circle radio-icon"></i>
            </div>
          </label>

          <label
            class="aptitud-radio"
            :class="{ active: form.aptitud === 'no_apto' }"
          >
            <input
              type="radio"
              name="aptitud"
              value="no_apto"
              :checked="form.aptitud === 'no_apto'"
              @change="form.aptitud = 'no_apto'"
              class="radio-input"
            />
            <div class="radio-content">
              <div class="radio-mark"></div>
              <div class="radio-text">
                <span class="radio-title">NO APTO</span>
                <span class="radio-desc">No puede desempeñar el puesto actual</span>
              </div>
              <i class="ti ti-circle-x radio-icon"></i>
            </div>
          </label>
        </div>

        <div v-if="!form.aptitud" class="error-message">
          <i class="ti ti-alert-triangle"></i>
          <span>Debe seleccionar la aptitud antes de guardar</span>
        </div>
      </div>
    </div>

    <!-- CARD: RESTRICCIONES & SEGUIMIENTO (condicional) -->
    <div v-if="form.aptitud === 'apto_con_restricciones'" class="card-section">
      <div class="card-header warning-header">
        <h3>Restricciones y limitaciones</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="restricciones">
            Detallar restricciones y limitaciones
            <span class="required-inline">*</span>
          </label>
          <textarea
            id="restricciones"
            v-model="form.restricciones"
            placeholder="Especificar de forma clara y concisa las limitaciones, prohibiciones y recomendaciones..."
            class="textarea-field required-field"
            rows="3"
            required
          ></textarea>
          <span class="helper-text">Requerido cuando hay restricciones</span>
        </div>
      </div>
    </div>

    <!-- CARD: RECOMENDACIONES & SEGUIMIENTO -->
    <div class="card-section">
      <div class="card-header">
        <h3>Recomendaciones y seguimiento</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="recomendaciones">Recomendaciones médicas</label>
          <textarea
            id="recomendaciones"
            v-model="form.recomendaciones"
            placeholder="Recomendaciones para el trabajador y/o empresa..."
            class="textarea-field"
            rows="2"
          ></textarea>
        </div>

        <div class="form-group">
          <label for="seguimiento">Próximo seguimiento</label>
          <select v-model="form.proximo_seguimiento" id="seguimiento" class="input-field">
            <option value="">Seleccionar intervalo</option>
            <option value="3m">3 meses</option>
            <option value="6m">6 meses</option>
            <option value="12m">12 meses (Anual)</option>
            <option value="extraordinaria">Extraordinaria (urgente)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="derivaciones">Derivaciones a especialistas</label>
          <textarea
            id="derivaciones"
            v-model="form.derivaciones_especialista"
            placeholder="Especialistas a los que se deriva el trabajador..."
            class="textarea-field"
            rows="2"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- CARD: DICTAMEN FINAL -->
    <div class="card-section final-card">
      <div class="card-header final-header">
        <i class="ti ti-file-text" aria-hidden="true"></i>
        <h3>Dictamen final</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="dictamen">
            Conclusión médica
            <span class="required-inline">*</span>
          </label>
          <textarea
            id="dictamen"
            v-model="form.dictamen_final"
            placeholder="Conclusión formal para registro legal. Debe incluir: diagnóstico, aptitud, recomendaciones..."
            class="textarea-field required-field"
            rows="4"
            required
          ></textarea>
          <span class="helper-text">Este texto formará parte del registro médico oficial</span>
        </div>

        <div class="form-group">
          <label for="observaciones">Observaciones adicionales</label>
          <textarea
            id="observaciones"
            v-model="form.observaciones"
            placeholder="Cualquier observación adicional no cubierta en los campos anteriores..."
            class="textarea-field"
            rows="2"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- SUMMARY BOX -->
    <div v-if="form.aptitud" class="summary-box" :class="'status-' + form.aptitud">
      <i :class="getIconoAptitud()" aria-hidden="true"></i>
      <div>
        <p class="summary-title">Estado de aptitud registrado</p>
        <p class="summary-text">{{ obtenerTextoAptitud() }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AptitudDictamen',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue', 'error'],
  data() {
    return {
      form: this.modelValue
    }
  },
  watch: {
    modelValue(newVal) {
      this.form = newVal
    },
    form: {
      handler(newVal) {
        this.$emit('update:modelValue', newVal)
        // Validar aptitud
        if (!newVal.aptitud) {
          this.$emit('error', 'Aptitud es requerida')
        } else {
          this.$emit('error', null)
        }
      },
      deep: true
    }
  },
  methods: {
    obtenerTextoAptitud() {
      const textos = {
        apto: 'El trabajador está APTO para desempeñar el puesto',
        apto_con_restricciones: 'El trabajador está APTO CON RESTRICCIONES — revisar limitaciones',
        no_apto: 'El trabajador NO ESTÁ APTO para desempeñar el puesto'
      }
      return textos[this.form.aptitud] || 'Sin definir'
    },
    getIconoAptitud() {
      const iconos = {
        apto: 'ti ti-check-circle',
        apto_con_restricciones: 'ti ti-alert-circle',
        no_apto: 'ti ti-circle-x'
      }
      return iconos[this.form.aptitud] || 'ti ti-help-circle'
    }
  }
}
</script>

<style scoped>
.aptitud-dictamen-container {
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
  color: #0F6E9F;
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
  border-left: 3px solid #0F6E9F;
  overflow: hidden;
}

.card-section.aptitud-card {
  border-left: 4px solid #2E8B57;
}

.card-section.final-card {
  border-left-color: #5F6E7E;
}

.card-header {
  background: linear-gradient(135deg, #0F6E9F 0%, #0A5A84 100%);
  padding: 12px 20px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  align-items: center;
  gap: 8px;
  position: relative;
}

.card-section.aptitud-card .card-header.aptitud-header {
  background: linear-gradient(135deg, #2E8B57 0%, #1E6B47 100%);
}

.card-section.final-card .card-header.final-header {
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%);
}

.card-header i {
  font-size: 16px;
  color: #FFFFFF;
}

.card-header h3 {
  font-size: 14px;
  font-weight: 500;
  color: #FFFFFF;
  margin: 0;
}

.required-badge {
  margin-left: auto;
  background: rgba(255, 255, 255, 0.25);
  color: #FFFFFF;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 3px;
  text-transform: uppercase;
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

.required-inline {
  color: #DC2626;
  margin-left: 2px;
}

.input-field {
  padding: 8px 12px;
  border: 0.5px solid #D1D5DB;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  color: #1F2937;
  background: #FFFFFF;
  transition: all 200ms ease;
}

.input-field:focus {
  outline: none;
  border-color: #0F6E9F;
  box-shadow: 0 0 0 2px rgba(15, 110, 159, 0.1);
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
  border-color: #0F6E9F;
  box-shadow: 0 0 0 2px rgba(15, 110, 159, 0.1);
}

.textarea-field.required-field {
  border-color: #FCD34D;
  background: #FFFBEB;
}

.helper-text {
  font-size: 12px;
  color: #9CA3AF;
  margin: 0;
}

/* APTITUD OPTIONS */
.aptitud-body {
  padding: 24px 20px;
}

.aptitud-instruction {
  font-size: 13px;
  color: #6B7280;
  margin: 0 0 16px 0;
  text-align: center;
}

.aptitud-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 12px;
}

.aptitud-radio {
  position: relative;
  display: flex;
  cursor: pointer;
}

.radio-input {
  display: none;
}

.radio-content {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid #E5E7EB;
  border-radius: 8px;
  background: #F9FAFB;
  transition: all 200ms ease;
  position: relative;
}

.aptitud-radio.active .radio-content {
  border-color: #2E8B57;
  background: #E8F5E9;
}

.radio-mark {
  width: 20px;
  height: 20px;
  border: 2px solid #D1D5DB;
  border-radius: 50%;
  background: #FFFFFF;
  transition: all 200ms ease;
  flex-shrink: 0;
}

.radio-input:checked + .radio-content .radio-mark {
  border-color: #2E8B57;
  background: #2E8B57;
}

.radio-input:checked + .radio-content .radio-mark::after {
  content: '';
  display: block;
  width: 8px;
  height: 4px;
  border: 2px solid white;
  border-top: none;
  border-right: none;
  transform: rotate(-45deg) translateY(-1px);
  position: absolute;
  top: 50%;
  left: 50%;
  margin-left: -4px;
  margin-top: -3px;
}

.radio-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.radio-title {
  font-size: 14px;
  font-weight: 600;
  color: #1F2937;
}

.radio-desc {
  font-size: 12px;
  color: #6B7280;
}

.radio-icon {
  font-size: 20px;
  color: #D1D5DB;
  transition: all 200ms ease;
  flex-shrink: 0;
}

.aptitud-radio.active .radio-icon {
  color: #2E8B57;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: #FEE2E2;
  border: 0.5px solid #FCA5A5;
  border-radius: 6px;
  color: #DC2626;
  font-size: 13px;
  margin-top: 8px;
}

.error-message i {
  font-size: 14px;
  flex-shrink: 0;
}

.warning-header {
  background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
}

/* SUMMARY BOX */
.summary-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  border-radius: 8px;
  padding: 16px;
}

.summary-box i {
  font-size: 24px;
  margin-top: 2px;
  flex-shrink: 0;
}

.summary-box.status-apto {
  background: #E8F5E9;
  border: 1px solid #81C784;
}

.summary-box.status-apto i {
  color: #2E8B57;
}

.summary-box.status-apto_con_restricciones {
  background: #FFF3E0;
  border: 1px solid #FFB74D;
}

.summary-box.status-apto_con_restricciones i {
  color: #D97706;
}

.summary-box.status-no_apto {
  background: #FFEBEE;
  border: 1px solid #EF5350;
}

.summary-box.status-no_apto i {
  color: #DC2626;
}

.summary-title {
  font-size: 12px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: inherit;
}

.summary-text {
  font-size: 14px;
  margin: 0;
  font-weight: 500;
  color: inherit;
}
</style>