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
                        <th>Folio</th> 
                        <th>Doctor</th>
                        <th>Especialidad</th>
                        <th>Horario</th>
                        <th>Días Laborales</th>
                        <th>Estado</th>
                        <th width="180">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <tr v-for="medico in medicos" :key="medico.medico_id">
                        
                        
                        <td>{{ medico.folio }}</td>

                        <!-- Nombre del Médico -->
                        <td>{{ medico.nombre }}</td>

                        <!-- Especialidad -->
                        <td>{{ medico.especialidad ? medico.especialidad.nombre : 'Sin especialidad' }}</td>

                        <!-- Horario Concatenado (Tomando la hora del primer y último horario como referencia) -->
                        <td>
                        {{ medico.horarios && medico.horarios.length 
                            ? `${medico.horarios[0].hora_inicio.substring(0, 5)} - ${medico.horarios[medico.horarios.length - 1].hora_fin.substring(0, 5)}` 
                            : 'Horario no definido' 
                        }}
                        </td>

                        <!-- Días Laborales (Mapeando el arreglo de horarios) -->
                        <td>
                        {{ medico.horarios && medico.horarios.length 
                            ? medico.horarios.map(h => {
                                const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                return dias[h.dia_semana] || 'Desconocido';
                            }).join(', ')
                            : 'Sin días de atención' 
                        }}
                        </td>


                        <td>
                            
                            <span class="badge badge-success">
                                Activo
                            </span>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-info text-white" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning text-white" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
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

import ApiService from '../../services/ApiService.js'

export default {

    data() {

        return {

            medicos: [],

            especialidades: [],

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

    },

    methods: {

        async obtenerMedicos() {

            try {

                const response = await ApiService.get('/medicos-horarios')
                this.medicos = response.data
                console.log('medico cargado:',this.medicos)
            } 
            catch (error) {

                console.error(
                    'Error al obtener medicos:',
                    error
                )
            }
        },


        async obtenerEspecialidades() {

            try {

                const response = await ApiService.get('especialidades')

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



    }

}


</script>