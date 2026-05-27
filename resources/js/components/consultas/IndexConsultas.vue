<template>

<div class="container-fluid content-wrapper-custom">

    <ConsultaHeader />

    <StatsGrid
        :stats="stats"
    />

    <div class="row">

        <div class="col-xl-4 mb-4">

            <ActivePatientPanel
                :patient="activePatient"
                @open-detail="openDetailModal"
                @open-expediente="openExpediente"
            />

        </div>

        <div class="col-xl-8 mb-4">

            <ConsultaTable
                :consultas="consultas"
                @select="setActivePatient"
                @view="openDetailModal"
                @delete="deleteConsulta"
            />

        </div>

    </div>

    <PatientDetailModal
        :show="showDetailModal"
        :patient="selectedPatient"
        @close="closeDetailModal"
    />

    <ExpedienteModal
        :show="showExpedienteModal"
        :patient="selectedPatient"
        @close="closeExpedienteModal"
    />

</div>

</template>

<script setup>
import { ref } from 'vue'

import ConsultaHeader from '../../components/consultas/header/ConsultaHeader.vue'
import StatsGrid from '../../components/consultas/stats/StatsGrid.vue'
import ActivePatientPanel from '../../components/consultas/panel/ActivePatientPanel.vue'
import ConsultaTable from '../../components/consultas/table/ConsultaTable.vue'
import PatientDetailModal from '../../components/consultas/modals/PatientDetailModal.vue'
import ExpedienteModal from '../../components/consultas/modals/ExpedienteModal.vue'

const consultas = ref([])

const activePatient = ref(null)

const selectedPatient = ref(null)

const showDetailModal = ref(false)

const showExpedienteModal = ref(false)

const stats = ref({
    hoy: 24,
    activos: 12,
    pendientes: 2,
    urgencias: 3
})

const setActivePatient = (patient) => {
    activePatient.value = patient
}

const openDetailModal = (patient) => {
    selectedPatient.value = patient
    showDetailModal.value = true
}

const closeDetailModal = () => {
    showDetailModal.value = false
}

const openExpediente = (patient) => {
    selectedPatient.value = patient
    showExpedienteModal.value = true
}

const closeExpedienteModal = () => {
    showExpedienteModal.value = false
}
</script>