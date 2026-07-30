<template>
    <section class="content">
        <div class="container-fluid">

            <!-- ===================================== -->
            <!-- MENSAJE / TOAST DE FEEDBACK -->
            <!-- ===================================== -->
            <div v-if="mensaje"
                 class="alert alert-dismissible fade show shadow position-fixed"
                 :class="'alert-' + mensaje.tipo"
                 style="top:20px; right:20px; z-index:9999; min-width:300px;">
                {{ mensaje.texto }}
                <button type="button" class="close" @click="mensaje = null">
                    <span>&times;</span>
                </button>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">
                                Buscar medicamento
                            </label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Nombre, código o lote"
                                   v-model="filtros.busqueda">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">
                                Estado
                            </label>
                            <select class="form-select" v-model="filtros.estado">
                                <option value="Todos">Todos</option>
                                <option value="Disponible">Disponible</option>
                                <option value="Bajo Stock">Bajo Stock</option>
                                <option value="Crítico">Crítico</option>
                                <option value="Próximo a Caducar">Próximo a Caducar</option>
                                <option value="Caducado">Caducado</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">
                                Categoría
                            </label>
                            <select class="form-select" v-model="filtros.categoria">
                                <option value="Todas">Todas</option>
                                <option value="Analgésicos">Analgésicos</option>
                                <option value="Antibióticos">Antibióticos</option>
                                <option value="Antiinflamatorios">Antiinflamatorios</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">
                                Caducidad
                            </label>
                            <select class="form-select" v-model="filtros.caducidad">
                                <option value="Todas">Todas</option>
                                <option value="Próximo vencer">Próximo vencer</option>
                                <option value="Vigente">Vigente</option>
                                <option value="Caducado">Caducado</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end mb-3">
                            <button class="btn btn-primary w-100" @click="limpiarFiltros">
                                <i class="fas fa-eraser"></i>
                                Limpiar filtros
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
                            <span class="badge badge-light text-muted ml-2">
                                {{ medicamentosFiltrados.length }} resultado(s)
                            </span>
                        </h5>
                        <button class="btn btn-outline-primary btn-sm" @click="exportarCSV">
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
            <tr v-if="medicamentosFiltrados.length === 0">
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-search mr-2"></i>
                    No se encontraron medicamentos con esos filtros.
                </td>
            </tr>

            <tr v-for="medica in medicamentosFiltrados" :key="medica.id">
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
                        {{ medica.inventario?.stock_actual ?? 0 }}
                    </span>
                </td>

                <!-- STOCK MINIMO -->
                <td>
                    <span class="badge badge-warning px-3 py-2">
                        {{ medica.inventario?.stock_minimo ?? 0 }}
                    </span>
                </td>

                <!-- CADUCIDAD -->
                <!-- FIX: antes leía medica.ultimo_movimiento.fecha_caducidad.
                     Esa relación es independiente del inventario y puede no
                     traer la fecha del lote vigente (p.ej. si el último
                     movimiento fue una salida). Ahora usa siempre
                     medica.inventario.fecha_caducidad, la misma fuente que
                     usa el backend (MedicamentoController@resumen) y el
                     componente de Alertas Críticas. -->
                <td>
                    <span
                        v-if="medica.inventario?.fecha_caducidad"
                        :class="{
                            'text-danger': estaCaducado(medica),
                            'text-warning': !estaCaducado(medica) && proximoACaducar(medica)
                        }"
                        class="font-weight-bold">
                        {{ medica.inventario.fecha_caducidad }}
                    </span>
                    <span
                        v-else
                        class="text-muted font-weight-bold">
                        Sin fecha de caducidad
                    </span>
                </td>

                <!-- ESTADO (estado del inventario, coincide con el filtro de arriba) -->
                <td>
                    <span
                        class="badge px-3 py-2"
                        :class="'badge-' + colorEstadoStock(estadoStock(medica))">
                        {{ estadoStock(medica) }}
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
                            title="Editar Medicamento"
                            @click="editarMedicamento(medica)">
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- MOVIMIENTO -->
                        <button
                            class="btn btn-sm btn-warning text-dark"
                            data-toggle="modal"
                            data-target="#modalMovimientoInventario"
                            title="Movimiento Inventario"
                            @click="abrirMovimiento(medica)">
                            <i class="fas fa-exchange-alt"></i>
                        </button>

                        <!-- ELIMINAR -->
                        <button
                            class="btn btn-sm btn-danger"
                            title="Eliminar"
                            :disabled="eliminandoId === medica.id"
                            @click="eliminarMedicamento(medica)">
                            <i class="fas fa-spinner fa-spin" v-if="eliminandoId === medica.id"></i>
                            <i class="fas fa-trash" v-else></i>
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
                                    <span v-if="medicamentoDetalle.requiere_receta == 1"
                                         class="badge badge-danger px-3 py-2">
                                         Sí
                                    </span>
                                    <span v-else
                                       class="badge badge-success px-3 py-2">
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
                                    {{ medicamentoDetalle.inventario?.stock_actual ?? 0 }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Stock Mínimo
                                </label>
                                <div class="h4 text-danger font-weight-bold">
                                    {{ medicamentoDetalle.inventario?.stock_minimo ?? 0 }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-muted">
                                    Estado Inventario
                                </label>
                                <div>
                                    <span
                                        class="badge px-3 py-2"
                                        :class="'badge-' + colorEstadoStock(estadoStock(medicamentoDetalle))">
                                        {{ estadoStock(medicamentoDetalle) }}
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
                                    {{ medicamentoDetalle.created_at ? new Date(medicamentoDetalle.created_at).toLocaleDateString() : '—' }}
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
                <button
                    class="btn btn-primary"
                    data-dismiss="modal"
                    data-toggle="modal"
                    data-target="#modalEditarMedicamento"
                    @click="editarMedicamento(medicamentoDetalle)">
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
                                    <input type="number" class="form-control" v-model="medicamentoSeleccionado.inventario.stock_minimo">
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
                <button
                    class="btn btn-primary"
                    :disabled="guardandoEdicion"
                    @click="actualizarMedicamento">
                    <i class="fas fa-spinner fa-spin mr-1" v-if="guardandoEdicion"></i>
                    <i class="fas fa-save mr-1" v-else></i>
                    {{ guardandoEdicion ? 'Guardando...' : 'Guardar Cambios' }}
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
                            <input type="text" class="form-control" :value="medicamentoSeleccionado?.inventario?.stock_actual" disabled>
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
                            <input type="text" class="form-control" :value="medicamentoSeleccionado?.inventario?.stock_minimo" disabled>
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
                        <input type="number" min="1" class="form-control" v-model.number="movimiento.cantidad">
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
                        <input type="number" step="0.01" class="form-control" v-model.number="movimiento.costo_unitario">
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
                    class="btn btn-success"
                    :disabled="guardandoMovimiento"
                    @click="guardarMovimiento">
                    <i class="fas fa-spinner fa-spin mr-1" v-if="guardandoMovimiento"></i>
                    <i class="fas fa-save mr-1" v-else></i>
                    {{ guardandoMovimiento ? 'Guardando...' : 'Guardar Movimiento' }}
                </button>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

// Umbral de días para considerar "Próximo a Caducar" / "Próximo vencer".
// Debe coincidir con MedicamentoController@resumen (30 días) y con
// AlertasCriticas.vue, para que todas las vistas sean consistentes.
const DIAS_LIMITE_CADUCIDAD = 30

export default {
    props: {
        // Array de medicamentos administrado por el componente padre
        // (única fuente de la verdad, compartida con las tarjetas KPI).
        medicamentos: {
            type: Array,
            default: () => []
        }
    },

    // Avisa al padre para que recargue medicamentos tras cualquier
    // acción que modifique datos (editar, eliminar, registrar movimiento).
    emits: ['actualizar-inventario'],

   data(){
        return{
            // ── FILTROS ──
            filtros: {
                busqueda: '',
                estado: 'Todos',
                categoria: 'Todas',
                caducidad: 'Todas'
            },

            // ── DETALLE ──
            medicamentoDetalle:{
                inventario:{},
                ultimo_movimiento:null
            },

            // ── EDICION ──
            medicamentoSeleccionado:{
                inventario:{}
            },

            // ── MOVIMIENTO ──
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

            // ── FEEDBACK VISUAL ──
            mensaje: null,          // { texto, tipo } - tipo: success | danger | warning
            guardandoEdicion: false,
            guardandoMovimiento: false,
            eliminandoId: null
        }
    },

    computed: {
        // Aplica los 4 filtros del panel superior sobre la lista recibida por props.
        medicamentosFiltrados() {
            const termino = this.filtros.busqueda.toLowerCase().trim()

            return this.medicamentos.filter(medica => {
                const coincideTexto = !termino ||
                    (medica.nombre ?? '').toLowerCase().includes(termino) ||
                    (medica.codigo ?? '').toLowerCase().includes(termino) ||
                    (medica.ultimo_movimiento?.lote ?? '').toLowerCase().includes(termino)

                const coincideEstado =
                    this.filtros.estado === 'Todos' ||
                    this.estadoStock(medica) === this.filtros.estado

                // NOTA: se asume que cada medicamento trae medica.categoria (string)
                // o medica.categoria.nombre. Ajustar según el shape real del API.
                const categoriaNombre = medica.categoria?.nombre ?? medica.categoria ?? ''
                const coincideCategoria =
                    this.filtros.categoria === 'Todas' ||
                    categoriaNombre === this.filtros.categoria

                const coincideCaducidad = this.coincideFiltroCaducidad(medica)

                return coincideTexto && coincideEstado && coincideCategoria && coincideCaducidad
            })
        }
    },

    methods: {
        // ═══════════════════════════════════════
        // VER DETALLE
        // ═══════════════════════════════════════
        async verMedicamento(id){
            try{
                const response = await ApiService.get('/medicamentos/' + id)
                this.medicamentoDetalle = response.data
            }catch(error){
                console.error(error)
                this.mostrarMensaje('No se pudo cargar el detalle del medicamento.', 'danger')
            }
        },

        // ═══════════════════════════════════════
        // EDITAR
        // ═══════════════════════════════════════
        editarMedicamento(medica){
            // Clonamos para no mutar la lista original mientras se edita,
            // y garantizamos que 'inventario' siempre exista (evita errores
            // de v-model sobre una propiedad undefined).
            this.medicamentoSeleccionado = {
                ...medica,
                inventario: { ...(medica.inventario || {}) }
            }
        },

        async actualizarMedicamento(){
            if (!this.medicamentoSeleccionado?.id) {
                this.mostrarMensaje('No hay un medicamento seleccionado para editar.', 'warning')
                return
            }

            this.guardandoEdicion = true
            try{
                // Route::resource('medicamentos', MedicamentoController::class) -> update
                await ApiService.put('/medicamentos/' + this.medicamentoSeleccionado.id, this.medicamentoSeleccionado)

                $('#modalEditarMedicamento').modal('hide')
                this.mostrarMensaje('Medicamento actualizado correctamente.', 'success')
                this.$emit('actualizar-inventario')
            }catch(error){
                console.error(error)
                this.mostrarMensaje('No se pudo actualizar el medicamento.', 'danger')
            }finally{
                this.guardandoEdicion = false
            }
        },

        // ═══════════════════════════════════════
        // MOVIMIENTO DE INVENTARIO
        // ═══════════════════════════════════════
        abrirMovimiento(medica){
            this.medicamentoSeleccionado = {
                ...medica,
                inventario: { ...(medica.inventario || {}) }
            }

            this.movimiento = {
                medicamento_id: medica.id,
                tipo_movimiento: 'entrada',
                cantidad: 0,
                fecha_movimiento: '',
                stock_minimo: medica.inventario?.stock_minimo ?? 0,
                ubicacion: medica.inventario?.ubicacion ?? '',
                lote: '',
                fecha_caducidad: '',
                proveedor: '',
                costo_unitario: 0,
                motivo_movimiento: '',
                referencia_documento: '',
                observaciones: ''
            }
        },

        async guardarMovimiento(){
            if (!this.movimiento.medicamento_id) {
                this.mostrarMensaje('Selecciona un medicamento antes de registrar el movimiento.', 'warning')
                return
            }
            if (!this.movimiento.cantidad || this.movimiento.cantidad <= 0) {
                this.mostrarMensaje('La cantidad debe ser mayor a cero.', 'warning')
                return
            }

            this.guardandoMovimiento = true
            try{
                // Route::resource('movimientos', MovimientoInventarioController::class)
                // es un recurso independiente (no anidado bajo /medicamentos/{id}),
                // por eso el medicamento_id va dentro del payload.
                await ApiService.post('/movimientos', this.movimiento)

                $('#modalMovimientoInventario').modal('hide')
                this.mostrarMensaje('Movimiento registrado correctamente.', 'success')
                this.$emit('actualizar-inventario')
            }catch(error){
                console.error(error)
                this.mostrarMensaje('No se pudo registrar el movimiento.', 'danger')
            }finally{
                this.guardandoMovimiento = false
            }
        },

        // ═══════════════════════════════════════
        // ELIMINAR
        // ═══════════════════════════════════════
        async eliminarMedicamento(medica){
            const confirmado = window.confirm(
                `¿Eliminar el medicamento "${medica.nombre}"? Esta acción no se puede deshacer.`
            )
            if (!confirmado) return

            this.eliminandoId = medica.id
            try{
                // Route::resource('medicamentos', MedicamentoController::class) -> destroy
                await ApiService.delete('/medicamentos/' + medica.id)

                this.mostrarMensaje('Medicamento eliminado correctamente.', 'success')
                this.$emit('actualizar-inventario')
            }catch(error){
                console.error(error)
                this.mostrarMensaje('No se pudo eliminar el medicamento.', 'danger')
            }finally{
                this.eliminandoId = null
            }
        },

        // ═══════════════════════════════════════
        // FILTROS
        // ═══════════════════════════════════════
        limpiarFiltros(){
            this.filtros = {
                busqueda: '',
                estado: 'Todos',
                categoria: 'Todas',
                caducidad: 'Todas'
            }
        },

        // Determina el estado del inventario para un medicamento, en base
        // a stock_actual / stock_minimo / fecha_caducidad. Coincide con las
        // opciones del filtro "Estado": Disponible, Bajo Stock, Crítico,
        // Próximo a Caducar, Caducado.
        estadoStock(medica){
            const stockActual = medica.inventario?.stock_actual ?? 0
            const stockMinimo = medica.inventario?.stock_minimo ?? 0

            if (this.estaCaducado(medica)) return 'Caducado'
            if (this.proximoACaducar(medica)) return 'Próximo a Caducar'
            if (stockActual <= 0) return 'Crítico'
            if (stockActual <= stockMinimo) return 'Bajo Stock'
            return 'Disponible'
        },

        colorEstadoStock(estado){
            const colores = {
                'Disponible':        'success',
                'Bajo Stock':        'warning',
                'Crítico':           'danger',
                'Próximo a Caducar': 'info',
                'Caducado':          'dark'
            }
            return colores[estado] ?? 'secondary'
        },

        // FIX: antes leía medica.ultimo_movimiento?.fecha_caducidad.
        // Ahora usa medica.inventario?.fecha_caducidad, la misma fuente
        // que usa el backend (MedicamentoController@resumen) y
        // AlertasCriticas.vue, para que el estado sea consistente en
        // toda la aplicación.
        estaCaducado(medica){
            const fecha = medica.inventario?.fecha_caducidad
            if (!fecha) return false
            return new Date(fecha) < new Date()
        },

        // NUEVO: determina si el medicamento caduca dentro del umbral
        // definido (30 días), igual que MedicamentoController@resumen.
        proximoACaducar(medica){
            const fecha = medica.inventario?.fecha_caducidad
            if (!fecha) return false

            const fechaCad = new Date(fecha)
            const hoy = new Date()
            const limite = new Date()
            limite.setDate(hoy.getDate() + DIAS_LIMITE_CADUCIDAD)

            return fechaCad >= hoy && fechaCad <= limite
        },

        coincideFiltroCaducidad(medica){
            if (this.filtros.caducidad === 'Todas') return true

            // FIX: antes leía medica.ultimo_movimiento?.fecha_caducidad
            const fecha = medica.inventario?.fecha_caducidad
            if (!fecha) return false

            const fechaCad = new Date(fecha)
            const hoy = new Date()
            const enTreintaDias = new Date()
            enTreintaDias.setDate(hoy.getDate() + DIAS_LIMITE_CADUCIDAD)

            if (this.filtros.caducidad === 'Caducado') return fechaCad < hoy
            if (this.filtros.caducidad === 'Próximo vencer') return fechaCad >= hoy && fechaCad <= enTreintaDias
            if (this.filtros.caducidad === 'Vigente') return fechaCad > enTreintaDias
            return true
        },

        // ═══════════════════════════════════════
        // EXPORTAR
        // ═══════════════════════════════════════
        exportarCSV(){
            if (this.medicamentosFiltrados.length === 0) {
                this.mostrarMensaje('No hay datos para exportar con los filtros actuales.', 'warning')
                return
            }

            const filas = this.medicamentosFiltrados.map(m => ({
                Codigo: m.codigo ?? '',
                Nombre: m.nombre ?? '',
                Generico: m.nombre_generico ?? '',
                Presentacion: m.presentacion ?? '',
                Lote: m.ultimo_movimiento?.lote ?? '',
                Stock: m.inventario?.stock_actual ?? 0,
                StockMinimo: m.inventario?.stock_minimo ?? 0,
                Caducidad: m.inventario?.fecha_caducidad ?? '',
                Estado: this.estadoStock(m)
            }))

            const encabezados = Object.keys(filas[0]).join(',')
            const cuerpo = filas.map(f => Object.values(f).join(',')).join('\n')
            const csv = encabezados + '\n' + cuerpo

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
            const url = URL.createObjectURL(blob)
            const link = document.createElement('a')
            link.href = url
            link.download = `inventario_medicamentos_${new Date().toISOString().slice(0,10)}.csv`
            link.click()
            URL.revokeObjectURL(url)
        },

        // ═══════════════════════════════════════
        // FEEDBACK VISUAL
        // ═══════════════════════════════════════
        mostrarMensaje(texto, tipo = 'success'){
            this.mensaje = { texto, tipo }
            setTimeout(() => { this.mensaje = null }, 3500)
        }
    }
}
</script>