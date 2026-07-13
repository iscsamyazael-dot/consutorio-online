<template>

<div class="col-lg-4">

    <!-- TITULO -->
    <div class="mb-4 page-title">

        <h1 class="fw-bold text-dark">
            <i class="fas fa-stethoscope text-primary"></i>
            Nueva Consulta Médica
        </h1>

        <small class="text-muted">
            Panel clínico avanzado
        </small>

    </div>

    <!-- PANEL IZQUIERDO -->
    <div class="fixed-panel">

        <!-- CARD PERFIL -->
        <div class="card side-card border-0 shadow-lg rounded-4">

            <div class="card-body text-center p-5">

                <div class="patient-avatar mx-auto mb-4">
                    <i class="fas fa-user-injured"></i>
                </div>

                <h4 class="fw-bold">
                    Consulta General
                </h4>

                <p class="side-subtitle">
                    Registro clínico inteligente
                </p>

                <div class="status-box mt-4">

                    <div class="status-item">
                        <span class="status-dot bg-success"></span>
                        Consulta activa
                    </div>

                    <div class="status-item">
                        <span class="status-dot bg-warning"></span>
                        Prioridad moderada
                    </div>

                </div>

            </div>

        </div>


        <!-- CARD INFO -->
        <div class="card border-0 info-card mt-4 shadow-sm rounded-4">

            <div class="card-body">

                <h5 class="fw-bold mb-4">
                    <i class="fas fa-chart-line text-primary"></i>
                    Información rápida
                </h5>

                <div class="quick-info">

                    <div class="quick-item">

                        <i class="fas fa-user-md text-primary"></i>

                        <div>
                            <small>Médico</small>
                            <h6>{{ medico }}</h6>
                        </div>

                    </div>

                    <div class="quick-item">

                        <i class="fas fa-calendar text-success"></i>

                        <div>
                            <small>Fecha</small>
                            <h6>{{ fecha }}</h6>
                        </div>

                    </div>

                    <div class="quick-item">

                        <i class="fas fa-heartbeat text-danger"></i>

                        <div>
                            <small>Estado</small>
                            <h6>{{ estado }}</h6>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</template>


<script>

export default {

    name: 'NuevaConsulta',

    data() {
        return {
            medico: 'Dr. Martínez',
            fecha: '22 Mayo 2026',
            estado: 'En evaluación',
            guardando: false,
            form: {
                motivo: '',
                diagnostico: '',
                peso: '',
                presion: '',
                observaciones: ''
            },
            errores: {
                motivo: '',
                diagnostico: '',
                peso: '',
                presion: ''
            }
        }
    },

    methods: {

        validarFormulario() {

            this.errores = {
                motivo: '',
                diagnostico: '',
                peso: '',
                presion: ''
            }

            let valido = true

            if (!this.form.motivo.trim()) {
                this.errores.motivo = 'El motivo de consulta es obligatorio.'
                valido = false
            }

            if (!this.form.diagnostico.trim()) {
                this.errores.diagnostico = 'El diagnóstico es obligatorio.'
                valido = false
            }

            if (this.form.peso && Number(this.form.peso) <= 0) {
                this.errores.peso = 'El peso debe ser mayor a 0.'
                valido = false
            }

            if (this.form.presion && !/^\d{2,3}\/\d{2,3}$/.test(this.form.presion)) {
                this.errores.presion = 'Formato esperado: 120/80.'
                valido = false
            }

            return valido

        },

        async guardarConsulta() {

            if (!this.validarFormulario()) {
                return
            }

            this.guardando = true

            try {

                // Reemplazar con la llamada real a la API
                await this.$emit('guardar', { ...this.form })

            } catch (error) {

                console.error('Error al guardar la consulta:', error)

            } finally {

                this.guardando = false

            }

        },

        cancelarConsulta() {

            this.form = {
                motivo: '',
                diagnostico: '',
                peso: '',
                presion: '',
                observaciones: ''
            }

            this.errores = {
                motivo: '',
                diagnostico: '',
                peso: '',
                presion: ''
            }

            this.$emit('cancelar')

        }

    }

}

</script>


<style scoped>

.rounded-4 {
    border-radius: 24px !important;
}

.page-title h1 {
    font-size: 2rem;
}

.fixed-panel {
    position: sticky;
    top: 20px;
}

.side-card {
    background: linear-gradient(135deg, #0d6efd, #3c8dff) !important;
    color: #fff !important;
}

.side-card * {
    color: #fff !important;
}

.patient-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
}

.side-subtitle {
    opacity: .85;
}

.status-box {
    background: rgba(255,255,255,0.1);
    border-radius: 18px;
    padding: 16px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.info-card {
    background: #fff;
}

.quick-item {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
}

.quick-item i {
    font-size: 24px;
}

.form-control {
    border-radius: 14px;
    padding: 12px;
}

textarea.form-control {
    resize: none;
}

@media (max-width: 768px) {

    .fixed-panel {
        position: static;
    }

    .page-title h1 {
        font-size: 1.5rem;
    }

}

</style>