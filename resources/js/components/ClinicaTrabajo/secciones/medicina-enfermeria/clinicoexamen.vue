<template>
  <div class="clinico-examen-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="ti ti-stethoscope" aria-hidden="true"></i>
      <h2>Examen clínico y hallazgos</h2>
    </div>

    <!-- CARD: ANTECEDENTES CLÍNICOS -->
    <div class="card-section">
      <div class="card-header">
        <h3>Antecedentes clínicos relevantes</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label>Comorbilidades relevantes</label>
          <div class="checkbox-grid">
            <div class="checkbox-item">
              <input
                id="comorb-hta"
                type="checkbox"
                :checked="form.comorbilidades_hipertension"
                @change="form.comorbilidades_hipertension = $event.target.checked"
                class="checkbox-input"
              />
              <label for="comorb-hta" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Hipertensión
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="comorb-dm"
                type="checkbox"
                :checked="form.comorbilidades_diabetes"
                @change="form.comorbilidades_diabetes = $event.target.checked"
                class="checkbox-input"
              />
              <label for="comorb-dm" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Diabetes
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="comorb-asma"
                type="checkbox"
                :checked="form.comorbilidades_asma"
                @change="form.comorbilidades_asma = $event.target.checked"
                class="checkbox-input"
              />
              <label for="comorb-asma" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Asma/EPOC
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="comorb-cardio"
                type="checkbox"
                :checked="form.comorbilidades_cardiopatia"
                @change="form.comorbilidades_cardiopatia = $event.target.checked"
                class="checkbox-input"
              />
              <label for="comorb-cardio" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Cardiopatía
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="comorb-neuro"
                type="checkbox"
                :checked="form.comorbilidades_neurologica"
                @change="form.comorbilidades_neurologica = $event.target.checked"
                class="checkbox-input"
              />
              <label for="comorb-neuro" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Neurológica
              </label>
            </div>

            <div class="checkbox-item">
              <input
                id="comorb-psiq"
                type="checkbox"
                :checked="form.comorbilidades_psiquiatrica"
                @change="form.comorbilidades_psiquiatrica = $event.target.checked"
                class="checkbox-input"
              />
              <label for="comorb-psiq" class="checkbox-label">
                <span class="checkbox-mark"></span>
                Psiquiátrica
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="medicamentos">Medicamentos actuales</label>
          <textarea
            id="medicamentos"
            v-model="form.medicamentos_actuales"
            placeholder="Listar medicamentos con dosis. Ej: Losartán 50mg daily, Omeprazol 20mg morning..."
            class="textarea-field"
            rows="2"
          ></textarea>
        </div>

        <div class="form-group">
          <label for="alergias">Alergias (medicamentos, alimentos, ambiente)</label>
          <textarea
            id="alergias"
            v-model="form.alergias"
            placeholder="Describir alergias conocidas y reacciones"
            class="textarea-field"
            rows="2"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- CARD: SIGNOS VITALES (DESTACADO) -->
    <div class="card-section vital-card">
      <div class="card-header vital-header">
        <i class="ti ti-heart" aria-hidden="true"></i>
        <h3>Signos vitales</h3>
      </div>

      <div class="card-body">
        <div class="vital-grid">
          <div class="form-group">
            <label for="pa-sistolica">Presión arterial sistólica</label>
            <div class="input-with-unit">
              <input
                id="pa-sistolica"
                v-model.number="form.signos_vitales_pa_sistolica"
                type="number"
                min="60"
                max="250"
                placeholder="120"
                class="input-field"
              />
              <span class="unit">mmHg</span>
            </div>
          </div>

          <div class="form-group">
            <label for="pa-diastolica">Presión arterial diastólica</label>
            <div class="input-with-unit">
              <input
                id="pa-diastolica"
                v-model.number="form.signos_vitales_pa_diastolica"
                type="number"
                min="40"
                max="150"
                placeholder="80"
                class="input-field"
              />
              <span class="unit">mmHg</span>
            </div>
          </div>

          <div class="form-group">
            <label for="fc">Frecuencia cardíaca</label>
            <div class="input-with-unit">
              <input
                id="fc"
                v-model.number="form.signos_vitales_fc"
                type="number"
                min="40"
                max="200"
                placeholder="72"
                class="input-field"
              />
              <span class="unit">lpm</span>
            </div>
          </div>

          <div class="form-group">
            <label for="fr">Frecuencia respiratoria</label>
            <div class="input-with-unit">
              <input
                id="fr"
                v-model.number="form.signos_vitales_fr"
                type="number"
                min="8"
                max="50"
                placeholder="16"
                class="input-field"
              />
              <span class="unit">rpm</span>
            </div>
          </div>

          <div class="form-group">
            <label for="temp">Temperatura</label>
            <div class="input-with-unit">
              <input
                id="temp"
                v-model.number="form.signos_vitales_temperatura"
                type="number"
                min="35"
                max="42"
                step="0.1"
                placeholder="36.5"
                class="input-field"
              />
              <span class="unit">°C</span>
            </div>
          </div>

          <div class="form-group">
            <label for="spo2">Saturación O₂</label>
            <div class="input-with-unit">
              <input
                id="spo2"
                v-model.number="form.signos_vitales_spo2"
                type="number"
                min="70"
                max="100"
                placeholder="98"
                class="input-field"
              />
              <span class="unit">%</span>
            </div>
          </div>

          <div class="form-group">
            <label for="peso">Peso</label>
            <div class="input-with-unit">
              <input
                id="peso"
                v-model.number="form.signos_vitales_peso"
                type="number"
                min="20"
                max="200"
                step="0.1"
                placeholder="75"
                class="input-field"
              />
              <span class="unit">kg</span>
            </div>
          </div>

          <div class="form-group">
            <label for="estatura">Estatura</label>
            <div class="input-with-unit">
              <input
                id="estatura"
                v-model.number="form.signos_vitales_estatura"
                type="number"
                min="1"
                max="2.5"
                step="0.01"
                placeholder="1.75"
                class="input-field"
              />
              <span class="unit">m</span>
            </div>
          </div>

          <div class="form-group">
            <label for="imc">IMC (calculado automáticamente)</label>
            <input
              id="imc"
              :value="calcularIMC().toFixed(1)"
              type="number"
              readonly
              class="input-field"
            />
            <span class="helper-text" :class="clasificacionIMC">{{ clasificacionIMC }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD: EXAMEN FÍSICO SEGMENTARIO -->
    <div class="card-section">
      <div class="card-header">
        <h3>Examen físico segmentario</h3>
      </div>

      <div class="card-body">
        <div class="examen-segmentario">
          <div v-for="region in regiones" :key="region.id" class="region-item">
            <label class="region-label">
              {{ region.nombre }}
              <input
                type="checkbox"
                :checked="form.examen_normal?.[region.id]"
                @change="toggleExamenNormal(region.id, $event.target.checked)"
                class="checkbox-input"
              />
              <span class="checkbox-mark"></span>
              Normal
            </label>
          </div>
        </div>

        <div class="form-group">
          <label for="hallazgos">Hallazgos en el examen (si hay anormalidades)</label>
          <textarea
            id="hallazgos"
            v-model="form.hallazgos_examen"
            placeholder="Describir detalladamente cualquier hallazgo anormal..."
            class="textarea-field"
            rows="4"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- CARD: ESTUDIOS PARACLÍNICOS -->
    <div class="card-section">
      <div class="card-header">
        <h3>Estudios paraclínicos</h3>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label for="paraclinicos">
            Resultados de laboratorio, imágenes u otros estudios
          </label>
          <textarea
            id="paraclinicos"
            v-model="form.paraclínicos"
            placeholder="Ej: Hemograma: Hb 14.5, Hto 45%, WBC 7.2K; Rx tórax: Normal; Audiometría: Normal..."
            class="textarea-field"
            rows="3"
          ></textarea>
          <span class="helper-text">Datos clínicos objetivos relevantes para aptitud</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClinicóExamen',
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
      regiones: [
        { id: 'cabeza', nombre: 'Cabeza y cuello' },
        { id: 'torax', nombre: 'Tórax' },
        { id: 'abdomen', nombre: 'Abdomen' },
        { id: 'mmss', nombre: 'Miembros superiores' },
        { id: 'mmii', nombre: 'Miembros inferiores' },
        { id: 'columna', nombre: 'Columna vertebral' }
      ]
    }
  },
  computed: {
    clasificacionIMC() {
      const imc = this.calcularIMC()
      if (imc < 18.5) return 'bajo-peso'
      if (imc < 25) return 'normal'
      if (imc < 30) return 'sobrepeso'
      return 'obeso'
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
    calcularIMC() {
      if (!this.form.signos_vitales_peso || !this.form.signos_vitales_estatura) {
        return 0
      }
      const peso = this.form.signos_vitales_peso
      const estatura = this.form.signos_vitales_estatura
      return peso / (estatura * estatura)
    },
    toggleExamenNormal(regionId, isChecked) {
      if (!this.form.examen_normal) {
        this.form.examen_normal = {}
      }
      this.form.examen_normal[regionId] = isChecked
      this.$emit('update:modelValue', this.form)
    }
  }
}
</script>

