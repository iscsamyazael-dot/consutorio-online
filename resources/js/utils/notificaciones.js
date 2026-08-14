import { defineStore } from 'pinia'
import axios from 'axios'
import { calcularAlertas } from '../utils/alertas'

export const useNotificacionesStore = defineStore('notificaciones', {
    state: () => ({
        alertas: [],
        cargando: false,
        error: null,
    }),

    getters: {
        noLeidas: (state) => state.alertas.filter((a) => !a.read).length,
    },

    actions: {
        async fetchAlerts() {
            this.cargando = true
            this.error = null
            try {
                const { data } = await axios.get('/medicamentos')
                // Ajusta esta línea según cómo venga la respuesta real
                // (a veces Laravel Resource envuelve la lista en data.data)
                const medicamentos = data.data ?? data
                this.alertas = calcularAlertas(medicamentos)
            } catch (err) {
                this.error = 'No se pudieron cargar las alertas.'
                console.error(err)
            } finally {
                this.cargando = false
            }
        },

        marcarLeida(id) {
            const alerta = this.alertas.find((a) => a.id === id)
            if (alerta) alerta.read = true
        },
    },
})