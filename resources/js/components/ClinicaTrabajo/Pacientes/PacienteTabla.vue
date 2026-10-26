<template>
  <div class="table-container">
    <div class="table-responsive">
      <table class="table table-hover align-middle modern-table">
        <thead>
          <tr>
            <th>Paciente</th>
            <th>Folio</th>
            <th>Teléfono</th>
            <th>Edad</th>
            <th>Género</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="paciente in pacientes" :key="paciente.id">
            <td>
              <div class="d-flex align-items-center gap-3">
                <div
                  class="avatar-circle"
                  :style="{ background: obtenerColor(paciente.nombre) }"
                >
                  {{ (paciente.nombre || 'P').substring(0, 2).toUpperCase() }}
                </div>
                <div>
                  <h6 class="fw-bold mb-0 patient-name">
                    {{ paciente.nombre }} {{ paciente.apellido_paterno }} {{ paciente.apellido_materno }}
                  </h6>
                </div>
              </div>
            </td>
            <td>
              <span class="folio-badge">{{ paciente.folio }}</span>
            </td>
            <td>
              <span class="text-muted">
                <i class="fas fa-phone me-1 text-success" style="font-size: 0.8rem;"></i>
                {{ paciente.telefono }}
              </span>
            </td>
            <td>{{ paciente.edad_formateada }}</td>
            <td>
              <span class="badge gender-badge" :class="paciente.genero === 'Hombre' ? 'badge-male' : 'badge-female'">
                {{ paciente.genero }}
              </span>
            </td>
            <td class="text-end">
              <!-- Alerta de datos faltantes -->
              <span
                v-if="tieneDatosFaltantes(paciente)"
                class="alert-indicator me-2"
                :title="'Faltan: ' + datosFaltantes(paciente).join(', ')"
                @click="irAEditar(paciente.id)"
                style="cursor: pointer"
              >
                <i class="fas fa-exclamation-triangle alert-icon"></i>
              </span>

              <!-- Ver -->
              <button
                class="btn-action btn-view"
                @click="$emit('view', paciente)"
                title="Ver detalles"
              >
                <i class="fas fa-eye"></i>
              </button>

              <!-- Expediente -->
              <a
                class="btn-action btn-folder"
                :href="'ExpedientePacientesClinicaTrabajo/' + paciente.id"
                title="Expediente"
              >
                <i class="fas fa-folder-open"></i>
              </a>

              <!-- Editar -->
              <a
                class="btn-action btn-edit"
                :href="'/ActualizarPaciente/' + paciente.id"
                title="Editar"
              >
                <i class="fas fa-edit"></i>
              </a>

              <!-- Eliminar -->
              <button
                class="btn-action btn-delete"
                @click="$emit('delete', paciente)"
                title="Eliminar"
              >
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({ pacientes: { type: Array, default: () => [] } })
defineEmits(['view', 'delete'])

const irAEditar = (id) => { window.location.href = '/PacienteNuevo/' + id }

const datosFaltantes = (p) => {
  if (!p || !p.nombre) return []
  const campos = [
    { campo: 'email', etiqueta: 'Email' }, { campo: 'sexo', etiqueta: 'Sexo' },
    { campo: 'tipo_sangre', etiqueta: 'Tipo de sangre' }, { campo: 'curp', etiqueta: 'CURP' },
    { campo: 'edad', etiqueta: 'Edad' }
  ]
  return campos.filter(c => !p[c.campo] || String(p[c.campo]).trim() === '').map(c => c.etiqueta)
}

const tieneDatosFaltantes = (p) => datosFaltantes(p).length > 0

const obtenerColor = (nombre) => {
  const colores = ["#1976d2", "#43a047", "#7b1fa2", "#ef6c00", "#00897b", "#c2185b", "#5d4037", "#546e7a"]
  if (!nombre) return "#6c757d"
  let suma = 0
  for (let i = 0; i < String(nombre).length; i++) suma += String(nombre).charCodeAt(i)
  return colores[suma % colores.length]
}
</script>

<style scoped>
.table-container {
  background: white;
  border-radius: 16px;
  padding: 0;
}

.modern-table {
  margin: 0;
}

.modern-table thead {
  background: #f8f9fa;
  border-bottom: 2px solid #e9ecef;
}

.modern-table thead th {
  padding: 16px 20px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #495057;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: none;
}

.modern-table tbody tr {
  border-bottom: 1px solid #f1f3f5;
  transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
  background: #f8f9ff;
}

.modern-table tbody td {
  padding: 16px 20px;
  vertical-align: middle;
  border: none;
}

/* Avatar */
.avatar-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
  font-weight: bold;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.patient-name {
  font-size: 0.95rem;
  color: #212529;
}

/* Badge de folio */
.folio-badge {
  background: #f1f3f5;
  color: #495057;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  font-family: 'Courier New', monospace;
}

/* Badges de género */
.gender-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-male {
  background: #e3f2fd;
  color: #1976d2;
}

.badge-female {
  background: #fce4ec;
  color: #c2185b;
}

/* Botones de acción */
.btn-action {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  margin-left: 4px;
  transition: all 0.2s ease;
  text-decoration: none;
  font-size: 14px;
}

.btn-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-view {
  background: #e3f2fd;
  color: #1976d2;
}

.btn-view:hover {
  background: #1976d2;
  color: white;
}

.btn-folder {
  background: #e0f7fa;
  color: #00838f;
}

.btn-folder:hover {
  background: #00838f;
  color: white;
}

.btn-edit {
  background: #fff3e0;
  color: #ef6c00;
}

.btn-edit:hover {
  background: #ef6c00;
  color: white;
}

.btn-delete {
  background: #ffebee;
  color: #c62828;
}

.btn-delete:hover {
  background: #c62828;
  color: white;
}

/* Indicador de alerta */
.alert-indicator {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.alert-icon {
  color: #ff5722;
  font-size: 16px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>