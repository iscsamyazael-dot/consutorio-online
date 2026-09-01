<template>
    <div class="patient-form">

        <!-- ══ NAVEGACIÓN DE PASOS ══ -->
        <nav class="step-nav">
            <button v-for="s in steps" :key="s.id" type="button"
                    class="step-pill" :class="{ active: activeStep === s.id }"
                    @click="scrollToSection(s.id)">
                <span class="step-pill-num">{{ s.num }}</span>
                <span class="step-pill-label">{{ s.label }}</span>
            </button>
        </nav>

        <!-- ══ SECCIÓN 1: DATOS PERSONALES ══ -->
        <div id="sec-personal" class="section-header">
            <span class="section-badge">01</span>
            <div>
                <h3>Datos personales</h3>
                <p>Información de identificación del paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2">
            <div class="col-md-12 field-wrap" style="--delay:.03s">
                <label class="form-label">Nombre completo</label>
                <div class="input-box">
                    <input type="text" v-model="form.nombre" class="premium-input" placeholder="Nombre completo del paciente">
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <div class="form-row row g-4 mt-1">
            <div class="col-md-3 field-wrap" style="--delay:.06s">
                <label class="form-label">Sexo</label>
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
            <div class="col-md-3 field-wrap" style="--delay:.08s">
                <label class="form-label">Fecha de nacimiento</label>
                <div class="input-box">
                    <input type="date" v-model="form.fecha_nacimiento" @change="calcularEdad" class="premium-input">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.1s">
                <label class="form-label">Edad</label>
                <div class="input-box">
                    <input type="number" v-model.number="form.edad_anios" class="premium-input" placeholder="Años" min="0" max="120">
                    <span class="unit-badge">años</span>
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.12s">
                <label class="form-label">CURP</label>
                <div class="input-box">
                    <input type="text" v-model="form.curp" class="premium-input curp-input" placeholder="XXXX000000XXXXXX00" maxlength="18">
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <div class="hairline"></div>

        <!-- ══ SECCIÓN 2: DATOS DE CONTACTO ══ -->
        <div id="sec-contacto" class="section-header">
            <span class="section-badge">02</span>
            <div>
                <h3>Datos de contacto</h3>
                <p>Información para comunicarse con el paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2">
            <div class="col-md-4 field-wrap" style="--delay:.03s">
                <label class="form-label">Teléfono</label>
                <div class="input-box">
                    <input type="text" v-model="form.telefono" class="premium-input" placeholder="9999999999" maxlength="10">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-4 field-wrap" style="--delay:.06s">
                <label class="form-label">Correo electrónico</label>
                <div class="input-box">
                    <input type="email" v-model="form.email" class="premium-input" placeholder="correo@ejemplo.com">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-4 field-wrap" style="--delay:.09s">
                <label class="form-label">Dirección</label>
                <div class="input-box">
                    <input type="text" v-model="form.direccion" class="premium-input" placeholder="Calle, número, colonia">
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <div class="hairline"></div>

        <!-- ══ SECCIÓN 3: DATOS ADMINISTRATIVOS ══ -->
        <div id="sec-administrativo" class="section-header">
            <span class="section-badge">03</span>
            <div>
                <h3>Datos administrativos</h3>
                <p>Estado del expediente y foto del paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2 align-items-start">
            <div class="col-md-4 field-wrap" style="--delay:.03s">
                <label class="form-label">Estado del expediente</label>
                <div class="input-box select-box">
                    <select v-model="form.estado" class="premium-input">
                        <option value="" disabled selected>Seleccionar...</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                    <svg class="select-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    <span class="status-dot" :class="'dot-' + (form.estado || 'none')"></span>
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-8 field-wrap" style="--delay:.06s">
                <label class="form-label">Foto del paciente</label>
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

        <div class="hairline"></div>

        <!-- ══ SECCIÓN 4: DATOS MÉDICOS PERMANENTES ══ -->
        <div id="sec-medico" class="section-header">
            <span class="section-badge">04</span>
            <div>
                <h3>Datos médicos permanentes</h3>
                <p>Información clínica relevante del paciente</p>
            </div>
        </div>

        <div class="form-row row g-4 mt-2">
            <div class="col-md-3 field-wrap" style="--delay:.03s">
                <label class="form-label">Tipo de sangre</label>
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
            <div class="col-md-3 field-wrap" style="--delay:.05s">
                <label class="form-label">Alergias</label>
                <div class="input-box">
                    <input type="text" v-model="form.alergias" class="premium-input" placeholder="Polen, polvo, látex...">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.07s">
                <label class="form-label">Alergia a medicamentos</label>
                <div class="input-box">
                    <input type="text" v-model="form.alergia_medicamentos" class="premium-input" placeholder="Penicilina, ibuprofeno...">
                    <span class="input-line"></span>
                </div>
            </div>
            <div class="col-md-3 field-wrap" style="--delay:.09s">
                <label class="form-label">Antecedentes</label>
                <div class="textarea-box">
                    <textarea v-model="form.antecedentes" class="premium-textarea" rows="3" placeholder="Diabetes, hipertensión, cirugías previas..."></textarea>
                    <span class="input-line"></span>
                </div>
            </div>
        </div>

        <div class="hairline"></div>

        <!-- ══ SECCIÓN 5: TRIAJE ══ -->
       
        <!-- ══ BOTONES ══ -->
        <div class="action-row mt-5">
            <button type="button" class="btn cancel-btn" @click="resetForm">
                Cancelar
            </button>
            <button type="button" class="btn save-btn" @click="guardarPaciente">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                Guardar datos
            </button>
        </div>

    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'
