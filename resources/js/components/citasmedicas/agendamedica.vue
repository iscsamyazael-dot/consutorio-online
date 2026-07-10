<template>
  <div>
    <div>
      <h1 class="planner-title">
        Agenda Médica
      </h1>
      <p class="planner-subtitle">
        Sistema profesional de planificación de citas
      </p>
    </div>

    <div class="planner-actions">
      <div class="live-date">{{ fechaHoy }}</div>

      <div class="planner-filtros">
        <select v-model="especialidadSeleccionada" class="filtro-select" @change="onEspecialidadChange">
          <option value="">Todas las especialidades</option>
          <option
            v-for="esp in especialidades"
            :key="esp.id"
            :value="esp.id"
          >
            {{ esp.nombre }}
          </option>
        </select>

        <select v-model="medicoSeleccionado" class="filtro-select" @change="emitirFiltro">
          <option value="">Todos los médicos</option>
          <option
            v-for="med in medicosFiltrados"
            :key="med.id"
            :value="med.id"
          >
            {{ med.nombre }}
          </option>
        </select>
      </div>

      <a :href="createUrl" class="btn-create">
        <i class="fas fa-plus"></i>
        Nueva Cita
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AgendaMedica',
  props: {
    createUrl: {
      type: String,
      required: true
    },
    medicos: {
      type: Array,
      default: () => []
    },
    especialidades: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      fechaHoy: '',
      medicoSeleccionado: '',
      especialidadSeleccionada: ''
    }
  },
  computed: {
    // Si se eligió especialidad, solo muestra médicos de esa especialidad
    medicosFiltrados() {
      if (!this.especialidadSeleccionada) return this.medicos
      return this.medicos.filter(m => m.especialidadId === this.especialidadSeleccionada)
    }
  },
  mounted() {
    this.updateDate();
    this.timer = setInterval(this.updateDate, 60000);
  },
  beforeUnmount() {
    clearInterval(this.timer);
  },
  methods: {
    updateDate() {
      this.fechaHoy = new Date().toLocaleDateString('es-MX', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    onEspecialidadChange() {
      // Si el médico elegido no pertenece a la nueva especialidad, se limpia
      if (this.medicoSeleccionado) {
        const sigueSiendoValido = this.medicosFiltrados.some(m => m.id === this.medicoSeleccionado)
        if (!sigueSiendoValido) this.medicoSeleccionado = ''
      }
      this.emitirFiltro()
    },
    emitirFiltro() {
      this.$emit('filtro-cambiado', {
        medicoId: this.medicoSeleccionado,
        especialidadId: this.especialidadSeleccionada
      });
    }
  }
}
</script>

<style scoped>
.planner-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a3c5e;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
}

.planner-subtitle {
  font-size: 0.95rem;
  color: #6b7280;
  margin: 0;
  font-weight: 400;
}

.planner-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 1.5rem;
  padding: 1rem 1.25rem;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

.live-date {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: capitalize;
}

.planner-filtros {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filtro-select {
  height: 38px;
  padding: 0 12px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  color: #1a3c5e;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  outline: none;
  transition: border-color 0.2s ease;
}

.filtro-select:hover,
.filtro-select:focus {
  border-color: #1a3c5e;
}

.btn-create {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  background: #1a3c5e;
  color: #ffffff;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.2s ease;
}

.btn-create:hover {
  background: #15304d;
  color: #ffffff;
}

.btn-create i {
  font-size: 14px;
}

@media (max-width: 768px) {
  .planner-actions {
    flex-direction: column;
    align-items: stretch;
  }
  .planner-filtros {
    order: 2;
  }
}
</style>