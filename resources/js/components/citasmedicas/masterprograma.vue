<template>
    <nuevaconsulta></nuevaconsulta>
    <stracartas></stracartas>
    <registrarconsulta
        :store-url="storeUrl"
        :index-url="indexUrl"
        :csrf-token="csrfToken"
        :pacientes="pacientes"
        :medicos="medicos"
        :especialidades="especialidades">
    </registrarconsulta>
</template>

<script>
import Nuevaconsulta from './nuevaconsulta.vue';
import Stracartas from './stracartas.vue';
import Registrarconsulta from './registrarconsulta.vue';

export default {
    components: {
        Nuevaconsulta,
        Stracartas,
        Registrarconsulta
    },
    props: {
        pacientes:      { type: Array, required: true },
        medicos:        { type: Array, required: true },
        especialidades: { type: Array, required: true },
    },
    data() {
        return {
            storeUrl:  '/citas',
            indexUrl:  '/citas',
            csrfToken: document.querySelector('meta[name="csrf-token"]').content
        }
    }
}
</script>