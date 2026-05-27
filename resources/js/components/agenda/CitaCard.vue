<template>

<div class="border rounded p-4 mb-3 shadow">

    <h3 class="font-bold text-lg">
        {{ cita.paciente }}
    </h3>

    <p>
        Doctor ID:
        {{ cita.doctor_id }}
    </p>

    <p>
        Fecha:
        {{ cita.fecha }}
    </p>

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

        <span :class="estadoClass">

            {{ cita.estado }}

        </span>

    </p>

    <div class="mt-3 flex gap-2">

        <button
            @click="$emit('edit', cita)"
            class="bg-blue-500 text-white px-3 py-1 rounded"
        >
            Editar
        </button>

        <button
            @click="$emit('delete', cita.id)"
            class="bg-red-500 text-white px-3 py-1 rounded"
        >
            Eliminar
        </button>

    </div>

</div>

</template>

<script setup lang="ts">

import { computed } from 'vue'
import type { Cita } from './types'

const props = defineProps<{

    cita:Cita

}>()

defineEmits([

    'edit',

    'delete'

])

const estadoClass = computed(() => {

    switch (props.cita.estado) {

        case 'confirmada':

            return 'text-green-600 font-bold'

        case 'cancelada':

            return 'text-red-600 font-bold'

        case 'pendiente':

            return 'text-yellow-600 font-bold'

        case 'completada':

            return 'text-blue-600 font-bold'

        default:

            return ''
    }

})

</script>