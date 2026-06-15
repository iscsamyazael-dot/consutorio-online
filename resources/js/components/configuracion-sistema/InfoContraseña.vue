<template>

<!-- Panel de Seguridad -->
    <div class="col-lg-4 mb-4">

        <div class="card shadow border-0 h-100">

            <div class="card-body text-center">

                <div class="mb-4">

                    <span class="badge bg-warning rounded-circle p-4">
                        <i class="fas fa-shield-alt fa-4x"></i>
                    </span>

                </div>

                <h4 class="fw-bold mb-2">
                    Seguridad de la Cuenta
                </h4>

                <p class="text-muted">
                    Mantén protegida tu cuenta actualizando
                    periódicamente tu contraseña.
                </p>

                <hr>

                <div class="text-start">

                    <p>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Cuenta Activa
                    </p>

                    <p>
                        <i class="fas fa-lock text-primary me-2"></i>
                        Contraseña Segura
                    </p>

                   <p class="mb-0">
                        <i class="fas fa-calendar text-warning me-2"></i>
                        Último cambio:
                          <!-- Formatea la fecha a dd/mm/yyyy -->
                        {{ perfil?.password_changed_at
                            ? perfil.password_changed_at.split(' ')[0].split('-').reverse().join('/')
                            :'No hay cambios'
                        }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</template>

<script>
// Importa Axios para realizar peticiones HTTP
import axios from 'axios'

export default {

    data() {
        return {
            perfil: {}
        }
    },

    async mounted() {

        try {
            
            // Obtiene los datos del usuario autenticado
            const response = await axios.get('/perfil-usuario')

            console.log(response.data)

            this.perfil = response.data

        } catch (error) {

            console.error(error)
        }
    }
}



</script>