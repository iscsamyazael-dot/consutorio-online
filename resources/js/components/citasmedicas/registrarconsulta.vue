<template>
  <div class="form-wrap">
    <form ref="formRef" :action="storeUrl" method="POST" class="form-grid" @submit.prevent="guardarCita">

      <input type="hidden" name="_token" :value="csrfToken">
      <input type="hidden" name="paciente_id" :value="pacienteEncontrado ? pacienteEncontrado.id : ''">
      <input type="hidden" name="especialidad_id" :value="especialidadSeleccionada">
      <input type="hidden" name="medico_id" :value="medicoSeleccionado">
      <input type="hidden" name="tipo" :value="tipoCita">
      <input type="hidden" name="estado" value="Agendado">

      <!-- ════════ COLUMNA PRINCIPAL ════════ -->
      <div class="form-main">

        <!-- HEADER -->
        <div class="hero-header">
          <div class="hero-icon">
            <i class="fas fa-calendar-plus"></i>
          </div>
          <div class="hero-text">
            <h2>Registrar consulta</h2>
            <p>Completa la información médica del paciente</p>
          </div>
          <div class="badge-activa">
            <i class="fas fa-circle"></i>
            Agenda activa
          </div>
        </div>

        <!-- TARJETA: PACIENTE -->
        <section class="panel">
          <div class="panel-title">
            <span class="step-dot">1</span>
            Datos del paciente
          </div>

          <div class="field">
            <label>Nombre del paciente</label>
            <div class="input-wrap">
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
                  <span class="auto-avatar">{{ iniciales(p.nombre) }}</span>
                  {{ p.nombre }}
                </li>
              </ul>
            </div>
            <span class="chip-valid" v-if="pacienteEncontrado">
              <i class="fas fa-check-circle"></i> Paciente encontrado: {{ pacienteEncontrado.nombre }}
            </span>
          </div>
        </section>

        <!-- TARJETA: ESPECIALIDAD -->
        <section class="panel">
          <div class="panel-title">
            <span class="step-dot">2</span>
            Especialidad
          </div>

          <div class="chip-grid">
            <button
              type="button"
              v-for="esp in especialidades"
              :key="esp.id"
              class="chip-option"
              :class="{ activo: especialidadSeleccionada === esp.id }"
              @click="elegirEspecialidad(esp.id)"
            >
              <i class="fas fa-stethoscope"></i>
              {{ esp.nombre }}
            </button>
          </div>
        </section>

        <!-- TARJETA: MÉDICO -->
        <section class="panel">
          <div class="panel-title">
            <span class="step-dot">3</span>
            Médico asignado
          </div>

          <p class="panel-hint" v-if="!especialidadSeleccionada">
            Selecciona una especialidad para ver los médicos disponibles.
          </p>

          <div class="doctor-grid" v-else>
            <button
              type="button"
              v-for="m in medicosFiltrados"
              :key="m.id"
              class="doctor-card"
              :class="{ activo: medicoSeleccionado === m.id }"
              @click="medicoSeleccionado = m.id"
            >
              <div class="doctor-avatar">{{ iniciales(m.nombre) }}</div>
              <div class="doctor-info">
                <span class="doctor-name">{{ m.nombre }}</span>
                <span class="doctor-spec">{{ nombreEspecialidad(especialidadSeleccionada) }}</span>
              </div>
              <i class="fas fa-check-circle doctor-check"></i>
            </button>

            <p v-if="medicosFiltrados.length === 0" class="panel-hint">
              No hay médicos disponibles para esta especialidad.
            </p>
          </div>
        </section>

        <!-- TARJETA: FECHA / HORA / TIPO -->
        <section class="panel">
          <div class="panel-title">
            <span class="step-dot">4</span>
            Detalles de la cita
          </div>

          <div class="grid2">
            <div class="field">
              <label>Fecha</label>
              <div class="input-wrap">
                <i class="fas fa-calendar"></i>
                <input type="date" name="fecha" v-model="fecha" required>
              </div>
            </div>

            <div class="field">
              <label>Hora</label>
              <div class="input-wrap">
                <i class="fas fa-clock"></i>
                <input type="time" name="hora" v-model="hora" required>
              </div>
            </div>
          </div>

          <div class="field" style="margin-top: 14px;">
            <label>Tipo de cita</label>
            <div class="toggle-group">
              <button
                type="button"
                class="toggle-option"
                :class="{ activo: tipoCita === 'Primera vez' }"
                @click="tipoCita = 'Primera vez'"
              >
                <i class="fas fa-star"></i> Primera vez
              </button>
              <button
                type="button"
                class="toggle-option"
                :class="{ activo: tipoCita === 'Seguimiento' }"
                @click="tipoCita = 'Seguimiento'"
              >
                <i class="fas fa-redo"></i> Seguimiento
              </button>
            </div>
          </div>
        </section>

        <!-- TARJETA: OBSERVACIONES -->
        <section class="panel">
          <div class="panel-title">
            <span class="step-dot">5</span>
            Observaciones
          </div>
          <textarea
            name="observaciones"
            v-model="observaciones"
            placeholder="Describe los síntomas o motivo de la consulta..."
          ></textarea>
        </section>

      </div>

      <!-- ════════ COLUMNA RESUMEN (sticky) ════════ -->
      <aside class="form-summary">
        <div class="summary-card">
          <p class="summary-title">Resumen de la cita</p>

          <div class="summary-row">
            <div class="summary-icon"><i class="fas fa-user"></i></div>
            <div>
              <span class="summary-label">Paciente</span>
              <span class="summary-value">{{ pacienteEncontrado?.nombre ?? '—' }}</span>
            </div>
          </div>

          <div class="summary-row">
            <div class="summary-icon"><i class="fas fa-stethoscope"></i></div>
            <div>
              <span class="summary-label">Especialidad</span>
              <span class="summary-value">{{ nombreEspecialidad(especialidadSeleccionada) || '—' }}</span>
            </div>
          </div>

          <div class="summary-row">
            <div class="summary-icon"><i class="fas fa-user-md"></i></div>
            <div>
              <span class="summary-label">Médico</span>
              <span class="summary-value">{{ nombreMedico(medicoSeleccionado) || '—' }}</span>
            </div>
          </div>

          <div class="summary-row">
            <div class="summary-icon"><i class="fas fa-calendar-day"></i></div>
            <div>
              <span class="summary-label">Fecha y hora</span>
              <span class="summary-value">{{ fechaHoraFormateada || '—' }}</span>
            </div>
          </div>

          <div class="summary-row">
            <div class="summary-icon"><i class="fas fa-notes-medical"></i></div>
            <div>
              <span class="summary-label">Tipo</span>
              <span class="summary-value">{{ tipoCita }}</span>
            </div>
          </div>

          <hr class="summary-divider">

          <button type="submit" class="btn-save" :disabled="guardando">
            <i v-if="guardando" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-save"></i>
            {{ guardando ? 'Guardando...' : 'Guardar consulta' }}
          </button>
          <a :href="indexUrl" class="btn-cancel">Cancelar</a>
        </div>
      </aside>

    </form>

    <!-- ── TOAST DE CONFIRMACIÓN ── -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast" class="toast-notificacion" :class="toast.tipo">
          <i :class="toast.icono"></i>
          <span>{{ toast.mensaje }}</span>
        </div>
      </Transition>
    </Teleport>
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
      nombrePaciente:           '',
      observaciones:            '',
      pacienteEncontrado:       null,
      sugerenciasPacientes:     [],
      especialidadSeleccionada: '',
      medicoSeleccionado:       '',
      medicosFiltrados:         [],
      fecha:                    '',
      hora:                     '',
      tipoCita:                 'Primera vez',
      guardando:                false,
      toast:                    null,
      toastTimer:               null,
    }
  },

  computed: {
    fechaHoraFormateada() {
      if (!this.fecha || !this.hora) return ''
      const [y, m, d] = this.fecha.split('-')
      const fechaObj = new Date(y, m - 1, d)
      const fechaTxt = fechaObj.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' })
      const [h, min] = this.hora.split(':')
      const hr = parseInt(h)
      const ampm = hr >= 12 ? 'PM' : 'AM'
      const h12 = hr % 12 || 12
      return `${fechaTxt}, ${h12}:${min} ${ampm}`
    },
  },

  mounted() {
    const raw = localStorage.getItem('citaPrecargar')
    if (!raw) return

    try {
      const datos = JSON.parse(raw)

      if (datos.paciente) {
        this.seleccionarPaciente(datos.paciente)
      }

      if (datos.especialidad_id) {
        this.elegirEspecialidad(datos.especialidad_id)

        if (datos.medico_id) {
          this.medicoSeleccionado = datos.medico_id
        }
      }
    } catch (e) {
      console.error('Error al precargar datos de la cita:', e)
    } finally {
      localStorage.removeItem('citaPrecargar')
    }
  },

  methods: {
    iniciales(nombre) {
      if (!nombre) return '?'
      return nombre.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
    },

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

    elegirEspecialidad(id) {
      this.especialidadSeleccionada = id
      this.medicoSeleccionado = ''
      this.filtrarMedicos()
    },

    filtrarMedicos() {
      this.medicosFiltrados = this.medicos.filter(m =>
        Number(m.especialidad_id) === Number(this.especialidadSeleccionada)
      )
    },

    nombreEspecialidad(id) {
      const esp = this.especialidades.find(e => e.id === id)
      return esp ? esp.nombre : ''
    },

    nombreMedico(id) {
      const m = this.medicos.find(m => m.id === id)
      return m ? m.nombre : ''
    },

    mostrarToast(mensaje, tipo = 'exito') {
      if (this.toastTimer) clearTimeout(this.toastTimer)
      this.toast = {
        mensaje,
        tipo,
        icono: tipo === 'exito' ? 'fas fa-check-circle' : 'fas fa-times-circle',
      }
      this.toastTimer = setTimeout(() => {
        this.toast = null
      }, 2500)
    },

    async guardarCita() {
      if (this.guardando) return
      this.guardando = true

      try {
        const formData = new FormData(this.$refs.formRef)

        const res = await fetch(this.storeUrl, {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData,
        })

        if (!res.ok) {
          const data = await res.json().catch(() => null)
          const mensaje = data?.message ?? 'No se pudo guardar la cita. Verifica los datos.'
          this.mostrarToast(mensaje, 'error')
          return
        }

        const data = await res.json()
        this.mostrarToast(data.message ?? 'Cita agendada correctamente', 'exito')

        // Espera a que se vea el mensaje antes de regresar al listado
        setTimeout(() => {
          window.location.href = this.indexUrl
        }, 1400)

      } catch (err) {
        console.error('Error al guardar la cita:', err)
        this.mostrarToast('Ocurrió un error al guardar la cita.', 'error')
      } finally {
        this.guardando = false
      }
    },
  }
}
</script>

