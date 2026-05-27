<template>

<form
    @submit.prevent="handleSubmit"
    class="border rounded p-4 shadow"
>

    <h2 class="text-xl font-bold mb-4">

        {{ isEditing
            ? 'Editar Cita'
            : 'Nueva Cita'
        }}

    </h2>

    <input
        v-model="form.paciente"
        type="text"
        placeholder="Paciente"
        class="border p-2 rounded w-full mb-3"
        required
    />

    <input
        v-model.number="form.doctor_id"
        type="number"
        placeholder="Doctor ID"
        class="border p-2 rounded w-full mb-3"
        required
    />

    <input
        v-model="form.fecha"
        type="date"
        class="border p-2 rounded w-full mb-3"
        required
    />

    <input
        v-model="form.hora"
        type="time"
        class="border p-2 rounded w-full mb-3"
        required
    />

    <textarea
        v-model="form.motivo"
        placeholder="Motivo"
        class="border p-2 rounded w-full mb-3"
        required
    />

    <select
        v-model="form.estado"
        class="border p-2 rounded w-full mb-4"
    >

        <option value="pendiente">
            Pendiente
        </option>

        <option value="confirmada">
            Confirmada
        </option>

        <option value="cancelada">
            Cancelada
        </option>

        <option value="completada">
            Completada
        </option>

    </select>

    <div class="flex gap-2">

        <button
            type="submit"
            class="bg-green-600 text-white px-4 py-2 rounded"
        >
            Guardar
        </button>

        <button
            type="button"
            @click="resetForm"
            class="bg-gray-500 text-white px-4 py-2 rounded"
        >
            Limpiar
        </button>

    </div>

</form>

</template>

<script setup lang="ts">

import { computed, reactive, watch } from 'vue'

import type {
    Cita,
    CreateCitaPayload
} from './types'

const props = defineProps<{

    cita?:Cita | null

}>()

const emit = defineEmits([

    'save'

])

const isEditing = computed(() => {

    return !!props.cita

})

const defaultForm = ():CreateCitaPayload => ({

    doctor_id:0,

    paciente:'',

    fecha:'',

    hora:'',

    motivo:'',

    estado:'pendiente'

})

const form = reactive(

    defaultForm()

)

watch(

    () => props.cita,

    (newValue) => {

        if (!newValue) {

            Object.assign(
                form,
                defaultForm()
            )

            return
        }

        Object.assign(form, {

            doctor_id:
                newValue.doctor_id,

            paciente:
                newValue.paciente,

            fecha:
                newValue.fecha,

            hora:
                newValue.hora,

            motivo:
                newValue.motivo,

            estado:
                newValue.estado
        })

    },

    { immediate:true }

)

function handleSubmit() {

    emit('save', {

        ...form
    })

}

function resetForm() {

    Object.assign(

        form,

        defaultForm()

    )

}

</script>