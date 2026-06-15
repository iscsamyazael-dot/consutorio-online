<template>

    <!-- Formulario -->
    <div class="col-lg-8">

        <div class="card shadow border-0">

            <div class="card-body p-4">

                <h4 class="fw-bold text-primary mb-1">
                    Actualizar Contraseña
                </h4>

                <p class="text-muted mb-4">
                    Ingresa tu contraseña actual y define una nueva contraseña segura.
                </p>


                

    <!-- Contraseña Actual -->
    <div class="mb-4">

        <label class="form-label fw-semibold"> 
            Contraseña Actual
        </label>

        <div class="input-group">

            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>

            <input
                id="passwordActual"
                type="password"
                class="form-control"
                v-model="passwordActual"
                placeholder="Ingrese su contraseña actual"
            >

            <button
                type="button"
                class="btn btn-outline-secondary"
                @click="togglePassword('passwordActual', $event.currentTarget)">

                <i class="fas fa-eye"></i>

            </button>

        </div>

    </div>

    <!-- Nueva Contraseña -->
    <div class="mb-4">

        <label class="form-label fw-semibold">
            Nueva Contraseña
        </label>

        <div class="input-group">

            <span class="input-group-text">
                <i class="fas fa-key"></i>
            </span>

            <input
                id="nuevaPassword"
                type="password"
                class="form-control"
                v-model="nuevaPassword"
                placeholder="Ingrese la nueva contraseña"
                @keyup="evaluarPassword"
            >
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="togglePassword('nuevaPassword', $event.currentTarget)">
                    <i class="fas fa-eye"></i>
                </button>
        </div>
    </div>

    <!-- Confirmar Contraseña -->
    <div class="mb-4">
        <label class="form-label fw-semibold">
            Confirmar Contraseña
        </label>

        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-check-circle"></i>
            </span>
            <input
                id="confirmarPassword"
                type="password"
                class="form-control"
                v-model="confirmarPassword"
                placeholder="Confirme la nueva contraseña"
            >

            <button
                type="button"
                class="btn btn-outline-secondary"
                @click="togglePassword('confirmarPassword', $event.currentTarget)">

                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <!-- Fortaleza -->
    <div class="mb-4">

        <label class="form-label fw-semibold">
            Fortaleza de la Contraseña
        </label>

        <div class="progress">

            <div
                id="passwordStrength"
                class="progress-bar"
                role="progressbar"
                style="width: 0%">
            </div>

        </div>

        <small
            id="passwordStrengthText"
            class="text-muted">

            Ingrese una contraseña

        </small>

    </div>

    <!-- Requisitos -->
    

            <h6 class="fw-bold text-primary">

                <i class="fas fa-shield-alt me-2"></i>
                Requisitos de Seguridad

            </h6>

            <ul class="list-unstyled mb-0">

                <li id="reqLength">
                    <i class="fas fa-times text-danger me-2"></i>
                    Mínimo 8 caracteres
                </li>

                <li id="reqUpper">
                    <i class="fas fa-times text-danger me-2"></i>
                    Al menos una letra mayúscula
                </li>

                <li id="reqNumber">
                    <i class="fas fa-times text-danger me-2"></i>
                    Al menos un número
                </li>

                <li id="reqSpecial">
                    <i class="fas fa-times text-danger me-2"></i>
                    Al menos un carácter especial
                </li>

            </ul>

       

    <!-- Botones -->
    <div class="text-end">

        <button
            type="reset"
            class="btn btn-outline-secondary rounded-pill px-4 me-2">

            <i class="fas fa-times me-2"></i>
            Cancelar

        </button>

        <button
            type="button"
            class="btn btn-primary btn-lg rounded-pill px-5"
            @click="actualizarPassword">

            <i class="fas fa-save me-2"></i>
            Actualizar Contraseña

        </button>
    </div>  
            
        </div>
    </div>
    </div>
</template>


<script>
import axios from 'axios'


export default {

    data() {
        return {
            passwordActual: '',
            nuevaPassword: '',
            confirmarPassword: '',
            
            
        }
    },

    methods: {
            //fUNCION PARA ACTUALIZAR LA CONTRASEÑA
        async actualizarPassword() {

            try {
                
                console.log({
                    current_password: this.passwordActual,
                    password: this.nuevaPassword,
                    password_confirmation: this.confirmarPassword
                });

                const response = await axios.post(
                    '/cambiar-password',
                    {
                        current_password: this.passwordActual,
                        password: this.nuevaPassword,
                        password_confirmation: this.confirmarPassword
                    }
                )

                alert(response.data.message)

                this.passwordActual = ''
                this.nuevaPassword = ''
                this.confirmarPassword = ''

            } catch (error) {

                alert(
                    error.response?.data?.message ||
                    'Ocurrió un error al actualizar la contraseña'
                )
            }
        },
        //FUNCION VER CONTRASEÑA
        togglePassword(inputId, button) {

            const input = document.getElementById(inputId);

            const icon = button.querySelector('i');

            if (input.type === 'password') {

                input.type = 'text';

                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');

            } else {

                input.type = 'password';

                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');

            }
        },
        //funcion de requisitos
        actualizarRequisito(id, cumple) {

            const elemento = document.getElementById(id);

            if (cumple) {

                elemento.innerHTML =
                    elemento.innerHTML.replace(
                        'fa-times text-danger',
                        'fa-check text-success'
                    );

            } else {

                elemento.innerHTML =
                    elemento.innerHTML.replace(
                        'fa-check text-success',
                        'fa-times text-danger'
                    );

            }
        },
        //FUNCION PARA VERIFICAR QUE CUMPLA CON LOS REQUISITOS 
        evaluarPassword() {

            const password = this.nuevaPassword;

            let score = 0;

            const tieneLongitud = password.length >= 8;
            const tieneMayuscula = /[A-Z]/.test(password);
            const tieneNumero = /\d/.test(password);
            const tieneEspecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            this.actualizarRequisito('reqLength', tieneLongitud);
            this.actualizarRequisito('reqUpper', tieneMayuscula);
            this.actualizarRequisito('reqNumber', tieneNumero);
            this.actualizarRequisito('reqSpecial', tieneEspecial);

            if (tieneLongitud) score++;
            if (tieneMayuscula) score++;
            if (tieneNumero) score++;
            if (tieneEspecial) score++;

            const barra =
                document.getElementById('passwordStrength');

            const texto =
                document.getElementById('passwordStrengthText');

            switch(score) {

                case 1:
                    barra.style.width = '25%';
                    barra.className = 'progress-bar bg-danger';
                    texto.textContent = 'Contraseña débil';
                    break;

                case 2:
                    barra.style.width = '50%';
                    barra.className = 'progress-bar bg-warning';
                    texto.textContent = 'Contraseña regular';
                    break;

                case 3:
                    barra.style.width = '75%';
                    barra.className = 'progress-bar bg-info';
                    texto.textContent = 'Contraseña buena';
                    break;

                case 4:
                    barra.style.width = '100%';
                    barra.className = 'progress-bar bg-success';
                    texto.textContent = 'Contraseña muy segura';
                    break;

                default:
                    barra.style.width = '0%';
                    barra.className = 'progress-bar';
                    texto.textContent = 'Ingrese una contraseña';
            }
        }
    }
}
</script>