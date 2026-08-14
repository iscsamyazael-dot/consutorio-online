<template>

    <div class="col-lg-4">

       <SidebarConsulta></SidebarConsulta>

    </div>

    <div class="col-lg-8">

        <div class="form-scroll">

            <div class="card main-card border-0">

                <div class="card-body p-5">

                    <PacienteForm></PacienteForm>

                    <EvaluacionClinica></EvaluacionClinica>

                    <ActionButtons></ActionButtons>

                </div>

            </div>

        </div>

    </div>

</template>

<script>
// Rutas corregidas: Salen de 'consultas' usando '../' y entran a 'layouts'
import SidebarConsulta from '../layouts/SidebarConsulta.vue'
import PacienteForm from '../layouts/PacienteForm.vue'
import EvaluacionClinica from '../layouts/EvaluacionClinica.vue'
import ActionButtons from '../layouts/ActionButtons.vue'

export default {
    components: {
        SidebarConsulta,
        PacienteForm,
        EvaluacionClinica,
        ActionButtons
    }
}
</script>