<style scoped>
* { box-sizing: border-box; }

.form-wrap {
  padding: 1.5rem 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  align-items: start;
}

/* ── HERO HEADER ── */
.hero-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 22px 24px;
  border-radius: 18px;
  background: linear-gradient(135deg, #1a3c5e 0%, #2563eb 100%);
  color: white;
  margin-bottom: 18px;
  box-shadow: 0 10px 30px rgba(37, 99, 235, .25);
  flex-wrap: wrap;
}

.hero-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  background: rgba(255, 255, 255, .15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.hero-text { flex: 1; min-width: 160px; }

.hero-text h2 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 800;
}

.hero-text p {
  margin: 4px 0 0;
  font-size: .82rem;
  opacity: .85;
}

.badge-activa {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, .15);
  border-radius: 8px;
  padding: 6px 12px;
  font-size: .76rem;
  font-weight: 700;
  white-space: nowrap;
}
.badge-activa i { font-size: 7px; color: #4ade80; }

/* ── PANELS ── */
.panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 20px 22px;
  margin-bottom: 16px;
}

.panel-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: .92rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 16px;
}

.step-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #eff6ff;
  color: #2563eb;
  font-size: .76rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.panel-hint {
  font-size: .8rem;
  color: #94a3b8;
  margin: 0;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field label {
  font-size: .82rem;
  font-weight: 600;
  color: #334155;
}

.input-wrap { position: relative; }

.input-wrap i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 13px;
  pointer-events: none;
  z-index: 1;
}

