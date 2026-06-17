<template>


    <!-- PERFIL -->
    <div class="col-lg-4">
        <div class="card shadow border-0 h-100">
            <div class="card-body text-center">

                <div class="mb-4">
                    <span class="badge bg-primary rounded-circle p-4">
                        <i class="fas fa-user-md fa-4x"></i>
                    </span>
                </div>

                <h4 class="fw-bold mb-1">
                    Dr. Gael 
                </h4>

                <p class="text-muted mb-3">
                    Médico General
                </p>

                <span class="badge bg-success mb-4">
                    Activo
                </span>

                <div class="d-grid">
                    <button
                        class="btn btn-outline-primary rounded-pill"
                        @click="abrirSelectorImagen"
                        type="button"
                    >
                        <i class="fas fa-camera me-2"></i>
                        Cambiar Foto
                    </button>

                    <input
                        ref="inputFoto"
                        type="file"
                        accept="image/*"
                        class="d-none"
                        @change="seleccionarFoto"
                    >
                </div>

                <hr>

                <div class="text-start">
                    <p class="mb-3">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        {{ perfil.correo }}
                    </p>

                    <p class="mb-3">
                        <i class="fas fa-user-tag text-primary me-2"></i>
                        Médico
                    </p>

                    <p class="mb-0">
                        <i class="fas fa-calendar text-primary me-2"></i>
                        
                        Registrado el: {{ perfil.fechaRegistro }}
                    </p>
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

            // Datos del perfil
            perfil: {

                nombre: '',
                correo: '',
                especialidad: '',
                cedula: '',
                rol: '',
                fechaRegistro: ''
            }
        }
    },

    async mounted() {

        // Carga la información del usuario al abrir la página
        await this.obtenerPerfil()

    

    console.log('MOUNTED EJECUTADO')

    

    },

    methods: {

        async obtenerPerfil() {

            try {

                // Obtiene los datos desde Laravel
                const response = await axios.get('perfil-usuario')
                console.log(response.data)

                // Llena el objeto perfil
                this.perfil = {

                    nombre: response.data.name,

                    correo: response.data.email,

                    especialidad: response.data.especialidad,

                    cedula: response.data.cedula_profesional,

                    rol: response.data.rol,

                    // Formatea la fecha de registro
                    fechaRegistro: new Date(
                        response.data.created_at
                    ).toLocaleDateString('es-MX')
                }

            } catch (error) {

                console.error(error)
            }
        },

        async guardarPerfil() {

        try {

        // Envía los cambios al servidor
            await axios.put(
                '/perfil-usuario',
                this.perfil
                )

            // Recarga los datos desde la BD
            await this.obtenerPerfil()

                alert(
                    'Perfil actualizado correctamente'
                )

                } catch (error) {

                console.error(error)
            }
        }
    }
}
</script>