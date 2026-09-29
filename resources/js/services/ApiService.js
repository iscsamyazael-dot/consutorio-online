import axios from 'axios'

const baseURL = document
    .querySelector('meta[name="base-url"]')
    .getAttribute('content')

const token = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content')

const apiClient = axios.create({
    baseURL: `${baseURL}`,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    }
})

apiClient.interceptors.request.use((config) => {

    console.log('========== API REQUEST ==========')
    console.log('METHOD:', config.method)
    console.log('BASE URL:', config.baseURL)
    console.log('URL:', config.url)
    console.log(
        'FULL URL:',
        `${config.baseURL || ''}${config.url || ''}`
    )
    console.log('DATA:', config.data)
    console.log('=================================')

    if (config.data instanceof FormData) {
        delete config.headers['Content-Type']
    }

    return config
})

export default apiClient

// Servicios API por submódulo - estructura para ValoracionInteligente
export const clinicaTrabajo = {
  psicologia: {
    lista: () => apiClient.get('/clinica/psicologia'),
    obtener: (id) => apiClient.get(`/clinica/psicologia/${id}`),
    crear: (data) => apiClient.post('/clinica/psicologia', data),
    actualizar: (id, data) => apiClient.put(`/clinica/psicologia/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/psicologia/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/psicologia/${id}/imprimir`),
  },
  nutricion: {
    lista: () => apiClient.get('/clinica/nutricion'),
    obtener: (id) => apiClient.get(`/clinica/nutricion/${id}`),
    crear: (data) => apiClient.post('/clinica/nutricion', data),
    actualizar: (id, data) => apiClient.put(`/clinica/nutricion/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/nutricion/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/nutricion/${id}/imprimir`),
  },
  audiologia: {
    lista: () => apiClient.get('/clinica/audiologia'),
    obtener: (id) => apiClient.get(`/clinica/audiologia/${id}`),
    crear: (data) => apiClient.post('/clinica/audiologia', data),
    actualizar: (id, data) => apiClient.put(`/clinica/audiologia/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/audiologia/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/audiologia/${id}/imprimir`),
  },
  ergonomia: {
    lista: () => apiClient.get('/clinica/ergonomia'),
    obtener: (id) => apiClient.get(`/clinica/ergonomia/${id}`),
    crear: (data) => apiClient.post(`/clinica/ergonomia`, data),
    actualizar: (id, data) => apiClient.put(`/clinica/ergonomia/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/ergonomia/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/ergonomia/${id}/imprimir`),
  },
  medicina: {
    lista: () => apiClient.get('/clinica/medicina'),
    obtener: (id) => apiClient.get(`/clinica/medicina/${id}`),
    crear: (data) => apiClient.post('/clinica/medicina', data),
    actualizar: (id, data) => apiClient.put(`/clinica/medicina/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/medicina/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/medicina/${id}/imprimir`),
  },
  catalogo: {
    factoresPsicosociales: () => apiClient.get('/catalogo/factores-psicosociales'),
    alimentos: () => apiClient.get('/catalogo/alimentos'),
    riesgosErgonomicos: () => apiClient.get('/catalogo/riesgos-ergonomicos'),
  },
  pacientes: {
    lista: () => apiClient.get('/pacientes'),
    obtener: (id) => apiClient.get(`/pacientes/${id}`),
  },
  empresas: {
    lista: () => apiClient.get('/empresas'),
    obtener: (id) => apiClient.get(`/empresas/${id}`),
  },
  medicos: {
    lista: () => apiClient.get('/medicos'),
    obtener: (id) => apiClient.get(`/medicos/${id}`),
  },
  puestos: {
    lista: () => apiClient.get('/puestos-trabajo'),
  },
}