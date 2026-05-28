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
                                <th>Caducidad</th>
                                <th width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
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
                                <td>
                                    <span class="text-warning fw-bold">
                                        12/10/2026
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetalleMedicamento">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalEditarMedicamento">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning text-dark" data-toggle="modal" data-target="#modalMovimientoInventario">
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

<!-- ============================================= -->
<!-- MODAL DETALLE MEDICAMENTO -->
<!-- ============================================= -->
<div class="modal fade" id="modalDetalleMedicamento" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg,#17a2b8,#138496);">
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
                    Información detallada del medicamento registrado en el sistema.
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
                            <!-- CODIGO -->
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Código
                                </label>
                                <div class="h6">
                                    MED-001
                                </div>
                            </div>

                            <!-- NOMBRE COMERCIAL -->
                            <div class="col-md-5 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Nombre Comercial
                                </label>
                                <div class="h5 font-weight-bold">
                                    Paracetamol 500mg
                                </div>
                            </div>
                            <!-- ESTADO -->

                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Estado
                                </label>
                                <div>
                                    <span class="badge badge-success px-3 py-2">
                                        Activo
                                    </span>
                                </div>
                            </div>

                            <!-- NOMBRE GENERICO -->
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Nombre Genérico
                                </label>
                                <div>
                                    Paracetamol
                                </div>
                            </div>
                            <!-- CONCENTRACION -->
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Concentración
                                </label>
                                <div>
                                    500 mg
                                </div>
                            </div>

                            <!-- VIA ADMINISTRACION -->
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Vía de Administración
                                </label>
                                <div>
                                    Oral
                                </div>
                            </div>

                            <!-- PRESENTACION -->
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Presentación
                                </label>
                                <div>
                                    Caja con 20 tabletas
                                </div>
                            </div>

                            <!-- REQUIERE RECETA -->
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Requiere Receta
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

                <!-- ===================================== -->
                <!-- INFORMACION MEDICA -->
                <!-- ===================================== -->

                <div class="card card-outline card-danger mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-notes-medical mr-1"></i>
                            Información Médica
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- DESCRIPCION -->
                            <div class="col-md-12 mb-4">
                                <label class="font-weight-bold text-muted">
                                    Descripción Farmacológica
                                </label>
                                <div class="border rounded p-3 bg-light">
                                    Analgésico y antipirético utilizado para el tratamiento del dolor leve a moderado y control de fiebre.
                                </div>
                            </div>

                            <!-- CONTRAINDICACIONES -->
                            <div class="col-md-12">
                                <label class="font-weight-bold text-muted">
                                    Contraindicaciones
                                </label>
                                <div class="border rounded p-3 bg-light">
                                    No administrar en pacientes con insuficiencia hepática severa o alergia al paracetamol.
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
                            <!-- PRECIO -->
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Precio
                                </label>
                                <div class="h4 text-primary">
                                    $85.00
                                </div>
                            </div>
                            <!-- PROVEEDOR -->
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Proveedor
                                </label>
                                <div>
                                    Farmacéutica Mérida
                                </div>
                            </div>

                            <!-- FECHA REGISTRO -->
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Fecha Registro
                                </label>
                                <div>
                                    28/05/2026
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
            <div class="modal-header text-white" style="background: linear-gradient(135deg,#007bff,#0056b3);">
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
                <!-- ALERTA -->
                <div class="alert alert-light border-left border-primary mb-4">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Modifique la información del medicamento según sea necesario.
                </div>
                <!-- ===================================== -->
                <!-- INFORMACION GENERAL -->
                <!-- ===================================== -->
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-capsules mr-1"></i>
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
                                        <input type="text" class="form-control"
                                            v-model="medicamentoSeleccionado.codigo">
                                    </div>
                                </div>
                            </div>
                            <!-- NOMBRE COMERCIAL -->
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

                            <!-- VIA ADMINISTRACION -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Vía de Administración
                                    </label>
                                    <select
                                        class="form-control" v-model="medicamentoSeleccionado.via_administracion">
                                        <option value="Oral">
                                            Oral
                                        </option>
                                        <option value="Intravenosa">
                                            Intravenosa
                                        </option>
                                        <option value="Intramuscular">
                                            Intramuscular
                                        </option>
                                        <option value="Subcutánea">
                                            Subcutánea
                                        </option>
                                        <option value="Tópica">
                                            Tópica
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- PRESENTACION -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Presentación
                                    </label>
                                    <input type="text" class="form-control" v-model="medicamentoSeleccionado.presentacion">
                                </div>
                            </div>
                            <!-- REQUIERE RECETA -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Requiere Receta
                                    </label>
                                    <select
                                        class="form-control" v-model="medicamentoSeleccionado.requiere_receta">
                                        <option :value="1">
                                            Sí
                                        </option>
                                        <option :value="0">
                                            No
                                        </option>
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
                            <i class="fas fa-notes-medical mr-1"></i>
                            Información Médica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- DESCRIPCION -->
                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Descripción Farmacológica
                                    </label>
                                    <textarea rows="4" class="form-control" v-model="medicamentoSeleccionado.descripcion">
                                    </textarea>
                                </div>
                            </div>

                            <!-- CONTRAINDICACIONES -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Contraindicaciones
                                    </label>
                                    <textarea rows="4" class="form-control" v-model="medicamentoSeleccionado.contraindicaciones">
                                    </textarea>
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
                            <!-- PRECIO -->
                            <div class="col-md-4">
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
                                        <input type="number" class="form-control"
                                            v-model="medicamentoSeleccionado.precio">
                                    </div>
                                </div>
                            </div>
                            <!-- PROVEEDOR -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Proveedor
                                    </label>
                                    <input type="text" class="form-control" v-model="medicamentoSeleccionado.proveedor">
                                </div>
                            </div>

                            <!-- STOCK MINIMO -->
                            <div class="col-md-4">
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
                <button class="btn btn-primary"
                    @click="actualizarMedicamento">
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

