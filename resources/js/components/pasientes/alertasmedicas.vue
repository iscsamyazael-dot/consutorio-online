<template>

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <!-- HEADER -->
        <div class="card-header bg-white border-0 py-3 rounded-top-4">

            <h6 class="fw-bold mb-0 d-flex align-items-center">
                <i class="fas fa-triangle-exclamation text-danger me-2"></i>
                Alertas médicas
            </h6>

        </div>

        <!-- BODY -->
        <div class="card-body">

            <div class="medical-alert d-flex align-items-start gap-3">

                <!-- ICONO -->
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>

                <!-- TEXTO -->
                <div>

                    <strong class="d-block text-dark mb-1">
                        Alergia a penicilina
                    </strong>

                    <small class="text-muted">
                        Evitar medicamentos relacionados.
                    </small>

                </div>

            </div>

        </div>

    </div>

</template>

<script>
export default {
    name: 'Alertasmedicas'
}
</script>

<style scoped>

.medical-alert{
    background: #fff8f8;
    border: 1px solid #ffd6d6;
    border-radius: 16px;
    padding: 16px;
}

.alert-icon{
    width: 45px;
    height: 45px;
    min-width: 45px;
    border-radius: 50%;
    background: #ffe5e5;
    color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

</style>