<template>
    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">
                                Buscar medicamento
                            </label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Nombre, código o lote">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">
                                Estado
                            </label>
                            <select class="form-select">
                                <option>Todos</option>
                                <option>Disponible</option>
                                <option>Bajo Stock</option>
                                <option>Crítico</option>
                                <option>Caducado</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">
                                Categoría
                            </label>
                            <select class="form-select">
                                <option>Todas</option>
                                <option>Analgésicos</option>
                                <option>Antibióticos</option>
                                <option>Antiinflamatorios</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">
                                Caducidad
                            </label>
                            <select class="form-select">
                                <option>Todas</option>
                                <option>Próximo vencer</option>
                                <option>Vigente</option>
                                <option>Caducado</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-3">
                            <button class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-box-open text-primary"></i>
                            Inventario Médico
                        </h5>
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-export"></i>
                            Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle" id="tablaMedicamentos">
                        <thead class="table-light">
                            <tr>
                                <th>Estado</th>
                                <th>Código</th>
                                <th>Medicamento</th>
                                <th>Presentación</th>
                                <th>Lote</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th>Caducidad</th>
                                <th>Ubicación</th>
                                <th>Precio</th>
                                <th width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge bg-success">
                                        Disponible
                                    </span>
                                </td>
                                <td>MED-001</td>
                                <td>
                                    <strong>Paracetamol 500mg</strong>
                                </td>
                                <td>Caja x 20</td>
                                <td>L-2026-01</td>
                                <td>
                                    <span class="fw-bold text-success">
                                        120
                                    </span>
                                </td>
                                <td>30</td>
                                <td>
                                    <span class="text-warning fw-bold">
                                        12/10/2026
                                    </span>
                                </td>
                                <td>Estante A-2</td>
                                <td>$85.00</td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetalleMedicamento">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning text-dark">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-danger">
                                        Crítico
                                    </span>
                                </td>
                                <td>MED-002</td>
                                <td>
                                    <strong>Insulina NPH</strong>
                                </td>
                                <td>Frasco</td>
                                <td>L-2026-08</td>
                                <td>
                                    <span class="fw-bold text-danger">
                                        5
                                    </span>
                                </td>
                                <td>20</td>
                                <td>
                                    <span class="text-danger fw-bold">
                                        02/06/2026
                                    </span>
                                </td>
                                <td>Refrigerador</td>
                                <td>$320.00</td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning text-dark">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</section>

<!--Modal para ver los detalles del médicamento-->
    <div class="modal fade" id="modalDetalleMedicamento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->

            <div class="modal-header text-white" style="background: linear-gradient(135deg,#17a2b8,#138496);">
                <div class="d-flex align-items-center">
                    <div
                        class="mr-3 d-flex justify-content-center align-items-center" style="width:50px; height:50px; border-radius:50%; background:rgba(255,255,255,.15);">
                        <i class="fas fa-pills fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold">
                            Detalle del Medicamento
                        </h4>
                        <small>
                            Información farmacéutica completa
                        </small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <!-- ===================================== -->
            <!-- BODY -->
            <!-- ===================================== -->
            <div class="modal-body p-4">
                <!-- ALERTA -->
                <div class="alert alert-light border-left border-info mb-4">
                    <i class="fas fa-info-circle text-info mr-2"></i>
                    Información detallada del medicamento e inventario.
                </div>

                <!-- ===================================== -->
                <!-- INFORMACION GENERAL -->
                <!-- ===================================== -->
                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-capsules mr-1"></i>
                                Información General
                        </h5>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Código
                                </label>
                                <div class="h5">
                                    MED-001
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Medicamento
                                </label>
                                <div class="h5 font-weight-bold">
                                    Paracetamol 500mg
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Presentación
                                </label>
                                <div>
                                    Caja x 20 tabletas
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Lote
                                </label>
                                <div>
                                    L-2026-01
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Estado
                                </label>
                                <div>
                                    <span class="badge badge-success px-3 py-2">
                                        Disponible
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- INVENTARIO -->
                <!-- ===================================== -->

                <div class="card card-outline card-success mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-boxes mr-1"></i>
                                 Inventario
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Stock Actual
                                </label>
                                <div class="h4 text-success font-weight-bold">
                                    120
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Stock Mínimo
                                </label>
                                <div class="h4 text-danger font-weight-bold">
                                    30
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Caducidad
                                </label>
                                <div class="font-weight-bold text-warning">
                                    12/10/2026
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Ubicación
                                </label>
                                <div>
                                    Estante A-2
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- INFORMACION ECONOMICA -->
                <!-- ===================================== -->

                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-dollar-sign mr-1"></i>
                                Información Económica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="font-weight-bold text-muted">
                                    Precio
                                </label>
                                <div class="h4 text-primary">
                                    $85.00
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold text-muted">
                                    Proveedor
                                </label>
                                <div>
                                    Farmacéutica Mérida
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold text-muted">
                                    Requiere receta
                                </label>
                                <div>
                                    <span class="badge badge-danger px-3 py-2">
                                        Sí
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- FOOTER -->
            <!-- ===================================== -->

            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                        Cerrar
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-edit mr-1"></i>
                        Editar Medicamento
                </button>
            </div>
        </div>
    </div>
</div>

</template>
