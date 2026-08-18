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
                    <th>Folio</th>
                    <th>Paciente</th>
                    <th>Tipo</th>
                    <th>Fecha de consulta</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody>
                <tr
                v-for="mostrar in listaArchivos" :key="mostrar.id"
                >
                    <td>
                        {{ mostrar.codigo_paciente }}
                    </td>

                    <td>
                        {{mostrar.paciente?.nombre}}
                    </td>

                    <td>
                        <div class="dropdown">

                            <button
                                class="btn btn-sm dropdown-toggle rounded-pill fw-bold shadow-sm"
                                :class="getTipoBadge(mostrar.tipo_archivo)"
                                data-bs-toggle="dropdown"
                            >
                                {{ getTipoIcon(mostrar.tipo_archivo) }}
                                {{ mostrar.tipo_archivo }}
                            </button>

                            <ul class="dropdown-menu shadow border-0">

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarTipo(mostrar,'Radiografía')"
                                    >
                                        🩻 Radiografía
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarTipo(mostrar,'Receta Médica')"
                                    >
                                        💊 Receta Médica
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarTipo(mostrar,'Análisis Clínico')"
                                    >
                                        🧪 Análisis Clínico
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarTipo(mostrar,'Expediente')"
                                    >
                                        📁 Expediente
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>

                    <td>
                        {{ mostrar.fecha_consulta?.split(' ')[0] || 'Sin consulta' }}
                    </td>

                    <td>{{ mostrar.fecha_subida?.split(' ')[0] }}</td>
                    
                    <td>

                        <div class="dropdown">

                            <button
                                class="btn btn-sm dropdown-toggle rounded-pill fw-bold shadow-sm"
                                :class="getEstadoBadge(mostrar.Estado)"
                                data-bs-toggle="dropdown"
                            >
                                {{ getEstadoIcon(mostrar.Estado) }}
                                {{ mostrar.Estado }}
                            </button>

                            <ul class="dropdown-menu shadow border-0">

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarEstado(mostrar,'Pendiente')"
                                    >
                                        🟡 Pendiente
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarEstado(mostrar,'En Revisión')"
                                    >
                                        🔵 En Revisión
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarEstado(mostrar,'Completado')"
                                    >
                                        🟢 Completado
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="actualizarEstado(mostrar,'Cancelado')"
                                    >
                                        🔴 Cancelado
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>

                    <td>
                            <button
                                type="button"
                                class="btn btn-info btn-sm"
                                @click="DatosArchivo(mostrar.id)"
                            >
                                <i class="fas fa-eye"></i>
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
                        ">

                        <i
                            class="fas fa-file-upload text-blue"
                            style="
                                font-size:30px;
                            "
                        >
                        </i>

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
                                        class="card border-0 text-dark rounded-4 mb-3 shadow-sm"
                                        style="
                                            background: rgba(255, 255, 255, 0.65);
                                            backdrop-filter: blur(10px);
                                        "
                                    >
                                        <div class="card-body d-flex align-items-center">
                                            <!-- Icono con tono azul suave -->
                                            <div
                                                class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 50px; height: 50px; flex-shrink: 0;"
                                            >
                                                <i class="fas fa-file-pdf fa-lg"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted fw-semibold d-block">
                                                    Formatos
                                                </small>
                                                <h6 class="fw-bold mb-0 text-dark">
                                                    PDF · JPG · PNG
                                                </h6>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tarjeta Seguridad -->
                                    <div
                                        class="card border-0 text-dark rounded-4 shadow-sm"
                                        style="
                                            background: rgba(255, 255, 255, 0.65);
                                            backdrop-filter: blur(10px);
                                        "
                                    >
                                        <div class="card-body d-flex align-items-center">
                                            <!-- Icono con tono amarillo/naranja suave -->
                                            <div
                                                class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 50px; height: 50px; flex-shrink: 0;"
                                            >
                                                <i class="fas fa-lock fa-lg"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted fw-semibold d-block">
                                                    Seguridad
                                                </small>
                                                <h6 class="fw-bold mb-0 text-dark">
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
                                        v-model="buscar" 
                                        list = "listaPacientes"
                                        @input="buscarPaciente" 
                                        @change="seleccionarPaciente" 
                                        
                                    >

                                    <datalist id="listaPacientes">
                                        <option
                                            v-for="paciente in filtrar"
                                            :key="paciente.id"
                                            :value=" paciente.nombre + ' ' + paciente.apellido_paterno + ' ' + paciente.apellido_materno"
                                            @click="buscarPaciente(paciente)"
                                        >
                                        </option>
                                    </datalist>
                                </div>

                                <!-- TIPO -->
                                <div class="mb-4">
                                    <label class="fw-bold mb-2">
                                        <i class="fas fa-file-alt text-success me-2"></i>
                                        Tipo de Archivo
                                    </label>
                                    <select
                                        class="form-select border-0 shadow-sm rounded-4 p-3" 
                                        v-model="form.tipo_archivo" 
                                    >
                                        <!-- Opción placeholder: oculta y deshabilitada -->
                                        <option value="" disabled hidden>
                                            Seleccionar archivo...
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
                                        v-model="form.fecha"
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
                                        v-model="form.estado"
                                    >

                                        <option value="" disabled hidden>
                                            Seleccionar Estado...
                                        </option>
                                        <option value="Pendiente">
                                            Pendiente
                                        </option>
                                        <option value="Completado">
                                            Completado
                                        </option>
                                        <option value="En Revisión">
                                            En Revisión
                                        </option>
                                        <option value="Cancelado">
                                            Cancelado
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
                                        @change="seleccionarArchivo"
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
                    @click="guardarArchivoClinico"
                >
                    <i class="fas fa-save me-2"></i>
                    Guardar Archivo
                </button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
