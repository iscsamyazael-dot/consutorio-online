<template>
  <div class="valoracion-inteligente">
    <!-- TABS NAVEGACIÓN -->
    <div class="tabs-header">
      <div class="tabs-nav">
        <button
          v-for="(tab, index) in tabsActuales"
          :key="index"
          @click="tabActiva = index"
          :class="['tab-btn', { 'tab-active': tabActiva === index }]"
        >
          <i :class="['icon', `icon-${tab.icon}`]"></i>
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- PACIENTE INFO HEADER -->
    <div class="paciente-header">
      <div class="paciente-info">
        <h3 class="nombre">{{ form.paciente?.nombre || 'Paciente' }}</h3>
        <div class="detalles">
          <span class="genero">{{ form.paciente?.genero }}</span>
          <span class="separador">|</span>
          <span class="edad">{{ calcularEdad(form.paciente?.fecha_nacimiento) }} años</span>
        </div>
      </div>
      <div class="acciones-header">
        <button @click="imprimir" class="btn-outline-sm" v-if="!esNueva">📄 Imprimir</button>
      </div>
    </div>

    <!-- CONTENIDO DEL FORMULARIO -->
    <div class="form-container">
      <div v-if="cargando" class="py-8 text-center text-gray-500">
        ⏳ Cargando formulario...
      </div>

      <div v-else class="tab-content-wrapper">
        <!-- MEDICINA -->
        <div v-if="submódulo === 'medicina'">
          <DatosPuestoMedicina v-show="tabActiva === 0" v-model="form" />
          <ExposiciónRiesgosMedicina v-show="tabActiva === 1" v-model="form" />
          <ClinicoExamenMedicina v-show="tabActiva === 2" v-model="form" />
          <AptitudDictamenMedicina v-show="tabActiva === 3" v-model="form" />
        </div>

        <!-- PSICOLOGÍA -->
        <div v-if="submódulo === 'psicologia'">
          <DatosBasicoPsicologia v-show="tabActiva === 0" v-model="form" />
          <GuiaIEventoPsicologia v-show="tabActiva === 1" v-model="form" />
          <GuiaIIIFactoresPsicologia v-show="tabActiva === 2" v-model="form" :factores="factoresDisponibles" />
          <DiagnosticoPsicologia v-show="tabActiva === 3" v-model="form" />
          <AptitudDictamenPsicologia v-show="tabActiva === 4" v-model="form" />
        </div>

        <!-- NUTRICIÓN -->
        <div v-if="submódulo === 'nutricion'">
          <DatosBasicoNutricion v-show="tabActiva === 0" v-model="form" />
          <FactoresLaboralesNutricion v-show="tabActiva === 1" v-model="form" />
          <HabitosConsumoNutricion v-show="tabActiva === 2" v-model="form" />
          <AntropometriaNutricion v-show="tabActiva === 3" v-model="form" />
          <BioquimicaNutricion v-show="tabActiva === 4" v-model="form" />
          <DiagnosticoNutricion v-show="tabActiva === 5" v-model="form" />
          <AptitudDictamenNutricion v-show="tabActiva === 6" v-model="form" />
        </div>

        <!-- AUDIOLOGÍA -->
        <div v-if="submódulo === 'audiologia'">
          <DatosBasicoAudiologia v-show="tabActiva === 0" v-model="form" />
          <AntecedentesLaboralesAudiologia v-show="tabActiva === 1" v-model="form" />
          <OtotoxicosAudiologia v-show="tabActiva === 2" v-model="form" />
          <SintomasAudiologia v-show="tabActiva === 3" v-model="form" />
          <OtoscopiaAudiologia v-show="tabActiva === 4" v-model="form" />
          <AudiometriaAudiologia v-show="tabActiva === 5" v-model="form" />
          <AptitudDictamenAudiologia v-show="tabActiva === 6" v-model="form" />
        </div>

        <!-- ERGONOMÍA -->
        <div v-if="submódulo === 'ergonomia'">
          <DatosBasicoErgonomia v-show="tabActiva === 0" v-model="form" />
          <SelectorInstrumentoErgonomia v-show="tabActiva === 1" v-model="form" />
          <InstrumentoEspecificoErgonomia v-show="tabActiva === 2" v-model="form" :instrumento="form.instrumento_utilizado" />
          <HallazgosErgonomia v-show="tabActiva === 3" v-model="form" :riesgos="riesgosDisponibles" />
          <ExamenMedicoErgonomia v-show="tabActiva === 4" v-model="form" />
          <AptitudDictamenErgonomia v-show="tabActiva === 5" v-model="form" />
        </div>
      </div>
    </div>

    <!-- TRANSCRIPCIÓN LIVE -->
    <div class="transcripcion-live">
      <div class="transcripcion-header">
        <h4>Notas & Transcripción</h4>
        <button @click="mostrarTranscripcion = !mostrarTranscripcion" class="btn-sm">
          {{ mostrarTranscripcion ? '▼ Contraer' : '▶ Expandir' }}
        </button>
      </div>

      <div v-if="mostrarTranscripcion" class="transcripcion-area">
        <textarea
          v-model="form.notas_consulta"
          placeholder="Escribe aquí tus notas, observaciones o transcripciones de la consulta..."
          class="textarea-full"
          rows="6"
        ></textarea>
        <div class="transcripcion-info">
          <small>📝 Última actualización: {{ ultimaActualizacion }}</small>
        </div>
      </div>
    </div>

    <!-- BOTONES DE ACCIÓN -->
    <div class="botones-accion">
      <button @click="cancelar" class="btn-secondary">
        ← Cancelar
      </button>
      <button @click="guardar" class="btn-primary" :disabled="guardando">
        {{ guardando ? '⏳ Guardando...' : '💾 Guardar Valoración' }}
      </button>
      <button @click="imprimir" class="btn-outline" v-if="!esNueva">
        📄 Imprimir PDF
      </button>
    </div>
  </div>
