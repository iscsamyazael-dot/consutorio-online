<template>

<div class="col-lg-8">

    <div class="form-scroll">

        <form @submit.prevent="guardarConsulta">

            <!-- CARD PACIENTE -->
            <div class="card main-card border-0 mb-4 animated-section">

                <div class="card-body p-5">
                    <div class="mb-5">
                        <h4 class="section-title">
                            <i class="fas fa-user text-primary"></i>
                            Información del Paciente
                        </h4>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                Paciente
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-user"></i>
                                <input
                                    type="text"
                                    class="form-control custom-input with-icon"
                                    placeholder="Nombre del paciente"
                                    v-model="infoPacientes.nombre"
                                >

                            </div>

                            <div class="invalid-feedback">
                            </div>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Doctor
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-user-md"></i>
                                <input
                                    type="text"
                                    class="form-control custom-input with-icon"
                                    placeholder="Doctor responsable"
                                    v-model="infoPacientes.doctor"
                                >
                            </div>
                            <div class="invalid-feedback">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Fecha
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-calendar-alt"></i>
                                <input
                                    type="date"
                                    class="form-control custom-input with-icon"
                                    :class="{ 'is-invalid': errores.fecha }"
                                    v-model="form.fecha"
                                >
                            </div>
                            <div class="invalid-feedback" v-if="errores.fecha">
                                {{ errores.fecha }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Hora
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-clock"></i>
                                <input
                                    type="time"
                                    class="form-control custom-input with-icon"
                                    :class="{ 'is-invalid': errores.hora }"
                                    v-model="form.hora"
                                >
                            </div>
                            <div class="invalid-feedback" v-if="errores.hora">
                                {{ errores.hora }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD DATOS DE LA CONSULTA -->
            <div class="card main-card border-0 mb-4 animated-section delay-1">
                <div class="card-body p-5">
                    <div class="mb-5">
                        <h4 class="section-title">
                            <i class="fas fa-clipboard-list text-primary"></i>
                            Datos de la Consulta
                        </h4>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                Motivo de consulta
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-comment-medical"></i>
                                <input
                                    type="text"
                                    class="form-control custom-input with-icon"
                                    :class="{ 'is-invalid': errores.motivo }"
                                    placeholder="Ingrese motivo"
                                    v-model="form.motivo"
                                >
                            </div>
                            <div class="invalid-feedback" v-if="errores.motivo">
                                {{ errores.motivo }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Diagnóstico
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-stethoscope"></i>
                                <input
                                    type="text"
                                    class="form-control custom-input with-icon"
                                    :class="{ 'is-invalid': errores.diagnostico }"
                                    placeholder="Ingrese diagnóstico"
                                    v-model="form.diagnostico"
                                >
                            </div>
                            <div class="invalid-feedback" v-if="errores.diagnostico">
                                {{ errores.diagnostico }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Peso (kg)
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-weight"></i>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    class="form-control custom-input with-icon"
                                    :class="{ 'is-invalid': errores.peso }"
                                    placeholder="Ej. 70"
                                    v-model="form.peso"
                                >
                            </div>
                            <div class="invalid-feedback" v-if="errores.peso">
                                {{ errores.peso }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Presión arterial
                            </label>
                            <div class="input-icon-box">
                                <i class="fas fa-heartbeat"></i>
                                <input
                                    type="text"
                                    class="form-control custom-input with-icon"
                                    :class="{ 'is-invalid': errores.presion }"
                                    placeholder="120/80"
                                    v-model="form.presion"
                                >
                            </div>
                            <div class="invalid-feedback" v-if="errores.presion">
                                {{ errores.presion }}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Observaciones
                            </label>
                            <div class="textarea-icon-box">
                                <i class="fas fa-edit"></i>
                                <textarea
                                    class="form-control custom-textarea with-icon"
                                    rows="4"
                                    placeholder="Observaciones médicas..."
                                    v-model="form.observaciones"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD EVALUACION CLINICA -->
            <div class="card main-card border-0 animated-section delay-2">
                <div class="card-body p-5">
                    <div class="mb-5">
                        <h4 class="section-title">
                            <i class="fas fa-file-medical text-danger"></i>
                            Evaluación Clínica
                        </h4>
                    </div>
                    <!-- SINTOMAS -->
                    <div class="mb-4">
                        <label class="form-label">
                            Síntomas
                        </label>
                        <div class="textarea-icon-box">
                            <i class="fas fa-notes-medical"></i>
                            <textarea
                                class="form-control custom-textarea with-icon"
                                :class="{ 'is-invalid': errores.sintomas }"
                                rows="4"
                                placeholder="Ingrese síntomas"
                                v-model="form.sintomas"
                            ></textarea>
                        </div>
                        <div class="invalid-feedback" v-if="errores.sintomas">
                            {{ errores.sintomas }}
                        </div>
                    </div>
                    <!-- BOTONES -->
                    <div class="d-flex justify-content-end gap-3 mt-5 animated-section delay-3">
                        <button
                            type="button"
                            class="btn btn-cancel btn-lg rounded-pill px-4"
                            @click="cancelarConsulta"
                            :disabled="guardando"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg rounded-pill px-5 save-btn"
                            :disabled="guardando"
                        >
                            <i class="fas fa-save"></i>
                            {{ guardando ? 'Guardando...' : 'Guardar Consulta' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</template>
<script>
import ApiService from '../../services/ApiService.js'
    export default {
        data() {
            return {
                infoPacientes: {},
                guardando: false,
                form: {
                    fecha: '',
                    hora: '',
                    motivo: '',
                    diagnostico: '',
                    peso: '',
                    presion: '',
                    observaciones: '',
                    sintomas: ''
                },
                errores: {
                    fecha: '',
                    hora: '',
                    motivo: '',
                    diagnostico: '',
                    peso: '',
                    presion: '',
                    sintomas: ''
                }
            }
        },
        mounted() {
            //this.obtenerPacientes();
            console.log('PROP PacienteId:', this.pacienteId);
        },
        methods: {
            async obtenerPacientes(){
                try {
                    const response = await ApiService.get('/ExpedienteDetalle/' + this.pacienteId)
                    this.infoPacientes = response.data
                    console.log('Pacientes cargados:',this.infoPacientes)
                }catch(error){
                        console.error("Error al obtener pacientes:", error)
                }
            },
            validarFormulario() {
                this.errores = {
                    fecha: '',
                    hora: '',
                    motivo: '',
                    diagnostico: '',
                    peso: '',
                    presion: '',
                    sintomas: ''
                }
                let valido = true
                if (!this.form.fecha) {
                    this.errores.fecha = 'La fecha es obligatoria.'
                    valido = false
                }
                if (!this.form.hora) {
                    this.errores.hora = 'La hora es obligatoria.'
                    valido = false
                }
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
                if (!this.form.sintomas.trim()) {
                    this.errores.sintomas = 'Describa los síntomas del paciente.'
                    valido = false
                }
                return valido
            },

           limpiarFormulario() {
                this.form = {
                    fecha: '',
                    hora: '',
                    motivo: '',
                    diagnostico: '',
                    peso: '',
                    presion: '',
                    observaciones: '',
                    sintomas: ''
                };
            },
            async guardarConsulta() {
                 if (!this.validarFormulario()) return  // ← faltaba esto

                this.guardando = true
                try {
                    const payload = {
                        ...this.form,
                        paciente_id: this.pacienteId  // ← faltaba incluir el ID
                    }
                    const response = await ApiService.post('/consultas', payload)  // ← verifica la ruta
                    console.log('Guardado:', response.data)
                    console.log('Payload enviado:', payload)  // ← agrega esto
                    Swal.fire({
                        icon: 'success',
                        title: 'Consulta registrada',
                        text: 'La consulta fue guardada exitosamente.',
                        confirmButtonText: 'Aceptar'
                    })
                    this.limpiarFormulario()
                } catch (error) {
                    console.error(error)
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al guardar la consulta.',
                        confirmButtonText: 'Aceptar'
                    })
                } finally {
                    this.guardando = false  // ← faltaba esto
                }
            },
            cancelarConsulta() {
                this.form = {
                    fecha: '',
                    hora: '',
                    motivo: '',
                    diagnostico: '',
                    peso: '',
                    presion: '',
                    observaciones: '',
                    sintomas: ''
                }
                this.errores = {
                    fecha: '',
                    hora: '',
                    motivo: '',
                    diagnostico: '',
                    peso: '',
                    presion: '',
                    sintomas: ''
                }
                this.$emit('cancelar')
            }
        },
        props:{
            //Esto guarda la el id que se trajo mediante la ruta parametrizada que el master hereda a los componentes hijos
            pacienteId:{
                type: [Number, String],
                required: true
            }
        },
        watch:{
            pacienteId:{
                immediate:true,
                handler(id){
                    if(id){
                        this.obtenerPacientes();
                    }
                }
            }
        }
    }
</script>
<style scoped>
.form-scroll{
    height:calc(100vh - 120px);
    overflow-y:auto;
    padding-right:10px;
}
.main-card{
    border-radius:30px;
    box-shadow:0 15px 40px rgba(0,0,0,.06);
    overflow:hidden;
}
.section-title{
    font-weight:800;
    color:#1f2937;
}
.form-label{
    font-weight:700;
    color:#374151;
    margin-bottom:8px;
}
.input-icon-box,
.textarea-icon-box{
    position:relative;
}
.input-icon-box i{
    position:absolute;
    top:50%;
    left:18px;
    transform:translateY(-50%);
    color:#0d6efd;
}
.textarea-icon-box i{
    position:absolute;
    top:20px;
    left:18px;
    color:#0d6efd;
}
.custom-input{
    height:46px;
    border:none;
    border-radius:14px;
    padding:10px 16px;
    background:#f8fafc;
    box-shadow:inset 0 0 0 1px #e5e7eb;
}
.custom-input.with-icon{
    padding-left:50px;
}
.custom-input.is-invalid,
.custom-textarea.is-invalid{
    box-shadow:inset 0 0 0 1px #dc2626;
}
.custom-textarea{
    border:none;
    border-radius:16px;
    padding:16px;
    background:#f8fafc;
    resize:none;
    box-shadow:inset 0 0 0 1px #e5e7eb;
}
.custom-textarea.with-icon{
    padding-left:50px;
}
.btn-cancel{
    background:#fee2e2;
    color:#dc2626;
    border:none;
    font-weight:700;
}
.btn-cancel:disabled,
.save-btn:disabled{
    opacity:.6;
    cursor:not-allowed;
}
.save-btn{
    background:linear-gradient(135deg,#0d6efd,#00c6ff);
    border:none;
}
.animated-section{
    animation: fadeSlideIn .5s ease-out both;
}
.animated-section.delay-1{
    animation-delay:.1s;
}
.animated-section.delay-2{
    animation-delay:.2s;
}
.animated-section.delay-3{
    animation-delay:.3s;
}
@keyframes fadeSlideIn {
    from{
        opacity:0;
        transform:translateY(16px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}
</style>