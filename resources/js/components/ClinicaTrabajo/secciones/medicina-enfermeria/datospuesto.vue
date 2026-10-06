<template>
  <div class="datos-puesto-container">
    <!-- HEADER -->
    <div class="section-header">
      <i class="fas fa-briefcase" aria-hidden="true"></i>
      <h2>Datos de Identificación y Laboral (WORLDSTRIDE - HOJA 1)</h2>
    </div>

    <!-- 1. DATOS DE EVALUACIÓN -->
    <div class="card-section">
      <div class="card-header">
        <h3>1. Datos de la Evaluación</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Folio *</label>
            <input type="text" v-model="form.folio" class="form-control" readonly />
            <small class="form-text text-muted">Auto-generado: MED-YYYYMMDD-NNNN</small>
          </div>
          <div class="form-group col-md-3">
            <label>Tipo Evaluación *</label>
            <select v-model="form.tipo_evaluacion" class="form-control" required>
              <option value="">Seleccionar</option>
              <option value="inicial">Evaluación Inicial</option>
              <option value="periodica">Periódica</option>
              <option value="cambio_puesto">Cambio de Puesto</option>
              <option value="retorno_laboral">Retorno Laboral</option>
              <option value="egreso">Egreso</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Fecha Evaluación *</label>
            <input type="date" v-model="form.fecha_evaluacion" class="form-control" required />
          </div>
          <div class="form-group col-md-3">
            <label>Hora Evaluación</label>
            <input type="time" v-model="form.hora_evaluacion" class="form-control" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-12">
            <label>Médico Evaluador *</label>
            <input type="text" v-model="form.medico_evaluador" class="form-control" required />
          </div>
        </div>
      </div>
    </div>

    <!-- 2. DATOS PERSONALES -->
    <div class="card-section">
      <div class="card-header">
        <h3>2. Datos Personales</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Nombre(s) completo *</label>
            <input type="text" v-model="form.nombre" class="form-control" required />
          </div>
          <div class="form-group col-md-3">
            <label>Fecha de nacimiento</label>
            <input type="date" v-model="form.fecha_nacimiento" class="form-control" />
          </div>
          <div class="form-group col-md-3">
            <label>Edad *</label>
            <input type="number" v-model="form.edad" class="form-control" min="18" max="80" required />
          </div>
          <div class="form-group col-md-3">
            <label>Cédula/Pasaporte *</label>
            <input type="text" v-model="form.cedula" class="form-control" required />
          </div>
        </div>

        <div class="form-row">
         
          <div class="form-group col-md-3">
            <label>Género *</label>
            <select v-model="form.genero" class="form-control" required>
              <option value="">Seleccionar</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Tipo Sanguíneo</label>
            <select v-model="form.tipo_sanguineo" class="form-control">
              <option value="">Seleccionar</option>
              <option value="O+">O+</option><option value="O-">O-</option>
              <option value="A+">A+</option><option value="A-">A-</option>
              <option value="B+">B+</option><option value="B-">B-</option>
              <option value="AB+">AB+</option><option value="AB-">AB-</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Estado civil</label>
            <select v-model="form.estado_civil" class="form-control">
              <option value="">Seleccionar</option>
              <option value="soltero">Soltero(a)</option>
              <option value="casado">Casado(a)</option>
              <option value="divorciado">Divorciado(a)</option>
              <option value="viudo">Viudo(a)</option>
              <option value="union_libre">Unión libre</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Lugar de Nacimiento</label>
            <input type="text" v-model="form.lugar_nacimiento" class="form-control" placeholder="Ciudad, país..." />
          </div>
        </div>

        <div class="form-row">
          
          
          <div class="form-group col-md-3">
            <label>Escolaridad</label>
            <select v-model="form.escolaridad" class="form-control">
              <option value="">Seleccionar</option>
              <option value="ninguna">Ninguna</option>
              <option value="primaria">Primaria</option>
              <option value="secundaria">Secundaria</option>
              <option value="tecnico">Técnico</option>
              <option value="profesional">Profesional</option>
              <option value="posgrado">Posgrado</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Edad inicio vida laboral</label>
            <input type="number" v-model="form.edad_inicio_vida_laboral" class="form-control" min="0" max="80" placeholder="Edad" />
          </div>
        </div>
      </div>
    </div>

    <!-- 3. INFORMACIÓN DE CONTACTO -->
    <div class="card-section">
      <div class="card-header">
        <h3>3. Información de Contacto</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Teléfono</label>
            <input type="tel" v-model="form.telefono" class="form-control" />
          </div>
          <div class="form-group col-md-3">
            <label>Celular</label>
            <input type="tel" v-model="form.celular" class="form-control" />
          </div>
          <div class="form-group col-md-3">
            <label>Correo Electrónico</label>
            <input type="email" v-model="form.email" class="form-control" />
          </div>
          <div class="form-group col-md-3">
            <label>Dirección</label>
            <input type="text" v-model="form.direccion" class="form-control" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Contacto Emergencia (Nombre)</label>
            <input type="text" v-model="form.emergencia_nombre" class="form-control" />
          </div>
          <div class="form-group col-md-4">
            <label>Teléfono Emergencia</label>
            <input type="tel" v-model="form.emergencia_telefono" class="form-control" />
          </div>
          <div class="form-group col-md-4">
            <label>Relación</label>
            <input type="text" v-model="form.emergencia_relacion" class="form-control" placeholder="Padre, cónyuge..." />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Última Consulta Médica (Fecha)</label>
            <input type="date" v-model="form.ultima_consulta_fecha" class="form-control" />
          </div>
          <div class="form-group col-md-6">
            <label>Motivo de la Consulta</label>
            <input type="text" v-model="form.ultima_consulta_motivo" class="form-control" placeholder="Razón de la consulta..." />
          </div>
        </div>
      </div>
    </div>

    <!-- 4. INFORMACIÓN LABORAL ACTUAL -->
    <div class="card-section">
      <div class="card-header">
        <h3>4. Información Laboral Actual</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Número Empleado *</label>
            <input type="text" v-model="form.numero_empleado" class="form-control" required />
          </div>
          <div class="form-group col-md-3">
            <label>Empresa *</label>
            <input type="text" v-model="form.empresa" class="form-control" required />
          </div>
          <div class="form-group col-md-3">
            <label>Departamento *</label>
            <input type="text" v-model="form.departamento" class="form-control" required />
          </div>
          <div class="form-group col-md-3">
            <label>Puesto/Cargo *</label>
            <input type="text" v-model="form.puesto" class="form-control" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Antigüedad en el Puesto</label>
            <input type="text" v-model="form.antiguedad" class="form-control" placeholder="Ej: 2 años 3 meses" />
          </div>
          <div class="form-group col-md-3">
            <label>Antigüedad Laboral Total</label>
            <input type="text" v-model="form.antiguedad_laboral" class="form-control" placeholder="Ej: 5 años" />
          </div>
          <div class="form-group col-md-3">
            <label>Antigüedad en la Empresa</label>
            <input type="text" v-model="form.antiguedad_empresa" class="form-control" placeholder="Ej: 5 años" />
          </div>
          <div class="form-group col-md-3">
            <label>Tipo Contrato *</label>
            <select v-model="form.tipo_contrato" class="form-control" required>
              <option value="">Seleccionar</option>
              <option value="indefinido">Indefinido</option>
              <option value="temporal">Temporal/Fijo</option>
              <option value="proyecto">Proyecto</option>
              <option value="practicante">Practicante</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Jornada Laboral *</label>
            <select v-model="form.jornada" class="form-control" required>
              <option value="">Seleccionar</option>
              <option value="diurna">Diurna</option>
              <option value="nocturna">Nocturna</option>
              <option value="mixta">Mixta</option>
              <option value="rotatoria">Rotatoria</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Modalidad de Trabajo *</label>
            <select v-model="form.modalidad_trabajo" class="form-control" required>
              <option value="">Seleccionar</option>
              <option value="presencial">Presencial</option>
              <option value="remoto">Remoto</option>
              <option value="hibrido">Híbrido</option>
              <option value="turnos">Por turnos</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Categoría Laboral *</label>
            <select v-model="form.categoria_laboral" class="form-control" required>
              <option value="">Seleccionar</option>
              <option value="operativo">Operativo</option>
              <option value="tecnico">Técnico</option>
              <option value="profesional">Profesional</option>
              <option value="administrativo">Administrativo</option>
              <option value="directivo">Directivo</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label>Antigüedad en el Puesto (años)</label>
            <input type="number" v-model.number="form.antiguedad" class="form-control" min="0" placeholder="Años" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Descripción General del Puesto/Funciones *</label>
            <textarea v-model="form.descripcion_puesto" class="form-control" rows="3" required></textarea>
          </div>
          <div class="form-group col-md-6">
            <label>Riesgos Identificados en el Puesto</label>
            <textarea v-model="form.riesgos_identificados" class="form-control" rows="3"></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. SIGNOS VITALES -->
    <div class="card-section">
      <div class="card-header">
        <h3>5. Signos Vitales (en el momento de la evaluación)</h3>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group col-md-2">
            <label>FC (lat/min)</label>
            <input type="number" v-model="form.signos_vitales.frecuencia_cardiaca" class="form-control" min="40" max="200" />
          </div>
          <div class="form-group col-md-2">
            <label>FR (resp/min)</label>
            <input type="number" v-model="form.signos_vitales.frecuencia_respiratoria" class="form-control" min="8" max="40" />
          </div>
          <div class="form-group col-md-2">
            <label>TA (mmHg)</label>
            <input type="text" v-model="form.signos_vitales.presion_arterial" class="form-control" placeholder="120/80" />
          </div>
          <div class="form-group col-md-2">
            <label>Temp (°C)</label>
            <input type="number" v-model="form.signos_vitales.temperatura" class="form-control" min="35" max="41" step="0.1" />
          </div>
          <div class="form-group col-md-2">
            <label>SatO2 (%)</label>
            <input type="number" v-model="form.signos_vitales.saturacion_oxigeno" class="form-control" min="70" max="100" />
          </div>
          <div class="form-group col-md-2">
            <label>Glucosa (mg/dL)</label>
            <input type="number" v-model="form.signos_vitales.glucosa" class="form-control" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DatosPuestoCompleto',
  props: {
    modelValue: {
      type: Object,
      required: true
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      form: this.modelValue || {
        folio: this.generarFolio(),
        tipo_evaluacion: '',
        fecha_evaluacion: new Date().toISOString().split('T')[0],
        hora_evaluacion: '',
        medico_evaluador: '',
        apellido_paterno: '',
        apellido_materno: '',
        nombre: '',
        edad: '',
        fecha_nacimiento: '',
        estado_civil: '',
        escolaridad: '',
        edad_inicio_vida_laboral: '',
        cedula: '',
        genero: '',
        lugar_nacimiento: '',
        tipo_sanguineo: '',
        telefono: '',
        celular: '',
        email: '',
        direccion: '',
        emergencia_nombre: '',
        emergencia_telefono: '',
        emergencia_relacion: '',
        ultima_consulta_fecha: '',
        ultima_consulta_motivo: '',
        numero_empleado: '',
        empresa: '',
        departamento: '',
        puesto: '',
        antiguedad: '',
        antiguedad_laboral: '',
        antiguedad_empresa: '',
        tipo_contrato: '',
        jornada: '',
        modalidad_trabajo: '',
        categoria_laboral: '',
        descripcion_puesto: '',
        riesgos_identificados: '',
        signos_vitales: {
          frecuencia_cardiaca: '',
          frecuencia_respiratoria: '',
          presion_arterial: '',
          temperatura: '',
          saturacion_oxigeno: '',
          glucosa: ''
        }
      }
    }
  },
  methods: {
    generarFolio() {
      const fecha = new Date()
      const ano = fecha.getFullYear()
      const mes = String(fecha.getMonth() + 1).padStart(2, '0')
      const dia = String(fecha.getDate()).padStart(2, '0')
      const numero = String(Math.floor(Math.random() * 10000)).padStart(4, '0')
      return `MED-${ano}${mes}${dia}-${numero}`
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
  }
}
</script>

<style scoped>
.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1.5rem;
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
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #5F6E7E 0%, #4A5568 100%);
  padding: 12px 20px;
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

.form-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-row:last-child {
  margin-bottom: 0;
}

.form-group {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #1F2937;
  margin-bottom: 0.5rem;
}

.form-control {
  padding: 10px 12px;
  border: 0.5px solid #D1D5DB;
  border-radius: 4px;
  font-size: 14px;
  height: 38px;
  transition: border-color 0.2s;
}

.form-control:focus {
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
  height: auto;
  resize: vertical;
}

.form-text {
  font-size: 12px;
  color: #6B7280;
  margin-top: 0.25rem;
}

/* GRID UNIFORME */
.col-md-2 {
  flex: 0 0 calc(16.666% - 0.8rem) !important;
}

.col-md-3 {
  flex: 0 0 calc(25% - 0.8rem) !important;
}

.col-md-4 {
  flex: 0 0 calc(33.333% - 0.8rem) !important;
}

.col-md-6 {
  flex: 0 0 calc(50% - 0.5rem) !important;
}

.col-md-12 {
  flex: 0 0 100% !important;
}
</style>