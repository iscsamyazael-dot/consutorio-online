<!-- Interfaz alertas pequeñas-->
<template>
    <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold text-danger">
                        <i class="fas fa-bell"></i>
                        Alertas Críticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3"></i>
                        <div>
                            <strong>Paracetamol 500mg</strong>
                            tiene stock crítico.
                        </div>
                    </div>
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="fas fa-clock me-3"></i>
                        <div>
                            <strong>Insulina NPH</strong>
                            caduca en 12 días.
                        </div>
                    </div>
                    <div class="alert alert-secondary d-flex align-items-center">
                        <i class="fas fa-ban me-3"></i>
                        <div>
                            <strong>Amoxicilina</strong>
                            sin existencias.
                        </div>
                    </div>
                </div>
            </div>
</template>