<style scoped>
.clinico-examen-container {
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

.card-section.vital-card {
  border-left-color: #2E8B57;
}

.card-header {
  background: linear-gradient(135deg, #0F6E9F 0%, #0A5A84 100%);
  padding: 12px 20px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-section.vital-card .card-header {
  background: linear-gradient(135deg, #2E8B57 0%, #1E6B47 100%);
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

.vital-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
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

.input-field:readonly {
  background: #F5F7FA;
  color: #6B7280;
}

.input-with-unit {
  display: flex;
  align-items: center;
  gap: 6px;
}

.input-with-unit .input-field {
  flex: 1;
}

.unit {
  font-size: 12px;
  color: #9CA3AF;
  font-weight: 500;
  white-space: nowrap;
}

.helper-text {
  font-size: 12px;
  color: #9CA3AF;
  margin: 0;
}

.helper-text.bajo-peso {
  color: #2563EB;
}

.helper-text.normal {
  color: #2E8B57;
}

.helper-text.sobrepeso {
  color: #D97706;
}

.helper-text.obeso {
  color: #DC2626;
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
}

.checkbox-input:checked + .checkbox-label .checkbox-mark::after {
  content: '✓';
  color: #FFFFFF;
  font-size: 12px;
  font-weight: bold;
}

.examen-segmentario {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 0.5px solid #E5E7EB;
}

.region-item {
  display: flex;
  align-items: center;
}

.region-label {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-size: 13px;
  color: #1F2937;
  margin: 0;
  user-select: none;
}

.region-label .checkbox-mark {
  width: 16px;
  height: 16px;
}
</style>