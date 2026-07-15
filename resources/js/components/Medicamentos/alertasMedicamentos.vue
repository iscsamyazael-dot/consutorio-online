<template>
    <section class="content">
        <div class="container-fluid">
            <!-- ===================================== -->
            <!-- HEADER -->
            <!-- ===================================== -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="font-weight-bold">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Centro de Alertas Farmacéuticas
                            </h3>
                            <small class="text-muted">
                                Monitoreo inteligente del inventario médico
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- KPI ALERTAS -->
            <!-- ===================================== -->

            <div class="row">
                <!-- CRITICAS -->
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>8</h3>
                            <p>Alertas Críticas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-radiation-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- CADUCAR -->
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>15</h3>
                            <p>Próximos a Caducar</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <!-- AGOTADOS -->
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>5</h3>
                            <p>Sin Existencia</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>

                <!-- IA -->
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>3</h3>
                            <p>Alertas IA</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- FILTROS -->
            <!-- ===================================== -->

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i>
                        Filtros de Alertas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- BUSCAR -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    Buscar medicamento
                                </label>
                                <input type="text" class="form-control form-control-sm" placeholder="Nombre medicamento...">
                            </div>
                        </div>

                        <!-- TIPO -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>
                                    Tipo alerta
                                </label>
                                <select class="form-control form-control-sm">
                                    <option>
                                        Todas
                                    </option>
                                    <option>
                                        Críticas
                                    </option>
                                    <option>
                                        Caducidad
                                    </option>
                                    <option>
                                        Agotados
                                    </option>
                                    <option>
                                        IA
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- PRIORIDAD -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>
                                    Prioridad
                                </label>
                                <select class="form-control form-control-sm">
                                    <option>
                                        Todas
                                    </option>
                                    <option>
                                        Alta
                                    </option>
                                    <option>
                                        Media
                                    </option>
                                    <option>
                                        Baja
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- BOTON -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-search"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- TABLA ALERTAS -->
            <!-- ===================================== -->

            <div class="card card-outline card-danger">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-bell"></i>
                            Alertas del Sistema

                        </h3>
                        <button class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i>
                            Exportar
                        </button>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tipo</th>
                                <th>Medicamento</th>
                                <th>Descripción</th>
                                <th>Prioridad</th>
                                <th>Fecha</th>
                                <th width="180">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- ALERTA -->
                            <tr>
                                <td>1</td>
                                <td>
                                    <span class="badge badge-danger">
                                        Stock crítico
                                    </span>
                                </td>
                                <td>
                                    <strong>
                                        Paracetamol 500mg
                                    </strong>
                                </td>
                                <td>
                                    El stock actual es menor al mínimo permitido.
                                </td>
                                <td>
                                    <span class="badge badge-danger">
                                        Alta
                                    </span>
                                </td>
                                <td>
                                    26/05/2026
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm">
                                            <i class="fas fa-boxes"></i>
                                        </button>
                                        <button class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- ALERTA -->
                            <tr>
                                <td>2</td>
                                <td>
                                    <span class="badge badge-warning">
                                        Caducidad
                                    </span>
                                </td>
                                <td>
                                    <strong>
                                        Insulina NPH
                                    </strong>
                                </td>
                                <td>
                                    El medicamento caduca en 12 días.
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        Media
                                    </span>
                                </td>
                                <td>
                                    26/05/2026
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- ALERTA -->
                            <tr>
                                <td>3</td>
                                <td>
                                    <span class="badge badge-secondary">
                                        Agotado
                                    </span>
                                </td>
                                <td>
                                    <strong>
                                        Amoxicilina
                                    </strong>
                                </td>
                                <td>
                                    El medicamento no tiene existencias.
                                </td>
                                <td>
                                    <span class="badge badge-dark">
                                        Alta
                                    </span>
                                </td>
                                <td>
                                    26/05/2026
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- IA -->
                            <tr>
                                <td>4</td>
                                <td>
                                    <span class="badge badge-info">
                                        IA
                                    </span>
                                </td>
                                <td>
                                    <strong>
                                        Antibióticos
                                    </strong>
                                </td>
                                <td>
                                    IA detectó aumento anormal de consumo.
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        Predictiva
                                    </span>
                                </td>
                                <td>
                                    26/05/2026
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-robot"></i>
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

<script>
export default {
    data(){
        return{

        }
    },
    methods: {

    }

}

</script>

<style scoped>

.small-box{
    border-radius: 10px;
}

.card{
    border-radius: 10px;
}

.table td,
.table th{
    vertical-align: middle;
}

.badge{
    font-size: 12px;
}

</style>