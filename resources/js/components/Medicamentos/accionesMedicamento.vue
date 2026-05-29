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
                    <button class="btn btn-success" data-toggle="modal" data-target="#modalEntradaInventario">
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
<!-- ===================================== -->
<!-- MODAL NUEVO MEDICAMENTO -->
<!-- ===================================== -->
<div class="modal fade" id="modalMedicamento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#007bff,#0056b3);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 d-flex justify-content-center align-items-center"
                        style="
                            width:50px;
                            height:50px;
                            border-radius:50%;
                            background:rgba(255,255,255,.15);">

                        <i class="fas fa-capsules fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold">
                            Nuevo Medicamento
                        </h4>
                        <small>
                            Registro farmacéutico completo
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
                    Capture toda la información farmacológica y clínica del medicamento.
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION GENERAL -->
                <!-- ===================================== -->
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-pills mr-2"></i>
                            Información General
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- CODIGO -->
                            <div class="col-md-3">
                                <label class="font-weight-bold">
                                    Código
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="MED-001">
                                </div>
                            </div>
                            <!-- NOMBRE -->
                            <div class="col-md-4 mt-3">
                                <label class="font-weight-bold">
                                    Nombre Comercial
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-capsules"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Paracetamol 500mg">
                                </div>
                            </div>
                              <div class="col-md-4 mt-3">
                                <label class="font-weight-bold">
                                    Stock mínimo
                                </label>

                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-layer-group"></i>
                                        </span>
                                    </div>

                                    <input type="number" class="form-control">
                                </div>
                            </div>
                            <!-- GENERICO -->
                            <div class="col-md-6 mt-3">
                                <label class="font-weight-bold">
                                    Nombre Genérico
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-file-medical"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <!-- PRESENTACION -->
                            <div class="col-md-3 mt-3">
                                <label class="font-weight-bold">
                                    Presentación
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-box-open"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Caja x 20 tabletas">
                                </div>
                            </div>
                            <!-- CONCENTRACION -->
                            <div class="col-md-3 mt-3">
                                <label class="font-weight-bold">
                                    Concentración
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-flask"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="500 mg">
                                </div>
                            </div>
                            <!-- VIA -->
                            <div class="col-md-4 mt-3">
                                <label class="font-weight-bold">
                                    Vía Administración
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-syringe"></i>
                                        </span>
                                    </div>
                                    <select class="form-control">
                                        <option>Oral</option>
                                        <option>Intravenosa</option>
                                        <option>Intramuscular</option>
                                        <option>Tópica</option>
                                        <option>Subcutánea</option>
                                    </select>
                                </div>
                            </div>
                            <!-- PRECIO -->
                            <div class="col-md-4 mt-3">
                                <label class="font-weight-bold">
                                    Precio
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="number" class="form-control">
                                </div>
                            </div>
                          
                            <!-- RECETA -->
                            <div class="col-md-4 mt-3">
                                <label class="font-weight-bold">
                                    Requiere Receta
                                </label>
                                <select class="form-control">
                                    <option>Sí</option>
                                    <option>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION FARMACOLOGICA -->
                <!-- ===================================== -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-notes-medical mr-2"></i>
                            Información Farmacológica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Descripción
                            </label>
                            <textarea rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Indicaciones
                            </label>
                            <textarea rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Contraindicaciones
                            </label>
                            <textarea rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">
                                Efectos Secundarios
                            </label>
                            <textarea rows="3" class="form-control"></textarea>
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
                <button class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar Medicamento
                </button>
            </div>
        </div>
    </div>
