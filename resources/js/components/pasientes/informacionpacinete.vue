<template>
    <div class="patient-form">

        <!-- ══ SECCIÓN 1: DATOS PERSONALES ══ -->
        <div class="section-header">
            <div class="header-icon-wrap blue-icon">
                <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm-7 8a7 7 0 0114 0" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h3>Datos Personales</h3>
                <p>Información de identificación del paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2">
            <div class="col-md-12 field-wrap" style="--delay:.05s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                    Nombre Completo
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.nombre" class="premium-input" placeholder="Nombre completo del paciente">
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

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
                    <input type="date"
                        v-model="form.fecha_nacimiento"
                        @change="calcularEdad"
                        class="premium-input">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.28s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a8 8 0 100 16 8 8 0 000-16z"/></svg>
                    Edad
                </label>
                <div style="display:flex; gap:8px; align-items:flex-start;">
                    <div class="input-box" style="flex:1">
                        <input
                            type="number"
                            v-model.number="form.edad_anios"
                            class="premium-input"
                            placeholder="Años"
                            min="0"
                            max="120"
                            style="padding: 0 12px;">
                        <span class="input-line"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.3s">
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

        <!-- ══ DIVIDER ══ -->
        <div class="premium-divider">
            <span class="divider-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                Contacto
            </span>
        </div>

        <!-- ══ SECCIÓN 2: DATOS DE CONTACTO ══ -->
        <div class="section-header">
            <div class="header-icon-wrap green-icon">
                <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h3>Datos de Contacto</h3>
                <p>Información para comunicarse con el paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2">
            <div class="col-md-4 field-wrap" style="--delay:.05s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    Teléfono
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.telefono" class="premium-input" placeholder="9999999999" maxlength="10">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-4 field-wrap" style="--delay:.1s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    Correo Electrónico
                </label>
                <div class="input-box">
                    <input type="email" v-model="form.email" class="premium-input" placeholder="correo@ejemplo.com">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-4 field-wrap" style="--delay:.15s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    Dirección
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.direccion" class="premium-input" placeholder="Calle, Número, Colonia">
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <!-- ══ DIVIDER ══ -->
        <div class="premium-divider">
            <span class="divider-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Administrativo
            </span>
        </div>

        <!-- ══ SECCIÓN 3: DATOS ADMINISTRATIVOS ══ -->
        <div class="section-header">
            <div class="header-icon-wrap amber-icon">
                <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 12h6m-3-3v6M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h3>Datos Administrativos</h3>
                <p>Estado del expediente y foto del paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2 align-items-start">
            <div class="col-md-4 field-wrap" style="--delay:.05s">
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
            <div class="col-md-8 field-wrap" style="--delay:.1s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    Foto del Paciente
                </label>
                <div class="foto-upload-area" @click="$refs.fotoInput.click()" @dragover.prevent @drop.prevent="onFotoDrop">
                    <img v-if="fotoPreview" :src="fotoPreview" class="foto-preview" alt="Foto paciente">
                    <div v-else class="foto-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 16a4 4 0 100-8 4 4 0 000 8zM3 9a2 2 0 012-2h.5l1.5-2h10l1.5 2H21a2 2 0 012 2v9a2 2 0 01-2 2H3a2 2 0 01-2-2V9z" stroke-linecap="round"/>
                        </svg>
                        <p>Clic o arrastra una foto</p>
                        <span>JPG, PNG — máx. 5MB</span>
                    </div>
                    <button v-if="fotoPreview" type="button" class="foto-remove" @click.stop="removeFoto">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <input ref="fotoInput" type="file" accept="image/*" style="display:none" @change="onFotoChange">
            </div>
        </div>

        <!-- ══ DIVIDER ══ -->
        <div class="premium-divider">
            <span class="divider-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                Médico
            </span>
        </div>

        <!-- ══ SECCIÓN 4: DATOS MÉDICOS PERMANENTES ══ -->
        <div class="section-header">
            <div class="header-icon-wrap red-icon">
                <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 12h6M12 9v6M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h3>Datos Médicos Permanentes</h3>
                <p>Información clínica relevante del paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2">
            <div class="col-md-3 field-wrap" style="--delay:.05s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                    Tipo de Sangre
                </label>
                <div class="input-box select-box">
                    <select v-model="form.tipo_sangre" class="premium-input">
                        <option value="" disabled selected>Seleccionar...</option>
                        <option>A+</option><option>A-</option>
                        <option>B+</option><option>B-</option>
                        <option>AB+</option><option>AB-</option>
                        <option>O+</option><option>O-</option>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.1s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Alergias
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.alergias" class="premium-input" placeholder="Polen, polvo, látex...">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.15s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM6.343 5.636a1 1 0 10-1.414 1.414l.707.707A1 1 0 107.05 6.343l-.707-.707zm8.364 1.414a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM17 10a1 1 0 100-2h-1a1 1 0 100 2h1zM5 10a1 1 0 100-2H4a1 1 0 100 2h1zm9.243 4.243a1 1 0 00-1.414 0l-.707.707a1 1 0 001.414 1.414l.707-.707a1 1 0 000-1.414zM6.343 14.95a1 1 0 000-1.414l-.707-.707a1 1 0 10-1.414 1.414l.707.707a1 1 0 001.414 0zM10 15a1 1 0 100 2 1 1 0 000-2z"/></svg>
                    Alergia a Medicamentos
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.alergia_medicamentos" class="premium-input" placeholder="Penicilina, ibuprofeno...">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.2s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    Antecedentes
                </label>
                <div class="textarea-box">
                    <textarea v-model="form.antecedentes" class="premium-textarea" rows="3"
                              placeholder="Diabetes, hipertensión, cirugías previas..."></textarea>
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <!-- ══ DIVIDER ══ -->
        <div class="premium-divider">
            <span class="divider-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg>
                Triaje
            </span>
        </div>

        <!-- ══ SECCIÓN 5: TRIAJE ══ -->
        <div class="section-header">
            <div class="header-icon-wrap triage-icon">
                <svg class="header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 12h6M12 9v6M4.5 12a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h3>Triaje</h3>
                <p>Signos vitales, síntomas y motivo de consulta</p>
            </div>
        </div>

        <!-- Signos Vitales -->
        <div class="vitals-grid mt-2">

            <div class="vital-card field-wrap" style="--delay:.05s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/></svg>
                    Presión Arterial
                </label>
                <div class="input-box">
                    <input type="text" v-model="form.presion_arterial" class="premium-input" placeholder="120/80" maxlength="7" style="padding-right:60px">
                    <span class="unit-badge">mmHg</span>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="vital-card field-wrap" style="--delay:.08s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd"/></svg>
                    Saturación O₂
                </label>
                <div class="input-box">
                    <input type="number" v-model.number="form.saturacion" class="premium-input" placeholder="98" min="0" max="100" style="padding-right:46px">
                    <span class="unit-badge">%</span>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="vital-card field-wrap" style="--delay:.11s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zm4.243 1.757a1 1 0 00-1.415 0l-.707.707a1 1 0 001.415 1.414l.707-.707a1 1 0 000-1.414zM10 16a1 1 0 100 2 1 1 0 000-2zm7-6a1 1 0 100-2h-1a1 1 0 100 2h1zm-14 0a1 1 0 100-2H2a1 1 0 100 2h1zm11.657 3.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM5.05 14.95a1 1 0 101.414-1.415l-.707-.707a1 1 0 00-1.414 1.415l.707.707zM15 10a5 5 0 11-10 0 5 5 0 0110 0z"/></svg>
                    Temperatura
                </label>
                <div class="input-box">
                    <input type="number" v-model.number="form.temperatura" class="premium-input" placeholder="36.5" min="30" max="45" step="0.1" style="padding-right:46px">
                    <span class="unit-badge">°C</span>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="vital-card field-wrap" style="--delay:.14s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd"/></svg>
                    Frec. Cardíaca
                </label>
                <div class="input-box">
                    <input type="number" v-model.number="form.frecuencia_cardiaca" class="premium-input" placeholder="72" min="0" max="300" style="padding-right:52px">
                    <span class="unit-badge">lpm</span>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="vital-card field-wrap" style="--delay:.17s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v5a1.5 1.5 0 11-3 0v-5zm4-1a1.5 1.5 0 113 0v6a1.5 1.5 0 11-3 0v-6zm4-3a1.5 1.5 0 113 0v9a1.5 1.5 0 11-3 0V6.5zm4 2a1.5 1.5 0 113 0v7a1.5 1.5 0 11-3 0v-7z"/></svg>
                    Frec. Respiratoria
                </label>
                <div class="input-box">
                    <input type="number" v-model.number="form.frecuencia_respiratoria" class="premium-input" placeholder="16" min="0" max="60" style="padding-right:52px">
                    <span class="unit-badge">rpm</span>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="vital-card field-wrap" style="--delay:.20s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16A8 8 0 0010 2zm0 14a6 6 0 110-12 6 6 0 010 12z" clip-rule="evenodd"/></svg>
                    Peso
                </label>
                <div class="input-box">
                    <input type="number" v-model.number="form.peso" class="premium-input" placeholder="70.0" min="0" max="300" step="0.1" style="padding-right:42px">
                    <span class="unit-badge">kg</span>
                    <span class="input-line"></span>
                </div>
            </div>

            <div class="vital-card field-wrap" style="--delay:.23s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                    Talla
                </label>
                <div class="input-box">
                    <input type="number" v-model.number="form.talla" class="premium-input" placeholder="170" min="0" max="250" style="padding-right:42px">
                    <span class="unit-badge">cm</span>
                    <span class="input-line"></span>
                </div>
            </div>

        </div>

        <!-- Síntoma y Motivo -->
        <div class="form-row row g-4 mt-3">
            <div class="col-md-6 field-wrap" style="--delay:.26s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Síntoma Principal
                </label>
                <div class="textarea-box">
                    <textarea v-model="form.sintomas" class="premium-textarea" rows="3"
                              placeholder="Describe el síntoma principal del paciente..."></textarea>
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-6 field-wrap" style="--delay:.29s">
                <label class="form-label">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                    Motivo de Consulta
                </label>
                <div class="textarea-box">
                    <textarea v-model="form.motivo_consulta" class="premium-textarea" rows="3"
                              placeholder="¿Por qué motivo acude el paciente hoy?"></textarea>
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <!-- ══ BOTONES ══ -->
        <div class="action-row mt-5">
            <button type="button" class="btn cancel-btn" @click="resetForm">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                Cancelar
            </button>
            <button type="button" class="btn save-btn" @click="guardarPaciente">
                Guardar Datos
            </button>
        </div>

    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    data() {
        return {
            fotoPreview: null,
            edadError: '',
            form: {
                nombre: '',
                sexo: '',
                fecha_nacimiento: '',
                edad_anios: 0,
                curp: '',
                telefono: '',
                email: '',
                direccion: '',
                estado: '',
                foto: null,
                tipo_sangre: '',
                alergias: '',
                alergia_medicamentos: '',
                antecedentes: '',
                nivel_urgencia: 'null',
                presion_arterial: '',
                saturacion: null,
                temperatura: null,
                peso: null,
                talla: null,
                frecuencia_cardiaca: null,
                frecuencia_respiratoria: null,
                sintomas: '',
                motivo_consulta: ''
            }
        }
    },

    //  mounted va aquí, al mismo nivel que data, computed y methods
    mounted() {
        const raw = localStorage.getItem('pacientePrecargar')
        if (!raw) return

        const p = JSON.parse(raw)
        localStorage.removeItem('pacientePrecargar')

        this.form.nombre      = [p.nombre, p.apellido_paterno, p.apellido_materno].filter(Boolean).join(' ')
        this.form.sexo        = p.sexo        || ''
        this.form.curp        = p.curp        || ''
        this.form.telefono    = p.telefono    || ''
        this.form.email       = p.email       || ''
        this.form.direccion   = p.direccion   || ''
        this.form.tipo_sangre = p.tipo_sangre || ''
        this.form.edad_anios  = p.edad        || ''
        this.form.estado      = p.estado      || ''
        this.form.alergias    = p.alergias    || ''
        this.form.fecha_nacimiento = p.fecha_nacimiento || ''
    },

    computed: {
        //  calcularEdad como método, no como computed (se llama con @change)
    },

    methods: {

        calcularEdad() {
            if (!this.form.fecha_nacimiento) return
            const fechaNacimiento = new Date(this.form.fecha_nacimiento)
            const hoy = new Date()
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear()
            const mes = hoy.getMonth() - fechaNacimiento.getMonth()
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--
            }
            this.form.edad_anios = edad
        },

        onFotoChange(e) {
            const file = e.target.files[0]
            if (file) this.procesarFoto(file)
        },

        onFotoDrop(e) {
            const file = e.dataTransfer.files[0]
            if (file && file.type.startsWith('image/')) this.procesarFoto(file)
        },

        procesarFoto(file) {
            this.form.foto = file
            const reader = new FileReader()
            reader.onload = (e) => { this.fotoPreview = e.target.result }
            reader.readAsDataURL(file)
        },

        removeFoto() {
            this.fotoPreview = null
            this.form.foto = null
            this.$refs.fotoInput.value = ''
        },

        limpiarFormulario() {
            this.form = {
                nombre: '',
                telefono: '',
                email: '',
                edad_anios: '',
                sexo: '',
                direccion: '',
                tipo_sangre: '',
                contacto_emergencia: '',
                telefono_emergencia: '',
                curp: '',
                notas_generales: '',
                fecha_nacimiento: '',
                presion_arterial: '',
                saturacion: '',
                temperatura: '',
                frecuencia_cardiaca: '',
                frecuencia_respiratoria: '',
                peso: '',
                talla: '',
                sintomas: '',
                motivo_consulta: ''
            }
        },

        async guardarPaciente() {
            try {
                const response = await ApiService.post('/pacientes', this.form)
                console.log('Guardado:', response.data)
                Swal.fire({
                    icon: 'success',
                    title: 'Paciente registrado',
                    text: 'El paciente fue guardado exitosamente.',
                    confirmButtonText: 'Aceptar'
                })
                this.limpiarFormulario()
            } catch (error) {
                console.error(error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al guardar el paciente.',
                    confirmButtonText: 'Aceptar'
                })
            }
        }
    }
}
</script>

