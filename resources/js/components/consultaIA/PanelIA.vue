<template>
    <!-- PANEL IA -->
    <div class="card bg-dark">
        <div class="card-header border-0">
            <h3 class="card-title">
                <i class="fas fa-robot"></i>
                Asistente Clínico IA
            </h3>
        </div>
        <div class="card-body">

            <div v-if="hasError" class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
                ⚠️ No se pudo obtener el análisis de la IA. Mostrando último dato disponible.
            </div>

            <div v-if="errorGuardado" class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
                ⚠️ {{ errorGuardado }}
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="small-box bg-primary position-relative">

                        <!-- ÍCONO DE EDITAR DENTRO DEL BOX -->
                        <button
                            v-if="!editandoDiagnostico"
                            type="button"
                            class="btn-editar-box"
                            title="Editar diagnóstico"
                            @click="activarEdicionDiagnostico"
                        >
                            <i class="fas fa-pen"></i>
                        </button>

                        <div class="inner">

                            <!-- MODO LECTURA -->
                            <template v-if="!editandoDiagnostico">
                                <h5>{{ diagnosticoPrincipal }}</h5>
                                <p>{{ compatibilidad }}</p>
                            </template>

                            <!-- MODO EDICIÓN: diagnóstico + buscador ICD-11 -->
                            <template v-else>
                                <label class="d-block text-white-50 small mb-1">Diagnóstico</label>
                                <div class="position-relative">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm mb-1"
                                        v-model="formDiagnostico"
                                        placeholder="Escribe o busca en ICD-11..."
                                        @input="buscarIcd11"
                                    >
                                    <ul v-if="resultadosIcd.length" class="icd-dropdown">
                                        <li
                                            v-for="r in resultadosIcd"
                                            :key="r.codigo"
                                            @click="seleccionarIcd(r)"
                                        >
                                            <strong>{{ r.codigo }}</strong> — {{ r.titulo }}
                                        </li>
                                    </ul>
                                    <small v-if="buscandoIcd" class="text-white-50">Buscando en ICD-11...</small>
                                </div>
                                <small v-if="formIcdCodigo" class="badge badge-light text-dark">
                                    Código ICD-11: {{ formIcdCodigo }}
                                </small>

                                <div class="mt-2 d-flex" style="gap:6px;">
                                    <button type="button" class="btn btn-sm btn-light" :disabled="guardando" @click="cancelarEdicionDiagnostico">
                                        Cancelar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" :disabled="guardando" @click="guardarDiagnostico">
                                        <span v-if="guardando"><i class="fas fa-spinner fa-spin"></i></span>
                                        <span v-else><i class="fas fa-check"></i> Guardar</span>
                                    </button>
                                </div>
                            </template>

                        </div>
                        <div class="icon">
                            <i class="fas fa-brain"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="small-box" :class="claseAlerta">
                        <div class="inner">
                            <h5>{{ alertaPrincipal }}</h5>
                            <p>{{ descripcionAlerta }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <h6 class="text-light mb-0">Recomendaciones IA</h6>
            </div>

            <!-- MODO LECTURA: lista de recomendaciones, con el botón de editar dentro -->
            <div v-if="!editandoRecomendaciones" class="recomendaciones-box position-relative mt-2">
                <button
                    type="button"
                    class="btn-editar-box btn-editar-box--claro"
                    title="Editar recomendaciones"
                    @click="activarEdicionRecomendaciones"
                >
                    <i class="fas fa-pen"></i>
                </button>
                <ul class="list-group">
                    <li
                        class="list-group-item text-dark"
                        v-for="(rec, index) in recomendaciones"
                        :key="rec + '-' + index"
                    >
                        {{ rec }}
                    </li>
                </ul>
            </div>

            <!-- MODO EDICIÓN: textarea libre + acciones dentro del mismo cuadro -->
            <div v-else class="recomendaciones-box mt-2">
                <textarea
                    class="form-control"
                    rows="4"
                    v-model="formRecomendaciones"
                    placeholder="Escribe las recomendaciones (una por línea)..."
                ></textarea>
                <div class="mt-2 d-flex" style="gap:6px;">
                    <button type="button" class="btn btn-sm btn-secondary" :disabled="guardando" @click="cancelarEdicionRecomendaciones">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" :disabled="guardando" @click="guardarDiagnostico">
                        <span v-if="guardando"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                        <span v-else><i class="fas fa-check"></i> Guardar cambios</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import ApiService from '../../services/ApiService.js'

export default {
    props: {
        iaData: { type: Object, default: null },
        hasError: { type: Boolean, default: false },
        consultaId: { type: [String, Number], default: null }
    },
    emits: ['diagnostico-guardado'],
    data() {
        return {
            // Toggles independientes: cada bloque se edita por su cuenta
            editandoDiagnostico: false,
            editandoRecomendaciones: false,

            guardando: false,
            errorGuardado: '',
            buscandoIcd: false,
            resultadosIcd: [],
            debounceIcd: null,

            // Overrides locales: una vez guardado, priman sobre iaData
            // (que sigue siendo la sugerencia cruda de la IA, sin tocar).
            diagnosticoConfirmado: null,
            recomendacionesConfirmadas: null,

            formDiagnostico: '',
            formIcdCodigo: '',
            formIcdTitulo: '',
            formRecomendaciones: ''
        }
    },
    computed: {
        diagnosticoPrincipal() {
            if (this.diagnosticoConfirmado) return this.diagnosticoConfirmado
            return this.iaData?.diagnostico_probable ?? 'Sin Diagnóstico';
        },
        compatibilidad() {
            if (this.hasError) return 'Último análisis disponible'
            if (this.diagnosticoConfirmado) return 'Confirmado por el médico'
            return this.iaData ? 'Análisis realizado' : 'Esperando datos clínicos';
        },
        nivelRiesgo() {
            return this.iaData?.nivel_riesgo ? String(this.iaData.nivel_riesgo).toLowerCase() : null;
        },
        alertaPrincipal() {
            return this.iaData?.nivel_riesgo ? String(this.iaData.nivel_riesgo).toUpperCase() : 'Sin alertas';
        },
        descripcionAlerta() {
            return this.iaData?.nivel_riesgo ? `Nivel de riesgo: ${this.iaData.nivel_riesgo}` : 'Paciente estable';
        },
        claseAlerta() {
            switch (this.nivelRiesgo) {
                case 'alto': case 'crítico': case 'critico': return 'bg-danger'
                case 'medio': case 'moderado': return 'bg-warning'
                case 'bajo': return 'bg-success'
                default: return 'bg-secondary'
            }
        },
        recomendaciones() {
            if (this.recomendacionesConfirmadas) {
                return this.recomendacionesConfirmadas.split('\n').filter(r => r.trim() !== '')
            }
            const recs = Array.isArray(this.iaData?.recomendaciones)
                ? this.iaData.recomendaciones.filter(r => typeof r === 'string' && r.trim() !== '')
                : [];
            return recs.length > 0 ? recs : ['Esperando síntomas clínicos'];
        }
    },
    methods: {
        activarEdicionDiagnostico() {
            this.errorGuardado = ''
            this.formDiagnostico = this.diagnosticoPrincipal === 'Sin Diagnóstico' ? '' : this.diagnosticoPrincipal
            this.formIcdCodigo = ''
            this.formIcdTitulo = ''
            this.resultadosIcd = []
            this.editandoDiagnostico = true

            // Si la IA ya sugirió un diagnóstico, buscamos de inmediato en
            // ICD-11 con ese mismo texto, para que el médico ya vea las
            // opciones oficiales de la OMS sin tener que escribir nada.
            if (this.formDiagnostico.trim().length >= 3) {
                this.buscarIcd11Inmediato(this.formDiagnostico.trim())
            }
        },
        cancelarEdicionDiagnostico() {
            if (this.guardando) return
            this.editandoDiagnostico = false
            this.resultadosIcd = []
        },

        activarEdicionRecomendaciones() {
            this.errorGuardado = ''
            this.formRecomendaciones = this.recomendaciones.includes('Esperando síntomas clínicos')
                ? ''
                : this.recomendaciones.join('\n')
            this.editandoRecomendaciones = true
        },
        cancelarEdicionRecomendaciones() {
            if (this.guardando) return
            this.editandoRecomendaciones = false
        },

        buscarIcd11() {
            clearTimeout(this.debounceIcd)
            const texto = this.formDiagnostico.trim()
            // Si el médico sigue escribiendo, cualquier código ya elegido
            // deja de ser válido hasta que vuelva a seleccionar uno.
            this.formIcdCodigo = ''
            this.formIcdTitulo = ''

            if (texto.length < 3) {
                this.resultadosIcd = []
                return
            }

            this.debounceIcd = setTimeout(async () => {
                this.buscandoIcd = true
                try {
                    const response = await ApiService.get('/icd11/buscar', { params: { texto } })
                    this.resultadosIcd = response.data.resultados || []
                } catch (error) {
                    console.error('Error al buscar en ICD-11:', error)
                    this.resultadosIcd = []
                } finally {
                    this.buscandoIcd = false
                }
            }, 400)
        },
        async buscarIcd11Inmediato(texto) {
            this.buscandoIcd = true
            try {
                const response = await ApiService.get('/icd11/buscar', { params: { texto } })
                this.resultadosIcd = response.data.resultados || []
            } catch (error) {
                console.error('Error al buscar en ICD-11:', error)
                this.resultadosIcd = []
            } finally {
                this.buscandoIcd = false
            }
        },
        seleccionarIcd(resultado) {
            this.formDiagnostico = resultado.titulo
            this.formIcdCodigo = resultado.codigo
            this.formIcdTitulo = resultado.titulo
            this.resultadosIcd = []
        },

        // Guarda lo que esté vigente en ese momento para AMBOS campos: si
        // solo se editó el diagnóstico, "formRecomendaciones" no se toca y
        // se manda el valor ya confirmado (o el de la IA si nunca se
        // confirmó); y viceversa si solo se editaron recomendaciones. Así
        // un solo endpoint sirve para los dos toggles independientes sin
        // pisar el campo que no se estaba editando.
        async guardarDiagnostico() {
            if (this.guardando || !this.consultaId) return

            const diagnosticoAEnviar = this.editandoDiagnostico
                ? this.formDiagnostico.trim()
                : (this.diagnosticoPrincipal === 'Sin Diagnóstico' ? '' : this.diagnosticoPrincipal)

            const recomendacionesAEnviar = this.editandoRecomendaciones
                ? this.formRecomendaciones.trim()
                : (this.recomendaciones.includes('Esperando síntomas clínicos') ? '' : this.recomendaciones.join('\n'))

            if (!diagnosticoAEnviar) {
                this.errorGuardado = 'El diagnóstico no puede quedar vacío.'
                return
            }

            this.guardando = true
            this.errorGuardado = ''

            try {
                const response = await ApiService.post(`/consultaIA/${this.consultaId}/diagnostico`, {
                    diagnostico: diagnosticoAEnviar,
                    diagnostico_icd11_codigo: this.editandoDiagnostico ? (this.formIcdCodigo || null) : null,
                    diagnostico_icd11_titulo: this.editandoDiagnostico ? (this.formIcdTitulo || null) : null,
                    recomendaciones: recomendacionesAEnviar || null
                })

                if (response.data.success === false) {
                    this.errorGuardado = response.data.error || 'No se pudo guardar.'
                    return
                }

                this.diagnosticoConfirmado = diagnosticoAEnviar
                this.recomendacionesConfirmadas = recomendacionesAEnviar
                this.editandoDiagnostico = false
                this.editandoRecomendaciones = false

                // Avisa al padre para sincronizar la Nota PSOAPP
                // (Análisis <- diagnóstico, Plan <- recomendaciones) y
                // para habilitar el botón "Actualizar con diagnóstico
                // confirmado" en Receta/Derivación.
                this.$emit('diagnostico-guardado', {
                    diagnostico: this.diagnosticoConfirmado,
                    recomendaciones: this.recomendacionesConfirmadas
                })

            } catch (error) {
                console.error('Error al guardar diagnóstico:', error)
                this.errorGuardado = error.response?.data?.error || 'No se pudo guardar el diagnóstico.'
            } finally {
                this.guardando = false
            }
        },
        cerrarDropdownIcd(evento) {
            // Si el clic fue dentro del contenedor del buscador (input o
            // lista), no cerramos -- eso ya lo maneja @click en cada <li>.
            if (this.$refs.contenedorIcd && this.$refs.contenedorIcd.contains(evento.target)) {
                return
            }
            this.resultadosIcd = []
        }
    },
    mounted(){
        document.addEventListener('click', this.cerrarDropdownIcd)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.cerrarDropdownIcd)
    }
}
</script>

<style scoped>
.icd-dropdown {
    position: absolute;
    z-index: 10;
    background: #fff;
    color: #212529;
    border: 1px solid #ced4da;
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    margin: 0;
    padding: 4px 0;
    list-style: none;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}
.icd-dropdown li {
    padding: 6px 10px;
    font-size: 13px;
    cursor: pointer;
}
.icd-dropdown li:hover {
    background: #f1f3f5;
}

/* Botón de editar dentro del box azul (diagnóstico) */
.btn-editar-box {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,.2);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    z-index: 2;
    transition: background .15s ease;
}
.btn-editar-box:hover {
    background: rgba(255,255,255,.35);
}

/* Variante clara del botón, para el cuadro blanco de Recomendaciones */
.recomendaciones-box {
    background: #fff;
    border-radius: 8px;
    padding: 10px;
}

.btn-editar-box--claro {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    z-index: 2;
    transition: background .15s ease;
}
.btn-editar-box--claro:hover {
    background: #e9ecef;
}
</style>