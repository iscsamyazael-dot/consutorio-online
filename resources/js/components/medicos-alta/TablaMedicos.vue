<template>
    <div class="container-fluid">

     <!--Tarjetas Resumen-->
    <div class="row mb-4">

        <div class="col-md-4">

            <div class="small-box bg-info">

                <div class="inner">
                    <h3>15</h3>
                    <p>Doctores Registrados</p>
                </div>

                <div class="icon">
                    <i class="fas fa-user-md"></i>
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="small-box bg-success">

                <div class="inner">
                    <h3>12</h3>
                    <p>Activos</p>
                </div>

                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="small-box bg-warning">

                <div class="inner">
                    <h3>3</h3>
                    <p>Inactivos</p>
                </div>

                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>

            </div>

        </div>

    </div>

    <!-- Tabla -->
    <div class="card shadow">

        <div class="card-header">

            <div class="row">

                <div class="col-md-6">

                    <h3 class="card-title">
                        Lista de Doctores
                    </h3>

                </div>

                <div class="col-md-6">

                    <div class="input-group">

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Buscar doctor..."
                        >

                        <div class="input-group-append">

                            <button class="btn btn-outline-secondary">

                                <i class="fas fa-search"></i>

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="card-body p-0">

            <table class="table table-hover text-nowrap">

                <thead class="thead-light">

                    <tr>

                        <th>ID</th>
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th>Horario</th>
                        <th>Días Laborales</th>
                        <th>Estado</th>
                        <th width="180">Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>1</td>

                        <td>
                            Dr. Juan Pérez
                        </td>

                        <td>
                            Cardiología
                        </td>

                        <td>
                            08:00 - 16:00
                        </td>
                        <td>
                            Miercoles-viernes
                        </td>

                        <td>

                            <span class="badge badge-success">
                                Activo
                            </span>

                        </td>

                        <td>

                            <button
                                class="btn btn-sm btn-info">

                                <i class="fas fa-eye"></i>

                            </button>

                            <button
                                class="btn btn-sm btn-warning">

                                <i class="fas fa-edit"></i>

                            </button>

                            <button
                                class="btn btn-sm btn-danger">

                                <i class="fas fa-trash"></i>

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>
</template>

<script>
</script>