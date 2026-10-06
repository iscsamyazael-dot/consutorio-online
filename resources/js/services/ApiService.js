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
    
    // ============ NUEVOS MÉTODOS: AUDIOLOGÍA DELLI 2026 ============
    // Rutas concentradas en web.php (SIN /api/)
    generarFolioDelli: () => apiClient.get('/clinica/audiologia/delli/generar-folio'),
    guardarFichaAudiologicaDelli: (data) => apiClient.post('/clinica/audiologia/delli/guardar', data),
    obtenerFichaAudiologicaDelli: (id) => apiClient.get(`/clinica/audiologia/delli/${id}`),
    actualizarFichaAudiologicaDelli: (id, data) => apiClient.put(`/clinica/audiologia/delli/${id}`, data),
    eliminarFichaAudiologicaDelli: (id) => apiClient.delete(`/clinica/audiologia/delli/${id}`),
    listarFichasAudiologicaDelli: (filtro = '') => apiClient.get('/clinica/audiologia/delli/', { params: { filtro } }),
    exportarFichaDELLIaPDF: (id) => apiClient.get(`/clinica/audiologia/delli/${id}/pdf`),
    
    // ============ NUEVOS MÉTODOS: AUDIOLOGÍA EXAMEN_TR ============
    // Rutas concentradas en web.php (SIN /api/)
    guardarExamenTrabajoTr: (data) => apiClient.post('/clinica/audiologia/tr/guardar', data),
    obtenerExamenTrabajoTr: (id) => apiClient.get(`/clinica/audiologia/tr/${id}`),
    actualizarExamenTrabajoTr: (id, data) => apiClient.put(`/clinica/audiologia/tr/${id}`, data),
    eliminarExamenTrabajoTr: (id) => apiClient.delete(`/clinica/audiologia/tr/${id}`),
    listarExamenesTr: (filtro = '') => apiClient.get('/clinica/audiologia/tr/', { params: { filtro } }),
    exportarExamenTRaPDF: (id) => apiClient.get(`/clinica/audiologia/tr/${id}/pdf`),
    
    // ============ UTILIDADES ============
    buscarPacienteAudiologia: (parametro) => apiClient.post('/clinica/audiologia/buscar/paciente', { parametro }),
    obtenerHistorialAudiologia: (cedula) => apiClient.get(`/clinica/audiologia/buscar/historial/${cedula}`),
    compararAudiometrias: (fichaAntigua, fichaActual) => apiClient.post('/clinica/audiologia/buscar/comparar-audiometrias', { antigua: fichaAntigua, actual: fichaActual }),
  },
  ergonomia: {
    lista: () => apiClient.get('/clinica/ergonomia'),
    obtener: (id) => apiClient.get(`/clinica/ergonomia/${id}`),
    crear: (data) => apiClient.post(`/clinica/ergonomia`, data),
    actualizar: (id, data) => apiClient.put(`/clinica/ergonomia/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/ergonomia/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/ergonomia/${id}/imprimir`),
  },
  espirometria: {
    lista: () => apiClient.get('/clinica/espirometria'),
    obtener: (id) => apiClient.get(`/clinica/espirometria/${id}`),
    crear: (data) => apiClient.post('/clinica/espirometria', data),
    actualizar: (id, data) => apiClient.put(`/clinica/espirometria/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/espirometria/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/espirometria/${id}/imprimir`),
  },
  medicina: {
    lista: () => apiClient.get('/clinica/medicina'),
    obtener: (id) => apiClient.get(`/clinica/medicina/${id}`),
    crear: (data) => apiClient.post('/clinica/medicina', data),
    actualizar: (id, data) => apiClient.put(`/clinica/medicina/${id}`, data),
    eliminar: (id) => apiClient.delete(`/clinica/medicina/${id}`),
    imprimir: (id) => apiClient.get(`/clinica/medicina/${id}/imprimir`),
    guardarFichaCompleta: (data) => apiClient.post('/clinica/medicina/ficha-completa', data),
    consultarIA: (data) => apiClient.post('/clinica/medicina/consultar-ia', data),
  },
  catalogo: {
    factoresPsicosociales: () => apiClient.get('/catalogo/factores-psicosociales'),
    alimentos: () => apiClient.get('/catalogo/alimentos'),
    riesgosErgonomicos: () => apiClient.get('/catalogo/riesgos-ergonomicos'),
  },
  pacientes: {
    lista: () => apiClient.get('/pacientes'),
    obtener: (id) => apiClient.get(`/pacientes/${id}`),
    buscar: (params) => apiClient.get('/pacientes/buscar', { params: { buscar: params.q || params.buscar } }),
  },
  empresas: {
    lista: () => apiClient.get('/empresas-cliente'),
    obtener: (id) => apiClient.get(`/empresas-cliente/${id}`),
  },
  medicos: {
    lista: () => apiClient.get('/medicos'),
    obtener: (id) => apiClient.get(`/medicos/${id}`),
  },
  puestos: {
    lista: () => apiClient.get('/puestos-trabajo'),
  },
  // MÉTODOS DE NIVEL RAÍZ para MasterFichaOcupacional
  buscarPacientes: (params) => apiClient.get('/pacientes/buscar', { params: { buscar: params.q || params.buscar } }),
  
  consultarIA: (data) => apiClient.post('/api/clinica-trabajo/ficha-ocupacional/analizar', data),
  
  guardarFichaCompleta: (data) => apiClient.post('/api/clinica-trabajo/ficha-ocupacional', data),
  
  obtenerEvaluacion: (id) => apiClient.get(`/api/clinica-trabajo/ficha-ocupacional/${id}`),
  
  actualizarEvaluacion: (id, data) => apiClient.put(`/api/clinica-trabajo/ficha-ocupacional/${id}`, data),
  
  listarEvaluaciones: (params) => apiClient.get('/api/clinica-trabajo/ficha-ocupacional', { params }),
}