// Importar SweetAlert2 para mostrar alertas
export default {
    data() {
        return {
            fotoPreview: null,
            edadError: '',
            activeStep: 'sec-personal',
            steps: [
                { id: 'sec-personal',       num: '01', label: 'Personal' },
                { id: 'sec-contacto',       num: '02', label: 'Contacto' },
                { id: 'sec-administrativo', num: '03', label: 'Administrativo' },
                { id: 'sec-medico',         num: '04', label: 'Médico' },
                { id: 'sec-triaje',         num: '05', label: 'Triaje' }
            ],
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
// Precargar datos del paciente desde localStorage si existen
    mounted() {
        const raw = localStorage.getItem('pacientePrecargar')
        if (!raw) return

        const p = JSON.parse(raw)
        localStorage.removeItem('pacientePrecargar')
// Precargar datos del paciente en el formulario
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
        this.form.alergia_medicamentos = p.alergia_medicamentos || ''
        this.form.antecedentes = p.antecedentes || ''
        this.form.presion_arterial = p.presion_arterial || ''
        this.form.saturacion = p.saturacion || ''
        this.form.temperatura = p.temperatura || ''
        this.form.frecuencia_cardiaca = p.frecuencia_cardiaca || ''
        this.form.frecuencia_respiratoria = p.frecuencia_respiratoria || ''
        this.form.peso = p.peso || ''
        this.form.talla = p.talla || ''
        this.form.sintomas = p.sintomas || ''
        this.form.motivo_consulta = p.motivo_consulta || ''
    },
// Computed properties for evaluating vital signs and overall triage status
    computed: {
        presionStatus() {
            const raw = this.form.presion_arterial
            if (!raw || !raw.includes('/')) return ''
            const [sysStr, diaStr] = raw.split('/')
            const sys = parseInt(sysStr, 10)
            const dia = parseInt(diaStr, 10)
            if (isNaN(sys) || isNaN(dia)) return ''
            if (sys >= 180 || dia >= 120 || sys < 90) return 'critical'
            if (sys >= 140 || dia >= 90) return 'warning'
            return 'normal'
        },
        saturacionStatus() {
            const v = this.form.saturacion
            if (v === null || v === '' || v === undefined) return ''
            if (v < 90) return 'critical'
            if (v < 95) return 'warning'
            return 'normal'
        },
        temperaturaStatus() {
            const v = this.form.temperatura
            if (v === null || v === '' || v === undefined) return ''
            if (v >= 38.5 || v < 35.5) return 'critical'
            if (v >= 37.6) return 'warning'
            return 'normal'
        },
        frecuenciaCardiacaStatus() {
            const v = this.form.frecuencia_cardiaca
            if (v === null || v === '' || v === undefined) return ''
            if (v < 50 || v > 120) return 'critical'
            if (v < 60 || v > 100) return 'warning'
            return 'normal'
        },
        frecuenciaRespiratoriaStatus() {
            const v = this.form.frecuencia_respiratoria
            if (v === null || v === '' || v === undefined) return ''
            if (v < 8 || v > 24) return 'critical'
            if (v < 12 || v > 20) return 'warning'
            return 'normal'
        },
        overallTriageStatus() {
            const statuses = [
                this.presionStatus, this.saturacionStatus, this.temperaturaStatus,
                this.frecuenciaCardiacaStatus, this.frecuenciaRespiratoriaStatus
            ]
            if (statuses.includes('critical')) return 'critical'
            if (statuses.includes('warning')) return 'warning'
            if (statuses.includes('normal')) return 'normal'
            return ''
        },
        overallTriageLabel() {
            return this.statusLabel(this.overallTriageStatus)
        }
    },
// Funciones y métodos
    methods: {
        statusLabel(status) {
            if (status === 'critical') return 'Fuera de rango'
            if (status === 'warning') return 'Vigilar'
            if (status === 'normal') return 'Normal'
            return ''
        },
// Navegar a la sección correspondiente al hacer clic en un paso
        scrollToSection(id) {
            this.activeStep = id
            const el = document.getElementById(id)
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
        },
// Calcular edad a partir de la fecha de nacimiento
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
// Manejar arrastrar y soltar foto
        onFotoDrop(e) {
            const file = e.dataTransfer.files[0]
            if (file && file.type.startsWith('image/')) this.procesarFoto(file)
        },
// Procesar la foto seleccionada
        procesarFoto(file) {
            this.form.foto = file
            const reader = new FileReader()
            reader.onload = (e) => { this.fotoPreview = e.target.result }
            reader.readAsDataURL(file)
        },
// Quitar foto seleccionada
        removeFoto() {
            this.fotoPreview = null
            this.form.foto = null
            this.$refs.fotoInput.value = ''
        },

        resetForm() {
            this.limpiarFormulario()
        },
// Limpiar todos los campos del formulario
        limpiarFormulario() {
            this.fotoPreview = null
            this.form = {
                nombre: '',
                sexo: '',
                fecha_nacimiento: '',
                edad_anios: '',
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
        // Guardar paciente en la base de datos.
        // El backend (PacienteController@store) verifica si ya existe un
        // paciente con el mismo nombre + CURP:
        //   - Si YA EXISTE (data.existe === true): no se crea nada nuevo,
        //     se redirige a ListaConsultas.
        //   - Si es NUEVO (data.existe === false): se crea el paciente
        //     (+ triage) y se redirige a ConsultaInteligente/{id} del
        //     paciente recién creado.
        async guardarPaciente() {
            if (!this.form.nombre) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos incompletos',
                    text: 'El nombre y el CURP son necesarios para verificar si el paciente ya está registrado.',
                    confirmButtonText: 'Aceptar'
                })
                return
            }

            try {
                const response = await ApiService.post('/pacientes', this.form)
                const data = response.data
                console.log('Guardado:', data)

                if (data.existe) {
                    // Paciente ya registrado con ese nombre + CURP: no se
                    // duplica, se manda directo a la lista de consultas.
                    Swal.fire({
                        icon: 'info',
                        title: 'Paciente ya registrado',
                        text: 'Ya existe un paciente con ese nombre y CURP. Te llevaremos a la lista de consultas.',
                        confirmButtonText: 'Continuar'
                    }).then(() => {
                        window.location.href = '/ListaConsultas'
                    })
                    return
                }

                // Paciente nuevo: se guardó correctamente, se abre
                // Consulta Inteligente para ese paciente.
                const pacienteId = data.data.Paciente.id

                // NUEVO: descarga automática del PDF del expediente (si se generó)
                if (data.expediente_pdf_url) {
                    this.descargarExpedientePdf(data.expediente_pdf_url, data.data.Paciente.paciente_id)
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Paciente registrado',
                    text: 'El paciente fue guardado exitosamente.',
                    confirmButtonText: 'Continuar'
                }).then(() => {
                    window.location.href = '/ConsultaInteligente/' + pacienteId
                })

            } catch (error) {
                console.error(error)
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al guardar el paciente.',
                    confirmButtonText: 'Aceptar'
                })
            }
        },

        // NUEVO: método aparte para mantener guardarPaciente() legible
        async descargarExpedientePdf(url, pacienteIdLegible) {
            try {
                const response = await ApiService.get(url, { responseType: 'blob' })
                const blobUrl = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = blobUrl
                link.setAttribute('download', `expediente-${pacienteIdLegible}.pdf`)
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(blobUrl)
            } catch (error) {
                console.error('Error al descargar el expediente PDF:', error)
                // No mostramos Swal de error aquí a propósito: el paciente
                // ya se guardó correctamente, no queremos que un fallo de
                // descarga se sienta como si el registro hubiera fallado.
            }
        }

    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

