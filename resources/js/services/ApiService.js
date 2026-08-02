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

// Si el body es FormData (por ejemplo, al subir archivos como el logo de
// la sede), dejamos que el navegador calcule el Content-Type con el
// boundary correcto en vez de forzar 'application/json' a mano. Sin esto,
// Laravel no puede parsear el archivo y $request->hasFile() da false.
apiClient.interceptors.request.use((config) => {
    if (config.data instanceof FormData) {
        delete config.headers['Content-Type']
    }
    return config
})

export default apiClient