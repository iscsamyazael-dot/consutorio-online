<template>

    <!-- TABLA -->
    <div class="card border-0 shadow rounded-4">

        <div class="card-body">

            <!-- FILTROS -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Buscar receta
                    </label>
                    <input
                        type="text"
                        class="form-control rounded-pill"
                        placeholder="Buscar por paciente o diagnóstico">
                </div>

                        <div class="col-md-3 d-flex flex-column align-items-center">
                            <label class="form-label fw-semibold text-center">
                                Estado
                                 </label>
                                  
                                    <select class="form-select rounded-pill estado-select">

                                        <option>  Todos</option>

                                        <option>🟢 Activa</option>

                                        <option>🔵 Completada</option>

                                        <option>🔴 Cancelada</option>

                                    </select>


                            </div>


                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Fecha
                    </label>
                    <input
                        type="date"
                        class="form-control rounded-pill">
                </div>
            </div>

            <!-- TABLA DE DATOS -->
            <div class="table-responsive">
                <table class="table recetas-table  align-middle">
                    <thead class="table-light">
                        <tr>

                            <th>Folio</th>
                            <th>Paciente</th>
                            <th>Diagnóstico</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Médico</th>
                            <th>Alertas</th>
                            <th class="text-center">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">
                                    #RX-001
                                </span>
                            </td>
                            <td>
                                Juan Pérez
                            </td>
                            <td>
                                Hipertensión arterial
                            </td>
                            <td>
                                28/05/2026
                            </td>
                            <td>
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    Activa
                                </span>
                            </td>
                            <td>
                                Dr. Samuel
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    2 alertas
                                </span>
                            </td>
                            <td class="text-center">
                            
                                    <button
                                        class="btn btn-outline-primary btn-sm rounded-circle me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalVistaReceta">

                                        <i class="fas fa-eye"></i>
                                    </button>

                                    

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">
                                    #RX-002
                                </span>
                            </td>
                            <td>
                                María López
                            </td>
                            <td>
                                Diabetes Tipo II
                            </td>
                            <td>
                                27/05/2026
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    Completada
                                </span>
                            </td>
                            <td>
                                Dr. Samuel
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    Sin alertas
                                </span>
                            </td>
                            <td class="text-center">
                               <button
                                        class="btn btn-outline-primary btn-sm rounded-circle me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalVistaReceta">

                                        <i class="fas fa-eye"></i>
                                    </button>
                                
                                
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


<div
    class="modal fade"
    id="modalVistaReceta"
    tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg modal-receta">

            <!-- HEADER -->

            <div class="modal-header border-0">

                <div>

                    <h4 class="fw-bold text-primary mb-1">

                        <i class="fas fa-file-medical me-2"></i>

                        Resumen de Receta

                    </h4>

                    <small class="text-muted">

                        Vista general de la prescripción médica

                    </small>

                </div>

                                        
                        <button
                            type="button"
                            class="btn btn-light rounded-circle btn-close-modern"
                            data-bs-dismiss="modal">

                            <i class="fas fa-times"></i>

                        </button>



            </div>

            <!-- BODY -->

            <div class="modal-body">

                <div class="row g-4">

                    <div class="col-md-6">

                        <div class="info-card">

                            <label>Paciente</label>

                            <h6>Juan Pérez</h6>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="info-card">

                            <label>Diagnóstico</label>

                            <h6>Hipertensión arterial</h6>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="info-card">

                            <label>Medicamentos</label>

                            <ul class="mb-0">

                                <li>Losartán 50mg</li>
                                <li>Aspirina 100mg</li>

                            </ul>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="info-card">

                            <label>Indicaciones</label>

                            <p class="mb-0">

                                Tomar una tableta cada 12 horas.

                            </p>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="info-card">

                            <label>Médico</label>

                            <h6>Dr. Samuel</h6>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="info-card">

                            <label>Estado IA</label>

                            <span class="badge bg-danger rounded-pill">

                                <i class="fas fa-robot me-1"></i>

                                2 alertas detectadas

                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FOOTER -->

            
