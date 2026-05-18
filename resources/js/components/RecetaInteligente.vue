
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
            <!-- SUGERENCIAS IA -->
            <div class="mb-3">
                <h6 class="text-success">
                    🤖 Sugerencias IA
                </h6>
                <div
                    v-if="medicamentosSugeridos.length === 0"
                    class="text-muted small"
                >
                    Esperando síntomas clínicos...
                </div>
                <button
                    v-for="(med,index) in medicamentosSugeridos"
                    :key="index"
                    class="btn btn-outline-success btn-sm mr-2 mb-2"
                    @click="agregarMedicamentoIA(med)"
                >
                    <i class="fas fa-plus"></i>
                    {{ med }}
                </button>
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
                    <tr
                        v-for="(med,index) in medicamentos"
                        :key="index"
                    >
                        <!-- NOMBRE -->
                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                v-model="med.nombre"
                            >
                        </td>

                        <!-- DOSIS -->
                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                v-model="med.dosis"
                            >
                        </td>
                        <!-- FRECUENCIA -->
                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                v-model="med.frecuencia"
                            >
                        </td>

                        <!-- DURACIÓN -->
                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                v-model="med.duracion"
                            >
                        </td>

                        <!-- ELIMINAR -->
                        <td>
                            <button
                                class="btn btn-danger btn-sm"
                                @click="eliminarMedicamento(index)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- AGREGAR -->
            <button
                class="btn btn-primary btn-sm btn-block"
                @click="agregarMedicamento"
            >
                <i class="fas fa-plus"></i>
                Agregar medicamento
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        sintomas: {
            type: Array,
            default: () => []

        }
    },

    data() {
        return {
            medicamentos: []

        }
    },

    computed: {
        medicamentosSugeridos() {
            let sugerencias = []
            // FIEBRE
            if(this.sintomas.includes('Fiebre')) {
                sugerencias.push('Paracetamol')
            }
            // DOLOR
            if(this.sintomas.includes('Dolor')) {
                sugerencias.push('Ibuprofeno')
            }
            // NÁUSEAS
            if(this.sintomas.includes('Náuseas')) {
                sugerencias.push('Ondansetrón')
            }
            return sugerencias
        }
    },

    methods: {
        agregarMedicamento() {
            this.medicamentos.push({
                nombre: '',
                dosis: '',
                frecuencia: '',
                duracion: ''
            })
        },

        eliminarMedicamento(index) {
            this.medicamentos.splice(index,1)
        },

        agregarMedicamentoIA(nombreMedicamento) {
            // EVITAR DUPLICADOS
            const existe = this.medicamentos.find(
                med => med.nombre === nombreMedicamento
            )
            if(existe) return
            // AGREGAR
            this.medicamentos.push({
                nombre: nombreMedicamento,
                dosis: '',
                frecuencia: '',
                duracion: ''
            })
        }
    }
}

</script>
 