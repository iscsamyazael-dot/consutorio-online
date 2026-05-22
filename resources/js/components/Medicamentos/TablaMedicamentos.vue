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
</template>
