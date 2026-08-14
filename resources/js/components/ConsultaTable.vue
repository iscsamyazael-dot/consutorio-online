<template>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100">

    <div class="table-responsive">

        <table class="table premium-table align-middle mb-0">

            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Folio</th>
                    <th>Estado</th>
                    <th>Urgencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <ConsultaRow
                    v-for="consulta in consultas"
                    :key="consulta.folio"
                    :consulta="consulta"
                    @select="$emit('select', consulta)"
                    @view="$emit('view', consulta)"
                    @delete="$emit('delete', consulta)"
                />

            </tbody>

        </table>

    </div>

</div>

</template>

<script setup>
defineProps({
    consultas: Array
})

defineEmits([
    'select',
    'view',
    'delete'
])
</script>