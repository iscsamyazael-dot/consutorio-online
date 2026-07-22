<template>

     <!-- CARDS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">
                                Recetas Activas
                            </p>
                            <h2 class="fw-bold text-success">
                                24
                            </h2>
                        </div>
                        <div class="icon-circle bg-success">
                            <i class="fas fa-file-medical text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">
                                Completadas
                            </p>
                            <h2 class="fw-bold text-primary">
                                120
                            </h2>
                        </div>
                        <div class="icon-circle bg-primary">

                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">
                                Alertas IA
                            </p>
                            <h2 class="fw-bold text-danger">
                                3
                            </h2>
                        </div>
                        <div class="icon-circle bg-danger">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1">
                                IA Sugerencias
                            </p>
                            <h2 class="fw-bold text-warning">
                                18
                            </h2>
                        </div>
                        <div class="icon-circle bg-warning">

                            <i class="fas fa-robot text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</template>


<style scoped>

.icon-circle{

    width: 55px;
    height: 55px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 22px;
}

</style>