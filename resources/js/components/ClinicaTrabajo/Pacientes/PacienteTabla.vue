<template>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Teléfono</th>
                    <th>Edad</th>
                    <th>Genero</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <tr v-for="paciente in pacientes" :key="paciente.id">
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle" :style="{ background: obtenerColor(paciente.nombre) }">
                                {{ (paciente.nombre || 'P').substring(0, 2).toUpperCase() }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">
                                    {{ paciente.nombre }} {{ paciente.apellido_paterno }} {{ paciente.apellido_materno }}
                                </h6>
                                <small class="text-muted">FOLIO: {{ paciente.folio }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ paciente.telefono }}</td>
                    <td>{{ paciente.edad_formateada }}</td>
                    <td>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                            {{ paciente.genero }}
                        </span>
                    </td>
                    <td class="text-end">
                        <!-- Alerta de datos faltantes (Redirige) -->
                        <span
                            v-if="tieneDatosFaltantes(paciente)"
                            class="alert-indicator me-2"
                            :title="'Faltan: ' + datosFaltantes(paciente).join(', ')"
                            @click="irAEditar(paciente.id)"
                            style="cursor:pointer"
                        >
                            <i class="fas fa-exclamation-triangle alert-icon"></i>
                        </span>

                        <!-- Ver -->
                        <button class="btn btn-light btn-sm action-btn me-2" @click="$emit('view', paciente)" title="Ver">
                            <i class="fas fa-eye text-primary"></i>
                        </button>

                        <!-- Expediente -->
                        <a class="btn btn-light btn-sm action-btn me-2" :href="'ExpedientePacientesClinicaTrabajo/' + paciente.id" title="Expediente">
                            <i class="fas fa-folder-open text-info"></i>
                        </a>

                        <!-- Editar (Enlace directo, sin modal) -->
                        <a class="btn btn-light btn-sm action-btn me-2" :href="'/ActualizarPaciente/' + paciente.id" title="Editar">
                            <i class="fas fa-edit text-warning"></i>
                        </a>

                        <!-- Eliminar -->
                        <button class="btn btn-light btn-sm action-btn" @click="$emit('delete', paciente)" title="Eliminar">
                            <i class="fas fa-trash text-danger"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
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