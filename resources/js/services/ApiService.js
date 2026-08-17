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