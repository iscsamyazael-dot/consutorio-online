<template>
        <!-- BREADCRUMB -->
        <nav class="mb-4 small text-muted">
            <i class="fas fa-home me-1"></i>
            Pacientes /
            <strong class="text-dark">
                Juan Pérez García
            </strong>
        </nav>

        <!-- HERO PACIENTE -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">

            <div class="card-body">

                <div class="row align-items-center">

                    <!-- AVATAR -->
                    <div class="col-lg-1 col-md-2 col-12 text-center mb-3 mb-md-0">

                        <div class="avatar-xl mx-auto">
                            JP
                        </div>

                    </div>

                    <!-- INFO -->
                    <div class="col-lg-8 col-md-7 col-12">

                        <h3 class="fw-bold mb-3">
                            Juan Pérez García
                        </h3>

                        <div class="d-flex flex-wrap gap-2">

                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                Expediente #0001
                            </span>

                            <span class="badge bg-info rounded-pill px-3 py-2">
                                35 años
                            </span>

                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                Masculino
                            </span>

                            <span class="badge bg-success rounded-pill px-3 py-2">
                                Paciente activo
                            </span>

                        </div>

                    </div>

                    <!-- BOTÓN -->
                    <div class="col-lg-3 col-md-3 col-12 text-md-end text-center mt-3 mt-md-0">

                        <a href="/consultas/create"
                           class="btn btn-success rounded-pill px-4 shadow-sm">

                            <i class="fas fa-stethoscope me-2"></i>
                            Nueva consulta

                        </a>

                    </div>

                </div>

            </div>

        </div>
</template>

<style scoped>

.avatar-xl{
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg,#0d6efd,#0dcaf0);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
}

</style>