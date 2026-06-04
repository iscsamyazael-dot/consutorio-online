<template>


    <!-- Información -->
    <div class="col-lg-8">
        <div class="card shadow border-0 rounded-4 h-100">
            <div class="card-body p-4">

                <!-- Información Personal -->
                <h5 class="fw-bold text-primary mb-4">
                    <i class="fas fa-id-card me-2"></i>
                    Información Personal
                </h5>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted fw-semibold">
                            Nombre Completo
                        </label>
                        <div class="fs-5">
                            Dr. Gael
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted fw-semibold">
                            Correo Electrónico
                        </label>
                        <div class="fs-5">
                            doctor@consultorio.com
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Información Profesional -->
                <h5 class="fw-bold text-primary mb-4">
                    <i class="fas fa-user-md me-2"></i>
                    Información Profesional
                </h5>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="text-muted fw-semibold">
                            Rol
                        </label>
                        <div class="fs-5">
                            <i class="fas fa-briefcase text-primary me-2"></i>
                            Médico
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted fw-semibold">
                            Especialidad
                        </label>
                        <div class="fs-5">
                            <i class="fas fa-stethoscope text-primary me-2"></i>
                            Medicina General
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted fw-semibold">
                            Cédula Profesional
                        </label>
                        <div class="fs-5">
                            <i class="fas fa-id-badge text-primary me-2"></i>
                            12345678
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted fw-semibold">
                            Fecha de Registro
                        </label>
                        <div class="fs-5">
                            <i class="fas fa-calendar text-primary me-2"></i>
                            29/05/2026
                        </div>
                    </div>
                </div>

                <!-- Botón dentro de la tarjeta -->
                <div class="text-end mt-5">
                    <button
                        class="btn btn-primary rounded-pill px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarPerfil"
                    >
                        <i class="fas fa-pen me-2"></i>
                        Editar Perfil
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL EDITAR PERFIL -->
    <div
        class="modal fade"
        id="modalEditarPerfil"
        tabindex="-1"
        aria-labelledby="modalEditarPerfilLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">

                <!-- Header -->
                <div class="modal-header bg-primary text-white">
                    <h5
                        class="modal-title fw-bold"
                        id="modalEditarPerfilLabel"
                    >
                        <i class="fas fa-user-edit me-2"></i>
                        Editar Perfil
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">

                    <div class="text-center mb-4">
                        <span class="badge bg-primary rounded-circle p-4">
                            <i class="fas fa-user-md fa-3x"></i>
                        </span>
                    </div>

                    <h6 class="fw-bold text-primary mb-3">
                        Información Personal
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nombre Completo
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="Dr. Samuel"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Correo Electrónico
                            </label>
                            <input
                                type="email"
                                class="form-control"
                                value="doctor@consultorio.com"
                            >
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3">
                        Información Profesional
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Especialidad
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="Medicina General"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Cédula Profesional
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                value="12345678"
                            >
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer border-0">
                    <button
                        type="button"
                        class="btn btn-outline-secondary rounded-pill"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary rounded-pill px-4"
                    >
                        <i class="fas fa-save me-2"></i>
                        Guardar Cambios
                    </button>
                </div>

            </div>
        </div>
    </div>



</template>