<div
    class="modal fade" id="modalMovimientoInventario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <!-- HEADER -->
            <div
                class="modal-header text-white"
                :class="{
                    'bg-success': tipoMovimiento === 'entrada',
                    'bg-danger': tipoMovimiento === 'salida',
                    'bg-primary': tipoMovimiento === 'ajuste'}">

                <div class="d-flex align-items-center">
                    <div
                        class="mr-3 d-flex justify-content-center align-items-center" style=" width:50px; height:50px; border-radius:50%; background:rgba(255,255,255,.15);">
                        <i
                            class="fas"
                            :class="{
                                'fa-plus': tipoMovimiento === 'entrada',
                                'fa-minus': tipoMovimiento === 'salida',
                                'fa-random': tipoMovimiento === 'ajuste'
                            }"
                        ></i>
                    </div>
                    <div>
                        <h4 class="mb-0 font-weight-bold">
                            {{
                                tipoMovimiento === 'entrada'
                                ? 'Entrada de Inventario'
                                : tipoMovimiento === 'salida'
                                    ? 'Salida de Inventario'
                                    : 'Ajuste de Inventario'
                            }}

                        </h4>
                        <small>
                            Movimiento farmacéutico
                        </small>
                    </div>
                </div>

                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <!-- BODY -->

            <div class="modal-body p-4">
                <!-- ALERTA -->
                <div
                    class="alert"
                    :class="{
                        'alert-success': tipoMovimiento === 'entrada',
                        'alert-danger': tipoMovimiento === 'salida',
                        'alert-primary': tipoMovimiento === 'ajuste'
                    }">
                    <i class="fas fa-info-circle mr-2"></i>
                    Registro de movimiento del inventario farmacéutico.
                </div>
                <!-- INFORMACION MEDICAMENTO -->
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-pills mr-1"></i>
                            Medicamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Nombre
                                    </label>
                                    <input type="text" class="form-control" :value="medicamentoSeleccionado.nombre" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Stock actual
                                    </label>
                                    <input type="text" class="form-control" :value="medicamentoSeleccionado.stock" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DATOS MOVIMIENTO -->
                <div class="card card-outline card-success mb-4">
                    <div class="card-header">
                        <h5 class="card-title font-weight-bold">
                            <i class="fas fa-boxes mr-1"></i>
                            Movimiento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- CANTIDAD -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Cantidad
                                    </label>
                                    <input type="number" class="form-control" v-model="movimiento.cantidad">
                                </div>
                            </div>

                            <!-- LOTE -->

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Lote
                                    </label>
                                    <input type="text" class="form-control" v-model="movimiento.lote">
                                </div>
                            </div>

                            <!-- CADUCIDAD -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Caducidad
                                    </label>
                                    <input type="date" class="form-control" v-model="movimiento.caducidad">
                                </div>
                            </div>

                            <!-- PROVEEDOR -->

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Proveedor
                                    </label>
                                    <input type="text" class="form-control" v-model="movimiento.proveedor">
                                </div>
                            </div>

                            <!-- OBSERVACIONES -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Observaciones
                                    </label>
                                    <textarea rows="4" class="form-control" v-model="movimiento.observaciones"></textarea>
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
                    Cancelar
                </button>
                <button class="btn" :class="{
                        'btn-success': tipoMovimiento === 'entrada',
                        'btn-danger': tipoMovimiento === 'salida',
                        'btn-primary': tipoMovimiento === 'ajuste'}">
                    <i class="fas fa-save mr-1"></i>
                    Guardar Movimiento
                </button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {

    props: {
        medicamento: {
            type: Object,
            default: () => ({})
        }
    },

   data(){
        return{
            movimiento:{
                medicamento:'',
                tipo:'entrada',
                cantidad:'',
                lote:'',
                caducidad:'',
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

        guardarMovimiento(){

            console.log(this.movimiento)

            $('#modalMovimientoInventario').modal('hide')

        }

    }

}
</script>
