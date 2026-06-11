<template>
    <div>

        <!-- Card superior (ya tiene su propio glass-card interno) -->
        <nuevopacinte></nuevopacinte>

        <!-- Card de consulta general (ya tiene su propio glass-card interno) -->
        <consultageneral></consultageneral>

        <!-- Información del paciente + Triage en el mismo glass-card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="glass-card form-card">
                    <informacionpacinete></informacionpacinete>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
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
    data() {
        return {}
    }
}
</script>

<style scoped>
.form-card {
    padding: 35px 40px 40px;
    animation: cardIn .55s cubic-bezier(.22,1,.36,1) both;
    animation-delay: .15s;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .form-card {
        padding: 24px 20px 28px;
    }
}
</style>