</template>

<script>
import { clinicaTrabajo } from '../../services/ApiService.js'

// Componentes por sección (importar según sea necesario)
import DatosBasicoPsicologia from './secciones/psicologia/DatosBasico.vue'
import GuiaIEventoPsicologia from './secciones/psicologia/GuiaIEvento.vue'
import GuiaIIIFactoresPsicologia from './secciones/psicologia/FactoresPsicosociales.vue'
import DiagnosticoPsicologia from './secciones/psicologia/Diagnostico.vue'
import AptitudDictamenPsicologia from './secciones/psicologia/AptitudDictamen.vue'

import DatosBasicoNutricion from './secciones/nutricion/DatosBasico.vue'
import FactoresLaboralesNutricion from './secciones/nutricion/FactoresLaborales.vue'
import HabitosConsumoNutricion from './secciones/nutricion/HabitosConsumoNutricion.vue'
import AntropometriaNutricion from './secciones/nutricion/Antropometria.vue'
import BioquimicaNutricion from './secciones/nutricion/Bioquimica.vue'
import DiagnosticoNutricion from './secciones/nutricion/Diagnostico.vue'
import AptitudDictamenNutricion from './secciones/nutricion/AptitudDictamen.vue'

import DatosBasicoAudiologia from './secciones/audiologia/DatosBasico.vue'
import AntecedentesLaboralesAudiologia from './secciones/audiologia/AntecedentesLaborales.vue'
import OtotoxicosAudiologia from './secciones/audiologia/Ototoxicos.vue'
import SintomasAudiologia from './secciones/audiologia/Sintomas.vue'
import OtoscopiaAudiologia from './secciones/audiologia/Otoscopia.vue'
import AudiometriaAudiologia from './secciones/audiologia/Audiometria.vue'
import AptitudDictamenAudiologia from './secciones/audiologia/AptitudDictamen.vue'

import DatosBasicoErgonomia from './secciones/ergonomia/DatosBasico.vue'
import SelectorInstrumentoErgonomia from './secciones/ergonomia/SelectorInstrumento.vue'
import InstrumentoEspecificoErgonomia from './secciones/ergonomia/InstrumentoEspecifico.vue'
import HallazgosErgonomia from './secciones/ergonomia/Hallazgos.vue'
import ExamenMedicoErgonomia from './secciones/ergonomia/ExamenMedico.vue'
import AptitudDictamenErgonomia from './secciones/ergonomia/AptitudDictamen.vue'

// MEDICINA (NUEVO)
import DatosPuestoMedicina from './secciones/medicina/DatosPuesto.vue'
import ExposicionRiesgosMedicina from './secciones/medicina/ExposicionRiesgos.vue'
import ClinicoExamenMedicina from './secciones/medicina/ClinicoExamen.vue'
import AptitudDictamenMedicina from './secciones/medicina/AptitudDictamen.vue'

