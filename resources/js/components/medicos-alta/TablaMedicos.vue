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
                            v-model="buscar"
                            type="text" 
                            class="form-control" 
                            placeholder="Buscar doctor..." 
                        />
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive card-body p-0">
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
                    
                    <tr v-for="medico in medicosFiltrados" :key="medico.id">
                    
                        <td>{{ medico.folio }}</td>
                        
                        <td>{{ medico.nombre }}</td>

                        <td>{{ medico.especialidad ? medico.especialidad.nombre : 'Sin especialidad' }}</td>

                        <!-- Horario Concatenado (Tomando la hora del primer y último horario como referencia) -->
                        <td>
                        {{ medico.horarios && medico.horarios.length 
                            ? `${medico.horarios[0].hora_inicio.substring(0, 5)} - ${medico.horarios[medico.horarios.length - 1].hora_fin.substring(0, 5)}` 
                            : 'Horario no definido' 
                        }}
                        </td>

                             <!-- Días Laborales (Mapeando el arreglo de horarios) -->
                            <td class="align-middle">
                                <!-- El secreto: un contenedor con ancho máximo que obliga al contenido a saltar de línea -->
                                <div style="max-width: 180px; width: 100%;">
                                    <div class="d-flex flex-wrap gap-1">
                                        <!-- Convertimos el string largo en elementos individuales usando .split() -->
                                        <span 
                                            v-for="(dia, index) in medico.dias_laborales?.split(', ')" 
                                            :key="index" 
                                            class="badge bg-light text-dark border fw-normal"
                                            style="font-size: 0.85rem;"
                                            >
                                            {{ dia }}
                                        </span>
                                    </div>
                                </div>
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
                            
                            <button  
                                class="btn btn-sm btn-warning text-white" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEditarMedico"
                                @click="prepararEditar(medico)"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button 
                                class="btn btn-sm btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEliminarMedico"
                                @click=""
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>






<!--MODAL ELIMINAR MEDICO-->

<div class="modal fade" id="modalEliminarMedico" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 2.5rem;"></i>
        <p class="fs-5 mb-1">¿Realmente quieres eliminar a este médico?</p>
        <p class="text-muted fw-bold">{{ medicoSeleccionado.nombre }}</p>
      </div>
      <div class="modal-footer d-flex justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger px-4" @click="confirmarEliminar">Eliminar</button>
      </div>
    </div>
  </div>
</div>



</template>

<script>

import ApiService from '../../services/ApiService.js'

export default {

    data() {

        return {
                
            medicoSeleccionado: {
                folio: '',
                nombre: '',
                especialidad: '',
                horario: '',
                dias_laborales: '',
                estado: ''
            },
            buscar: '',    // Vinculado al input de texto
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


    computed: {
        // Esta función filtra localmente los médicos de la tabla en tiempo real
        medicosFiltrados() {
            // Si el input está vacío, muestra la lista completa de la tabla
            if (!this.buscar.trim()) {
                return this.medicos;
            }
            const query = this.buscar.toLowerCase();
            // Filtra sobre el array de la memoria local
            return this.medicos.filter(medico => {
                return medico.nombre && medico.nombre.toLowerCase().includes(query);
            });
        }
    },
                


    mounted() {

        this.obtenerEspecialidades();
        this.obtenerMedicos();
        this.buscarMedico();

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

        async buscarMedico() {
            // Si borran el buscador, puedes traer todos los médicos de nuevo o vaciar
            if (!this.buscar.trim()) {
                // Opcional: puedes llamar a tu método inicial para listar todo
                 this.getMedicos(); 
            }
            
            try {
                const response = await ApiService.get('/buscarMedico?buscar=' + this.buscar);
                this.medicos = response.data;
            } catch (error) {
                console.error('No se encuentran resultados', error);
            }
        },



    }

}


</script>