<template>
  <div class="form-card">

    <div class="form-top">
      <div>
        <h2>Registrar Consulta</h2>
        <p>Completa la información médica del paciente.</p>
      </div>
      <div class="top-badge">
        <i class="fas fa-calendar-check"></i>
        Agenda activa
      </div>
    </div>

    <form :action="storeUrl" method="POST">

      <input type="hidden" name="_token" :value="csrfToken">
      <input type="hidden" name="paciente_id" :value="pacienteEncontrado ? pacienteEncontrado.id : ''">

      <div class="form-grid">

        <!-- Nombre del Paciente -->
        <div class="form-group full">
          <label>Nombre del Paciente</label>
          <div class="input-modern">
            <i class="fas fa-user"></i>
            <input
              type="text"
              v-model="nombrePaciente"
              @input="buscarPaciente"
              placeholder="Escribe el nombre del paciente..."
              autocomplete="off"
              required
            >
            <ul class="autocomplete-list" v-if="sugerenciasPacientes.length">
              <li
                v-for="p in sugerenciasPacientes"
                :key="p.id"
                @click="seleccionarPaciente(p)"
              >
                <i class="fas fa-user-circle"></i> {{ p.nombre }}
              </li>
            </ul>
          </div>
          <span class="campo-validado" v-if="pacienteEncontrado">
            <i class="fas fa-check-circle"></i> {{ pacienteEncontrado.nombre }}
          </span>
        </div>

        <!-- Especialidad -->
        <div class="form-group">
          <label>Especialidad</label>
          <div class="input-modern">
            <i class="fas fa-stethoscope"></i>
            <select name="especialidad_id" v-model="especialidadSeleccionada" @change="filtrarMedicos" required>
              <option value="" disabled selected>Selecciona la especialidad...</option>
              <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">
                {{ esp.nombre }}
              </option>
            </select>
          </div>
        </div>

        <!-- Médico -->
        <div class="form-group">
          <label>Médico Asignado</label>
          <div class="input-modern">
            <i class="fas fa-user-md"></i>
            <select name="medico_id" :disabled="!especialidadSeleccionada" required>
              <option value="" disabled selected>
                {{ especialidadSeleccionada ? 'Selecciona un médico...' : 'Selecciona especialidad primero...' }}
              </option>
              <option v-for="m in medicosFiltrados" :key="m.id" :value="m.id">
                {{ m.nombre }}
              </option>
            </select>
          </div>
        </div>

        <!-- Fecha -->
        <div class="form-group">
          <label>Fecha</label>
          <div class="input-modern">
            <i class="fas fa-calendar"></i>
            <input type="date" name="fecha" required>
          </div>
        </div>

        <!-- Hora -->
        <div class="form-group">
          <label>Hora</label>
          <div class="input-modern">
            <i class="fas fa-clock"></i>
            <input type="time" name="hora" required>
          </div>
        </div>

        <!-- Tipo de Cita -->
        <div class="form-group">
          <label>Tipo de Cita</label>
          <div class="input-modern">
            <i class="fas fa-notes-medical"></i>
            <select name="tipo">
              <option value="Primera vez">Primera vez</option>
              <option value="Seguimiento">Seguimiento</option>
            </select>
          </div>
        </div>

        <!-- Estado -->
        <div class="form-group">
          <label>Estado</label>
          <div class="input-modern">
            <i class="fas fa-check-circle"></i>
            <select name="estado">
              <option value="programada">Programada</option>
              <option value="Completada">Completada</option>
              <option value="Cancelada">Cancelada</option>
            </select>
          </div>
        </div>

        <!-- Observaciones -->
        <div class="form-group full">
          <label>Observaciones</label>
          <textarea
            name="observaciones"
            v-model="observaciones"
            placeholder="Describe los síntomas o motivo de la consulta..."
          ></textarea>
        </div>

      </div>

      <div class="actions">
        <a :href="indexUrl" class="btn-cancel">Cancelar</a>
        <button type="submit" class="btn-save">
          <i class="fas fa-save"></i>
          Guardar Consulta
        </button>
      </div>

    </form>
  </div>
</template>