<div class="modal-footer border-0 justify-content-between">


    <!-- ACCIONES -->

    <div class="d-flex gap-1">

        <!-- PDF -->

        <button
            class="btn btn-danger rounded-pill px-4 btn-pdf">

            <i class="fas fa-file-pdf me-4"></i>

            Descargar PDF

        </button>

        

        <!-- IMPRIMIR -->

        <button
            class="btn btn-primary rounded-pill px-4 btn-print">

            <i class="fas fa-print me-4"></i>

            Imprimir

        </button>

    </div>

</div>



        </div>

    </div>

</div>

</template>

<style scoped>

.modal-receta{

    border-radius: 24px;

}

.info-card{

    background: #f8fafc;

    padding: 18px;

    border-radius: 18px;

}

.info-card label{

    font-size: 13px;

    color: #64748b;

    margin-bottom: 6px;

    display: block;

}



/* =========================
   FILTROS
========================= */

.estado-select{

    width: 180px;

}

/* =========================
   INPUTS
========================= */

.form-control,
.form-select{

    height: 46px;

    border: 1px solid #dbe1e8;

    transition: .2s ease;

    background: #fff;

}

.form-control:focus,
.form-select:focus{

    border-color: #0c1fce;

    box-shadow: 0 0 0 3px rgba(37,99,235,.10);

}

/* LABELS */

.form-label{

    color: #555555;

    margin-bottom: 10px;

    font-size: 20px;

}

/* =========================
   TABLA
========================= */

.recetas-table{

    border-collapse: separate;

    border-spacing: 0 10px;

}

/* ELIMINAR LÍNEAS BOOTSTRAP */

.recetas-table>:not(caption)>*>*{

    border-bottom-width: 0 !important;

    box-shadow: none !important;

}

/* HEADER */

.recetas-table thead th{

    background: transparent !important;

    color: #64748b;

    font-size: 13px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .5px;

    padding: 14px 18px;

    border: none !important;

}

/* FILAS */

.recetas-table tbody tr{

    background: #f0f0f0;

    transition: .25s ease;

    box-shadow: 0 4px 10px rgba(0,0,0,.04);

}

/* HOVER */

.recetas-table tbody tr:hover{

    transform: translateY(-2px);

    box-shadow: 0 10px 24px rgba(37,99,235,.10);

}

/* CELDAS */

.recetas-table tbody td{

    padding: 18px;

    vertical-align: middle;

    border: none !important;

}

/* BORDES REDONDOS */

.recetas-table tbody tr td:first-child{

    border-top-left-radius: 16px;

    border-bottom-left-radius: 16px;

}

.recetas-table tbody tr td:last-child{

    border-top-right-radius: 16px;

    border-bottom-right-radius: 16px;

}

/* =========================
   BADGES
========================= */

.badge{

    padding: 8px 14px;

    border-radius: 50px;

    font-size: 12px;

    font-weight: 600;

}



/* =========================
   BOTONES
========================= */

.btn{

    transition: .2s ease;

}

.btn:hover{

    transform: scale(1.06);

}

/* BOTONES ACCIONES */

.btn-outline-primary,
.btn-outline-success,
.btn-outline-warning{

    width: 36px;

    height: 36px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

}

/* BOTON CERRAR HEADER */

.btn-close-modern{

    width: 40px;

    height: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    border: none;

    transition: .25s ease;

    color: #64748b;

    background: #f8fafc;

}

/* HOVER */

.btn-close-modern:hover{

    background: #dbeafe;

    color: #2563eb;

    transform: rotate(90deg) scale(1.05);

}

/* ICONO */

.btn-close-modern i{

    font-size: 14px;

}

/* BOTÓN PDF */

.btn-pdf{

    background: linear-gradient(
        135deg,
        #dc2626,
        #ef4444
    );

    border: none;

    box-shadow: 0 4px 14px rgba(220,38,38,.25);

    transition: .2s ease;

}

/* HOVER PDF */

.btn-pdf:hover{

    transform: translateY(-2px);

    box-shadow: 0 8px 20px rgba(220,38,38,.35);

}

/* BOTÓN IMPRIMIR */

.btn-print{

    box-shadow: 0 4px 14px rgba(37,99,235,.20);

    transition: .2s ease;

}

/* HOVER PRINT */

.btn-print:hover{

    transform: translateY(-2px);

    box-shadow: 0 8px 20px rgba(37,99,235,.30);

}



/* =========================
   CARD
========================= */

.card{

    border-radius: 24px;

    overflow: hidden;

}

</style>
