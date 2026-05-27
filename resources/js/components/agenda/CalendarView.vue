<template>

<div>

    <h2 class="text-xl font-bold mb-4">

        Calendario de Citas

    </h2>

    <input
        v-model="selectedDate"
        type="date"
        class="border p-2 rounded mb-4"
    />

    <div
        v-if="filteredCitas.length === 0"
    >

        No hay citas para esta fecha.

    </div>

    <div
        v-else
        class="space-y-3"
    >

        <div
            v-for="cita in filteredCitas"
            :key="cita.id"
            class="border rounded p-3 shadow"
        >

            <h3 class="font-bold">

                {{ cita.paciente }}

            </h3>

            <p>

                Hora:
                {{ cita.hora }}

            </p>

            <p>

                Motivo:
                {{ cita.motivo }}

            </p>

            <p>

                Estado:
                {{ cita.estado }}

            </p>

        </div>

    </div>

</div>

</template>

<script setup lang="ts">

import {
    computed,
    ref,
    onMounted
} from 'vue'

import { useCitas }
from './composables/useCitas'

const {

    citas,

    fetchCitas

} = useCitas()

const selectedDate = ref(

    new Date()
        .toISOString()
        .split('T')[0]

)

const filteredCitas = computed(() => {

    return citas.value.filter(

        cita =>

            cita.fecha ===
            selectedDate.value

    )

})

onMounted(() => {

    fetchCitas()

})

</script>