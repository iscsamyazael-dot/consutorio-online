
<template>

<!-- Lista -->

<div class="card card-outline card-primary shadow-lg">

    <div class="card-header">

        <h3 class="card-title font-weight-bold">
            Lista de Pacientes TRIAGE
        </h3>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Prioridad</th>
                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Presión</th>
                    <th>Saturación</th>
                    <th>Temperatura</th>
                    <th>Estado</th>
                    <th>Tiempo</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>
                        <span class="badge badge-danger p-2">
                            CRÍTICO
                        </span>
                    </td>

                    <td>
                        Juan Pérez
                    </td>

                    <td>
                        Dolor torácico
                    </td>

                    <td>
                        180/120
                    </td>

                    <td>
                        86%
                    </td>

                    <td>
                        39°C
                    </td>

                    <td>
                        <span class="badge badge-danger">
                            Riesgo Alto
                        </span>
                    </td>

                    <td>
                        2 min
                    </td>

                    <td>

                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <span class="badge badge-warning p-2">
                            URGENTE
                        </span>
                    </td>

                    <td>
                        María López
                    </td>

                    <td>
                        Fiebre alta
                    </td>

                    <td>
                        140/90
                    </td>

                    <td>
                        94%
                    </td>

                    <td>
                        38°C
                    </td>

                    <td>
                        <span class="badge badge-warning">
                            Observación
                        </span>
                    </td>

                    <td>
                        10 min
                    </td>

                    <td>

                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <span class="badge badge-info p-2">
                            MODERADO
                        </span>
                    </td>

                    <td>
                        Carlos Méndez
                    </td>

                    <td>
                        Dolor abdominal
                    </td>

                    <td>
                        120/80
                    </td>

                    <td>
                        98%
                    </td>

                    <td>
                        37°C
                    </td>

                    <td>
                        <span class="badge badge-info">
                            Estable
                        </span>
                    </td>

                    <td>
                        20 min
                    </td>

                    <td>

                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>
</template>