MODAL VER ARCHIVO CLINICO
========================================= -->

<div
    v-if="modalArchivoClinico"
    class="modal fade show d-block"
    id="modalVerTriage"
    tabindex="-1"
    style="
        background: rgba(15,23,42,.75);
        backdrop-filter: blur(6px);
    "
>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-5 overflow-hidden shadow-lg">
            
            <div
                class="modal-header p-4 text-white border-0"
                style="
                    background: linear-gradient(
                        135deg,
                        #7c3aed,
                        #a855f7
                    );
                "
            >
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center">
                        <div
                            class="rounded-circle d-flex justify-content-center align-items-center me-4"
                            style="
                                width:80px;
                                height:80px;
                                background:rgba(255,255,255,.2);
                            "
                        >
                            <i
                                class="fas fa-file-medical"
                                style="font-size:35px;"
                            ></i>
                        </div>
                        <div>
                            <h2 class="modal-title fw-bold mb-1">
                                Archivo Clínico
                            </h2>
                            <p class="mb-0 opacity-75">
                                Información del expediente médico
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn btn-light rounded-circle"
                        @click="modalArchivoClinico = false"
                        aria-label="Close"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div
                class="modal-body p-5"
                style="background:#f1f5f9;"
            >
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4">
                            <small class="text-muted">
                                <i class="fas fa-user me-2 text-primary"></i>
                                Paciente
                            </small>
                            <h5 class="fw-bold mt-3">
                                {{ detallearchivo?.paciente?.nombre }}
                            </h5>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4">
                            <small class="text-muted">
                                <i class="fas fa-folder-open me-2 text-warning"></i>
                                Tipo Archivo
                            </small>
                            <h5 class="fw-bold mt-3">
                                {{ detallearchivo?.tipo_archivo }}
                                </h5>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-2 text-info"></i>
                                Fecha
                            </small>
                            <h5 class="fw-bold mt-3">
                                {{ detallearchivo.fecha_subida?.split(' ')[0] }}
                                </h5>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="bg-white rounded-4 shadow-sm p-4">
                            <small class="text-muted">
                                <i class="fas fa-check-circle me-2 text-success"></i>
                                Estado
                            </small>
                            <h5 class="fw-bold mt-3">
                                {{ detallearchivo?.Estado }}
                                </h5>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="bg-white rounded-4 shadow-sm p-5 text-center">
                             
                            <!-- función que trae el ícono de acuerdo al tipo de documento -->
                            <i
                                :class="obtenerIcono(detallearchivo?.archivo_url)"
                                style="font-size:70px;"
                            ></i>
                            <div class="mt-4">
                                <div class="fw-bold text-dark">
                                    {{ detallearchivo?.tipo_archivo || 'Archivo clínico' }}
                                </div>
                                <small class="text-muted">
                                    Archivo disponible para visualización
                                </small>
                            </div>
                            <button
                            type="button"
                            class="btn btn-primary rounded-pill px-4 mt-4"
                            @click="verArchivo(detallearchivo)"
                        >
                            <i class="fas fa-eye me-2"></i>
                            Visualizar Archivo
                        </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Modal para mostrar los archivos a traves de un modal-->
    <div
    class="modal fade"
    id="modalArchivo"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <!-- HEADER -->
            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fas fa-file-medical me-2"></i>
                    Vista del Archivo Clínico
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <!-- BODY -->
            <div class="modal-body text-center bg-light">

                <!-- IMAGEN -->
                <div v-if="esImagen()">

                    <img
                        :src="archivoSeleccionado"
                        class="img-fluid rounded shadow"
                        style="max-height: 70vh;"
                        alt="Archivo clínico"
                    >

                </div>

                <!-- PDF -->
                <div v-else-if="esPDF()">

                    <iframe
                        :src="archivoSeleccionado"
                        width="100%"
                        height="700"
                        style="border: none;"
                    ></iframe>

                </div>

                <!-- OTROS ARCHIVOS -->
                <div v-else class="py-5">

                    <i
                        class="fas fa-file-alt text-secondary"
                        style="font-size: 70px;"
                    ></i>

                    <h5 class="mt-3">
                        Este tipo de archivo no tiene
                        vista previa.
                    </h5>

                    <a
                        :href="archivoSeleccionado"
                        target="_blank"
                        class="btn btn-primary rounded-pill mt-3"
                    >
                        <i class="fas fa-external-link-alt me-2"></i>
                        Abrir archivo
                    </a>

                </div>

            </div>

        </div>
    </div>
