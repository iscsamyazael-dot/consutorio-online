<template>
    <div class="patient-form">

        <!-- HEADER -->
        <div class="section-header">
            <div class="header-icon-wrap">
                <svg class="header-icon pulse-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 12h6m-3-3v6M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" stroke-linecap="round"/>
                    <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z" opacity=".2"/>
                </svg>
            </div>
            <div>
                <h3>Información del Paciente</h3>
                <p>Complete los datos clínicos de la consulta</p>
            </div>
        </div>

        <!-- FILA 1: Nombre completo -->
        <div class="form-row row g-4 mt-2">

            <div class="col-md-4 field-wrap" style="--delay:.05s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                    Nombre(s)
                </label>
                <div class="input-box">
                    <input 
                        type="text" 
                        v-model="form.nombre" 
                        class="premium-input" 
                        placeholder="Nombre del paciente">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-4 field-wrap" style="--delay:.1s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                    Apellido Paterno
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.apellido_paterno" class="premium-input" placeholder="Apellido paterno">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-4 field-wrap" style="--delay:.15s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                    Apellido Materno
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.apellido_materno" class="premium-input" placeholder="Apellido materno">
                    <span class="input-line"></span>
                </div>
            </div>

        </div>

        <!-- FILA 2: Datos personales -->
        <div class="form-row row g-4 mt-1">

            <div class="col-md-3 field-wrap" style="--delay:.2s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    Sexo
                </label>
                <div class="input-box select-box">
                    <select v-model="form.sexo" class="premium-input">
                        <option value="" disabled selected>Seleccionar...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-3 field-wrap" style="--delay:.25s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                    Fecha de Nacimiento
                </label>
                <div class="input-box">
                    <input type="date" v-model="form.fecha_nacimiento" class="premium-input">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-3 field-wrap" style="--delay:.3s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                    Tipo de Sangre
                </label>
                <div class="input-box select-box">
                    <select v-model="form.tipo_sangre" class="premium-input">
                        <option value="" disabled selected>Seleccionar...</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-3 field-wrap" style="--delay:.35s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                    CURP
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.curp" class="premium-input curp-input"
                           placeholder="XXXX000000XXXXXX00" maxlength="18">
                    <span class="input-line"></span>
                </div>
            </div>

        </div>

        <!-- FILA 3: Contacto -->
        <div class="form-row row g-4 mt-1">

            <div class="col-md-4 field-wrap" style="--delay:.4s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    Número de Teléfono
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.telefono" class="premium-input"
                           placeholder="9999999999" maxlength="10">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-4 field-wrap" style="--delay:.45s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    Correo Electrónico
                </label>
                <div class="input-box">
                    <input type="email" v-model="form.email" class="premium-input"
                           placeholder="correo@ejemplo.com">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-4 field-wrap" style="--delay:.5s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    Dirección
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.direccion" class="premium-input"
                           placeholder="Calle, Número, Colonia">
                    <span class="input-line"></span>
                </div>
            </div>

        </div>

        <!-- FILA 4: Contacto de emergencia -->
        <div class="form-row row g-4 mt-1">

            <div class="col-md-6 field-wrap" style="--delay:.55s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    Contacto de Emergencia
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.contacto_emergencia" class="premium-input"
                           placeholder="Nombre del contacto">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-3 field-wrap" style="--delay:.6s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    Teléfono de Emergencia
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.telefono_emergencia" class="premium-input"
                           placeholder="9999999999" maxlength="10">
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="col-md-3 field-wrap" style="--delay:.65s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Estado
                </label>
                <div class="input-box select-box">
                    <select v-model="form.estado" class="premium-input">
                        <option value="" disabled selected>Seleccionar...</option>
                        <option value="activo">✅ Activo</option>
                        <option value="inactivo">⛔ Inactivo</option>
                        <option value="pendiente">⏳ Pendiente</option>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    <span class="input-line"></span>
                </div>
            </div>

        </div>

        <!-- FILA 5: Notas generales -->
        <div class="form-row row g-4 mt-1">
            <div class="col-12 field-wrap" style="--delay:.7s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    Notas Generales
                </label>
                <div class="textarea-box">
                    <textarea v-model="form.notas_generales" class="premium-textarea" rows="3"
                              placeholder="Observaciones, alergias, antecedentes importantes..."></textarea>
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* ── Base ── */
.patient-form {
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* ── Header ── */
.section-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    animation: slideDown .5s cubic-bezier(.22,1,.36,1) both;
}

.header-icon-wrap {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(37,99,235,.35);
    flex-shrink: 0;
}

.header-icon {
    width: 26px;
    height: 26px;
    color: #fff;
}