.patient-form {
    --ink: #0F172A;
    --ink-soft: #51607A;
    --ink-faint: #94A3B8;
    --paper: #F5F7FA;
    --surface: #FFFFFF;
    --line: #E3E8EF;
    --line-soft: #EDF1F6;
    --accent: #0B7285;
    --accent-dark: #075E6D;
    --accent-soft: #E5F3F5;
    --status-normal: #0E9F6E;
    --status-normal-soft: #E4F7EF;
    --status-warning: #D97706;
    --status-warning-soft: #FDF1DF;
    --status-critical: #DC2626;
    --status-critical-soft: #FCE8E8;
    --panel-dark: #0F2530;
    --panel-dark-soft: #16323F;

    font-family: 'Inter', system-ui, sans-serif;
    color: var(--ink);
    background: var(--paper);
    padding: 28px clamp(16px, 3vw, 36px) 40px;
    border-radius: 20px;
}

/* ── Step nav ── */
.step-nav {
    display: flex; gap: 8px; overflow-x: auto;
    padding: 6px; margin-bottom: 30px;
    background: var(--surface); border: 1px solid var(--line);
    border-radius: 14px; position: sticky; top: 8px; z-index: 5;
    box-shadow: 0 4px 14px rgba(15, 37, 48, .04);
}
.step-pill {
    display: flex; align-items: center; gap: 8px; flex: 1 1 auto;
    white-space: nowrap; border: none; background: transparent;
    padding: 10px 14px; border-radius: 10px; cursor: pointer;
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .82rem;
    color: var(--ink-soft); transition: background .2s, color .2s;
}
.step-pill:hover { background: var(--line-soft); }
.step-pill.active { background: var(--accent); color: #fff; }
.step-pill-num {
    font-family: 'IBM Plex Mono', monospace; font-size: .72rem;
    font-weight: 600; opacity: .7;
}

/* ── Section headers ── */
.section-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 22px; animation: fadeUp .4s ease both; }
.section-badge {
    font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: .8rem;
    color: var(--accent); background: var(--accent-soft);
    border: 1px solid rgba(11,114,133,.18);
    width: 40px; height: 40px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.section-header h3 {
    font-family: 'Sora', sans-serif; font-size: 1.08rem; font-weight: 700;
    color: var(--ink); margin: 6px 0 0; letter-spacing: -.2px;
}
.section-header p { color: var(--ink-faint); margin: 3px 0 0; font-size: .84rem; }
.section-header { position: relative; }

.overall-badge {
    margin-left: auto; align-self: center;
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 13px; border-radius: 999px;
    font-size: .78rem; font-weight: 700; font-family: 'Inter', sans-serif;
}
.overall-dot { width: 7px; height: 7px; border-radius: 50%; }
.badge-normal   { background: var(--status-normal-soft);  color: #067A56; }
.badge-normal .overall-dot { background: var(--status-normal); }
.badge-warning  { background: var(--status-warning-soft); color: #A15A05; }
.badge-warning .overall-dot { background: var(--status-warning); }
.badge-critical { background: var(--status-critical-soft); color: #B31414; }
.badge-critical .overall-dot { background: var(--status-critical); animation: pulse 1.4s infinite; }

@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

/* ── Hairline divider ── */
.hairline { height: 1px; background: var(--line); margin: 32px 0; }

/* ── Field animation ── */
.field-wrap { animation: fadeUp .4s cubic-bezier(.22,1,.36,1) both; animation-delay: var(--delay, 0s); }
@keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* ── Labels ── */
.form-label {
    font-family: 'Inter', sans-serif; font-weight: 600; font-size: .76rem;
    color: var(--ink-soft); margin-bottom: 7px; display: block;
    letter-spacing: .2px;
}

/* ── Inputs ── */
.input-box { position: relative; }
.input-line {
    position: absolute; bottom: 0; left: 50%; width: 0; height: 2px;
    background: var(--accent); border-radius: 2px;
    transition: width .3s cubic-bezier(.22,1,.36,1), left .3s;
    pointer-events: none;
}
.input-box:focus-within .input-line { width: 100%; left: 0; }

.premium-input {
    width: 100%; height: 46px; border: 1px solid var(--line); border-radius: 11px;
    padding: 0 16px; background: var(--surface);
    font-size: .9rem; color: var(--ink); font-family: 'Inter', sans-serif;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.premium-input::placeholder { color: var(--ink-faint); }
.premium-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(11,114,133,.12); }
.premium-input:hover:not(:focus) { border-color: #C7D0DC; }

.select-box .premium-input { appearance: none; -webkit-appearance: none; padding-right: 38px; cursor: pointer; }
.select-arrow { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--ink-faint); pointer-events: none; transition: transform .25s; }
.select-box:focus-within .select-arrow { transform: translateY(-50%) rotate(180deg); color: var(--accent); }

.status-dot { position: absolute; right: 38px; top: 50%; transform: translateY(-50%); width: 8px; height: 8px; border-radius: 50%; pointer-events: none; }
.dot-activo { background: var(--status-normal); }
.dot-inactivo { background: var(--ink-faint); }
.dot-pendiente { background: var(--status-warning); }
.dot-none { display: none; }

.curp-input { text-transform: uppercase; letter-spacing: 1px; font-family: 'IBM Plex Mono', monospace; font-size: .78rem; }

.unit-badge {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    font-size: .7rem; font-weight: 600; color: var(--ink-faint);
    pointer-events: none; font-family: 'Inter', sans-serif;
}

/* ── Textarea ── */
.textarea-box { position: relative; }
.premium-textarea {
    width: 100%; border: 1px solid var(--line); border-radius: 11px;
    padding: 12px 16px; background: var(--surface);
    font-size: .9rem; color: var(--ink); font-family: 'Inter', sans-serif;
    resize: none; outline: none; line-height: 1.6;
    transition: border-color .2s, box-shadow .2s;
}
.premium-textarea::placeholder { color: var(--ink-faint); }
.premium-textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(11,114,133,.12); }
.premium-textarea:hover:not(:focus) { border-color: #C7D0DC; }

/* ── Foto upload ── */
.foto-upload-area {
    position: relative; border-radius: 14px; border: 1.5px dashed #C7D0DC;
    background: var(--surface); min-height: 116px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; overflow: hidden;
    transition: border-color .2s, background .2s;
}
.foto-upload-area:hover { border-color: var(--accent); background: var(--accent-soft); }

.foto-placeholder { display: flex; flex-direction: column; align-items: center; gap: 7px; padding: 18px; }
.foto-placeholder svg { width: 30px; height: 30px; color: var(--ink-faint); }
.foto-placeholder p { font-size: .86rem; font-weight: 600; color: var(--ink-soft); margin: 0; }
.foto-placeholder span { font-size: .72rem; color: var(--ink-faint); }

.foto-preview { width: 100%; height: 136px; object-fit: cover; border-radius: 12px; }
.foto-remove {
    position: absolute; top: 8px; right: 8px;
    width: 26px; height: 26px; border-radius: 50%;
    background: rgba(15,23,42,.6); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.foto-remove:hover { background: var(--status-critical); }
.foto-remove svg { width: 13px; height: 13px; color: #fff; }

/* ══ VITALS PANEL — versión clara ══ */
.vitals-panel {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 18px; padding: 22px;
}
.vitals-panel-head {
    display: flex; align-items: baseline; justify-content: space-between;
    margin-bottom: 16px; padding: 0 2px;
}
.vitals-panel-head > span:first-child {
    font-family: 'Sora', sans-serif; font-weight: 700; font-size: .86rem;
    color: var(--ink); letter-spacing: .3px;
}
.vitals-panel-sub { font-size: .72rem; color: var(--ink-faint); }

.vitals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(158px, 1fr));
    gap: 12px;
}
.vital-card {
    background: var(--paper);
    border: 1px solid var(--line);
    border-radius: 13px; padding: 14px 16px;
    animation: fadeUp .4s cubic-bezier(.22,1,.36,1) both; animation-delay: var(--delay, 0s);
    transition: border-color .2s, background .2s;
}
.vital-label { font-size: .7rem; font-weight: 600; color: var(--ink-faint); letter-spacing: .3px; margin-bottom: 8px; text-transform: uppercase; }
.vital-readout { display: flex; align-items: baseline; gap: 6px; }
.vital-input {
    width: 100%; background: transparent; border: none; outline: none;
    font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: 1.35rem;
    color: var(--ink); padding: 0; min-width: 0;
}
.vital-input::placeholder { color: var(--ink-faint); opacity: .55; }
.vital-unit { font-family: 'IBM Plex Mono', monospace; font-size: .7rem; color: var(--ink-faint); flex-shrink: 0; }

.vital-status-tag {
    display: inline-block; margin-top: 9px; font-size: .66rem; font-weight: 700;
    letter-spacing: .3px; text-transform: uppercase; padding: 3px 8px; border-radius: 999px;
}
.v-normal   { background: var(--status-normal-soft); border-color: rgba(14,159,110,.3); }
.v-normal .vital-status-tag { background: rgba(14,159,110,.14); color: #067A56; }
.v-warning  { background: var(--status-warning-soft); border-color: rgba(217,119,6,.3); }
.v-warning .vital-input { color: #A15A05; }
.v-warning .vital-status-tag { background: rgba(217,119,6,.16); color: #A15A05; }
.v-critical { background: var(--status-critical-soft); border-color: rgba(220,38,38,.35); }
.v-critical .vital-input { color: #B31414; }
.v-critical .vital-status-tag { background: rgba(220,38,38,.16); color: #B31414; }

/* ══ BOTONES ══ */
.action-row { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
.btn {
    display: inline-flex; align-items: center; gap: 8px;
    border: none; border-radius: 12px; padding: 13px 24px;
    font-weight: 700; font-size: .87rem; cursor: pointer;
    font-family: 'Inter', sans-serif;
    transition: transform .15s, box-shadow .2s, background .2s;
}
.btn svg { width: 15px; height: 15px; }
.btn:active { transform: translateY(1px); }

.cancel-btn { background: var(--surface); color: var(--ink-soft); border: 1px solid var(--line); }
.cancel-btn:hover { border-color: #C7D0DC; background: var(--line-soft); }

.save-btn {
    background: var(--accent); color: #fff;
    box-shadow: 0 6px 18px rgba(11,114,133,.28);
}
.save-btn:hover { background: var(--accent-dark); box-shadow: 0 8px 22px rgba(11,114,133,.36); }

/* ══ Responsive ══ */
@media (max-width: 640px) {
    .patient-form { padding: 20px 14px 32px; border-radius: 14px; }
    .section-header { flex-wrap: wrap; }
    .overall-badge { margin-left: 56px; }
}

/* ══ Accesibilidad ══ */
.premium-input:focus-visible,
.premium-textarea:focus-visible,
.vital-input:focus-visible,
.btn:focus-visible,
.step-pill:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
}
@media (prefers-reduced-motion: reduce) {
    .field-wrap, .vital-card, .section-header, .overall-dot { animation: none !important; }
}
</style>