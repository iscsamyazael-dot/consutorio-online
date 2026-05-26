<template>

<div class="row">
<div class="col-lg-12">
    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h1 class="font-weight-bold">
                Archivos Clínicos
            </h1>

            <small class="text-muted">
                Gestión de estudios médicos
            </small>
        </div>

        <button  class="btn btn-success btn-lg rounded-pill shadow"
    data-bs-toggle="modal"
    data-bs-target="#modalArchivoClinico">
    <i class="fas fa-file-medical me-2"></i>
            Subir Archivo
        </button>

    </div>
</div>
</div>

<div class="row">
    <div class="col">

            <div class="card shadow-lg">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">
            Estudios Médicos
        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-hover table-bordered">

            <thead class="bg-light">

                <tr>

                    <th>Paciente</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Archivo</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>María López</td>
                    <td>Radiografía</td>
                    <td>20/05/2026</td>

                    <td>

                        <span class="badge badge-success">
                            Procesado
                        </span>

                    </td>

                    <td>

                        <button class="btn btn-primary btn-sm">

                            <i class="fas fa-file-medical"></i>

                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

    </div>
</div>




<!-- =========================================
MODAL ARCHIVO CLINICO
========================================= -->

<div
    class="modal fade"
    id="modalArchivoClinico"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- =========================================
            HEADER
            ========================================== -->

            <div
                class="modal-header border-0 text-white p-4"
                style="
    background:
    linear-gradient(
        135deg,
        #2563eb,
        #0ea5e9
    );
