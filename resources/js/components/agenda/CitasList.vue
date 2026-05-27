<template>

<div>

    <h2 class="text-xl font-bold mb-4">

        Lista de Citas

    </h2>

    <input
        v-model="search"
        type="text"
        placeholder="Buscar paciente..."
        class="border p-2 rounded w-full mb-4"
    />

    <div v-if="loading">

        Cargando citas...

    </div>

    <div
        v-else-if="error"
        class="text-red-500"
    >

        {{ error }}

    </div>

    <div
        v-else-if="filteredCitas.length === 0"
    >

        No hay citas registradas.

    </div>

    <div v-else>

        <CitaCard
            v-for="cita in filteredCitas"
            :key="cita.id"
            :cita="cita"
            @edit="$emit('edit', $event)"
            @delete="$emit('delete', $event)"
        />

    </div>

</div>

</template>

<script setup lang="ts">

import { computed, ref, onMounted } from 'vue'

import CitaCard from './CitaCard.vue'

import { useCitas } from './composables/useCitas'

const emit = defineEmits([

    'edit',

    'delete'

])

const {

    citas,

    loading,

    error,

    fetchCitas

} = useCitas()

const search = ref('')

const filteredCitas = computed(() => {

    if (!search.value) {

        return citas.value

    }

    return citas.value.filter(cita =>

        cita.paciente
            .toLowerCase()
            .includes(
                search.value.toLowerCase()
            )
    )

})

onMounted(() => {

    fetchCitas()

})

</script>