import axios from 'axios'

const baseURL = document
    .querySelector('meta[name="base-url"]')
    .getAttribute('content')

const token = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content')

const apiClient = axios.create({
    baseURL: `${baseURL}/api`,
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    }
})
export default apiClient