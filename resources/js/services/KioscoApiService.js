import axios from 'axios'

const baseURL = document
    .querySelector('meta[name="base-url"]')
    .getAttribute('content')

// Clave en localStorage donde el flujo de emparejamiento guarda el
// token del dispositivo (ver pantalla de emparejamiento, pendiente).
export const KIOSCO_TOKEN_KEY = 'kiosco_device_token'

// ──────────────────────────────────────────
// Por qué este cliente es distinto de ApiService.js:
// ApiService se autentica con CSRF token + cookie de sesión del
// usuario logueado (Sanctum SPA). Las rutas api/kiosco/* están
// EXCLUIDAS de la verificación CSRF (bootstrap/app.php) porque el
// kiosco no tiene un usuario logueado — se autentica con el Bearer
// token del dispositivo emparejado. Son dos mecanismos de auth
// distintos, por eso el kiosco necesita su propia instancia en vez
// de reusar ApiService.
// ──────────────────────────────────────────
const kioscoApiClient = axios.create({
    baseURL: `${baseURL}`,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    }
})

kioscoApiClient.interceptors.request.use((config) => {
    const token = localStorage.getItem(KIOSCO_TOKEN_KEY)
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

export default kioscoApiClient