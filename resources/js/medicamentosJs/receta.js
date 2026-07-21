import { createApp } from 'vue'

createApp({

    data() {
        return {
            medicamentos: [
                {
                    nombre: '',
                    dosis: '',
                    frecuencia: '',
                    duracion: ''
                }
            ]
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
            this.medicamentos.splice(index, 1)
        }

    }

}).mount('#recetaApp')