.section-header h3 {
    font-size: 1.25rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
    letter-spacing: -.3px;
}

.section-header p {
    color: #6b7280;
    margin: 4px 0 0;
    font-size: .875rem;
}

/* ── Pulse animation on header icon ── */
@keyframes pulse-ring {
    0%   { box-shadow: 0 8px 20px rgba(37,99,235,.35), 0 0 0 0 rgba(37,99,235,.4); }
    70%  { box-shadow: 0 8px 20px rgba(37,99,235,.35), 0 0 0 10px rgba(37,99,235,0); }
    100% { box-shadow: 0 8px 20px rgba(37,99,235,.35), 0 0 0 0 rgba(37,99,235,0); }
}
.header-icon-wrap {
    animation: pulse-ring 2.5s ease-out infinite;
}

/* ── Field entrance animation ── */
.field-wrap {
    animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both;
    animation-delay: var(--delay, 0s);
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Labels ── */
.form-label {
    font-weight: 700;
    font-size: .8rem;
    color: #374151;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.form-label svg {
    width: 14px;
    height: 14px;
    color: #2563eb;
    flex-shrink: 0;
}

/* ── Input box & underline effect ── */
.input-box {
    position: relative;
}

.input-line {
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    border-radius: 2px;
    transition: width .35s cubic-bezier(.22,1,.36,1), left .35s cubic-bezier(.22,1,.36,1);
    pointer-events: none;
}

.input-box:focus-within .input-line {
    width: 100%;
    left: 0;
}

/* ── Inputs & selects ── */
.premium-input {
    width: 100%;
    height: 47px;
    border: none;
    border-radius: 14px;
    padding: 0 18px;
    background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #e5e7eb;
    font-size: .92rem;
    color: #111827;
    transition: background .25s, box-shadow .25s, transform .15s;
    outline: none;
}

.premium-input::placeholder {
    color: #9ca3af;
}

.premium-input:focus {
    background: #fff;
    box-shadow: inset 0 0 0 1.5px transparent, 0 0 0 4px rgba(37,99,235,.12);
    transform: translateY(-1px);
}

.premium-input:hover:not(:focus) {
    box-shadow: inset 0 0 0 1.5px #93c5fd;
}

/* Select arrow */
.select-box {
    position: relative;
}

.select-box .premium-input {
    appearance: none;
    -webkit-appearance: none;
    padding-right: 40px;
    cursor: pointer;
}

.select-arrow {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #9ca3af;
    pointer-events: none;
    transition: transform .25s, color .25s;
}

.select-box:focus-within .select-arrow {
    transform: translateY(-50%) rotate(180deg);
    color: #2563eb;
}

/* CURP uppercase */
.curp-input {
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: .85rem;
}

/* ── Textarea ── */
.textarea-box {
    position: relative;
}

.premium-textarea {
    width: 100%;
    border: none;
    border-radius: 18px;
    padding: 16px 20px;
    background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #e5e7eb;
    font-size: .92rem;
    color: #111827;
    font-family: inherit;
    resize: none;
    min-height: 100px;
    transition: background .25s, box-shadow .25s, transform .15s;
    outline: none;
    line-height: 1.6;
}

.premium-textarea::placeholder {
    color: #9ca3af;
}

.premium-textarea:focus {
    background: #fff;
    box-shadow: inset 0 0 0 1.5px transparent, 0 0 0 4px rgba(37,99,235,.12);
    transform: translateY(-1px);
}

.premium-textarea:hover:not(:focus) {
    box-shadow: inset 0 0 0 1.5px #93c5fd;
}

.textarea-box .input-line {
    border-radius: 0 0 18px 18px;
}
</style>
<script>
//import ApiService from '../../services/ApiService.js'
    export default{
        data(){
                return{
                    form:{
                        nombre: '',
                        apellido_paterno: '',
                        apellido_materno: '',
                        sexo: '',
                        fecha_nacimiento: '',
                        tipo_sangre: '',
                        curp: '',
                        telefono: '',
                        email: '',
                        direccion: '',
                        contacto_emergencia: '',
                        telefono_emergencia: '',
                        estado: '',
                        notas_generales: ''
                    }
            }
        },
            mounted(){
                
            },
            methods:{
                //* Metodo para guardar un medicamento a la base de datos *//
                async guardarMedicamento(){
                    try {
                        const response = await ApiService.post('/pacientes',this.form)
                        console.log(response.data)
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro exitoso',
                            text: 'El paciente fue registrado correctamente',
                            confirmButtonText: 'Aceptar'
                        })
                    }
                    catch(error){
                        console.error(error)
                    }

                }
       }
    }
</script>