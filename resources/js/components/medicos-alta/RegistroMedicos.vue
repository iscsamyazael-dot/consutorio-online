<template>

    <div class="row mt-4 mb-4 ">
        <div class="col-12 text-center text-md-left d-md-flex align-items-center justify-content-between header-custom-container p-4 rounded shadow-sm bg-white">
            <div>
                <h1 class="font-weight-black text-dark mb-1 tracking-tight">
                    <span class="badge badge-primary-gradient p-2 mr-2 rounded-lg">
                        <i class="fas fa-user-md text-white animate-pulse"></i>
                    </span> 
                    Registrar Nuevo Médico
                </h1>
                <p class="text-muted mb-0 ml-md-5 pl-md-2 font-weight-light">Alta y asignación de horarios para el personal de salud.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <button @click="$emit('volver')" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm text-white font-weight-bold" style="background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%); border: none;">
                    <i class="fas fa-chevron-left mr-2"></i>
                    Regresar a Gestión
                </button>
            </div>
        </div>
    </div>


<div class="container-fluid py-4">

    <form @submit.prevent="guardarMedico">

        <div class="row">

            <!-- COLUMNA IZQUIERDA -->
            <div class="col-lg-8">

                    <!-- INFORMACIÓN PERSONAL -->
                    <div class="card card-custom shadow-lg mb-4 border-0 overflow-hidden">
                        <div class="card-decor-line bg-primary-gradient"></div>

                        <div class="card-body p-4">

                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-shape bg-primary-light text-primary rounded-circle mr-3">
                                    <i class="fas fa-id-card fa-lg"></i>
                                </div>
                                <h4 class="mb-0 font-weight-bold text-dark-blue">
                                    Información Personal
                                </h4>
                            </div>

                            <div class="row">

                                <div class="col-md-6 mb-4">
                                    <label class="form-label-custom">
                                        Nombre Completo
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group-custom">
                                        <i class="fas fa-user input-icon"></i>

                                        <input
                                            type="text"
                                            class="form-control-custom"
                                            placeholder="Ej. Dr. Alejandro Ríos"
                                            v-model="form.nombre"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- INFORMACIÓN PROFESIONAL -->
                    <div class="card card-custom shadow-lg mb-4 border-0 overflow-hidden">

                        <div class="card-decor-line bg-success-gradient"></div>

                        <div class="card-body p-4">

                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-shape bg-success-light text-success rounded-circle mr-3">
                                    <i class="fas fa-graduation-cap fa-lg"></i>
                                </div>

                                <h4 class="mb-0 font-weight-bold text-dark-blue">
                                    Información Profesional
                                </h4>
                            </div>

                            <div class="row">

                                <div class="col-md-6 mb-4">

                                    <label class="form-label-custom">
                                        Cédula Profesional
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group-custom">
                                        <i class="fas fa-file-medical input-icon"></i>

                                        <input
                                            type="text"
                                            class="form-control-custom"
                                            placeholder="Ingrese número de cédula"
                                            v-model="form.cedula_profesional"
                                            required
                                        >
                                    </div>

                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label-custom">
                                        Especialidad Médica
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="fas fa-stethoscope input-icon"></i>
                                        <select
                                            class="form-control-custom select-custom"
                                            v-model="form.especialidad"
                                            required
                                        >
                                            <option disabled value="">
                                                Seleccione una especialidad...
                                            </option>
                                            <option
                                                v-for="especialidad in especialidades"
                                                :key="especialidad.id"
                                                :value="especialidad.id"
                                            >
                                                {{ especialidad.nombre }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="card card-custom mt-4">
                    <div class="card-decor-line bg-warning-gradient rounded-top"></div>
                    
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-shape bg-warning-light rounded-circle text-warning me-3">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <h5 class="m-0 font-weight-bold text-dark-blue">Información de la Consulta</h5>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label-custom">
                                    Costo de la Consulta
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group-custom">
                                    <i class="fas fa-dollar-sign input-icon"></i>
                                    <input
                                        type="number"
                                        class="form-control-custom"
                                        v-model.number="form.costo_consulta"
                                        placeholder="Ej. 450.00"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label-custom">
                                    Sucursal / Ubicación
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group-custom">
                                    <i class="fas fa-hospital input-icon"></i>
                                    <select
                                        class="form-control-custom select-custom"
                                        v-model="form.ubicacion_id"
                                        required
                                    >
                                        <option disabled value="">
                                            Seleccione una sucursal...
                                        </option>
                                        <option
                                            v-for="sucursal in sucursales"
                                            :key="sucursal.id"
                                            :value="sucursal.id"
                                        >
                                            {{ sucursal.nombre }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            
        

            <!-- COLUMNA DERECHA -->
            <div class="col-lg-4">

                <div class="card card-custom shadow-lg mb-4 border-0 overflow-hidden h-100">

                    <div class="card-decor-line bg-warning-gradient"></div>

                    <div class="card-body p-4 d-flex flex-column justify-content-between">

                        <div>

                            <div class="d-flex align-items-center mb-4">

                                <div class="icon-shape bg-warning-light text-warning rounded-circle mr-3">
                                    <i class="fas fa-calendar-alt fa-lg"></i>
                                </div>

                                <h4 class="mb-0 font-weight-bold text-dark-blue">
                                    Disponibilidad
                                </h4>

                            </div>

                            <div class="mb-4">
                                <label class="form-label-custom">
                                    Hora de Entrada
                                </label>
                                <div class="input-group-custom">
                                    <i class="fas fa-clock input-icon text-warning"></i>
                                    <input
                                        type="time"
                                        class="form-control-custom"
                                        v-model="form.hora_entrada"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mb-4">

                                <label class="form-label-custom">
                                    Hora de Salida
                                </label>

                                <div class="input-group-custom">
                                    <i class="fas fa-history input-icon text-warning"></i>

                                    <input
                                        type="time"
                                        class="form-control-custom"
                                        v-model="form.hora_salida"
                                        required
                                    >
                                </div>

                            </div>

                            

                            <div class="mb-4">
                                <label class="form-label-custom">Duración de la Consulta <span class="text-danger">*</span></label>
                                <div class="input-group-custom">
                                    <input type="number" 
                                        name="duracion_consulta" 
                                        class="form-control-custom" 
                                        placeholder="Ej. 30" 
                                        min="5"
                                         v-model="form.duracion_consulta" 
                                        required>
                                    <i class="fas fa-hourglass-half input-icon"></i>
                                    
                                    <span class="input-suffix">Minutos</span>
                                </div>
                            </div>

                            <hr class="my-4 border-light">


                            <!-- DÍAS LABORALES -->

                           <div class="days-grid-selector">

                                <label
                                    v-for="dia in dias"
                                    :key="dia.nombre"
                                    class="day-btn-checkbox"
                                >

                                    <input
                                        type="checkbox"
                                        :value="dia.nombre"
                                        v-model="form.dias_laborales"
                                    >

                                    <span
                                        class="day-box"
                                        :title="dia.nombre"
                                    >
                                        {{ dia.inicial }}
                                    </span>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2 text-center">
                                Presione los días para activarlos
                            </small>
                        </div>
                        <div class="mt-5 pt-4 border-top border-light">
                            <button
                                type="submit"
                                class="btn btn-primary-gradient btn-block btn-lg rounded-pill shadow mb-2"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Guardar Registro
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- TOAST -->

    <div
        v-if="toastSuccess"
        class="position-fixed p-3"
        style="z-index: 9999; right: 20px; top: 20px; min-width: 300px;"
    >

        <div
            class="toast show border-0 shadow-lg text-white"
            style="background: linear-gradient(135deg, #00bfa5 0%, #00897b 100%); border-radius: 12px;"
        >

            <div class="d-flex align-items-center px-3 py-3">

                <div
                    class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                    style="background-color: rgba(255,255,255,.2); width:30px; height:30px;"
                >
                    <i class="fas fa-check-circle text-white"></i>
                </div>

                <div class="toast-body p-0 font-weight-bold flex-grow-1">
                    {{ mensajeSuccess }}
                </div>

                <button
                    type="button"
                    class="close text-white"
                    @click="toastSuccess = false"
                >
                    <span>&times;</span>
                </button>

            </div>

        </div>

    </div>

</div>

</template>

<script>

import ApiService from '../../services/ApiService.js'

export default {

        
    // ESTA LÍNEA SIRVE PARA AVISARLE A VUE QUE ESTE EVENTO ES MÍO
    emits: ['volver'],

    data() {

        return {

            medicos: [],
            especialidades: [],
            sucursales: [],


            form: {

                nombre: '',
                cedula_profesional: '',
                especialidad: '',
                hora_entrada: '',
                hora_salida: '',
                duracion_consulta: '',
                ubicacion_id: '',       
                costo_consulta: '',
                dias_laborales: []
                

            },

            

            dias: [

                { nombre: 'Lunes', inicial: 'L' },
                { nombre: 'Martes', inicial: 'MA' },
                { nombre: 'Miércoles', inicial: 'MI' },
                { nombre: 'Jueves', inicial: 'J' },
                { nombre: 'Viernes', inicial: 'V' },
                { nombre: 'S' , inicial: 'S' },
                { nombre: 'Domingo', inicial: 'D' }

            ],

            toastSuccess: false,
            mensajeSuccess: ''

        }

    },
 
    mounted() {

        this.obtenerEspecialidades();
        this.obtenerMedicos();
        this.obtenerSucursales();

    },

    methods: {

        async obtenerMedicos() {

            try {

                const response = await ApiService.get('/medicos/')
                this.medicos = response.data
                console.log(
                    'Medicos cargados:',
                    this.medicos
                )
            } catch (error) {

                console.error(
                    'Error al obtener medicos:',
                    error
                )
            }
        },


        async obtenerEspecialidades() {

            try {

                const response = await ApiService.get('/especialidades/')

                this.especialidades = response.data

                console.log(
                    'Especialidades cargadas:',
                    this.especialidades
                )

            } catch (error) {

                console.error(
                    'Error al obtener especialidades:',
                    error
                )

            }
 
        },

        async obtenerSucursales() {
            try {
                // Hacemos la petición a la ruta de ubicaciones/sucursales
                const response = await ApiService.get('/ubicaciones/list')
                
                // Guardamos los datos en tu array del data()
                this.sucursales = response.data
                
                console.log(
                    'Sucursales cargadas:',
                    this.sucursales
                )
            } catch (error) {
                console.error(
                    'Error al obtener sucursales:',
                    error
                )
            }
        },


        

        async guardarMedico() {

            try {

                const response = await ApiService.post('/medicos',this.form)

                console.log('Médico registrado:', response.data)

                this.mensajeSuccess =
                    response.data.message ||
                    'Médico registrado correctamente'

                this.toastSuccess = true

                this.limpiarFormulario()

                setTimeout(() => {

                    this.toastSuccess = false

                }, 4000)

            } catch (error) {

                console.error(
                    'Error al registrar médico:',
                    error
                )

                if (error.response) {

                    console.error(
                        'Errores:',
                        error.response.data
                    )

                }

            }

        },

        limpiarFormulario() {

            this.form = {

                nombre: '',
                cedula_profesional: '',
                especialidad: '',
                hora_entrada: '',
                hora_salida: '',
                dias_laborales: []

            }

        }

    }

}


</script>







<style>

    @keyframes slideInMedico {
        from {
          transform: translateX(120%);
          opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* VARIABLES DE COLOR MODERNAS */
    :root {
        --primary-gradient: linear-gradient(135deg, #0061f2 0%, #00ba94 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --bg-light-blue: #f8faff;
        --dark-blue: #1e293b;
    }

    body {
        background-color: var(--bg-light-blue) !important;
    }

    /* Fuentes y Estilo General */
    .font-weight-black { font-weight: 900; }
    .text-dark-blue { color: var(--dark-blue); }
    
    /* Contenedor del header animado */
    .header-custom-container {
        border-left: 5px solid #0061f2;
        background: #ffffff;
    }

    .badge-primary-gradient {
        background: var(--primary-gradient);
    }

    /* TARJETAS PREMIUM (Cards) */
    .card-custom {
        border-radius: 16px !important;
        background: #ffffff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 1rem 3rem rgba(31, 45, 65, 0.08) !important;
    }
    .card-decor-line {
        height: 5px;
        width: 100%;
    }
    .bg-primary-gradient { background: var(--primary-gradient); }
    .bg-success-gradient { background: var(--success-gradient); }
    .bg-warning-gradient { background: var(--warning-gradient); }

    /* ICONOS DE SECCIÓN */
    .icon-shape {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .bg-primary-light { background-color: rgba(0, 97, 242, 0.1); }
    .bg-success-light { background-color: rgba(16, 185, 129, 0.1); }
    .bg-warning-light { background-color: rgba(245, 158, 11, 0.1); }

    /* INPUTS TOTALMENTE RESTRUCTURADOS UI/UX */
    .form-label-custom {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .input-group-custom {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-control-custom {
        width: 100%;
        padding: 14px 16px 14px 45px;
        font-size: 0.95rem;
        background-color: #f1f5f9;
        border: 2px solid transparent;
        border-radius: 12px;
        color: #334155;
        transition: all 0.25s ease;
    }

    .form-control-custom:focus {
        background-color: #ffffff;
        border-color: #0061f2;
        box-shadow: 0 0 0 4px rgba(0, 97, 242, 0.15);
        outline: none;
    }

    .input-icon {
        position: absolute;
        left: 16px;
        color: #94a3b8;
        font-size: 1.1rem;
        transition: color 0.25s ease;
    }

    .form-control-custom:focus + .input-icon {
        color: #0061f2;
    }

    .select-custom {
        appearance: none;
        cursor: pointer;
    }

    /* BOTONES INTERACTIVOS PARA DÍAS DE LA SEMANA (Estilo App Móvil) */
    .days-grid-selector {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
    }

    .day-btn-checkbox input[type="checkbox"] {
        display: none;
    }

    .day-btn-checkbox .day-box {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        border-radius: 10px;
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        user-select: none;
    }

    .day-btn-checkbox input[type="checkbox"]:checked + .day-box {
        background: var(--primary-gradient);
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 97, 242, 0.3);
        transform: scale(1.05);
    }

    .day-btn-checkbox:hover .day-box {
        background-color: #e2e8f0;
    }

    /* BOTONES PREMIUM GRADIENTES */
    .btn-primary-gradient {
        background: var(--primary-gradient);
        color: white;
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    .btn-primary-gradient:hover {
        opacity: 0.95;
        box-shadow: 0 6px 20px rgba(0, 97, 242, 0.4) !important;
        transform: translateY(-1px);
        color: white;
    }

    /* Animaciones */
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }
    .animate-pulse {
        animation: pulse 2s infinite ease-in-out;
    }

    /* SUFIJO PARA EL INPUT DE DURACIÓN (MINUTOS) */
    .input-group-custom .input-suffix {
        position: absolute;
        right: 43px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5 px;
        pointer-events: none; 
    }

    /* AJUSTES ESPECÍFICOS PARA EL INPUT DE PRECIO */
    .input-group-custom .input-icon-price {
        position: absolute;
        left: 16px;
        color: #94a3b8;
        font-size: 1.1rem;
        font-weight: 700;
        transition: color 0.25s ease;
        pointer-events: none;
    }

    /* Cambia el color del $ al enfocar el input de precio */
    .form-control-price:focus + .input-icon-price {
        color: #0061f2;
    }

    /* Margen izquierdo extra exclusivo para el input de precio para que no choque con el $ */
    .form-control-price {
        padding-left: 35px !important; 
    }



</style>



    
