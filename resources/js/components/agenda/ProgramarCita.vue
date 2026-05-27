<template>

<div class="max-w-4xl mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">

        Gestión de Citas

    </h1>

    <CitaForm
        :cita="selectedCita"
        @save="guardarCita"
    />

    <hr class="my-6">

    <CitasList
        @edit="editarCita"
        @delete="eliminarCita"
    />

</div>

</template>

<script setup lang="ts">

import { ref } from 'vue'

import CitaForm from './CitaForm.vue'
import CitasList from './CitasList.vue'

import { useCitas } from './composables/useCitas'

import type {
    Cita,
    CreateCitaPayload
} from './types'

const {

    createCita,

    updateCita,

    deleteCita

} = useCitas()

const selectedCita = ref<Cita | null>(null)

async function guardarCita(
    payload:CreateCitaPayload
) {

    try {

        if (selectedCita.value) {

            await updateCita(

                selectedCita.value.id,

                payload

            )

        } else {

            await createCita(payload)

        }

        selectedCita.value = null

    } catch (error) {

        console.error(error)

    }

}

function editarCita(
    cita:Cita
) {

    selectedCita.value = cita

}

async function eliminarCita(
    id:number
) {

    const confirmed = confirm(
        '¿Eliminar esta cita?'
    )

    if (!confirmed) {

        return

    }

    await deleteCita(id)

    if (

        selectedCita.value?.id === id

    ) {

        selectedCita.value = null

    }

}

</script>