</div>
<!--Aqui termina el modal para ver los archivos-->

</template>

<script>
import ApiService from '../../services/ApiService.js'
export default {

    data() { 

        return {

            // SIRVE PARA VER MODAL
            modalArchivoClinico: false,
            cargandoArchivo: false,
            detallearchivo: [],
            listaArchivos: [],
            filtrar: [],
            buscar:'', 
            archivoSeleccionado: '',
            archivoExtension: '',
            form: {
                paciente_id: '',
                codigo_paciente: '',
                tipo_archivo: '',
                fecha: '', 
                estado: '',
                archivo: ''
            }  
        }
    },

    mounted(){

     this.ObtenerArchivos()

    },


    methods: {

        getTipoBadge(tipo) {
            switch (tipo) {
                case 'Radiografía':
                    return 'bg-primary text-white'

                case 'Receta Médica':
                    return 'bg-success text-white'

                case 'Análisis Clínico':
                    return 'bg-info text-white'

                case 'Expediente':
                    return 'bg-secondary text-white'

                default:
                    return 'bg-light text-dark'
            }
        },

        getTipoIcon(tipo) {
            switch (tipo) {
                case 'Radiografía':
                    return '🩻'

                case 'Receta Médica':
                    return '💊'

                case 'Análisis Clínico':
                    return '🧪'

                case 'Expediente':
                    return '📁'

                default:
                    return '📄'
            }
        },

        getEstadoBadge(estado) {
            switch (estado) {
                case 'Pendiente':
                    return 'bg-warning text-dark'

                case 'En Revisión':
                    return 'bg-primary text-white'

                case 'Completado':
                    return 'bg-success text-white'

                case 'Cancelado':
                    return 'bg-danger text-white'

                default:
                    return 'bg-secondary text-white'
            }
        },

        getEstadoIcon(estado) {
            switch (estado) {
                case 'Pendiente':
                    return '🟡'

                case 'En Revisión':
                    return '🔵'

                case 'Completado':
                    return '🟢'

                case 'Cancelado':
                    return '🔴'

                default:
                    return '⚪'
            }
        },

        async actualizarEstado(item, nuevoEstado) {
            try {

                await ApiService.put(`/archivos-clinicos/${item.id}`, {
                    Estado: nuevoEstado
                });

                item.Estado = nuevoEstado;

            } catch (error) {
                console.error(error);
            }
        },
        
        async actualizarTipo(item, nuevoTipo) {
            try {

                await ApiService.put(`/archivos-clinicos/${item.id}`, {
                    tipo_archivo: nuevoTipo
                });

                item.tipo_archivo = nuevoTipo;

            } catch (error) {
                console.error(error);
            }
        },

        async buscarPaciente(){
            try{
                const response = await ApiService.get('/buscarPaciente?buscar=' + this.buscar);
                this.filtrar = response.data;
            }catch(error){
                console.error('No se encuentran resultados'.error)
            }
        },

        //Selecciona un paciente que se trae a traves de la input de busqueda//                                           
        seleccionarPaciente() { 
            const paciente = this.filtrar.find(p => 
                `${p.nombre} ${p.apellido_paterno} ${p.apellido_materno}` === this.buscar); 
            if (paciente) { 
                this.form.paciente_id = paciente.id; 
                this.form.codigo_paciente = paciente.paciente_id; 
                console.log('ID:', paciente.id);
                console.log('Código:', paciente.paciente_id); 
            } else {
                // Un log extra por si la cadena de texto sigue sin coincidir perfectamente
                console.log('No se encontró coincidencia exacta para:', this.buscar);
            }
        },


        //* Metodo para guardar archivos clinicos a la base de datos *//
        async guardarArchivoClinico() {

            try {
                let data = new FormData();

                data.append('paciente_id', this.form.paciente_id);
                data.append('codigo_paciente', this.form.codigo_paciente);
                data.append('tipo_archivo', this.form.tipo_archivo);
                data.append('fecha', this.form.fecha);
                data.append('Estado', this.form.estado);
                data.append('archivo', this.form.archivo);

                console.log('Formulario:', this.form);


                const response = await ApiService.post(
                    '/archivoClinico',
                    data,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                );

                    // Recargar la tabla
                    await this.ObtenerArchivos();

                    // Cerrar modal Bootstrap
                    $('#modalArchivoClinico').modal('hide');

                console.log(response.data);
                Swal.fire({
                    icon: 'success',
                    title: 'Archivo clínico guardado',
                    text: 'El archivo fue guardado correctamente'
                });

            } catch(error) {

                console.error(error);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar el archivo'
                });

            }
        },

            
        //AL SELECCIONAR UN ARCHIVO APARECE UN MENSAJE: "ARCHIVO SELECCIONADO"
        seleccionarArchivo(event) {
            this.form.archivo = event.target.files[0];
            console.log('Archivo seleccionado:', this.form.archivo);
        },
            

        async ObtenerArchivos() {
            try {
                const response = await ApiService.get('archivoclinico')
                this.listaArchivos = response.data
                console.log('archivos:', this.listaArchivos)
            } catch (error) {
                console.error("error al cargar los archivos:", error)
            } 

        },


        async DatosArchivo(id) {
            try {
                const response = await ApiService.get('/archivoclinico/' + id)
                this.detallearchivo = response.data
                console.log('detallearchivo:', this.detallearchivo)
            } catch (error) {
                console.error("error al cargar los datos:", error)
            }

            this.modalArchivoClinico = true

        },

        obtenerIcono(ruta){
            console.log('Ruta recibida:', ruta);
            if(!ruta){
                return 'fas fa-file-alt text-secondary';
            }
            const extension = ruta.split('.').pop().toLowerCase();
            switch(extension){
                case 'pdf':
                    return 'fas fa-file-pdf text-danger';
                case 'doc':
                case 'docx':
                    return 'fas fa-file-word text-primary';
                case 'xls':
                case 'xlsx':
                    return 'fas fa-file-excel text-success';
                case 'ppt':
                case 'pptx':
                    return 'fas fa-file-powerpoint text-warning';
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'webp':
                    return 'fas fa-file-image text-info';
                default:
                    return 'fas fa-file-alt text-secondary';
            }
        },


       

        //Función para determinar que tipo de archivo se mostrara mediante un modal//
        //  Corrección: los archivos que vienen del chat de Consulta IA tienen
        //  consulta_id y viven en el disco privado 'local', así que hay que
        //  pasar por la ruta de descarga del backend (consultaIA.descargarArchivo).
        //  Los subidos manualmente (sin consulta_id) viven en public/archivos_clinicos
        //  y se sirven con una URL directa.
        //  Además: la extensión real se saca de archivo_url (nombre guardado en BD),
        //  no de la URL final mostrada, porque la ruta de descarga no trae extensión.
        
        async verArchivo(documentos){
            if (!documentos || !documentos.archivo_url) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin archivo',
                    text: 'Este registro no tiene archivo asociado.'
                });
                return;
            }

            // Libera el blob anterior para no dejar memoria colgada
            if (this.archivoSeleccionado?.startsWith('blob:')) {
                URL.revokeObjectURL(this.archivoSeleccionado);
            }

            this.archivoExtension = documentos.archivo_url.split('.').pop().toLowerCase();
            this.cargandoArchivo = true;

            try {
                let ruta;

                if (documentos.consulta_id) {
                    // Viene del chat de Consulta IA: disco privado 'local'
                    ruta = `/consultaIA/archivo/${documentos.id}/descargar`;
                } else {
                    // Subido manualmente: disco público
                    ruta = documentos.archivo_url.startsWith('/')
                        ? documentos.archivo_url
                        : '/' + documentos.archivo_url;
                }

                // Pide el archivo como blob (ApiService manda el token de auth)
                const response = await ApiService.get(ruta, {
                    responseType: 'blob'
                });

                this.archivoSeleccionado = URL.createObjectURL(response.data);

                console.log('Blob URL generada:', this.archivoSeleccionado);

                $('#modalArchivo').modal('show');

            } catch (error) {
                console.error('Error al cargar archivo:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo abrir el archivo',
                    text: 'Intenta descargarlo directamente.'
                });
            } finally {
                this.cargandoArchivo = false;
            }
        },
        esImagen(){
            return ['jpg','jpeg','png','gif','webp'].includes(this.archivoExtension)
        },
        esPDF(){
            return this.archivoExtension === 'pdf'
        },

    },

    

}

</script>