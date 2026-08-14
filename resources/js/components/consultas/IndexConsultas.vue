<template>
    <div class="row">
        <div class="col-xl-12 mb-4">
            <ConsultaTable
                :consultas="consultas"
                @select="setActivePatient"
                @view="openDetailModal"
                @delete="deleteConsulta"
            />
        </div>
    </div>
</template>

<script>
// Ruta corregida: Sube un nivel para encontrar el archivo suelto en components
import ConsultaTable from '../ConsultaTable.vue'

export default {
    components: {
        ConsultaTable
    },
    data() {
        return {
            consultas: [],
            activePatient: null,
            selectedPatient: null
        }
    },
    methods: {
        setActivePatient(patient) {
            this.activePatient = patient
        },
        openDetailModal(patient) {
            this.selectedPatient = patient
        },
        deleteConsulta(id) {
            this.consultas = this.consultas.filter(
                consulta => consulta.id !== id
            )
        }
    }
}
</script>