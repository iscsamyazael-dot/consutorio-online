

<template>
    <div>
        <StatsCards />
        <PacientesTable />
    </div>
</template>
<script >
import { ref } from 'vue' // Esto reemplaza al data()
import StatsCards from './components/StatsCards.vue'
import PacientesTable from './PasientesTable.vue'

export default {
     
    components: {
        StatsCards,
        PacientesTable
    },
    data() {
        return {}
    },
    methods: {}
}
</script>
