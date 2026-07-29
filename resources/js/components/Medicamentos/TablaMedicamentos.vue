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
                <th>Código</th>
                <th>Medicamento</th>
                <th>Presentación</th>
                <th>Lote</th>
                <th>Stock</th>
                <th>Stock Mínimo</th>
                <th>Caducidad</th>
                <th>Estado</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>

        <tbody>
            <tr v-for=" medica in medicamentos" :key="medica.id">
                <!-- CODIGO -->
                <td>
                    <span class="font-weight-bold text-primary">
                        {{medica.codigo}}
                    </span>
                </td>

                <!-- MEDICAMENTO -->
                <td>
                    <div>
                        <strong>
                             {{medica.nombre}}
                        </strong>
                        <br>
                        <small class="text-muted">
                             {{medica.nombre_generico}}
                        </small>
                    </div>
                </td>

                <!-- PRESENTACION -->
                <td>
                    {{medica.presentacion}}
                </td>

                <!-- LOTE -->
                <td>
                    <span
                        v-if="medica.ultimo_movimiento" 
                        class="badge badge-secondary px-3 py-2">
                        {{ medica.ultimo_movimiento.lote }}
                    </span>
                    <span
                        v-else
                        class="badge badge-warning px-3 py-2">
                        Sin Lote
                    </span>
                </td>

                <!-- STOCK -->
                <td>
                    <span class="badge badge-success px-3 py-2">
                        {{ medica.inventario.stock_actual }}
                    </span>
                </td>

                <!-- STOCK MINIMO -->
                <td>
                    <span class="badge badge-warning px-3 py-2">
                        {{ medica.inventario.stock_minimo }}
                    </span>
                </td>

                <!-- CADUCIDAD -->
                <td>
                    <span
                        v-if="medica.ultimo_movimiento" 
                        class="text-warning font-weight-bold">
                        {{ medica.ultimo_movimiento.fecha_caducidad }}
                    </span>
                    <span 
                        v-else
                        class="text-danger font-weight-bold">
                        Sin fecha de caducidad
                    </span>
                </td>

                <!-- ESTADO -->
                <td>
                    <span
                        v-if="medica.activo == 1"
                        class="badge badge-success px-3 py-2">
                        Activo
                    </span>
                    <span
                        v-else
                        class="badge badge-danger px-3 py-2">
                        Inactivo
                    </span>
                </td>

                <!-- ACCIONES -->
                <td>
                    <div class="btn-group shadow-sm">

                        <!-- VER DETALLE -->
                        <button
                            class="btn btn-sm btn-info"
                            data-toggle="modal"
                            data-target="#modalDetalleMedicamento"
                            title="Ver Detalle"
                            @click="verMedicamento(medica.id)">
                            <i class="fas fa-eye"></i>
                        </button>

                        <!-- EDITAR -->
                        <button
                            class="btn btn-sm btn-primary"
                            data-toggle="modal"
                            data-target="#modalEditarMedicamento"
                            title="Editar Medicamento">
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- MOVIMIENTO -->
                        <button
                            class="btn btn-sm btn-warning text-dark"
                            data-toggle="modal"
                            data-target="#modalMovimientoInventario"
                            title="Movimiento Inventario">
                            <i class="fas fa-exchange-alt"></i>
                        </button>

                        <!-- ELIMINAR -->
                        <button
                            class="btn btn-sm btn-danger"
                            title="Eliminar">
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