</div>
<!--Modal para el inventario del medicamento-->
<!-- ===================================== -->
<!-- MODAL ENTRADA INVENTARIO -->
<!-- ===================================== -->
<div class="modal fade" id="modalEntradaInventario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-lg">

            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="modal-header text-white border-0"
                style="background:linear-gradient(135deg,#28a745,#1e7e34);">

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
                            Entrada de Inventario
                        </h4>

                        <small class="text-light">
                            Registro de ingreso farmacéutico
                        </small>
                    </div>
                </div>

                <button type="button"
                    class="close text-white"
                    data-dismiss="modal">

                    <span>&times;</span>
                </button>
            </div>

            <!-- ===================================== -->
            <!-- BODY -->
            <!-- ===================================== -->
            <div class="modal-body p-4 bg-light">

                <!-- ALERTA -->
                <div class="alert alert-light border-left border-success shadow-sm">
                    <i class="fas fa-info-circle text-success mr-2"></i>

                    Capture correctamente la información del lote ingresado
                    al inventario farmacéutico.
                </div>

                <!-- ===================================== -->
                <!-- INFORMACION MEDICAMENTO -->
                <!-- ===================================== -->
                <div class="card card-outline card-success shadow-sm mb-4">

                    <div class="card-header">
                        <h5 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-capsules mr-2"></i>
                            Información del Medicamento
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <!-- MEDICAMENTO -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Medicamento
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-pills text-success"></i>
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

                            <!-- STOCK ACTUAL -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Stock Actual
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-warehouse text-primary"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control bg-white"
                                            value="120"
                                            disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- STOCK MINIMO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Stock Mínimo
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-exclamation-triangle text-danger"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control bg-white"
                                            value="30"
                                            disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- ESTADO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Estado Inventario
                                    </label>

                                    <div>
                                        <span class="badge badge-success px-3 py-2 mt-2">
                                            Stock Disponible
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- DATOS DEL MOVIMIENTO -->
                <!-- ===================================== -->
                <div class="card card-outline card-primary shadow-sm mb-4">

                    <div class="card-header">
                        <h5 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-random mr-2"></i>
                            Movimiento de Inventario
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <!-- TIPO MOVIMIENTO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Tipo Movimiento
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-exchange-alt text-primary"></i>
                                            </span>
                                        </div>

                                        <select class="form-control">
                                            <option>Entrada</option>
                                            <option>Salida</option>
                                            <option>Ajuste</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- CANTIDAD -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Cantidad
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-sort-numeric-up text-success"></i>
                                            </span>
                                        </div>

                                        <input type="number"
                                            class="form-control"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>

                            <!-- FECHA -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Fecha Movimiento
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-calendar-alt text-info"></i>
                                            </span>
                                        </div>

                                        <input type="datetime-local"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- MOTIVO -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Motivo Movimiento
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-comment-medical text-warning"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control"
                                            placeholder="Compra, ajuste, devolución, etc.">
                                    </div>
                                </div>
                            </div>

                            <!-- REFERENCIA -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Documento Referencia
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-file-invoice text-secondary"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control"
                                            placeholder="Factura / Folio / OC">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- INFORMACION DEL LOTE -->
                <!-- ===================================== -->
                <div class="card card-outline card-warning shadow-sm mb-4">

                    <div class="card-header">
                        <h5 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-barcode mr-2"></i>
                            Información del Lote
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <!-- LOTE -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Lote
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-barcode text-dark"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control"
                                            placeholder="LOT-2026-001">
                                    </div>
                                </div>
                            </div>

                            <!-- CADUCIDAD -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Fecha Caducidad
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-calendar-times text-danger"></i>
                                            </span>
                                        </div>

                                        <input type="date"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- UBICACION -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Ubicación
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-map-marker-alt text-primary"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control"
                                            placeholder="Estante A-2">
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
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-truck text-success"></i>
                                            </span>
                                        </div>

                                        <input type="text"
                                            class="form-control"
                                            placeholder="Proveedor farmacéutico">
                                    </div>
                                </div>
                            </div>

                            <!-- COSTO -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Costo Unitario
                                    </label>

                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                $
                                            </span>
                                        </div>

                                        <input type="number"
                                            class="form-control"
                                            placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ===================================== -->
                <!-- OBSERVACIONES -->
                <!-- ===================================== -->
                <div class="card card-outline card-secondary shadow-sm">

                    <div class="card-header">
                        <h5 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-notes-medical mr-2"></i>
                            Observaciones
                        </h5>
                    </div>

                    <div class="card-body">
                        <textarea rows="4"
                            class="form-control"
                            placeholder="Observaciones farmacéuticas del movimiento..."></textarea>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- FOOTER -->
            <!-- ===================================== -->
            <div class="modal-footer bg-white border-top">

                <button class="btn btn-secondary px-4"
                    data-dismiss="modal">

                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>

                <button class="btn btn-success px-4">
                    <i class="fas fa-save mr-1"></i>
                    Guardar Movimiento
                </button>
            </div>
        </div>
    </div>
</div>
</template>
