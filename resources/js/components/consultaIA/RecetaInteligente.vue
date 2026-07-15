<template>
    <!-- RECETA -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-prescription"></i>
                Receta Inteligente
            </h3>
        </div>
        <div class="card-body">
            <!-- SUGERENCIAS IA (desde inventario) -->
            <div class="mb-3">
                <h6 class="text-success">
                    🤖 Sugerencias IA
                </h6>

                <div v-if="cargandoSugerencias" class="text-muted small">
                    <i class="fas fa-spinner fa-spin"></i> Analizando triage clínico...
                </div>

                <div v-else-if="errorSugerencias" class="alert alert-danger py-1 px-2 small mb-2">
                    ⚠️ No se pudo consultar la base de medicamentos.
                </div>

                <div v-else-if="sintomas.length === 0" class="text-muted small">
                    Esperando síntomas clínicos...
                </div>

                <!-- CASO: la IA determinó que este caso requiere DERIVACIÓN, no receta -->
                <div v-else-if="tipoRespuesta === 'derivacion'">
                    <div class="alert alert-warning py-2 px-3 small mb-2">
                        ⚠️ Según el triage de la IA
                        <span v-if="triage && triage.nivel">
                            (nivel <strong>{{ triage.nivel }}</strong>)
                        </span>
                        este caso requiere <strong>derivación</strong>, no receta.
                        <div v-if="triage && triage.justificacion" class="mt-1">
                            {{ triage.justificacion }}
                        </div>
                        <div class="mt-1 font-italic">
                            Revisa el panel de Derivación para continuar con este flujo.
                        </div>
                    </div>
                </div>

                <!-- CASO: hay coincidencias reales en el inventario -->
                <div v-else-if="medicamentosSugeridos.length > 0">
                    <button
                        v-for="med in medicamentosSugeridos"
                        :key="med.id"
                        class="btn btn-outline-success btn-sm mr-2 mb-2"
                        :title="med.contraindicaciones ? 'Contraindicaciones: ' + med.contraindicaciones : ''"
                        @click="agregarMedicamentoIA(med)"
                    >
                        <i class="fas fa-plus"></i>
                        {{ med.nombre }}
                        <span v-if="med.requiere_receta" class="badge badge-warning ml-1">Rx</span>
                    </button>
                </div>

                <!-- CASO: no hay match en inventario -> sugerencias genéricas de la IA -->
                <div v-else-if="medicamentosSugeridosIA.length > 0">
                    <div class="alert alert-warning py-2 px-3 small mb-2">
                        ⚠️ <strong>No hay medicamentos en tu inventario</strong> asociados a estos síntomas.
                        La IA sugiere considerar las siguientes opciones generales:
                    </div>

                    <div
                        v-for="(med, idx) in medicamentosSugeridosIA"
                        :key="idx"
                        class="border rounded p-2 mb-2"
                    >
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ med.nombre }}</strong>
                                <span class="badge badge-secondary ml-1">no verificado</span>
                                <div class="small text-muted mt-1">{{ med.descripcion }}</div>
                            </div>
                            <button
                                class="btn btn-outline-secondary btn-sm ml-2"
                                @click="agregarMedicamentoIA(med)"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="font-italic small text-muted">
                        Estas sugerencias NO fueron verificadas contra tu inventario ni contraindicaciones.
                        No incluyen dosis — decisión y prescripción exclusiva del médico.
                    </div>
                </div>

                <div v-else class="text-muted small">
                    No se encontraron medicamentos asociados a los síntomas actuales.
                </div>
            </div>
            <hr>

            <!-- TABLA -->
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosis</th>
                        <th>Frecuencia</th>
                        <th>Duración</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(med,index) in medicamentos" :key="index">
                        <td>
                            <input type="text" class="form-control form-control-sm" v-model="med.nombre">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" v-model="med.dosis">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" v-model="med.frecuencia">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" v-model="med.duracion">
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm" @click="eliminarMedicamento(index)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button class="btn btn-primary btn-sm btn-block" @click="agregarMedicamento">
                <i class="fas fa-plus"></i>
                Agregar medicamento
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

var route = document.querySelector("[name=route]").value
var urlRecetaInteligente = route + '/recetaInteligente'

export default {
    props: {
        sintomas: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            medicamentos: [],
            medicamentosSugeridos: [],       // matches reales del inventario
            medicamentosSugeridosIA: [],     // sugerencias genéricas no verificadas ({nombre, descripcion})
            tipoRespuesta: null,             // 'receta_inteligente' | 'derivacion'
            triage: null,
            cargandoSugerencias: false,
            errorSugerencias: false
        }
    },

    watch: {
        sintomas: {
            handler(nuevosSintomas) {
                if (nuevosSintomas.length > 0) {
                    this.buscarSugerencias(nuevosSintomas)
                } else {
                    this.resetSugerencias()
                }
            },
            deep: true
        }
    },

    methods: {

        resetSugerencias() {
            this.medicamentosSugeridos = []
            this.medicamentosSugeridosIA = []
            this.tipoRespuesta = null
            this.triage = null
        },

        async buscarSugerencias(sintomas) {
            this.cargandoSugerencias = true
            this.errorSugerencias = false

            try {
                const response = await axios.post(urlRecetaInteligente, {
                    sintomas: sintomas
                })

                if (response.data.success) {
                    this.tipoRespuesta = response.data.tipo || null
                    this.triage = response.data.triage || null
                    this.medicamentosSugeridos = response.data.medicamentos || []
                    this.medicamentosSugeridosIA = response.data.medicamentos_sugeridos_ia || []
                } else {
                    this.resetSugerencias()
                }

            } catch (error) {
                console.error('Error al consultar receta inteligente:', error)
                this.errorSugerencias = true
                this.resetSugerencias()
            } finally {
                this.cargandoSugerencias = false
            }
        },

        agregarMedicamento() {
            this.medicamentos.push({ nombre: '', dosis: '', frecuencia: '', duracion: '' })
        },

        eliminarMedicamento(index) {
            this.medicamentos.splice(index,1)
        },

        agregarMedicamentoIA(med) {
            const existe = this.medicamentos.find(m => m.nombre === med.nombre)
            if(existe) return

            this.medicamentos.push({
                nombre: med.nombre,
                dosis: med.concentracion || '',
                frecuencia: '',
                duracion: ''
            })
        }
    }
}
</script>