export default {
  name: 'ValoracionInteligente',
  components: {
    DatosBasicoPsicologia, GuiaIEventoPsicologia, GuiaIIIFactoresPsicologia, DiagnosticoPsicologia, AptitudDictamenPsicologia,
    DatosBasicoNutricion, FactoresLaboralesNutricion, HabitosConsumoNutricion, AntropometriaNutricion, BioquimicaNutricion, DiagnosticoNutricion, AptitudDictamenNutricion,
    DatosBasicoAudiologia, AntecedentesLaboralesAudiologia, OtotoxicosAudiologia, SintomasAudiologia, OtoscopiaAudiologia, AudiometriaAudiologia, AptitudDictamenAudiologia,
    DatosBasicoErgonomia, SelectorInstrumentoErgonomia, InstrumentoEspecificoErgonomia, HallazgosErgonomia, ExamenMedicoErgonomia, AptitudDictamenErgonomia,
    DatosPuestoMedicina, ExposicionRiesgosMedicina, ClinicoExamenMedicina, AptitudDictamenMedicina,
  },
  props: {
    submódulo: {
      type: String,
      required: true,
      validator: v => ['medicina', 'psicologia', 'nutricion', 'audiologia', 'ergonomia'].includes(v)
    },
    id: {
      type: [String, Number],
      default: null
    }
  },
  data() {
    return {
      form: {},
      tabActiva: 0,
      guardando: false,
      cargando: false,
      mostrarTranscripcion: true,
      ultimaActualizacion: new Date().toLocaleString('es-MX'),
      factoresDisponibles: [],
      riesgosDisponibles: [],
      tabsDefinicion: {
        medicina: [
          { label: 'Datos & Puesto', icon: 'user' },
          { label: 'Exposicion & Riesgos', icon: 'alert' },
          { label: 'Clinico & Examen', icon: 'stethoscope' },
          { label: 'Aptitud & Dictamen', icon: 'check' },
        ],
        psicologia: [
          { label: 'Datos Básicos', icon: 'user' },
          { label: 'Guía I: Evento Traumático', icon: 'alert' },
          { label: 'Guía III: Factores Psicosociales', icon: 'chart' },
          { label: 'Diagnóstico', icon: 'stethoscope' },
          { label: 'Aptitud & Dictamen', icon: 'check' },
        ],
        nutricion: [
          { label: 'Datos & Empresa', icon: 'user' },
          { label: 'Factores Laborales', icon: 'briefcase' },
          { label: 'Habitos & Consumo', icon: 'apple' },
          { label: 'Antropometria', icon: 'ruler' },
          { label: 'Bioquimica', icon: 'flask' },
          { label: 'Diagnostico', icon: 'stethoscope' },
          { label: 'Aptitud & Dictamen', icon: 'check' },
        ],
        audiologia: [
          { label: 'Datos & Pueto', icon: 'user' },
          { label: 'Antecedentes Laborales', icon: 'briefcase' },
          { label: 'Ototoxicos & Pasatiempos', icon: 'activity' },
          { label: 'Sintomas', icon: 'alert' },
          { label: 'Otoscopia', icon: 'eye' },
          { label: 'Audiometria', icon: 'sound' },
          { label: 'Aptitud & Dictamen', icon: 'check' },
        ],
        ergonomia: [
          { label: 'Datos & Puesto', icon: 'user' },
          { label: 'Seleccionar Instrumento', icon: 'tool' },
          { label: 'RULA / REBA / NIOSH', icon: 'chart' },
          { label: 'Hallazgos', icon: 'list' },
          { label: 'Examen Medico', icon: 'stethoscope' },
          { label: 'Aptitud & Dictamen', icon: 'check' },
        ],
      }
    }
  },
  computed: {
    esNueva() {
      return !this.id
    },
    tabsActuales() {
      return this.tabsDefinicion[this.submódulo] || []
    }
  },
  methods: {
    async cargar() {
      if (!this.id) {
        this.form = this.crearFormularioVacío()
        return
      }

      this.cargando = true
      try {
        const response = await clinicaTrabajo[this.submódulo].obtener(this.id)
        this.form = response.data || response
      } catch (error) {
        console.error('Error cargando valoración:', error)
        this.$toast?.error('Error cargando valoración')
      } finally {
        this.cargando = false
      }
    },

    crearFormularioVacío() {
      const base = {
        folio: '',
        paciente_id: null,
        empresa_cliente_id: null,
        puesto_trabajo_id: null,
        fecha_valoracion: new Date().toISOString().split('T')[0],
        aptitud: '', // SIN DEFAULT
        notas_consulta: '',
      }

      if (this.submódulo === 'medicina') {
        return {
          ...base,
          tipo: 'Ingreso',
          hora_valoracion: new Date().toTimeString().slice(0, 5),
        }
      }

      return {
        ...base,
        tipo_evaluacion: 'ingreso',
        hora_valoracion: new Date().toTimeString().slice(0, 5),
      }
    },

    async guardar() {
      // Validar aptitud
      if (!this.form.aptitud && !this.form.dictamen) {
        this.$toast?.error('Debe indicar la APTITUD/DICTAMEN (no tiene default)')
        this.tabActiva = this.tabsActuales.length - 1
        return
      }

      this.guardando = true
      const isNew = !this.id

      try {
        let response

        if (isNew) {
          response = await clinicaTrabajo[this.submódulo].crear(this.form)
        } else {
          response = await clinicaTrabajo[this.submódulo].actualizar(this.id, this.form)
        }

        this.$toast?.success(isNew ? 'Valoración creada' : 'Actualizada')
        this.ultimaActualizacion = new Date().toLocaleString('es-MX')

        // Si es nueva, actualizar el ID
        if (isNew) {
          this.id = response.data?.id || response.id
        }
      } catch (error) {
        console.error('Error guardando:', error)
        this.$toast?.error(error.response?.data?.message || 'Error guardando')
      } finally {
        this.guardando = false
      }
    },

    cancelar() {
      this.$router.back()
    },

    imprimir() {
      if (!this.id) {
        this.$toast?.error('Debe guardar la valoración antes de imprimir')
        return
      }
      window.open(`/clinica/${this.submódulo}/${this.id}/imprimir`, '_blank')
    },

    calcularEdad(fechaNacimiento) {
      if (!fechaNacimiento) return 0
      const hoy = new Date()
      const nacimiento = new Date(fechaNacimiento)
      let edad = hoy.getFullYear() - nacimiento.getFullYear()
      const mes = hoy.getMonth() - nacimiento.getMonth()
      if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--
      }
      return edad
    },

    async cargarCatálogos() {
      try {
        if (this.submódulo === 'psicologia') {
          const response = await ApiService.catalogo.factoresPsicosociales()
          this.factoresDisponibles = response
        }
        if (this.submódulo === 'ergonomia') {
          const response = await ApiService.catalogo.riesgosErgonomicos()
          this.riesgosDisponibles = response
        }
      } catch (error) {
        console.error('Error cargando catálogos:', error)
      }
    }
  },
  mounted() {
    this.cargar()
    this.cargarCatálogos()
  }
}
</script>