<script>
export default {
  name: 'RegistrarConsulta',
  props: {
    storeUrl:       { type: String, required: true },
    indexUrl:       { type: String, required: true },
    csrfToken:      { type: String, required: true },
    pacientes:      { type: Array,  required: true },
    medicos:        { type: Array,  required: true },
    especialidades: { type: Array,  required: true },
  },

  data() {
    return {
      nombrePaciente:          '',
      observaciones:           '',
      pacienteEncontrado:      null,
      sugerenciasPacientes:    [],
      especialidadSeleccionada: '',
      medicosFiltrados:        [],
    }
  },

  methods: {
    buscarPaciente() {
      this.pacienteEncontrado = null
      const texto = this.nombrePaciente.trim().toLowerCase()
      if (texto.length < 2) {
        this.sugerenciasPacientes = []
        return
      }
      this.sugerenciasPacientes = this.pacientes.filter(p =>
        p.nombre && p.nombre.toLowerCase().includes(texto)
      ).slice(0, 5)
    },

    seleccionarPaciente(paciente) {
      this.pacienteEncontrado   = paciente
      this.nombrePaciente       = paciente.nombre
      this.sugerenciasPacientes = []
    },

    filtrarMedicos() {
  this.medicosFiltrados = this.medicos.filter(m =>
    Number(m.especialidad_id) === Number(this.especialidadSeleccionada)
  )
},
  }
}
</script>

<style scoped>
.form-card {
  background: rgba(255, 255, 255, 0.78);
  backdrop-filter: blur(18px);
  border-radius: 28px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.form-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  gap: 15px;
  flex-wrap: wrap;
}

.form-top h2 {
  margin: 0;
  font-size: 2rem;
  font-weight: 800;
  color: #0f172a;
}

.form-top p {
  margin: 6px 0 0;
  color: #64748b;
}

.top-badge {
  background: linear-gradient(135deg, #dbeafe, #eef2ff);
  color: #3730a3;
  padding: 12px 16px;
  border-radius: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 22px;
}

.full { grid-column: span 2; }

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 10px;
  font-weight: 700;
  color: #334155;
}

.input-modern { position: relative; }

.input-modern i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  z-index: 1;
}

.input-modern input,
.input-modern select {
  width: 100%;
  height: 56px;
  border-radius: 18px;
  border: 1px solid #dbe4ee;
  background: white;
  padding: 0 18px 0 48px;
  outline: none;
  transition: 0.25s ease;
  box-sizing: border-box;
  font-size: 0.95rem;
}

.input-modern input:focus,
.input-modern select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.10);
}

.input-modern select:disabled {
  background: #f8fafc;
  color: #94a3b8;
  cursor: not-allowed;
}

/* Autocomplete */
.autocomplete-list {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dbe4ee;
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.10);
  list-style: none;
  margin: 0;
  padding: 8px;
  z-index: 100;
}

.autocomplete-list li {
  padding: 10px 14px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.15s;
}

.autocomplete-list li:hover {
  background: #eff6ff;
  color: #2563eb;
}

.campo-validado {
  margin-top: 6px;
  font-size: 0.82rem;
  color: #16a34a;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 5px;
}

textarea {
  width: 100%;
  min-height: 130px;
  border-radius: 20px;
  border: 1px solid #dbe4ee;
  background: white;
  padding: 18px;
  resize: vertical;
  outline: none;
  transition: 0.25s ease;
  box-sizing: border-box;
  font-size: 0.95rem;
  font-family: inherit;
}

textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.10);
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 14px;
  margin-top: 30px;
}

.btn-cancel {
  background: white;
  border: 1px solid #dbe4ee;
  padding: 13px 22px;
  border-radius: 16px;
  font-weight: 700;
  color: #334155;
  text-decoration: none;
  transition: 0.25s ease;
}

.btn-cancel:hover {
  background: #f8fafc;
  color: #0f172a;
  text-decoration: none;
}

.btn-save {
  border: none;
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  color: white;
  padding: 13px 24px;
  border-radius: 16px;
  font-weight: 700;
  transition: 0.25s ease;
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
  cursor: pointer;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(37, 99, 235, 0.35);
}

@media (max-width: 768px) {
  .form-grid { grid-template-columns: 1fr; }
  .full { grid-column: span 1; }
  .form-card { padding: 22px; }
  .actions { flex-direction: column; }
  .btn-save, .btn-cancel { width: 100%; text-align: center; }
}
</style>