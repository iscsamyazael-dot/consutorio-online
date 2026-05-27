<template>
    <div class="container-fluid main-wrapper">

        <div class="row g-4">

            <div class="col-lg-4">
                <SidebarConsulta />
            </div>

            <div class="col-lg-8">

                <div class="form-scroll">

                    <div class="card main-card border-0">

                        <div class="card-body p-5">

                            <PacienteForm />

                            <EvaluacionClinica />

                            <ActionButtons />

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</template>

<script setup>
import SidebarConsulta from '../../components/consultas/SidebarConsulta.vue'
import PacienteForm from '../../components/consultas/PacienteForm.vue'
import EvaluacionClinica from '../../components/consultas/EvaluacionClinica.vue'
import ActionButtons from '../../components/consultas/ActionButtons.vue'
</script>