"
            >

                <div class="d-flex align-items-center">

                    <div
                        class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="
                            width:65px;
                            height:65px;
                        "
                    >

                        <i
                            class="fas fa-file-upload text-blue"
                            style="
                                font-size:30px;
                            "
                        ></i>

                    </div>

                    <div>

                        <h3 class="fw-bold mb-1 ">

                            Archivo Clínico

                        </h3>

                        <small class="opacity-75">

                            Gestión y carga de documentos médicos

                        </small>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <!-- =========================================
            BODY
            ========================================== -->

            <div class="modal-body bg-light p-4">

                <div class="row align-items-stretch">

                    <!-- =========================================
                    PANEL IZQUIERDO
                    ========================================== -->

                    <div class="col-lg-4 mb-4">

                        <div class="card border-0 shadow rounded-4 h-100 d-flex flex-column">

                            <div
                                class="card-body text-white p-4"
                                style="
                                    background:
                                    linear-gradient(
                                        180deg,
                                        #1e293b,
                                        #2563eb
                                    );
                                    border-radius:1rem;
                                "
                            >

                                <div class="text-center mb-5">

                                    <div
                                        class="bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center shadow"
                                        style="
                                            width:90px;
                                            height:90px;
                                        "
                                    >

                                        <i
                                            class="fas fa-folder-open text-blue"
                                            style="
                                                font-size:38px;
                                            "
                                        ></i>

                                    </div>

                                </div>

                                <h4 class="fw-bold text-center mb-3">

                                    Archivos Médicos

                                </h4>

                                <p
                                    class="text-center opacity-75"
                                    style="
                                        line-height:1.8;
                                    "
                                >

                                    Suba documentos clínicos,
                                    estudios y archivos médicos
                                    de forma segura.

                                </p>

                                <!-- INFO -->

                                <div class="mt-5">

                                    <div
                                        class="card border-0 text-white rounded-4 mb-3"
                                        style="
                                            background:
                                            rgba(255,255,255,0.15);
                                            backdrop-filter:
                                            blur(10px);
                                        "
                                    >

                                        <div class="card-body d-flex align-items-center">

                                            <div
                                                class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="
                                                    width:50px;
                                                    height:50px;
                                                "
                                            >

                                                <i class="fas fa-file-pdf"></i>

                                            </div>

                                            <div>

                                                <small class="opacity-75">

                                                    Formatos

                                                </small>

                                                <h6 class="fw-bold mb-0">

                                                    PDF · JPG · PNG

                                                </h6>

                                            </div>

                                        </div>

                                    </div>

                                    <div
                                        class="card border-0 text-white rounded-4"
                                        style="
                                            background:
                                            rgba(255,255,255,0.15);
                                            backdrop-filter:
                                            blur(10px);
                                        "
                                    >

                                        <div class="card-body d-flex align-items-center">

                                            <div
                                                class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="
                                                    width:50px;
                                                    height:50px;
                                                "
                                            >

                                                <i class="fas fa-lock"></i>

                                            </div>

                                            <div>

                                                <small class="opacity-75">

                                                    Seguridad

                                                </small>

                                                <h6 class="fw-bold mb-0">

                                                    Protección Clínica

                                                </h6>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- =========================================
                    FORMULARIO
                    ========================================== -->

                    <div class="col-lg-8">

                        <div class="card border-0 shadow rounded-4 h-100 d-flex flex-column ">

                            <div class="card-body p-4">

                                <!-- PACIENTE -->

                                <div class="mb-4">

                                    <label class="fw-bold mb-2">

                                        <i class="fas fa-user text-primary me-2"></i>

                                        Nombre del Paciente

                                    </label>

                                    <input
                                        type="text"
                                        class="form-control border-0 shadow-sm rounded-4 p-3"
                                        placeholder="Ingrese nombre..."
                                        v-model="archivo.nombrePaciente"
                                    >

                                </div>

                                <!-- TIPO -->

                                <div class="mb-4">

                                    <label class="fw-bold mb-2">

                                        <i class="fas fa-file-alt text-success me-2"></i>

                                        Tipo de Archivo

                                    </label>

                                    <select
                                        class="form-select border-0 shadow-sm rounded-4 p-3"
                                        v-model="archivo.tipo"
                                    >

                                        <option value="">

                                            Seleccionar...

                                        </option>

                                        <option value="Radiografía">

                                            Radiografía

                                        </option>

                                        <option value="Receta Médica">

                                            Receta Médica

                                        </option>

                                        <option value="Análisis Clínico">

                                            Análisis Clínico

                                        </option>

                                        <option value="Expediente">

                                            Expediente

                                        </option>

                                    </select>

                                </div>

                                <!-- FECHA -->

                                <div class="mb-4">

                                    <label class="fw-bold mb-2">

                                        <i class="fas fa-calendar-alt text-danger me-2"></i>

                                        Fecha

                                    </label>

                                    <input
                                        type="date"
                                        class="form-control border-0 shadow-sm rounded-4 p-3"
                                        v-model="archivo.fecha"
                                    >

                                </div>

                                <!-- ESTADO -->

                                <div class="mb-4">

                                    <label class="fw-bold mb-2">

                                        <i class="fas fa-heartbeat text-warning me-2"></i>

                                        Estado

                                    </label>

                                    <select
                                        class="form-select border-0 shadow-sm rounded-4 p-3"
                                        v-model="archivo.estado"
                                    >

                                        <option value="">

                                            Seleccionar...

                                        </option>

                                        <option value="Pendiente">

                                            Pendiente

                                        </option>

                                        <option value="Revisado">

                                            Revisado

                                        </option>

                                        <option value="Urgente">

                                            Urgente

                                        </option>

                                    </select>

                                </div>

                                <!-- ARCHIVO -->

                                <div class="mb-3">

                                    <label class="fw-bold mb-2">

                                        <i class="fas fa-upload text-info me-2"></i>

                                        Subir Archivo

                                    </label>

                                    <input
                                        type="file"
                                        class="form-control border-0 shadow-sm rounded-4 p-3"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- =========================================
            FOOTER
            ========================================== -->

            <div class="modal-footer border-0 bg-white p-4">

                <button
                    class="btn btn-light rounded-pill px-4 shadow-sm"
                    data-bs-dismiss="modal"
                >

                    <i class="fas fa-times me-2"></i>

                    Cancelar

                </button>

                <button
                    class="btn btn-primary rounded-pill px-5 shadow"
                    type="button"
                >

                    <i class="fas fa-save me-2"></i>

                    Guardar Archivo

                </button>

            </div>

        </div>

    </div>

</div>


</template>

<script>
export default {

    data() {

        return {

            archivo: {

                nombrePaciente: '',

                tipo: '',

                fecha: '',

                estado: ''

            }

        }

    },

    methods: {

        guardarArchivoClinico() {

            console.log(this.archivo)

        }

    }

}
</script>