<style scoped>

.patient-form { font-family: 'Segoe UI', system-ui, sans-serif; }

/* ── Headers ── */
.section-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; animation: slideDown .5s cubic-bezier(.22,1,.36,1) both; }
.section-header h3 { font-size: 1.1rem; font-weight: 800; color: #111827; margin: 0; letter-spacing: -.3px; }
.section-header p  { color: #6b7280; margin: 4px 0 0; font-size: .85rem; }

.header-icon-wrap {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.header-icon { width: 24px; height: 24px; color: #fff; }

.blue-icon   { background: linear-gradient(135deg, #2563eb, #1d4ed8); box-shadow: 0 6px 16px rgba(37,99,235,.3); }
.green-icon  { background: linear-gradient(135deg, #16a34a, #15803d); box-shadow: 0 6px 16px rgba(22,163,74,.3); }
.amber-icon  { background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 6px 16px rgba(217,119,6,.3); }
.red-icon    { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 6px 16px rgba(239,68,68,.3); }
.triage-icon { background: linear-gradient(135deg, #7c3aed, #6d28d9); box-shadow: 0 6px 16px rgba(124,58,237,.3); }

/* ── Animations ── */
.field-wrap { animation: fadeUp .45s cubic-bezier(.22,1,.36,1) both; animation-delay: var(--delay, 0s); }
@keyframes fadeUp   { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
@keyframes slideDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }

/* ── Labels ── */
.form-label {
    font-weight: 700; font-size: .78rem; color: #374151;
    margin-bottom: 8px; display: flex; align-items: center;
    gap: 6px; text-transform: uppercase; letter-spacing: .5px;
}
.form-label svg { width: 14px; height: 14px; color: #2563eb; flex-shrink: 0; }

/* ── Inputs ── */
.input-box { position: relative; }
.input-line {
    position: absolute; bottom: 0; left: 50%; width: 0; height: 2px;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    border-radius: 2px;
    transition: width .35s cubic-bezier(.22,1,.36,1), left .35s;
    pointer-events: none;
}
.input-box:focus-within .input-line { width: 100%; left: 0; }

.premium-input {
    width: 100%; height: 47px; border: none; border-radius: 14px;
    padding: 0 18px; background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #e5e7eb;
    font-size: .92rem; color: #111827;
    transition: background .25s, box-shadow .25s, transform .15s;
    outline: none;
}
.premium-input::placeholder { color: #9ca3af; }
.premium-input:focus { background: #fff; box-shadow: inset 0 0 0 1.5px transparent, 0 0 0 4px rgba(37,99,235,.12); transform: translateY(-1px); }
.premium-input:hover:not(:focus) { box-shadow: inset 0 0 0 1.5px #93c5fd; }

.select-box { position: relative; }
.select-box .premium-input { appearance: none; -webkit-appearance: none; padding-right: 40px; cursor: pointer; }
.select-arrow { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #9ca3af; pointer-events: none; transition: transform .25s, color .25s; }
.select-box:focus-within .select-arrow { transform: translateY(-50%) rotate(180deg); color: #2563eb; }

.curp-input { text-transform: uppercase; letter-spacing: 1px; font-size: .85rem; }

/* ── Textarea ── */
.textarea-box { position: relative; }
.premium-textarea {
    width: 100%; border: none; border-radius: 14px;
    padding: 12px 16px; background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #e5e7eb;
    font-size: .92rem; color: #111827; font-family: inherit;
    resize: none; outline: none; line-height: 1.6;
    transition: background .25s, box-shadow .25s;
}
.premium-textarea::placeholder { color: #9ca3af; }
.premium-textarea:focus { background: #fff; box-shadow: inset 0 0 0 1.5px transparent, 0 0 0 4px rgba(37,99,235,.12); }
.premium-textarea:hover:not(:focus) { box-shadow: inset 0 0 0 1.5px #93c5fd; }
.textarea-box .input-line { border-radius: 0 0 14px 14px; }

/* ── Foto upload ── */
.foto-upload-area {
    position: relative; border-radius: 16px; border: 2px dashed #d1d5db;
    background: #f8fafc; min-height: 120px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; overflow: hidden;
    transition: border-color .25s, background .25s;
}
.foto-upload-area:hover { border-color: #2563eb; background: #eff6ff; }

.foto-placeholder { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px; }
.foto-placeholder svg { width: 36px; height: 36px; color: #9ca3af; }
.foto-placeholder p { font-size: .9rem; font-weight: 600; color: #374151; margin: 0; }
.foto-placeholder span { font-size: .75rem; color: #9ca3af; }

.foto-preview { width: 100%; height: 140px; object-fit: cover; border-radius: 14px; }
.foto-remove {
    position: absolute; top: 8px; right: 8px;
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(0,0,0,.55); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.foto-remove:hover { background: #ef4444; }
.foto-remove svg { width: 14px; height: 14px; color: #fff; }

/* ══ DIVIDER ══ */
.premium-divider {
    position: relative; height: 2px; margin: 36px 0;
    background: linear-gradient(to right, transparent, rgba(37,99,235,.2), transparent);
    display: flex; align-items: center; justify-content: center;
}
.divider-label {
    position: absolute; background: #fff; padding: 0 14px;
    font-size: .68rem; font-weight: 800; letter-spacing: 1.5px;
    text-transform: uppercase; color: #2563eb;
    display: flex; align-items: center; gap: 6px;
}
.divider-label svg { width: 12px; height: 12px; }

/* ══ TRIAJE ══ */
.vitals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
}
.vital-card {
    background: #faf5ff;
    border-radius: 14px;
    padding: 14px 16px;
    box-shadow: inset 0 0 0 1.5px #ede9fe;
}
.vital-card .form-label svg { color: #7c3aed; }
.unit-badge {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    font-size: .75rem; font-weight: 700; color: #7c3aed;
    background: #ede9fe; padding: 2px 8px; border-radius: 20px;
    pointer-events: none;
}

/* ══ BOTONES ══ */
.action-row { display: flex; justify-content: flex-end; gap: 14px; flex-wrap: wrap; }
.btn {
    display: inline-flex; align-items: center; gap: 8px;
    border: none; border-radius: 16px; padding: 14px 26px;
    font-weight: 700; font-size: .9rem; cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}
.btn svg { width: 17px; height: 17px; }
.btn:hover  { transform: translateY(-3px); }
.btn:active { transform: translateY(0); }

.cancel-btn { background: #fff; color: #374151; box-shadow: 0 4px 14px rgba(0,0,0,.07); }
.cancel-btn:hover { box-shadow: 0 8px 20px rgba(0,0,0,.1); }

.save-btn {
    background: linear-gradient(135deg, #2563eb, #38bdf8);
    color: #fff; box-shadow: 0 8px 24px rgba(37,99,235,.3);
    position: relative; overflow: hidden;
}
.save-btn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, #1d4ed8, #0ea5e9); opacity: 0; transition: opacity .25s; }
.save-btn:hover::after { opacity: 1; }
.save-btn:hover { box-shadow: 0 12px 30px rgba(37,99,235,.4); }
.save-btn > * { position: relative; z-index: 1; }
</style>