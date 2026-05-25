<template>
    
    <div class="card shadow-lg">
        
            <div class="card-header bg-warning text-white">

        <h5 class="mb-0">
            Lista de Derivaciones
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Motivo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>

                    <td>Cardiología</td>

                    <td>
                        Sospecha cardiovascular
                    </td>

                    <td>

                        <span class="badge badge-danger p-2">
                            ALTA
                        </span>

                    </td>

                    <td>

                        <span class="badge badge-warning">
                            Pendiente
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