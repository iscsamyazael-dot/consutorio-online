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

                        <button
                            v-if="!editandoDiagnosticos"
                            type="button"
                            class="btn-editar-box"
                            :class="{ 'btn-editar-box--pendiente': diagnosticosProbablesIA.length && !diagnosticosConfirmadosLocal.length }"
                            title="Editar diagnósticos"
                            @click="activarEdicionDiagnosticos"
                        >
                            <i class="fas fa-pen"></i>
                        </button>

                        <div class="inner">

                            <!-- MODO LECTURA: lista de diagnósticos confirmados -->
                            <template v-if="!editandoDiagnosticos">
                                <template v-if="diagnosticosConfirmadosLocal.length">
                                    <div v-for="(dx, i) in diagnosticosConfirmadosLocal" :key="i" class="mb-1">
                                        <h5 class="mb-0">{{ dx.diagnostico }}</h5>
                                        <small v-if="dx.icd11_codigo" class="text-white-50">ICD-11: {{ dx.icd11_codigo }}</small>
                                    </div>
                                    <p class="mt-1 mb-0">Confirmado por el médico</p>
                                </template>
                                <template v-else-if="diagnosticosProbablesIA.length">
                                    <h5 class="mb-1">
                                        {{ diagnosticosProbablesIA.length }} diagnóstico{{ diagnosticosProbablesIA.length > 1 ? 's' : '' }} sugerido{{ diagnosticosProbablesIA.length > 1 ? 's' : '' }} por IA
                                    </h5>
                                    <p
                                        v-for="(dp, i) in diagnosticosProbablesIA"
                                        :key="'probable-' + i" 
                                        class="mb-0"
                                        style="font-size: 13px; line-height: 1.4;"
                                        >
                                        {{ dp.diagnostico }} ({{ dp.porcentaje }}%)
                                    </p>
                                    <small class="text-white-50 d-block mt-1">Pendiente de confirmar</small>
                                </template>
                                <template v-else>
                                    <h5>Sin diagnóstico</h5>
                                    <p>{{ hasError ? 'Último análisis disponible' : (iaData ? 'Análisis realizado (sin confirmar)' : 'Esperando datos clínicos') }}</p>
                                </template>
                            </template>

                            <!-- MODO EDICIÓN: lista editable de diagnósticos -->
                            <template v-else>
                                <label class="d-block text-white-50 small mb-1">
                                    Diagnósticos ({{ formDiagnosticos.length }})
                                </label>

                                <!-- Sugerencias rápidas de la IA (diagnósticos probables) -->
                                <div v-if="diagnosticosProbablesIA.length" class="mb-2">
                                    <small class="text-white-50 d-block mb-1">Sugeridos por IA:</small>
                                    <button
                                        v-for="(dp, i) in diagnosticosProbablesIA"
                                        :key="'sug-'+i"
                                        type="button"
                                        class="badge badge-light text-dark mr-1 mb-1 btn-sugerencia-dx"
                                        @click="agregarDesdeSugerencia(dp)"
                                    >
                                        + {{ dp.diagnostico }} ({{ dp.porcentaje }}%)
                                    </button>
                                </div>

                                <div v-for="(dx, index) in formDiagnosticos" :key="index" class="position-relative mb-2 dx-row">
                                    <div class="position-relative" :ref="'contenedorIcd' + index">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm mb-1 pr-5"
                                            v-model="dx.diagnostico"
                                            placeholder="Escribe o busca en ICD-11..."
                                            @input="buscarIcd11(index)"
                                            @keyup.enter="buscarAhoraIcd11(index)"
                                        >
                                        <button type="button" class="btn-lupa-icd" title="Buscar de nuevo" @click="buscarAhoraIcd11(index)">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button
                                            v-if="formDiagnosticos.length > 1"
                                            type="button"
                                            class="btn-quitar-dx"
                                            title="Quitar este diagnóstico"
                                            @click="quitarDiagnostico(index)"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <ul v-if="dx.resultadosIcd && dx.resultadosIcd.length" class="icd-dropdown">
                                            <li v-for="r in dx.resultadosIcd" :key="r.codigo" @click="seleccionarIcd(index, r)">
                                                <strong>{{ r.codigo }}</strong> — {{ r.titulo }}
                                            </li>
                                        </ul>
                                        <small v-if="dx.buscandoIcd" class="text-white-50">Buscando en ICD-11...</small>
                                    </div>

                                    <small v-if="dx.icd11_codigo" class="badge badge-light text-dark">
                                        Código ICD-11: {{ dx.icd11_codigo }}
                                    </small>
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-light mb-2" @click="agregarDiagnosticoVacio">
                                    <i class="fas fa-plus"></i> Agregar diagnóstico
                                </button>

                                <div class="mt-2 d-flex" style="gap:6px;">
                                    <button type="button" class="btn btn-sm btn-light" :disabled="guardando" @click="cancelarEdicionDiagnosticos">
                                        Cancelar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" :disabled="guardando" @click="guardarDiagnosticos">
                                        <span v-if="guardando"><i class="fas fa-spinner fa-spin"></i></span>
                                        <span v-else><i class="fas fa-check"></i> Guardar</span>
                                    </button>
                                </div>
                            </template>

                        </div>
                        <div class="icon"><i class="fas fa-brain"></i></div>
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
                    <button type="button" class="btn btn-sm btn-success" :disabled="guardando" @click="guardarDiagnosticos">
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
            editandoDiagnosticos: false,
            editandoRecomendaciones: false,

            guardando: false,
            errorGuardado: '',
            buscandoIcd: false,
            resultadosIcd: [],
            debounceIcd: null,

            // Overrides locales: una vez guardado, priman sobre iaData
            // (que sigue siendo la sugerencia cruda de la IA, sin tocar).
            diagnosticosConfirmadosLocal: [], 
            diagnosticoConfirmado: null,
            recomendacionesConfirmadas: null,

            formDiagnostico: '',
            formIcdCodigo: '',
            formIcdTitulo: '',
            formDiagnosticos: [],
            formRecomendaciones: ''
        }
    },
    computed: {
        diagnosticosProbablesIA() {
            return Array.isArray(this.iaData?.diagnosticos_probables) ? this.iaData.diagnosticos_probables : []
        },
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
        activarEdicionDiagnosticos() {
            this.errorGuardado = ''

            if (this.diagnosticosConfirmadosLocal.length) {
                this.formDiagnosticos = this.diagnosticosConfirmadosLocal.map(dx => ({
                    diagnostico: dx.diagnostico,
                    icd11_codigo: dx.icd11_codigo || '',
                    icd11_titulo: dx.icd11_titulo || '',
                    resultadosIcd: [],
                    buscandoIcd: false
                }))
            } else if (this.diagnosticosProbablesIA.length) {
                this.formDiagnosticos = this.diagnosticosProbablesIA.map(dp => ({
                    diagnostico: dp.diagnostico,
                    icd11_codigo: '', icd11_titulo: '',
                    resultadosIcd: [], buscandoIcd: false
                }))
            } else {
                this.formDiagnosticos = [this.filaVacia()]
            }

            this.editandoDiagnosticos = true

            this.formDiagnosticos.forEach((dx, i) => {
                if (dx.diagnostico && dx.diagnostico.trim().length >= 3) {
                    this.buscarIcd11Inmediato(i, dx.diagnostico.trim())
                }
            })
        },
         filaVacia() {
            return { 
                diagnostico: '', 
                icd11_codigo: '', 
                icd11_titulo: '', 
                resultadosIcd: [], 
                buscandoIcd: false 
            }
        },
        cancelarEdicionDiagnosticos() {
            if (this.guardando) return
            this.editandoDiagnosticos = false
            this.formDiagnosticos = []
        },

        agregarDiagnosticoVacio() {
            this.formDiagnosticos.push(this.filaVacia())
        },

        quitarDiagnostico(index) {
            this.formDiagnosticos.splice(index, 1)
        },

        agregarDesdeSugerencia(dp) {
            const yaExiste = this.formDiagnosticos.some(
                dx => dx.diagnostico.trim().toLowerCase() === dp.diagnostico.trim().toLowerCase()
            )
            if (yaExiste) return

            if (this.formDiagnosticos.length === 1 && !this.formDiagnosticos[0].diagnostico.trim()) {
                this.formDiagnosticos[0].diagnostico = dp.diagnostico
            } else {
                this.formDiagnosticos.push({ ...this.filaVacia(), diagnostico: dp.diagnostico })
            }

            this.buscarIcd11Inmediato(this.formDiagnosticos.length - 1, dp.diagnostico)
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

        buscarIcd11(index) {
            clearTimeout(this.debounceIcd)
            const fila = this.formDiagnosticos[index]
            const texto = fila.diagnostico.trim()

            fila.icd11_codigo = ''
            fila.icd11_titulo = ''

            if (texto.length < 3) {
                fila.resultadosIcd = []
                return
            }

            this.debounceIcd = setTimeout(() => this.buscarIcd11Inmediato(index, texto), 400)
        },
        
        async buscarIcd11Inmediato(index, texto) {
            const fila = this.formDiagnosticos[index]
            if (!fila) return
            fila.buscandoIcd = true
            try {
                const response = await ApiService.get('/icd11/buscar', { params: { texto } })
                fila.resultadosIcd = response.data.resultados || []
            } catch (error) {
                console.error('Error al buscar en ICD-11:', error)
                fila.resultadosIcd = []
            } finally {
                fila.buscandoIcd = false
            }
        },

        // FIX BUG 2: dispara una búsqueda inmediata (cancelando cualquier
        // debounce pendiente) al presionar Enter en el input o al hacer
        // clic en el ícono de lupa. Así el médico puede volver a buscar
        // sin tener que cancelar y reabrir el modo edición.
        buscarAhoraIcd11(index) {
            clearTimeout(this.debounceIcd)
            const texto = this.formDiagnosticos[index].diagnostico.trim()
            if (texto.length < 3) return
            this.buscarIcd11Inmediato(index, texto)
        },

        seleccionarIcd(index, resultado) {
            const fila = this.formDiagnosticos[index]
            fila.diagnostico = resultado.titulo
            fila.icd11_codigo = resultado.codigo
            fila.icd11_titulo = resultado.titulo
            fila.resultadosIcd = []
        },
        // Guarda lo que esté vigente en ese momento para AMBOS campos: si
        // solo se editó el diagnóstico, "formRecomendaciones" no se toca y
        // se manda el valor ya confirmado (o el de la IA si nunca se
        // confirmó); y viceversa si solo se editaron recomendaciones. Así
        // un solo endpoint sirve para los dos toggles independientes sin
        // pisar el campo que no se estaba editando.
        async guardarDiagnosticos() {
            if (this.guardando || !this.consultaId) return

            const diagnosticosAEnviar = this.editandoDiagnosticos
                ? this.formDiagnosticos
                    .map(d => ({
                        diagnostico: d.diagnostico.trim(),
                        icd11_codigo: d.icd11_codigo || null,
                        icd11_titulo: d.icd11_titulo || null
                    }))
                    .filter(d => d.diagnostico !== '')
                : this.diagnosticosConfirmadosLocal

            const recomendacionesAEnviar = this.editandoRecomendaciones
                ? this.formRecomendaciones.trim()
                : (this.recomendaciones.includes('Esperando síntomas clínicos') ? '' : this.recomendaciones.join('\n'))

            if (diagnosticosAEnviar.length === 0) {
                this.errorGuardado = 'Debes confirmar al menos un diagnóstico.'
                return
            }

            this.guardando = true
            this.errorGuardado = ''

            try {
                const response = await ApiService.post(`/consultaIA/${this.consultaId}/diagnostico`, {
                    diagnosticos: diagnosticosAEnviar,          // <-- antes: diagnostico/diagnostico_icd11_codigo/diagnostico_icd11_titulo sueltos
                    recomendaciones: recomendacionesAEnviar || null
                })

                if (response.data.success === false) {
                    this.errorGuardado = response.data.error || 'No se pudo guardar.'
                    return
                }

                this.diagnosticosConfirmadosLocal = diagnosticosAEnviar
                this.recomendacionesConfirmadas = recomendacionesAEnviar
                this.editandoDiagnosticos = false
                this.editandoRecomendaciones = false

                this.$emit('diagnostico-guardado', {
                    diagnosticos: this.diagnosticosConfirmadosLocal,   // <-- antes: diagnostico (string)
                    recomendaciones: this.recomendacionesConfirmadas
                })

            } catch (error) {
                console.error('Error al guardar diagnósticos:', error)
                this.errorGuardado = error.response?.data?.error || 'No se pudo guardar el diagnóstico.'
            } finally {
                this.guardando = false
            }
        },
        cerrarDropdownIcd(evento) {
            const dentroDeAlgunaFila = Object.values(this.$refs)
                .flat()
                .filter(Boolean)
                .some(el => el.contains && el.contains(evento.target))

            if (!dentroDeAlgunaFila) {
                this.formDiagnosticos.forEach(dx => { dx.resultadosIcd = [] })
            }
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

/* Botón de lupa embebido dentro del input de diagnóstico */
.btn-lupa-icd {
    position: absolute;
    top: 3px;
    right: 6px;
    width: 22px;
    height: 22px;
    border: none;
    background: transparent;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    z-index: 3;
}
.btn-lupa-icd:hover {
    color: #495057;
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

.dx-row { padding-bottom: 4px; border-bottom: 1px dashed rgba(255,255,255,.15); }
.dx-row:last-of-type { border-bottom: none; }

.btn-quitar-dx {
    position: absolute; top: 3px; right: 28px; width: 20px; height: 20px;
    border: none; background: transparent; color: #dc3545; font-size: 12px;
    cursor: pointer; z-index: 3;
}

.btn-sugerencia-dx { border: none; cursor: pointer; }

.btn-editar-box--pendiente {
    animation: pulseBadge 1.5s ease-in-out infinite;
}
@keyframes pulseBadge {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,.4); }
    50% { box-shadow: 0 0 0 6px rgba(255,255,255,0); }
}

</style>