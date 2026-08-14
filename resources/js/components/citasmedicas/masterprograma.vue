<template>
    <nuevaconsulta></nuevaconsulta>
    <stracartas :citas="citas"></stracartas>
    <registrarconsulta
        :store-url="storeUrl"
        :index-url="paginaCitasUrl"
        :csrf-token="csrfToken"
        :pacientes="pacientes"
        :medicos="medicos"
        :especialidades="especialidades"
        @cita-creada="cargarCitas">
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
            storeUrl:      '/citas',        // para guardar la cita (POST)
            apiCitasUrl:   '/api/citas',     // para TRAER datos en JSON (cargarCitas)
            paginaCitasUrl:'/Agenda',         // para "Cancelar" y redirigir tras guardar (página normal)
            csrfToken:     document.querySelector('meta[name="csrf-token"]').content,
            citas: []
        }
    },
    mounted() {
        this.cargarCitas();
    },
    methods: {
        async cargarCitas() {
            try {
                const res = await fetch(this.apiCitasUrl, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                const data = await res.json();
                this.citas = Array.isArray(data) ? data : (data.data || []);
            } catch (e) {
                console.error('Error al cargar citas:', e);
                this.citas = [];
            }
        }
    }
}
</script>