<script setup>
import { ref } from 'vue' // Esto reemplaza al data()
import StatsCards from './components/StatsCards.vue'
import PacientesTable from './components/PacientesTable.vue'

// --- TUS VARIABLES (Esto reemplaza a data) ---
// Ejemplo por si necesitas reactividad: 
// const pacientes = ref([])

// --- TUS FUNCIONES (Esto reemplaza a methods) ---
// Ejemplo de cómo crear un método aquí adentro:
// const obtenerPacientes = () => {
//     console.log('Buscando pacientes...')
// }
</script>

<template>
    <div>
        <StatsCards />
        <PacientesTable />
    </div>
</template>