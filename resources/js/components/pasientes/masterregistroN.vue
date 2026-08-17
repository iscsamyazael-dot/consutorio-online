<template>
    <div>

        <!-- Card superior (ya tiene su propio glass-card interno) -->
        <nuevopacinte :paciente="paciente"></nuevopacinte>

        <!-- Card de consulta general (ya tiene su propio glass-card interno) -->
        <consultageneral :paciente="paciente"></consultageneral>

        <!-- Información del paciente + Triage en el mismo glass-card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="glass-card form-card">
                    <informacionpacinete :paciente="paciente"></informacionpacinete>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios'

import nuevopacinte from './nuevopacinte.vue'
import consultageneral from './consultageneral.vue'
import informacionpacinete from './informacionpacinete.vue'
import triage from './triage.vue'

export default {
    components: {
        nuevopacinte,
        consultageneral,
        informacionpacinete,
        triage
    },

    props: {
        pacienteId: {
            type: [Number, String],
            default: null
        }
    },

    data() {
        return {
            paciente: {}
        }
    },

    async mounted() {
        if (this.pacienteId) {
            try {
                const response = await axios.get(`/pacientes/${this.pacienteId}`);
                this.paciente = response.data;
            } catch (error) {
                console.error('Error al cargar el paciente:', error);
            }
        }
    }
}
</script>