<style scoped>
.valoracion-inteligente {
  background: white;
  padding: 0;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* TABS NAVEGACIÓN */
.tabs-header {
  border-bottom: 2px solid #e5e7eb;
  background: #f9fafb;
}

.tabs-nav {
  display: flex;
  gap: 0;
  overflow-x: auto;
  padding: 0 1rem;
}

.tab-btn {
  padding: 1rem;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 500;
  color: #6b7280;
  white-space: nowrap;
  border-bottom: 3px solid transparent;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tab-btn:hover {
  color: #3b82f6;
}

.tab-active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.icon {
  font-size: 1.1rem;
}

/* PACIENTE HEADER */
.paciente-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #f0f9ff;
  border-bottom: 1px solid #e0e7ff;
}

.paciente-info {
  flex: 1;
}

.nombre {
  font-size: 1.2rem;
  font-weight: bold;
  color: #1e40af;
  margin: 0;
}

.detalles {
  font-size: 0.9rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.separador {
  margin: 0 0.5rem;
}

.acciones-header {
  display: flex;
  gap: 0.5rem;
}

/* CONTENIDO */
.form-container {
  padding: 1.5rem;
  min-height: 300px;
}

.tab-content-wrapper {
  width: 100%;
}

.tab-content {
  animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* TRANSCRIPCIÓN LIVE */
.transcripcion-live {
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  padding: 1rem 1.5rem;
  margin-top: 1rem;
}

.transcripcion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.transcripcion-header h4 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
}

.btn-sm {
  padding: 0.25rem 0.75rem;
  border: none;
  background: #e5e7eb;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.8rem;
}

.btn-sm:hover {
  background: #d1d5db;
}

.transcripcion-area {
  margin-top: 0.75rem;
}

.textarea-full {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  resize: vertical;
}

.textarea-full:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.transcripcion-info {
  margin-top: 0.5rem;
  color: #9ca3af;
  font-size: 0.8rem;
}

/* BOTONES ACCIÓN */
.botones-accion {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: white;
}

.btn-primary, .btn-secondary, .btn-outline {
  padding: 0.6rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.btn-outline {
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}
</style>