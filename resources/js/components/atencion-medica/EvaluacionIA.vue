<template>
    
<div class="card shadow-lg">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            Evaluaciones Inteligentes
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Síntomas</th>
                    <th>Riesgo</th>
                    <th>Diagnóstico IA</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>
                    <td>Dolor torácico</td>

                    <td>
                        <span class="badge badge-danger p-2">
                            ALTO
                        </span>
                    </td>

                    <td>
                        Evento cardiovascular
                    </td>

                    <td>
                        <span class="badge badge-warning">
                            En análisis
                        </span>
                    </td>

                    <td>

                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i>
                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>
</template>