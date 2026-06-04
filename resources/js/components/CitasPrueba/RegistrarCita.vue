<template>
        <form action="" method="">
            <div class="form-grid">
                <!-- PACIENTE -->
                <div class="form-group full">
                    <label>Paciente</label>
                    <div class="input-modern">
                        <i class="fas fa-user"></i>
                        <input
                            type="text"
                            name="NombrePaciente"
                            placeholder = "Escribe el nombre del paciente"
                            v-model="paciente.nombre">
                    </div>
                </div>
                <div class="form-group full">
                    <label>Telefono</label>
                    <div class="input-modern">
                        <i class="fas fa-user"></i>
                        <input
                            type="text"
                            name="Telefono"
                            placeholder = "Escribe el telefono del paciente"
                            v-model="paciente.telefono">
                    </div>
                </div>
                <!-- FECHA -->
                <div class="form-group">
                    <label>Fecha</label>
                    <div class="input-modern">
                        <i class="fas fa-calendar"></i>
                        <input
                            type="date"
                            name="fecha_cita"
                            required
                            v-model="paciente.fecha">
                    </div>
                </div>

                <!-- HORA -->
                <div class="form-group">
                    <label>Hora</label>
                    <div class="input-modern">
                        <i class="fas fa-clock"></i>
                        <input
                            type="time"
                            name="hora_cita"
                            required
                            v-model="paciente.hora"
                        >
                    </div>
                </div>
                <!-- ESTADO -->
                <div class="form-group">
                    <label>Estado</label>
                    <div class="input-modern">
                        <i class="fas fa-check-circle"></i>
                        <select name="estado" v-model="paciente.estado">
                            <option value="Pendiente">
                                Pendiente
                            </option>
                            <option value="Completada">
                                Completada
                            </option>
                            <option value="Cancelada">
                                Cancelada
                            </option>
                        </select>
                    </div>
                </div>

                <!-- OBSERVACIONES -->
                <div class="form-group full">
                    <label>Observaciones</label>
                    <textarea
                        name="observaciones"
                        placeholder="Agregar observaciones médicas..."
                        v-model="paciente.observaciones">
                    </textarea>
                </div>
            </div>
        </form>
        <!-- ACTIONS -->
            <div class="actions">
                <a href="" class="btn-cancel">
                    Cancelar
                </a>
                <button type="submit" class="btn-save" @click="guardarCitaPaciente()">
                    <i class="fas fa-save"></i>
                    Guardar Consulta
                </button>
            </div>
</template>

<script>
    import ApiService from '../../services/ApiService.js'
    export default{
        data(){
            return{
                paciente:{
                    nombre:'',
                    telefono:'',
                    fecha:'',
                    hora:'',
                    estado:'',
                    observaciones:''
                }
            }
        },
        mounted(){
            
        },
        methods:{
            //Función para guardar datos a la tabla de movimientos inventario y para actualizar los datos del inventario//
            async guardarCitaPaciente(){
                try {
                    const response = await ApiService.post('/citasPrueba',this.paciente)
                    console.log(response.data)
                    Swal.fire({
                        icon: 'success',
                        title: 'Registro de Citas',
                        text: 'La Cita fue registrada Exitosamente',
                        confirmButtonText: 'Aceptar'
                    })
                    //Sive para enviar los datos otro componente usando la función $emit//
                    this.paciente = {
                        nombre:'',
                        telefono:'',
                        fecha:'',
                        hora:'',
                        estado:'',
                        observaciones:''
                    }
                }
                catch(error){
                    console.error(error)
                }
            },
        },
    }
</script>