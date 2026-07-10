<template>
    <!-- HEADER -->
    <div class="card-header bg-transparent border-0 p-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">


            <!-- TITULO -->
            <div>

                <h3 class="fw-bold text-dark mb-1">
                    <i class="fas fa-users text-primary me-2"></i>
                    Gestión de Usuarios
                </h3>

                <small class="text-muted">
                    Administración y control de usuarios registrados
                </small>
            </div>
            <!-- BOTON -->
            <button 
                class="btn btn-light border rounded-pill px-4 py-2 shadow-sm text-secondary fw-semibold"
                @click="$emit('volver')"
            >
                <i class="fas fa-arrow-left me-2"></i>
                Volver al registro
            </button>
        </div>
    </div>

    <div class="container-fluid">
        <!-- CARD PRINCIPAL -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">


            <!-- CUERPO -->
            <div class="card-body px-4">

                <!-- BARRA DE HERRAMIENTAS -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            Usuarios registrados
                        </h5>
                        <small class="text-muted">
                            Consulta y administra los accesos al sistema
                        </small>
                    </div>

                    <!-- BUSCADOR -->
                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="width:280px;">

                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="fas fa-search text-primary"></i>
                        </span>

                        <input
                            type="text"
                            class="form-control border-0 ps-2"
                            placeholder="Buscar usuario..."
                            style="background:#f8fafc;"
                        >

                    </div>
                </div>

                <!-- TABLA -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">


                        <thead>
                            <tr class="text-secondary small">

                                <th>
                                    Usuario
                                </th>

                                <th>
                                    Correo electrónico
                                </th>

                                <th>
                                    Rol
                                </th>

                                <th class="text-center">
                                    Acciones
                                </th>
                            </tr>
                        </thead>



                        <tbody>

                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div 
                                            class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="
                                            width:42px;
                                            height:42px;
                                            background:#eef2ff;
                                            color:#4f46e5;"
                                        >

                                            <i class="fas fa-user"></i>

                                        </div>



                                        <div>
                                            <strong>
                                                Juan Pérez
                                            </strong>

                                            <small class="d-block text-muted">
                                                Usuario activo
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    juan@gmail.com
                                </td>

                                <td>
                                    <span class="badge rounded-pill px-3"
                                    style="background:#eef2ff;color:#4f46e5;">
                                        Administrador
                                    </span>
                                </td>

                                <td class="text-center">
                                    <button 
                                        class="btn btn-outline-danger btn-sm rounded-circle"
                                        title="Eliminar usuario"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </button>
                                </td>
                            </tr>

                            <tr>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div 
                                            class="rounded-circle d-flex align-items-center justify-content-center"
                                            style="
                                            width:42px;
                                            height:42px;
                                            background:#ecfeff;
                                            color:#0891b2;"
                                        >
                                            <i class="fas fa-user-md"></i>
                                        </div>

                                        <div>

                                            <strong>
                                                María López
                                            </strong>


                                            <small class="d-block text-muted">
                                                Médico registrado
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    maria@gmail.com
                                </td>

                                <td>
                                    <span 
                                        class="badge rounded-pill px-3"
                                        style="background:#ecfeff;color:#0891b2;"
                                    >
                                        Médico
                                    </span>

                                </td>

                                <td class="text-center">
                                    <button 
                                        class="btn btn-outline-danger btn-sm rounded-circle"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>

defineEmits(['volver'])

</script>