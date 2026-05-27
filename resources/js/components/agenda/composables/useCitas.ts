import { ref } from 'vue'
import axios from 'axios'
import type {
    Cita,
    CreateCitaPayload,
    UpdateCitaPayload
} from '../types'

const citas = ref<Cita[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

export function useCitas() {

    async function fetchCitas() {
        try {

            loading.value = true
            error.value = null

            const response = await axios.get('/api/citas')

            citas.value = response.data

        } catch (err:any) {

            error.value =
                err.response?.data?.message ||
                'Error cargando citas'

        } finally {

            loading.value = false

        }
    }

    async function createCita(
        payload:CreateCitaPayload
    ) {

        try {

            loading.value = true

            await axios.post(
                '/api/citas',
                payload
            )

            await fetchCitas()

        } catch (err:any) {

            error.value =
                err.response?.data?.message ||
                'Error creando cita'

        } finally {

            loading.value = false

        }
    }

    async function updateCita(
        id:number,
        payload:UpdateCitaPayload
    ) {

        try {

            loading.value = true

            await axios.put(
                `/api/citas/${id}`,
                payload
            )

            await fetchCitas()

        } catch (err:any) {

            error.value =
                err.response?.data?.message ||
                'Error actualizando cita'

        } finally {

            loading.value = false

        }
    }

    async function deleteCita(id:number) {

        try {

            loading.value = true

            await axios.delete(
                `/api/citas/${id}`
            )

            await fetchCitas()

        } catch (err:any) {

            error.value =
                err.response?.data?.message ||
                'Error eliminando cita'

        } finally {

            loading.value = false

        }
    }

    return {

        citas,

        loading,

        error,

        fetchCitas,

        createCita,

        updateCita,

        deleteCita
    }
}