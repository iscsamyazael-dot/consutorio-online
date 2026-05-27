<template>
   <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h3 class="mb-0">
                         <i class="fas fa-pills text-primary"></i>
                            Medicamentos e Inventario
                    </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">Inicio</li>
                            <li class="breadcrumb-item active">Medicamentos</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalMedicamento">
                        <i class="fas fa-plus-circle"></i>
                            Nuevo Medicamento
                    </button>
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalInventario">
                         <i class="fas fa-boxes"></i>
                            Entrada Inventario
                    </button>
                    <button class="btn btn-warning text-dark"  @click="$emit('cambiarVista', 'alertas')">
                        <i class="fas fa-exclamation-triangle"></i>
                            Alertas
                    </button>
                    <button class="btn btn-primary"  @click="$emit('cambiarVista', 'inventario')">
                        <i class="fas fa-warehouse"></i>
                            Inventario
                    </button>
                </div>
            </div>
        </div>
    </section>

<!--Modal para registrar un médicamento-->
<div class="modal fade" id="modalMedicamento">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="modal-header bg-primary py-2">
                <h5 class="modal-title">
                    <i class="fas fa-capsules mr-2"></i>
                    Registro de Medicamento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"> <span>&times;</span>
                </button>
            </div>

            <!-- ===================================== -->
            <!-- BODY -->
            <!-- ===================================== -->

            <div class="modal-body p-3">
                <!-- ALERTA SUPERIOR -->
                <div class="alert alert-light border mb-3 py-2">
                    <i class="fas fa-info-circle text-primary mr-1"></i>
                    Complete la información farmacéutica del medicamento.
                </div>
                <div class="row">
                    <!-- CODIGO -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-barcode text-primary mr-1"></i>
                                Código
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" placeholder="MED-001">
                            </div>
                        </div>
                    </div>

                    <!-- NOMBRE -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-pills text-primary mr-1"></i>
                                Nombre del medicamento
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-capsules"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" placeholder="Paracetamol 500mg">
                            </div>
                        </div>
                    </div>

                    <!-- PRESENTACION -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-box-open text-primary mr-1"></i>
                                Presentación
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-box"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" placeholder="Caja x 20 tabletas">
                            </div>
                        </div>
                    </div>

                    <!-- PRECIO -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-dollar-sign text-success mr-1"></i>
                                Precio
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        $
                                    </span>
                                </div>
                                <input type="number" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- RECETA -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-file-medical text-danger mr-1"></i>
                                Requiere receta
                            </label>
                            <select
                                class="form-control form-control-sm">
                                <option value="1">
                                    Sí
                                </option>
                                <option value="0">
                                    No
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- ESTADO -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-toggle-on text-success mr-1"></i>
                                Estado
                            </label>
                            <select
                                class="form-control form-control-sm">
                                <option value="1">
                                    Activo
                                </option>
                                <option value="0">
                                    Inactivo
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- DESCRIPCION -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-notes-medical text-primary mr-1"></i>
                                Descripción farmacológica
                            </label>
                            <textarea
                                rows="3"
                                class="form-control form-control-sm"
                                placeholder="Información farmacológica, indicaciones, observaciones clínicas..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- FOOTER -->
            <!-- ===================================== -->

            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-save mr-1"></i>
                    Guardar medicamento
                </button>
            </div>
        </div>
    </div>
</div>
<!--Modal para el inventario del medicamento-->
<div class="modal fade" id="modalInventario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div
                class="modal-header text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #007bff,
                        #0056b3
                    );">

                <div class="d-flex align-items-center">
                    <div
                        class="d-flex align-items-center justify-content-center mr-3"
                        style="
                            width:45px;
                            height:45px;
                            border-radius:50%;
                            background:rgba(255,255,255,.15);">
                        <i class="fas fa-boxes fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0">
                            Entrada de Inventario
                        </h5>
                        <small class="opacity-75">
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
                <div
                    class="alert alert-light border-left border-primary shadow-sm">
                    <i
                        class="fas fa-info-circle text-primary mr-2"></i>
                    Complete correctamente la entrada
                    del medicamento al inventario.
                </div>

                <!-- ===================================== -->
                <!-- INFORMACION MEDICAMENTO -->
                <!-- ===================================== -->

                <div class="mb-4">
                    <h6
                        class="font-weight-bold text-primary border-bottom pb-2">
                        <i class="fas fa-capsules mr-1"></i>
                        Información del Medicamento
                    </h6>
                    <div class="row">
                        <!-- MEDICAMENTO -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Medicamento
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-pills"></i>
                                        </span>
                                    </div>
                                    <select class="form-control">
                                        <option>
                                            Seleccionar medicamento
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- MOVIMIENTO -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Tipo movimiento
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-random"></i>
                                        </span>
                                    </div>
                                    <select class="form-control">
                                        <option>
                                            Entrada
                                        </option>
                                        <option>
                                            Ajuste
                                        </option>
                                        <option>
                                            Devolución
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- DETALLES INVENTARIO -->
                <!-- ===================================== -->

                <div class="mb-4">
                    <h6
                        class="font-weight-bold text-primary border-bottom pb-2">
                        <i class="fas fa-warehouse mr-1"></i>
                        Detalles del Inventario
                    </h6>
                    <div class="row">
                        <!-- CANTIDAD -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Cantidad
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-sort-numeric-up"></i>
                                        </span>
                                    </div>
                                    <input type="number" class="form-control" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <!-- LOTE -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Lote
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="LOT-2026-01">
                                </div>
                            </div>
                        </div>

                        <!-- CADUCIDAD -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Caducidad
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-times"></i>
                                        </span>
                                    </div>
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- COSTO -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Costo Unitario
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$  
                                        </span>
                                    </div>
                                    <input type="number" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- UBICACION -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Ubicación
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Estante A-2">
                                </div>
                            </div>
                        </div>

                        <!-- PROVEEDOR -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Proveedor
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-truck"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Proveedor farmacéutico">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- OBSERVACIONES -->
                <!-- ===================================== -->

                <div class="form-group">
                    <label class="font-weight-bold">
                        <i class="fas fa-notes-medical text-primary mr-1"></i>
                        Observaciones
                    </label>
                    <textarea rows="3" class="form-control" placeholder="Notas farmacéuticas..."></textarea>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- FOOTER -->
            <!-- ===================================== -->

            <div class="modal-footer bg-light d-flex justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-shield-alt text-success mr-1"></i>
                    Registro farmacéutico seguro
                </small>
                <div>
                    <button class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Guardar Entrada
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