<!-- ============================================= -->
<!-- MODAL DETALLE MEDICAMENTO -->
<!-- ============================================= -->
<!-- ===================================== -->
<!-- MODAL DETALLE MEDICAMENTO -->
<!-- ===================================== -->
<div class="modal fade" id="modalDetalleMedicamento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- HEADER -->
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#17a2b8,#138496);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex justify-content-center align-items-center"
                        style="
                            width:55px;
                            height:55px;
                            border-radius:50%;
                            background:rgba(255,255,255,.15);">
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
                <button
                    type="button"
                    class="close text-white"
                    data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <!-- BODY -->
            <div class="modal-body p-4">
                <!-- ALERTA -->
                <div class="alert alert-light border-left border-info shadow-sm mb-4">
                    <i class="fas fa-info-circle text-info mr-2"></i>
                    Información detallada del medicamento registrado en el sistema.
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION GENERAL -->
                <!-- ===================================== -->
                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-capsules mr-2"></i>
                            Información General
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Código
                                </label>
                                <div class="h6">
                                    {{ medicamentoDetalle.codigo }}
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Nombre Comercial
                                </label>
                                <div class="h5 font-weight-bold">
                                    {{ medicamentoDetalle.nombre }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Estado
                                </label>
                                <div>
                                    <span v-if="medicamentoDetalle.activo == 1" 
                                        class="badge badge-success px-3 py-2">
                                        Activo
                                    </span>
                                    <span v-else
                                        class="badge badge-danger px-3 py-2">
                                        Inactivo
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Nombre Genérico
                                </label>
                                <div>
                                    {{ medicamentoDetalle.nombre_generico }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Concentración
                                </label>
                                <div>
                                    {{ medicamentoDetalle.concentracion }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Vía Administración
                                </label>
                                <div>
                                    {{ medicamentoDetalle.via_administracion }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Presentación
                                </label>
                                <div>
                                    {{ medicamentoDetalle.presentacion }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Requiere Receta
                                </label>
                                <div>
                                    <span v-if="medicamentoDetalle == 1"
                                         class="badge badge-danger px-3 py-2">
                                         Sí
                                    </span>
                                    <span v-else
                                       class="badge badge-succes px-3 py-2">
                                        No
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
                            <i class="fas fa-boxes mr-2"></i>
                            Inventario Actual
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Stock Actual
                                </label>
                                <div class="h4 text-success font-weight-bold">
                                    {{ medicamentoDetalle.inventario.stock_actual}}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Stock Mínimo
                                </label>
                                <div class="h4 text-danger font-weight-bold">
                                    {{ medicamentoDetalle.inventario.stock_minimo}}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Estado Inventario
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
                <!-- INFORMACION MEDICA -->
                <!-- ===================================== -->
                <div class="card card-outline card-danger mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-notes-medical mr-2"></i>
                            Información Médica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="font-weight-bold text-muted">
                                Descripción Farmacológica
                            </label>
                            <div class="border rounded p-3 bg-light">
                                {{ medicamentoDetalle.descripcion}}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="font-weight-bold text-muted">
                                Indicaciones
                            </label>
                            <div class="border rounded p-3 bg-light">
                                {{ medicamentoDetalle.indicaciones}}
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="font-weight-bold text-muted">
                                Contraindicaciones
                            </label>
                            <div class="border rounded p-3 bg-light">
                                {{ medicamentoDetalle.contraindicaciones}}
                            </div>
                        </div>
                        <div>
                            <label class="font-weight-bold text-muted">
                                Efectos Secundarios
                            </label>
                            <div class="border rounded p-3 bg-light">
                                {{ medicamentoDetalle.efectos_secundarios}}
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
                            <i class="fas fa-dollar-sign mr-2"></i>
                            Información Económica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="font-weight-bold text-muted">
                                    Precio
                                </label>
                                <div class="h4 text-primary">
                                    {{ medicamentoDetalle.precio}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold text-muted">
                                    Fecha Registro
                                </label>
                                <div>
                                    {{ new Date(medicamentoDetalle.created_at).toLocaleDateString() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- FOOTER -->
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
<!--Modal para editar un medicamento-->
<!-- ===================================== -->
<!-- MODAL EDITAR MEDICAMENTO -->
<!-- ===================================== -->
<div class="modal fade" id="modalEditarMedicamento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="modal-header text-white"
                style="background:linear-gradient(135deg,#007bff,#0056b3);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex justify-content-center align-items-center"
                        style="
                            width:55px;
                            height:55px;
                            border-radius:50%;
                            background:rgba(255,255,255,.15);">
                        <i class="fas fa-edit fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold">
                            Editar Medicamento
                        </h4>
                        <small>
                            Actualización de información farmacéutica
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
                <div class="alert alert-light border-left border-primary shadow-sm mb-4">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Modifique la información farmacéutica del medicamento.
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION GENERAL -->
                <!-- ===================================== -->
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-capsules mr-2"></i>
                            Información General
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- CODIGO -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Código
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-barcode"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" v-model="medicamentoSeleccionado.codigo">
                                    </div>
                                </div>
                            </div>
                            <!-- NOMBRE -->
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Nombre Comercial
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-pills"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" v-model="medicamentoSeleccionado.nombre">
                                    </div>
                                </div>
                            </div>
                            <!-- ESTADO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Estado
                                    </label>
                                    <select class="form-control" v-model="medicamentoSeleccionado.activo">
                                        <option :value="1">
                                            Activo
                                        </option>
                                        <option :value="0">
                                            Inactivo
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- NOMBRE GENERICO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Nombre Genérico
                                    </label>
                                    <input type="text" class="form-control" v-model="medicamentoSeleccionado.nombre_generico">
                                </div>
                            </div>

                            <!-- CONCENTRACION -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Concentración
                                    </label>
                                    <input type="text" class="form-control" v-model="medicamentoSeleccionado.concentracion">
                                </div>
                            </div>
                            <!-- VIA -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Vía Administración
                                    </label>
                                    <select class="form-control"
                                        v-model="medicamentoSeleccionado.via_administracion">
                                        <option value="Oral">Oral</option>
                                        <option value="Intravenosa">Intravenosa</option>
                                        <option value="Intramuscular">Intramuscular</option>
                                        <option value="Subcutánea">Subcutánea</option>
                                        <option value="Tópica">Tópica</option>
                                    </select>
                                </div>
                            </div>
                            <!-- PRESENTACION -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Presentación
                                    </label>
                                    <input type="text"
                                        class="form-control" v-model="medicamentoSeleccionado.presentacion">
                                </div>
                            </div>

                            <!-- RECETA -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Requiere Receta
                                    </label>
                                    <select class="form-control"
                                        v-model="medicamentoSeleccionado.requiere_receta">
                                        <option :value="1">Sí</option>
                                        <option :value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION MEDICA -->
                <!-- ===================================== -->
                <div class="card card-outline card-danger mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-notes-medical mr-2"></i>
                            Información Médica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Descripción Farmacológica
                            </label>
                            <textarea rows="3" class="form-control" v-model="medicamentoSeleccionado.descripcion"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Indicaciones
                            </label>
                            <textarea rows="3" class="form-control" v-model="medicamentoSeleccionado.indicaciones"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Contraindicaciones
                            </label>
                            <textarea rows="3" class="form-control" v-model="medicamentoSeleccionado.contraindicaciones"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">
                                Efectos Secundarios
                            </label>
                            <textarea rows="3" class="form-control" v-model="medicamentoSeleccionado.efectos_secundarios"></textarea>
                        </div>
                    </div>
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION ECONOMICA -->
                <!-- ===================================== -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-dollar-sign mr-2"></i>
                            Información Económica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- PRECIO -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Precio
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                $
                                            </span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" v-model="medicamentoSeleccionado.precio">
                                    </div>
                                </div>
                            </div>
                            <!-- STOCK MINIMO -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Stock Mínimo
                                    </label>
                                    <input type="number" class="form-control" v-model="medicamentoSeleccionado.stock_minimo">
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
                    Cancelar
                </button>
                <button class="btn btn-primary" @click="actualizarMedicamento">
                    <i class="fas fa-save mr-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>
<!--Modal para el movimiento del Inventario-->
<!-- ===================================== -->
<!-- MODAL MOVIMIENTO INVENTARIO -->
<!-- ===================================== -->

<div class="modal fade" id="modalMovimientoInventario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="modal-header text-white border-0" style="background:linear-gradient(135deg,#ffc107,#ffc107);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex justify-content-center align-items-center"
                        style="
                            width:55px;
                            height:55px;
                            border-radius:50%;
                            background:rgba(255,255,255,.15);">
                        <i class="fas fa-boxes fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold">
                            Movmiento Inventario
                        </h4>
                        <small class="text-light">
                            Registro farmacéutico
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
                <div class="alert alert-light border-left border-primary shadow-sm">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Capture correctamente la información del movimiento de inventario.
                </div>
                <div class="row">
                    <!-- MEDICAMENTO -->
                    <div class="col-12">
                        <h5 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-pills mr-2"></i>
                            Información del Medicamento
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">
                            Medicamento
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-capsules"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" :value="medicamentoSeleccionado?.nombre" disabled>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">
                            Stock Actual 
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-boxes"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" :value="medicamentoSeleccionado?.stock" disabled>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="font-weight-bold">
                            Stock Mínimo
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" :value="medicamentoSeleccionado?.stock_minimo" disabled>
                        </div>
                    </div>
                    <!-- MOVIMIENTO -->
                    <div class="col-12 mt-4">
                        <h5 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-random mr-2"></i>
                            Datos del Movimiento
                        </h5>
                    </div>
                    <div class="col-md-3">
                        <label class="font-weight-bold">
                            Tipo Movimiento
                        </label>
                        <select
                            class="form-control"
                            v-model="movimiento.tipo_movimiento">
                            <option value="entrada">
                                Entrada
                            </option>
                            <option value="salida">
                                Salida
                            </option>
                            <option value="ajuste">
                                Ajuste
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="font-weight-bold">
                            Cantidad
                        </label>
                        <input type="number" class="form-control" v-model="movimiento.cantidad">
                    </div>

                    <div class="col-md-6">
                        <label class="font-weight-bold">
                            Motivo Movimiento
                        </label>

                        <input type="text" class="form-control" v-model="movimiento.motivo_movimiento">
                    </div>

                    <!-- LOTE -->
                    <div class="col-12 mt-4">
                        <h5 class="text-warning border-bottom pb-2 mb-3">
                            <i class="fas fa-barcode mr-2"></i>
                            Información del Lote
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">
                            Lote
                        </label>
                        <input type="text" class="form-control" v-model="movimiento.lote">
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">
                            Fecha Caducidad
                        </label>
                        <input type="date" class="form-control" v-model="movimiento.fecha_caducidad">
                    </div>

                    <div class="col-md-4">
                        <label class="font-weight-bold">
                            Ubicación
                        </label>
                        <input type="text" class="form-control" v-model="movimiento.ubicacion">
                    </div>
                    <!-- FINANCIERA -->
                    <div class="col-12 mt-4">
                        <h5 class="text-info border-bottom pb-2 mb-3">
                            <i class="fas fa-dollar-sign mr-2"></i>
                            Información Financiera
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">
                            Proveedor
                        </label>
                        <input type="text" class="form-control" v-model="movimiento.proveedor">
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">
                            Costo Unitario
                        </label>
                        <input type="number" class="form-control" v-model="movimiento.costo_unitario">
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="font-weight-bold">
                            Referencia Documento
                        </label>
                        <input type="text" class="form-control" v-model="movimiento.referencia_documento">
                    </div>
                    <!-- OBSERVACIONES -->
                    <div class="col-12 mt-4">
                        <h5 class="text-secondary border-bottom pb-2 mb-3">
                            <i class="fas fa-notes-medical mr-2"></i>
                            Observaciones
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <textarea rows="4" class="form-control" v-model="movimiento.observaciones">
                        </textarea>
                    </div>
                </div>
            </div>
            <!-- ===================================== -->
            <!-- FOOTER -->
            <!-- ===================================== -->
            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button
                    class="btn btn-success">
                    <i class="fas fa-save mr-1"></i>
                    Guardar Movimiento
                </button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import ApiService from '../../services/ApiService.js'
export default {
    props: {
        medicamento: {
            type: Object,
            default: () => ({})
        },
        // NUEVO: array de medicamentos que ahora administra el componente padre
        // (MedicamentosIndex.vue), única fuente de la verdad compartida con KPI_CARDS.
        medicamentos: {
            type: Array,
            default: () => []
        }
    },

    emits: ['actualizar-inventario'],

   data(){
        return{
            medicamentoDetalle:{
                inventario:{},
                ultimo_movimiento:[]
            },
            stockActual:0,
            movimiento:{
                    // Medicamento
                    medicamento_id:'',
                    // Movimiento
                    tipo_movimiento:'entrada',
                    cantidad:0,
                    fecha_movimiento:'',
                    // Inventario
                    stock_minimo:0,
                    ubicacion:'',
                    // Lote
                    lote:'',
                    fecha_caducidad:'',
                    // Compra
                    proveedor:'',
                    costo_unitario:0,
                    // Control
                    motivo_movimiento:'',
                    referencia_documento:'',
                    observaciones:''
                },
            medicamentoSeleccionado:{}
        }
    },

    watch: {
        medicamento: {
            immediate: true,
            handler(nuevo){
                if(nuevo){
                    this.movimiento.medicamento = nuevo.nombre || ''
                }
            }
        }
    },

    methods: {
        //Metodo para obtner el detalle de un solo medicamento//
        async verMedicamento(id){
            console.log('ID del medicamento:',id)
            try{
                const response = await ApiService.get('/medicamentos/' + id)
                this.medicamentoDetalle = response.data
                console.log('Medicamento Detalle',this.medicamentoDetalle)
            }catch(error){
                console.error(error)
            }
        },
        //Aqui termina el metodo para obtner el detalle de un solo medicamento//
        guardarMovimiento(){
            console.log(this.movimiento)
            $('#modalMovimientoInventario').modal('hide')
            // Avisa al padre para que recargue medicamentos y así se
            // actualicen tanto la tabla como las tarjetas KPI juntas
            this.$emit('actualizar-inventario')
        },
        ///////////////////////////////////////////////////////////////////////
         //Función para hacer la busqueda de un medicamento mediante su ID dentro de la tabla de inventariod//
         seleccionarMedicamento(){
                const medicamentoSeleccionado = this.medicamentos.find(
                    medicamento => medicamento.id == this.movimiento.medicamento_id
                )
                console.log('Medicamentos del Inventario',medicamentoSeleccionado)
                if(medicamentoSeleccionado && medicamentoSeleccionado.inventario){
                    this.stockActual = medicamentoSeleccionado.inventario.stock_actual
                    this.movimiento.stock_minimo = medicamentoSeleccionado.inventario.stock_minimo
                    this.movimiento.ubicacion = medicamentoSeleccionado.inventario.ubicacion
                }
            }
    }
}
</script>