.input-wrap input,
.input-wrap select {
  width: 100%;
  height: 42px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  padding: 0 12px 0 36px;
  font-size: .85rem;
  color: #0f172a;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  appearance: none;
}

.input-wrap input:focus,
.input-wrap select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
}

.input-wrap input::placeholder { color: #94a3b8; }

/* Autocomplete */
.autocomplete-list {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
  list-style: none;
  margin: 0;
  padding: 6px;
  z-index: 100;
}

.autocomplete-list li {
  padding: 9px 10px;
  border-radius: 8px;
  cursor: pointer;
  font-size: .84rem;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: background .15s;
}
.autocomplete-list li:hover { background: #eff6ff; color: #2563eb; }

.auto-avatar {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: white;
  font-size: .65rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.chip-valid {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: .78rem;
  color: #16a34a;
  font-weight: 600;
  margin-top: 8px;
}

/* ── ESPECIALIDAD CHIPS ── */
.chip-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.chip-option {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 16px;
  border-radius: 999px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #475569;
  font-size: .82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .15s;
}
.chip-option:hover { border-color: #93c5fd; background: #eff6ff; }
.chip-option.activo {
  background: #2563eb;
  border-color: #2563eb;
  color: white;
  box-shadow: 0 4px 12px rgba(37, 99, 235, .3);
}

/* ── DOCTOR CARDS ── */
.doctor-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.doctor-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  border-radius: 14px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  cursor: pointer;
  text-align: left;
  position: relative;
  transition: all .15s;
}
.doctor-card:hover {
  border-color: #93c5fd;
  background: #eff6ff;
  transform: translateY(-2px);
}
.doctor-card.activo {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 6px 16px rgba(37, 99, 235, .15);
}

.doctor-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #1a3c5e, #2563eb);
  color: white;
  font-size: .78rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.doctor-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.doctor-name {
  font-size: .85rem;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.doctor-spec {
  font-size: .72rem;
  color: #64748b;
}

.doctor-check {
  position: absolute;
  top: 10px;
  right: 10px;
  color: #2563eb;
  font-size: .85rem;
  opacity: 0;
  transition: opacity .15s;
}
.doctor-card.activo .doctor-check { opacity: 1; }

/* ── TOGGLE TIPO CITA ── */
.toggle-group {
  display: flex;
  gap: 10px;
}

.toggle-option {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 42px;
  border-radius: 10px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #475569;
  font-size: .82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .15s;
}
.toggle-option:hover { border-color: #93c5fd; }
.toggle-option.activo {
  background: #2563eb;
  border-color: #2563eb;
  color: white;
}

.grid2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

textarea {
  width: 100%;
  min-height: 100px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  padding: 12px 14px;
  font-size: .85rem;
  color: #0f172a;
  font-family: inherit;
  resize: vertical;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
}
textarea:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
}
textarea::placeholder { color: #94a3b8; }

/* ── RESUMEN (sticky) ── */
.form-summary { position: sticky; top: 20px; }

.summary-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  padding: 20px;
}

.summary-title {
  font-size: .78rem;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: .06em;
  margin: 0 0 16px;
}

.summary-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}
.summary-row:last-of-type { border-bottom: none; }

.summary-icon {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  flex-shrink: 0;
}

.summary-label {
  display: block;
  font-size: .7rem;
  color: #94a3b8;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .03em;
}

.summary-value {
  display: block;
  font-size: .85rem;
  color: #0f172a;
  font-weight: 700;
  margin-top: 2px;
}

.summary-divider {
  border: none;
  border-top: 1px solid #f1f5f9;
  margin: 14px 0;
}

.btn-save {
  width: 100%;
  height: 44px;
  border: none;
  border-radius: 12px;
  background: #2563eb;
  color: white;
  font-size: .86rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background .2s, transform .15s;
  margin-bottom: 10px;
}
.btn-save:hover { background: #1d4ed8; transform: translateY(-1px); }

.btn-cancel {
  width: 100%;
  height: 42px;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  background: white;
  color: #64748b;
  font-size: .84rem;
  font-weight: 600;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .2s;
}
.btn-cancel:hover { background: #f1f5f9; color: #0f172a; }

/* ── TOAST ── */
.toast-notificacion {
  position: fixed;
  bottom: 28px;
  right: 28px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 14px;
  font-size: .85rem;
  font-weight: 700;
  color: white;
  z-index: 99999;
  box-shadow: 0 8px 28px rgba(0, 0, 0, .15);
  pointer-events: none;
}
.toast-notificacion.exito {
  background: #10b981;
  box-shadow: 0 8px 28px rgba(16, 185, 129, .35);
}
.toast-notificacion.error {
  background: #ef4444;
  box-shadow: 0 8px 28px rgba(239, 68, 68, .35);
}
.toast-notificacion i { font-size: 1rem; }

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: opacity .3s ease, transform .3s ease;
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateY(16px);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
  .form-grid { grid-template-columns: 1fr; }
  .form-summary { position: static; order: -1; }
}

@media (max-width: 640px) {
  .grid2 { grid-template-columns: 1fr; }
  .hero-header { flex-direction: column; align-items: flex-start; }
}
</style>