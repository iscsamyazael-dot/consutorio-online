
import './bootstrap';
import './medicamentosJs/receta.js';

import 'bootstrap';
import 'admin-lte';

import { createApp } from 'vue';
import ConsultaInteligente from './components/ConsultaInteligente.vue'
import masteragenda from './components/citasmedicas/mastercitas.vue'
import masterprocita from './components/citasmedicas/masterprograma.vue'
const app = createApp({})

app.component(
    'masterprocita',
    masterprocita
)
app.component(
    'masteragenda',
    masteragenda
)

app.component(
    'consulta-inteligente',
    ConsultaInteligente
)
app.mount('#app')

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import axios from  'axios';
window.axios = axios;