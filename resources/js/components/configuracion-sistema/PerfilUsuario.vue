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

                <!-- NOMBRE DEL USUARIO DINÁMICO -->
                <h4 class="fw-bold mb-1">
                    {{ perfil.nombre || 'Cargando...' }}
                </h4>

                <p class="text-muted mb-3">
                    {{ perfil.especialidad || 'Sin especialidad' }}
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
                    <!-- CORREO -->
                    <p class="mb-3">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        {{ perfil.correo }}
                    </p>

                    <!-- ROL DINÁMICO -->
                    <p class="mb-3 text-capitalize">
                        <i class="fas fa-user-tag text-primary me-2"></i>
                        {{ perfil.rol || 'Sin rol' }}
                    </p>

                    <!-- FECHA DE REGISTRO -->
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
    },

    methods: {

        async obtenerPerfil() {
            try {
                // Obtiene los datos usando ApiService
                const response = await ApiService.get('perfil-usuario')

                // Llena el objeto perfil
                this.perfil = {
                    nombre: response.data.name,
                    correo: response.data.email,
                    especialidad: response.data.especialidad,
                    cedula: response.data.cedula_profesional,
                    rol: response.data.rol,
                    
                    // Formatea la fecha de registro
                    fechaRegistro: response.data.created_at 
                        ? new Date(response.data.created_at).toLocaleDateString('es-MX')
                        : ''
                }

            } catch (error) {
                console.error(error)
            }
        },

        async guardarPerfil() {
            try {
                // Envía los cambios al servidor usando ApiService
                await ApiService.put('/perfil-usuario', this.perfil)

                // Recarga los datos desde la BD
                await this.obtenerPerfil()

                Swal.fire({
                    icon: 'success',
                    title: 'Perfil actualizado',
                    timer: 2000,
                    showConfirmButton: false
                })

            } catch (error) {
                console.error(error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el perfil.'
                })
            }
        },

        abrirSelectorImagen() {
            if (this.$refs.inputFoto) {
                this.$refs.inputFoto.click()
            }
        },

        seleccionarFoto(event) {
            const archivo = event.target.files[0]
            if (archivo) {
                console.log('Imagen seleccionada:', archivo)
            }
        }
    }
}
</script>